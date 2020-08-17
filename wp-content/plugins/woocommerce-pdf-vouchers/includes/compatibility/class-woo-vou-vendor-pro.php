<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Vendor Pro Class. To enhance compatibility with WC Vendor Pro plugin.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.2
 */
class WOO_Vou_Vendor_Pro {
	
	var $scripts, $model, $render, $voumeta, $vouadmin;
	
	public function __construct() {
		
		global $woo_vou_scripts, $woo_vou_model, $woo_vou_render, $woo_vou_admin_meta, $woo_vou_admin;
		
		$this->scripts 	= $woo_vou_scripts;
		$this->model 	= $woo_vou_model;
		$this->render 	= $woo_vou_render;
		$this->voumeta	= $woo_vou_admin_meta;
		$this->vouadmin = $woo_vou_admin;
		
		// include required files
		$this->includes();
	}
		
	/**
	 * Include required core files used to add compability of WC Vendor Pro
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function includes() {
		
		//Vendor Class for front-end
		require_once ( WOO_VOU_META_DIR . '/woo-vou-meta-box-functions.php' );		
	}
	
	/**
	 * Add PDF Vocuher tab
	 * 
	 * Handels to add PDF Voucher tab
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */ 
	public function woo_vou_product_write_panel_tab( $tabs ) {
		
		// add pdf voucher tab
		$tabs[ 'pdf_vouchers' ]  = array( 
			'label'  => '<span>' . esc_html__( 'PDF Vouchers', 'woovoucher' ) . '</span>',
			'target' => 'woo_vou_voucher',
			'class'  => array( 'woo_vou_voucher_tab', 'show_if_downloadable', 'show_if_variable' ),
		);
				
		return $tabs;
	}

	/**
	 * Add Popup For import Voucher Code in Admin
	 * 
	 * Handels to show import voucher code popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_import_code_popup() {
		
		global $post;

		// get dashboard page id	
		$dashboard_page_id = WCVendors_Pro::get_option('dashboard_page_id');

		if( is_array( $dashboard_page_id ) && !empty( $dashboard_page_id ) ){
			$dashboard_page_id = $dashboard_page_id[0];
		}
		
		//If current page is vendor dashboard page
		if( isset( $post ) && $post->ID == $dashboard_page_id ) {
			
			// include import voucher code popup file
			include_once( WOO_VOU_ADMIN . '/forms/metabox-popups/woo-vou-import-code-popup.php' );
		}
	}	
	
	/**
	 * Save pdf voucher data
	 * 
	 * Handels to save all pdf voucher data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_product_save_meta( $post_id ) {
		
		// call to save function
		woo_vou_product_save_data( $post_id, get_post( $post_id ) );

		// Vendor auto save
		$user_id = get_current_user_id(); // Get current user

		// get prefix
		$prefix = WOO_VOU_META_PREFIX;

		if( isset($_POST['_woo_vou_enable']) && !empty($user_id) ){
			// Vendor User
			update_post_meta( $post_id, $prefix . 'vendor_user', $user_id );
		}
	}	
	
	/**
	 * include JS
	 * 
	 * Handels to add JS on vendor dashboard page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_vendor_scripts() {
		
		global $post, $wp_version, $woocommerce;
				
		$dashboard_page_id = WCVendors_Pro::get_option('dashboard_page_id');

		if( is_array( $dashboard_page_id ) && !empty( $dashboard_page_id ) ){
			$dashboard_page_id = $dashboard_page_id[0];
		}
		
		if( isset( $post ) && $post->ID == $dashboard_page_id ) {
			
			// Enqueue Meta Box Scripts
			wp_enqueue_script( 'woo-vou-meta-box', WOO_VOU_META_URL . '/js/meta-box.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION, true );
			
			//localize script
			$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
			wp_localize_script( 
				'woo-vou-meta-box', 
				'WooVou', 
				array(	
					'new_media_ui'	=>	$newui,
					'one_file_min'	=>  esc_html__('You must have at least one file.','woovoucher' )
				)
			);
			
			// register and enqueue script		
			wp_register_script( 'woo-vou-script-metabox', WOO_VOU_URL.'includes/js/woo-vou-metabox.js', array( 'jquery', 'jquery-form' ), WOO_VOU_PLUGIN_VERSION, true ); 
			wp_enqueue_script( 'woo-vou-script-metabox' );
			
			// localize script
			wp_localize_script( 
				'woo-vou-script-metabox', 
				'WooVouMeta', 			
				array(	
					'invalid_url' 			=> esc_html__( 'Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ),
					'noofvouchererror' 		=> '<div>' . esc_html__( 'Please enter Number of Voucher Codes.', 'woovoucher' ) . '</div>',
					'onlydigitserror' 		=> '<div>' . esc_html__( 'Please enter only Numeric values in Number of Voucher Codes.', 'woovoucher' ) . '</div>',
					'patternemptyerror' 	=> '<div>' . esc_html__( 'Please enter Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
					'generateerror' 		=> '<div>' . esc_html__( 'Please enter Valid Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
					'filetypeerror'			=> '<div>' . esc_html__( 'Please upload csv file.', 'woovoucher' ) . '</div>',
					'fileerror'				=> '<div>' . esc_html__( 'File can not be empty, please upload valid file.', 'woovoucher' ) . '</div>',
					'enable_voucher'        => get_option( 'vou_enable_voucher' ), //Localize "Auto Enable Voucher" setting to use in JS
					'ajaxurl'               => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
				) 
			);
			
			// Enqueue for  image or file uploader
			wp_enqueue_script( 'media-upload' );
			add_thickbox();
			wp_enqueue_script( 'jquery-ui-sortable' );
			
			// woocommerce js directory url
			$js_dir = $woocommerce->plugin_url() . '/assets/js/';
			
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				
			
			// Enqueue for datepicker
			wp_enqueue_script( array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
			
			wp_deregister_script( 'datepicker-slider' );
			wp_register_script( 'datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'datepicker-slider' );
			
			wp_deregister_script( 'timepicker-addon' );
			wp_register_script( 'timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true );
			wp_enqueue_script( 'timepicker-addon' );
			
			// Enqueu built-in script for color picker.
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
				wp_enqueue_script( 'wp-color-picker' );
			} else {
				wp_enqueue_script( 'farbtastic' );
			}						
		}																
	}
	
	/**
	 * include css
	 * 
	 * Handels to add css on vendor dashboard page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_vendor_styles() {
		
		global $post, $wp_version, $woocommerce;				
		
		// get vendor dashboard page id
		$dashboard_page_id = WCVendors_Pro::get_option('dashboard_page_id');

		if( is_array( $dashboard_page_id ) && !empty( $dashboard_page_id ) ){
			$dashboard_page_id = $dashboard_page_id[0];
		}
		
		// if current page is vendor dashboard page
		if( isset( $post ) && $post->ID == $dashboard_page_id ) {
			
			// Enqueue Meta Box Style
			wp_enqueue_style( 'woo-vou-meta-box', WOO_VOU_META_URL . '/css/meta-box.css', array(), WOO_VOU_PLUGIN_VERSION );
			
			wp_register_style( 'woo-vou-style-metabox', WOO_VOU_URL.'includes/css/woo-vou-metabox.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-style-metabox' );
			
			// css directory url of woocommerce
			$css_dir = $woocommerce->plugin_url() . '/assets/css/';
			
			// enqueue woocommerce admin styles
			wp_enqueue_style( 'woo_vou_admin_styles', $css_dir . 'admin.css', array(), WOOCOMMERCE_VERSION );							
			
			// Enqueue for datepicker
			wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
			
			// Enqueue built-in style for color picker.
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
				wp_enqueue_style( 'wp-color-picker' );
			} else {
				wp_enqueue_style( 'farbtastic' );
			}
			
			wp_register_style( 'woo-vou-vendor-pro-styles', WOO_VOU_URL . 'includes/css/woo-vou-vendor-pro.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-vendor-pro-styles' );
		}				
	}
	
	/**
	 * To set product id	 
	 * while viewing editing product from fortend
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_edit_product_id( $post_id, $post ) {		
		
		// check post content has vendor dashboard pro shortcode
		if( has_shortcode( $post->post_content, 'wcv_pro_dashboard' ) ) {
			
			// get product id
			$current_product_id = get_query_var( 'object_id' );
			
			// assign product id to post id
			$post_id = !empty( $current_product_id ) ? $current_product_id : $post_id;			
		}
		
		return $post_id;
	}
	
	/**
	 * To display options when product is varible
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_add_product_variable_meta( $loop, $variation ) {		
		
		// include variable product meta file
		include( WOO_VOU_ADMIN . '/forms/woo-vou-product-variable-meta.php' );
	}

	/**
	 * Function to show/hide pdf vouchers
	 * on product add/edit page on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function woo_vou_remove_pdf_voucher_frontend($settings){
	
		// Get global option
		$vou_enable_vendor_acess_pdf_vou_frontend = get_option('vou_enable_vendor_acess_pdf_vou_frontend');
	
		// If global setting is not empty & it is set to no
		// And if pdf vouchers is set in tab array
		if(!empty($vou_enable_vendor_acess_pdf_vou_frontend) && $vou_enable_vendor_acess_pdf_vou_frontend == 'no' && isset($settings['pdf_vouchers'])){
			unset($settings['pdf_vouchers']); // Unset PDF Vouchers
		}
	
		// Return settings
		return $settings;
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for Vendor Pro.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function add_hooks(){
		$vou_enable_vendor_acess_pdf_vou_frontend 	= get_option('vou_enable_vendor_acess_pdf_vou_frontend');

		if( !empty( $vou_enable_vendor_acess_pdf_vou_frontend ) && $vou_enable_vendor_acess_pdf_vou_frontend == 'yes' ){ // check Enable Access to PDF Vouchers tab should ticked
			
			// add action to add PDF Voucher panel
			add_action( 'wcv_product_meta_tabs', array( $this, 'woo_vou_product_write_panel_tab' ) );
			
			// add action to add PDF Vocuher panel data
			add_action( 'wcv_after_shipping_tab', array( $this->voumeta, 'woo_vou_product_write_panel' ) );
			
			// add action to include import voucher code popup file
			add_action( 'wp_footer', array( $this, 'woo_vou_import_code_popup' ) );
					
			// add action Save data
			add_action( 'wcv_save_product_meta', array( $this, 'woo_vou_product_save_meta' ) );
			
			// add action to add css for on vendor dashboard page
			add_action( 'wp_enqueue_scripts', array( $this, 'woo_vou_vendor_styles' ) );
			
			// add action to add scripts for vendor dashboard page
			add_action('wp_enqueue_scripts', array( $this, 'woo_vou_vendor_scripts') );

			// add filter to change/set product id
			add_filter( 'woo_vou_edit_product_id', array( $this, 'woo_vou_edit_product_id' ), 10, 2 );
			
			// add action to add pdf voucher data when variable product
			add_action( 'wcv_add_product_variable_meta', array( $this, 'woo_vou_add_product_variable_meta' ), 10, 2 );
			
			// add action to save products variation data
			add_action( 'wcv_save_product_variation', 'woo_vou_product_save_variable_meta', 20, 2 );

			// Add action to remove pdf voucher from frontend
	        add_filter( 'wcv_product_meta_tabs', array( $this, 'woo_vou_remove_pdf_voucher_frontend' ), 15);
	    }
	}	
}