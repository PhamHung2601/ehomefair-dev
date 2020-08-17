<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WPML Compability Class
 * 
 * Handles WPML Compability
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.0
 */
class WOO_Vou_Wpml {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}
	
	/**
	 * Get original product id from translated product id
	 * 
	 * To add compatibility with WPML
	 * Handles to get original product id from translated product id.	 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.0
	 */
	public function woo_vou_get_original_product_id( $product_id ) {

  		
		global $sitepress;
  		
		// get current language
		$current_language = $sitepress->get_current_language();
		// get default language
        $default_language = $sitepress->get_default_language();

        // if current language and default language is different then only get product id of default language
        if( $current_language != $default_language ) {

            $product_id = icl_object_id( $product_id, 'product', true, $default_language );	
        }
		
		return $product_id;
	}
	
	public function woo_vou_get_original_id_from_translated_id( $product_id ) {
		
		// get original product/variation id from translated product/variation id
		$product_id = $this->woo_vou_get_original_product_id( $product_id );
		
		return $product_id;
	}
	
	/**
	 * Get original product id from translated product id and post_type
	 * 
	 * To add compatibility with WPML
	 * Handles to get original product id from translated product id and post_type 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_convert_get_originalid ( $translated_id, $post_type, $woo_vou_order_lang ) {
			
		global $sitepress;
  		
		// get current language
		$current_language = $sitepress->get_current_language();
		
		if( !empty( $woo_vou_order_lang ) ) {
	
			// Get original product_id from translated_id and post_type
			$translated_id = icl_object_id( $translated_id, $post_type, false, $woo_vou_order_lang );
		}

		return $translated_id;
	}
	
	/**
	 * Get voucher template id from order language
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.0.2
	 */
	public function woo_vou_get_voucher_template_id( $voucher_template_id, $orderid ) {
						
		// get order language
		$order_language = get_post_meta( $orderid, 'wpml_language', true );
		
		// Get voucher template id from order language
		$translated_template_id = icl_object_id( $voucher_template_id, WOO_VOU_POST_TYPE, false, $order_language );
		
		if( !empty( $translated_template_id ) )
			$voucher_template_id = $translated_template_id;			
		
		return  $voucher_template_id;
	}

	/**
	 * Handles to check whether product is translated or not
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.0.2
	 */
	public function woo_vou_check_product_is_translated( $is_translated, $product_id ) {

		// Get original product/variation id from translated product/variation id
		$_productid = $this->woo_vou_get_original_product_id( $product_id );

		// If both product id is same than product is main language product
		if( $_productid == $product_id ) {
			$is_translated = false; // Main product
		} else {
            $is_translated = true; // Translated product
        }

		return $is_translated;
	}

	/**
	 * Get translated product id from other id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.0.2
	 */
	public function woo_vou_get_productid_for_order_language( $product_id, $order_id ) {

		$order_lang = get_post_meta($order_id, 'wpml_language', true); // Get order language
		$product_id = icl_object_id( $product_id, 'product', false, $order_lang ); // Get product id
        
        return $product_id;
	}

	/**
	 * Get product name from product id
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.0.2
	 */
	public function woo_vou_product_name_for_order_language( $product_name, $product_id ) {

		$product = wc_get_product($product_id);
		
		return $product->get_name();
	}
	
	/**
	 * Add action to enqueue lock field script
	 * 
	 * Check if page is product edit page
	 * Check if page is translated product edit page, so original product edit page will not effect
	 * Check if source_lang is set
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_enqueue_wpml_lock_field_scripts() {
		
		global $pagenow, $woocommerce_wpml;
	
		if( ( $pagenow == 'post.php' && isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'product' && !empty( $woocommerce_wpml->products ) &&!$woocommerce_wpml->products->is_original_product( $_GET['post'] ) ) ||
            ( $pagenow == 'post-new.php' && isset( $_GET['source_lang'] ) && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) && 
            !$woocommerce_wpml->settings['trnsl_interface'] ) {
            
            // add action to enqueue lock fields js
        	add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_load_wpml_lock_fields_js') );
        }

	}
	
	/**
	 * Enqueue lock field script
	 * 
	 * Handles to add lock fields js For WPML Support
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_load_wpml_lock_fields_js() {
		
		// register lock script
        wp_register_script( 'woo-vou-wcml-lock-script', WOO_VOU_URL . 'includes/js/woo-vou-lock-fields.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );
        
        // enqueue lock script
        wp_enqueue_script( 'woo-vou-wcml-lock-script' );
    }

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the WPML compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		// Add filter to return original language product id before updating voucher code
		add_filter( 'woo_vou_before_update_voucher_code', array( $this, 'woo_vou_get_original_id_from_translated_id' ), 10 );
				
		// Add filter to return original language product id before getting voucher code
		add_filter( 'woo_vou_before_get_voucher_code', array( $this, 'woo_vou_get_original_id_from_translated_id' ), 10 );

		// Add filter to check whether product is translated
		add_filter( 'woo_vou_is_translation_product', array( $this, 'woo_vou_check_product_is_translated' ), 10, 2 );
		
		// Add filter to return order_id, in the language in which order was made
		add_filter( 'woo_vou_before_admin_vou_download_link', array( $this, 'woo_vou_convert_get_originalid' ), 10, 3 );
		
		// Add filter to return voucher template id
		add_filter( 'woo_vou_voucher_template_id', array( $this, 'woo_vou_get_voucher_template_id' ), 10, 2 );

		// Add filter to get original product name
		add_filter( 'woo_vou_productid_from_orderid', array( $this, 'woo_vou_get_productid_for_order_language' ), 10, 2 );

		// Add filter to get product name
		add_filter( 'woo_vou_product_name_from_productid', array( $this, 'woo_vou_product_name_for_order_language' ), 10, 2 );

		// Add action to enqueue lock script when translated product is edited
		add_action( 'admin_init', array( $this, 'woo_vou_enqueue_wpml_lock_field_scripts') );
	}
}