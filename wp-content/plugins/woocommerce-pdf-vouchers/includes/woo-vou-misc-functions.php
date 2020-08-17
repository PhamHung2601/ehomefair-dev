<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Misc Functions
 * 
 * All misc functions handles to 
 * different functions 
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
	
/**
 * Initilize PDF Voucher
 * 
 * Handle to initilize PDF voucher
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_vendor_initilize() {

	global $woo_vou_vendor_role;

	$woo_vou_vendor_role = apply_filters( 'woo_vou_edit_vendor_role', array( WOO_VOU_VENDOR_ROLE )  );

}

/**
 * Different Pdf size Array
 * 
 * Handle to get different pdf sizes
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
function woo_vou_get_pdf_sizes() {

	return apply_filters( 'woo_vou_get_pdf_sizes', array(
									'A0'	=> array(
													'width'		=> 841,
													'height'	=> 1189,
													'fontsize'	=> 50
												),
									'A1'	=> array(
													'width'		=> 594,
													'height'	=> 841,
													'fontsize'	=> 35
												),
									'A2'	=> array(
													'width'		=> 420,
													'height'	=> 594,
													'fontsize'	=> 25
												),
									'A3'	=> array(
													'width'		=> 297,
													'height'	=> 420,
													'fontsize'	=> 17
												),
									'A4'	=> array(
													'width'		=> 210,
													'height'	=> 297,
													'fontsize'	=> 12
												),
									'A5'	=> array(
													'width'		=> 148,
													'height'	=> 210,
													'fontsize'	=> 10
												),
									'A6'	=> array(
													'width'		=> 105,
													'height'	=> 148,
													'fontsize'	=> 9
												),
									'A7'	=> array(
													'width'		=> 74,
													'height'	=> 105,
													'fontsize'	=> 8
												),
									'A8'	=> array(
													'width'		=> 52,
													'height'	=> 74,
													'fontsize'	=> 7
												)
								)
				);
}

/**
 * defaul pdf sizes array
 * 
 * Handle to get different pdf sizes
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.9
 */
function woo_vou_get_default_pdf_sizes() {

	return array( 'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8');
}

/**
 * Different Pdf size Array for select box
 * 
 * Handle to get Different Pdf size Array for select box
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
function woo_vou_get_pdf_sizes_select() {

	$sizes	= woo_vou_get_pdf_sizes();
	$size_select_data	= array();

	if( !empty( $sizes ) ) {//if size is not empty

		foreach ( $sizes as $size => $values ) {

			$size_select_data[$size]	= $size;
		}
	}

	return apply_filters( 'woo_vou_get_pdf_sizes_select', $size_select_data );
}

/**
 * Handle to Set voucher download text
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
function woo_vou_voucher_download_text( $product_id ) {

	// Get voucher download text
	$vou_download_text = get_option( 'vou_download_text' );
	$vou_download_text = !empty($vou_download_text) ? $vou_download_text : esc_html__( 'Voucher Download', 'woovoucher' ) ;

	return apply_filters( 'woo_vou_voucher_download_text', $vou_download_text, $product_id );
}

/**
 * Get Woocommerce Screen ID
 * 
 * Handles to get woocommerce screen id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.2
 */
function woo_vou_get_wc_screen_id() {

	$wc_screen_id = sanitize_title( esc_html__( 'WooCommerce', 'woocommerce' ) );
	return apply_filters( 'woo_vou_get_wc_screen_id', $wc_screen_id );
}

/**
 * Get Pdf Voucher Screen ID
 * 
 * Handles to get woocommerce screen id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.2
 */
function woo_vou_get_voucher_screen_id() {

	$woo_vou_screen_id = sanitize_title( esc_html__( 'WooCommerce', 'woovoucher' ) );
	return apply_filters( 'woo_vou_get_voucher_screen_id', $woo_vou_screen_id );
}

/**
 * Get Voucher Admin Roles
 * 
 * Handles to get voucher admin roles
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.5
 */
function woo_vou_assigned_admin_roles() {

	return apply_filters( 'woo_vou_assigned_admin_roles', array( 'administrator' ) );
}

/**
 * Convert hex color to rgb
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.5
 */
function woo_vou_hex_to_rgb( $hex ) {
	
	$hex	= str_replace( "#", "", $hex );
	
	if( strlen( $hex ) == 3 ) {
		
		$r	= hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
		$g	= hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
		$b	= hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
	} else {
		
		$r	= hexdec( substr( $hex, 0, 2 ) );
		$g	= hexdec( substr( $hex, 2, 2 ) );
		$b	= hexdec( substr( $hex, 4, 2 ) );
	}
	
	$rgb	= array( $r, $g, $b );
	
	return $rgb; // returns an array with the rgb values
}

/**
 * Unlimited Voucher Code Pattern
 * 
 * Handle to get unlimited voucher code pattern
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.1
 */
function woo_vou_unlimited_voucher_code_pattern( $voucode_args = array() ) {
	
	$voucode = '';
	
	$voucode_args	= apply_filters( 'woo_vou_unlimited_code_pattern', $voucode_args );
	
	// get separator from arguments
	$separator	= isset( $voucode_args['separator'] ) ? $voucode_args['separator'] : '-';
	
	if( !empty( $voucode_args ) ) { //arguments are not empty
		
		$length		= isset( $voucode_args['separator'] ) ? ( count( $voucode_args ) - 1 ) : count( $voucode_args );			

		$counter	= 1;

		// Get vouche code postfix from option
		$vou_code_postfix = get_option('vou_code_postfix');
		
		foreach( $voucode_args as $key => $voucode_arg ) {						
			
			if( $key == 'code_prefix' && empty( $voucode_arg ) ) {
				$length -= 1;
				continue;
			}				
				
			if( $key != 'separator' ) {
				$voucode .= $voucode_arg;
				if( $counter != $length ) {
					$voucode .= $separator;
				}
			}
			$counter++;
		}

		if( !empty($vou_code_postfix) ){
			$voucode .= $separator.$vou_code_postfix;
			$vou_code_postfix = (int)$vou_code_postfix + 1;
			update_option( 'vou_code_postfix', $vou_code_postfix );
		}
	}
	
	return apply_filters( 'woo_vou_unlimited_voucher_code', $voucode, $voucode_args );
}

/**
 * Enable/Disable Template Selection Display
 * 
 * Handle to enable/disable template selection display
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.5
 */
function woo_vou_enable_template_display_features() {
	return apply_filters( 'woo_vou_enable_template_display_features', true );
}

function woo_vou_replace_all_shortcodes_with_value( $voucher_template_html, $woo_vou_details ) {
	
	foreach ( $woo_vou_details as $key => $woo_vou_detail ) {
	
		switch( $key ) {
			case 'vendoraddress':
			case 'redeem':
			case 'recipientmessage':
				$voucher_template_html = str_replace( '{' . $key. '}', nl2br( $woo_vou_detail ), $voucher_template_html );			
				break;
			default:
				$voucher_template_html = str_replace( '{' . $key. '}', $woo_vou_detail, $voucher_template_html );
				break;
		}		
	}

    // check if template tags contains {productshortdesc-numbers}
	$shortcode_limit_char = array( 
		'productshortdesc', 
		'variationdesc', 
		'productfulldesc' 
	);
	foreach ($shortcode_limit_char as $shortcode) {
	    if( preg_match_all( '/\{('.$shortcode.')(-)(\d*)\}/', $voucher_template_html, $code_matches ) ) {
	        $trim_tag = $code_matches[0][0];
	        $trim_length = $code_matches[3][0];
	        $trim_content = (!empty($woo_vou_details[$shortcode])) ? strip_tags($woo_vou_details[$shortcode]) : '' ;
	        $trim_content = substr( $trim_content, 0, $trim_length);
	        
	        $voucher_template_html = str_replace( $trim_tag, $trim_content, $voucher_template_html );
	    }
	}
	
	return $voucher_template_html;
}


/**
 * Convert Long url into tiny/small url 
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.0
 */
function woo_vou_shorten_url_with_tinyurl ( $pageurl ) {	
	
	$tiny_url = '';
	
	if( !empty( $pageurl ) ) {		
		$tiny_url =  wp_remote_fopen('http://tinyurl.com/api-create.php?url=' . urlencode( $pageurl ) );		
	}
	
	if ( !empty( $tiny_url ) ) {
		return $tiny_url;
	} else {
		return $pageurl;
	}
}

/**
 * Get capabilities for vendor roles.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.7
 */
 function woo_vou_get_capabilities() {
	$capabilities = array();

	$capability_types = array( WOO_VOU_POST_TYPE );

	foreach ( $capability_types as $capability_type ) {

		$capabilities[ $capability_type ] = array(
			// Post type
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",

			// Terms
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms"
		);
	}

	return $capabilities;
}

/**
 * Add Capabilities to roles.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.7
 */
 function woo_vou_add_role_capabilities() {

 	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	//Get supported roles
	$woo_vou_vendor_role = apply_filters( 'woo_vou_edit_vendor_role', array( 'administrator', WOO_VOU_VENDOR_ROLE ) );

	if( !empty( $woo_vou_vendor_role ) ) {
		foreach ( $woo_vou_vendor_role as $role ) {
		    foreach ( woo_vou_get_capabilities() as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( $role, $cap );
				}//End loop #cap
			}//End loop #cap group
		}//End loop #role
	}//End if
 }
 
/**
 * Get Voucher Code Total Price with filter.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
 function woo_vou_get_voucher_code_total_price( $voucodeid, $total_price ) {
	return apply_filters( 'woo_vou_get_voucher_code_total_price', $total_price, $voucodeid );
 }
 
/**
 * Get Voucher Price
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_get_voucher_price( $item_id, $product_id, $values = array() ) {

	$prefix		= WOO_VOU_META_PREFIX; // Get prefix

	$_product 	= wc_get_product( $product_id ); // Get product detail

	$values	= array_merge( array(
										'line_subtotal_tax' => 0,
										'line_subtotal' => 0,
										'quantity' => 1,
										'is_preview' => false
									), $values );

 	// Get voucher price options
	$price_options 	= get_option('vou_voucher_price_options');
	$price_options	= isset($price_options) ? $price_options : '';

	$tax_inclusive		= get_option( 'woocommerce_prices_include_tax' ); // Get Woocommerce option for tax inclusive/exclusive
	$enable_tax_calc	= get_option( 'woocommerce_calc_taxes' ); // Get option whether tax are enabled or not

	$decimal_points	= get_option( 'woocommerce_price_num_decimals' ); // Get option decimal points number after points

	
	// If price option is not empty
	if( !empty( $price_options ) ) {
		
		if( $price_options == 1 ) {

			$regular_price	= woo_vou_get_pro_regular_price($_product); // Get product regular price

			if($enable_tax_calc === 'yes' && $tax_inclusive === 'yes' && !empty($regular_price)){
				$regular_price = apply_filters('woo_vou_assign_voucher_price', $regular_price, $values, $product_id);
				return 	$regular_price;
			} else if( isset($regular_price) && !empty($regular_price) ) { // Return regular price if not empty

				$voucher_price = $regular_price + $values['line_subtotal_tax'];
				$voucher_price = round( $voucher_price, $decimal_points );
				$voucher_price = apply_filters('woo_vou_assign_voucher_price', $voucher_price, $values, $product_id);
				return 	$voucher_price;
			} else {

				if( $values['is_preview'] ) {

					$product_price = wc_get_price_including_tax( $_product );
				} else {

					$product_price = $values['line_subtotal'] / $values['quantity']; // Return order item line subtotal if regular price is empty
				}

				$product_price = round( $product_price, $decimal_points );

				$product_price = apply_filters('woo_vou_assign_voucher_price', $product_price, $values, $product_id);
				return $product_price;
			}
		} else {

			global $woo_vou_wc_currency_switch;

			$voucher_price		= get_post_meta( $product_id, $prefix.'voucher_price', true ); // Get vocher amount from product meta

			if($enable_tax_calc === 'yes' && $tax_inclusive === 'yes' && !empty($voucher_price)){ // Check whether Tax Calculation is checked and Tax inclusive is checked or not
				$currency_price = $woo_vou_wc_currency_switch->woo_vou_multi_currency_from_default_price( $voucher_price, true ); // If tax is inclusive than return voucher price

				$currency_price = apply_filters('woo_vou_assign_voucher_price', $currency_price, $values, $product_id);
				return $currency_price;

			} else if( isset($voucher_price) && !empty($voucher_price) ){ // Return voucher price if not empty

				$voucher_price = $voucher_price + $values['line_subtotal_tax'];
				$voucher_price = round( $voucher_price, $decimal_points );
				$voucher_price = $woo_vou_wc_currency_switch->woo_vou_multi_currency_from_default_price( $voucher_price, true );
				
				$voucher_price = apply_filters('woo_vou_assign_voucher_price', $voucher_price, $values, $product_id);

				return apply_filters( 'woo_vou_get_voucher_price_with_tax', $voucher_price, $product_id );
				
			} else {

				if( $values['is_preview'] ) {

					$voucher_price = wc_get_price_including_tax( $_product );
				} else {

					$voucher_price = $values['line_subtotal'] + $values['line_subtotal_tax'];
				}

				// Return order item line subtotal if voucher price empty
				$product_price = $voucher_price / $values['quantity'];
				$product_price = round( $product_price, $decimal_points );

				$product_price = apply_filters('woo_vou_assign_voucher_price', $product_price, $values, $product_id);

				return $product_price;
			}
		}
	} else {

		if( $values['is_preview'] ) {

			$voucher_price = wc_get_price_including_tax( $_product );
		} else {

			$voucher_price = $values['line_subtotal'] + $values['line_subtotal_tax'];
		}

		// Return order item line subtotal if price option not set
		$product_price = isset ( $values['quantity'] ) ? $voucher_price / $values['quantity'] : $voucher_price;
		
		$product_price = round( $product_price, $decimal_points );

		$product_price = apply_filters('woo_vou_assign_voucher_price', $product_price, $values, $product_id);

		return $product_price;
	} 
 }

/**
 * Get Voucher Price By Order Item
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_get_voucher_price_by_order_item( $item , $item_id, $order = '' ){

 	$prefix = WOO_VOU_META_PREFIX; // Get prefix

	$vou_price 	=  wc_get_order_item_meta( $item_id, $prefix.'voucher_price', true ); // Get total price of voucher code

	// Check voucher price empty or not
	if( empty( $vou_price ) ) {

		if ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) == -1 ) {
			
			$vou_price = !empty( $item['qty'] ) ? $item['line_subtotal'] / $item['qty'] : $item['line_subtotal']; // Calculate voucher price
		} else {
			
			$quantity 		= wc_get_order_item_meta( $item_id, '_qty', true). '<br>'; // quantity
    		$line_sub_total = wc_get_order_item_meta( $item_id, '_line_subtotal', true). '<br>'; // Line subtotal
    		$vou_price		= $line_sub_total / $quantity;
		}
	}

	return $vou_price; // Return result
}

/**
 * Get Product ID
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $product
 * @return $product_id
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.2
 */
function woo_vou_get_product_id( $product ) {
	
	if( empty( $product )) {
		return '';
	}

	return method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
}

/**
 * Get Product Variation ID
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $variation
 * @return $product_variation_id
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.2
 */
function woo_vou_get_product_variation_id( $variation ) {
	return method_exists( $variation, 'get_id' ) ? $variation->get_id() : $variation->variation_id;
}

/**
 * Get order Currency
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $variation
 * @return string order currency
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.2
 */
function woo_vou_get_order_currency( $order ) {
	return method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
}

/**
 * Get order id
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $order
 * @return $order_id
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_get_order_id( $order ) {
	return method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
}

/**
 * Get order status
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $order
 * @return string order status
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_get_order_status( $order ) {
	return method_exists( $order, 'get_status' ) ? $order->get_status() : $order->status;
}

/**
 * Get product regular price
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $product
 * @return $regular_price
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_get_pro_regular_price( $product ) {
	return method_exists( $product, 'get_regular_price' ) ? $product->get_regular_price() : $product->regular_price;
}

/**
 * Get product sale price
 * 
 * Added to add compability with WooCommerce version 3.0.0
 *
 * @param object $product
 * @return $sale_price
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_get_pro_sale_price( $product ) {
	return method_exists( $product, 'get_sale_price' ) ? $product->get_sale_price() : $product->sale_price;
}

/**
 * Check product partial redeem is enable or not by product id
 * 
 * @param $product_id
 * @return $product_partial_redeem
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
 function woo_vou_check_partial_redeem_by_product_id( $product_id ) {

 	$product_partial_redeem = false;
	$vou_enable_partial_redeem = get_option('vou_enable_partial_redeem');
	$vou_partial_redeem_product_ids = get_option('vou_partial_redeem_product_ids');

	if( $vou_enable_partial_redeem == 'yes' ) { // if partial redeem option is checked

		$product_partial_redeem = true;
	} elseif( $vou_enable_partial_redeem == 'no' ){ // if partial redeem option is unchecked

		if( empty($vou_partial_redeem_product_ids) ){ // if partial redeem product id not selected

			$product_partial_redeem = false;
		} elseif( !empty($vou_partial_redeem_product_ids) ) { // if partial redeem products id selected

			$vou_partial_redeem_product_ids_array = explode( ',', $vou_partial_redeem_product_ids );
		    if ( in_array( $product_id, $vou_partial_redeem_product_ids_array ) ) { //Add partial redeem field
		        $product_partial_redeem = true;
		    } else {
		        $product_partial_redeem = false;
		    }
		}
	}

	return $product_partial_redeem;
}
 
 
/**
 * Check product partial redeem is enable or not by order and voucher code
 * 
 * @param $voucode, $order
 * @return $enable_partial_redeem
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
 function woo_vou_check_partial_redeem_by_order( $voucode, $order = '' ) {

	// Define global variables
	global $woo_vou_model, $woo_vou_voucher;

	$prefix = WOO_VOU_META_PREFIX; // Get prefix

	if( empty($order) ){
		// get voucher id 
		$voucodeid = woo_vou_get_voucodeid_from_voucode($voucode);

		// get order from voucher meta	 
		$voucode_order_id = get_post_meta($voucodeid, $prefix . 'order_id', true);
		$order = new Wc_Order($voucode_order_id);
	}

	//get order items
	$order_items = $order->get_items();

	$check_code	= trim( $voucode );
	$item_array	= $woo_vou_model->woo_vou_get_item_data_using_voucher_code( $order_items, $check_code );

	$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
	$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();
	$_product 	= $order->get_product_from_item( $item );
	$product_id = woo_vou_get_product_id( $_product ); // get product id

	// get partial redeem
	$enable_partial_redeem = woo_vou_check_partial_redeem_by_product_id( $product_id );

	return $enable_partial_redeem;
}



/**
 * Handles to get a list of all delivery methods
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.3
 */
function woo_vou_voucher_delivery_methods(){

	return apply_filters('woo_vou_voucher_delivery_methods', array(
																		'email' 	=> esc_html__( 'Email to Recipient', 'woovoucher' ),
																		'offline'	=> esc_html__( 'Offline', 'woovoucher' )
																	));
}

/**
 * Handles to get a list of all recipient details
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.3
 */
function woo_vou_voucher_recipient_details(){

	return apply_filters('woo_vou_voucher_recipient_details', array(
		'recipient_name' => array( 
								'label' => esc_html__( 'Recipient Name', 'woovoucher' ),
								'type'	=> 'text'
							),
		'recipient_email' => array( 
								'label' => esc_html__( 'Recipient Email', 'woovoucher' ),
								'type'	=> 'email'
							),
		'recipient_message' => array( 
								'label' => esc_html__( 'Recipient Message', 'woovoucher' ),
								'type'	=> 'textarea'
							),
		'recipient_giftdate' => array( 
								'label' => esc_html__( 'Recipient Gift Date', 'woovoucher' ),
								'type'	=> 'date'
							),
		'recipient_phone' => array(  // Add recipient phone tab
								'label' => esc_html__( 'Recipient Phone', 'woovoucher' ),
								'type'	=> 'text'
							)
	));
}


/**
 * return rules for htaccess. 
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.0
 */
function woo_vou_htaccess_rule_string() {
	
$custom_rules = '
# BEGIN WooCommerce PDF Vouchers
<IfModule mod_rewrite.c>
RewriteCond %{HTTP_USER_AGENT} ^$
RewriteRule ^ - [L]
</IfModule>
# END WooCommerce PDF Vouchers';	

	return $custom_rules;
}


/**
 * Instanciate the filesystem class
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.0
 * @return object WP_Filesystem_Direct instance
 */
function woo_vou_direct_filesystem() {

    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    return new WP_Filesystem_Direct( new StdClass() );
}


/**
 * File creation based on WordPress Filesystem
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.0
 *
 * @param string $file    The path of file will be created.
 * @param string $content The content that will be printed in advanced-cache.php.
 * @return bool
 */
function woo_vou_put_content( $file, $content ) {
	$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
	return woo_vou_direct_filesystem()->put_contents( $file, $content, $chmod );
}

/**
 * Used to add the rules to .htaccess file
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.0
 */
function woo_vou_add_rules_to_htaccess() {

	// if iTheme security plugin is activated
	if( class_exists( 'ITSEC_Core' ) ) {		

		$htaccess_file = ABSPATH . '.htaccess';

		if ( empty( get_option('vou_is_htaccess_write') ) && woo_vou_direct_filesystem()->is_writable( $htaccess_file ) ) {

			// Get content of .htaccess file.
			$content = woo_vou_direct_filesystem()->get_contents( $htaccess_file );
			
			// Get PDF Voucher code which need to add in htaccess file
			$rules = woo_vou_htaccess_rule_string();			

			// Update the .htacces file.
			woo_vou_put_content( $htaccess_file, $rules . $content );

			// Update option
			update_option('vou_is_htaccess_write', 1 );
		}
	}
}

function woo_vou_remove_rules_from_htaccess() {

	$htaccess_file = ABSPATH . '.htaccess';

	if ( woo_vou_direct_filesystem()->is_writable( $htaccess_file ) ) {		

		// Get content of .htaccess file.
		$content = woo_vou_direct_filesystem()->get_contents( $htaccess_file );		
		
		// Remove the PDF Voucher marker.
		$content = preg_replace( '/# BEGIN WooCommerce PDF Vouchers(.*)# END WooCommerce PDF Vouchers/isU', '', $content );

		// Remove blank lines if there is any
		$content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);				

		// Update the .htacces file.
		woo_vou_put_content( $htaccess_file, $content );

		// Delete option
		delete_option( 'vou_is_htaccess_write' );
	}
}
