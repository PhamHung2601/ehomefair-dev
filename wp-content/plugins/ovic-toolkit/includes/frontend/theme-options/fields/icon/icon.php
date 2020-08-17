<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_icon' ) ) {
	class OVIC_Field_icon extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			$value  = $this->element_value();
			$hidden = ( empty( $value ) ) ? ' hidden' : '';
			echo '<div class="ovic-icon-select">';
			echo '<span class="ovic-icon-preview' . $hidden . '"><i class="' . $value . '"></i></span>';
			echo '<a href="#" class="button button-primary ovic-icon-add">' . __( 'Add Icon', 'ovic-toolkit' ) . '</a>';
			echo '<a href="#" class="button ovic-warning-primary ovic-icon-remove' . $hidden . '">' . __( 'Remove Icon', 'ovic-toolkit' ) . '</a>';
			echo '<input type="text" name="' . $this->element_name() . '" value="' . $value . '"' . $this->element_class( 'ovic-icon-value' ) . $this->element_attributes() . ' />';
			echo '</div>';
			echo $this->element_after();
		}
	}
}
