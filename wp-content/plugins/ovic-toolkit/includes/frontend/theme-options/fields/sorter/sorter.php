<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Sorter
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_Sorter' ) ) {
	class OVIC_Field_Sorter extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			// Only Show Enabled
			$enabled_only = ( !empty( $this->field['enabled_only'] ) ) ? false : true;
			// Define defaults
			$enables  = ( !empty( $this->field['options']['enabled'] ) ) ? $this->field['options']['enabled'] : array();
			$disables = ( !empty( $this->field['options']['disabled'] ) ) ? $this->field['options']['disabled'] : array();
			$options  = wp_parse_args( $enables, $disables );
			// Define Value
			$value    = $this->element_value();
			$value    = ( !empty( $value ) ) ? $value : array();
			$enabled  = ( !empty( $value['enabled'] ) ) ? $value['enabled'] : array_keys( $enables );
			$disabled = ( !empty( $value['disabled'] ) ) ? $value['disabled'] : array_keys( $disables );
			echo '<button class="reset-field-customizer" data-field="' . $this->field['id'] . '">' . esc_html__( 'Reset This Field', 'ovic-toolkit' ) . '</button>';
			echo ( $enabled_only ) ? '<div class="ovic-modules">' : '';
			echo ( !empty( $this->field['enabled_title'] ) ) ? '<div class="ovic-sorter-title">' . $this->field['enabled_title'] . '</div>' : '';
			echo '<ul class="ovic-enabled">';
			if ( !empty( $enabled ) ) {
				foreach ( $enabled as $key ) {
					echo '<li><input type="hidden" name="' . $this->element_name( '[enabled][]' ) . '" value="' . $key . '"/><label>' . $options[$key] . '</label></li>';
				}
			}
			echo '</ul>';
			// Check for has disabled
			if ( $enabled_only ) {
				echo '</div>';
				echo '<div class="ovic-modules">';
				echo ( !empty( $this->field['disabled_title'] ) ) ? '<div class="ovic-sorter-title">' . $this->field['disabled_title'] . '</div>' : '';
				echo '<ul class="ovic-disabled">';
				if ( !empty( $disabled ) ) {
					foreach ( $disabled as $key ) {
						echo '<li><input type="hidden" name="' . $this->element_name( '[disabled][]' ) . '" value="' . $key . '"/><label>' . $options[$key] . '</label></li>';
					}
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '<div class="clear"></div>';
			echo $this->element_after();
		}

		public static function enqueue()
		{
			wp_enqueue_script( 'jquery-ui-sortable' );
		}
	}
}
