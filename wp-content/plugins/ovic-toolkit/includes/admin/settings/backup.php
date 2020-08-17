<?php
if ( !class_exists( 'Ovic_Settings_Backup' ) ) {
	class  Ovic_Settings_Backup
	{
		public $key = 'ovic_settings_backup';

		public function __construct()
		{
			add_filter( 'ovic_registered_settings', array( $this, 'options' ), 100, 1 );
			add_filter( 'ovic_settings_print_option_page', array( $this, 'ovic_settings_print_option_page' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'export' ) );
			add_action( 'admin_init', array( $this, 'import' ) );
		}

		public function options( $options )
		{
			$options['ovic_settings_backup'] = array(
				'id'         => 'ovic_settings_backup', //id used as tab page slug, must be unique
				'title'      => __( 'Import/Export', 'ovic-toolkit' ),
				'show_names' => true,
				'sections'   => array(
					'settings_backup' => array(
						'id'    => $this->key,
						'title' => __( 'Import/Export', 'ovic-toolkit' ),
					),
				),
			);

			return $options;
		}

		function ovic_settings_print_option_page( $html, $active_tab, $active_section )
		{
			if ( $active_tab == 'ovic_settings_backup' ) {
				ob_start();
				?>
                <h3 class="title"><?php esc_html_e( 'Export Options', 'ovic-toolkit' ); ?></h3>
                <p><?php echo __( 'Click below to generate a <b>.txt</b> file for all settings.', 'ovic-toolkit' ); ?></p>
                <form action="" method="post">
                    <input type="hidden" name="ovic_action" value="export_options">
                    <button class="button button-primary"><?php esc_html_e( 'Export', 'ovic-toolkit' ); ?></button>
                </form>

                <h3 class="title"><?php esc_html_e( 'Import Options', 'ovic-toolkit' ); ?></h3>
                <form action="" method="post">
                    <input type="hidden" name="ovic_action" value="import_options">
                    <p>
                        <textarea style="display: block; width: 600px;" name="import_options_data" cols="30"
                                  rows="10"></textarea>
                    </p>
                    <button class="button button-primary"><?php esc_html_e( 'Import', 'ovic-toolkit' ); ?></button>
                </form>
				<?php
				$html = ob_get_clean();
			}

			return $html;
		}

		public function export()
		{
			if ( isset( $_POST['ovic_action'] ) && $_POST['ovic_action'] == 'export_options' ) {
				// Build filename
				// Single Site: yoursite.com-widgets.wie
				// Multisite: site.multisite.com-widgets.wie or multisite.com-site-widgets.wie.
				$site_url = site_url( '', 'http' );
				$site_url = trim( $site_url, '/\\' ); // Remove trailing slash.
				$filename = str_replace( 'http://', '', $site_url ); // Remove http://.
				$filename = str_replace( array( '/', '\\' ), '-', $filename ); // Replace slashes with - .
				$filename .= '-settings.txt'; // Append.
				$filename = $filename;
				// Generate export file contents.
				$file_contents = $this->generate_export_data();
				$filesize      = strlen( $file_contents );
				// Headers to prompt "Save As".
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . $filesize );
				// Clear buffering just in case.
				// @codingStandardsIgnoreLine
				@ob_end_clean();
				flush();
				// Output file contents.
				// @todo export or verify the output data. Or simply ignore the line.
				echo $file_contents;
				// Stop execution.
				exit;
			}
		}

		public function generate_export_data()
		{
			$data        = array();
			$all_options = Ovic_Settings::option_fields();
			if ( !empty( $all_options ) ) {
				foreach ( $all_options as $tabs ) {
					if ( !empty( $tabs ) && !empty( $tabs['sections'] ) ) {
						foreach ( $tabs['sections'] as $key => $section ) {
							$value = get_option( $key, false );
							if ( !empty( $value ) ) {
								$data[$key] = $value;
							}
						}
					}
				}
			}

			return base64_encode( wp_json_encode( $data ) );
		}

		public function import()
		{
			if ( isset( $_POST['ovic_action'] ) && $_POST['ovic_action'] == 'import_options' ) {
				$import_options_data = $_POST['import_options_data'];
				self::import_data( $import_options_data );
			}
		}

		public static function import_data( $import_options_data )
		{
			if ( $import_options_data != "" ) {
				$data = base64_decode( $import_options_data );
				$data = json_decode( $data, true );
				if ( !empty( $data ) ) {
					foreach ( $data as $key => $value ) {
						update_option( $key, $value );
					}
				}
			}
		}
	}

	new Ovic_Settings_Backup();
}