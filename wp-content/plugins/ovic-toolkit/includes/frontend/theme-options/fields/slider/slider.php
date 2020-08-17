<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Slider
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_slider' ) ) {
	class OVIC_Field_slider extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			$min  = ( !empty( $this->field['options']['min'] ) ) ? $this->field['options']['min'] : 0;
			$max  = ( !empty( $this->field['options']['max'] ) ) ? $this->field['options']['max'] : 1000;
			$step = ( !empty( $this->field['options']['step'] ) ) ? $this->field['options']['step'] : 1;
			$unit = ( !empty( $this->field['options']['unit'] ) ) ? '<em>' . $this->field['options']['unit'] . '</em>' : '';
			echo '<div class="ovic-table">';
			echo '<div class="ovic-table-cell ovic-table-expanded"><div class="ovic-slider-ui"></div></div>';
			echo '<div class="ovic-table-cell ovic-nowrap">';
			echo '<input type="text" name="' . $this->element_name() . '" value="' . $this->element_value() . '"' . $this->element_class() . $this->element_attributes() . ' data-max="' . $max . '" data-min="' . $min . '" data-step="' . $step . '" />' . $unit;
			echo '</div>';
			echo '</div>';
			echo $this->element_after();
		}

		public static function enqueue()
		{
			wp_enqueue_script( 'jquery-ui-slider' );
		}
	}
}
