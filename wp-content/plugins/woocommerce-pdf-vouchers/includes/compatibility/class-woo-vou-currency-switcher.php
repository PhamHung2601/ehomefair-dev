<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Currenct switcher Class
 * 
 * Handles WC CURRENCY SWITCHER Compability
 * https://currency-switcher.com/
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.14
 */
class WOO_Vou_WC_Currency_Switcher {

	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/** 
	 * Handles to convert the currency to base currency rate
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.14
	 */
	public function woo_vou_multi_currency_price( $subtotal, $orderid = '' ){

		// return $subtotal; // uncomment this if report an issue

	    $prefix     = WOO_VOU_META_PREFIX; // Get prefix

	    // if Currency switcher exist
	    if( class_exists('WOOCS_STARTER') ){

	        global $WOOCS;

	        $default_currency = '';

	        $currencies = $WOOCS->get_currencies();
	        $decimal_points = get_option('woocommerce_price_num_decimals'); // Get option decimal points number after points

	        if (!empty($currencies) AND is_array($currencies)) {

	            foreach ( $currencies as $key => $currency) {
	                if ($currency['is_etalon']) {
	                    $default_currency = $key;
	                    break;
	                }
	            }
	        }

	       	if( !empty( $orderid ) ) {
		        
	       		$woo_order  = wc_get_order($orderid); // Get Order

		        $order_currency = $woo_order->get_currency();
		        $order_currency_rate = '';

		        $order_base_currency = get_post_meta( $orderid, '_woocs_order_base_currency', true ); // get order base currency

		        $decimal_points = get_option('woocommerce_price_num_decimals');
		        
		        $currencies = $WOOCS->get_currencies(); // get all currencies

		        if (!empty($currencies) AND is_array($currencies)) {

		            foreach ( $currencies as $key => $currency) {
		                if ($currency['is_etalon']) {
		                    $default_currency = $key; // get default currency
		                }
		                
		                if( $key == $order_currency ){
		                    $order_currency_rate = $currency['rate'];
		                }
		            }
		        }


		        $default_currency = !empty( $default_currency ) ? $default_currency : get_woocommerce_currency();

		        
		        $default_currency_rate = !empty( $currencies[$default_currency]['rate'] ) ? $currencies[$default_currency]['rate'] : $order_currency_rate;

		        if( !empty( $order_currency_rate ) && !empty( $default_currency_rate ) && is_admin() && !$this->woo_vou_request_is_frontend_ajax() ) { // if admin side

		            if( $order_currency != $default_currency ){
		            	
		                $subtotal = $subtotal * $default_currency_rate / $order_currency_rate;

		                $subtotal = round( $subtotal, $decimal_points);
		            }
		        }
		        elseif( $this->woo_vou_request_is_frontend_ajax() ){ // if front side

		            global $WOOCS;

		            if( $order_currency != $WOOCS->current_currency && !empty( $currencies ) ){
		                $current_cur_rate = $currencies[$WOOCS->current_currency]['rate'];
		                $subtotal = $subtotal * $current_cur_rate / $order_currency_rate;
		                $subtotal = round( $subtotal, $decimal_points);
		            }
		        }
		    } else{ // if no order id the convert currency from default currency

		    	$default_currency = !empty( $default_currency ) ? $default_currency : get_woocommerce_currency();

		    	if( $WOOCS->current_currency != $default_currency ){

		    		$current_cur_rate = $currencies[$WOOCS->current_currency]['rate'];

		    		$default_currency_rate = !empty( $currencies[$default_currency]['rate'] ) ? $currencies[$default_currency]['rate'] : '';
		    		
		    		if( !empty( $default_currency_rate) ){

		                $subtotal = $subtotal * $default_currency_rate / $current_cur_rate;

		                $subtotal = round( $subtotal,$decimal_points);
		            }
		        }
		    }
	    }

	    return $subtotal;
	}
	
	/** 
	 * Handles to convert the default base currency to current selected rate
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.14
	 */
	public function woo_vou_multi_currency_from_default_price( $subtotal, $custom_price = false ){

		// return $subtotal; // uncomment this if report an issue

		$prefix     = WOO_VOU_META_PREFIX; // Get prefix

	    // if Currency switcher exist
	    if( class_exists('WOOCS_STARTER') ){

	    	if( $this->woo_vou_request_is_frontend_ajax() || $custom_price ) { // if front side	

		        global $WOOCS;

		        $default_currency = '';

		        $currencies = $WOOCS->get_currencies();
		        $decimal_points = get_option('woocommerce_price_num_decimals'); // Get option decimal points number after points

		        if (!empty($currencies) AND is_array($currencies)) {

		            foreach ( $currencies as $key => $currency) {
		                if ($currency['is_etalon']) {
		                    $default_currency = $key;
		                    break;
		                }
		            }
		        }

		        $default_currency = !empty( $default_currency ) ? $default_currency : get_woocommerce_currency();

		    	if( $WOOCS->current_currency != $default_currency ){

		    		$current_cur_rate = $currencies[$WOOCS->current_currency]['rate'];

		    		$default_currency_rate = !empty( $currencies[$default_currency]['rate'] ) ? $currencies[$default_currency]['rate'] : '';
		    		
		    		if( !empty( $default_currency_rate) ){

		                $subtotal = $subtotal * $current_cur_rate / $default_currency_rate;

		                $subtotal = round( $subtotal,$decimal_points);
		            }
		        }

		    }

	    }

	    return $subtotal;
	}

	/** 
	 * Check whether request from admin or front side
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.14
	 */
	public function woo_vou_request_is_frontend_ajax() {
	  $script_filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
	 
	  //Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
	  if((defined('DOING_AJAX') && DOING_AJAX))
	  {
	          //From wp-includes/functions.php, wp_get_referer() function.
	          //Required to fix: https://core.trac.wordpress.org/ticket/25294
	          $ref = '';
	          if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
	              $ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
	          elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) )
	              $ref = wp_unslash( $_SERVER['HTTP_REFERER'] );
	 
	    //If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
	    if(((strpos($ref, admin_url()) === false) && (basename($script_filename) === 'admin-ajax.php')))
	      return true;
	  }
	 
	  //If no checks triggered, we end up here - not an AJAX request.
	  return false;
	}

	/** 
	 * Handles to add hooks 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.14
	 */
	public function add_hooks(){
		
		// added filter on voucher price to convrt the rate based on selected currency
		add_filter('woo_vou_get_product_price', array( $this, 'woo_vou_multi_currency_price'),10, 2);
	}
}