<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Marketplace Class. To enhance compatibility with WC Marketplace plugin.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.8.2
 */
class WOO_Vou_WC_Marketplace {
	
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
	 * Include required core files used to add compability of WC marketplace for the front end
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function includes() {
		
		//Vendor Class for front-end
		require_once ( WOO_VOU_META_DIR . '/woo-vou-meta-box-functions.php' );		
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
		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' ) {
			//If current page is vendor dashboard page
			if( is_vendor_dashboard() ) {
				
				// include import voucher code popup file
				include_once( WOO_VOU_ADMIN . '/forms/metabox-popups/woo-vou-import-code-popup.php' );
			}
		}
	}

	/**
	 * Modify WC Marketplace Role Array
	 * 
	 * Handle to modify settings array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcmp_settings( $settings ) {

		array_push($settings,array( 
				'name'	=>	esc_html__( 'WC Marketplace Settings', 'woovoucher' ),
				'type'	=>	'title',
				'desc'	=>	'',
				'id'	=>	'vou_wcmp_settings'
			),
		array(
				'id'		=> 'vou_enable_auto_integrate_wcmp_vendor',
				'name'		=> esc_html__( 'Auto Integrate vendor with PDF Voucher', 'woovoucher' ),
				'desc'		=> esc_html__( 'Auto Integrate vendor with PDF Voucher', 'woovoucher' ),
				'type'		=> 'checkbox',
				'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to integrate WCMp Vendors with PDF Vouchers.', 'woovoucher' ) . '</p>',
				'default'	=> 'yes'
			),
		array(
				'id'		=> 'vou_enable_wcmp_vendor_acess_pdf_vou_meta',
				'name'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
				'desc'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
				'type'		=> 'checkbox',
				'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow vendors to access PDF Vouchers on add/edit product page on frontend and backend.', 'woovoucher' ) . '</p>',
				'default'	=> 'yes'
			),
		array( 
				'type' 		=> 'sectionend',
				'id' 		=> 'vou_wcmp_settings'
			));

		return $settings;
	}

    /**
     * Handles to hide PDF Vouchers tab
     *
     * Function handles to hide PDF Vouchers tab on product add/edit page
     * for vendor when WC Marketplace is activated
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woo_vou_wcmp_remove_pdf_vou_tab() {

        global $woo_vou_admin_meta;
		
        $user_id = get_current_user_id(); // Get current user 
        $vou_wcmp_vendor_acess_pdf_vou_meta = get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta'); // Get global option

        // If WCMp class exists and if option is not empty and set to yes
        if ( is_user_wcmp_vendor($user_id) && (!empty($vou_wcmp_vendor_acess_pdf_vou_meta) && $vou_wcmp_vendor_acess_pdf_vou_meta == 'no' )) {

            // Remove PDF Vouchers tab
            remove_action('woocommerce_product_write_panel_tabs', array($woo_vou_admin_meta, 'woo_vou_product_write_panel_tab'));
        }
    }

	/**
	 * Modify Vendor Role Array
	 * 
	 * Handle to modify vendor role array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcmp_append_voucher_role( $woo_vou_roles ) {

		// Get global options
		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_auto_integrate_wcmp_vendor');

		// add WC Marketplace as a vendor
		if( empty( $vou_enable_auto_integrate_wcmp_vendor ) || $vou_enable_auto_integrate_wcmp_vendor == 'yes' ) {
			$woo_vou_roles[]	= 'dc_vendor';
		}

		return $woo_vou_roles;
	}
	
	/**
	 * Added PDF Voucher Tab
	 * 
	 * Handle to Add PDF Voucher Tab in WCMp
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcmp_product_write_panel_tab($product_tabs){
		$pdf_vouchers_tab = array();

		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' ) {

			 $pdf_vouchers_tab = array(
	            'pdf_vouchers' => array(
	                'label'    => __( 'PDF Vouchers', 'woovoucher' ),
	                'target'   => 'woo_vou_voucher',
	                'class'    => array('woo_vou_voucher_tab', 'show_if_downloadable', 'show_if_variable'),
	                'priority' => 300,
	            ),
	        );
		}

		return array_merge( $product_tabs, $pdf_vouchers_tab );
	}
	
	
	/**
	 * Added PDF Voucher Tab
	 * 
	 * Handle to Add PDF Voucher Tab content in WCMp
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcmp_product_write_panel_tab_content(){
		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' ) {
		?>
		<div role="tabpanel" class="tab-pane fade" id="woo_vou_voucher">
			<div class="row-padding">
			<?php
				$this->voumeta->woo_vou_product_write_panel(true); // to fix inline css iss with wcmp
			?>
			</div>
		</div>
		<?php
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
	 public function woo_vou_wcmp_vendor_scripts() {
		
		global $post, $wp_version, $woocommerce;
		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' && is_vendor_dashboard() ) {
			
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
	public function woo_vou_wcmp_vendor_styles() {
		
		global $post, $wp_version, $woocommerce;
		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' && is_vendor_dashboard() ) {
			
			// Enqueue Meta Box Style		
			wp_enqueue_style( 'woo-vou-meta-box-1', WOO_VOU_META_URL . '/css/meta-box.css');		
			
			wp_register_style( 'woo-vou-style-metabox-1', WOO_VOU_URL.'includes/css/woo-vou-metabox.css');
		
			wp_enqueue_style( 'woo-vou-style-metabox-1' );
			
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

			wp_register_style( 'woo-vou-wcmp-public-styles', WOO_VOU_URL . 'includes/css/woo-vou-wcmp-front-style.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-wcmp-public-styles' );
			
			//
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
	public function woo_vou_product_save_meta( $product ) {

		$vou_enable_auto_integrate_wcmp_vendor 	= get_option('vou_enable_wcmp_vendor_acess_pdf_vou_meta');

		if( !empty( $vou_enable_auto_integrate_wcmp_vendor ) && $vou_enable_auto_integrate_wcmp_vendor == 'yes' && !empty( $product ) ) {
			// call to save function
			woo_vou_product_save_data( $product->get_id(), get_post( $product->get_id() ) );
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
		
		// check if dashboard 
		if( is_vendor_dashboard() ) {
			
			// get product id
			$current_product_id = get_query_var( 'edit-product' );
			// assign product id to post id
			$post_id = !empty( $current_product_id ) ? $current_product_id : $post_id;			
		}
		
		return $post_id;
	}

	/**
	 * To set primary vendor id	 
	 * Handle to set the primary vendor id for the pdf voucher
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.4
	 */
	public function woo_vou_auto_set_vendor( $product, $post_data ){
		$product_id = $product->get_id();
		$user_id = get_current_user_id(); // Get current user
		// get prefix
    	$prefix = WOO_VOU_META_PREFIX;

		if( isset( $post_data['_woo_vou_enable'] ) && !empty( $user_id ) ){
			// Vendor User
    		update_post_meta( $product_id, $prefix . 'vendor_user', $user_id);
		}
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for Vendor.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function add_hooks(){
		
		// Append the settings array
		add_filter( 'woo_vou_misc_settings', array( $this, 'woo_vou_wcmp_settings' ) );
		
		// Append the pdf voucher role
		add_filter( 'woo_vou_edit_vendor_role', array($this, 'woo_vou_wcmp_append_voucher_role' ) );

        // remove metabox in products when WC Marketplace is active
        add_action('admin_init', array($this, 'woo_vou_wcmp_remove_pdf_vou_tab'));

        add_action('wcmp_process_product_object', array( $this, 'woo_vou_product_save_meta') );
		
		//Register tab
		add_filter( 'wcmp_product_data_tabs', array( $this, 'woo_vou_wcmp_product_write_panel_tab' ) );
		
		//Add tab content
		add_action( 'wcmp_product_tabs_content', array( $this, 'woo_vou_wcmp_product_write_panel_tab_content' ) );
		
		// add action to add scripts for vendor dashboard page
		add_action('wcmp_frontend_enqueue_scripts', array( $this, 'woo_vou_wcmp_vendor_scripts'),999 );	
		
		// add action to add css for on vendor dashboard page
		add_action( 'wcmp_frontend_enqueue_scripts', array( $this, 'woo_vou_wcmp_vendor_styles' ),999 );

		// add action to include import voucher code popup file
		add_action( 'wp_footer', array( $this, 'woo_vou_import_code_popup' ) );

		// add filter to change/set product id
		add_filter( 'woo_vou_edit_product_id', array( $this, 'woo_vou_edit_product_id' ), 10, 2 );

		// action hook to set the vendor automatically when adding product from the frontend
		add_action('wcmp_process_product_object', array( $this, 'woo_vou_auto_set_vendor'),10,2);
		
	}	
}