<?php
/**
 * Plugin Name: WooCommerce - PDF Vouchers
 * Plugin URI:  https://wpwebelite.com/
 * Description: With Pdf Vouchers Extension, you can create unlimited vouchers, either for Local Businesses / Local Stores or even online stores. The sky is the limit.
 * Version: 4.1.10
 * Author: WPWeb
 * Author URI: https://wpwebelite.com/
 * Text Domain: woovoucher
 * Domain Path: languages
 * 
 * WC tested up to: 4.2.0
 * Tested up to: 5.4.1
 * 
 * @package WooCommerce - PDF Vouchers
 * @category Core
 * @author WPWeb
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Basic plugin definitions
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
if( !defined( 'WOO_VOU_PLUGIN_VERSION' ) ) {
	define( 'WOO_VOU_PLUGIN_VERSION', '4.1.10' ); //Plugin version number
}
if( !defined( 'WOO_VOU_DIR' ) ) {
	define( 'WOO_VOU_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'WOO_VOU_URL' ) ) {
	define( 'WOO_VOU_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'WOO_VOU_ADMIN' ) ) {
	define( 'WOO_VOU_ADMIN', WOO_VOU_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'WOO_VOU_IMG_DIR' ) ) {
	define( 'WOO_VOU_IMG_DIR', WOO_VOU_DIR.'/includes/images' ); // plugin image dir
}
if( !defined( 'WOO_VOU_IMG_URL' ) ) {
	define( 'WOO_VOU_IMG_URL', WOO_VOU_URL.'includes/images' ); // plugin image url
}
if( !defined( 'WOO_VOU_META_DIR' ) ) {
	define( 'WOO_VOU_META_DIR', WOO_VOU_DIR . '/includes/meta-boxes' ); // path to meta boxes
}
if( !defined( 'WOO_VOU_META_URL' ) ) {
	define( 'WOO_VOU_META_URL', WOO_VOU_URL . 'includes/meta-boxes' ); // path to meta boxes
}
if( !defined( 'WOO_VOU_META_PREFIX' ) ) {
	define( 'WOO_VOU_META_PREFIX', '_woo_vou_' ); // meta box prefix
}
if( !defined( 'WOO_VOU_ORDER_META_PREFIX' ) ) {
	define( 'WOO_VOU_ORDER_META_PREFIX', 'woo_vou_' ); // order meta data box prefix
}
if( !defined( 'WOO_VOU_POST_TYPE' ) ) {
	define( 'WOO_VOU_POST_TYPE', 'woovouchers' ); // custom post type voucher templates
}
if( !defined( 'WOO_VOU_CODE_POST_TYPE' ) ) {
	define( 'WOO_VOU_CODE_POST_TYPE', 'woovouchercodes' ); // custom post type voucher codes
}
if( !defined( 'WOO_VOU_PARTIAL_REDEEM_POST_TYPE' ) ) {
	define( 'WOO_VOU_PARTIAL_REDEEM_POST_TYPE', 'woovoupartredeem' ); // woocommerce partial redeem post type
}
if( !defined( 'WOO_VOU_UNLIMITED_REDEEM_POST_TYPE' ) ) {
	define( 'WOO_VOU_UNLIMITED_REDEEM_POST_TYPE', 'woovounolimitredeem' ); // woocommerce partial redeem post type
}
if( !defined( 'WOO_VOU_MAIN_POST_TYPE' ) ) {
	define( 'WOO_VOU_MAIN_POST_TYPE', 'product' ); //woocommerce post type
}
if( !defined( 'WOO_VOU_MAIN_SHOP_POST_TYPE' ) ) {
	define( 'WOO_VOU_MAIN_SHOP_POST_TYPE', 'shop_order' ); //woocommerce post type
}
if( !defined( 'WOO_VOU_MAIN_MENU_NAME' ) ) {
	define( 'WOO_VOU_MAIN_MENU_NAME', 'woocommerce' ); //woocommerce main menu name
}
if( !defined( 'WOO_VOU_PLUGIN_BASENAME' ) ) {
	define( 'WOO_VOU_PLUGIN_BASENAME', basename( WOO_VOU_DIR ) ); //Plugin base name
}
if( !defined( 'WOO_VOU_PLUGIN_BASE_FILENAME' ) ) {
	define( 'WOO_VOU_PLUGIN_BASE_FILENAME', basename( __FILE__ ) ); //Plugin base file name
}
if ( ! defined( 'WOO_VOU_PLUGIN_KEY' ) ) {
	define( 'WOO_VOU_PLUGIN_KEY', 'woovouchers' ); // plugin key
}
if ( ! defined( 'WOO_VOU_REFUND_STATUS' ) ) {
	define( 'WOO_VOU_REFUND_STATUS', 'wpv-refunded' ); // refund status
}
if ( ! defined( 'WOO_VOU_AVAILABLE_EXTENSIONS' ) ) {
	define( 'WOO_VOU_AVAILABLE_EXTENSIONS', 2 ); // No.of add-ons
}

//Get Vendor Role name
if( !defined( 'WOO_VOU_VENDOR_ROLE' ) ) {
	define( 'WOO_VOU_VENDOR_ROLE', 'woo_vou_vendors' ); //plugin vendor role
}
if( !defined( 'WOO_VOU_VENDOR_LEVEL' ) ) {
	define( 'WOO_VOU_VENDOR_LEVEL' , 'woo_vendor_options' ); //plugin vendor capability
}

$upload_dir		= wp_upload_dir();
$upload_path	= isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'].'/' : ABSPATH;
$upload_url		= isset( $upload_dir['baseurl'] ) ? $upload_dir['baseurl'] : site_url();

// Pdf voucher upload dir for email
if( !defined( 'WOO_VOU_UPLOAD_DIR' ) ) {
	define( 'WOO_VOU_UPLOAD_DIR' , $upload_path . 'woocommerce_uploads/wpv-uploads/' ); // Voucher upload dir
}
// Pdf voucher upload dir for email
if( !defined( 'WOO_VOU_PREVIEW_UPLOAD_DIR' ) ) {
	define( 'WOO_VOU_PREVIEW_UPLOAD_DIR' , $upload_path . 'wpv-preview-uploads/' ); // Voucher upload dir
}

// Pdf voucher upload url for email
if( !defined( 'WOO_VOU_UPLOAD_URL' ) ) {
	define( 'WOO_VOU_UPLOAD_URL' , $upload_url . '/woocommerce_uploads/wpv-uploads/' ); // Voucher upload url
}
// Pdf voucher upload url for email
if( !defined( 'WOO_VOU_PREVIEW_UPLOAD_URL' ) ) {
	define( 'WOO_VOU_PREVIEW_UPLOAD_URL' , $upload_url . '/wpv-preview-uploads/' ); // Voucher upload url
}

// Required Wpweb updater functions file
if ( ! function_exists( 'wpweb_updater_install' ) ) {
	require_once( 'includes/wpweb-upd-functions.php' );
}

global $woo_vou_vendor_role;

// loads the Voucher Template Functions file
require_once ( WOO_VOU_DIR . '/includes/woo-vou-template-html.php' );

// loads the Misc Functions file
require_once ( WOO_VOU_DIR . '/includes/woo-vou-misc-functions.php' );

//Post type to handle custom post type
require_once( WOO_VOU_DIR . '/includes/woo-vou-post-types.php' );

//Pagination Class
require_once( WOO_VOU_DIR . '/includes/class-woo-vou-pagination-public.php' ); // front end pagination class

// loads the shortcode functions file
require_once ( WOO_VOU_DIR . '/includes/woo-vou-shortcode-functions.php' );

// loads the check voucher code page functions file
require_once ( WOO_VOU_DIR . '/includes/woo-vou-cvc-functions.php' );

// loads the public functions file
require_once ( WOO_VOU_DIR . '/includes/public/woo-vou-public-functions.php' );

// loads the public functions file
require_once ( WOO_VOU_DIR . '/includes/public/woo-vou-voucher-functions.php' );

// loads the admin functions file
require_once ( WOO_VOU_DIR . '/includes/admin/woo-vou-admin-functions.php' );

// Load the plugin activation file
include_once ( WOO_VOU_DIR . '/includes/woo-vou-install.php' );

// Load the plugin deactivation file
include_once ( WOO_VOU_DIR . '/includes/woo-vou-uninstall.php' );

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'woo_vou_install' );

/**
 * Deactivation Hook
 * 
 * Register plugin deactivation hook.
 * 
 * @package WooCommerce - PDF Vouchers
 *  @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'woo_vou_uninstall' );

/**
 * Check if current page is edit page.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_is_edit_page() {
	
	global $pagenow;
	
	return in_array( $pagenow, array( 'post.php', 'post-new.php', 'user-edit.php', 'profile.php' ) );
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.3
 */
function woo_vou_load_text_domain() {
	
	// Set filter for plugin's languages directory
	$woo_vou_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$woo_vou_lang_dir	= apply_filters( 'woo_vou_languages_directory', $woo_vou_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'woovoucher' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'woovoucher', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $woo_vou_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . WOO_VOU_PLUGIN_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/woocommerce-pdf-vouchers folder
		load_textdomain( 'woovoucher', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/woocommerce-pdf-vouchers/languages/ folder
		load_textdomain( 'woovoucher', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'woovoucher', false, $woo_vou_lang_dir );
	}
}



//add action to load plugin
add_action( 'plugins_loaded', 'woo_vou_plugin_loaded', 12);

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_plugin_loaded() {
	
	//check Woocommerce is activated or not
	if( class_exists( 'Woocommerce' ) ) {
		
		/**
		 * Add plugin action links
		 *
		 * Adds a Settings, Support and Docs link to the plugin list.
		 *
		 * @package WooCommerce - PDF Vouchers
		 * @since 2.2.0
		 */
		function woo_vou_add_plugin_links( $links ) {
			$plugin_links = array(
				'<a href="admin.php?page=wc-settings&tab=woo-vou-settings">' . esc_html__( 'Settings', 'woovoucher' ) . '</a>',
				'<a href="https://support.wpwebelite.com/">' . esc_html__( 'Support', 'woovoucher' ) . '</a>',
				'<a href="https://docs.wpwebelite.com/woocommerce-pdf-vouchers/">' . esc_html__( 'Docs', 'woovoucher' ) . '</a>'
			);

			return array_merge( $plugin_links, $links );
		}

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_vou_add_plugin_links' );

		// load first plugin text domain
		woo_vou_load_text_domain();

		/**
		 * Action To Initialize Vendors Role
		 * 
		 * Handles to action for Initialize role
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		add_action( 'init', 'woo_vou_add_other_role_to_vendor', 9 );

		/**
		 * Initialize Vendor Role
		 * 
		 * Handles to Initialize vendor role
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		function woo_vou_add_other_role_to_vendor() {

			//Initilize pdf voucher plugin
			woo_vou_vendor_initilize();
			
			// IF Add-on tab select
			if( isset($_GET['section']) && $_GET['section'] == 'vou_addon'  ){
				 update_option('woo_vou_viewed_extensions',WOO_VOU_AVAILABLE_EXTENSIONS);
			}
		}
        
        // Load the plugin deactivation file
        include_once ( WOO_VOU_DIR . '/includes/woo-vou-uninstall.php' );
		
		//global variables
		global $woo_vou_scripts,$woo_vou_model,$woo_vou_voucher,$woo_vou_render,
				$woo_vou_shortcode,$woo_vou_admin,$woo_vou_public,
				$woo_vou_admin_meta,$woo_vou_upgrade, 
				$woo_vou_template_shortcodes,$woo_vou_wpml,$woo_vou_order_sms,
				$woo_vou_qtranslatex,$woo_vou_vendor_pro, $woo_vou_wc_booking, $woo_vou_wc_bundles, $woo_vou_eml, $woo_vou_wc_currency_switch, $woo_vou_your_price, $woo_vou_wc_vendor, $woo_vou_wedevs_dokan, $woo_vou_wcmp,$woo_vou_yith_booking;

		//Model class handles most of functionalities of plugin
		include_once( WOO_VOU_DIR . '/includes/class-woo-vou-model.php' );
		$woo_vou_model = new WOO_Vou_Model();

		//Voucher class handles most of functionalities of vouchers in plugin
		include_once( WOO_VOU_DIR . '/includes/public/class-woo-vou-voucher.php' );
		$woo_vou_voucher = new WOO_Vou_Voucher();
		
		// Script Class to manage all scripts and styles
		include_once( WOO_VOU_DIR . '/includes/class-woo-vou-scripts.php' );
		$woo_vou_scripts = new WOO_Vou_Scripts();
		$woo_vou_scripts->add_hooks();
		
		//Render class to handles most of html design for plugin
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-renderer.php' );
		$woo_vou_render = new WOO_Vou_Renderer();
		
		// Admin meta class to handles most of html design for pdf voucher panel
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-admin-meta.php' );
		$woo_vou_admin_meta = new WOO_Vou_Admin_Meta();
		
		//Shortcodes class for handling shortcodes
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-shortcodes.php' );
		$woo_vou_shortcode = new WOO_Vou_Shortcodes();
		$woo_vou_shortcode->add_hooks();
		
		//Public Class to handles most of functionalities of public side
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-public.php');
		$woo_vou_public = new WOO_Vou_Public();
		$woo_vou_public->add_hooks();
		
		//Admin Pages Class for admin side
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-admin.php' );
		$woo_vou_admin = new WOO_Vou_Admin();
		$woo_vou_admin->add_hooks();
		
		//Admin Pages Class for admin side
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-upgrade.php' );
		$woo_vou_upgrade = new WOO_Vou_Upgrade();
		$woo_vou_upgrade->add_hooks();
		
		if( woo_vou_is_edit_page() ) {
			
			//include the meta functions file for metabox
			require_once ( WOO_VOU_META_DIR . '/woo-vou-meta-box-functions.php' );
			
		}
		
		//Export to CSV Process for used voucher codes
		require_once( WOO_VOU_DIR . '/includes/woo-vou-used-codes-export-csv.php' );
		
		//Generate PDF Process for voucher code and used voucher codes
		require_once( WOO_VOU_DIR . '/includes/woo-vou-used-codes-pdf.php' );
		require_once( WOO_VOU_DIR . '/includes/woo-vou-pdf-process.php' );
		
		//Loads the Templates Functions file
		require_once ( WOO_VOU_DIR . '/includes/woo-vou-template-functions.php' );
		
		//Load the Template Hook File
		require_once ( WOO_VOU_DIR . '/includes/woo-vou-template-hooks.php' );
		
		//Load the Voucher Template Custom Shortcodes File
		require_once ( WOO_VOU_DIR . '/includes/class-woo-vou-template-shortcodes.php' );		
		$woo_vou_template_shortcodes = new WOO_Vou_Template_Shortcodes();
		$woo_vou_template_shortcodes->add_hooks();

		// if WC Currency Switcher plugin is activated
		require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-currency-switcher.php' );
		$woo_vou_wc_currency_switch = new WOO_Vou_WC_Currency_Switcher();
		$woo_vou_wc_currency_switch->add_hooks();
		
		// check wpml and woocommerce multilingual plugin is activated
		if( function_exists('icl_object_id') && class_exists('woocommerce_wpml') ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-wpml.php' );
			$woo_vou_wpml = new WOO_Vou_Wpml();
			$woo_vou_wpml->add_hooks();			
		}
		
		// check WC Order SMS Notification plugin is activated
		if( class_exists( 'Sat_WC_Order_SMS' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-order-sms.php' );
			$woo_vou_order_sms = new WOO_Vou_Order_Sms();
			$woo_vou_order_sms->add_hooks();			
		}
		
		// check QTranslateX plugin is activated
		if( defined( 'QTX_VERSION' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-qtranslate-x.php' );
			$woo_vou_qtranslatex = new WOO_Vou_QtranslateX();
			$woo_vou_qtranslatex->add_hooks();
		}

		// if WC Vendor Pro plugin is activated
		if( class_exists( 'WCVendors_Pro' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-vendor-pro.php' );
			$woo_vou_vendor_pro = new WOO_Vou_Vendor_Pro();
			$woo_vou_vendor_pro->add_hooks();								
		}

		// if WC Booking plugin is activated
		if( class_exists( 'WC_Booking' ) ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-booking.php' );
			$woo_vou_wc_booking = new WOO_Vou_WC_Booking();
			$woo_vou_wc_booking->add_hooks();								
		}

		// if Yith Booking plugin is activated
		if( class_exists( 'YITH_WCBK' ) ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-yith-booking.php' );
			$woo_vou_yith_booking = new WOO_Vou_Yith_Booking();
			$woo_vou_yith_booking->add_hooks();								
		}

		// if WC Booking plugin is activated
		if( class_exists( 'WC_Bundles' ) ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-bundles.php' );
			$woo_vou_wc_bundles = new WOO_Vou_WC_Bundles();
			$woo_vou_wc_bundles->add_hooks();								
		}

		// if Enhanced Media Library plugin is activated
		if( defined('EML_VERSION') ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-eml.php' );
			$woo_vou_eml = new WOO_Vou_EML();
			$woo_vou_eml->add_hooks();								
		}

		// if WC Name Your Price & WC Pay Your Price plugin is activated
		if( class_exists('WC_Name_Your_Price') || class_exists('PayYourPrice') ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-your-price.php' );
			$woo_vou_your_price = new WOO_Vou_Your_Price();
			$woo_vou_your_price->add_hooks();								
		}

		// if WC Vendors plugin is activated
		if( class_exists('WCV_Vendors') ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-vendor.php' );
			$woo_vou_wc_vendor = new WOO_Vou_WC_Vendor();
			$woo_vou_wc_vendor->add_hooks();
		}

		// if WeDevs Dokan plugin is activated
		if( class_exists( 'WeDevs_Dokan' ) ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-wedevs-dokan.php' );
			$woo_vou_wedevs_dokan = new WOO_Vou_WeDevs_Dokan();
			$woo_vou_wedevs_dokan->add_hooks();
		}

		// if WC Market place plugin is activated
		if( class_exists('WCMp') ) {
			require_once( WOO_VOU_DIR .'/includes/compatibility/class-woo-vou-wcmp.php' );
			$woo_vou_wcmp = new WOO_Vou_WC_Marketplace();
			$woo_vou_wcmp->add_hooks();
		}



	} //end if to check class Woocommerce is exist or not
	
    // Check if Wpweb Updter is not activated then load updater from plugin itself
    if( !class_exists( 'Wpweb_Upd_Admin' ) ) {
        
        // Load the updater file
        include_once ( WOO_VOU_DIR . '/includes/updater/wpweb-updater.php' );
        // call to updater function
        woo_vou_wpweb_updater();
    } else{ // added code from the end of file to fix the undefind contstant WPWEB_UPD_DOMAIN
		// call to updater function
        woo_vou_wpweb_updater();    	
    }
} //end if to check plugin loaded is called or not

/**
 * Add plugin to updater list and create updater object
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.8
 */
function woo_vou_wpweb_updater() {
    
    // Plugin updates
    wpweb_queue_update( plugin_basename( __FILE__ ), WOO_VOU_PLUGIN_KEY );

    /**
     * Include Auto Updating Files
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */ 
    if( class_exists( 'Wpweb_Upd_Admin' ) )        
        require_once( WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating        
    else
        require_once( WOO_VOU_WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating        

    $WpwebWoovouUpdateChecker = new WpwebPluginUpdateChecker (
        WPWEB_UPD_DOMAIN . '/Updates/WOOVouchers/license-info.php',
        __FILE__,
        WOO_VOU_PLUGIN_KEY
    );

    /**
     * Auto Update
     * 
     * Get the license key and add it to the update checker.
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    function woo_vou_add_secret_key( $query ) {

        $plugin_key	= WOO_VOU_PLUGIN_KEY;

        $query['lickey'] = wpweb_get_plugin_purchase_code( $plugin_key );
        return $query;
    }

    $WpwebWoovouUpdateChecker->addQueryArgFilter( 'woo_vou_add_secret_key' );
}
 