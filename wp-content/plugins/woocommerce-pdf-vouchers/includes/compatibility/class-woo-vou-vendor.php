<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Vendor Class. To enhance compatibility with WC Vendor plugin.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.8.2
 */
class WOO_Vou_WC_Vendor {
	
	public function __construct() {
	}

	/**
	 * Modify WC Vendor Pro Role Array
	 * 
	 * Handle to modify settings array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcvendor_settings( $settings ) {

		array_push( $settings, 
			array( 
				'name'	=>	esc_html__( 'WC Vendor Settings', 'woovoucher' ),
				'type'	=>	'title',
				'desc'	=>	'',
				'id'	=>	'vou_wc_vendor_settings'
			),
			array(
				'id'		=> 'vou_enable_auto_integrate_wc_vendor',
				'name'		=> esc_html__( 'Auto Integrate vendor with PDF Voucher', 'woovoucher' ),
				'desc'		=> esc_html__( 'Auto Integrate vendor with PDF Voucher', 'woovoucher' ),
				'type'		=> 'checkbox',
				'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to integrate WC Vendors with PDF Vouchers.', 'woovoucher' ) . '</p>',
				'default'	=> 'yes'
			),
			array(
				'id'		=> 'vou_enable_vendor_acess_pdf_vou_frontend',
				'name'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
				'desc'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
				'type'		=> 'checkbox',
				'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow vendors to access PDF Vouchers on add/edit product page on frontend and backend.', 'woovoucher' ) . '</p>',
				'default'	=> 'yes'
			),
			array( 
				'type' 		=> 'sectionend',
				'id' 		=> 'vou_wc_vendor_settings'
			)
		);

		return $settings;
	}

	/**
	 * Add Capability to vendor role
	 * 
	 * Handle to add capability to vendor role
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcvendor_initilize_role_capabilities() {

	    global $woo_vou_vendor_role;

	    foreach ($woo_vou_vendor_role as $vendor_role) {

	        //get vendor role
	        $vendor_role_obj = get_role($vendor_role);

	        if (!empty($vendor_role_obj)) { // If vendor role is exist 
	            if (!$vendor_role_obj->has_cap(WOO_VOU_VENDOR_LEVEL)) { //If capabilty not exist
	                //Add vucher level capability to vendor roles
	                $vendor_role_obj->add_cap(WOO_VOU_VENDOR_LEVEL);
	            }
	        }
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
	public function woo_vou_wcvendor_append_voucher_role( $woo_vou_roles ) {

		// Get global options
		$vou_enable_auto_integrate_wc_vendor 	= get_option('vou_enable_auto_integrate_wc_vendor');

		 // add wc vendor as voucher vendor
		if( empty( $vou_enable_auto_integrate_wc_vendor ) || $vou_enable_auto_integrate_wc_vendor == 'yes' ) {
			$woo_vou_roles[]	= 'vendor';
		}

		return $woo_vou_roles;
	}

	/**
	 * Modify Vendor shopname
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcvendor_voucher_shopname( $vendor_shopname, $vendor_user ) {

		// Get vendor's shop name
		$vendor_shopname = get_user_meta($vendor_user, 'pv_shop_name', true); 

		return $vendor_shopname;
	}

	/**
	 * Preview PDF - Replace shortcode with value
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_wcvendor_voucher_shopname_pdf_preview_shortcode( $vendor_shopname ) {

		// Get vendor's shop name
		$vendor_shopname = esc_html__('Vendor Shop', 'woovoucher');

		return $vendor_shopname;
	}

    /**
     * Handles to hide PDF Vouchers tab
     *
     * Function handles to hide PDF Vouchers tab on product add/edit page
     * for vendor when WC Vendor is activated
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.0
     */
    public function woo_vou_wcvendor_remove_pdf_vou_tab() {

        global $woo_vou_admin_meta;

        $user_id = get_current_user_id(); // Get current user 
        $vou_enable_vendor_acess_pdf_vou_frontend = get_option('vou_enable_vendor_acess_pdf_vou_frontend'); // Get global option
 
        // If option is not empty and set to no
        if ( WCV_Vendors::is_vendor($user_id) && !empty($vou_enable_vendor_acess_pdf_vou_frontend) && $vou_enable_vendor_acess_pdf_vou_frontend == 'no') {

            // Remove PDF Vouchers tab
            remove_action('woocommerce_product_write_panel_tabs', array($woo_vou_admin_meta, 'woo_vou_product_write_panel_tab'));
        }
    }

    /**
	 * To set primary vendor id	 
	 * Handle to set the primary vendor id for the pdf voucher
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.4
	 */
	public function woo_vou_wcvendor_auto_set_vendor( $post_id ){

		global $post_type;

	    $post_type_object = get_post_type_object($post_type);

	    // Check for which post type we need to add the meta box
	    $pages = array(WOO_VOU_POST_TYPE);

	    if( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)					// Check Autosave
            || (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'] ) // Check Revision
            || (!in_array($post_type, $pages) )			// Check if current post type is supported.
            || (!check_admin_referer(WOO_VOU_PLUGIN_BASENAME, 'at_woo_vou_meta_box_nonce') )      // Check nonce - Security
            || (!current_user_can($post_type_object->cap->edit_post, $post_id) )) {	// Check permission

	        return $post_id;
	    }

	    // Get current user
		$user = wp_get_current_user();

		$user_id = isset( $user->ID ) ? $user->ID : get_current_user_id();
		$roles = ( array ) $user->roles;

		if( ! in_array('seller', $roles) && ! in_array('vendor', $roles) && ! in_array('woo_vou_vendors', $roles) ) return $post_id;

		// get prefix
		$prefix = WOO_VOU_META_PREFIX;

		if( isset($_POST['_woo_vou_enable']) && !empty($user_id) ){
			// Vendor User
			update_post_meta( $post_id, $prefix . 'vendor_user', $user_id );
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
		add_filter( 'woo_vou_misc_settings', array( $this, 'woo_vou_wcvendor_settings' ) );

        // add capabilities to user roles
        add_action('init', array($this, 'woo_vou_wcvendor_initilize_role_capabilities' ), 100 );

		// Append the pdf voucher role
		add_filter( 'woo_vou_edit_vendor_role', array($this, 'woo_vou_wcvendor_append_voucher_role' ) );

		// Modify Vendor shopname
		add_filter( 'woo_vou_shopname_pdf_preview', array($this, 'woo_vou_wcvendor_voucher_shopname' ), 10, 2 );
		add_filter( 'woo_vou_shopname_pdf_process', array($this, 'woo_vou_wcvendor_voucher_shopname' ), 10, 2 );

		// Preview PDF - Replace shortcode with value
		add_filter( 'woo_vou_vendor_shopname_pdf_preview_shortcode', array($this, 'woo_vou_wcvendor_voucher_shopname_pdf_preview_shortcode' ) );

        // add metabox in products
        add_action('admin_init', array($this, 'woo_vou_wcvendor_remove_pdf_vou_tab'));

        //set the vendor automatically when adding product by vendor
        add_action('save_post', array($this, 'woo_vou_wcvendor_auto_set_vendor'));
	}	
}