<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Get icons from admin ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_get_icons' ) ) {
	function ovic_get_icons()
	{
		do_action( 'ovic_load_icons_before' );
		$jsons = array_merge(
			apply_filters( 'ovic_load_icon_json', array() ),
			glob( OVIC_OPTIONS_DIR . '/fields/icon/*.json' )
		);
		if ( !empty( $jsons ) ) {
			foreach ( $jsons as $path ) {
				if ( file_exists( $path ) ) {
					$object = ovic_get_icon_fonts( $path );
				} else {
					$object = (object)$path;
				}
				if ( is_object( $object ) ) {
					echo ( count( $jsons ) >= 2 ) ? '<h4 class="ovic-icon-title">' . $object->name . '</h4>' : '';
					foreach ( $object->icons as $icon ) {
						echo '<a class="ovic-icon-tooltip" data-ovic-icon="' . $icon . '" title="' . $icon . '"><span class="ovic-icon ovic-selector"><i class="' . $icon . '"></i></span></a>';
					}
				} else {
					echo '<h4 class="ovic-icon-title">' . __( 'Error! Can not load json file.', 'ovic-toolkit' ) . '</h4>';
				}
			}
		}
		do_action( 'ovic_load_icons_after' );
		die();
	}

	add_action( 'wp_ajax_ovic-get-icons', 'ovic_get_icons' );
}
/**
 *
 * Export options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_export_options' ) ) {
	function ovic_export_options()
	{
		if ( isset( $_GET['export'] ) && isset( $_GET['wpnonce'] ) && wp_verify_nonce( $_GET['wpnonce'], 'ovic_backup' ) ) {
			header( 'Content-Type: plain/text' );
			header( 'Content-disposition: attachment; filename=backup-options-' . gmdate( 'd-m-Y' ) . '.txt' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			echo ovic_encode_string( get_option( $_GET['export'] ) );
		}
		die();
	}

	add_action( 'wp_ajax_ovic-export-options', 'ovic_export_options' );
}
/**
 *
 * Import options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_import_options' ) ) {
	function ovic_import_options()
	{
		if ( isset( $_POST['unique'] ) && !empty( $_POST['value'] ) && isset( $_POST['wpnonce'] ) && wp_verify_nonce( $_POST['wpnonce'], 'ovic_backup' ) ) {
			update_option( $_POST['unique'], ovic_decode_string( $_POST['value'] ) );
		}
		die();
	}

	add_action( 'wp_ajax_ovic-import-options', 'ovic_import_options' );
}
/**
 *
 * Reset options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_reset_options' ) ) {
	function ovic_reset_options()
	{
		if ( isset( $_POST['unique'] ) && isset( $_POST['wpnonce'] ) && wp_verify_nonce( $_POST['wpnonce'], 'ovic_backup' ) ) {
			delete_option( $_POST['unique'] );
		}
		die();
	}

	add_action( 'wp_ajax_ovic-reset-options', 'ovic_reset_options' );
}
/**
 *
 * Set icons for wp dialog
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_set_icons' ) ) {
	function ovic_set_icons()
	{
		?>
        <div id="ovic-modal-icon" class="ovic-modal ovic-modal-icon">
            <div class="ovic-modal-table">
                <div class="ovic-modal-table-cell">
                    <div class="ovic-modal-overlay"></div>
                    <div class="ovic-modal-inner">
                        <div class="ovic-modal-title">
							<?php _e( 'Add Icon', 'ovic-toolkit' ); ?>
                            <div class="ovic-modal-close ovic-icon-close"></div>
                        </div>
                        <div class="ovic-modal-header ovic-text-center">
                            <input type="text" placeholder="<?php _e( 'Search a Icon...', 'ovic-toolkit' ); ?>"
                                   class="ovic-icon-search"/>
                        </div>
                        <div class="ovic-modal-content">
                            <div class="ovic-icon-loading"><?php _e( 'Loading...', 'ovic-toolkit' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	add_action( 'admin_footer', 'ovic_set_icons' );
	add_action( 'customize_controls_print_footer_scripts', 'ovic_set_icons' );
}
