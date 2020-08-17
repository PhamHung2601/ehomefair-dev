<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Upload
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_upload' ) ) {
	class OVIC_Field_upload extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			echo $this->element_before();
			$value = $this->element_value();
			$upload_type  = ( !empty( $this->field['settings']['upload_type'] ) ) ? $this->field['settings']['upload_type'] : '';
			$button_title = ( !empty( $this->field['settings']['button_title'] ) ) ? $this->field['settings']['button_title'] : __( 'Upload', 'ovic-toolkit' );
			$frame_title  = ( !empty( $this->field['settings']['frame_title'] ) ) ? $this->field['settings']['frame_title'] : __( 'Upload', 'ovic-toolkit' );
			$insert_title = ( !empty( $this->field['settings']['insert_title'] ) ) ? $this->field['settings']['insert_title'] : __( 'Use Image', 'ovic-toolkit' );
			if ( !empty( $this->field['preview'] ) ) {
				$exts   = array( 'jpg', 'gif', 'png', 'svg', 'jpeg' );
				$exp    = explode( '.', $value );
				$ext    = ( !empty( $exp ) ) ? end( $exp ) : '';
				$image  = ( !empty( $value ) && in_array( $ext, $exts ) ) ? $value : '';
				$hidden = ( empty( $value ) || !in_array( $ext, $exts ) ) ? ' hidden' : '';
				echo '<div class="ovic-image-preview' . $hidden . '">';
				echo '<div class="ovic-image-inner"><i class="fa fa-times ovic-image-remove"></i><img src="' . $image . '" alt="preview" /></div>';
				echo '</div>';
			}
			echo '<div class="ovic-table">';
			echo '<div class="ovic-table-cell"><input type="text" name="' . $this->element_name() . '" value="' . $value . '"' . $this->element_class() . $this->element_attributes() . '/></div>';
			echo '<div class="ovic-table-cell"><a href="#" class="button ovic-button" data-frame-title="' . $frame_title . '" data-upload-type="' . $upload_type . '" data-insert-title="' . $insert_title . '">' . $button_title . '</a></div>';
			echo '</div>';
			echo $this->element_after();
		}
	}
}
