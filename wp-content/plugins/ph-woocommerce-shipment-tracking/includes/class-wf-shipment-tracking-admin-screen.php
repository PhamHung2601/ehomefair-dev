<?php

if (!defined('ABSPATH')) {
	exit;
}

class WF_Shipment_Tracking_Admin_Screen {

	/**
	 * Constructor
	**/
	public function __construct() {

		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_print_styles', array($this, 'admin_scripts'));
		add_action('admin_notices', array($this, 'admin_notices'));

		// Perform Ajax Functions for Adding, Editing and Deleting Carriers
		add_action( 'wp_ajax_ph_shipment_tracking_save_carrier', array( $this, 'ph_shipment_tracking_save_carrier') );
		add_action( 'wp_ajax_ph_shipment_tracking_edit_carrier', array( $this, 'ph_shipment_tracking_edit_carrier') );
		add_action( 'wp_ajax_ph_shipment_tracking_delete_carrier', array( $this, 'ph_shipment_tracking_delete_carrier') );

	}

	/**
	 * Notices in admin
	**/
	public function admin_notices() {

		if (!function_exists('mb_detect_encoding')) {
			echo '<div class="error"><p>' . __('CSV Import requires the function <code>mb_detect_encoding</code> to import CSV files. Please ask your hosting provider to enable this function.', 'woocommerce-shipment-tracking') . '</p></div>';
		}
	}

	/**
	 * Admin Menu
	**/
	public function admin_menu() {

		// Add Menu Page for Settings
		add_menu_page(
			__('Shipment Tracking', 'woocommerce-shipment-tracking'),
			__('Shipment Tracking', 'woocommerce-shipment-tracking'),
			'manage_woocommerce',
			'shipment_tracking_pro',
			array($this, 'ph_shipment_tracking'),
			'dashicons-location-alt',
			55.5
		);

		$store_id   = get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );

		// Add Submenu Page for Orders Listing
		// To support Customers who are using Access Token
		if( !empty($store_id) ) {

			add_submenu_page( 
				"shipment_tracking_pro",
				__('Orders', 'woocommerce-shipment-tracking'),
				__('Orders', 'woocommerce-shipment-tracking'),
				"manage_woocommerce",
				"PluginHive-Orders",
				array( $this, "pluginhive_orders")
			);
		}

		// Add Submenu Page for Import
		add_submenu_page(
			'shipment_tracking_pro',
			__('Import', 'woocommerce-shipment-tracking'),
			__('Import', 'woocommerce-shipment-tracking'),
			'manage_woocommerce',
			'import_shipment_tracking_csv',
			array($this, 'output')
		);

	}

	/**
	 * Admin Scripts
	**/
	public function admin_scripts() {

		$settings 	= PH_Shipment_Tracking_General_Settings::get_settings( 'ph_shipment_tracking_settings_data' );
		$urlLink 	= isset($settings['custom_page_url']) ? $settings['custom_page_url'] : '';

		if( empty($urlLink) ) {

			$urlLink = 'NA';
		}

		$wp_date_format     = get_option('date_format');
		
		// Enqueue ThickBox
		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox');

		// Enqueue Select2
		wp_enqueue_script( 'ph-shipment-tracking-select2-js', plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/js/select2.min.js', basename(__FILE__) ), array( 'jquery' ));
		wp_enqueue_style( 'ph-shipment-tracking-select2-css', plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/css/select2.min.css', basename(__FILE__) ) );

		// Enqueue JQuery Block
		wp_enqueue_script( 'ph-shipment-tracking-block-js', plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/js/shipment-tracking-jquery-block.js', basename(__FILE__) ), array( 'jquery' ));
		
		wp_enqueue_script( 'ph-shipment-tracking-admin', plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/js/shipment-tracking-admin.js', basename(__FILE__) ), array( 'jquery' ));
		wp_localize_script('ph-shipment-tracking-admin', 'ph_shipment_tracking_admin_js', array( 'urlLink' => $urlLink, 'wpDateFormat' => $wp_date_format) );

		wp_enqueue_script('woocommerce-shipment-tracking-csv-importer', plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/js/shipment-tracking-csv-import.min.js', basename(__FILE__)), '', '1.0.0', 'screen');
		wp_localize_script('woocommerce-shipment-tracking-csv-importer', 'woocommerce_shipment_tracking_csv_cron_params', array('enable_ftp_ie' => '', 'shipment_tracking_auto_import' => 'Disabled'));
		
	}

	/**
	 * Admin Screen Output
	**/
	public function ph_shipment_tracking() {

		printf( '<div class="wrap"><h2>%s</h2>', __( 'Shipment Tracking Settings', 'woocommerce-shipment-tracking' ).
		'&nbsp;<a href="#TB_inline?height=150&width=525&inlineId=add-new-carrier&modal=true" title="Add Tracking Carrier" class="page-title-action thickbox">'.__( 'Add Carrier', 'woocommerce-shipment-tracking' ).'</a></div>' );

		$this->load_settings_page();
	}

	public function load_settings_page() {

		$settingsId 				= "ph_shipment_tracking_settings_data";			// Main Plugin Settings
		$cronSettingsId 			= "ph_shipment_tracking_cron_settings";			// Cron Settings & Custom Order Status Settings
		$savedListSettingsId 		= "ph_shipment_tracking_saved_carrier_list";	// All Available Carrier List including Custom Carrier added by Customers
		$carrierSettingsId 			= "ph_shipment_tracking_carrier_data";			// The added carriers to display
		$carrierCredentialsStatus 	= "ph_shipment_tracking_carrier_cred_status"; 	// To save Credentials Status of Live Tracking Carriers

		// To check Credentials are added or not for Live Tracking
		// Default will be False
		$this->liveTrackigEnabled = array(
			'united-states-postal-service-usps' => false,
			'ups' 								=> false,
			'canada-post' 						=> false,
			'fedex'								=> false,
			'blue-dart' 						=> false,
			'australia-post' 					=> false,
			'delhivery' 						=> false,
			'dhl-express' 						=> false,
			'aramex'							=> false,
		);

		// Default Subject & Content for Tracking Status Change
		$defaultSubject 	= "Tracking Details for Your Order [ORDER_NUM]";
		$defaultTemplate	= "Hi [CUSTOMER_NAME],\n\nYour Order #[ORDER_NUM] placed using [EMAIL_ID] is Shipped via [CARRIER_NAME] and is [TRACKING_STATUS].\n\nTrack your orders in real-time using the following details.\n\nTracking ID - [TRACKING_ID]\nOr,\nFind the Shipment Progress below.\n\n[SHIPMENT_PROGRESS]";

		$resetData 			= isset($_POST['reset_tracking_data']) ? true : false;
		$ph_settings 		= array();
		$ph_cron_settings 	= array();

		// Save Settings
		if( isset($_POST['ph_save_tracking_settings']) ) {

			$ph_settings['custom_page_url'] 		= isset($_POST['custom_page_url']) && !empty($_POST['custom_page_url']) ? $_POST['custom_page_url'] : '';
			$ph_settings['turn_on_api'] 			= isset($_POST['turn_on_api']) && !empty($_POST['turn_on_api']) ? 'yes' : 'no';
			$ph_settings['auto_refresh'] 			= isset($_POST['auto_refresh']) && !empty($_POST['auto_refresh']) ? $_POST['auto_refresh'] : 'disable';
			$ph_settings['custom_message'] 			= isset($_POST['custom_message']) && !empty($_POST['custom_message']) ? stripslashes($_POST['custom_message']) : '';
			$ph_settings['tracking_to_customer'] 	= isset($_POST['tracking_to_customer']) && !empty($_POST['tracking_to_customer']) ? 'yes' : 'no';
			$ph_settings['tracking_to_mail'] 		= isset($_POST['tracking_to_mail']) && !empty($_POST['tracking_to_mail']) ? 'yes' : 'no';
			$ph_settings['ups_integration'] 		= isset($_POST['ups_integration']) && !empty($_POST['ups_integration']) ? 'yes' : 'no';
			$ph_settings['fedex_integration'] 		= isset($_POST['fedex_integration']) && !empty($_POST['fedex_integration']) ? 'yes' : 'no';
			$ph_settings['shipping_easy'] 			= isset($_POST['shipping_easy']) && !empty($_POST['shipping_easy']) ? 'yes' : 'no';
			$ph_settings['go_shippo'] 				= isset($_POST['go_shippo']) && !empty($_POST['go_shippo']) ? 'yes' : 'no';

			$ph_cron_settings['live_tracking_cron_enable'] 			= isset($_POST['live_tracking_cron_enable']) && !empty($_POST['live_tracking_cron_enable']) ? 'yes' : 'no';
			$ph_cron_settings['from_order_status_to_track'] 		= isset($_POST['from_order_status_to_track']) && !empty($_POST['from_order_status_to_track']) ? $_POST['from_order_status_to_track'] : array('wc-pending', 'wc-processing', 'wc-completed');
			$ph_cron_settings['prior_to_order_days'] 				= isset($_POST['prior_to_order_days']) && !empty($_POST['prior_to_order_days']) ? $_POST['prior_to_order_days'] : '14';
			$ph_cron_settings['cron_interval_time'] 				= isset($_POST['cron_interval_time']) && !empty($_POST['cron_interval_time']) ? $_POST['cron_interval_time'] : '60';
			$ph_cron_settings['to_order_status_after_delivery'] 	= isset($_POST['to_order_status_after_delivery']) && !empty($_POST['to_order_status_after_delivery']) ? $_POST['to_order_status_after_delivery'] : '';
			$ph_cron_settings['sender_email_name'] 					= isset($_POST['sender_email_name']) && !empty($_POST['sender_email_name']) ? $_POST['sender_email_name'] : '';
			$ph_cron_settings['sender_email_address'] 				= isset($_POST['sender_email_address']) && !empty($_POST['sender_email_address']) ? $_POST['sender_email_address'] : '';
			$ph_cron_settings['tracking_mail_subject'] 				= isset($_POST['tracking_mail_subject']) && !empty($_POST['tracking_mail_subject']) ? $_POST['tracking_mail_subject'] : $defaultSubject;
			$ph_cron_settings['tracking_mail_template'] 			= isset($_POST['tracking_mail_template']) && !empty($_POST['tracking_mail_template']) ? $_POST['tracking_mail_template'] : '';

			PH_Shipment_Tracking_General_Settings::update_settings( $settingsId, $ph_settings );
			PH_Shipment_Tracking_General_Settings::update_settings( $cronSettingsId, $ph_cron_settings );
		}

		// Get All Settings and Carrier List
		$settings 				= PH_Shipment_Tracking_General_Settings::get_settings( $settingsId );
		$cronSettings 			= PH_Shipment_Tracking_General_Settings::get_settings( $cronSettingsId );
		$savedListSettings 		= PH_Shipment_Tracking_General_Settings::get_settings( $savedListSettingsId );
		
		$custom_page_url 		= isset($settings['custom_page_url']) && !empty($settings['custom_page_url']) ? $settings['custom_page_url'] : '';
		$turn_on_api 			= isset($settings['turn_on_api']) && !empty($settings['turn_on_api']) ? $settings['turn_on_api'] : 'yes';
		$auto_refresh 			= isset($settings['auto_refresh']) && !empty($settings['auto_refresh']) ? $settings['auto_refresh'] : 'disable';
		$custom_message 		= isset($settings['custom_message']) && !empty($settings['custom_message']) ? $settings['custom_message'] : '';
		$tracking_to_customer 	= isset($settings['tracking_to_customer']) && !empty($settings['tracking_to_customer']) ? $settings['tracking_to_customer'] : 'yes';
		$tracking_to_mail 		= isset($settings['tracking_to_mail']) && !empty($settings['tracking_to_mail']) ? $settings['tracking_to_mail'] : 'yes';
		$ups_integration 		= isset($settings['ups_integration']) && !empty($settings['ups_integration']) ? $settings['ups_integration'] : 'no';
		$fedex_integration 		= isset($settings['fedex_integration']) && !empty($settings['fedex_integration']) ? $settings['fedex_integration'] : 'no';
		$shipping_easy 			= isset($settings['shipping_easy']) && !empty($settings['shipping_easy']) ? $settings['shipping_easy'] : 'no';
		$go_shippo 				= isset($settings['go_shippo']) && !empty($settings['go_shippo']) ? $settings['go_shippo'] : 'no';

		$live_tracking_cron_enable 		= isset($cronSettings['live_tracking_cron_enable']) && $cronSettings['live_tracking_cron_enable'] == 'yes' ? 'yes' : 'no';
		$from_order_status_to_track 	= isset($cronSettings['from_order_status_to_track']) && !empty($cronSettings['from_order_status_to_track']) ? $cronSettings['from_order_status_to_track'] : array('wc-pending', 'wc-processing', 'wc-completed');
		$prior_to_order_days 			= isset($cronSettings['prior_to_order_days']) && !empty($cronSettings['prior_to_order_days']) ? $cronSettings['prior_to_order_days'] : '14';
		$cron_interval_time 			= isset($cronSettings['cron_interval_time']) && !empty($cronSettings['cron_interval_time']) ? $cronSettings['cron_interval_time'] : '60';
		$to_order_status_after_delivery = isset($cronSettings['to_order_status_after_delivery']) && !empty($cronSettings['to_order_status_after_delivery']) ? $cronSettings['to_order_status_after_delivery'] : '';
		$sender_email_name 				= isset($cronSettings['sender_email_name']) && !empty($cronSettings['sender_email_name']) ? $cronSettings['sender_email_name'] : '';
		$sender_email_address 			= isset($cronSettings['sender_email_address']) && !empty($cronSettings['sender_email_address']) ? $cronSettings['sender_email_address'] : '';
		$tracking_mail_subject 			= isset($cronSettings['tracking_mail_subject']) && !empty($cronSettings['tracking_mail_subject']) ? $cronSettings['tracking_mail_subject'] : $defaultSubject;
		$tracking_mail_template 		= isset($cronSettings['tracking_mail_template']) && !empty($cronSettings['tracking_mail_template']) ? $cronSettings['tracking_mail_template'] : '';

		$default_tracking_data	= Ph_Shipment_Tracking_Util::load_tracking_data( true, true );
		$tracking_data_txt 		= Ph_Shipment_Tracking_Util::convert_tracking_data_to_piped_text( $default_tracking_data );
		$this->tracking_data 	= Ph_Shipment_Tracking_Util::convert_piped_text_to_tracking_data( $tracking_data_txt , $default_tracking_data);

		$usps_userid 			= isset($savedListSettings['united-states-postal-service-usps']) && isset($savedListSettings['united-states-postal-service-usps']['userid']) ? $savedListSettings['united-states-postal-service-usps']['userid'] : '';

		$ups_userid 			= isset($savedListSettings['ups']) && isset($savedListSettings['ups']['userid']) ? $savedListSettings['ups']['userid'] : '';
		$ups_password 			= isset($savedListSettings['ups']) && isset($savedListSettings['ups']['password']) ? $savedListSettings['ups']['password'] : '';
		$ups_access_key 		= isset($savedListSettings['ups']) && isset($savedListSettings['ups']['accesskey']) ? $savedListSettings['ups']['accesskey'] : '';

		$canadapost_userid 		= isset($savedListSettings['canada-post']) && isset($savedListSettings['canada-post']['userid']) ? $savedListSettings['canada-post']['userid'] : '';
		$canadapost_password 	= isset($savedListSettings['canada-post']) && isset($savedListSettings['canada-post']['password']) ? $savedListSettings['canada-post']['password'] : '';

		$fedex_account 			= isset($savedListSettings['fedex']) && isset($savedListSettings['fedex']['accountnum']) ? $savedListSettings['fedex']['accountnum'] : '';
		$fedex_meter_num 		= isset($savedListSettings['fedex']) && isset($savedListSettings['fedex']['meternum']) ? $savedListSettings['fedex']['meternum'] : '';
		$fedex_service_key 		= isset($savedListSettings['fedex']) && isset($savedListSettings['fedex']['servicekey']) ? $savedListSettings['fedex']['servicekey'] : '';
		$fedex_service_pass 	= isset($savedListSettings['fedex']) && isset($savedListSettings['fedex']['servicepass']) ? $savedListSettings['fedex']['servicepass'] : '';

		$bluedart_userid 		= isset($savedListSettings['blue-dart']) && isset($savedListSettings['blue-dart']['userid']) ? $savedListSettings['blue-dart']['userid'] : '';
		$bluedart_api_key 		= isset($savedListSettings['blue-dart']) && isset($savedListSettings['blue-dart']['apikey']) ? $savedListSettings['blue-dart']['apikey'] : '';

		$delhivery_userid 		= isset($savedListSettings['delhivery']) && isset($savedListSettings['delhivery']['userid']) ? $savedListSettings['delhivery']['userid'] : '';
		$delhivery_api_key 		= isset($savedListSettings['delhivery']) && isset($savedListSettings['delhivery']['apikey']) ? $savedListSettings['delhivery']['apikey'] : '';

		$dhl_siteid 			= isset($savedListSettings['dhl-express']) && isset($savedListSettings['dhl-express']['siteid']) ? $savedListSettings['dhl-express']['siteid'] : '';
		$dhl_api_key 			= isset($savedListSettings['dhl-express']) && isset($savedListSettings['dhl-express']['apikey']) ? $savedListSettings['dhl-express']['apikey'] : '';

		$au_account_num 		= isset($savedListSettings['australia-post']) && isset($savedListSettings['australia-post']['accountnum']) ? $savedListSettings['australia-post']['accountnum'] : '';
		$au_api_key 			= isset($savedListSettings['australia-post']) && isset($savedListSettings['australia-post']['apikey']) ? $savedListSettings['australia-post']['apikey'] : '';
		$au_password 			= isset($savedListSettings['australia-post']) && isset($savedListSettings['australia-post']['password']) ? $savedListSettings['australia-post']['password'] : '';

		$aramex_user_name 		= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['user_name']) ? $savedListSettings['aramex']['user_name'] : '';
		$aramex_password 		= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['password']) ? $savedListSettings['aramex']['password'] : '';
		$aramex_account_num 	= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['account_num']) ? $savedListSettings['aramex']['account_num'] : '';
		$aramex_account_pin 	= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['account_pin']) ? $savedListSettings['aramex']['account_pin'] : '';
		$aramex_entity 			= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['entity']) ? $savedListSettings['aramex']['entity'] : '';
		$aramex_country_code 	= isset($savedListSettings['aramex']) && isset($savedListSettings['aramex']['country_code']) ? $savedListSettings['aramex']['country_code'] : '';

		if( !empty($usps_userid) ) {

			$this->liveTrackigEnabled['united-states-postal-service-usps'] = true;
		}

		if( !empty($ups_userid) && !empty($ups_password) && !empty($ups_access_key) ) {

			$this->liveTrackigEnabled['ups'] = true;
		}

		if( !empty($canadapost_userid) && !empty($canadapost_password) ) {

			$this->liveTrackigEnabled['canada-post'] = true;
		}

		if( !empty($fedex_account) && !empty($fedex_meter_num) && !empty($fedex_service_key) && !empty($fedex_service_pass) ) {

			$this->liveTrackigEnabled['fedex'] = true;
		}

		if( !empty($bluedart_userid) && !empty($bluedart_api_key) ) {

			$this->liveTrackigEnabled['blue-dart'] = true;
		}

		if( !empty($au_account_num) && !empty($au_api_key) && !empty($au_password) ) {

			$this->liveTrackigEnabled['australia-post'] = true;
		}

		if( !empty($delhivery_userid) && !empty($delhivery_api_key) ) {

			$this->liveTrackigEnabled['delhivery'] = true;
		}

		if( !empty($dhl_siteid) && !empty($dhl_api_key) ) {

			$this->liveTrackigEnabled['dhl-express'] = true;
		}

		if( !empty($aramex_user_name) && !empty($aramex_password) && !empty($aramex_account_num) && !empty($aramex_account_pin) && !empty($aramex_entity) && !empty($aramex_country_code) ) {

			$this->liveTrackigEnabled['aramex'] = true;
		}

		// Update the Array with Credentials Status of Tracking Carriers
		PH_Shipment_Tracking_General_Settings::update_settings( $carrierCredentialsStatus, $this->liveTrackigEnabled );

		add_thickbox();

		include('views/html-ph-tracking-settings.php');
	}

	public function selected_status( $currentStatus, $selectedStatusArray ) {

		if( in_array( $currentStatus, $selectedStatusArray ) ) {
			return "selected";
		}else{
			return "";
		}		
	}

	/**
	 * Admin Import Screen output
	 */
	public function output() {
		$tab = 'settings';
		if (!empty($_GET['tab'])) {
			if ($_GET['tab'] == 'settings') {
				$tab = 'settings';
			}
		}

		include( 'views/html-wf-admin-screen.php' );
	}

	/**
	 * Admin Order Screen Output
	**/
	public function pluginhive_orders() {

		include_once('class-ph-shipment-tracking-orders-page.php');

		$all_orders = new PH_Live_Order_Tracking();

		printf( '<div class="wrap"><h2>%s</h2>', __( 'Live Order Tracking', 'ph-woocommerce-multi-vendor' ) );
		echo '<form id="live-tracking-order-table-form" method="post">';
		$all_orders->views();
		$all_orders->prepare_order_items();
		$all_orders->display();
		
		echo '</form>';
		echo '</div>';

	}

	/**
	 * Ajax Call to Save Carrier
	**/
	public function ph_shipment_tracking_save_carrier() {
		
		$settingsId 			= "ph_shipment_tracking_saved_carrier_list";
		$carrierSettingsId 		= "ph_shipment_tracking_carrier_data";

		$carrierName 	= isset($_POST['selected_carrier']) ? $_POST['selected_carrier'] : '';
		$customName 	= isset($_POST['custom_name']) ? $_POST['custom_name'] : '';
		$customUrl 		= isset($_POST['custom_url']) ? $_POST['custom_url'] : '';

		$uspsUserid 	= isset($_POST['usps_userid']) ? $_POST['usps_userid'] : '';

		$upsUserid 		= isset($_POST['ups_userid']) ? $_POST['ups_userid'] : '';
		$upsPassword 	= isset($_POST['ups_password']) ? $_POST['ups_password'] : '';
		$upsAccessKey 	= isset($_POST['ups_access_key']) ? $_POST['ups_access_key'] : '';

		$canadapostUserid 	= isset($_POST['canadapost_userid']) ? $_POST['canadapost_userid'] : '';
		$canadapostPassword = isset($_POST['canadapost_password']) ? $_POST['canadapost_password'] : '';

		$fedexAccount 		= isset($_POST['fedex_account']) ? $_POST['fedex_account'] : '';
		$fedexMeterNum 		= isset($_POST['fedex_meter_num']) ? $_POST['fedex_meter_num'] : '';
		$fedexServiceKey 	= isset($_POST['fedex_service_key']) ? $_POST['fedex_service_key'] : '';
		$fedexServicePass 	= isset($_POST['fedex_service_pass']) ? $_POST['fedex_service_pass'] : '';

		$bluedartUserid 	= isset($_POST['bluedart_userid']) ? $_POST['bluedart_userid'] : '';
		$bluedartApiKey 	= isset($_POST['bluedart_api_key']) ? $_POST['bluedart_api_key'] : '';

		$delhiveryUserid 	= isset($_POST['delhivery_userid']) ? $_POST['delhivery_userid'] : '';
		$delhiveryApiKey 	= isset($_POST['delhivery_api_key']) ? $_POST['delhivery_api_key'] : '';

		$dhlSiteid 	= isset($_POST['dhl_siteid']) ? $_POST['dhl_siteid'] : '';
		$dhlApiKey 	= isset($_POST['dhl_api_key']) ? $_POST['dhl_api_key'] : '';

		$auAccountNum 	= isset($_POST['au_account_num']) ? $_POST['au_account_num'] : '';
		$auApiKey 		= isset($_POST['au_api_key']) ? $_POST['au_api_key'] : '';
		$auPassword 	= isset($_POST['au_password']) ? $_POST['au_password'] : '';

		$aramexUserName 	= isset($_POST['aramex_user_name']) ? $_POST['aramex_user_name'] : '';
		$aramexPassword 	= isset($_POST['aramex_password']) ? $_POST['aramex_password'] : '';
		$aramexAccountNum 	= isset($_POST['aramex_account_num']) ? $_POST['aramex_account_num'] : '';
		$aramexAccountPin 	= isset($_POST['aramex_account_pin']) ? $_POST['aramex_account_pin'] : '';
		$aramexEntity 		= isset($_POST['aramex_entity']) ? $_POST['aramex_entity'] : '';
		$aramexCountryCode 	= isset($_POST['aramex_country_code']) ? $_POST['aramex_country_code'] : '';

		$carrierSettings 	= PH_Shipment_Tracking_General_Settings::get_settings( $carrierSettingsId );

		$default_carriers	= Ph_Shipment_Tracking_Util::load_tracking_data( true, true );
		// Add Custom Carrier to Tracking Carrier List
		if( $carrierName == 'custom-carrier' ) {

			$carrierName 		= sanitize_title($customName);

			// Do not add Defailt Carrier as Custom Carrier
			if( !empty($carrierSettings) && is_array($carrierSettings) && !( array_key_exists($carrierName, $default_carriers) ) ) {

				$carrierSettings[$carrierName] 	= array(
					'name' 				=> $customName,
					'tracking_url' 		=> $customUrl,
					'api_url' 			=> '',
				);

				PH_Shipment_Tracking_General_Settings::update_settings( $carrierSettingsId, $carrierSettings );
			}
		} else {

			if( !empty($carrierSettings) && is_array($carrierSettings) && isset($carrierSettings[$carrierName]) ) {

				$carrierSettings[$carrierName]['name'] = $customName;

				if( $carrierName == 'aramex' ) {
					$carrierSettings[$carrierName]['api_url'] = 'http://ws.aramex.net/ShippingAPI/v1/';
				}

				PH_Shipment_Tracking_General_Settings::update_settings( $carrierSettingsId, $carrierSettings );
			}
		}

		// If anyone tries to add Default Carrier as Custom Carrier, make Custom URL as empty
		if( array_key_exists($carrierName, $default_carriers) ) {

			$customUrl = '';
		}

		if( $carrierName == 'united-states-postal-service-usps' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'userid'	=> $uspsUserid,
			);

		} else if ( $carrierName == 'ups' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'userid'	=> $upsUserid,
				'password'	=> $upsPassword,
				'accesskey'	=> $upsAccessKey,
			);

		} else if ( $carrierName == 'canada-post' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'userid'	=> $canadapostUserid,
				'password'	=> $canadapostPassword,
			);

		} else if ( $carrierName == 'fedex' ) {

			$carrierDetails 	= array(
				'name'			=> $customName,
				'url'			=> $customUrl,
				'accountnum'	=> $fedexAccount,
				'meternum'		=> $fedexMeterNum,
				'servicekey'	=> $fedexServiceKey,
				'servicepass'	=> $fedexServicePass,
			);

		} else if ( $carrierName == 'blue-dart' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'userid'	=> $bluedartUserid,
				'apikey'	=> $bluedartApiKey,
			);

		} else if ( $carrierName == 'delhivery' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'userid'	=> $delhiveryUserid,
				'apikey'	=> $delhiveryApiKey,
			);

		} else if ( $carrierName == 'dhl-express' ) {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
				'siteid'	=> $dhlSiteid,
				'apikey'	=> $dhlApiKey,
			);

		} else if ( $carrierName == 'australia-post' ) {

			$carrierDetails 	= array(
				'name'			=> $customName,
				'url'			=> $customUrl,
				'accountnum'	=> $auAccountNum,
				'apikey'		=> $auApiKey,
				'password'		=> $auPassword,
			);

		} else if ( $carrierName == 'aramex' ) {

			$carrierDetails 	= array(
				'name'			=> $customName,
				'url'			=> $customUrl,
				'user_name'		=> $aramexUserName,
				'password'		=> $aramexPassword,
				'account_num'	=> $aramexAccountNum,
				'account_pin'	=> $aramexAccountPin,
				'entity'		=> $aramexEntity,
				'country_code'	=> $aramexCountryCode,
			);

		} else {

			$carrierDetails 	= array(
				'name'		=> $customName,
				'url'		=> $customUrl,
			);

		}

		$saved_carriers 	= PH_Shipment_Tracking_General_Settings::get_settings( $settingsId );

		// Check for existing carriers, if yes - append
		if( !empty($saved_carriers) && is_array($saved_carriers) ) {

			$saved_carriers[$carrierName] 	= [];
			$saved_carriers[$carrierName] 	= $carrierDetails;

			PH_Shipment_Tracking_General_Settings::update_settings( $settingsId, $saved_carriers );

		} else {
			
			$carrierData 				= [];
			$carrierData[$carrierName] 	= [];
			$carrierData[$carrierName] 	= $carrierDetails;
			
			PH_Shipment_Tracking_General_Settings::update_settings( $settingsId, $carrierData );
		}

		return array();
	}

	/**
	 * Ajax call to Edit Carrier
	**/
	public function ph_shipment_tracking_edit_carrier() {

		$savedListSettingsId 	= "ph_shipment_tracking_saved_carrier_list";
		$savedListSettings 		= PH_Shipment_Tracking_General_Settings::get_settings( $savedListSettingsId );
		$editingCarrier 		= isset($_GET['editing_carrier']) ? $_GET['editing_carrier'] : '';

		// Editing ThickBox is added by Ajax Link
		echo '<div id="a" name="a" style="margin: auto; display: grid; overflow: hidden;text-align: center;">';

		echo '<br/>';

		echo '<table class="new_carrier_details" cellpadding="5">';

			echo '<tbody>';

				echo '<tr>';
					echo '<th style="text-align:left;width:33%"><i>Shipping Carrier</i></th>';
					echo '<td>';
						echo '<select id="ph_edit_tracking_carrier" name="ph_tracking_carrier" style="width: 100%;">';
							
							foreach ( $savedListSettings as $carrierName => $carrierData ) {

								if( $editingCarrier == $carrierName ) {
									echo '<option value="' . esc_attr( $carrierName ) . '" >' .strtoupper( str_replace('-', ' ', $carrierName) ). '</option>';
								}
							}
						echo '</select>';
					echo '</td>';
				echo '</tr>';

				$data 	= $savedListSettings[$editingCarrier];
				$name 	= isset($data['name']) ? $data['name'] : '';
				$url 	= isset($data['url']) ? $data['url'] : '';

				echo '<tr class="">';
				echo '<th style="text-align:left;width:33%"><i>Display Name</i></th>';
				echo '<td><input style="width: 100%;" type="text" name="ph_carrier_custom_name" id="ph_edit_carrier_custom_name" value="'.$name.'" placeholder="Carrier Name" required></td>';
				echo '<tr>';

				if( !empty($url) ) {

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Tracking URL</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_custom_carrier_url" id="ph_edit_custom_carrier_url" value="'.$url.'" placeholder="Tracking URL" required></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'united-states-postal-service-usps' ) {

					$uspsUserid 	= isset($data['userid']) ? $data['userid'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your USPS Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>User Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_usps_user_id" id="ph_edit_usps_user_id" value="'.$uspsUserid.'" placeholder="User Id"></td>';
					echo '<tr>';

				}

				if( $editingCarrier == 'ups' ) {

					$upsUserid 		= isset($data['userid']) ? $data['userid'] : '';
					$upsPassword 	= isset($data['password']) ? $data['password'] : '';
					$upsAccessKey 	= isset($data['accesskey']) ? $data['accesskey'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your UPS Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>User Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_ups_user_id" id="ph_edit_ups_user_id" value="'.$upsUserid.'" placeholder="User Id"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_ups_password" id="ph_edit_ups_password" value="'.$upsPassword.'" placeholder="Password"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Access Key</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_ups_access_key" id="ph_edit_ups_access_key" value="'.$upsAccessKey.'" placeholder="Access Key"></td>';
					echo '<tr>';

				}

				if( $editingCarrier == 'canada-post' ) {

					$canadapostUserid 		= isset($data['userid']) ? $data['userid'] : '';
					$canadapostPassword 	= isset($data['password']) ? $data['password']: '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your Canada Post Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>User Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_canadapost_user_id" id="ph_edit_canadapost_user_id" value="'.$canadapostUserid.'" placeholder="User Id"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_canadapost_user_password" id="ph_edit_canadapost_user_password" value="'.$canadapostPassword.'" placeholder="Password"></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'fedex' ) {

					$fedexAccount 		= isset($data['accountnum']) ? $data['accountnum'] : '';
					$fedexMeterNum 		= isset($data['meternum']) ? $data['meternum'] : '';
					$fedexServiceKey 	= isset($data['servicekey']) ? $data['servicekey'] : '';
					$fedexServicePass 	= isset($data['servicepass']) ? $data['servicepass'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your FedEx Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_fedex_account_number" id="ph_edit_fedex_account_number" value="'.$fedexAccount.'" placeholder="Account Number"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Meter Number</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_fedex_meter_number" id="ph_edit_fedex_meter_number" value="'.$fedexMeterNum.'" placeholder="Meter Number"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Web Services Key</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_fedex_web_service_key" id="ph_edit_fedex_web_service_key" value="'.$fedexServiceKey.'" placeholder="Web Services Key"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Web Services Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_fedex_web_service_password" id="ph_edit_fedex_web_service_password" value="'.$fedexServicePass.'" placeholder="Web Services Password"></td>';
					echo '<tr>';

				}

				if( $editingCarrier == 'blue-dart' ) {

					$bluedartUserid 		= isset($data['userid']) ? $data['userid'] : '';
					$bluedartApiKey 		= isset($data['apikey']) ? $data['apikey'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your Blue Dart Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>User Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_bluedart_user_id" id="ph_edit_bluedart_user_id" value="'.$bluedartUserid.'" placeholder="User Id"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>API Key</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_bluedart_api_key" id="ph_edit_bluedart_api_key" value="'.$bluedartApiKey.'" placeholder="API Key"></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'delhivery' ) {

					$delhiveryUserid 		= isset($data['userid']) ? $data['userid'] : '';
					$delhiveryApiKey 		= isset($data['apikey']) ? $data['apikey'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your Delhivery Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>User Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_delhivery_user_id" id="ph_edit_delhivery_user_id" value="'.$delhiveryUserid.'" placeholder="User Id"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>API Key</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_delhivery_api_key" id="ph_edit_delhivery_api_key" value="'.$delhiveryApiKey.'" placeholder="API Key"></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'dhl-express' ) {

					$dhlSiteid 		= isset($data['siteid']) ? $data['siteid'] : '';
					$dhlApiKey 		= isset($data['apikey']) ? $data['apikey'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your DHL Express Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Site Id</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_dhlexpress_site_id" id="ph_edit_dhlexpress_site_id" value="'.$dhlSiteid.'" placeholder="Site Id"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_dhlexpress_api_key" id="ph_edit_dhlexpress_api_key" value="'.$dhlApiKey.'" placeholder="Password"></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'australia-post' ) {

					$auAccountNum 	= isset($data['accountnum']) ? $data['accountnum'] : '';
					$auApiKey 		= isset($data['apikey']) ? $data['apikey'] : '';
					$auPassword 	= isset($data['password']) ? $data['password'] : '';

					echo '<tr class="">';
					echo '<th colspan="2"><hr/><br/>Enter your Australia Post Account Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_au_account_num" id="ph_edit_au_account_num" value="'.$auAccountNum.'" placeholder="Account Number"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>API Key</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_au_api_key" id="ph_edit_au_api_key" value="'.$auApiKey.'" placeholder="API Key"></td>';
					echo '<tr>';

					echo '<tr class="">';
					echo '<th style="text-align:left;width:33%"><i>API Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_au_password" id="ph_edit_au_password" value="'.$auPassword.'" placeholder="API Password"></td>';
					echo '<tr>';
				}

				if( $editingCarrier == 'aramex' ) {

					$aramexUserName 	= isset($data['user_name']) ? $data['user_name'] : '';
					$aramexPassword 	= isset($data['password']) ? $data['password'] : '';
					$aramexAccountNum 	= isset($data['account_num']) ? $data['account_num'] : '';
					$aramexAccountPin 	= isset($data['account_pin']) ? $data['account_pin'] : '';
					$aramexEntity 		= isset($data['entity']) ? $data['entity'] : '';
					$aramexCountryCode 	= isset($data['country_code']) ? $data['country_code'] : '';

					echo '<tr class="appendedTracking">';
					echo '<th colspan="2"><hr/><br/>Enter your Aramex Credentials for Live Tracking (optional)<br/><br/></th>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>User Name</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_aramex_username" id="ph_edit_aramex_username" value="'.$aramexUserName.'" placeholder="User Name"></td>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>Password</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_aramex_password" id="ph_edit_aramex_password" value="'.$aramexPassword.'" placeholder="Password"></td>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_aramex_accountnum" id="ph_edit_aramex_accountnum" value="'.$aramexAccountNum.'" placeholder="Account Number"></td>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>Account Pin</i></th>';
					echo '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_aramex_accountpin" id="ph_edit_aramex_accountpin" value="'.$aramexAccountPin.'" placeholder="Account Pin"></td>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>Account Entity</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_aramex_accountentity" id="ph_edit_aramex_accountentity" value="'.$aramexEntity.'" placeholder="AMM"></td>';
					echo '<tr>';

					echo '<tr class="appendedTracking">';
					echo '<th style="text-align:left;width:33%"><i>Account Country Code</i></th>';
					echo '<td><input style="width: 100%;" type="text" name="ph_aramex_countrycode" id="ph_edit_aramex_countrycode" maxlength="2" value="'.$aramexCountryCode.'" placeholder="Country Code"></td>';
					echo '<tr>';
				}

			echo '</tbody>';

			echo '<tfoot>';

				echo '<tr class="">';
				echo '<th colspan="2"><br/>';
				echo '<input type="button" id="edit_carrier" class="button button-primary edit_carrier" value="Update Carrier" style="width:25%; margin: auto 2px;" />';
				echo '<input type="submit" name="remove_modal" class="button remove_modal" value="Cancel" style="width:25%; margin: auto 2px;"/>';
				echo '</th>';
				echo '<tr>';

			echo '</tfoot>';

		echo '</table>';
		echo '</div>';

		?>
		<script type="text/javascript" src="<?php echo plugins_url(basename(plugin_dir_path(WF_ShipmentTracking_FILE)) . '/js/shipment-tracking-admin.js', basename(__FILE__) );?>"></script>
		<?php
		die();
	}

	/**
	 * Ajax call to Delete Carrier
	**/
	public function ph_shipment_tracking_delete_carrier() {

		$settingsId 		= 'ph_shipment_tracking_saved_carrier_list';
		$carrierSettingsId 	= "ph_shipment_tracking_carrier_data";
		$carrierName 		= isset($_POST['selected_carrier']) ? $_POST['selected_carrier'] : '';

		$saved_carriers 	= PH_Shipment_Tracking_General_Settings::get_settings( $settingsId );
		$all_carriers 		= PH_Shipment_Tracking_General_Settings::get_settings( $carrierSettingsId );

		if( !empty($saved_carriers) && is_array($saved_carriers) && isset($saved_carriers[$carrierName]) ) {

			unset($saved_carriers[$carrierName]);

			PH_Shipment_Tracking_General_Settings::update_settings( $settingsId, $saved_carriers );
		}

		$default_carriers	= Ph_Shipment_Tracking_Util::load_tracking_data( true, true );

		if( !empty($all_carriers) && !empty($default_carriers) && !( array_key_exists($carrierName, $default_carriers) ) && isset($all_carriers[$carrierName]) ) {

			unset($all_carriers[$carrierName]);

			PH_Shipment_Tracking_General_Settings::update_settings( $carrierSettingsId, $all_carriers );
		}

	}

	/**
	 * Admin Settings Page for Import
	 */
	public function admin_settings_page() {
		include( 'views/settings/html-wf-settings-shipment-tracking.php' );
	}

}
new WF_Shipment_Tracking_Admin_Screen();