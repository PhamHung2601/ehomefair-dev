<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Order SMS Notification Compability Class
 * 
 * Handles Order SMS Notification
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.0
 */
class WOO_Vou_Order_Sms {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}
	
	/**
	 * Replace shortcode [voucher_details] with its value
	 * 
	 * It will include product name, voucher code, price, expiry date and download link
	 * 
	 * @param string $sms_body
	 * @param string $order_id
	 * @return string $sms
	 */
	function woo_vou_pharse_sms_body_container( $sms_body, $order_id ) {
		
		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		$find = array(
					'[voucher_details]',
       			 );
       			 
       	$voucher_details = array();			
        
        // Creating order object for order id
		$woo_order 		= wc_get_order( $order_id );		
		
		// getting all ordered items
		$order_items	= $woo_order->get_items();
		
		//get all voucher details from order meta
		$allorderdata 	= $this->model->woo_vou_get_all_ordered_data( $order_id );
				
		// loop through all ordered items
		foreach( $order_items as $item_key => $item_val ) {
			
			// get product id
			$product_id		= isset( $item_val['product_id'] ) ? $item_val['product_id'] : '';			
		
			// get product		
			$product = wc_get_product( $product_id );
						
			// if product type is variable then $product_id will contain variation id
			$variation_id = $product_id;
			// check if product type is variable then we need to take parent id of variation id
			if( $product->is_type( 'variation' ) || $product->is_type( 'variable' ) ) {
				
				// productid is variation id in case of variable product so we need to take actual product id					
				$woo_variation 	= new WC_Product_Variation( $product_id );			
				$product_id		= ( !empty($woo_variation->id) ) ? $woo_variation->id : $variation_id; 
			}
			
			// get product price
			$product_price		= 	vou_get_voucher_price_by_order_item( $item_val , $item_key );		
			
			// get expiry code
			$expire_date = $allorderdata[$product_id]['exp_date'];					
			$expire_date = !empty( $expire_date ) ? $this->model->woo_vou_get_date_format( $expire_date, true ) : '' ;			
			
			$_product	= apply_filters( 'woocommerce_order_item_product', $woo_order->get_product_from_item( $item_val ), $item_val );
								
			if( !$_product ) {//If product deleted
				$download_file_data = array();
			} else {
			 	$download_file_data = $this->model->woo_vou_get_item_downloads_from_order($woo_order, $item_val);
			}
									
			// download pdf link
			$links		= array();
			foreach ( $download_file_data as $keys => $download_file ) {
				
				$check_key	= strpos( $keys, 'woo_vou_pdf_' );

				if( !empty( $download_file ) && $check_key !== false ) {
					
					$links[] = $download_file['download_url'];
				}
			}
			
			$links =  implode( PHP_EOL, $links );

			// convert long url to tiny url			
			$links = woo_vou_shorten_url_with_tinyurl( $links );
				
			$voucher_details[] = esc_html__( 'Product Name:', 'woovoucher' ) . $item_val['name'] . ',' .
								 esc_html__( 'Voucher Code:', 'woovoucher' ) . $item_val['woo_vou_codes'] . ',' .
								 esc_html__( 'Price:', 'woovoucher' ) . $product_price . ',' .
								 esc_html__( 'Expire date:', 'woovoucher' ) . $expire_date . ',' .
								 esc_html__( 'Link:', 'woovoucher' ) . $links;
		}
		
		$voucher_details = implode( PHP_EOL, $voucher_details );
	 
        $replace = array(
			           	$voucher_details,
			       	 );

       	$sms_body = str_replace( $find, $replace, $sms_body );
       	
       	// return sms body
       	return $sms_body;
	}
	
	/**
	 * Create message body for voucher details 
	 * 
	 * @package WooCommerce - PDF Vouchers	 
	 * @since 2.8.0
	 */	
	public function woo_vou_pharse_sms_body( $sms_body, $data ) {
		
		if( !empty( $data['order_status'] ) ) {
					
			// Woocommerce grant access after payment
			$grant_access_after_payment	= get_option( 'woocommerce_downloads_grant_access_after_payment' ); 
			
			if ( $data['order_status'] == "completed" ||  ( $grant_access_after_payment == 'yes' && $data['order_status'] == "processing" ) ) {
				
				// Replace shortcode [voucher_details] with value
				$sms_body = $this->woo_vou_pharse_sms_body_container( $sms_body, $data['order_id'] );
			}
		}
		
		return  $sms_body;
	}
	
	/**
	 * Add Shortcodes at admin side 
	 * 
	 * @package WooCommerce - PDF Vouchers	 
	 * @since 2.8.0
	 */		
	public function woo_vou_sms_shortcode_insert( $shortcodes ) {
		
		$shortcodes = $shortcodes .',For PDF voucher details add <code>[voucher_details]</code>';
		
		return  $shortcodes;
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the ORDER SMS.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.0
	 */
	public function add_hooks() {

		// add filter to add voucher details in admin sms
		add_filter( 'sat_sms_pharse_body', array( $this, 'woo_vou_pharse_sms_body' ), 10, 2 );	
		
		// add filter to add shortcodes at admin side
		add_filter( 'sat_sms_shortcode_insert_description', array( $this, 'woo_vou_sms_shortcode_insert' ), 10,1 );	
	}
}