<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Field: Backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC_Field_backup' ) ) {
	class OVIC_Field_backup extends OVIC_Fields
	{
		public function __construct( $field, $value = '', $unique = '', $where = '' )
		{
			parent::__construct( $field, $value, $unique, $where );
		}

		public function output()
		{
			$nonce   = wp_create_nonce( 'ovic_backup' );
			$options = get_option( $this->unique );
			$export  = esc_url( add_query_arg( array(
				'action'  => 'ovic-export-options',
				'export'  => $this->unique,
				'wpnonce' => $nonce,
			), admin_url( 'admin-ajax.php' )
			)
			);
			if ( !empty( $options['_transient'] ) ) {
				unset( $options['_transient'] );
			}
			echo $this->element_before();
			echo '<textarea name="_nonce" class="ovic-import-data"></textarea>';
			echo '<a href="#" class="button button-primary ovic-confirm ovic-import-js">' . __( 'Import a Backup', 'ovic-toolkit' ) . '</a>';
			echo '<small>( ' . __( 'copy-paste your backup string here', 'ovic-toolkit' ) . ' )</small>';
			echo '<hr />';
			echo '<textarea name="_nonce" class="ovic-export-data" disabled="disabled">' . ovic_encode_string( $options ) . '</textarea>';
			echo '<a href="' . $export . '" class="button button-primary" target="_blank">' . __( 'Export and Download Backup', 'ovic-toolkit' ) . '</a>';
			echo '<hr />';
			echo '<a href="#" class="button button-primary ovic-warning-primary ovic-confirm ovic-reset-js">' . __( 'Reset All Options', 'ovic-toolkit' ) . '</a>';
			echo '<small class="ovic-text-warning">' . __( 'Please be sure for reset all of framework options.', 'ovic-toolkit' ) . '</small>';
			echo '<div class="ovic-data" data-unique="' . $this->unique . '" data-wpnonce="' . $nonce . '"></div>';
			echo $this->element_after();
		}
	}
}
