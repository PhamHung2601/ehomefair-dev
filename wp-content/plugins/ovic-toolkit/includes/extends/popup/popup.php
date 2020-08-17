<?php
if ( !class_exists( 'Ovic_Popup' ) ) {
	class Ovic_Popup
	{
		public $key     = '_ovic_popup_settings';
		public $options = array();
		public $popups  = array();
		public $pages   = array();

		public function __construct()
		{
			if ( is_admin() ) {
				$this->get_popups();
				$this->get_pages();
			}
			$this->options = get_option( $this->key );
			add_action( 'init', array( $this, 'post_type' ) );
			add_filter( 'ovic_registered_settings', array( $this, 'add_options' ) );
			add_filter( 'ovic_registered_metabox_settings', array( $this, 'meta_box' ) );
			if ( isset( $this->options['enable_popup'] ) && $this->options['enable_popup'] == 'on' ) {
				add_filter( 'body_class', array( $this, 'body_class' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
				add_action( 'wp_ajax_ovic_get_content_popup', array( $this, 'get_content_popup' ) );
				add_action( 'wp_ajax_nopriv_ovic_get_content_popup', array( $this, 'get_content_popup' ) );
			}
		}

		public function get_popups()
		{
			$args   = array(
				'post_type'      => 'popup',
				'posts_per_page' => -1,
				'orderby'        => 'ASC',
			);
			$loop   = get_posts( $args );
			$popups = array();
			foreach ( $loop as $value ) {
				setup_postdata( $value );
				$popups[$value->ID] = $value->post_title;
			}
			wp_reset_postdata();
			$this->popups = $popups;
		}

		public function get_pages()
		{
			// Get pages
			$args  = array(
				'post_type'      => 'page',
				'posts_per_page' => -1,
				'orderby'        => 'ASC',
			);
			$loop  = get_posts( $args );
			$pages = array();
			foreach ( $loop as $value ) {
				setup_postdata( $value );
				$pages[$value->ID] = $value->post_title;
			}
			wp_reset_postdata();
			$this->pages = $pages;
		}

		public function post_type()
		{
			$args = array(
				'labels'              => array(
					'name'               => __( 'Popup', 'ovic-toolkit' ),
					'singular_name'      => __( 'Popup menu item', 'ovic-toolkit' ),
					'add_new'            => __( 'Add new', 'ovic-toolkit' ),
					'add_new_item'       => __( 'Add new Popup item', 'ovic-toolkit' ),
					'edit_item'          => __( 'Edit Popup item', 'ovic-toolkit' ),
					'new_item'           => __( 'New Popup item', 'ovic-toolkit' ),
					'view_item'          => __( 'View Popup item', 'ovic-toolkit' ),
					'search_items'       => __( 'Search Popup items', 'ovic-toolkit' ),
					'not_found'          => __( 'No Popup items found', 'ovic-toolkit' ),
					'not_found_in_trash' => __( 'No Popup items found in trash', 'ovic-toolkit' ),
					'parent_item_colon'  => __( 'Parent Popup item:', 'ovic-toolkit' ),
					'menu_name'          => __( 'Popup', 'ovic-toolkit' ),
				),
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor' ),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => 'ovic-dashboard',
				'menu_position'       => 40,
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => false,
				'show_in_rest'        => true,
				'capability_type'     => 'page',
				'menu_icon'           => 'dashicons-welcome-widgets-menus',
			);
			register_post_type( 'popup', $args );
		}

		public function scripts()
		{
			if ( $this->check_page_enable_popup() ) {
				$current_page_id = $this->get_page_current();
				$popup_id        = isset( $this->options['popup_content'] ) ? $this->options['popup_content'] : 0;
				$_popup_in_page  = get_post_meta( $current_page_id, '_ovic_popup_display_for_page', true );
				// Check settings meta box
				if ( is_numeric( $_popup_in_page ) && $_popup_in_page > 0 ) {
					$popup_id = $_popup_in_page;
				}
				$enable = isset( $this->options['enable_popup'] ) ? $this->options['enable_popup'] : 0;
				if ( !$popup_id )
					$enable = 0;
				wp_enqueue_style( 'ovic-popup', OVIC_TOOLKIT_PLUGIN_URL . 'includes/extends/popup/popup.css' );
				wp_enqueue_style( 'magnific-popup', OVIC_TOOLKIT_PLUGIN_URL . 'assets/css/magnific-popup.css' );
				wp_enqueue_script( 'magnific-popup', OVIC_TOOLKIT_PLUGIN_URL . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ), '1.0', true );
				wp_enqueue_script( 'ovic-popup', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/popup/popup.min.js', array( 'jquery' ), '1.0', true );
				wp_localize_script( 'ovic-popup', 'ovic_popup', array(
						'ajaxurl'             => admin_url( 'admin-ajax.php' ),
						'security'            => wp_create_nonce( 'ovic_popup' ),
						'enable_popup'        => $enable,
						'delay_time'          => isset( $this->options['delay_time'] ) ? $this->options['delay_time'] : 0,
						'enable_popup_mobile' => isset( $this->options['enable_popup_mobile'] ) ? $this->options['enable_popup_mobile'] : 0,
						'pages_display'       => isset( $this->options['pages_display'] ) ? $this->options['pages_display'] : array(),
						'current_page_id'     => $current_page_id,
					)
				);
				if ( $popup_id ) {
					$shortcodes_custom_css = '';
					$shortcodes_custom_css .= get_post_meta( $popup_id, '_wpb_post_custom_css', true );
					$shortcodes_custom_css .= get_post_meta( $popup_id, '_wpb_shortcodes_custom_css', true );
					$shortcodes_custom_css .= get_post_meta( $popup_id, '_Ovic_Shortcode_custom_css', true );
					if ( $shortcodes_custom_css != '' ) {
						wp_add_inline_style( 'ovic-popup', $shortcodes_custom_css );
					}
				}
			}
		}

		public function add_options( $options )
		{
			if ( isset( $options['extends_options']['sections'] ) ) {
				$options['extends_options']['sections'][$this->key] = array(
					'id'     => $this->key,
					'title'  => __( 'Popup', 'ovic-toolkit' ),
					'fields' => array(
						array(
							'name' => 'Popup Enable',
							'id'   => 'enable_popup',
							'type' => 'switch',
						),
						array(
							'name'             => __( 'Popup Content', 'ovic-toolkit' ),
							'desc'             => 'Select an option',
							'id'               => 'popup_content',
							'type'             => 'select',
							'show_option_none' => true,
							'default'          => '0',
							'options'          => $this->popups,
						),
						array(
							'name'    => __( 'Delay time', 'ovic-toolkit' ),
							'default' => 0,
							'id'      => 'delay_time',
							'type'    => 'text_small',
						),
						array(
							'name' => 'Mobile Display',
							'id'   => 'enable_popup_mobile',
							'type' => 'switch',
						),
						array(
							'name'       => __( 'Pages Display', 'ovic-toolkit' ),
							'id'         => 'pages_display',
							'desc'       => 'Select options.',
							'type'       => 'pw_multiselect',
							'options'    => $this->pages,
							'attributes' => array(
								'placeholder' => __( 'Select pages', 'ovic-toolkit' ),
							),
						),
					),
				);
			}

			return $options;
		}

		public function meta_box( $options )
		{
			// Options for page
			$options[] = array(
				'id'           => 'popup_settings',
				'title'        => esc_html__( 'Popup Settings', 'ovic-toolkit' ),
				'object_types' => array( 'page' ), // Post type
				'context'      => 'normal',
				'fields'       => array(
					array(
						'name'             => esc_html__( 'Popup Display', 'ovic-toolkit' ),
						'id'               => '_ovic_popup_display_for_page',
						'type'             => 'select',
						'show_option_none' => true,
						'options'          => $this->popups,
					),
				),
			);
			// Options for popup
			$options[] = array(
				'id'           => 'popup_item_settings',
				'title'        => esc_html__( 'Popup Settings', 'ovic-toolkit' ),
				'object_types' => array( 'popup' ), // Post type
				'context'      => 'side',
				'fields'       => array(
					array(
						'name'             => esc_html__( 'Effect Display', 'ovic-toolkit' ),
						'id'               => '_ovic_popup_display_effect',
						'type'             => 'select',
						'show_option_none' => true,
						'options'          => array(
							'mfp-zoom-in'         => __( 'Zoom', 'ovic-toolkit' ),
							'mfp-newspaper'       => __( 'Newspaper', 'ovic-toolkit' ),
							'mfp-move-horizontal' => __( 'Horizontal Move', 'ovic-toolkit' ),
							'mfp-move-from-top'   => __( 'Move From Top', 'ovic-toolkit' ),
							'mfp-3d-unfold'       => __( '3d Unfold', 'ovic-toolkit' ),
							'mfp-zoom-out'        => __( 'Zoom-out', 'ovic-toolkit' ),
						),
					),
				),
			);

			return $options;
		}

		public function body_class( $class )
		{
			if ( $this->check_page_enable_popup() ) {
				$class[] = 'ovic-popup-on';
			}

			return $class;
		}

		public function check_page_enable_popup()
		{
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'yith-woocompare-view-table' ) {
				return false;
			}
			if ( isset( $this->options['enable_popup'] ) && $this->options['enable_popup'] == 'on' ) {
				$page_id       = $this->get_page_current();
				$pages_display = isset( $this->options['pages_display'] ) ? (array)$this->options['pages_display'] : array();
				if ( !empty( $pages_display ) && in_array( $page_id, $pages_display ) ) {
					return true;
				}
			}

			return false;
		}

		public function get_page_current()
		{
			$page_id = 0;
			if ( is_front_page() && is_home() ) {
				// Default homepage
			} elseif ( is_front_page() ) {
				$page_id = get_option( 'page_on_front' );
			} elseif ( is_home() ) {
				$page_id = get_option( 'page_for_posts' );
			} elseif ( is_page() ) {
				$page_id = get_the_ID();
			}
			if ( class_exists( 'WooCommerce' ) ) {
				if ( is_shop() ) {
					$page_id = get_option( 'woocommerce_shop_page_id' );
				}
			}

			return $page_id;
		}

		public function get_content_popup()
		{
			$current_page_id              = $_POST['current_page_id'];
			$popup_id                     = isset( $this->options['popup_content'] ) ? $this->options['popup_content'] : 0;
			$_ovic_popup_display_for_page = get_post_meta( $current_page_id, '_ovic_popup_display_for_page', true );
			// Check settings meta box
			if ( is_numeric( $_ovic_popup_display_for_page ) && $_ovic_popup_display_for_page > 0 ) {
				$popup_id = $_ovic_popup_display_for_page;
			}
			if ( class_exists( 'Vc_Manager' ) ) {
				WPBMap::addAllMappedShortcodes();
			}
			$_ovic_popup_display_effect = get_post_meta( $popup_id, '_ovic_popup_display_effect', true );
			$data                       = array(
				'display_effect' => $_ovic_popup_display_effect,
				'content'        => '',
			);
			ob_start();
			?>
            <div class="ovic-popup">
				<?php
				$query = new WP_Query( array( 'p' => $popup_id, 'post_type' => 'popup', 'posts_per_page' => 1 ) );
				if ( $query->have_posts() ):
					while ( $query->have_posts() ): $query->the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile;
				endif;
				wp_reset_postdata();
				?>
            </div>
			<?php
			$data['content'] = apply_filters( 'ovic_popup_output_content', ob_get_clean() );
			wp_send_json( $data );
			wp_die();
		}
	}

	new Ovic_Popup();
}