<?php
/**
 * CMB Tabbed Theme Options
 *
 * @author    Arushad Ahmed <@dash8x, contact@arushad.org>
 * @link      http://arushad.org/how-to-create-a-tabbed-options-page-for-your-wordpress-theme-using-cmb
 * @version   0.1.0
 */
if ( !class_exists( 'Ovic_Settings' ) ) {
	class Ovic_Settings
	{
		/**
		 * Default Option key
		 * @var string
		 */
		private       $key  = 'ovic_options';
		public static $test = '';
		/**
		 * Array of metaboxes/fields
		 * @var array
		 */
		protected $option_metabox = array();
		/**
		 * Options Page title
		 * @var string
		 */
		protected $title = '';
		/**
		 * Options Tab Pages
		 * @var array
		 */
		protected $options_pages = array();

		/**
		 * Constructor
		 * @since 0.1.0
		 */
		public function __construct()
		{
			$this->includes();
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
			// Set our title
			$this->title = __( 'Theme Options', 'ovic-toolkit' );
		}

		public function admin_menu()
		{
			if ( current_user_can( 'edit_theme_options' ) ) {
				add_submenu_page( 'ovic-dashboard', 'Settings', 'Settings', 'manage_options', 'ovic-settings', array( $this, 'options_page' ) );
			}
		}

		public function includes()
		{
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/CMB2/init.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/Extends/CMB2-Image_Select-Field-Type/image_select_metafield.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/Extends/cmb-field-select2/cmb-field-select2.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/Extends/cmb2-switch.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/ovic-metabox.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
			require_once OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/settings/backup.php';
		}

		/**
		 * Admin page markup. Mostly handled by CMB
		 * @since  0.1.0
		 */
		public function options_page()
		{
			$file_stylesheet = trailingslashit( get_template_directory() ) . 'style.css';
			$theme_info      = get_file_data( $file_stylesheet, array( 'market' => 'Market' ) );
			$market          = ( isset( $theme_info['market'] ) ) ? $theme_info['market'] : '';
			$default         = 'general_options';
			if ( $market == 'Envato' || $market == 'Templatemonster' ) {
				$default = 'extends_options';
			}
			$active_tab     = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : $default;
			$active_section = isset( $_GET['section'] ) ? $_GET['section'] : '';
			$option_tabs    = self::option_fields(); //get all option tabs
			$tab_forms      = array();
			$sections       = $this->get_sections( $active_tab );
			?>
            <div class="wrap ovic-wrap-options <?php echo $this->key; ?>">
                <h2><?php esc_html_e( 'Ovic Settings', 'ovic-toolkit' ); ?></h2>
                <!-- Options Page Nav Tabs -->
                <h2 class="nav-tab-wrapper">
					<?php foreach ( $option_tabs as $option_tab ) :
						$tab_url = add_query_arg(
							array(
								'settings-updated' => false,
								'tab'              => $option_tab['id'],
							)
						);
						// Remove the section from the tabs so we always end up at the main section
						$tab_url   = remove_query_arg( 'section', $tab_url );
						$active    = $active_tab == $option_tab['id'] ? ' nav-tab-active' : '';
						$tab_slug  = $option_tab['id'];
						$nav_class = 'nav-tab';
						if ( $tab_slug == $active_tab ) {
							$nav_class .= ' nav-tab-active'; //add active class to current tab
							if ( !empty( $sections ) && count( $sections ) > 0 ) {
								foreach ( $sections as $section ) {
									if ( $active_section == '' ) {
										$tab_forms[]    = $section;
										$active_section = $section['id'];
										break;
									} else {
										if ( $active_section == $section['id'] ) {
											$tab_forms[] = $section;
											break;
										}
									}
								}
							} else {
								$tab_forms[] = $option_tab; //add current tab to forms to be rendered
							}
						}
						?>
                        <a class="<?php echo $nav_class; ?>"
                           href="<?php echo esc_url( $tab_url ); ?>"><?php esc_attr_e( $option_tab['title'] ); ?></a>
					<?php endforeach; ?>
                </h2>

				<?php
				$number_of_sections = count( $sections );
				$number             = 0;
				if ( $number_of_sections > 1 ) {
					echo '<div><ul class="subsubsub ovic-settings-sub-nav" style="margin: 8px 0 0;">';
					foreach ( $sections as $section ) {
						echo '<li>';
						$number++;
						$tab_url = add_query_arg( array(
								'settings-updated' => false,
								'tab'              => $active_tab,
								'section'          => $section['id'],
							)
						);
						$class   = '';
						if ( $active_section == $section['id'] ) {
							$class = 'current';
						}
						echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section['title'] . '</a>';
						echo '</li>';
					}
					echo '</ul></div>';
				}
				$args = array(
					'save_button' => esc_html__( 'Save Settings', 'ovic-toolkit' ),
					'object_type' => 'options-page',
				);
				?>
                <!-- End of Nav Tabs -->
				<?php ob_start(); ?>
				<?php foreach ( $tab_forms as $tab_form ) : ?>
                    <div id="<?php esc_attr_e( $tab_form['id'] ); ?>" class="group">
						<?php cmb2_metabox_form( $tab_form, $tab_form['id'], $args ); ?>
                    </div>
				<?php endforeach; ?>
				<?php
				$html = ob_get_clean();
				echo apply_filters( 'ovic_settings_print_option_page', $html, $active_tab, $active_section ); ?>
            </div>
			<?php
		}

		public function get_sections( $tab_active )
		{
			$option_tabs = self::option_fields(); //get all option tabs
			$sections    = array();
			if ( !empty( $option_tabs ) ) {
				foreach ( $option_tabs as $option_tab ) {
					if ( isset( $option_tab['sections'] ) && !empty( $option_tab['sections'] ) && $option_tab['id'] == $tab_active ) {
						$sections = $option_tab['sections'];
					}
				}
			}

			return $sections;
		}

		/**
		 * Defines the theme option metabox and field configuration
		 * @return array
		 * @since  0.1.0
		 */
		public static function option_fields()
		{
			$file_stylesheet = trailingslashit( get_template_directory() ) . 'style.css';
			$theme_info      = get_file_data( $file_stylesheet, array( 'market' => 'Market' ) );
			$market          = ( isset( $theme_info['market'] ) ) ? $theme_info['market'] : '';
			if ( $market != 'Envato' && $market != 'Templatemonster' ) {
				$options['general_options'] = array(
					'id'         => 'general_options', //id used as tab page slug, must be unique
					'title'      => __( 'General', 'ovic-toolkit' ),
					'show_names' => true,
					'sections'   => array(),
				);
			}
			$options['extends_options'] = array(
				'id'         => 'extends_options',
				'title'      => __( 'Extends Settings', 'ovic-toolkit' ),
				'show_names' => true,
				'sections'   => array(),
			);

			return apply_filters( 'ovic_registered_settings', $options );
		}

		/**
		 * Initiate our hooks
		 * @since 0.1.0
		 */
		public function hooks()
		{
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		/**
		 * Register our setting tabs to WP
		 * @since  0.1.0
		 */
		public function init()
		{
			$option_tabs = self::option_fields();
			foreach ( $option_tabs as $index => $option_tab ) {
				register_setting( $option_tab['id'], $option_tab['id'] );
			}
		}
	}

	// Get it started
	$Ovic_Settings = new Ovic_Settings();
	$Ovic_Settings->hooks();
}