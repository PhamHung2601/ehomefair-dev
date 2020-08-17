<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Repeater
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_repeater' ) ) {
	class OVIC_Field_repeater extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			$fields    = $this->field['fields'];
			$unallows  = array( 'wysiwyg', 'group', 'repeater' );
			$limit     = ( !empty( $this->field['limit'] ) ) ? $this->field['limit'] : 0;
			$unique_id = ( !empty( $this->unique ) ) ? $this->unique : $this->field['id'];
			$button_title = ( !empty( $this->field['button_title'] ) ) ? $this->field['button_title'] : '+';
			echo '<div class="ovic-cloneable-item ovic-cloneable-hidden">';
			echo '<div class="ovic-cloneable-content">';
			foreach ( $fields as $field ) {
				if ( in_array( $field['type'], $unallows ) ) {
					$field['_notice'] = true;
				}
				$field['sub']        = true;
				$field['wrap_class'] = ( !empty( $field['wrap_class'] ) ) ? $field['wrap_class'] . ' ovic-no-script' : 'ovic-no-script';
				$unique        = ( !empty( $this->unique ) ) ? '_nonce[' . $this->field['id'] . '][num]' : '_nonce[num]';
				$field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
				echo ovic_add_field( $field, $field_default, $unique, 'field/repeater' );
			}
			echo '</div>';
			echo '<div class="ovic-cloneable-helper">';
			echo '<div class="ovic-cloneable-helper-inner">';
			echo '<i class="ovic-cloneable-sort fa fa-arrows"></i>';
			echo '<i class="ovic-cloneable-clone fa fa-clone"></i>';
			echo '<i class="ovic-cloneable-remove fa fa-times"></i>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<div class="ovic-cloneable-wrapper">';
			if ( !empty( $this->value ) ) {
				$num = 0;
				foreach ( $this->value as $key => $value ) {
					echo '<div class="ovic-cloneable-item">';
					echo '<div class="ovic-cloneable-content">';
					foreach ( $fields as $field ) {
						if ( in_array( $field['type'], $unallows ) ) {
							$field['_notice'] = true;
						}
						$field['sub'] = true;
						$unique       = ( !empty( $this->unique ) ) ? $this->unique . '[' . $this->field['id'] . '][' . $num . ']' : $this->field['id'] . '[' . $num . ']';
						$value        = ( isset( $field['id'] ) && isset( $this->value[$key][$field['id']] ) ) ? $this->value[$key][$field['id']] : '';
						echo ovic_add_field( $field, $value, $unique, 'field/repeater' );
					}
					echo '</div>';
					echo '<div class="ovic-cloneable-helper">';
					echo '<div class="ovic-cloneable-helper-inner">';
					echo '<i class="ovic-cloneable-sort fa fa-arrows"></i>';
					echo '<i class="ovic-cloneable-clone fa fa-clone"></i>';
					echo '<i class="ovic-cloneable-remove fa fa-times"></i>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					$num++;
				}
			}
			echo '</div>';
			echo '<div class="ovic-cloneable-data" data-unique-id="' . $unique_id . '" data-limit="' . $limit . '">' . __( 'You can not add more than', 'ovic-toolkit' ) . ' ' . $limit . '</div>';
			echo '<a href="#" class="button button-primary ovic-cloneable-add">' . $button_title . '</a>';
			echo $this->element_after();
		}
	}
}
