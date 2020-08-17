<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Group
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_group' ) ) {
	class OVIC_Field_group extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			$unallows    = array( 'wysiwyg', 'group', 'repeater' );
			$limit       = ( !empty( $this->field['limit'] ) ) ? $this->field['limit'] : 0;
			$fields      = array_values( $this->field['fields'] );
			$acc_title   = ( isset( $this->field['accordion_title'] ) ) ? $this->field['accordion_title'] : __( 'Adding', 'ovic-toolkit' );
			$field_title = ( isset( $fields[0]['title'] ) ) ? $fields[0]['title'] : $fields[1]['title'];
			$field_id    = ( isset( $fields[0]['id'] ) ) ? $fields[0]['id'] : $fields[1]['id'];
			$unique_id   = ( !empty( $this->unique ) ) ? $this->unique : $this->field['id'];
			$search_id   = ovic_array_search( $fields, 'id', $acc_title );
			if ( !empty( $search_id ) ) {
				$acc_title = ( isset( $search_id[0]['title'] ) ) ? $search_id[0]['title'] : $acc_title;
				$field_id  = ( isset( $search_id[0]['id'] ) ) ? $search_id[0]['id'] : $field_id;
			}
			echo '<div class="ovic-cloneable-item ovic-cloneable-hidden ovic-no-script">';
			echo '<div class="ovic-cloneable-helper">';
			echo '<i class="ovic-cloneable-pending fa fa-circle"></i>';
			echo '<i class="ovic-cloneable-clone fa fa-clone"></i>';
			echo '<i class="ovic-cloneable-remove fa fa-times"></i>';
			echo '</div>';
			echo '<h4 class="ovic-cloneable-title"><span class="ovic-cloneable-text">' . $acc_title . '</span></h4>';
			echo '<div class="ovic-cloneable-content">';
			foreach ( $fields as $field ) {
				if ( in_array( $field['type'], $unallows ) ) {
					$field['_notice'] = true;
				}
				$field['sub']        = true;
				$field['wrap_class'] = ( !empty( $field['wrap_class'] ) ) ? $field['wrap_class'] . ' ovic-no-script' : 'ovic-no-script';
				$unique        = ( !empty( $this->unique ) ) ? '_nonce[' . $this->field['id'] . '][num]' : '_nonce[num]';
				$field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
				echo ovic_add_field( $field, $field_default, $unique, 'field/group' );
			}
			echo '<div class="ovic-field ovic-text-right"><a href="#" class="button ovic-warning-primary ovic-cloneable-remove">' . __( 'Remove', 'ovic-toolkit' ) . '</a></div>';
			echo '</div>';
			echo '</div>';
			echo '<div class="ovic-cloneable-wrapper">';
			if ( !empty( $this->value ) ) {
				$num = 0;
				foreach ( $this->value as $key => $value ) {
					$title = ( isset( $this->value[$key][$field_id] ) ) ? $this->value[$key][$field_id] : '';
					if ( is_array( $title ) && isset( $this->multilang ) ) {
						$lang  = ovic_language_defaults();
						$title = $title[$lang['current']];
						$title = is_array( $title ) ? $title[0] : $title;
					}
					$field_title = ( !empty( $search_id ) ) ? $acc_title : $field_title;
					echo '<div class="ovic-cloneable-item">';
					echo '<div class="ovic-cloneable-helper">';
					echo '<i class="ovic-cloneable-pending fa fa-circle"></i>';
					echo '<i class="ovic-cloneable-clone fa fa-clone"></i>';
					echo '<i class="ovic-cloneable-remove fa fa-times"></i>';
					echo '</div>';
					echo '<h4 class="ovic-cloneable-title"><span class="ovic-cloneable-text">' . $field_title . ': ' . $title . '</span></h4>';
					echo '<div class="ovic-cloneable-content">';
					foreach ( $fields as $field ) {
						if ( in_array( $field['type'], $unallows ) ) {
							$field['_notice'] = true;
						}
						$field['sub']        = true;
						$field['wrap_class'] = ( !empty( $field['wrap_class'] ) ) ? $field['wrap_class'] . ' ovic-no-script' : 'ovic-no-script';
						$unique = ( !empty( $this->unique ) ) ? $this->unique . '[' . $this->field['id'] . '][' . $num . ']' : $this->field['id'] . '[' . $num . ']';
						$value  = ( isset( $field['id'] ) && isset( $this->value[$key][$field['id']] ) ) ? $this->value[$key][$field['id']] : '';
						echo ovic_add_field( $field, $value, $unique, 'field/group' );
					}
					echo '<div class="ovic-field ovic-text-right"><a href="#" class="button ovic-warning-primary ovic-cloneable-remove">' . __( 'Remove', 'ovic-toolkit' ) . '</a></div>';
					echo '</div>';
					echo '</div>';
					$num++;
				}
			}
			echo '</div>';
			echo '<div class="ovic-cloneable-data" data-unique-id="' . $unique_id . '" data-limit="' . $limit . '">' . __( 'You can not add more than', 'ovic-toolkit' ) . ' ' . $limit . '</div>';
			echo '<a href="#" class="button button-primary ovic-cloneable-add">' . $this->field['button_title'] . '</a>';
			echo $this->element_after();
		}
	}
}
