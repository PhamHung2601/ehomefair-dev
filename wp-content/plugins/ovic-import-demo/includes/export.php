<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}
/**
 * Define replace class export.
 */
if ( !class_exists( 'Ovic_Export_Data' ) ) {
	class Ovic_Export_Data
	{
		public static $file_path = '';
		public static $file_url  = '';

		public function __construct()
		{
			self::$file_path = trailingslashit( get_template_directory() ) . 'importer';
			self::$file_url  = trailingslashit( get_template_directory_uri() ) . 'importer';

			/* ENQUEUE SCRIPT */
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			/* EXPORT DATA IMPORT */
			add_action( 'wp_ajax_ovic_export_data', array( $this, 'export_data' ) );

			/* UPDATE GUID ATTACHMENT */
			add_action( 'wp_ajax_ovic_guid_attachment', array( $this, 'guid_attachment' ) );

			/* DOWNLOAD EXPORT DATA */
			add_action( 'init', array( $this, 'download_export_data' ) );
		}

		public function scripts( $preflix )
		{
			if ( $preflix == 'ovic-plugins_page_ovic-import' ) {
				$require_plugins = class_exists( 'TGM_Plugin_Activation' ) ? TGM_Plugin_Activation::$instance->plugins : array();
				wp_enqueue_script(
					'ovic-export',
					OVIC_IMPORT_PLUGIN_URL . '/assets/export.js',
					'jquery',
					OVIC_IMPORT_VERSION,
					true
				);
				wp_localize_script(
					'ovic-export',
					'ovic_export_params',
					array(
						'plugins' => $require_plugins,
					)
				);
			}
		}

		public static function export_button()
		{
			?>
            <div class="export-demo">
                <div class="alert-export"></div>
                <label>
                    <button class="update-guid-attachment button button-primary">Update Guid Attachment</button>
                </label>
                <label>
                    <button class="create-export-data button button-primary">Create Import Data</button>
                </label>
                <label>
                    <input type="text" name="theme_option" class="theme-option" placeholder="Theme option key"
                           value="_ovic_customize_options">
                </label>
                <label>
                    <input type="checkbox" name="download_export" class="download-export" value="0">
                    Download Export
                </label>
                <span class="spinner" style="margin: 0 10px;"></span>
            </div>
			<?php
		}

		public function guid_attachment()
		{
			$response = array(
				'message' => '',
				'success' => 'no',
			);

			$attachments = get_posts(
				array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
				)
			);

			$count = 0;

			if ( !empty( $attachments ) ) {
				global $wpdb;

				foreach ( $attachments as $attachment ) {
					$guid = wp_get_attachment_url( $attachment->ID );
					$wpdb->query(
						$wpdb->prepare( "UPDATE $wpdb->posts SET guid = %s WHERE post_type = 'attachment' AND ID = %s",
							$guid,
							$attachment->ID
						)
					);

					$count++;
				}
			}

			if ( $count > 0 ) {
				$response['message'] = 'Done';
				$response['success'] = 'yes';
			}

			wp_send_json( $response );
		}

		public function export_data()
		{
			$response = array(
				'message'  => '',
				'redirect' => '',
				'success'  => 'no',
			);

			if ( !empty( $_POST['download'] ) && $_POST['download'] == 'yes' ) {
				$upload_dir  = wp_upload_dir();
				$folder_name = 'ovic-import-data';

				self::$file_path = $upload_dir['basedir'] . '/' . $folder_name . '/';
				self::$file_url  = $upload_dir['url'] . '/' . $folder_name . '/';

				$response['redirect'] = add_query_arg(
					array(
						'_export'  => 'download',
						'_name'    => $folder_name,
						'_basedir' => $upload_dir['basedir'],
						'_wpnonce' => wp_create_nonce( 'download-export-data' ),
					),
					admin_url( 'admin-ajax.php' )
				);
			}

			// Raise memory limit.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit();
			}
			// Disable error reporting.
			if ( function_exists( 'error_reporting' ) ) {
				error_reporting( 0 );
			}
			// Do not limit execution time.
			if ( function_exists( 'set_time_limit' ) ) {
				set_time_limit( 0 );
			}

			/* EXPORT XML */
			self::export_xml();

			/* EXPORT OPTIONS */
			if ( !empty( $_POST['key'] ) ) {
				self::export_options( $_POST['key'] );
			}

			/* EXPORT WIDGETS */
			self::export_widgets();

			/* EXPORT REVSLIDER */
			self::export_revslider();

			/* EXPORT CLASS */
			if ( !empty( $_POST['key'] ) ) {
				self::export_class_importer( $_POST['key'] );
			}

			/* EXPORT CLASS */
			if ( !empty( $_POST['key'] ) ) {
				$plugins = !empty( $_POST['plugins'] ) ? $_POST['plugins'] : array();

				self::export_class_database( $_POST['key'], $plugins );
			}

			$response['message'] = 'Success';
			$response['success'] = 'yes';

			wp_send_json( $response );
		}

		public function download_export_data()
		{
			if ( !empty( $_REQUEST['_export'] ) && !empty( $_REQUEST['_wpnonce'] ) && $_REQUEST['_export'] == 'download' ) {
				$basedir     = !empty( $_REQUEST['_basedir'] ) ? $_REQUEST['_basedir'] : '';
				$folder_name = !empty( $_REQUEST['_name'] ) ? $_REQUEST['_name'] : '';

				if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'download-export-data' ) ) {
					self::download_file( $basedir, $folder_name,
						false
					);
				}
			}
		}

		public function export_xml()
		{
			/** Load WordPress export API */
			if ( !function_exists( 'export_wp' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/export.php' );
			}
			ob_start();

			export_wp();

			$file = ob_get_clean();

			$path = self::$file_path . '/data/content.xml';

			/* create file */
			$this->filesystem( 'put', $path, $file );
		}

		public function export_options( $key )
		{
			ob_start();

			echo json_encode( get_option( wp_unslash( $key ) ) );

			$file = ob_get_clean();

			$path = self::$file_path . '/data/theme-options.json';

			/* create file */
			$this->filesystem( 'put', $path, $file );
		}

		public function export_widgets()
		{
			ob_start();

			// Generate export file contents.
			echo self::wie_generate_export_data();

			$file = ob_get_clean();

			$path = self::$file_path . '/data/widgets.wie';

			/* create file */
			$this->filesystem( 'put', $path, $file );
		}

		public function export_revslider()
		{
			if ( class_exists( 'RevSlider' ) ) {
				$rev     = new RevSlider();
				$sliders = $rev->getArrSliders();
				if ( !empty( $sliders ) ) {
					foreach ( $sliders as $slider ) {
						if ( defined( 'RS_REVISION' ) && version_compare( RS_REVISION, '6.0.0', '>=' ) ) {
							if ( !class_exists( 'OvicSliderSliderExport' ) ) {
								include OVIC_IMPORT_PLUGIN_DIR . 'includes/classes/revslider-export-v6.php';
							}
							$file_path = self::$file_path . "/revsliders/";
							if ( !file_exists( $file_path ) ) {
								$file_path = self::prepare_directory( $file_path );
							}
							$export_path_zip = $file_path . "{$slider->alias}.zip";
							$export_url_zip  = self::$file_url . "/revsliders/{$slider->alias}.zip";

							$export = new OvicSliderSliderExport( $export_path_zip, $export_url_zip );
							$export->export_slider( $slider->id );
						} else {
							if ( !class_exists( 'OvicRevSlider' ) ) {
								include OVIC_IMPORT_PLUGIN_DIR . 'includes/classes/revslider-export.php';
							}
							$export = new OvicRevSlider();
							$export->initByID( $slider->getID() );
							$export->OvicExportSlider();
						}
					}
				}
			}
		}

		public function export_class_database( $option_key, $require_plugins )
		{
			$file_path = OVIC_IMPORT_PLUGIN_DIR . 'includes/classes/database.php';

			$home_urls = get_home_url();
			$home_url  = str_replace( 'https', 'http', get_home_url() );

			$parse_url    = parse_url( $home_urls );
			$urls         = explode( '.', $parse_url['host'] );
			$site_pattern = 'https?(%3A|:)[%2F\\\\/]+';
			foreach ( $urls as $key => $url ) {
				if ( $key == 0 ) {
					$site_pattern .= "(rc|demo|{$url})";
				} else {
					$site_pattern .= "\.{$url}";
				}
			}

			$plugins = self::require_plugins( $require_plugins );

			$content = $this->filesystem( 'get', $file_path );
			$find    = array(
				"{home_urls}",
				"{home_url}",
				"{site_pattern}",
				"{option_key}",
				"/*plugins*/",
			);
			$replace = array(
				$home_urls,
				$home_url,
				$site_pattern,
				$option_key,
				$plugins,
			);
			$content = str_replace( $find, $replace, $content );

			ob_start();

			echo $content;

			$file = ob_get_clean();

			$path = self::$file_path . '/importer-db.php';

			/* create file */
			$this->filesystem( 'put', $path, $file );

			/* create sample data */
			Ovic_Import_Database_Sample_Data::generate_sample_data(
				self::$file_path . '/data/',
				'sample-data'
			);
		}

		public function export_class_importer( $option_key )
		{
			$file_path = OVIC_IMPORT_PLUGIN_DIR . 'includes/classes/importer.php';

			$api_key   = '';
			$form_id   = 0;
			$home_urls = get_home_url();
			$home_url  = str_replace( 'https', 'http', get_home_url() );
			if ( function_exists( '_mc4wp_load_plugin' ) ) {
				$api_key = mc4wp_get_api_key();
				$form_id = get_option( 'mc4wp_default_form_id' );
			}
			$attributes    = self::wc_get_attribute_taxonomies();
			$menu_location = self::menu_location();

			$woo_catalog  = get_option( 'woocommerce_thumbnail_image_width', 300 );
			$woo_single   = get_option( 'woocommerce_single_image_width', 600 );
			$ratio_width  = get_option( 'woocommerce_thumbnail_cropping_custom_width', 1 );
			$ratio_height = get_option( 'woocommerce_thumbnail_cropping_custom_height', 1 );
			$woo_ratio    = "{$ratio_width}:{$ratio_height}";

			$content = $this->filesystem( 'get', $file_path );
			$find    = array(
				"{home_urls}",
				"{home_url}",
				"{api_key}",
				"{form_id}",
				"{woo_catalog}",
				"{woo_single}",
				"{woo_ratio}",
				"{option_key}",
				"/*attributes*/",
				"/*menu_location*/",
			);
			$replace = array(
				$home_urls,
				$home_url,
				$api_key,
				$form_id,
				$woo_catalog,
				$woo_single,
				$woo_ratio,
				$option_key,
				$attributes,
				$menu_location,
			);
			$content = str_replace( $find, $replace, $content );

			ob_start();

			echo $content;

			$file = ob_get_clean();

			$path = self::$file_path . '/importer.php';

			/* create file */
			$this->filesystem( 'put', $path, $file );
		}

		public function menu_location()
		{
			$menus     = '';
			$locations = get_nav_menu_locations();
			if ( !empty( $locations ) ) {
				foreach ( $locations as $slug => $location ) {
					$menu  = wp_get_nav_menu_object( $location );
					$menus .= "\n               '{$slug}' => '{$menu->name}',";
				}
			}

			return $menus;
		}

		public function require_plugins( $require_plugins )
		{
			$plugin_html = "array(\n";

			$not_allows = array(
				'ovic-demo',
				'ovic-import',
				'loco-translate',
				'ovic-import-demo',
				'regenerate-thumbnails',
				'widget-importer-exporter',
				'envato-theme-check-master',
			);
			if ( !empty( $require_plugins ) ) {
				foreach ( $require_plugins as $plugin ) {
					if ( !in_array( $plugin['slug'], $not_allows ) ) {
						$plugin_html .= "               array(\n";
						$plugin_html .= "                   'name'=>'{$plugin['name']}',\n";
						$plugin_html .= "                   'slug'=>'{$plugin['slug']}',\n";
						$plugin_html .= "                   'source'=>'{$plugin['source']}',\n";
						$plugin_html .= "                   'source_type'=>'{$plugin['source_type']}',\n";
						$plugin_html .= "                   'file_path'=>'{$plugin['file_path']}',\n";
						$plugin_html .= "               ),\n";
					}
				}
			}

			$plugin_html .= ")";

			return $plugin_html;
		}

		/**
		 * Get attribute
		 * @return string
		 */
		public function wc_get_attribute_taxonomies()
		{
			$attributes_html = "array(\n";

			if ( class_exists( 'WooCommerce' ) ) {
				global $woocommerce;

				if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
					$attributes = wc_get_attribute_taxonomies();
				} else {
					$attributes = $woocommerce->get_attribute_taxonomies();
				}
				if ( !empty( $attributes ) ) {
					foreach ( $attributes as $attribute ) {
						$attributes_html .= "               array(\n";
						$attributes_html .= "                   'attribute_name'	    =>'{$attribute->attribute_name}',\n";
						$attributes_html .= "                   'attribute_label'	    =>'{$attribute->attribute_label}',\n";
						$attributes_html .= "                   'attribute_type'	    =>'{$attribute->attribute_type}',\n";
						$attributes_html .= "                   'attribute_orderby'    =>'{$attribute->attribute_orderby}',\n";
						$attributes_html .= "                   'attribute_public'	    =>'{$attribute->attribute_public}',\n";
						$attributes_html .= "               ),\n";
					}
				}
			}

			$attributes_html .= "           )";

			return $attributes_html;
		}

		/**
		 * Available widgets
		 *
		 * Gather site's widgets into array with ID base, name, etc.
		 * Used by export and import functions.
		 *
		 * @return array Widget information
		 * @global array $wp_registered_widget_updates
		 * @since 0.4
		 */
		function wie_available_widgets()
		{
			global $wp_registered_widget_controls;

			$widget_controls = $wp_registered_widget_controls;

			$available_widgets = array();

			foreach ( $widget_controls as $widget ) {
				// No duplicates.
				if ( !empty( $widget['id_base'] ) && !isset( $available_widgets[$widget['id_base']] ) ) {
					$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
					$available_widgets[$widget['id_base']]['name']    = $widget['name'];
				}
			}

			return apply_filters( 'wie_available_widgets', $available_widgets );
		}

		/**
		 * Generate export data
		 *
		 * @return string Export file contents
		 * @since 0.1
		 */
		public function wie_generate_export_data()
		{
			// Get all available widgets site supports.
			$available_widgets = $this->wie_available_widgets();

			// Get all widget instances for each widget.
			$widget_instances = array();

			// Loop widgets.
			foreach ( $available_widgets as $widget_data ) {
				// Get all instances for this ID base.
				$instances = get_option( 'widget_' . $widget_data['id_base'] );

				// Have instances.
				if ( !empty( $instances ) ) {
					// Loop instances.
					foreach ( $instances as $instance_id => $instance_data ) {
						// Key is ID (not _multiwidget).
						if ( is_numeric( $instance_id ) ) {
							$unique_instance_id                    = $widget_data['id_base'] . '-' . $instance_id;
							$widget_instances[$unique_instance_id] = $instance_data;
						}
					}
				}
			}

			// Gather sidebars with their widget instances.
			$sidebars_widgets          = get_option( 'sidebars_widgets' );
			$sidebars_widget_instances = array();
			foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
				// Skip inactive widgets.
				if ( 'wp_inactive_widgets' === $sidebar_id ) {
					continue;
				}

				// Skip if no data or not an array (array_version).
				if ( !is_array( $widget_ids ) || empty( $widget_ids ) ) {
					continue;
				}

				// Loop widget IDs for this sidebar.
				foreach ( $widget_ids as $widget_id ) {
					// Is there an instance for this widget ID?
					if ( isset( $widget_instances[$widget_id] ) ) {
						// Add to array.
						$sidebars_widget_instances[$sidebar_id][$widget_id] = $widget_instances[$widget_id];
					}
				}
			}

			// Filter pre-encoded data.
			$data = apply_filters( 'wie_unencoded_export_data', $sidebars_widget_instances );

			// Encode the data for file contents.
			$encoded_data = wp_json_encode( $data );

			// Return contents.
			return apply_filters( 'wie_generate_export_data', $encoded_data );
		}

		public static function filesystem( $method, $path, $data = '' )
		{
			global $wp_filesystem;
			$content = '';
			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}
			if ( $method == 'put' ) {
				$path = self::prepare_directory( $path );

				if ( $wp_filesystem->put_contents( $path, $data, FS_CHMOD_FILE ) ) {
					$content = esc_html__( 'Success', 'ovic-import' );
				} else {
					$content = esc_html__( 'Error saving file!', 'ovic-import' );
				}
			} elseif ( $method == 'get' ) {
				if ( file_exists( $path ) ) {
					$content = $wp_filesystem->get_contents( $path );
				}
			} elseif ( $method == 'del' ) {
				if ( file_exists( $path ) ) {
					$wp_filesystem->delete( $path, true );
				}
			}

			return $content;
		}

		/**
		 * Prepare a directory.
		 *
		 * @param string $path Directory path.
		 *
		 * @return  mixed
		 */
		public static function prepare_directory( $path )
		{
			if ( !is_dir( $path ) ) {
				$results = explode( '/', str_replace( '\\', '/', $path ) );
				$path    = array();
				while ( count( $results ) ) {
					$path[] = current( $results );
					// Shift paths.
					array_shift( $results );
				}
				$file_name = end( $path );
				array_pop( $path );
			}

			// Re-build target directory.
			$path = is_array( $path ) ? implode( '/', $path ) : $path;

			if ( !wp_mkdir_p( $path ) ) {
				return false;
			}

			if ( !is_dir( $path ) ) {
				return false;
			}

			return $path . '/' . $file_name;
		}

		/**
		 * download_file.
		 *
		 * @return string
		 * @since   1.1.0
		 * @version 1.3.0
		 */
		function download_file( $file_dir, $file_name, $add_main_dir )
		{
			$zip_file_name = 'importer-' . date( 'Y-m-d' ) . '.zip';
			$zip_file_path = tempnam( sys_get_temp_dir(), $zip_file_name );
			$file_path     = $file_dir . '/' . $file_name;
			$exclude_path  = ( $add_main_dir ? $file_dir : $file_path );
			$args          = array(
				'zip_file_path' => $zip_file_path,
				'exclude_path'  => $exclude_path,
			);
			$files         = $this->get_files( $file_path );
			if ( $this->create_zip( $args, $files ) ) {
				// export file zip
				$this->send_file( $zip_file_name, $zip_file_path );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * get_files.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 */
		function get_files( $file_path )
		{
			$files       = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $file_path ), RecursiveIteratorIterator::LEAVES_ONLY );
			$files_paths = array();
			foreach ( $files as $name => $file ) {
				if ( !$file->isDir() ) {
					$file_path     = str_replace( '\\', '/', $file->getRealPath() );
					$files_paths[] = $file_path;
				}
			}

			return $files_paths;
		}

		/**
		 * create_zip.
		 *
		 * @version 1.3.0
		 * @since   1.3.0
		 * @todo    (maybe) add option to manually select first/main `$zip_library`
		 * @todo    (maybe) add fully autonomous PHP Zip library (e.g. https://github.com/alexcorvi/php-zip)
		 */
		function create_zip( $args, $files )
		{
			$zip_library = ( class_exists( 'ZipArchive' ) ? 'ziparchive' : 'pclzip' );
			switch ( $zip_library ) {
				case 'pclzip':
					return $this->create_zip_pclzip( $args, $files );
				default: // 'ziparchive':
					return $this->create_zip_ziparchive( $args, $files );
			}
		}

		/**
		 * create_zip_ziparchive.
		 *
		 * @version 1.4.1
		 * @since   1.3.0
		 * @todo    [dev] (maybe) check `new ZipArchive`, `$zip->addFile`, `$zip->close` for errors
		 */
		function create_zip_ziparchive( $args, $files )
		{
			$zip = new ZipArchive();
			if ( true !== ( $result = $zip->open( $args['zip_file_path'], ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) ) {
				$this->last_error = sprintf( __( '%s can not open a new zip archive (error code %s).', 'ovic-import' ),
					'<code>ZipArchive</code>', '<code>' . $result . '</code>'
				);

				return false;
			}
			$exclude_from_relative_path = strlen( $args['exclude_path'] ) + 1;
			foreach ( $files as $file_path ) {
				$zip->addFile( $file_path, substr( $file_path, $exclude_from_relative_path ) );
			}
			$zip->close();

			return true;
		}

		/**
		 * create_zip_pclzip.
		 *
		 * @version 1.4.1
		 * @since   1.3.0
		 * @todo    [dev] (maybe) check `new PclZip` for errors
		 * @see     http://www.phpconcept.net/pclzip
		 */
		function create_zip_pclzip( $args, $files )
		{
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			$zip = new PclZip( $args['zip_file_path'] );
			if ( 0 == $zip->create( $files, PCLZIP_OPT_REMOVE_PATH, $args['exclude_path'] ) ) {
				$this->last_error = sprintf( '%s %s.', '<code>PclZip</code>', $zip->errorInfo( true ) );

				return false;
			}

			return true;
		}

		/**
		 * send_file.
		 *
		 * @version 1.4.0
		 * @since   1.3.0
		 */
		function send_file( $zip_file_name, $zip_file_path )
		{
			// required for IE
			if ( ini_get( 'zlib.output_compression' ) ) {
				ini_set( 'zlib.output_compression', 'Off' );
			}

			header( 'Pragma: public' );    // required
			header( 'Expires: 0' );        // no cache
			header( 'Content-Type: application/force-download' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Type: application/download' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Disposition: attachment; filename=' . urlencode( $zip_file_name ) );
			header( 'Content-Description: File Transfer' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $zip_file_path ) ) . ' GMT' );
			header( 'Content-Length: ' . filesize( $zip_file_path ) );

			flush();
			if ( false !== ( $fp = fopen( $zip_file_path, 'rb' ) ) ) {
				while ( !feof( $fp ) ) {
					echo fread( $fp, 65536 );
					flush();
				}
				fclose( $fp );
				// clear temp folder
				unlink( $zip_file_path );
				die();
			} else {
				die( __( 'Unexpected error', 'ovic-import' ) );
			}
		}
	}

	new Ovic_Export_Data();
}