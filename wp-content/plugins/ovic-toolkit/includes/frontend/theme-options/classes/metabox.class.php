<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Metabox Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Metabox' ) ) {
	class OVIC_Metabox extends OVIC_Abstract
	{
		/**
		 *
		 * options
		 * @access public
		 * @var array
		 *
		 */
		public $options = array();

		// run metabox construct
		public function __construct( $options )
		{
			$this->options = apply_filters( 'ovic_options_metabox', $options );
			$this->addAction( 'add_meta_boxes', 'add_meta_box' );
			$this->addAction( 'save_post', 'save_meta_box', 10, 2 );
			$this->addEnqueue( $this->options );
		}

		// instance
		public static function instance( $options = array() )
		{
			return new self( $options );
		}

		// add metabox
		public function add_meta_box( $post_type )
		{
			foreach ( $this->options as $value ) {
				add_meta_box( $value['id'], $value['title'], array( &$this, 'add_meta_box_content' ), $value['post_type'], $value['context'], $value['priority'], $value );
			}
		}

		// add metabox content
		public function add_meta_box_content( $post, $callback )
		{
			global $post, $ovic, $typenow;
			wp_nonce_field( 'ovic-metabox', 'ovic-metabox-nonce' );
			$args       = $callback['args'];
			$unique     = $args['id'];
			$sections   = $args['sections'];
			$meta_value = get_post_meta( $post->ID, $unique, true );
			$has_nav    = ( count( $sections ) >= 2 && $args['context'] != 'side' ) ? true : false;
			$show_all   = ( !$has_nav ) ? ' ovic-show-all' : '';
			$timenow    = round( microtime( true ) );
			$errors     = ( isset( $meta_value['_transient']['errors'] ) ) ? $meta_value['_transient']['errors'] : array();
			$section    = ( isset( $meta_value['_transient']['section'] ) ) ? $meta_value['_transient']['section'] : false;
			$expires    = ( isset( $meta_value['_transient']['expires'] ) ) ? $meta_value['_transient']['expires'] : 0;
			$timein     = ovic_timeout( $timenow, $expires, 20 );
			$section_id = ( $timein && $section ) ? $section : '';
			$section_id = ovic_get_var( 'ovic-section', $section_id );
			// add erros
			$ovic['errors'] = ( $timein ) ? $errors : array();
			do_action( 'ovic_html_metabox_before' );
			echo '<div class="ovic ovic-metabox">';
			echo '<input type="hidden" name="' . $unique . '[_transient][section]" class="ovic-section-id" value="' . $section_id . '">';
			echo '<div class="ovic-wrapper' . $show_all . '">';
			if ( $has_nav ) {
				echo '<div class="ovic-nav">';
				echo '<ul>';
				$num = 0;
				foreach ( $sections as $value ) {
					if ( !empty( $value['typenow'] ) && $value['typenow'] !== $typenow ) {
						continue;
					}
					$tab_icon = ( !empty( $value['icon'] ) ) ? '<i class="ovic-icon ' . $value['icon'] . '"></i>' : '';
					if ( isset( $value['fields'] ) ) {
						$active_section = ( ( empty( $section_id ) && $num === 0 ) || $section_id == $unique . '_' . $value['name'] ) ? ' class="ovic-section-active"' : '';
						echo '<li><a href="#"' . $active_section . ' data-section="' . $unique . '_' . $value['name'] . '">' . $tab_icon . $value['title'] . '</a></li>';
					} else {
						echo '<li><div class="ovic-seperator">' . $tab_icon . $value['title'] . '</div></li>';
					}
					$num++;
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '<div class="ovic-content">';
			echo '<div class="ovic-sections">';
			$num = 0;
			foreach ( $sections as $v ) {
				if ( !empty( $v['typenow'] ) && $v['typenow'] !== $typenow ) {
					continue;
				}
				if ( isset( $v['fields'] ) ) {
					$active_content = ( ( empty( $section_id ) && $num === 0 ) || $section_id === $unique . '_' . $v['name'] ) ? 'ovic-onload' : 'hidden';
					echo '<div id="ovic-tab-' . $unique . '_' . $v['name'] . '" class="ovic-section ' . $active_content . '">';
					echo ( isset( $v['title'] ) ) ? '<div class="ovic-section-title"><h3>' . $v['title'] . '</h3></div>' : '';
					foreach ( $v['fields'] as $field_key => $field ) {
						$default    = ( isset( $field['default'] ) ) ? $field['default'] : '';
						$elem_id    = ( isset( $field['id'] ) ) ? $field['id'] : '';
						$elem_value = ( is_array( $meta_value ) && isset( $meta_value[$elem_id] ) ) ? $meta_value[$elem_id] : $default;
						echo ovic_add_field( $field, $elem_value, $unique, 'metabox' );
					}
					echo '</div>';
				}
				$num++;
			}
			echo '</div>';
			echo '<div class="clear"></div>';
			if ( !empty( $args['show_restore'] ) ) {
				echo '<div class=" ovic-metabox-restore">';
				echo '<label>';
				echo '<input type="checkbox" name="' . $unique . '[_restore]" />';
				echo '<span class="button ovic-button-restore">' . __( 'Restore', 'ovic-toolkit' ) . '</span>';
				echo '<span class="button ovic-button-cancel">' . sprintf( '<small>( %s )</small> %s', __( 'update post for restore ', 'ovic-toolkit' ), __( 'Cancel', 'ovic-toolkit' ) ) . '</span>';
				echo '</label>';
				echo '</div>';
			}
			echo '</div>';
			echo ( $has_nav ) ? '<div class="ovic-nav-background"></div>' : '';
			echo '<div class="clear"></div>';
			echo '</div>';
			echo '</div>';
			do_action( 'ovic_html_metabox_after' );
		}

		// save metabox
		public function save_meta_box( $post_id, $post )
		{
			if ( wp_verify_nonce( ovic_get_var( 'ovic-metabox-nonce' ), 'ovic-metabox' ) ) {
				$errors    = array();
				$post_type = ovic_get_var( 'post_type' );
				foreach ( $this->options as $request_value ) {
					if ( in_array( $post_type, (array)$request_value['post_type'] ) ) {
						$request_key = $request_value['id'];
						$request     = ovic_get_var( $request_key, array() );
						// ignore _nonce
						if ( isset( $request['_nonce'] ) ) {
							unset( $request['_nonce'] );
						}
						// sanitize and validate
						foreach ( $request_value['sections'] as $key => $section ) {
							if ( !empty( $section['fields'] ) ) {
								foreach ( $section['fields'] as $field ) {
									if ( !empty( $field['id'] ) ) {
										// sanitize
										if ( !empty( $field['sanitize'] ) ) {
											$sanitize = $field['sanitize'];
											if ( function_exists( $sanitize ) ) {
												$value_sanitize        = ovic_get_vars( $request_key, $field['id'] );
												$request[$field['id']] = call_user_func( $sanitize, $value_sanitize );
											}
										}
										// validate
										if ( !empty( $field['validate'] ) ) {
											$validate = $field['validate'];
											if ( function_exists( $validate ) ) {
												$value_validate = ovic_get_vars( $request_key, $field['id'] );
												$has_validated  = call_user_func( $validate, array( 'value' => $value_validate, 'field' => $field ) );
												if ( !empty( $has_validated ) ) {
													$meta_value            = get_post_meta( $post_id, $request_key, true );
													$errors[$field['id']]  = array( 'code' => $field['id'], 'message' => $has_validated, 'type' => 'error' );
													$default_value         = isset( $field['default'] ) ? $field['default'] : '';
													$request[$field['id']] = ( isset( $meta_value[$field['id']] ) ) ? $meta_value[$field['id']] : $default_value;
												}
											}
										}
										// auto sanitize
										if ( !isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
											$request[$field['id']] = '';
										}
									}
								}
							}
						}
						$request['_transient']['expires'] = round( microtime( true ) );
						if ( !empty( $errors ) ) {
							$request['_transient']['errors'] = $errors;
						}
						$request = apply_filters( 'ovic_save_metabox', $request, $request_key, $post );
						if ( empty( $request ) || !empty( $request['_restore'] ) ) {
							delete_post_meta( $post_id, $request_key );
						} else {
							update_post_meta( $post_id, $request_key, $request );
						}
					}
				}
			}
		}
	}
}
