<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Options class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Options' ) ) {
	class OVIC_Options extends OVIC_Abstract
	{
		/**
		 *
		 * unique
		 * @access public
		 * @var string
		 *
		 */
		public $unique = '';
		/**
		 *
		 * notice
		 * @access public
		 * @var boolean
		 *
		 */
		public $notice = false;
		/**
		 *
		 * settings
		 * @access public
		 * @var array
		 *
		 */
		public $settings = array();
		/**
		 *
		 * options
		 * @access public
		 * @var array
		 *
		 */
		public $options = array();
		/**
		 *
		 * sections
		 * @access public
		 * @var array
		 *
		 */
		public $sections = array();
		/**
		 *
		 * fields
		 * @access public
		 * @var array
		 *
		 */
		public $fields = array();
		/**
		 *
		 * options store
		 * @access public
		 * @var array
		 *
		 */
		public $db_option = array();

		// run framework construct
		public function __construct( $settings = array(), $options = array() )
		{
			$this->settings  = apply_filters( 'ovic_settings_framework', $settings );
			$this->options   = apply_filters( 'ovic_options_framework', $options );
			$this->unique    = $this->settings['option_name'];
			$this->sections  = $this->getSections( $this->options );
			$this->fields    = $this->getFields( $this->options );
			$this->db_option = get_option( $this->unique );
			$this->installed = get_option( $this->unique . '_installed' );
			$this->addAction( 'admin_init', 'setup' );
			$this->addAction( 'admin_menu', 'add_admin_menu' );
			$this->addEnqueue( $this->options );
		}

		// instance of framework
		public static function instance( $settings = array(), $options = array() )
		{
			return new self( $settings, $options );
		}

		// wp settings api
		public function setup()
		{
			foreach ( $this->sections as $section ) {
				$unique = $this->unique . '_' . $section['name'];
				register_setting( $this->unique . '_group', $this->unique, array( &$this, 'validate_save' ) );
				if ( !empty( $section['fields'] ) ) {
					add_settings_section( $unique . '_section', $section['title'], '', $unique . '_section_group' );
					foreach ( $section['fields'] as $key => $field ) {
						add_settings_field( $key . '_field', '', array( &$this, 'field_callback' ), $unique . '_section_group', $unique . '_section', $field );
						// set default option if isset
						if ( isset( $field['id'] ) ) {
							$field_default                 = ( isset( $field['default'] ) ) ? $field['default'] : '';
							$field_value                   = ( isset( $this->db_option[$field['id']] ) ) ? $this->db_option[$field['id']] : $field_default;
							$this->db_option[$field['id']] = $field_value;
						}
					}
				}
			}
			// check for is saved defaults in database
			if ( $this->settings['save_defaults'] && empty( $this->installed ) ) {
				update_option( $this->unique, $this->db_option );
				update_option( $this->unique . '_installed', true );
			}
		}

		// section fields validate in save
		public function validate_save( $request )
		{
			$add_errors = array();
			$section_id = ( !empty( $request['_transient']['section'] ) ) ? $request['_transient']['section'] : '';
			// ignore nonce requests
			if ( isset( $request['_nonce'] ) ) {
				unset( $request['_nonce'] );
			}
			// import
			if ( isset( $request['import'] ) && !empty( $request['import'] ) ) {
				$decode_string = ovic_decode_string( $request['import'] );
				if ( is_array( $decode_string ) ) {
					return $decode_string;
				}
				$add_errors[] = $this->add_settings_error( __( 'Success. Imported backup options.', 'ovic-toolkit' ), 'updated' );
			}
			// reset all options
			if ( isset( $request['resetall'] ) ) {
				foreach ( $this->fields as $field ) {
					if ( !empty( $field['id'] ) ) {
						if ( isset( $field['default'] ) ) {
							$request[$field['id']] = $field['default'];
						} else {
							$request[$field['id']] = '';
						}
					}
				}
				$add_errors[] = $this->add_settings_error( __( 'Default options restored.', 'ovic-toolkit' ), 'updated' );
			}
			// restore section
			if ( isset( $request['restore'] ) && !empty( $section_id ) ) {
				foreach ( $this->sections as $value ) {
					if ( $this->unique . '_' . $value['name'] == $section_id ) {
						foreach ( $value['fields'] as $field ) {
							if ( isset( $field['id'] ) ) {
								if ( isset( $field['default'] ) ) {
									$request[$field['id']] = $field['default'];
								} else {
									unset( $request[$field['id']] );
								}
							}
						}
					}
				}
				$add_errors[] = $this->add_settings_error( __( 'Default options restored for only this section.', 'ovic-toolkit' ), 'updated' );
			}
			// sanitize and validate
			foreach ( $this->fields as $field ) {
				if ( !empty( $field['id'] ) ) {
					// sanitize
					if ( !empty( $field['sanitize'] ) ) {
						$sanitize = $field['sanitize'];
						if ( function_exists( $sanitize ) ) {
							$value_sanitize        = isset( $request[$field['id']] ) ? $request[$field['id']] : '';
							$request[$field['id']] = call_user_func( $sanitize, $value_sanitize );
						}
					}
					// validate
					if ( !empty( $field['validate'] ) ) {
						$validate = $field['validate'];
						if ( function_exists( $validate ) ) {
							$value_validate = isset( $request[$field['id']] ) ? $request[$field['id']] : '';
							$has_validated  = call_user_func( $validate, array( 'value' => $value_validate, 'field' => $field ) );
							if ( !empty( $has_validated ) ) {
								$add_errors[]          = $this->add_settings_error( $has_validated, 'error', $field['id'] );
								$request[$field['id']] = ( isset( $this->db_option[$field['id']] ) ) ? $this->db_option[$field['id']] : '';
							}
						}
					}
					// auto sanitize
					if ( !isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
						$request[$field['id']] = '';
					}
				}
			}
			$request = apply_filters( 'ovic_save_validate', $request );
			do_action( 'ovic_save_validate_after', $request );
			// set transient
			$request['_transient']['expires'] = round( microtime( true ) );
			$request['_transient']['errors']  = $add_errors;
			$request['_transient']['section'] = $section_id;

			return $request;
		}

		// field callback
		public function field_callback( $field )
		{
			$value = ( isset( $field['id'] ) && isset( $this->db_option[$field['id']] ) ) ? $this->db_option[$field['id']] : '';
			echo ovic_add_field( $field, $value, $this->unique, 'options' );
		}

		// wp api: settings sections
		public function do_settings_sections( $page )
		{
			global $wp_settings_sections, $wp_settings_fields;
			if ( !isset( $wp_settings_sections[$page] ) ) {
				return;
			}
			foreach ( $wp_settings_sections[$page] as $section ) {
				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}
				if ( !isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) ) {
					continue;
				}
				$this->do_settings_fields( $page, $section['id'] );
			}
		}

		// wp api: settings fields
		public function do_settings_fields( $page, $section )
		{
			global $wp_settings_fields;
			if ( !isset( $wp_settings_fields[$page][$section] ) ) {
				return;
			}
			foreach ( $wp_settings_fields[$page][$section] as $field ) {
				call_user_func( $field['callback'], $field['args'] );
			}
		}

		// custom add settings error
		public function add_settings_error( $message, $type = 'error', $id = 'global' )
		{
			return array( 'setting' => 'ovic-errors', 'code' => $id, 'message' => $message, 'type' => $type );
		}

		// custom transient
		public function transient( $option = '', $default = '' )
		{
			return ( !empty( $this->db_option['_transient'][$option] ) ) ? $this->db_option['_transient'][$option] : $default;
		}

		// wp api: admin menu
		public function add_admin_menu()
		{
			$defaults = array(
				'menu_parent'     => '',
				'menu_title'      => '',
				'menu_type'       => '',
				'menu_slug'       => '',
				'menu_icon'       => '',
				'menu_capability' => 'manage_options',
				'menu_position'   => null,
			);
			$args     = wp_parse_args( $this->settings, $defaults );
			if ( $args['menu_type'] == 'submenu' ) {
				call_user_func( 'add_' . $args['menu_type'] . '_page', $args['menu_parent'], $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array( &$this, 'add_options_html' ) );
			} else {
				call_user_func( 'add_' . $args['menu_type'] . '_page', $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array( &$this, 'add_options_html' ), $args['menu_icon'], $args['menu_position'] );
			}
		}

		// option page html output
		public function add_options_html()
		{
			$timenow      = round( microtime( true ) );
			$timein       = ovic_timeout( $timenow, $this->transient( 'expires', 0 ), 20 );
			$section_data = $this->transient( 'section' );
			$errors       = $this->transient( 'errors' );
			$has_nav      = ( count( $this->options ) <= 1 ) ? ' ovic-show-all' : '';
			$section_id   = ( !empty( $section_data ) && $timein ) ? $section_data : $this->unique . '_' . $this->sections[0]['name'];
			$section_id   = ovic_get_var( 'ovic-section', $section_id );
			$ajax_class   = ( $this->settings['ajax_save'] ) ? ' ovic-save-ajax' : '';
			$sticky_class = ( $this->settings['sticky_header'] ) ? ' ovic-sticky-header' : '';
			do_action( 'ovic_html_options_before' );
			echo '<div class="ovic ovic-options">';
			echo '<form method="post" action="options.php" enctype="multipart/form-data" id="OVIC_form">';
			echo '<input type="hidden" class="ovic-section-id" name="' . $this->unique . '[_transient][section]" value="' . $section_id . '">';
			if ( $this->settings['ajax_save'] !== true && !empty( $errors ) && $timein ) {
				global $ovic;
				foreach ( $errors as $error ) {
					if ( in_array( $error['setting'], array( 'general', 'ovic-errors' ) ) ) {
						echo '<div class="ovic-settings-error ' . $error['type'] . '">';
						echo '<p><strong>' . $error['message'] . '</strong></p>';
						echo '</div>';
					}
					$ovic['errors'] = $errors;
				}
			}
			settings_fields( $this->unique . '_group' );
			echo '<div class="ovic-header' . esc_attr( $sticky_class ) . '">';
			echo '<div class="ovic-header-inner">';
			echo '<div class="ovic-header-left">';
			echo '<h1>' . $this->settings['framework_title'] . '</h1>';
			echo ( $this->settings['show_search'] ) ? '<div class="ovic-search"><input type="text" placeholder="' . __( 'Search option(s)', 'ovic-toolkit' ) . '" /></div>' : '';
			echo '</div>';
			echo '<div class="ovic-header-right">';
			echo '<div class="ovic-buttons">';
			submit_button( __( 'Save', 'ovic-toolkit' ), 'primary ovic-save' . $ajax_class, 'save', false, array( 'data-save' => __( 'Saving...', 'ovic-toolkit' ) ) );
			submit_button( __( 'Restore', 'ovic-toolkit' ), 'secondary ovic-restore ovic-confirm', $this->unique . '[restore]', false );
			if ( $this->settings['show_reset'] ) {
				submit_button( __( 'Reset All Options', 'ovic-toolkit' ), 'secondary ovic-reset ovic-warning-primary ovic-confirm', $this->unique . '[resetall]', false );
			}
			echo '</div>';
			echo ( empty( $has_nav ) && $this->settings['show_all_options'] ) ? '<a href="#" class="ovic-expand-all"><i class="fa fa-eye-slash"></i> ' . __( 'show all options', 'ovic-toolkit' ) . '</a>' : '';
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '</div>';
			echo '<div class="ovic-wrapper' . $has_nav . '">';
			echo '<div class="ovic-nav">';
			echo '<ul>';
			foreach ( $this->options as $key => $tab ) {
				if ( !empty( $tab['sections'] ) ) {
					$tab_active  = ovic_array_search( $tab['sections'], 'name', str_replace( $this->unique . '_', '', $section_id ) );
					$active_list = ( !empty( $tab_active ) ) ? ' ovic-tab-active' : '';
					$tab_icon    = ( !empty( $tab['icon'] ) ) ? '<i class="' . $tab['icon'] . '"></i>' : '';
					echo '<li class="ovic-sub' . $active_list . '">';
					echo '<a href="#" class="ovic-arrow">' . $tab_icon . $tab['title'] . '</a>';
					echo '<ul>';
					foreach ( $tab['sections'] as $tab_section ) {
						$active_tab = ( $section_id == $this->unique . '_' . $tab_section['name'] ) ? ' class="ovic-section-active"' : '';
						$icon       = ( !empty( $tab_section['icon'] ) ) ? '<i class="' . $tab_section['icon'] . '"></i>' : '';
						echo '<li><a href="#"' . $active_tab . ' data-section="' . $this->unique . '_' . $tab_section['name'] . '">' . $icon . $tab_section['title'] . '</a></li>';
					}
					echo '</ul>';
					echo '</li>';
				} else {
					$icon = ( !empty( $tab['icon'] ) ) ? '<i class="' . $tab['icon'] . '"></i>' : '';
					if ( !empty( $tab['fields'] ) ) {
						$active_list = ( $section_id == $this->unique . '_' . $tab['name'] ) ? ' class="ovic-section-active"' : '';
						echo '<li><a href="#"' . $active_list . ' data-section="' . $this->unique . '_' . $tab['name'] . '">' . $icon . $tab['title'] . '</a></li>';
					} else {
						echo '<li><div class="ovic-seperator">' . $icon . $tab['title'] . '</div></li>';
					}
				}
			}
			echo '</ul>';
			echo '</div>';
			echo '<div class="ovic-content">';
			echo '<div class="ovic-sections">';
			foreach ( $this->sections as $section ) {
				if ( !empty( $section['fields'] ) ) {
					$active_content = ( $section_id !== $this->unique . '_' . $section['name'] ) ? 'hidden' : 'ovic-onload';
					echo '<div id="ovic-tab-' . $this->unique . '_' . $section['name'] . '" class="ovic-section ' . $active_content . '">';
					echo ( !empty( $section['title'] ) && empty( $has_nav ) ) ? '<div class="ovic-section-title"><h3>' . $section['title'] . '</h3></div>' : '';
					$this->do_settings_sections( $this->unique . '_' . $section['name'] . '_section_group' );
					echo '</div>';
				}
			}
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '<div class="ovic-nav-background"></div>';
			echo '</div>';
			if ( $this->settings['show_footer'] ) {
				echo '<div class="ovic-footer">';
				echo '<div class="ovic-buttons">';
				submit_button( __( 'Save', 'ovic-toolkit' ), 'primary ovic-save' . $ajax_class, 'save', false, array( 'data-save' => __( 'Saving...', 'ovic-toolkit' ) ) );
				submit_button( __( 'Restore', 'ovic-toolkit' ), 'secondary ovic-restore ovic-confirm', $this->unique . '[restore]', false );
				if ( $this->settings['show_reset'] ) {
					submit_button( __( 'Reset All Options', 'ovic-toolkit' ), 'secondary ovic-reset ovic-warning-primary ovic-confirm', $this->unique . '[resetall]', false );
				}
				echo '</div>';
				echo '<div class="ovic-copyright">Powered by Ovic Framework v1.0.0</div>';
				echo '<div class="clear"></div>';
				echo '</div>';
			}
			echo '</form>';
			echo '<div class="clear"></div>';
			echo '</div>';
			do_action( 'ovic_html_options_after' );
		}
	}
}