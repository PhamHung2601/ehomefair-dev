<?php
/**
 * @version    1.0
 * @package    Ovic_Mapper
 */
if ( !class_exists( 'Ovic_Mapper_Post_Type' ) ) {
	class Ovic_Mapper_Post_Type
	{
		/**
		 * Initialize.
		 *
		 * @return  void
		 */
		public static function initialize()
		{
			// Register Post_Type.
			add_action( 'init', array( __CLASS__, 'register_post_type' ), 999 );
			// Register Ajax actions / filters.
			add_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'search_products' ) );
		}

		public static function register_post_type()
		{
			$labels = array(
				'name'               => __( 'Mapper', 'ovic-toolkit' ),
				'singular_name'      => __( 'Mapper', 'ovic-toolkit' ),
				'menu_name'          => __( 'Mapper', 'ovic-toolkit' ),
				'name_admin_bar'     => __( 'Mapper', 'ovic-toolkit' ),
				'add_new'            => __( 'Add New', 'ovic-toolkit' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'ovic-toolkit' ), 'Mapper' ),
				'new_item'           => sprintf( __( 'New %s', 'ovic-toolkit' ), 'Mapper' ),
				'edit_item'          => sprintf( __( 'Edit %s', 'ovic-toolkit' ), 'Mapper' ),
				'view_item'          => sprintf( __( 'View %s', 'ovic-toolkit' ), 'Mapper' ),
				'all_items'          => sprintf( __( '%s', 'ovic-toolkit' ), 'Mapper' ),
				'search_items'       => sprintf( __( 'Search %s', 'ovic-toolkit' ), 'Mapper' ),
				'parent_item_colon'  => sprintf( __( 'Parent %s:', 'ovic-toolkit' ), 'Mapper' ),
				'not_found'          => sprintf( __( 'No %s found.', 'ovic-toolkit' ), 'Mapper' ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'ovic-toolkit' ), 'Mapper' ),
			);
			$args   = array(
				'labels'             => $labels,
				'description'        => '',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'ovic-dashboard',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'ovic_mapper' ),
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-pressthis',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 4,
				'supports'           => array( 'title', 'editor' ),
			);
			register_post_type( 'ovic_mapper', $args );
			// Check if WR Mapper page is requested.
			global $pagenow, $post_type, $post;
			if ( in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
				// Get current post type.
				if ( !isset( $post_type ) ) {
					$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : null;
				}
				if ( empty( $post_type ) && ( isset( $post ) || isset( $_REQUEST['post'] ) ) ) {
					$post_type = isset( $post ) ? $post->post_type : get_post_type( $_REQUEST['post'] );
				}
				if ( 'ovic_mapper' == $post_type ) {
					add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 999 );
					if ( 'edit.php' == $pagenow ) {
						// Register necessary actions / filters to customize All Items screen.
						add_filter( 'bulk_actions-edit-ovic_mapper', array( __CLASS__, 'bulk_actions' ) );
						add_filter( 'manage_ovic_mapper_posts_columns', array( __CLASS__, 'register_columns' ) );
						add_action( 'manage_posts_custom_column', array( __CLASS__, 'display_columns' ), 10, 2 );
					} else if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
						if ( !isset( $_REQUEST['action'] ) || 'trash' != $_REQUEST['action'] ) {
							// Register necessary actions / filters to override Item Details screen.
							add_action( 'admin_footer', array( __CLASS__, 'load_edit_form' ) );
							add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );
						}
					}
				}
			}
		}

		/**
		 * Setup bulk actions for in stock alert subscription screen.
		 *
		 * @param array $actions Current actions.
		 *
		 * @return  array
		 */
		public static function bulk_actions( $actions )
		{
			// Remove edit action.
			unset( $actions['edit'] );

			return $actions;
		}

		/**
		 * Register columns for in stock alert subscription screen.
		 *
		 * @param array $columns Current columns.
		 *
		 * @return  array
		 */
		public static function register_columns( $columns )
		{
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __( 'Name', 'ovic-toolkit' ),
				'image'     => __( 'Image', 'ovic-toolkit' ),
				'num_pins'  => __( 'Number of Pins', 'ovic-toolkit' ),
				'shortcode' => __( 'Shortcode', 'ovic-toolkit' ),
				'date'      => __( 'Time', 'ovic-toolkit' ),
			);

			return $columns;
		}

		/**
		 * Display columns for in stock alert subscription screen.
		 *
		 * @param array $column Column to display content for.
		 * @param int   $post_id Post ID to display content for.
		 *
		 */
		public static function display_columns( $column, $post_id )
		{
			switch ( $column ) {
				case 'image' :
					// Get current image.
					$attachment_id = get_post_meta( $post_id, 'ovic_mapper_image', true );
					if ( $attachment_id ) {
						// Print image source.
						echo wp_get_attachment_image( $attachment_id, array( 70, 70 ) );
					} else {
						_e( 'No image', 'ovic-toolkit' );
					}
					break;
				case 'num_pins' :
					// Get all pins.
					$pins = get_post_meta( $post_id, 'ovic_mapper_pins', true );
					echo $pins ? count( $pins ) : 0;
					break;
				case 'shortcode' :
					?>
                    <span>[ovic_mapper id="<?php echo absint( $post_id ); ?>"]</span>
					<?php
					break;
			}
		}

		/**
		 * Enqueue assets for custom add/edit item form.
		 *
		 * @return  string
		 */
		public static function enqueue_assets()
		{
			// Check if WR Mapper page is requested.
			global $pagenow, $post_type;
			wp_dequeue_script( 'select2' );
			if ( in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
				if ( 'ovic_mapper' == $post_type ) {
					// Enqueue library to support copy to clipboard.
					wp_enqueue_script( 'clipboard', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/clipboard/clipboard.min.js' );
					if ( 'edit.php' == $pagenow ) {
						// Register action to print inline initialization script.
						add_action( 'admin_print_footer_scripts', array( __CLASS__, 'print_footer_scripts' ) );
					} else if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
						// Enqueue media.
						wp_enqueue_media();
						// Enqueue Select2.
						wp_enqueue_style( 'select2', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/select2/select2.min.css' );
						wp_register_script( 'wr_select2', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/select2/select2.min.js', array(), false, true );
						wp_enqueue_script( 'wr_select2' );
						// Enqueue custom color picker library.
						wp_enqueue_style( 'cs-wp-color-picker', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/wp-color-picker/wp-color-picker.min.css', array( 'wp-color-picker' ) );
						wp_enqueue_script( 'cs-wp-color-picker', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/wp-color-picker/wp-color-picker.min.js', array( 'wp-color-picker' ), false, true );
						// Awesome
						wp_enqueue_style( 'font-awesome-backend', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/3rd-party/font-awesome/css/font-awesome.min.css', array(), '2.4' );
						// Enqueue assets for custom add/edit item form.
						wp_enqueue_style( 'ovic-toolkit', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/css/backend.css' );
						wp_enqueue_script( 'ovic-toolkit', OVIC_TOOLKIT_PLUGIN_URL . '/includes/extends/mapper/assets/js/backend.js', array(), false, true );
						wp_localize_script( 'ovic-toolkit', 'ovic_mapper',
							array(
								'product_selector' => array(
									'url'      => admin_url( 'admin-ajax.php?action=woocommerce_json_search_products' ),
									'security' => wp_create_nonce( 'search-products' ),
								),
								'text'             => array(
									'img_selector_btn_label'   => __( 'Select', 'ovic-toolkit' ),
									'img_selector_modal_title' => __( 'Select or upload an image', 'ovic-toolkit' ),
									'ask_for_saving_changes'   => __( 'Your changes on this page are not saved!', 'ovic-toolkit' ),
									'confirm_removing_pin'     => __( 'Are you sure you want to remove this pin?', 'ovic-toolkit' ),
									'please_input_a_title'     => __( 'Please input a title for this pin', 'ovic-toolkit' ),
								),
							)
						);
					}
				}
			}
		}

		/**
		 * Method to print inline initialization script for items list screen.
		 *
		 * @return  void
		 */
		public static function print_footer_scripts()
		{
			?>
            <script type="text/javascript">
                jQuery(function ($) {
                    // Init action to copy shortcode to clipboard.
                    $('[data-clipboard-target]').each(function () {
                        var clipboard = new Clipboard('#' + $(this).attr('id'));

                        $(this).data('original-text', $(this).text());

                        clipboard.on('success', $.proxy(function (e) {
                            e.clearSelection();

                            // Swap button status.
                            $(this).text($(this).attr('data-success-text')).attr('disabled', 'disabled');

                            // Restore button after 5 seconds.
                            setTimeout($.proxy(function () {
                                $(this).text($(this).data('original-text')).removeAttr('disabled');
                            }, this), 5000);
                        }, this));

                        clipboard.on('error', $.proxy(function (e) {
                            // Swap button status.
                            $(this).text($(this).attr('data-error-text')).attr('disabled', 'disabled');

                            // Restore button after 5 seconds.
                            setTimeout($.proxy(function () {
                                $(this).text($(this).data('original-text')).removeAttr('disabled');
                            }, this), 5000);
                        }, this));
                    });
                });
            </script>
			<?php
		}

		/**
		 * Hide default add/edit item form.
		 *
		 * @return  void
		 */
		public static function hide_default_form()
		{
			?>
            <style type="text/css">
                #screen-meta, #screen-meta-links, #submitdiv, #pageparentdiv > .wrap {
                    display: none;
                }
            </style>
			<?php
		}

		/**
		 * Load custom add/edit item form.
		 *
		 * @return  void
		 */
		public static function load_edit_form()
		{
			global $pagenow, $post_type;
			if ( in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
				if ( 'ovic_mapper' == $post_type ) {
					// Load template file.
					include_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/extends/mapper/templates/admin/form.php';
				}
			}
		}

		/**
		 * Save custom post type extra data.
		 *
		 * @param int $id Current post ID.
		 *
		 * @return  $id
		 */
		public static function save_post( $id )
		{
			if ( isset( $_POST['ovic_mapper_image'] ) ) {
				update_post_meta( $id, 'ovic_mapper_image', absint( $_POST['ovic_mapper_image'] ) );
			}
			if ( isset( $_POST['ovic_mapper_settings'] ) && is_array( $_POST['ovic_mapper_settings'] ) ) {
				// Sanitize input data.
				$ovic_mapper_settings = array();
				foreach ( $_POST['ovic_mapper_settings'] as $key => $value ) {
					$ovic_mapper_settings[$key] = sanitize_text_field( $value );
				}
				update_post_meta( $id, 'ovic_mapper_settings', $ovic_mapper_settings );
			}
			if ( isset( $_POST['ovic_mapper_pins'] ) && is_array( $_POST['ovic_mapper_pins'] ) ) {
				$ovic_mapper_pins = array();
				foreach ( $_POST['ovic_mapper_pins'] as $k => $pin ) {
					// Sanitize input data.
					foreach ( $pin as $key => $value ) {
						if ( 'settings' == $key ) {
							foreach ( $value as $settings_key => $settings_value ) {
								if ( 'text' == $settings_key ) {
									$ovic_mapper_pins[$k][$key][$settings_key] = esc_sql(
										str_replace(
											array( "\r\n", "\r", "\n", '\\' ),
											array( '<br>', '<br>', '<br>', '' ),
											$settings_value
										)
									);
								} else {
									$ovic_mapper_pins[$k][$key][$settings_key] = sanitize_text_field( $settings_value );
								}
								if ( 'id' == $settings_key && empty( $settings_value ) ) {
									$ovic_mapper_pins[$k][$key][$settings_key] = wp_generate_password( 5, false, false );
								}
							}
						} else {
							$ovic_mapper_pins[$k][$key] = sanitize_text_field( $value );
						}
					}
				}
				update_post_meta( $id, 'ovic_mapper_pins', $ovic_mapper_pins );
			} else {
				delete_post_meta( $id, 'ovic_mapper_pins' );
			}
			// Publish post if needed.
			if ( !defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE ) {
				$post = get_post( $id );
				if ( __( 'Auto Draft' ) != $post->post_title && 'publish' != $post->post_status ) {
					wp_publish_post( $post );
				}
			}
			// Image Tesst
			if ( !isset( $_POST['mapperper_image_fields'] ) || !wp_verify_nonce( $_POST['mapperper_image_fields'], basename( __FILE__ ) ) ) {
				return $id;
			}
			// Check Autosave
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
				return $id;
			}
			// Don't save if only a revision
			if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
				return $id;
			}
			// Check permissions
			if ( !current_user_can( 'edit_post', $post->ID ) ) {
				return $id;
			}
			$meta['pin_style_select'] = ( isset( $_POST['pin_style_select'] ) ? esc_textarea( $_POST['pin_style_select'] ) : '' );
			foreach ( $meta as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}

		/**
		 * Method to alter results of WooCommerce's product search function.
		 *
		 * @param array $found_products Current search results.
		 *
		 * @return  array
		 */
		public static function search_products( $found_products )
		{
			// Check if term is a number.
			$id = ( string )wc_clean( stripslashes( $_GET['term'] ) );
			if ( preg_match( '/^\d+$/', $id ) ) {
				// Get product.
				$product        = wc_get_product( ( int )$id );
				$found_products = array(
					$id => rawurldecode( str_replace( '&ndash;', ' - ', $product->get_formatted_name() ) ),
				);
			}

			return $found_products;
		}
	}
}
