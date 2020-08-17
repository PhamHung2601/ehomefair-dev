<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once('Net/PH_SFTP.php');

if ( class_exists( 'WP_Importer' ) && !class_exists('WF_Shipment_Tracking_Importer') ) {

	class WF_Shipment_Tracking_Importer extends WP_Importer {

		var $id;
		var $file_url;
		var $import_page;
		var $delimiter;
		var $posts = array();
		var $imported;
		var $skipped;
		var $upload_dir;
		var $skip_allready_processed;

		/**
		 * __construct function.
		 */
		public function __construct() {

			$this->log = new WC_Logger();
			$this->import_page = 'import_shipment_tracking_csv';
			$this->skip_allready_processed = TRUE;
			// defining upload directory
			$this->upload_dir = 'wp-content/uploads/';
		}

		/**
		 * Registered callback function for the WordPress Importer
		 *
		 * Manages the three separate stages of the CSV import process
		 */
		public function dispatch() {

			$this->header();

			if ( ! empty( $_POST['delimiter'] ) )
				$this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );

			if ( ! $this->delimiter )
				$this->delimiter = ',';

			$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];

			switch ( $step ) {

				case 0:
				$this->greet();
				break;

				case 1:
				check_admin_referer( 'import-upload' );
				if ( $this->handle_upload() ) {

					if ( $this->id )
						$files[] = get_attached_file( $this->id );
					else{
						$files = is_array($this->file_url)?$this->file_url:array($this->file_url);
					}

					add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

					if ( function_exists( 'gc_enable' ) )
						gc_enable();

					@set_time_limit(0);
					@ob_flush();
					@flush();

					foreach($files as $file){
						$this->import( $file );
					}
				}
				break;
			}

			$this->footer();
		}

		/**
		 * format_data_from_csv function.
		 *
		 * @param mixed $data
		 * @param string $enc
		 * @return string
		 */
		public function format_data_from_csv( $data, $enc ) {
			return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		}

		/**
		 * import function.
		 *
		 * @param mixed $file
		 */
		function import( $file ) {

			global $wpdb;

			try{

				$this->log->add('shipment-tracking-import', __('--------------- Entered into Import ---------------', 'woocommerce-shipment-tracking'));

				$this->imported = $this->skipped = 0;

				// To save CSV Data in array 
				$this->orderTrackingCSV 	= array();
				$this->orderTrackingCSV 	= get_option( 'ph_server_side_order_creation_array', array() );
				$this->store_id 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );

				if (!is_file($file)) {

					echo '<p><strong>' . __('Unable to proceed:', 'woocommerce-shipment-tracking') . '</strong><br />';
					echo __('The file does not exist. Please go back and check your settings.', 'woocommerce-shipment-tracking') . '</p>';
					$this->log->add('shipment-tracking-import', __('Unable to proceed:File does not exist', 'woocommerce-shipment-tracking'));
					$this->footer();
					die();
				}

				if (!class_exists('WF_Tracking_Admin')) {
					include_once ( 'class-wf-tracking-admin.php' );
				}

				ini_set('auto_detect_line_endings', '1');

				// To support Import Shipment Tracking Details via XML Addon
				$file = apply_filters( 'ph_shipment_tracking_modify_file_content', $file );

				if (( $handle = fopen($file, "r") ) !== FALSE) {

					$this->log->add('shipment-tracking-import', __('Reading File...', 'woocommerce-shipment-tracking'));

					$header = fgetcsv($handle, 0, $this->delimiter);

					if (sizeof($header) == 4 || sizeof($header) == 5 || sizeof($header) == 6) {

						$this->log->add('shipment-tracking-import', __('CSV Validation Done', 'woocommerce-shipment-tracking'));

						$i = 1;

						while ( ( $row = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE ) {
							
							$order_id 	= isset($row[0]) ? trim($row[0]) : '';

							// To support the WooCommerce Sequential Order Number plugin - snippet required
							$order_id 	= apply_filters( 'xa_tracking_importer_order_id', $order_id);

							$carrier 				= isset($row[1]) ? trim($row[1]) : '';
							$trackingnumber 		= isset($row[2]) ? trim($row[2]) : '';
							$shippingdate 			= isset($row[3]) ? trim($row[3]) : '';
							$tracking_description 	= isset($row[4]) ? sanitize_textarea_field($row[4]) : null;
							$order_status 			= (isset($row[5]) && !empty(($row[5]))) ? trim($row[5]) : 'completed';
							$order_status 			= strtolower($order_status);

							$order = wc_get_order($order_id);

							if ( $order instanceof WC_Order && !empty($carrier) && !empty($trackingnumber) ) {

								$csv_processed 	= get_post_meta($order_id, '_tracking_csv_processed', true);

								if ( $csv_processed !== 1 && $this->skip_allready_processed ) {

									// Auto fill tracking info.
									$this->log->add('shipment-tracking-import', __("Processing Order Id ----------------- $order_id", 'woocommerce-shipment-tracking'));

									$message = Ph_Shipment_Tracking_Util::update_tracking_data($order_id, $trackingnumber, sanitize_title($carrier), WF_Tracking_Admin::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, $shippingdate, $tracking_description, true );

									update_post_meta( $order_id, '_tracking_csv_processed', 1 );
									update_post_meta( $order_id, WF_Tracking_Admin::TRACKING_MESSAGE_KEY, $message );

									$order->update_status( $order_status, __('PluginHive Shipment Tracking -', 'woocommerce-shipment-tracking') );

									echo '<br>' . __('Order #', 'woocommerce-shipment-tracking') . $order_id . __(' updated successfully.', 'woocommerce-shipment-tracking');

									// If Store Id exists, create imported tracking details as array to create server side Order using Cron
									// Cron will be running every 1 hour
									if( !empty($this->store_id) ) {

										$this->orderTrackingCSV[$order_id] = array();
										
										$this->orderTrackingCSV[$order_id] = array (
											'orderNum'		=> $order_id,
											'trackingNum' 	=> $trackingnumber,
											'carrier'		=> sanitize_title($carrier),
										);
									}
									
									$this->imported++;

								} else {

									$this->skipped++;
								}

							} else {

								$this->skipped++;
							}

							$i++;
						}

					} else {
						
						$this->log->add('shipment-tracking-import', __('There has been an error:The CSV is invalid', 'woocommerce-shipment-tracking'));

						echo '<p><strong>' . __('Sorry, there has been an error.', 'woocommerce-shipment-tracking') . '</strong><br />';
						echo __('The CSV is invalid.', 'woocommerce-shipment-tracking') . '</p>';

						$this->footer();
						die();
					}

					fclose($handle);
				}

				$this->log->add('shipment-tracking-import', __('Imported:' . $this->imported, 'woocommerce-shipment-tracking'));
				$this->log->add('shipment-tracking-import', __('Skipped:' . $this->skipped, 'woocommerce-shipment-tracking'));
				
				// Update option for to create Order at Server Level Using Store Id - Cron Job
				update_option( 'ph_server_side_order_creation_array', $this->orderTrackingCSV );

				// Show Result
				echo '<div class="updated settings-error below-h2"><p>' . sprintf(__('Import complete - imported <strong>%s</strong> Shipment Tracking and skipped <strong>%s</strong>.', 'woocommerce-shipment-tracking'), $this->imported, $this->skipped) . '
				</p></div>';

				$this->import_end();

			}catch(Exception $e){
				echo "Sorry, there has been an error: ".$e->getMessage()."\n";
			}
		}

		/**
		 * Performs post-import cleanup of files and the cache
		 */
		public function import_end() {
			echo '<p>' . __( 'All Done!', 'woocommerce-shipment-tracking' ) . '</p>';
			$this->log->add('shipment-tracking-import', __('--------------- All Done! ---------------', 'woocommerce-shipment-tracking'));
			do_action( 'import_end' );
		}

		/**
		 * Handles the CSV upload and initial parsing of the file to prepare for
		 * displaying author import options
		 *
		 * @return bool False if error uploading or invalid file, true otherwise
		 */
		public function handle_upload() {
			if($this->handle_ftp()){
				return true;
			}
			else if ( empty( $_POST['file_url'] ) ) {

				$file = wp_import_handle_upload();

				if ( isset( $file['error'] ) ) {
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'woocommerce-shipment-tracking' ) . '</strong><br />';
					echo esc_html( $file['error'] ) . '</p>';
					return false;
				}

				$this->id = (int) $file['id'];
			} else {

				$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

				if ( file_exists( ABSPATH . $_POST['file_url'] ) ) {

					$this->file_url = esc_attr( $_POST['file_url'] );

				} elseif ( !empty($document_root) && file_exists( $document_root.'/'.$_POST['file_url'] ) ) {

					$this->file_url = esc_attr( $_POST['file_url'] );

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'woocommerce-shipment-tracking' ) . '</strong></p>';
					return false;
				}
			}

			return true;
		}

		private function handle_ftp() {
			
			$enable_ftp_ie         	= !empty( $_POST['enable_ftp_ie'] ) ? true : false;

			if($enable_ftp_ie == false) return false;

			$ftp_or_sftp            = ! empty($_POST['ftp_or_sftp']) ? $_POST['ftp_or_sftp'] : '';
			$ftp_server				= ! empty( $_POST['ftp_server'] ) ? $_POST['ftp_server'] : '';
			$ftp_server_path		= ! empty( $_POST['ftp_server_path'] ) ? $_POST['ftp_server_path'] : '';
			$ftp_user				= ! empty( $_POST['ftp_user'] ) ? $_POST['ftp_user'] : '';
			$ftp_password           = ! empty( $_POST['ftp_password'] ) ? $_POST['ftp_password'] : '';
			$use_ftps         		= ! empty( $_POST['use_ftps'] ) ? true : false;
			$use_passive_mode        = ! empty( $_POST['use_passive_mode']) ? true : false;
			$ftp_port               = ! empty( $_POST['ftp_port'] ) ? $_POST['ftp_port'] : 21;
			$ftp_timeout            = ! empty( $_POST['ftp_timeout'] ) ? $_POST['ftp_timeout'] : '90';

			$settings = array(
				'ftp_or_sftp'       =>  $ftp_or_sftp,
				'ftp_server'        =>  $ftp_server,
				'ftp_user'          =>  $ftp_user,
				'ftp_password'      =>  $ftp_password,
				'use_ftps'          =>  $use_ftps,
				'ftp_port'          =>  $ftp_port,
				'use_passive_mode'   =>  $use_passive_mode,
				'ftp_timeout'       =>  $ftp_timeout,
				'enable_ftp_ie'     =>  $enable_ftp_ie,
				'ftp_server_path'   =>  $ftp_server_path
			);

			$local_file = 'wp-content/uploads/woocommerce-shipment-tracking-temp-import.csv';
			$server_file = $ftp_server_path;

			update_option( 'wf_shipment_tracking_importer_ftp', $settings );

			$error_message = "";
			$success = false;

			if( $ftp_or_sftp == 'sftp' ) {

				if( ! empty($ftp_server) ) {

					$sftp = new PH_Net_SFTP($ftp_server);
				} else {

					die( __( 'Please Provide FTP Host IP / Address .', 'woocommerce-shipment-tracking' ) );
				}

				if ( ! $sftp->login($ftp_user, $ftp_password )) {

					$error_message = __("Could not Connect to Server : ", 'woocommerce-shipment-tracking').$ftp_server.'<br/><br/><b>'.__( 'Possible Reasons :', 'woocommerce-shipment-tracking' ).'</b><br/><br />'.__( '1. Please select appropriate Protocol: FTP/SFTP.', 'woocommerce-shipment-tracking' ).'<br />'.__( '2. Server/Host address may be wrong . Server - ', 'woocommerce-shipment-tracking' ).$ftp_server.'<br />'.__( '3. Username/Password may be wrong . User Name - ', 'woocommerce-shipment-tracking' ).$ftp_user.'<br />'.__( '4. CSV File Name may be wrong. File Path Name - ', 'woocommerce-shipment-tracking' ).$ftp_server_path;
				} else {

					$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

					if ( $sftp->get($server_file, ABSPATH.$local_file) ) {

						$error_message 	= "";
						$success 		= true;
						$local_file 	= ABSPATH.$local_file;

					} elseif ( !empty($document_root) && $sftp->get($server_file, $document_root.'/'.$local_file) ) {

						$error_message 	= "";
						$success 		= true;
						$local_file 	= $document_root.'/'.$local_file;

					} else {
						$error_message = __( "There was a problem while getting files from SFTP Server and uploading into wp-contents/uploads/. ", 'woocommerce-shipment-tracking' ).'<br /><b>'.__( '1. Try with or without Passive Mode .', 'woocommerce-shipment-tracking' ).'</b><br />'.__( '2. Check your SFTP file permission .', 'woocommerce-shipment-tracking' ).'<br/>'.__( '3. Check permission of folder wp-contents/uploads/ .', 'woocommerce-shipment-tracking' );
					}
				}

			} else {

				if( ! empty($ftp_server) ) {

					$ftp_conn = $use_ftps ? @ftp_ssl_connect( $ftp_server, $ftp_port, $ftp_timeout ) : @ftp_connect( $ftp_server, $ftp_port, $ftp_timeout );
				} else {

					die( __( 'Please Provide FTP Host IP / Address .', 'woocommerce-shipment-tracking' ) );
				}

				if($ftp_conn == false) {

					$error_message = __("Could not Connect to Server : ", 'woocommerce-shipment-tracking').$ftp_server.'<br /><b>'.__( 'Possible Reasons :', 'woocommerce-shipment-tracking' ).'</b><br /><br />'.__( '1. Please select appropriate Protocol: FTP/SFTP.', 'woocommerce-shipment-tracking' ).'<br />'.__( '2. Server/Host address may be wrong . Server - ', 'woocommerce-shipment-tracking' ).$ftp_server.'<br />'.__( '3. Port may be wrong . Port no. - ', 'woocommerce-shipment-tracking' ).$ftp_port.'<br />'.__( '4. Time out.', 'woocommerce-shipment-tracking' );
				}

				if(empty($error_message)) {

					if( @ftp_login($ftp_conn, $ftp_user, $ftp_password) == false ) {

						$error_message = __( 'Connected to the server but not able to login .', 'woocommerce-shipment-tracking' ).'<br /><b>'.__( 'Possible Reasons - ', 'woocommerce-shipment-tracking' ).'</b><br />'.__( '1. Please select appropriate Protocol: FTP/SFTP.', 'woocommerce-shipment-tracking' ).'<br />'.__( '2. Username may be wrong . User Name - ', 'woocommerce-shipment-tracking' ).$ftp_user.'<br/>'.__( '3. Password may be wrong . Password - ', 'woocommerce-shipment-tracking' ).$ftp_password.'<br/>'.__( '4. Try with/without FTPS.', 'woocommerce-shipment-tracking' );
					}
				}

				if(empty($error_message)) {

					if($use_passive_mode)   ftp_pasv($ftp_conn, $use_passive_mode);

					$folder_pattern =   "/\/\.\*$/";

					// Folder is provided
					if(preg_match($folder_pattern, $server_file ,$matches)) {

						$local_file 	=   array();
						$folder_path    =   preg_replace($folder_pattern,'',$server_file);
						$file_list 		= ftp_nlist($ftp_conn, $folder_path);

						foreach($file_list as $file) {

							$file_name = basename($file);

							// Skip everything except CSV
							// Second Condition to support XML Import Addon
							if( preg_match("/\.csv$/i", $file) || ( apply_filters( 'ph_shipment_trackig_support_xml_import', false ) && preg_match("/\.xml$/i", $file) ) ) {

								$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

								if ( ftp_get($ftp_conn, ABSPATH.$this->upload_dir.$file_name, $folder_path.'/'.$file_name, FTP_BINARY) ) {

									$error_message 	= "";
									$success 		= true;
									$local_file[] 	= ABSPATH.$this->upload_dir.$file_name;

								} elseif ( !empty($document_root) && ftp_get($ftp_conn, $document_root.'/'.$this->upload_dir.$file_name, $folder_path.'/'.$file_name, FTP_BINARY) ) {

									$error_message 	= "";
									$success 		= true;
									$local_file[] 	= $document_root.'/'.$this->upload_dir.$file_name;

								} else {

									$success = false;
									$error_message = __( "There was a problem while getting files from ftp server and uploading into wp-contents/uploads/. ", 'woocommerce-shipment-tracking' ).'<br /><b>'.__( '1. Try with or without Passive Mode .', 'woocommerce-shipment-tracking' ).'</b><br />'.__( '2. Check your FTP file permission .', 'woocommerce-shipment-tracking' ).'<br/>'.__( '3. Check permission of folder wp-contents/uploads/ .', 'woocommerce-shipment-tracking' );
									break;
								}

							}else{

								$this->log->add('shipment-tracking-import', __( stripslashes(json_encode($file))." - Import couldn't be completed as the File Format is Invalid. Please check the format(.csv)", 'woocommerce-shipment-tracking'));
							}
						}

					} else {   // Single File

						$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

						if ( @ftp_get($ftp_conn, ABSPATH.$local_file, $server_file, FTP_BINARY) ) {

							$error_message 	= "";
							$success 		= true;
							$local_file 	= ABSPATH.$local_file;

						} elseif ( !empty($document_root) && @ftp_get($ftp_conn, $document_root.'/'.$local_file, $server_file, FTP_BINARY) ) {

							$error_message 	= "";
							$success 		= true;
							$local_file 	= $document_root.'/'.$local_file;

						} else {

							$error_message = __( "There was a problem while getting files from ftp server and uploading into wp-contents/uploads/. ", 'woocommerce-shipment-tracking' ).'<br /><b>'.__( '1. Try with or without Passive Mode .', 'woocommerce-shipment-tracking' ).'</b><br />'.__( '2. Check your FTP file permission .', 'woocommerce-shipment-tracking' ).'<br/>'.__( '3. Check permission of folder wp-contents/uploads/ .', 'woocommerce-shipment-tracking' );
						}
					}
				}

				if($ftp_conn) {
					ftp_close($ftp_conn);
				}

			}

			if($success) {

				$this->file_url = $local_file;
			}else{

				die($error_message);
			}

			return true;
		}

		/**
		 * header function.
		 */
		public function header() {
			echo '<div class="wrap"><div class="icon32 icon32-woocommerce-importer" id="icon-woocommerce"><br></div>';

			$tab = 'manual_importer';
			include( 'views/html-wf-admin-screen.php' );
			
			echo '<h2>' . __( 'Import Shipment Tracking Details to WooCommerce Orders', 'woocommerce-shipment-tracking' ) . '</h2>';
		}

		/**
		 * footer function.
		 */
		public function footer() {
			echo '</div>';
		}

		/**
		 * greet function.
		 */
		public function greet() {

			$ftp_settings 		= get_option( 'wf_shipment_tracking_importer_ftp');
			$settings 			= get_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', null);

			$ftp_or_sftp         = '';
			$ftp_server          = '';
			$ftp_user            = '';
			$ftp_password        = '';
			$use_ftps            = '';
			$use_passive_mode    = isset($ftp_settings['use_passive_mode']) ? $ftp_settings['use_passive_mode'] : true;
			$enable_ftp_ie       = '';	
			$ftp_server_path     = '';	

			if( !empty( $ftp_settings ) ){

				$ftp_or_sftp         = isset($ftp_settings[ 'ftp_or_sftp' ]) ? $ftp_settings[ 'ftp_or_sftp' ] : 'ftp';

				$ftp_server         = $ftp_settings[ 'ftp_server' ];
				$ftp_user           = $ftp_settings[ 'ftp_user' ];
				$ftp_password       = $ftp_settings[ 'ftp_password' ];
				$use_ftps           = $ftp_settings[ 'use_ftps' ];
				$enable_ftp_ie      = $ftp_settings[ 'enable_ftp_ie' ];
				$ftp_server_path    = $ftp_settings[ 'ftp_server_path' ];
				$ftp_port           = isset($ftp_settings[ 'ftp_port' ]) ? $ftp_settings[ 'ftp_port' ] : 21;
				$ftp_timeout        = isset($ftp_settings[ 'ftp_timeout' ]) ? $ftp_settings[ 'ftp_timeout' ] : 90;
			}

			$delimiter 	= isset($settings['shipment_tracking_auto_import_delimiter']) ? $settings['shipment_tracking_auto_import_delimiter'] : ',';

			echo '<div class="narrow">';
			echo '<p>' . __( 'Hi there! Upload a CSV file containing Shipping Tracking to import into your shop. Choose a .csv file to upload, then click "Upload file and import".', 'woocommerce-shipment-tracking' ).'</p>';

			echo '<p>' . sprintf( __( 'Shipping Tracking CSV need to be defined with columns in a specific order (5 columns). <a href="%s">Click here to download a sample</a>.', 'woocommerce-shipment-tracking' ), WooCommerce_Shipment_Tracking::wf_plugin_url() . '/sample-data/sample_shipment_tracking.csv' ) . '</p>';

			$action = 'admin.php?import=import_shipment_tracking_csv&step=1';

			$bytes 		= apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size 		= size_format( $bytes );
			$upload_dir = wp_upload_dir();

			if ( ! empty( $upload_dir['error'] ) ) :

				?>

				<div class="error">
					<p>
						<?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'woocommerce-shipment-tracking' ); ?>
					</p>
					<p><strong><?php echo $upload_dir['error']; ?></strong></p>
				</div>

				<?php
			else :
				?>
				<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>
									<label for="upload"><?php _e( 'Choose a file from your computer:', 'woocommerce-shipment-tracking' ); ?></label>
								</th>
								<td>
									<input type="file" id="upload" name="import" size="25" />
									<input type="hidden" name="action" value="save" />
									<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
									<small><?php printf( __('Maximum size: %s', 'woocommerce-shipment-tracking' ), $size ); ?></small>
								</td>
							</tr>

							<tr>
								<th>
									<label for="ftp"><?php _e( 'OR Provide FTP/SFTP Path:', 'woocommerce-shipment-tracking' ); ?></label>
								</th>
								<td>
									<table class="form-table">
										<tr>
											<th>
												<label for="enable_ftp_ie"><?php _e( 'Enable FTP/SFTP Import', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="checkbox" name="enable_ftp_ie" id="enable_ftp_ie" class="checkbox" <?php checked( $enable_ftp_ie, 1 ); ?> />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_or_sftp"><?php _e( 'Select FTP or SFTP', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<select name="ftp_or_sftp" id="ftp_or_sftp" style="width: 21%;">
													<option <?php if ($ftp_or_sftp === 'ftp') echo 'selected'; ?> value="ftp"><?php _e( 'FTP', 'woocommerce-shipment-tracking' ); ?></option>
													<option <?php if ($ftp_or_sftp === 'sftp') echo 'selected'; ?> value="sftp"><?php _e( 'SFTP', 'woocommerce-shipment-tracking' ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_server"><?php _e( 'FTP/SFTP Server Host/IP', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="text" name="ftp_server" id="ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_server; ?>" class="input-text" />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_user"><?php _e( 'FTP/SFTP User Name', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="text" name="ftp_user" id="ftp_user" placeholder="<?php _e('', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_user; ?>" class="input-text" />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_password"><?php _e( 'FTP/SFTP Password', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="password" name="ftp_password" id="ftp_password" placeholder="<?php _e('', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_password; ?>" class="input-text" />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_port"><?php _e( 'FTP/SFTP Port', 'woocommerce-shipment-tracking' ); ?></label>
												<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Default port will be used if left empty.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" />
											</th>
											<td>
												<input type="text" name="ftp_port" id="ftp_port" placeholder="<?php _e('21', 'woocommerce-shipment-tracking'); ?>" value="<?php if( isset($ftp_port) ) echo $ftp_port; ?>" class="input-text" />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_timeout"><?php _e( 'FTP/SFTP Timeout', 'woocommerce-shipment-tracking' ); ?></label>
												<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Default timeout default value will be used if left empty.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" />
											</th>
											<td>
												<input type="text" name="ftp_timeout" id="ftp_timeout" placeholder="<?php _e('90', 'woocommerce-shipment-tracking'); ?>" value="<?php if( isset($ftp_timeout) ) echo $ftp_timeout; ?>" class="input-text" />
											</td>
										</tr>
										<tr>
											<th>
												<label for="ftp_server_path"><?php _e( 'Path/CSV File Name', 'woocommerce-shipment-tracking' ); ?></label>
												<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Remote CSV File Path starting from FTP home directory excluding leading slash and File Name in the end.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" />
											</th>
											<td>
												<input type="text" name="ftp_server_path" id="ftp_server_path" placeholder="<?php _e('Path/StartingFrom/FTP-Directory/FileName.csv', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_server_path; ?>" class="input-text" />
												<br/>
												<small>Note: <i>For SFTP Connections the path should always end with a file name (Folder name is not supported).</i></small>
											</td>
										</tr>

										<tr>
											<th>
												<label for="use_ftps"><?php _e( 'Use FTPS', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="checkbox" name="use_ftps" id="use_ftps" class="checkbox" <?php checked( $use_ftps, 1 ); ?> />
											</td>
										</tr>

										<tr>
											<th>
												<label for="use_passive_mode"><?php _e( 'Use Passive Mode', 'woocommerce-shipment-tracking' ); ?></label>
											</th>
											<td>
												<input type="checkbox" name="use_passive_mode" id="use_passive_mode" class="checkbox" <?php checked( $use_passive_mode, 1 ); ?> />
											</td>
										</tr>

									</table>
								</td>
							</tr>
							<tr>
								<th><label><?php _e( 'Delimiter', 'woocommerce-shipment-tracking' ); ?></label><br/></th>
								<td><input type="text" name="delimiter" placeholder="," size="2" value="<?php echo $delimiter; ?>" /></td>
							</tr>
						</tbody>
					</table>
					<button type="button" class="button button-primary" id="ph_shipment_tracking_test_ftp_button"> Test FTP/SFTP </button>
					<p class="submit">
						<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Upload file and import', 'woocommerce-shipment-tracking' ); ?>" />
					</p>
				</form>
				<?php
			endif;

			echo '</div>';

			?>
			<script>
				jQuery("#ph_shipment_tracking_test_ftp_button").click( function(){
					jQuery("#ph_shipment_tracking_test_ftp_button").attr("disabled", true);
					let data = {}
					data.action             = 'ph_shipment_tracking_ftp_test';
					data.ftp_or_sftp        = jQuery("#ftp_or_sftp").val();
					data.ftp_server         = jQuery("#ftp_server").val();
					data.ftp_user           = jQuery("#ftp_user").val();
					data.ftp_password       = jQuery("#ftp_password").val();
					data.ftp_port           = jQuery("#ftp_port").val();
					data.ftp_timeout        = jQuery("#ftp_timeout").val();
					data.ftp_server_path    = jQuery("#ftp_server_path").val();
					data.use_ftps           = jQuery("#use_ftps").is(':checked') ? true : '';
					data.use_passive_mode   = jQuery("#use_passive_mode").is(':checked') ? true : '';
					jQuery.post(
						ajaxurl,
						data,
						)
					.done( function(data){
						jQuery("#ph_shipment_tracking_test_ftp_button").removeAttr("disabled");
						alert(data);
					});
				});
			</script>
			<?php
		}

		/**
		 * Added to http_request_timeout filter to force timeout at 60 seconds during import
		 *
		 * @param  int $val
		 * @return int 60
		 */
		public function bump_request_timeout( $val ) {
			return 60;
		}
	}
}