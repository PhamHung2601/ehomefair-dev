<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Bundles Compability Class
 * 
 * Handles WC Bundles Compability
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.10
 */
class WOO_Vou_WC_Bundles {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/**
	 * Filter to Remove download from bundle items on Order detail & email.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function woo_vou_order_get_downloadable_items( $downloads, $order ) {

	    //Extract product ids which bundle's part
	    $bpart_ids = array();
	    $order_items = $order->get_items();
	    if( !empty( $order_items ) ) {
	        foreach ( $order_items as $order_item ) {
	            
	            //Check if order item is bundle's part
	            if( wc_pb_is_bundled_order_item( $order_item, $order ) ) {
	                $bpart_ids[] = $order_item->get_product_id();
	            }
	        }
	    }

	    //Remove from displaying
	    if( !empty( $downloads ) ) {
	        foreach ( $downloads as $dkey => $download ) {

	            if( in_array( $download['product_id'] , $bpart_ids ) ) {
	                unset( $downloads[$dkey] );
	            }
	        }
	    }

	    return $downloads;
	}

	/**
	 * Prevent generate voucher code of bundle product's items
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function woo_vou_check_enable_bundle_product_voucher( $enable, $productid, $variation_id, $args ) {

        //Check if part of bundle products
        if( !empty( $args['cart_item'] )
            && wc_pb_is_bundled_cart_item( $args['cart_item'] ) ) {
            $enable = false; //if cart item is part of bundle
        } elseif ( !empty( $args['order_item'] ) && !empty( $args['order'] )
            && wc_pb_is_bundled_order_item( $args['order_item'], $args['order'] ) ) {
            $enable = false; //if order item is part of bundle
        }

		return $enable;
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the WC Bundles compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function add_hooks() {

        //Action to Remove download from bundle items
        add_filter( 'woocommerce_order_get_downloadable_items', array( $this, 'woo_vou_order_get_downloadable_items' ), 10, 2 );

        add_filter( 'woo_vou_check_enable_voucher', array( $this, 'woo_vou_check_enable_bundle_product_voucher' ), 10, 4 );
	}
}