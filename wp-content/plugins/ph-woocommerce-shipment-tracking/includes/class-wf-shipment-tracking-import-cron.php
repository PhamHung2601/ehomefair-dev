<?php

if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

include_once('Net/PH_SFTP.php');

class WF_ShipmentTracking_ImportCron {

	public $settings;
	public $file_url;
	public $error_message;

	public function __construct() {
		
		add_filter('cron_schedules', array($this, 'wf_shipment_tracking_auto_import_schedule'));
		add_action('init', array($this, 'wf_shipment_tracking_new_scheduled_import'));
		add_action('wf_shipment_tracking_csv_im_ex_auto_import', array($this, 'wf_scheduled_import_shipment_tracking'));

		$this->settings 			= get_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', null);
		$this->settings_ftp_import 	= get_option('wf_shipment_tracking_importer_ftp', null);
		$this->imports_enabled 		= FALSE;
		

		if (isset($this->settings['shipment_tracking_auto_import']) && $this->settings['shipment_tracking_auto_import'] === 'Enabled') {
			$this->imports_enabled = TRUE;
		}

		$this->store_id 			= get_option( 'wf_tracking_ph_store_id' );
		$this->accessTokenOrder 	= FALSE;

		// Run Cron if Store Id is Available
		if( !empty($this->store_id) ) {

			// Run Cron if the imported order tracking number array is not empty
			$this->orderTrackingCSV = get_option( 'ph_server_side_order_creation_array', array() );

			if( is_array($this->orderTrackingCSV) && !empty($this->orderTrackingCSV) ) {
				
				$this->accessTokenOrder = TRUE;
			}

			add_action('ph_server_side_order_creation_for_csv', array($this, 'ph_create_server_side_order_for_csv_data'));
		}
		
		$this->upload_dir = 'wp-content/uploads/';
	}

	public function wf_shipment_tracking_auto_import_schedule($schedules) {

		if ($this->imports_enabled) {
			$import_interval = $this->settings['shipment_tracking_auto_import_interval'];
			if ($import_interval) {
				$schedules['import_interval'] = array(
					'interval' => (int) $import_interval * 60,
					'display' => sprintf(__('Every %d minutes', 'woocommerce-shipment-tracking'), (int) $import_interval)
				);
			}
		}

		// Add Cron Job Interval for every 60 Minutes for Server Side Order Creation
		$schedules['server_side_interval'] = array(
			'interval' 	=> (int) 60 * 60,
			'display' 	=> sprintf(__('Every %d minutes', 'woocommerce-shipment-tracking'), (int) 60)
		);

		return $schedules;
	}

	public function wf_shipment_tracking_new_scheduled_import() {

		if ($this->imports_enabled) {
			if (!wp_next_scheduled('wf_shipment_tracking_csv_im_ex_auto_import')) {
				$start_time = $this->settings['shipment_tracking_auto_import_start_time'];
				$current_time = current_time('timestamp');
				if ($start_time) {
					if ($current_time > strtotime('today ' . $start_time, $current_time)) {
						$start_timestamp = strtotime('tomorrow ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
					} else {
						$start_timestamp = strtotime('today ' . $start_time, $current_time) - ( get_option('gmt_offset') * HOUR_IN_SECONDS );
					}
				} else {
					$import_interval = $this->settings['shipment_tracking_auto_import_interval'];
					$start_timestamp = strtotime("now +{$import_interval} minutes");
				}
				wp_schedule_event($start_timestamp, 'import_interval', 'wf_shipment_tracking_csv_im_ex_auto_import');
			}
		}

		if ( $this->accessTokenOrder && !wp_next_scheduled('ph_server_side_order_creation_for_csv') ) {
			wp_schedule_event(time(), 'server_side_interval', 'ph_server_side_order_creation_for_csv');
		}
	}

	public function ph_create_server_side_order_for_csv_data() {

		// Create WooCommerce Order at Server Side 
		if( is_array($this->orderTrackingCSV) && !empty($this->orderTrackingCSV) ) {

			if( !class_exists('PH_Shipment_Tracking_API') ) {
				include_once ( 'includes/class-ph-shipment-tracking-api.php' );
			}

			$result     = new PH_Shipment_Tracking_API();

			foreach ($this->orderTrackingCSV as $orderId => $details) {
				
				$order 				= wc_get_order($orderId);
				$trackingnumber 	= $details['trackingNum'];
				$carrier 			= $details['carrier'];

 				$result->ph_server_side_order_creation($orderId, $trackingnumber, sanitize_title($carrier), $order);
			}

			// Update the option to empty array once the Order Creation Completes
			update_option( 'ph_server_side_order_creation_array', array() );
		}

	}

	public static function load_wp_importer() {
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if (!class_exists('WP_Importer')) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if (file_exists($class_wp_importer)) {
				require $class_wp_importer;
			}
		}
	}

	public function wf_scheduled_import_shipment_tracking() {

		define('WP_LOAD_IMPORTERS', true);

		if (!class_exists('WooCommerce')) :
			require ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';
		endif;

		WF_ShipmentTracking_ImportCron::shipment_tracking_cron_importer();
		$GLOBALS['WF_Shipment_Tracking_Importer']->skip_allready_processed = !empty($this->settings['shipment_tracking_auto_import_merge']) ? $this->settings['shipment_tracking_auto_import_merge'] : 1;

		$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('--------------- Starting Auto Import --------------', 'woocommerce-shipment-tracking'));
		if ($this->handle_ftp_for_autoimport()) {

			$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Inside FTP', 'woocommerce-shipment-tracking'));

			$files = is_array($this->file_url)?$this->file_url:array($this->file_url);

			foreach($files as $file){
				$GLOBALS['WF_Shipment_Tracking_Importer']->import($file);
				//$GLOBALS['WF_Shipment_Tracking_Importer']->import_end();
			}

			die();
		} else {
			$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Fetching file failed. Reason:' . $this->error_message, 'woocommerce-shipment-tracking'));
		}
	}

	public function clear_wf_shipment_tracking_scheduled_import() {
		wp_clear_scheduled_hook('wf_shipment_tracking_csv_im_ex_auto_import');
	}

	private function handle_ftp_for_autoimport() {
	  
		$enable_ftp_ie = $this->settings_ftp_import['enable_ftp_ie'];

		$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('FTP Status '.$enable_ftp_ie, 'woocommerce-shipment-tracking'));
		
		if (!$enable_ftp_ie) {
			return false;
		}

		$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('FTP Started', 'woocommerce-shipment-tracking'));

		$ftp_or_sftp 		= $this->settings_ftp_import['ftp_or_sftp'];
		$ftp_server 		= $this->settings_ftp_import['ftp_server'];
		$ftp_port 			= !empty($this->settings_ftp_import['ftp_port']) ? $this->settings_ftp_import['ftp_port'] : 21;
		$ftp_timeout 		= !empty($this->settings_ftp_import['ftp_timeout']) ? $this->settings_ftp_import['ftp_timeout'] : 90;
		$ftp_user 			= $this->settings_ftp_import['ftp_user'];
		$ftp_password 		= $this->settings_ftp_import['ftp_password'];
		$use_ftps 			= $this->settings_ftp_import['use_ftps'];
		$use_passive_mode 	= isset($this->settings_ftp_import['use_passive_mode']) ? $this->settings_ftp_import['use_passive_mode'] : true;
		$ftp_server_path 	= $this->settings_ftp_import['ftp_server_path'];

		$local_file 		= 'wp-content/uploads/temp-import.csv';
		$server_file 		= $ftp_server_path;

		$this->error_message 	= "";
		$success 				= false;

		if( $ftp_or_sftp == 'sftp' ) {

			if( ! empty($ftp_server) ) {

				$sftp = new PH_Net_SFTP($ftp_server);
			} else {

				$this->error_message = "There is connection problem\n";
				$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Connection:'.$this->error_message, 'woocommerce-shipment-tracking')); 
			}

			if ( ! $sftp->login($ftp_user, $ftp_password )) {
				$this->error_message = "Not able to login \n";
				$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Login:'.$this->error_message, 'woocommerce-shipment-tracking'));
			}
			else
			{
				$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

				if ( $sftp->get($server_file, ABSPATH.$local_file) ) {
					$this->error_message 	= "";
					$success 				= true;
					$local_file 			= ABSPATH.$local_file;
				} elseif ( !empty($document_root) && $sftp->get($server_file, $document_root.'/'.$local_file) ) {
					$this->error_message 	= "";
					$success 				= true;
					$local_file 			= $document_root.'/'.$local_file;
				} else {
					$this->error_message = "There was a problem\n";
					$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('FTP get:'.$this->error_message, 'woocommerce-shipment-tracking'));
				}

			}
		}
		else
		{
			$ftp_conn = $use_ftps ? @ftp_ssl_connect( $ftp_server, $ftp_port, $ftp_timeout ) : @ftp_connect( $ftp_server, $ftp_port, $ftp_timeout );

			if ($ftp_conn == false) {
				$this->error_message = "There is connection problem\n";
				$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Connection:'.$this->error_message, 'woocommerce-shipment-tracking'));
			}

			if (empty($this->error_message)) {
				if (ftp_login($ftp_conn, $ftp_user, $ftp_password) == false) {
					$this->error_message = "Not able to login \n";
					$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Login:'.$this->error_message, 'woocommerce-shipment-tracking'));
				}
			}
			if (empty($this->error_message)) {

				if( $use_passive_mode == true )   ftp_pasv($ftp_conn, $use_passive_mode);

				$folder_pattern =   "/\/\.\*$/";

				if(preg_match($folder_pattern, $server_file ,$matches)){    // Folder is provided

					$local_file =   array();
					$folder_path    =   preg_replace($folder_pattern,'',$server_file);
					$file_list = ftp_nlist($ftp_conn, $folder_path);
					
					if( !empty($file_list) && is_array($file_list) )
					{
						foreach($file_list as $file){

							$file_name = basename($file);

							// Skip everything except CSV
							// Second Condition to support XML Import Addon
							if( preg_match("/\.csv$/i", $file) || ( apply_filters( 'ph_shipment_trackig_support_xml_import', false ) && preg_match("/\.xml$/i", $file) ) ) {

								$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

								if ( ftp_get($ftp_conn, ABSPATH.$this->upload_dir.$file_name, $folder_path.'/'.$file_name, FTP_BINARY) ) {

									$this->error_message 	= "";
									$success 				= true;
									$local_file[] 			= ABSPATH.$this->upload_dir.$file_name;

								} elseif ( !empty($document_root) && ftp_get($ftp_conn, $document_root.'/'.$this->upload_dir.$file_name, $folder_path.'/'.$file_name, FTP_BINARY) ) {

									$this->error_message 	= "";
									$success 				= true;
									$local_file[] 			= $document_root.'/'.$this->upload_dir.$file_name;

								} else {
									$success = false;
									$this->error_message = __( "There was a problem while getting files from FTP server\n", 'woocommerce-shipment-tracking' );
									$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('FTP get:'.$this->error_message, 'woocommerce-shipment-tracking'));
									break;
								}
							}else{
								$this->error_message = __( stripslashes(json_encode($file))." - Import couldn't be completed as the File Format is Invalid. Please check the format(.csv)", 'woocommerce-shipment-tracking' );
								$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __( $this->error_message, 'woocommerce-shipment-tracking'));
							}
						}
					}
				}
				else
				{
					$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ) : '';

					if (ftp_get($ftp_conn, ABSPATH . $local_file, $server_file, FTP_BINARY)) {

						$this->error_message 	= "";
						$success 				= true;
						$local_file 			= ABSPATH.$local_file;

					} elseif ( !empty($document_root) && ftp_get($ftp_conn, $document_root.'/'.$local_file, $server_file, FTP_BINARY) ) {

						$this->error_message 	= "";
						$success 				= true;
						$local_file 			= $document_root.'/'.$local_file;

					} else {
						$this->error_message = "There was a problem while getting files from FTP server\n";
						$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('FTP get:'.$this->error_message, 'woocommerce-shipment-tracking'));
					}
				}
			}

			ftp_close($ftp_conn);
		}

		if ($success) {

			$this->file_url = $local_file;
		} else {

			$GLOBALS['WF_Shipment_Tracking_Importer']->log->add('shipment-tracking-import', __('Error:'.$this->error_message, 'woocommerce-shipment-tracking'));

			die($this->error_message);
		}

		return true;
	}

	public static function shipment_tracking_cron_importer() {

		if (!defined('WP_LOAD_IMPORTERS')) {
			return;
		}

		self::load_wp_importer();

		// includes
		require_once 'class-wf-shipment-tracking-importer.php';

		if ( !class_exists('WC_Logger') ) {

			$class_wc_logger = ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-logger.php';

			if ( file_exists($class_wc_logger) ) {
				require $class_wc_logger;
			}
		}

		$class_wc_logger = ABSPATH . 'wp-includes/pluggable.php';
		require_once($class_wc_logger);

		wp_set_current_user(1); // escape user access check while running cron

		$settings 	= get_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', null);

		$GLOBALS['WF_Shipment_Tracking_Importer'] 				= new WF_Shipment_Tracking_Importer();
		$GLOBALS['WF_Shipment_Tracking_Importer']->import_page 	= 'import_shipment_tracking_csv_cron';
		$GLOBALS['WF_Shipment_Tracking_Importer']->delimiter 	= isset($settings['shipment_tracking_auto_import_delimiter']) ? $settings['shipment_tracking_auto_import_delimiter'] : ',';
	}

}