<?php
/*
	Plugin Name: WooCommerce Shipment Tracking Pro
	Plugin URI: https://www.pluginhive.com/product/woocommerce-shipment-tracking-pro/
	Description: Provide live shipment tracking details. Engage customers with a tracking look up page, Send tracking update emails to customers, Automatically mark orders as Completed, Schedule Automatic Imports via CSV or FTP/SFTP.
	Version: 2.5.4
	Author: PluginHive
	Author URI: https://www.pluginhive.com/
	Copyright: PluginHive
	Text Domain: woocommerce-shipment-tracking
	WC requires at least: 2.6.0
	WC tested up to: 4.2.2
*/


	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	// Define PH_SHIPMENT_TRACKING_PLUGIN_VERSION
	if ( ! defined( 'PH_SHIPMENT_TRACKING_PLUGIN_VERSION' ) )
		define( 'PH_SHIPMENT_TRACKING_PLUGIN_VERSION', '2.5.4' );

	// Include API Manager
	if ( !class_exists( 'PH_Shipment_Tracking_API_Manager' ) ) {

		include_once( 'ph-api-manager/ph_api_manager_shipment_tracking.php' );
	}

	if ( class_exists( 'PH_Shipment_Tracking_API_Manager' ) ) {

		$shipment_tracking_api_obj 	= new PH_Shipment_Tracking_API_Manager( __FILE__, '', PH_SHIPMENT_TRACKING_PLUGIN_VERSION, 'plugin', 'https://www.pluginhive.com/', 'Shipment Tracking' );
	}

	/**
	 * Plugin activation check woocommerce_shipment_tracking (wst)
	 */
	if( !function_exists('wf_wst_basic_pre_activation_check') )
	{
		function wf_wst_basic_pre_activation_check(){
		//check if basic version is there
			if ( is_plugin_active('woo-shipment-tracking-order-tracking/woocommerce-shipment-tracking.php') ){
				deactivate_plugins( basename( __FILE__ ) );
				wp_die( __("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete WooCommerce Shipment Tracking Basic Woocommerce Extension and then try again.", "woocommerce-shipment-tracking" ), "", array('back_link' => 1 ));
			}
		}
	}
	register_activation_hook( __FILE__, 'wf_wst_basic_pre_activation_check' );

	define( "WF_SHIPMENT_TRACKING_IMP_EXP_ID", "wf_shipment_tracking_csv_im_ex" );
	define( "WF_SHIPMENT_TRACKING_CSV_IM_EX", "import_shipment_tracking_csv" );
	define( "PH_SHIPMENT_TRACKING_PLUGIN_PATH", __DIR__ );

	if( ! defined('PH_SHIPMENT_TRACKING_STORE_ID_URL') ) define( 'PH_SHIPMENT_TRACKING_STORE_ID_URL', 'https://track-api.pluginhive.com' );

	/**
	 * Common Class.
	 */
	if( ! class_exists('PH_Shipment_Tracking_Common') ) {
		require_once 'woocommerce-shipment-tracking-common.php';
	}

	/**
	 * Check if WooCommerce is active
	 */
	$ph_active_plugins = PH_Shipment_Tracking_Common::get_active_plugins();

	if (in_array( 'woocommerce/woocommerce.php', $ph_active_plugins ) && !class_exists('WooCommerce_Shipment_Tracking') ) {

	/**
	 * WooCommerce_Shipment_Tracking class
	 */
	class WooCommerce_Shipment_Tracking {

		public $cron_shipment_import;
		public $cron_live_tracking_status;
		/**
		 * Constructor
		 */
		public function __construct() {

			define( 'WF_ShipmentTracking_FILE', __FILE__ );
			add_action( 'init', array( $this, 'init' ) );
			add_action(	'rest_api_init', array( $this, 'init_storepep_mobile_app_rest_api' ), 100);
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wf_plugin_action_links' ) );
			add_action( 'init', array( $this, 'catch_save_settings' ), 20 );

			require_once( 'includes/class-wf-shipment-tracking-import-cron.php' );

			$this->cron_shipment_import = new WF_ShipmentTracking_ImportCron();

			register_activation_hook(__FILE__, array( $this->cron_shipment_import, 'wf_shipment_tracking_new_scheduled_import'));
			register_deactivation_hook( __FILE__, array( $this->cron_shipment_import, 'clear_wf_shipment_tracking_scheduled_import' ) );

			add_action( 'wp_ajax_ph_shipment_tracking_ftp_test', array( $this, 'ph_shipment_tracking_ftp_test') );

			/*** Actions on Live Tracking Status ***/
			require_once ( 'includes/live-status/class-ph-live-tracking-status-cron.php' );

			$this->cron_live_tracking_status = new PH_Cron_For_Live_Tracking_Status();
			
			register_activation_hook(__FILE__, array( $this->cron_live_tracking_status, 'ph_shipment_tracking_live_status_scheduled_check'));
			register_deactivation_hook( __FILE__, array( $this->cron_live_tracking_status, 'clear_ph_shipment_tracking_live_status_scheduled_check' ) );

			require_once ( 'includes/live-status/class-ph-live-tracking-order-manager.php' );
			require_once ( 'includes/live-status/class-ph-live-tracking-status-mapper.php' );
			/*** End of Actions on Live Tracking Status ***/

		}

		// StorePep Mobile Rest API.
		public function init_storepep_mobile_app_rest_api() {

			if( ! class_exists('Storepep_Woocommerce_Mobile_App_Compatibility') ) {
				require_once 'includes/compatibility/class-ph-shipment-tracking-storepep-mobile-app-compatibility.php';
			}
			$object = Ph_Shipment_Tracking_Storepep_Mobile_App_Compatibility::instance();
			$object->init();
		}

		/**
		 * Test FTP status.
		 */
		public function ph_shipment_tracking_ftp_test(){
			include 'includes/ftp_test.php';
			wp_die();
		}

		public function init() {

			// Data Migration to Support New Settings View 
			if( ! class_exists('PH_Tracking_Data_Migration') ) {
				require_once "ph-data-migration.php";
			}

			new PH_Tracking_Data_Migration();

			if ( ! class_exists( 'wf_order' ) ) {
				include_once 'includes/class-wf-legacy.php';
			}
			include_once ( 'includes/class-wf-tracking-admin.php' );
			// include_once ( 'includes/class-wf-tracking-settings.php' );
			include_once ( 'includes/class-wf-shipment-tracking-setup.php' );
			include_once ( 'includes/class-wf-shipment-tracking-admin-screen.php' );

			if( ! class_exists('PH_Shipment_Tracking_API') ) {
				include_once ( 'includes/class-ph-shipment-tracking-api.php' );
			}

			require_once ( 'includes/class-shipment-tracking-shortcodes.php');

			include_once ( 'includes/class-ph-shipment-tracking-general-settings.php' );

			new Ph_Shipment_Tracking_Shortcodes();
			
			if ( is_admin() ) {
				include_once 'includes/class-ph-shipment-tracking-on-order-page.php';
			}
			$this->third_party_comaptibility();

			// Localisation
			load_plugin_textdomain( 'woocommerce-shipment-tracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * It will handle compatibility with third party plugins.
		 */
		private function third_party_comaptibility() {

			include_once 'includes/compatibility/class-ph-shipment-tracking-third-party-compatibility.php';

			// For shortcode to display tracking details
			require_once 'includes/compatibility/func-ph-shortcode-shipment-tracking-details.php';

			// For PluginHive Shipment Plugins Integration
			require_once 'includes/compatibility/func-pluginhive-shipping-plugins-integration.php';

		}

		/**
		 * Plugin page links
		 */
		public function wf_plugin_action_links( $links ) {

			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=shipment_tracking_pro' ) . '">' . __( 'Settings', 'woocommerce-shipment-tracking' ) . '</a>',
				'<a href="' . admin_url( 'admin.php?import=import_shipment_tracking_csv' ) . '">' . __( 'Import', 'woocommerce-shipment-tracking' ) . '</a>',
				'<a href="https://www.pluginhive.com/knowledge-base/setting-woocommerce-shipment-tracking-pro-plugin/" target="_blank">' . __('Documentation', 'woocommerce-shipment-tracking') . '</a>',
				'<a href="https://www.pluginhive.com/support/" target="_blank">' . __('Support', 'woocommerce-shipment-tracking') . '</a>'
			);
			return array_merge( $plugin_links, $links );
		}
		
		static function wf_plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		
		static function wf_plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function catch_save_settings() {
			if ( ! empty( $_GET['action'] ) && ! empty( $_GET['page'] ) && $_GET['page'] == 'import_shipment_tracking_csv' ) {
				switch ( $_GET['action'] ) {
					case "settings" :
					include_once( 'includes/settings/class-wf-shipment-tracking-settings.php' );
					WF_Shipment_Tracking_Settings::save_settings( );
					break;
				}
			}
		}
	}
	
	new WooCommerce_Shipment_Tracking();
}