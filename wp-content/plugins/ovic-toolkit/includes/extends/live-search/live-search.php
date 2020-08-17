<?php
if ( !class_exists( 'Ovic_Live_Search' ) ) {
	class Ovic_Live_Search
	{
		public         $key     = '_ovic_live_search_settings';
		public         $options = array();
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Ovic_Live_Search ) ) {
				self::$instance = new Ovic_Live_Search;
			}

			return self::$instance;
		}

		public function __construct()
		{
			$this->options      = get_option( $this->key );
			$enable_live_search = isset( $this->options['enable_live_search'] ) ? $this->options['enable_live_search'] : false;
			if ( $enable_live_search ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
				add_action( 'wp_ajax_ovic_live_search', array( $this, 'get_results' ) );
				add_action( 'wp_ajax_nopriv_ovic_live_search', array( $this, 'get_results' ) );
				add_shortcode( "ovic_live_search_form", array( $this, 'live_search_form' ) );
			}
			/* INCLUDE WIDGET */
			include_once( 'widget-livesearch.php' );
			/* CREATE OPTIONS */
			add_filter( 'ovic_registered_settings', array( $this, 'add_options' ) );
		}

		public function scripts()
		{
			$enable_live_search = isset( $this->options['enable_live_search'] ) ? $this->options['enable_live_search'] : false;
			if ( $enable_live_search != false ) {
				wp_enqueue_style( 'ovic-live-search', OVIC_TOOLKIT_PLUGIN_URL . 'includes/extends/live-search/assets/css/live-search.min.css' );
				wp_enqueue_script( 'ovic-live-search', OVIC_TOOLKIT_PLUGIN_URL . 'includes/extends/live-search/assets/js/live-search.min.js', array( 'jquery' ), '1.0', true );
				wp_localize_script( 'ovic-live-search', 'ovic_ajax_live_search', array(
						'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
						'security'                        => wp_create_nonce( 'ovic_ajax_live_search' ),
						'view_all_text'                   => esc_html__( 'View All', 'ovic-toolkit' ),
						'product_matches_text'            => esc_html__( 'Product Matches', 'ovic-toolkit' ),
						'results_text'                    => esc_html__( 'Results', 'ovic-toolkit' ),
						'ovic_live_search_min_characters' => isset( $this->options['min_characters'] ) ? $this->options['min_characters'] : 3,
					)
				);
			}
		}

		public function add_options( $options )
		{
			if ( isset( $options['extends_options']['sections'] ) ) {
				$options['extends_options']['sections'][$this->key] = array(
					'id'     => $this->key,
					'title'  => __( 'Live Search', 'ovic-toolkit' ),
					'fields' => array(
						array(
							'name' => __( 'Live Search Enable', 'ovic-toolkit' ),
							'id'   => 'enable_live_search',
							'type' => 'switch',
						),
						array(
							'name' => __( 'Display suggestion', 'ovic-toolkit' ),
							'id'   => 'show_suggestion',
							'type' => 'switch',
						),
						array(
							'name'    => __( 'Search Min Characters', 'ovic-toolkit' ),
							'default' => 3,
							'id'      => 'min_characters',
							'type'    => 'text_small',
						),
						array(
							'name'    => __( 'Search Max Results', 'ovic-toolkit' ),
							'default' => 3,
							'id'      => 'max_results',
							'type'    => 'text_small',
						),
						array(
							'name'    => __( 'Search In', 'ovic-toolkit' ),
							'id'      => 'search_in',
							'type'    => 'multicheck',
							'options' => array(
								'title'       => esc_html__( 'Title', 'ovic-toolkit' ),
								'description' => esc_html__( 'Description', 'ovic-toolkit' ),
								'content'     => esc_html__( 'Content', 'ovic-toolkit' ),
								'sku'         => esc_html__( 'SKU', 'ovic-toolkit' ),
							),
						),
					),
				);
			}

			return $options;
		}

		/**
		 * Get results for the requested keyword.
		 *
		 * @return  void
		 */
		public function get_results()
		{
			$keyword     = $_POST['keyword'];
			$product_cat = $_POST['product_cat'];
			if ( !isset( $keyword ) || $keyword == '' ) {
				exit;
			}
			$data                    = array();
			$options                 = get_option( $this->key );
			$data['max_results']     = isset( $options['max_results'] ) ? $options['max_results'] : 3;
			$data['show_suggestion'] = isset( $options['show_suggestion'] ) ? $options['show_suggestion'] : '';
			$data['search_in']       = isset( $options['search_in'] ) ? $options['search_in'] : array( 'title' );
			$data['keyword']         = $keyword;
			$data['product_cat']     = $product_cat;
			$args                    = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'orderby'        => 'post_title',
				'order'          => 'ASC',
				'posts_per_page' => ( int )$data['max_results'],
			);
			if ( $product_cat != "" ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array_map( 'sanitize_title', explode( ',', $product_cat )
						),
					),
				);
			}
			// Prepare suggestion setting.
			if ( $data['show_suggestion'] != 'on' ) {
				$args['fields'] = 'ids';
			}
			// Globalize Live Search settings.
			$GLOBALS['ovic_live_search_settings'] = $data;
			// Register where filter.
			add_filter( 'posts_where', array( __CLASS__, 'posts_where' ) );
			add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ) );
			// Register join filter.
			if ( in_array( 'sku', $data['search_in'] ) ) {
				add_filter( 'posts_join', array( __CLASS__, 'posts_join' ) );
			}
			// Prepare return data.
			$return_data = array();
			// Query for results.
			$products                    = new WP_Query();
			$products                    = $products->query( $args );
			$return_data['result_count'] = count( $products );
			if ( $products ) {
				foreach ( $products as $key => $product ) {
					$product = wc_get_product( $product );
					// Add property sku to products
					if ( $data['show_suggestion'] == 'on' && in_array( 'sku', $data['search_in'] ) ) {
						$products[$key]->ovic_sku = $product->get_sku();
					}
					if ( $product ) {
						$return_data['list_product'][] = array(
							'title' => $product->get_title(),
							'url'   => $product->get_permalink(),
							'image' => $product->get_image( array( 100, 100 ) ),
							'price' => $product->get_price_html(),
						);
					}
				}
				if ( $data['show_suggestion'] == 'on' ) {
					foreach ( $products as $product ) {
						// Find keyword in title.
						if ( in_array( 'title', $data['search_in'] ) ) {
							// Convert HTML tag and shortcode to space.
							$content_search = preg_replace( '/\\[[^\\]]*\\]|<[^>]*>/', ' ', $product->post_title );
							// Find keyword.
							$position_keyword = stripos( $content_search, $data['keyword'] );
							if ( $position_keyword !== false && $position_keyword + strlen( $data['keyword'] ) < strlen( $content_search ) ) {
								// Get suggestion of keyword in content.
								$return_data['suggestion'] = self::get_suggestion( $content_search, $data['keyword'] );
								break;
							}
						}
						// Find keyword in description.
						if ( in_array( 'description', $data['search_in'] ) && !isset( $return_data['suggestion'] ) ) {
							// Convert HTML tag and shortcode to space.
							$content_search = preg_replace( '/\\[[^\\]]*\\]|<[^>]*>/', ' ', $product->post_excerpt );
							// Find keyword.
							$position_keyword = stripos( $content_search, $data['keyword'] );
							if ( $position_keyword !== false && $position_keyword + strlen( $data['keyword'] ) < strlen( $content_search ) ) {
								// Get suggestion of keyword in content.
								$return_data['suggestion'] = self::get_suggestion( $content_search, $data['keyword'] );
								break;
							}
						}
						// Find keyword in content.
						if ( in_array( 'content', $data['search_in'] ) && !isset( $return_data['suggestion'] ) ) {
							// Convert HTML tag and shortcode to space.
							$content_search = preg_replace( '/\\[[^\\]]*\\]|<[^>]*>/', ' ', $product->post_content );
							// Find keyword.
							$position_keyword = stripos( $content_search, $data['keyword'] );
							if ( $position_keyword !== false && $position_keyword + strlen( $data['keyword'] ) < strlen( $content_search ) ) {
								// Get suggestion of keyword in content.
								$return_data['suggestion'] = self::get_suggestion( $content_search, $data['keyword'] );
								break;
							}
						}
						// Find keyword in sku.
						if ( in_array( 'sku', $data['search_in'] ) && !isset( $return_data['suggestion'] ) ) {
							// Convert HTML tag and shortcode to space.
							$content_search = preg_replace( '/\\[[^\\]]*\\]|<[^>]*>/', ' ', $product->ovic_sku );
							// Find keyword.
							$position_keyword = stripos( $content_search, $data['keyword'] );
							if ( $position_keyword !== false && $position_keyword + strlen( $data['keyword'] ) < strlen( $content_search ) ) {
								// Get suggestion of keyword in content.
								$return_data['suggestion'] = self::get_suggestion( $content_search, $data['keyword'] );
								break;
							}
						}
					}
				}
				wp_send_json( $return_data );
			}
			wp_send_json( array( 'message' => esc_html__( 'No results.', 'ovic-toolkit' ) ) );
			wp_die();
		}

		/**
		 * Prepare where clause for query statement.
		 *
		 * @param   string $where Current where clause.
		 *
		 * @return  string
		 */
		public static function posts_where( $where )
		{
			global $wpdb, $ovic_live_search_settings;
			// Prepare search coverages.
			$columns = array();
			if ( in_array( 'title', $ovic_live_search_settings['search_in'] ) ) {
				$columns[] = ' ' . $wpdb->posts . '.post_title LIKE "%' . sanitize_text_field( $ovic_live_search_settings['keyword'] ) . '%" ';
			}
			if ( in_array( 'description', $ovic_live_search_settings['search_in'] ) ) {
				$columns[] = ' ' . $wpdb->posts . '.post_excerpt LIKE "%' . sanitize_text_field( $ovic_live_search_settings['keyword'] ) . '%" ';
			}
			if ( in_array( 'content', $ovic_live_search_settings['search_in'] ) ) {
				$columns[] = ' ' . $wpdb->posts . '.post_content LIKE "%' . sanitize_text_field( $ovic_live_search_settings['keyword'] ) . '%" ';
			}
			if ( in_array( 'sku', $ovic_live_search_settings['search_in'] ) ) {
				$columns[] = '( ' . $wpdb->postmeta . '.meta_key = "_sku" AND ' . $wpdb->postmeta . '.meta_value LIKE "%' . sanitize_text_field( $ovic_live_search_settings['keyword'] ) . '%" )';
			}
			if ( count( $columns ) ) {
				$where .= ' AND ( ' . implode( ' OR ', $columns ) . ' ) ';
			}

			return $where;
		}

		/**
		 * Prepare groupby clause for query statement.
		 *
		 * @param   string $groupby Current groupby clause.
		 *
		 * @return  string
		 */
		public static function posts_groupby( $groupby )
		{
			global $wpdb;
			$groupby = "{$wpdb->posts}.ID";

			return $groupby;
		}

		/**
		 * Prepare join clause for query statement.
		 *
		 * @param   string $join Current join clause.
		 *
		 * @return  string
		 */
		public static function posts_join( $join )
		{
			global $wpdb;
			if ( strpos( $join, $wpdb->postmeta ) === false ) {
				$join .= ' INNER JOIN ' . $wpdb->postmeta . ' ON ( ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ) ';
			}

			return $join;
		}

		/**
		 * Get suggestion of keyword in content.
		 *
		 * @param   string $content Content.
		 * @param   string $keyword Keyword.
		 *
		 * @return  string
		 */
		public static function get_suggestion( $content, $keyword )
		{
			// Get the postion of the first keyword in content.
			$index_keyword = stripos( $content, $keyword );
			// Strip the content from that keyword postion.
			$post_title = substr( $content, ( $index_keyword + strlen( $keyword ) ), 40 );
			// Get the postion of the last keyword in content.
			$index_keyword = stripos( $content, $post_title ) + strlen( $post_title );
			// Prepare the title.
			for ( $i = 0; $i < 30; $i++ ) {
				$post_title_add = substr( $content, $index_keyword + $i, 1 );
				if ( $post_title_add == ' ' ) {
					break;
				} else {
					$post_title .= $post_title_add;
				}
			}

			return $keyword . $post_title;
		}

		public function live_search_form( $atts, $content = '' )
		{
			$default = array(
				'placeholder' => __( 'Search products', 'ovic-toolkit' ),
			);
			$atts    = shortcode_atts( $default, $atts );
			ob_start();
			?>
            <form method="get" action="<?php echo esc_url( home_url( '/' ) ) ?>"
                  class="block-search ovic-live-search-form">
				<?php if ( class_exists( 'WooCommerce' ) ): ?>
                    <input type="hidden" name="post_type" value="product"/>
				<?php endif; ?>
                <div class="search-box results-search">
                    <input autocomplete="off" type="text" class="serchfield txt-livesearch" name="s"
                           value="<?php echo esc_attr( get_search_query() ); ?>"
                           placeholder="<?php echo esc_html( $atts['placeholder'] ); ?>">
                </div>
            </form>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'ovic_output_live_search_form', $html, $atts );
		}
	}
}
$ovic_live_search = new Ovic_Live_Search();
$ovic_live_search::instance();
