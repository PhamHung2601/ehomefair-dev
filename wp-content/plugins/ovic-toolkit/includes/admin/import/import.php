<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}
if ( !class_exists( 'OVIC_IMPORTER' ) && !class_exists( 'Ovic_Import_Demo' ) ) {
	class OVIC_IMPORTER
	{
		public $ajax_optionid;
		public $ajax_rtl_content;
		public $ajax_menu_import;
		public $ajax_attachments      = false;
		public $ajax_options_name     = array();
		public $ajax_main_content;
		public $ajax_options_posttype = array();
		public $data_demos            = array();
		public $content_path;
		public $widget_path;
		public $revslider_path;
		public $woo_pages;
		public $woo_catalog;
		public $woo_single;
		public $woo_thumbnail;
		public $item_import;
		public $woo_ratio;

		public function __construct()
		{
			$registed_menu = array(
				'primary' => esc_html__( 'Primary Menu', 'ovic-toolkit' ),
			);
			$menu_location = array(
				'primary' => 'Primary Menu',
			);
			$data_filter   = array(
				'data_demos'  => array(
					array(
						'name'             => esc_html__( 'Demo 01', 'ovic-toolkit' ),
						'slug'             => 'home-01',
						'menus'            => $registed_menu,
						'homepage'         => 'Home 01',
						'blogpage'         => 'Blog',
						'preview'          => get_theme_file_uri( 'screenshot.png' ),
						'demo_link'        => 'http://kutethemes.com/',
						'menu_locations'   => $menu_location,
						'theme_option'     => get_template_directory() . '/import/theme-options.txt',
						'setting_option'   => get_template_directory() . '/import/setting-options.txt',
						'content_path'     => get_template_directory() . '/import/content.xml',
						'content_path_rtl' => get_template_directory() . '/import/content-rtl.xml',
						'widget_path'      => get_template_directory() . '/import/widgets.wie',
						'revslider_path'   => get_template_directory() . '/import/revsliders/',
						'mega_menu'        => array(
							array(
								'name'  => 'Main Menu', // Name Of Menu
								'metas' => array(
									'_ovic_megamenu_enabled' => 1, // 1 or 0
									'_megamenu_layout'       => 'horizontal' // horizontal or vertical
								),
							),
						),
					),
				),
				'item_import' => array(
					'kt_import_full_content'    => 'Import full content',
					'kt_import_page_content'    => 'Import Page',
					'kt_import_theme_options'   => 'Import Theme Options',
					'kt_import_setting_options' => 'Import Setting Options',
					'kt_import_post_content'    => 'Import Post',
					'kt_import_product_content' => 'Import Product',
					'kt_import_menu'            => 'Import Menu',
					'kt_import_widget'          => 'Import Widget',
					'kt_import_revslider'       => 'Import Revslider',
					'kt_import_attachments'     => 'Import Attachments',
				),
				'woo_pages'   => array(
					'woocommerce_shop_page_id'      => 'Shop',
					'woocommerce_cart_page_id'      => 'Cart',
					'woocommerce_checkout_page_id'  => 'Checkout',
					'woocommerce_myaccount_page_id' => 'My Account',
				),
				'woo_ratio'   => '4:3',
				'woo_catalog' => 300,
				'woo_single'  => 600,
			);
			$import_data   = apply_filters( 'ovic_data_import', $data_filter );
			// SET DATA DEMOS
			$this->data_demos  = isset( $import_data['data_demos'] ) ? $import_data['data_demos'] : array();
			$this->item_import = isset( $import_data['item_import'] ) ? $import_data['item_import'] : array();
			$this->woo_pages   = isset( $import_data['woo_pages'] ) ? $import_data['woo_pages'] : array();
			$this->woo_catalog = isset( $import_data['woo_catalog'] ) ? $import_data['woo_catalog'] : 300;
			$this->woo_single  = isset( $import_data['woo_single'] ) ? $import_data['woo_single'] : 600;
			$this->woo_ratio   = isset( $import_data['woo_ratio'] ) ? $import_data['woo_ratio'] : '4:3';
			// JS and css
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
			add_action( 'importer_page_content', array( $this, 'importer_page_content' ) );
			/* Register ajax action */
			add_action( 'wp_ajax_kt_import_menu', array( $this, 'import_menu' ) );
			add_action( 'wp_ajax_kt_import_widget', array( $this, 'import_widget' ) );
			add_action( 'wp_ajax_kt_import_config', array( $this, 'import_config' ) );
			add_action( 'wp_ajax_kt_import_revslider', array( $this, 'import_revslider' ) );
			add_action( 'wp_ajax_kt_import_full_content', array( $this, 'import_full_content' ) );
			add_action( 'wp_ajax_kt_import_post_content', array( $this, 'import_post_content' ) );
			add_action( 'wp_ajax_kt_import_page_content', array( $this, 'import_page_content' ) );
			add_action( 'wp_ajax_kt_import_product_content', array( $this, 'import_product_content' ) );
			add_action( 'wp_ajax_kt_import_single_page_content', array( $this, 'import_single_page_content' ) );
			add_action( 'wp_ajax_kt_import_attachments', array( $this, 'import_attachments' ) );
			add_action( 'wp_ajax_kt_import_theme_options', array( $this, 'import_theme_options' ) );
			add_action( 'wp_ajax_kt_import_setting_options', array( $this, 'import_setting_options' ) );
			$this->define_constants();
		}

		/**
		 * Define  Constants.
		 */
		public function define_constants()
		{
			$this->define( 'OVIC_IMPORTER_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'OVIC_IMPORTER_URI', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		public function define( $name, $value )
		{
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}

		public function register_scripts( $hook_suffix )
		{
			if ( $hook_suffix == 'toplevel_page_ovic-dashboard' ) {
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'kt-importer-style', OVIC_IMPORTER_URI . '/assets/circle.css' );
				wp_enqueue_style( 'kt-importer-circle', OVIC_IMPORTER_URI . '/assets/import.css' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'kt-importer-script', OVIC_IMPORTER_URI . '/assets/import.js', array( 'jquery' ), false );
			}
		}

		public function importer_page_content()
		{
			$theme_name = wp_get_theme()->get( 'Name' );
			?>
            <div class="kt-importer-wrapper">
                <div class="progress_test" style="height: 5px; background-color: red; width: 0;"></div>
                <h1 class="heading"><?php echo ucfirst( esc_html( $theme_name ) ); ?> - Install Demo Content</h1>
                <div class="note">
                    <h3>Please read before importing:</h3>
                    <p>This importer will help you build your site look like our demo. Importing data is recommended
                        on fresh install.</p>
                    <p>Please ensure you have already installed and
                        activated Ovic Toolkit, WooCommerce, Visual
                        Composer and Revolution Slider plugins.</p>
                    <p>Please note that importing data only builds a frame for your website. <strong>It will
                            import all demo contents.</strong></p>
                    <p>It can take a few minutes to complete. <strong>Please don't close your browser while
                            importing.</strong></p>
                    <h3>Select the options below which you want to import:</h3>
                </div>
				<?php if ( !empty( $this->data_demos ) ) : ?>
                    <div class="options theme-browser">
						<?php foreach ( $this->data_demos as $key => $data ): ?>
                            <div id="option-<?php echo $key; ?>" class="option">
                                <div class="inner">
                                    <div class="preview">
                                        <img src="<?php echo esc_url( $data['preview'] ); ?>">
                                    </div>
                                    <span class="more-details">HAVE IMPORTED</span>
                                    <h3 class="demo-name theme-name"><?php echo $data['name']; ?></h3>
                                    <div class="group-control theme-actions">
                                        <div class="control-inner">
                                            <button data-id="<?php echo $key; ?>"
                                                    data-optionid="<?php echo $key; ?>"
                                                    class="button button-primary open-import">Install
                                            </button>
                                            <a target="_blank" class="button"
                                               href="<?php echo $data['demo_link']; ?>">View demo</a>
                                        </div>
                                    </div>
                                </div>
                                <div id="content-demo-<?php echo $key; ?>" class="option" style="display: none;">
                                    <div class="inner" data-option="<?php echo $key; ?>">
                                        <div class="plugin-check">
                                            <strong>The Following Required To Import Content !</strong>
                                            <p>
                                                <span>PHP Version > 5.6, max_execution_time 180</span>
                                                <span>( * )</span>
                                            </p>
                                            <p>
                                                <span>Your Host allow download file from other site and zip file</span>
                                                <span>( * )</span>
                                            </p>
                                            <p>
                                                <span>memory_limit 128M, post_max_size 32M, upload_max_filesize 32M</span>
                                                <span>( * )</span>
                                            </p>
                                        </div>
                                        <div class="block-title">
                                            <h3 class="demo-name"><?php echo $data['name']; ?></h3>
                                            <a target="_blank" class="more"
                                               href="<?php echo $data['demo_link']; ?>">View demo</a>
											<?php if ( isset( $data['content_path_rtl'] ) ): ?>
                                                <label class="import-rtl-content">
                                                    <input type="checkbox"
                                                           name="import_rtl_content">
                                                    RTL Content
                                                </label>
											<?php endif; ?>
                                        </div>
                                        <div class="kt-control">
                                            <h4 class="import-title">Import content</h4>
                                            <div class="control-inner">
                                                <div class="group-control">
													<?php foreach ( $this->item_import as $keys => $item ) : ?>
                                                        <label for="<?php echo esc_attr( $keys ); ?>-<?php echo $key; ?>">
                                                            <input id="<?php echo esc_attr( $keys ); ?>-<?php echo $key; ?>"
                                                                   type="checkbox"
                                                                   class="<?php echo esc_attr( $keys ); ?>"
                                                                   value="<?php echo $key; ?>">
															<?php echo esc_html( $item ); ?>
                                                        </label>
													<?php endforeach; ?>
                                                    <button data-id="<?php echo $key; ?>"
                                                            data-slug="<?php echo $data['slug']; ?>"
                                                            data-optionid="<?php echo $key; ?>"
                                                            class="button button-primary kt-button-import">Install
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress-wapper">
                                            <div class="progress-item">
												<?php foreach ( $this->item_import as $keys => $item ) : ?>
                                                    <div class="meter item <?php echo esc_attr( $keys ); ?>">
														<?php echo esc_html( $item ); ?>
                                                        <div class="checkmark">
                                                            <div class="checkmark_stem"></div>
                                                            <div class="checkmark_kick"></div>
                                                        </div>
                                                        <span style="width: 100%"></span>
                                                    </div>
												<?php endforeach; ?>
                                                <div class="meter item kt_import_single_page_content">
                                                    Import this page content
                                                    <div class="checkmark">
                                                        <div class="checkmark_stem"></div>
                                                        <div class="checkmark_kick"></div>
                                                    </div>
                                                    <span style="width: 100%"></span>
                                                </div>
                                                <div class="meter item kt_import_config">
                                                    Import Config
                                                    <div class="checkmark">
                                                        <div class="checkmark_stem"></div>
                                                        <div class="checkmark_kick"></div>
                                                    </div>
                                                    <span style="width: 100%"></span>
                                                </div>
                                            </div>
                                            <div class="progress-circle">
                                                <div class="c100 p0 dark green" data-percent="1">
                                                    <span class="percent">0%</span>
                                                    <div class="slice">
                                                        <div class="bar"></div>
                                                        <div class="fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php else: ?>
                    <p>No data import</p>
				<?php endif; ?>
            </div>
			<?php
		}

		/* DOWNLOAD FILE */
		public function download( $url = "", $file_name = "" )
		{
			$filepath = "";
			if ( $url != "" ) {
				$upload_dir = wp_upload_dir();
				$ch         = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				$data = curl_exec( $ch );
				curl_close( $ch );
				$destination = $upload_dir['path'] . "/" . $file_name;
				$file        = fopen( $destination, "w+" );
				fputs( $file, $data );
				fclose( $file );
				$filepath = $destination;
			}

			return $filepath;
		}

		/* Include Importer Classes */
		public function include_importer_classes()
		{
			if ( !class_exists( 'WP_Importer' ) ) {
				include ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			}
			if ( !class_exists( 'KT_WP_Import' ) ) {
				if ( file_exists( dirname( __FILE__ ) . '/includes/wordpress-importer.php' ) ) {
					include_once dirname( __FILE__ ) . '/includes/wordpress-importer.php';
				}
			}
		}

		public function no_resize_image( $sizes )
		{
			return array();
		}

		public function before_content_import()
		{
			if ( class_exists( 'WooCommerce' ) ) {
				global $wpdb;
				if ( current_user_can( 'administrator' ) ) {
					$attributes = array(
						array(
							'attribute_label'   => 'Color',
							'attribute_name'    => 'color',
							'attribute_type'    => 'box_style', // text, box_style, select
							'attribute_orderby' => 'menu_order',
							'attribute_public'  => '0',
						),
						array(
							'attribute_label'   => 'Size',
							'attribute_name'    => 'size',
							'attribute_type'    => 'select', // text, box_style, select
							'attribute_orderby' => 'menu_order',
							'attribute_public'  => '0',
						),
					);
					$attributes = apply_filters( 'ovic_import_wooCommerce_attributes', $attributes );
					foreach ( $attributes as $attribute ):
						if ( empty( $attribute['attribute_name'] ) || empty( $attribute['attribute_label'] ) ) {
							return new WP_Error( 'error', __( 'Please, provide an attribute name and slug.', 'ovic-toolkit' ) );
						} elseif ( ( $valid_attribute_name = $this->wc_valid_attribute_name( $attribute['attribute_name'] ) ) && is_wp_error( $valid_attribute_name ) ) {
							return $valid_attribute_name;
						} elseif ( taxonomy_exists( wc_attribute_taxonomy_name( $attribute['attribute_name'] ) ) ) {
							return new WP_Error( 'error', sprintf( __( 'Slug "%s" is already in use. Change it, please.', 'ovic-toolkit' ), sanitize_title( $attribute['attribute_name'] ) ) );
						}
						$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );
						do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
						$attribute_name = wc_sanitize_taxonomy_name( 'pa_' . $attribute['attribute_name'] );
						if ( !taxonomy_exists( $attribute_name ) ) {
							$args = array(
								'hierarchical' => true,
								'show_ui'      => false,
								'query_var'    => true,
								'rewrite'      => false,
							);
							register_taxonomy( $attribute_name, array( 'product' ), $args );
						}
						flush_rewrite_rules();
						delete_transient( 'wc_attribute_taxonomies' );
					endforeach;
				}
			}
			do_action( 'ovic_before_content_import' );
		}

		public function wc_valid_attribute_name( $attribute_name )
		{
			if ( !class_exists( 'WooCommerce' ) ) {
				return false;
			}
			if ( strlen( $attribute_name ) >= 28 ) {
				return new WP_Error( 'error', sprintf( __( 'Slug "%s" is too long (28 characters max). Shorten it, please.', 'ovic-toolkit' ), sanitize_title( $attribute_name ) ) );
			} elseif ( wc_check_if_attribute_name_is_reserved( $attribute_name ) ) {
				return new WP_Error( 'error', sprintf( __( 'Slug "%s" is not allowed because it is a reserved term. Change it, please.', 'ovic-toolkit' ), sanitize_title( $attribute_name ) ) );
			}

			return true;
		}

		public function import_full_content()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'page' );
			$this->ajax_main_content     = 1;
			$this->ajax_attachments      = true;
			$this->import_content();
			wp_die();
		}

		public function import_single_page_content()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'page' );
			$this->ajax_options_name     = isset( $_POST['slug_home'] ) ? $_POST['slug_home'] : array();
			$this->import_content();
			wp_die();
		}

		public function import_page_content()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'page' );
			$this->import_content();
			wp_die();
		}

		public function import_post_content()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'post' );
			$this->import_content();
			wp_die();
		}

		public function import_attachments()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'attachment' );
			$this->ajax_attachments      = true;
			$this->import_content();
			wp_die();
		}

		public function import_product_content()
		{
			$this->ajax_optionid         = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content      = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_options_posttype = array( 'product' );
			$this->import_content();
			wp_die();
		}

		public function import_menu()
		{
			global $wpdb;
			//			$wpdb->query( "DELETE FROM `" . $wpdb->prefix . "posts` WHERE `post_type`='nav_menu_item'" );
			$this->ajax_optionid    = isset( $_POST['optionid'] ) ? $_POST['optionid'] : '';
			$this->ajax_rtl_content = isset( $_POST['rtl_content'] ) ? (int)$_POST['rtl_content'] : 0;
			$this->ajax_menu_import = 1;
			$this->import_content();
			wp_die();
		}

		public function import_theme_options()
		{
			$optionid = isset( $_POST['optionid'] ) ? $_POST['optionid'] : "";
			if ( $optionid != "" ) {
				$demo = $this->data_demos[$optionid];
				if ( !is_array( $demo ) ) {
					return;
				}
			}
			if ( isset( $demo['theme_option'] ) && $demo['theme_option'] != "" ) {
				$data = file_get_contents( $demo['theme_option'] );
				update_option( OVIC_CUSTOMIZE, ovic_decode_string( $data ) );
			}
			wp_die();
		}

		public function import_setting_options()
		{
			$optionid = isset( $_POST['optionid'] ) ? $_POST['optionid'] : "";
			if ( $optionid != "" ) {
				$demo = $this->data_demos[$optionid];
				if ( !is_array( $demo ) ) {
					return;
				}
			}
			if ( isset( $demo['setting_option'] ) && $demo['setting_option'] != "" ) {
				$data = file_get_contents( $demo['setting_option'] );
				Ovic_Settings_Backup::import_data( $data );
			}
			wp_die();
		}

		public function import_content()
		{
			set_time_limit( 0 );
			if ( !defined( 'WP_LOAD_IMPORTERS' ) ) {
				define( 'WP_LOAD_IMPORTERS', true );
			}
			$ajax_optionid         = $this->ajax_optionid;
			$ajax_rtl_content      = $this->ajax_rtl_content;
			$ajax_menu_import      = $this->ajax_menu_import;
			$ajax_options_posttype = $this->ajax_options_posttype;
			$ajax_options_name     = $this->ajax_options_name;
			$ajax_main_content     = $this->ajax_main_content;
			$ajax_attachments      = $this->ajax_attachments;
			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'no_resize_image' ) );
			if ( $ajax_optionid != '' ) {
				$ajax_path = $this->data_demos[$ajax_optionid]['content_path'];
				$this->before_content_import();
				$this->include_importer_classes();
				$importer                        = new KT_WP_Import();
				$importer->fetch_attachments     = $ajax_attachments;
				$importer->ajax_options_posttype = $ajax_options_posttype;
				$importer->ajax_options_name     = $ajax_options_name;
				$importer->ajax_main_content     = $ajax_main_content;
				$importer->menu_import           = $ajax_menu_import;
				if ( $ajax_rtl_content == 1 )
					$ajax_path = $this->data_demos[$ajax_optionid]['content_path_rtl'];
				$importer->import( $ajax_path );
				//				$this->update_menu_custom_link();
				echo 'Successful Import Demo Content';
			}
			do_action( 'ovic_after_content_import' );
		}

		public function update_menu_custom_link()
		{
			$menus    = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			$home_url = get_home_url();
			if ( !empty( $menus ) ) {
				foreach ( $menus as $menu ) {
					$items = wp_get_nav_menu_items( $menu->term_id );
					if ( !empty( $items ) ) {
						foreach ( $items as $item ) {
							$metas           = get_post_meta( $item->ID );
							$_menu_item_type = get_post_meta( $item->ID, '_menu_item_type', true );
							$_menu_item_url  = get_post_meta( $item->ID, '_menu_item_url', true );
							if ( $_menu_item_type == 'custom' ) {
								$_menu_item_url = str_replace( 'https://link', $home_url, $_menu_item_url );
								$_menu_item_url = str_replace( 'http://link', $home_url, $_menu_item_url );
								update_post_meta( $item->ID, '_menu_item_url', $_menu_item_url );
							}
						}
					}
				}
			}
		}

		/* import Sidebar Content */
		public function import_widget()
		{
			$optionid = isset( $_POST['optionid'] ) ? $_POST['optionid'] : "";
			if ( $optionid == "" ) {
				return;
			}
			$url  = $this->data_demos[$optionid]['widget_path'];
			$data = file_get_contents( $url );
			$data = json_decode( $data );
			global $wp_registered_sidebars;
			if ( empty( $data ) || !is_object( $data ) ) {
				wp_die();
			}
			update_option( 'sidebars_widgets', array( false ) );
			do_action( 'wie_before_import' );
			$data              = apply_filters( 'wie_import_data', $data );
			$available_widgets = $this->available_widgets();
			$widget_instances  = array();
			foreach ( $available_widgets as $widget_data ) {
				$widget_instances[$widget_data['id_base']] = get_option( 'widget_' . $widget_data['id_base'] );
			}
			$results = array();
			foreach ( $data as $sidebar_id => $widgets ) {
				if ( 'wp_inactive_widgets' == $sidebar_id ) {
					continue;
				}
				if ( isset( $wp_registered_sidebars[$sidebar_id] ) ) {
					$sidebar_available    = true;
					$use_sidebar_id       = $sidebar_id;
					$sidebar_message_type = 'success';
					$sidebar_message      = '';
				} else {
					$sidebar_available    = false;
					$use_sidebar_id       = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
					$sidebar_message_type = 'error';
					$sidebar_message      = __( 'Sidebar does not exist in theme (using Inactive)', 'ovic-toolkit' );
				}
				$results[$sidebar_id]['name']         = !empty( $wp_registered_sidebars[$sidebar_id]['name'] ) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
				$results[$sidebar_id]['message_type'] = $sidebar_message_type;
				$results[$sidebar_id]['message']      = $sidebar_message;
				$results[$sidebar_id]['widgets']      = array();
				foreach ( $widgets as $widget_instance_id => $widget ) {
					$fail               = false;
					$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
					$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );
					if ( !$fail && !isset( $available_widgets[$id_base] ) ) {
						$fail                = true;
						$widget_message_type = 'error';
						$widget_message      = __( 'Site does not support widget', 'ovic-toolkit' );
					}
					$widget = apply_filters( 'wie_widget_settings', $widget );
					$widget = json_decode( json_encode( $widget ), true );
					$widget = apply_filters( 'wie_widget_settings_array', $widget );
					if ( !$fail && isset( $widget_instances[$id_base] ) ) {
						$sidebars_widgets        = get_option( 'sidebars_widgets' );
						$sidebar_widgets         = isset( $sidebars_widgets[$use_sidebar_id] ) ? $sidebars_widgets[$use_sidebar_id] : array();
						$single_widget_instances = !empty( $widget_instances[$id_base] ) ? $widget_instances[$id_base] : array();
						foreach ( $single_widget_instances as $check_id => $check_widget ) {
							if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array)$widget == $check_widget ) {
								$fail                = true;
								$widget_message_type = 'warning';
								$widget_message      = __( 'Widget already exists', 'ovic-toolkit' );
								break;
							}
						}
					}
					if ( !$fail ) {
						$single_widget_instances   = get_option( 'widget_' . $id_base );
						$single_widget_instances   = !empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 );
						$single_widget_instances[] = $widget;
						end( $single_widget_instances );
						$new_instance_id_number = key( $single_widget_instances );
						if ( '0' === strval( $new_instance_id_number ) ) {
							$new_instance_id_number                           = 1;
							$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
							unset( $single_widget_instances[0] );
						}
						if ( isset( $single_widget_instances['_multiwidget'] ) ) {
							$multiwidget = $single_widget_instances['_multiwidget'];
							unset( $single_widget_instances['_multiwidget'] );
							$single_widget_instances['_multiwidget'] = $multiwidget;
						}
						update_option( 'widget_' . $id_base, $single_widget_instances );
						$sidebars_widgets                    = get_option( 'sidebars_widgets' );
						$new_instance_id                     = $id_base . '-' . $new_instance_id_number;
						$sidebars_widgets[$use_sidebar_id][] = $new_instance_id;
						update_option( 'sidebars_widgets', $sidebars_widgets );
						$after_widget_import = array(
							'sidebar'           => $use_sidebar_id,
							'sidebar_old'       => $sidebar_id,
							'widget'            => $widget,
							'widget_type'       => $id_base,
							'widget_id'         => $new_instance_id,
							'widget_id_old'     => $widget_instance_id,
							'widget_id_num'     => $new_instance_id_number,
							'widget_id_num_old' => $instance_id_number,
						);
						do_action( 'wie_after_widget_import', $after_widget_import );
						if ( $sidebar_available ) {
							$widget_message_type = 'success';
							$widget_message      = __( 'Imported', 'ovic-toolkit' );
						} else {
							$widget_message_type = 'warning';
							$widget_message      = __( 'Imported to Inactive', 'ovic-toolkit' );
						}
					}
					$results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset( $available_widgets[$id_base]['name'] ) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
					$results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = !empty( $widget['title'] ) ? $widget['title'] : __( 'No Title', 'ovic-toolkit' ); // show "No Title" if widget instance is untitled
					$results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
					$results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
				}
			}
			do_action( 'wie_after_import' );
			wp_die();
		}

		public function available_widgets()
		{
			global $wp_registered_widget_controls;
			$widget_controls   = $wp_registered_widget_controls;
			$available_widgets = array();
			foreach ( $widget_controls as $widget ) {
				if ( !empty( $widget['id_base'] ) && !isset( $available_widgets[$widget['id_base']] ) ) { // no dupes
					$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
					$available_widgets[$widget['id_base']]['name']    = $widget['name'];
				}
			}

			return apply_filters( 'wie_available_widgets', $available_widgets );
		}

		/* Import Revolution Slider */
		public function import_revslider()
		{
			$optionid = isset( $_POST['optionid'] ) ? $_POST['optionid'] : "";
			if ( $optionid == '' ) {
				return;
			}
			if ( class_exists( 'UniteFunctionsRev' ) && class_exists( 'ZipArchive' ) ) {
				global $wpdb;
				$updateAnim    = true;
				$updateStatic  = true;
				$rev_directory = $this->data_demos[$optionid]['revslider_path'];
				$rev_files     = array();
				$rev_db        = new RevSliderDB();
				foreach ( glob( $rev_directory . '*.zip' ) as $filename ) {
					$filename      = basename( $filename );
					$allow_import  = false;
					$arr_filename  = explode( '_', $filename );
					$slider_new_id = absint( $arr_filename[0] );
					if ( $slider_new_id > 0 ) {
						$response = $rev_db->fetch( RevSliderGlobals::$table_sliders, 'id=' + $slider_new_id );
						if ( empty( $response ) ) { /* not exists */
							$rev_files_ids[] = $slider_new_id;
							$allow_import    = true;
						}
					} else {
						$rev_files_ids[] = 0;
						$allow_import    = true;
					}
					if ( $allow_import ) {
						$rev_files[] = $rev_directory . $filename;
					}
				}
				foreach ( $rev_files as $index => $rev_file ) {
					$filepath  = $rev_file;
					$zip       = new ZipArchive;
					$importZip = $zip->open( $filepath, ZIPARCHIVE::CREATE );
					if ( $importZip === true ) {
						$slider_export     = $zip->getStream( 'slider_export.txt' );
						$custom_animations = $zip->getStream( 'custom_animations.txt' );
						$dynamic_captions  = $zip->getStream( 'dynamic-captions.css' );
						$static_captions   = $zip->getStream( 'static-captions.css' );
						$content           = '';
						$animations        = '';
						$dynamic           = '';
						$static            = '';
						while ( !feof( $slider_export ) ) $content .= fread( $slider_export, 1024 );
						if ( $custom_animations ) {
							while ( !feof( $custom_animations ) ) $animations .= fread( $custom_animations, 1024 );
						}
						if ( $dynamic_captions ) {
							while ( !feof( $dynamic_captions ) ) $dynamic .= fread( $dynamic_captions, 1024 );
						}
						if ( $static_captions ) {
							while ( !feof( $static_captions ) ) $static .= fread( $static_captions, 1024 );
						}
						fclose( $slider_export );
						if ( $custom_animations ) {
							fclose( $custom_animations );
						}
						if ( $dynamic_captions ) {
							fclose( $dynamic_captions );
						}
						if ( $static_captions ) {
							fclose( $static_captions );
						}
					} else {
						$content = @file_get_contents( $filepath );
					}
					if ( $importZip === true ) {
						$db         = new UniteDBRev();
						$animations = @unserialize( $animations );
						if ( !empty( $animations ) ) {
							foreach ( $animations as $key => $animation ) {
								$exist = $db->fetch( GlobalsRevSlider::$table_layer_anims, "handle = '" . $animation['handle'] . "'" );
								if ( !empty( $exist ) ) {
									if ( $updateAnim == 'true' ) {
										$arrUpdate           = array();
										$arrUpdate['params'] = stripslashes( json_encode( str_replace( "'", '"', $animation['params'] ) ) );
										$db->update( GlobalsRevSlider::$table_layer_anims, $arrUpdate, array( 'handle' => $animation['handle'] ) );
										$id = $exist['0']['id'];
									} else {
										$arrInsert           = array();
										$arrInsert["handle"] = 'copy_' . $animation['handle'];
										$arrInsert["params"] = stripslashes( json_encode( str_replace( "'", '"', $animation['params'] ) ) );
										$id                  = $db->insert( GlobalsRevSlider::$table_layer_anims, $arrInsert );
									}
								} else {
									$arrInsert           = array();
									$arrInsert["handle"] = $animation['handle'];
									$arrInsert["params"] = stripslashes( json_encode( str_replace( "'", '"', $animation['params'] ) ) );
									$id                  = $db->insert( GlobalsRevSlider::$table_layer_anims, $arrInsert );
								}
								$content = str_replace( array( 'customin-' . $animation['id'], 'customout-' . $animation['id'] ), array( 'customin-' . $id, 'customout-' . $id ), $content );
							}
						}
						if ( !empty( $static ) ) {
							if ( isset( $updateStatic ) && $updateStatic == 'true' ) {
								RevOperations::updateStaticCss( $static );
							} else {
								$static_cur = RevOperations::getStaticCss();
								$static     = $static_cur . "\n" . $static;
								RevOperations::updateStaticCss( $static );
							}
						}
						$dynamicCss = UniteCssParserRev::parseCssToArray( $dynamic );
						if ( is_array( $dynamicCss ) && $dynamicCss !== false && count( $dynamicCss ) > 0 ) {
							foreach ( $dynamicCss as $class => $styles ) {
								$class = trim( $class );
								if ( ( strpos( $class, ':hover' ) === false && strpos( $class, ':' ) !== false ) ||
									strpos( $class, " " ) !== false ||
									strpos( $class, ".tp-caption" ) === false ||
									( strpos( $class, "." ) === false || strpos( $class, "#" ) !== false ) ||
									strpos( $class, ">" ) !== false
								) {
									continue;
								}
								if ( strpos( $class, ':hover' ) !== false ) {
									$class                 = trim( str_replace( ':hover', '', $class ) );
									$arrInsert             = array();
									$arrInsert["hover"]    = json_encode( $styles );
									$arrInsert["settings"] = json_encode( array( 'hover' => 'true' ) );
								} else {
									$arrInsert           = array();
									$arrInsert["params"] = json_encode( $styles );
								}
								$result = $db->fetch( GlobalsRevSlider::$table_css, "handle = '" . $class . "'" );
								if ( !empty( $result ) ) {
									$db->update( GlobalsRevSlider::$table_css, $arrInsert, array( 'handle' => $class ) );
								} else {
									$arrInsert["handle"] = $class;
									$db->insert( GlobalsRevSlider::$table_css, $arrInsert );
								}
							}
						}
					}
					$content      = preg_replace_callback( '!s:(\d+):"(.*?)";!', array( 'RevSliderSlider', 'clear_error_in_string' ), $content ); //clear errors in string
					$arrSlider    = @unserialize( $content );
					$sliderParams = $arrSlider["params"];
					if ( isset( $sliderParams["background_image"] ) )
						$sliderParams["background_image"] = UniteFunctionsWPRev::getImageUrlFromPath( $sliderParams["background_image"] );
					$json_params         = json_encode( $sliderParams );
					$arrInsert           = array();
					$arrInsert["params"] = $json_params;
					$arrInsert["title"]  = UniteFunctionsRev::getVal( $sliderParams, "title", "Slider1" );
					$arrInsert["alias"]  = UniteFunctionsRev::getVal( $sliderParams, "alias", "slider1" );
					if ( $rev_files_ids[$index] != 0 ) {
						$arrInsert["id"] = $rev_files_ids[$index];
						$arrFormat       = array( '%s', '%s', '%s', '%d' );
					} else {
						$arrFormat = array( '%s', '%s', '%s' );
					}
					$sliderID = $wpdb->insert( GlobalsRevSlider::$table_sliders, $arrInsert, $arrFormat );
					$sliderID = $wpdb->insert_id;
					/* create all slides */
					$arrSlides       = $arrSlider["slides"];
					$alreadyImported = array();
					foreach ( $arrSlides as $slide ) {
						$params = $slide["params"];
						$layers = $slide["layers"];
						if ( isset( $params["image"] ) ) {
							if ( trim( $params["image"] ) !== '' ) {
								if ( $importZip === true ) {
									$image = $zip->getStream( 'images/' . $params["image"] );
									if ( !$image ) {
										echo $params["image"] . ' not found!<br>';
									} else {
										if ( !isset( $alreadyImported['zip://' . $filepath . "#" . 'images/' . $params["image"]] ) ) {
											$importImage = UniteFunctionsWPRev::import_media( 'zip://' . $filepath . "#" . 'images/' . $params["image"], $sliderParams["alias"] . '/' );
											if ( $importImage !== false ) {
												$alreadyImported['zip://' . $filepath . "#" . 'images/' . $params["image"]] = $importImage['path'];
												$params["image"]                                                            = $importImage['path'];
											}
										} else {
											$params["image"] = $alreadyImported['zip://' . $filepath . "#" . 'images/' . $params["image"]];
										}
									}
								}
							}
							$params["image"] = UniteFunctionsWPRev::getImageUrlFromPath( $params["image"] );
						}
						foreach ( $layers as $key => $layer ) {
							if ( isset( $layer["image_url"] ) ) {
								if ( trim( $layer["image_url"] ) !== '' ) {
									if ( $importZip === true ) {
										$image_url = $zip->getStream( 'images/' . $layer["image_url"] );
										if ( !$image_url ) {
											echo $layer["image_url"] . ' not found!<br>';
										} else {
											if ( !isset( $alreadyImported['zip://' . $filepath . "#" . 'images/' . $layer["image_url"]] ) ) {
												$importImage = UniteFunctionsWPRev::import_media( 'zip://' . $filepath . "#" . 'images/' . $layer["image_url"], $sliderParams["alias"] . '/' );
												if ( $importImage !== false ) {
													$alreadyImported['zip://' . $filepath . "#" . 'images/' . $layer["image_url"]] = $importImage['path'];
													$layer["image_url"]                                                            = $importImage['path'];
												}
											} else {
												$layer["image_url"] = $alreadyImported['zip://' . $filepath . "#" . 'images/' . $layer["image_url"]];
											}
										}
									}
								}
								$layer["image_url"] = UniteFunctionsWPRev::getImageUrlFromPath( $layer["image_url"] );
								$layers[$key]       = $layer;
							}
						}
						/* create new slide */
						$arrCreate                = array();
						$arrCreate["slider_id"]   = $sliderID;
						$arrCreate["slide_order"] = $slide["slide_order"];
						$arrCreate["layers"]      = json_encode( $layers );
						$arrCreate["params"]      = json_encode( $params );
						$wpdb->insert( GlobalsRevSlider::$table_slides, $arrCreate );
					}
				}
			}
			wp_die();
		}

		public function import_config()
		{
			$optionid = isset( $_POST['optionid'] ) ? $_POST['optionid'] : "";
			if ( $optionid != "" ) {
				$demo = $this->data_demos[$optionid];
				if ( !is_array( $demo ) ) {
					return;
				}
			}
			$this->woocommerce_settings();
			$this->menu_locations( $demo );
			$this->mega_menu( $demo );
			$this->update_options( $demo );
			wp_die();
		}

		public function mega_menu( $demo )
		{
			if ( isset( $demo['mega_menu'] ) && !empty( $demo['mega_menu'] ) ) {
				foreach ( $demo['mega_menu'] as $item ) {
					$menu = $menu = wp_get_nav_menu_object( $item['name'] );
					if ( !empty( $menu ) && !empty( $item['metas'] ) ) {
						foreach ( $item['metas'] as $key => $value ) {
							update_term_meta( $menu->term_id, $key, $value );
						}
					}
				}
			}
		}

		/* WooCommerce Settings */
		public function woocommerce_settings()
		{
			foreach ( $this->woo_pages as $woo_page_name => $woo_page_title ) {
				$woopage = get_page_by_title( $woo_page_title );
				if ( isset( $woopage->ID ) && $woopage->ID ) {
					update_option( $woo_page_name, $woopage->ID );
				}
			}
			if ( class_exists( 'YITH_Woocompare' ) ) {
				update_option( 'yith_woocompare_compare_button_in_products_list', 'yes' );
				update_option( 'yith_woocompare_is_button', 'link' );
			}
			if ( class_exists( 'WC_Admin_Notices' ) ) {
				WC_Admin_Notices::remove_notice( 'install' );
			}
			delete_transient( '_wc_activation_redirect' );
			// Image sizes
			$ratio = explode( ':', $this->woo_ratio );
			update_option( 'woocommerce_thumbnail_cropping', 'custom' );
			update_option( 'woocommerce_thumbnail_image_width', $this->woo_catalog );
			update_option( 'woocommerce_thumbnail_cropping_custom_width', $ratio[0] );
			update_option( 'woocommerce_thumbnail_cropping_custom_height', $ratio[1] );
			update_option( 'woocommerce_single_image_width', $this->woo_single );    // Single product image
			flush_rewrite_rules();
		}

		/* Menu Locations */
		public function menu_locations( $demo )
		{
			$menu_location = array();
			$locations     = get_theme_mod( 'nav_menu_locations' );
			$menus         = wp_get_nav_menus();
			if ( isset( $demo['menu_locations'] ) && is_array( $demo['menu_locations'] ) ) {
				if ( $menus ) {
					foreach ( $menus as $menu ) {
						foreach ( $demo['menu_locations'] as $key => $value ) {
							if ( $menu->name == $value ) {
								$menu_location[$key] = $menu->term_id;
							}
						}
					}
				}
				set_theme_mod( 'nav_menu_locations', $menu_location );
			} else if ( isset( $demo['menus'] ) && is_array( $demo['menus'] ) ) {
				$menu_location = $locations;
				set_theme_mod( 'nav_menu_locations', $menu_location );
			}
		}

		/* Update Options */
		public function update_options( $demo )
		{
			if ( isset( $demo['homepage'] ) && $demo['homepage'] != "" ) {
				// Home page
				$homepage = get_page_by_title( $demo['homepage'] );
				if ( isset( $homepage ) && $homepage->ID ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $homepage->ID );
				}
			}
			// Blog page
			if ( isset( $demo['blogpage'] ) && $demo['blogpage'] != "" ) {
				$post_page = get_page_by_title( $demo['blogpage'] );
				if ( isset( $post_page ) && $post_page->ID ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_for_posts', $post_page->ID );
				}
			}
		}
	}

	new OVIC_IMPORTER();
}