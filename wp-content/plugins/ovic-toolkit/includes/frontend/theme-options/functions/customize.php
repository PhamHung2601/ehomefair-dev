<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * WP Customize custom panel
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'WP_Customize_Panel_OVIC' ) ) {
	class WP_Customize_Panel_OVIC extends WP_Customize_Panel
	{
		public $type = "ovic";
	}
}
/**
 *
 * WP Customize custom section
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'WP_Customize_Section_OVIC' ) ) {
	class WP_Customize_Section_OVIC extends WP_Customize_Section
	{
		public $type = "ovic";
	}
}
/**
 *
 * WP Customize custom control
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'WP_Customize_Control_OVIC' ) ) {
	class WP_Customize_Control_OVIC extends WP_Customize_Control
	{
		public $type   = "ovic";
		public $field  = '';
		public $unique = '';

		public function render_content()
		{
			$unallows   = array( 'wysiwyg' );
			$field_id   = ( !empty( $this->field['id'] ) ) ? $this->field['id'] : '';
			$complex    = apply_filters( 'ovic_customize_complex', array( 'checkbox', 'sorter', 'image_select', 'background', 'typography', 'fieldset', 'group', 'repeater', 'tabbed', 'accordion' ) );
			$custom     = ( !empty( $this->field['customizer'] ) ) ? true : false;
			$is_complex = ( in_array( $this->field['type'], $complex ) ) ? true : false;
			$class      = ( $is_complex || $custom ) ? ' ovic-customize-complex' : '';
			$atts       = ( $is_complex || $custom ) ? ' data-unique-id="' . $this->unique . '" data-option-id="' . $field_id . '"' : '';
			if ( !$is_complex && !$custom ) {
				$this->field['attributes']['data-customize-setting-link'] = $this->settings['default']->id;
			}
			$this->field['name'] = $this->settings['default']->id;
			if ( in_array( $this->field['type'], $unallows ) ) {
				$this->field['_notice'] = true;
			}
			echo '<div class="ovic-customize-field' . $class . '"' . $atts . '>';
			echo ovic_add_field( $this->field, $this->value(), $this->unique, 'customize' );
			echo '</div>';
		}
	}
}
