<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Select Preview
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_select_preview' ) ) {
	class OVIC_Field_select_preview extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '' )
		{
			parent::__construct( $field, $value, $unique );
		}

		public function output()
		{
			echo $this->element_before();
			if ( isset( $this->field['options'] ) ) {
				echo '<div class="container-select_preview">';
				$options    = $this->field['options'];
				$class      = $this->element_class();
				$options    = ( is_array( $options ) ) ? $options : array_filter( $this->element_data( $options ) );
				$extra_name = ( isset( $this->field['attributes']['multiple'] ) ) ? '[]' : '';
				$chosen_rtl = ( is_rtl() && strpos( $class, 'chosen' ) ) ? 'chosen-rtl' : '';
				echo '<select name="' . $this->element_name( $extra_name ) . '"' . $this->element_class( $chosen_rtl ) . $this->element_attributes() . ' class="ovic_select_preview">';
				echo ( isset( $this->field['default_option'] ) ) ? '<option value="">' . $this->field['default_option'] . '</option>' : '';
				if ( !empty( $options ) ) {
					foreach ( $options as $key => $value ) {
						echo '<option data-preview="' . $value['preview'] . '" value="' . $key . '" ' . $this->checked( $this->element_value(), $key, 'selected' ) . '>' . $value['title'] . '</option>';
					}
				}
				echo '</select>';
				$url = '';
				if ( isset( $this->field['options'][$this->value]['preview'] ) ) {
					$url = $this->field['options'][$this->value]['preview'];
				}
				echo '<div class="image-preview" style="margin-top:10px;display:inline-block;width:100%;"><img src="' . esc_url( $url ) . '" alt="' . get_the_title() . '"></div>';
				echo "</div>";
			}
			echo $this->element_after();
		}

		public static function enqueue()
		{
			$ovic_uri = OVIC_OPTIONS_URL;
			if ( is_ssl() ) {
				$ovic_uri = str_replace( 'http://', 'https://', OVIC_OPTIONS_URL );
			}
			if ( !wp_script_is( 'select_preview', 'enqueued' ) ) {
				wp_enqueue_script( 'select_preview', $ovic_uri . '/fields/select_preview/select_preview.js', array( 'jquery' ), '1.0.0', true );
			}
		}
	}
}