<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Ace Editor
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_ace_editor' ) ) {
	class OVIC_Field_ace_editor extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			$editor_id = $this->field['id'];
			$defaults  = array(
				'theme'           => 'ace/theme/monokai',
				'mode'            => 'ace/mode/javascript',
				'showGutter'      => false,
				'showPrintMargin' => false,
			);
			$options   = ( !empty( $this->field['options'] ) ) ? $this->field['options'] : array();
			$options   = json_encode( wp_parse_args( $options, $defaults ) );
			echo $this->element_before();
			echo '<div class="ovic-ace-editor-wrapper">';
			echo '<div id="ovic-ace-' . $editor_id . '" class="ovic-ace-editor"></div>';
			echo '</div>';
			echo '<textarea class="ovic-ace-editor-textarea hidden" name="' . $this->element_name() . '"' . $this->element_attributes() . '>' . $this->element_value() . '</textarea>';
			echo '<textarea class="ovic-ace-editor-options hidden">' . $options . '</textarea>';
			echo $this->element_after();
		}

		public static function enqueue()
		{
			if ( !wp_script_is( 'ace-editor', 'enqueued' ) ) {
				wp_enqueue_script( 'ace-editor', '//cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js', array( 'jquery' ), '1.3.3', true );
			}
		}
	}
}
