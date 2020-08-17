<?php
/**
 * Handles to get voucher code detail
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */

// Define global variables
global $woo_vou_model, $woo_vou_voucher,$woo_vou_wc_currency_switch;

$prefix = WOO_VOU_META_PREFIX; // Get prefix

// If voucher code is not empty
if (!empty($voucodeid)) {


    // Get vouchercodes data 
    $voucher_data 	= get_post($voucodeid);
    $voucode		= get_post_meta($voucodeid, $prefix . 'purchased_codes', true);
    $voucode        = apply_filters('woo_vou_code_detail_info_voucher_code', $voucode);
    $voucode 		= strtolower(trim($voucode));
    $order_id 		= get_post_meta($voucodeid, $prefix . 'order_id', true);
    $order 			= wc_get_order($order_id); // Get order

    // Get order details
    $order_date 	= $woo_vou_model->woo_vou_get_order_date_from_order($order); // order date
    $payment_method = $woo_vou_model->woo_vou_get_payment_method_from_order($order); // payment method
    $order_total 	= wc_price($woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total(), $order_id)); // order total
    
    $order_discount = wc_price( $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total_discount() , $order_id ), array('currency' => woo_vou_get_order_currency($order)));
    
    $items 			= $order->get_items(); // Get order items
    $redeemed_infos = $redeem_info_columns = array();

    // Looping on order items
    foreach ($items as $item_id => $product_data) {

        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
       
        $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
        $voucher_codes = array_map('trim', $voucher_codes);
        $voucher_codes = array_map('strtolower', $voucher_codes);

        // If voucher code belongs to current item
        if (in_array($voucode, $voucher_codes)) {

            // Get product data
            $product_name 		= $product_data['name'];
            $product_id 		= $product_data['product_id'];
            $_product 			= $order->get_product_from_item($product_data);
            $variation_id 		= $product_data['variation_id'];
            $product_item_meta 	= isset($product_data['item_meta']) ? $product_data['item_meta'] : array();
            $product_variations = !empty( $product_data['item_meta'] ) ? $product_data['item_meta'] : array();
            $total_price 		= $woo_vou_model->woo_vou_get_product_price($order_id, $item_id, $product_data);

            $_product_id		= !empty( $variation_id ) ? $variation_id : $product_id;

            if (isset($total_price) && !empty($total_price)) {

                $item_price = wc_price($total_price);
            }
			
			$product_start_date = get_post_meta($product_id,$prefix.'product_start_date',true);
			$product_exp_date 	= get_post_meta($product_id,$prefix.'product_exp_date',true);
			
            $total_redeemed_price 	= $woo_vou_voucher->woo_vou_get_total_redeemed_price_for_vouchercode($voucodeid);
            $remaining_redeem_price = number_format((float) ($total_price - $total_redeemed_price), 2, '.', '');
            $redemable_price 		= wc_price($remaining_redeem_price);

            $product_info_columns = array(
                'item_name' => esc_html__('Item Name', 'woovoucher'),
                'item_price' => esc_html__('Price ( Voucher Price )', 'woovoucher'),
            );
			
            // add redeemable price column
            $product_info_columns['redeemable_price'] = esc_html__('Redeemable Price', 'woovoucher');
            
			if(!empty($product_start_date) && !empty($product_exp_date)){
				// Add Product start date end date column
				$product_info_columns['product_date_renge'] = esc_html__('Product Date(s)', 'woovoucher');
			}

            // redeem information key parameter
            $redeem_info_columns = array(
                'item_name' => esc_html__('Item Name', 'woovoucher'),
                'redeem_price' => esc_html__('Redeem Amount', 'woovoucher'),
                'redeem_by' => esc_html__('Redeem By', 'woovoucher'),
                'redeem_date' => esc_html__('Redeem Date', 'woovoucher')
            );

            // get product information
            $product_information = array(
                'item_id' => $product_id,
                'item_name' => $product_name,
                'item_price' => $item_price,
                'redeemable_price' => $redemable_price,
            );
			if(!empty($product_start_date) && !empty($product_exp_date)){
				$product_start_date = $woo_vou_model->woo_vou_get_date_format($product_start_date,true);
				$product_exp_date = $woo_vou_model->woo_vou_get_date_format($product_exp_date,true);
				
				
				$product_date_renge = '<strong>'.esc_html__('Start Date', 'woovoucher').'</strong><br>';
				$product_date_renge .= $product_start_date.'<br>';

				$product_date_renge .= '<strong>'.esc_html__('End Date','woovoucher').'</strong><br>';
				$product_date_renge .= $product_exp_date;
					
				$product_information['product_date_renge']  = $product_date_renge;
			}

            $primary_vendor_user = '';

            $author = get_post_field( 'post_author', $voucodeid );
            $user_data = get_userdata($author);
            
            // check if the post author is not admin then it's primary vendor
            if( !empty( $user_data ) && !in_array( 'administrator', $user_data->roles ) ){
                
                // get primary vendor data
                $primary_vendor_user = $author;
            }

            if (!empty($primary_vendor_user)) {
                $user_data = get_userdata($primary_vendor_user);
                if( !empty($user_data) ){
                    $primary_vendor_data = array(
                        'id' => $primary_vendor_user,
                        'user_email' => $user_data->user_email,
                        'display_name' => $user_data->display_name
                    );
                }
            }

            // get secondary vendor data
            $sec_vendor_users = get_post_meta($voucodeid, $prefix . 'sec_vendor_users', true);
            if (!empty($sec_vendor_users)) {
                $seconday_vendorIds = explode(",", $sec_vendor_users);
            }

            if (!empty($seconday_vendorIds)) {
                foreach ($seconday_vendorIds as $key => $vendor_id) {

                    $user_data = get_userdata($vendor_id);
                    if( !empty($user_data) ){

                        $secondary_vendors[] = array('id' => $vendor_id, 'user_email' => $user_data->user_email, 'display_name' => $user_data->display_name);
                    }
                }
            }

            // get voucher information
            $allorderdata = $woo_vou_model->woo_vou_get_all_ordered_data($order_id);

            // Default vendor address
            $vendor_address_data = esc_html__('N/A', 'woovoucher');
			$expires_colum_lebal = esc_html__('Expires', 'woovoucher');
            if ($_product) {

            	$parent_product_id = $woo_vou_model->woo_vou_get_item_productid_from_product($_product);
            	//get all voucher details from order meta
				$allvoucherdata = apply_filters( 'woo_vou_order_voucher_metadata', isset( $allorderdata[$parent_product_id] ) ? $allorderdata[$parent_product_id] : array(), $order_id, $item_id, $parent_product_id );
		
				
                if ($_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['vendor_address'])) {

                    if (isset($allvoucherdata['vendor_address'][$variation_id]) && !empty($allvoucherdata['vendor_address'][$variation_id])) {
                    	$vendor_address_data = nl2br($allvoucherdata['vendor_address'][$variation_id]);
                    }
                } elseif (isset($allvoucherdata['vendor_address']) && !empty($allvoucherdata['vendor_address'])) {

                    $vendor_address_data = nl2br($allvoucherdata['vendor_address']);
                }

				
				$vou_start_date = !empty(get_post_meta($voucodeid,$prefix.'start_date',true)) ? $woo_vou_model->woo_vou_get_date_format(get_post_meta($voucodeid,$prefix.'start_date',true), true) : '';
	           $exp_date = '';
               
				if ($_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['exp_date'])) {
		
                    if (isset($allvoucherdata['exp_date'][$variation_id]) && !empty($allvoucherdata['exp_date'][$variation_id])) {
						
                    	$exp_date = !empty($allvoucherdata['exp_date'][$variation_id]) ? $woo_vou_model->woo_vou_get_date_format($allvoucherdata['exp_date'][$variation_id], true) : '';
                    }
                } elseif (isset($allvoucherdata['exp_date']) && !empty($allvoucherdata['exp_date'])) {

                    $exp_date = !empty($allvoucherdata['exp_date']) ? $woo_vou_model->woo_vou_get_date_format($allvoucherdata['exp_date'], true) : '';     
                }
				
				
				
				if ($_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['exp_type'])) {
		
                    if (isset($allvoucherdata['exp_type'][$variation_id]) && !empty($allvoucherdata['exp_type'][$variation_id])) {
						
                    	$exp_type = !empty($allvoucherdata['exp_type'][$variation_id]) ? $allvoucherdata['exp_type'][$variation_id] : '';
                    }
                } elseif (isset($allvoucherdata['exp_type']) && !empty($allvoucherdata['exp_type'])) {

                    $exp_type = !empty($allvoucherdata['exp_type']) ? $allvoucherdata['exp_type'] : '';     
                } 
				
				 $recipient_giftdate = wc_get_order_item_meta($item_id, $prefix . 'recipient_giftdate');
				
                $start_date = '';

				if( $exp_type == 'based_on_gift_date'){
					$start_date = $woo_vou_model->woo_vou_get_date_format($recipient_giftdate['value'],true);
                    if( empty( $recipient_giftdate['value'] ) ) {
                        $start_date = get_post_meta($voucodeid,$prefix . 'start_date',true);
                        $start_date = !empty($start_date) ? $woo_vou_model->woo_vou_get_date_format($start_date, true) : '';
                    }
				}else{
					if(!empty(get_post_meta($voucodeid,$prefix . 'start_date',true))){
						$start_date = get_post_meta($voucodeid,$prefix . 'start_date',true);
                        $start_date = !empty($start_date) ? $woo_vou_model->woo_vou_get_date_format($start_date, true) : '';     
					}
					else{
						if ($_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['start_date'])) {
		
							if (isset($allvoucherdata['start_date'][$variation_id]) && !empty($allvoucherdata['start_date'][$variation_id])) {
								
								$start_date = !empty($allvoucherdata['start_date'][$variation_id]) ? $woo_vou_model->woo_vou_get_date_format($allvoucherdata['start_date'][$variation_id], true) : '';
							}
						} elseif (isset($allvoucherdata['start_date']) && !empty($allvoucherdata['start_date'])) {

							$start_date = !empty($allvoucherdata['start_date']) ? $woo_vou_model->woo_vou_get_date_format($allvoucherdata['start_date'], true) : '';     
						}	
					}
				}
				
			   
	            $website_url 	= !empty($allvoucherdata['website_url']) ? $allvoucherdata['website_url'] : esc_html__('N/A', 'woovoucher');
	            $redeem 		= !empty($allvoucherdata['redeem']) ? nl2br($allvoucherdata['redeem']) : esc_html__('N/A', 'woovoucher');
	            $pdf_template	= isset($allvoucherdata['pdf_template']) ? $allvoucherdata['pdf_template'] : '';

                //PDF Selection Data
                if ( isset( $items[$item_id]['woo_vou_pdf_template_selection'] ) ) {

                    $pdf_template_data  = maybe_unserialize( $items[$item_id]['woo_vou_pdf_template_selection'] );
                    $pdf_template       = isset($pdf_template_data['value']) ? $pdf_template_data['value'] : '';
                }

                $global_pdf_template    = get_option( 'vou_pdf_template' );
                if( !empty( $pdf_template ) ){
                    if( is_array($pdf_template) ){
                        if( isset($pdf_template[$_product_id]) && !empty($pdf_template[$_product_id]) ){
                            $pdf_template = $pdf_template[$_product_id];
                        } else {
                            $pdf_template = $global_pdf_template;
                        }
                    } else {
                        $pdf_template = $pdf_template;
                    }
                } else {
                    $pdf_template = $global_pdf_template;
                }

                $voucher_vendor_logo = isset($allvoucherdata['vendor_logo']) ? $allvoucherdata['vendor_logo']['src'] : '';
	            
				
				//If coupon code
				$coupon = new WC_Coupon($voucode);
                
				if( !empty( $coupon ) ) {
                    $coupon_post = get_post($coupon->get_id());
                }
				
                $is_coupon = esc_html__("No","woovoucher");

				if(isset($coupon_post) && !empty($coupon_post)){
					$is_coupon = esc_html__("Yes","woovoucher");
				} 
				
				$redeem_exlude_days = !empty(get_post_meta($voucodeid,$prefix.'disable_redeem_day',true))?get_post_meta($voucodeid,$prefix.'disable_redeem_day',true):array();
				
				$voucher_location = isset($allvoucherdata['avail_locations']) ? $allvoucherdata['avail_locations'] : array();
				
				$voucher_information = array(
	                'logo' 			=> $voucher_vendor_logo,
	                'pdf_template' 	=> $pdf_template,
	                'website_url' 	=> $website_url,
	                'redeem' 		=> $redeem,
	                'expires' 		=> $exp_date,
	                'voucher_start_date'=> $start_date,
	                'voucher_exp_type'=> $exp_type,
	                'is_coupon'		=> $is_coupon,
	                'exlude_redeem_day'	=> $redeem_exlude_days,
	                'vendor_locations'	=> $voucher_location, 
	            );
				if(!empty($start_date)){
					$expires_colum_lebal = esc_html__('Voucher Date(s)','woovoucher');
				}
				
            } else {

            	$voucher_information = array(
	                'logo' 			=> esc_html__('N/A', 'woovoucher'),
	                'pdf_template' 	=> esc_html__('N/A', 'woovoucher'),
	                'website_url' 	=> esc_html__('N/A', 'woovoucher'),
	                'redeem' 		=> esc_html__('N/A', 'woovoucher'),
	                'expires' 		=> '',
	                'other_vou_info' => '',
	            );
            }

            $voucher_info_columns = array(
                'logo' 			=> esc_html__('Logo', 'woovoucher'),
                'voucher_data' 	=> esc_html__('Vendor Data', 'woovoucher'),
                'expires' 		=> $expires_colum_lebal,
                'pdf_template' 	=> esc_html__('PDF Template', 'woovoucher'),
                'other_vou_info' => esc_html__('Other Information', 'woovoucher')
            );

            break;
        }
    }

    // get buyer information
    $buyer_info_columns = array(
        'buyer_name' => esc_html__('Name', 'woovoucher'),
        'buyer_email' => esc_html__('Email', 'woovoucher'),
        'billing_address' => esc_html__('Billing Address', 'woovoucher'),
        'shipping_address' => esc_html__('Shipping Address', 'woovoucher'),
        'buyer_phone' => esc_html__('Phone', 'woovoucher')
    );

    $buyer_information = $woo_vou_model->woo_vou_get_buyer_information($order_id);
    $billing_address = $order->get_formatted_billing_address();
    $shipping_address = $order->get_formatted_shipping_address();
    $buyer_information['billing_address'] = $billing_address;
    $buyer_information['shipping_address'] = $shipping_address;

    // get order information
    $order_info_columns = array(
        'order_id' => esc_html__('Order ID', 'woovoucher'),
        'order_date' => esc_html__('Order Date', 'woovoucher'),
        'payment_method' => esc_html__('Payment Method', 'woovoucher'),
        'order_total' => esc_html__('Order Total', 'woovoucher'),
        'order_discount' => esc_html__('Order Discount', 'woovoucher')
    );

    $order_information = array(
        'order_id' => $order_id,
        'order_date' => $woo_vou_model->woo_vou_get_date_format($order_date, true),
        'payment_method' => $payment_method,
        'order_total' => $order_total,
        'order_discount' => $order_discount,
    );

    // Get partially used voucher code data
    $args = $partially_redeemed_data = array();
    $args = array(
        'woo_vou_list' => true,
        'post_parent' => $voucodeid
    );

    //get partially used voucher codes data from database
    $redeemed_data = woo_vou_get_partially_redeem_details($args);
    $partially_redeemed_data = isset($redeemed_data['data']) ? $redeemed_data['data'] : '';
    $redeemed_data_cnt = isset($redeemed_data['total']) ? $redeemed_data['total'] : '';

    if (!empty($partially_redeemed_data)) {

        foreach ($partially_redeemed_data as $key => $value) {

            $user_id = get_post_meta($value['ID'], $prefix . 'redeem_by', true);
			if($user_id == '0'){
				$user_detail = get_userdata($user_id);
				$display_name = esc_html__('Guest User','woovoucher');
			}
			else{
				$user_detail = get_userdata($user_id);
				$display_name = isset($user_detail->display_name) ? $user_detail->display_name : '';
			}
            

            $redeemed_amount = get_post_meta($value['ID'], $prefix . 'partial_redeem_amount', true);
            $redeem_date = get_post_meta($value['ID'], $prefix . 'used_code_date', true);

            $redeemed_infos[$key] = array(
                "redeem_by" => $display_name,
                "redeem_amount" => $redeemed_amount,
                "redeem_date" => $woo_vou_model->woo_vou_get_date_format($redeem_date, true),
            );

            $redeemed_infos[$key] =  apply_filters('woo_vou_redeem_infos_data',$redeemed_infos[$key],$value);
        }
    } else {

    	$is_code_used 	= get_post_meta( $voucodeid, $prefix.'used_codes', true );
    	$redeem_by		= get_post_meta( $voucodeid, $prefix.'redeem_by', true );
		
		if($redeem_by == '0'){				
				$display_name = esc_html__('Guest User','woovoucher');
			}
			else{
				$user_detail = get_userdata($redeem_by);
				$display_name = isset($user_detail->display_name) ? $user_detail->display_name : '';
			}
		
		
    	$redeem_date 	= get_post_meta( $voucodeid, $prefix . 'used_code_date', true);
    	if( !empty( $is_code_used ) && !empty( $redeem_by ) ) {

    		$redeemed_infos[] = array(
                "redeem_by" 	=> $display_name,
                "redeem_amount" => $total_price,
                "redeem_date" 	=> $woo_vou_model->woo_vou_get_date_format($redeem_date, true),
            );
            
            $redeemed_infos[] =  apply_filters('woo_vou_redeem_infos_data',$redeemed_infos,$value);
    	}
    }

    // Get voucher extra notes
    $voucher_extra_note = get_post_meta( $voucodeid, $prefix.'extra_note', true );
}

// set voucher details data to retrive in template
$vou_code_data = array(
    'voucode' => $voucode,
    'item_id' => $item_id,
    'voucodeid' => $voucodeid,
    'redeem_info_columns' => $redeem_info_columns,
    'redeemed_infos' => $redeemed_infos,
    'order' => $order,
    'product_data' => $product_data,
    'product' => $_product,
    'product_info_columns' => $product_info_columns,
    'product_information' => $product_information,
    'order_info_columns' => $order_info_columns,
    'order_information' => $order_information,
    'buyer_info_columns' => $buyer_info_columns,
    'buyer_information' => $buyer_information,
    'product_variations' => (isset($product_variations) && !empty($product_variations)) ? $product_variations : array(),
    'voucher_info_columns' => $voucher_info_columns,
    'voucher_information' => $voucher_information,
    'primary_vendor_data' => (isset($primary_vendor_data) && !empty($primary_vendor_data)) ? $primary_vendor_data : array(),
    'secondary_vendors' => (isset($secondary_vendors) && !empty($secondary_vendors)) ? $secondary_vendors : array(),
    'vendor_address_data' => $vendor_address_data,
    'voucher_extra_note' => $voucher_extra_note,
    'recipient_columns' => woo_vou_voucher_recipient_details()
);

// do_action to add voucher details content through add_action
do_action('woo_vou_get_voucher_details_custom', $vou_code_data);
?>