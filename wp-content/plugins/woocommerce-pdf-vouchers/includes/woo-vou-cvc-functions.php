<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
    
/**
 * Display Reddem information
 * Like Redeem by, Redeem date
 * 
 * Handles to display information related to redeem
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.7.2
 */
function woo_vou_display_redeem_info_html($vouchercodeid, $order_id, $type = 'html', $default_tab = 'used') {
	
	global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $redeem_details_html = '';

    $user_id = get_post_meta($vouchercodeid, $prefix . 'redeem_by', true);
    $user_detail = get_userdata($user_id);

    $user_profile = add_query_arg(array('user_id' => $user_id), admin_url('user-edit.php'));
    $display_name = isset($user_detail->display_name) ? $user_detail->display_name : '';
    if ( $user_id == '0' ) {
        $display_name = esc_html__( 'Guest User', 'woovoucher' );
    }
    
    if( $user_id == '0' ){
        $display_name_link = $display_name;
    }
    elseif (!empty($display_name)) {
        $display_name_link = '<a href="' . esc_url($user_profile) . '">' . $display_name . '</a>';
    } else {
        $display_name_link = $display_name = esc_html__('N/A', 'woovoucher');
    }

    $redeem_date = get_post_meta($vouchercodeid, $prefix . 'used_code_date', true);
    $redeem_date = !empty($redeem_date) ? $woo_vou_model->woo_vou_get_date_format($redeem_date, true) : '';
    if ($type == 'csv') {

        // get redeem amount 
        $redeem_details_html .= 'Redeem By: ' . $display_name . "\n";
        $redeem_details_html .= 'Redeem Time: ' . $redeem_date . "\n";
    } else { // type is 'html'
        
        // Get partial redeem

        $voucode = get_post_meta($vouchercodeid, $prefix . 'purchased_codes', true);

        $check_redeem_list = !empty($check_redeem_list) ? $check_redeem_list : '';

        $redeem_details_html .= '<table>';
        $redeem_details_html .= '<tr><td style="font-weight:bold;">' . esc_html__('Redeem By:', 'woovoucher') . '</td><td>' . $display_name_link . '</td></tr>';
        $redeem_details_html .= '<tr><td style="font-weight:bold;">' . esc_html__('Redeem Time:', 'woovoucher') . '</td><td>' . $redeem_date . '</td></tr>';
        $redeem_details_html .= $check_redeem_list;
        $redeem_details_html .= '</table>';
    }

    return apply_filters('woo_vou_display_redeem_info_html', $redeem_details_html, $vouchercodeid, $order_id, $type, $default_tab);
}

/**
 * Display Order information
 * 
 * Handles to display buyers information
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.6
 */
function woo_vou_display_order_info_html($order_id, $type = 'html') {
	
	global $woo_vou_model, $woo_vou_wc_currency_switch;

    $order_details_html = '';

    //get order Don't use Wc_Order( $order_id ) as it will thorow fatal error if order not exist
    $order = wc_get_order($order_id);
    if (!empty($order)) {
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            // Order discount
            $order_discount = wc_price($woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total_discount(), $order_id), array('currency' => $order->get_order_currency()));
        } else {
            // Order discount
            $order_discount = wc_price( $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total_discount(), $order_id ), array('currency' => $order->get_currency()));
        }
        
        $order_date = $woo_vou_model->woo_vou_get_order_date_from_order($order); // Get order date
        $payment_method = $woo_vou_model->woo_vou_get_payment_method_from_order($order); // Get payment method

        // format order date
        $order_date = !empty($order_date) ? $woo_vou_model->woo_vou_get_date_format($order_date, true) : '';

        //Order title
        $order_total = wc_price($woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total(), $order_id));

        if ($type == 'html')
            $order_id_url = '<a href="' . esc_url(admin_url('post.php?post=' . absint($order_id) . '&action=edit')) . '">' . $order_id . '</a>';
        if ($type == 'pdf')
            $order_id_url = $order_id;

        if ($type == 'csv') {

            $order_details_html .= 'ID : ' . $order_id . "\n";
            $order_details_html .= 'Order Date : ' . $order_date . "\n";
            $order_details_html .= 'Payment Method : ' . $payment_method . "\n";
            $order_details_html .= 'Order Total : 	' . strip_tags($order_total) . "\n";
            $order_details_html .= 'Order Discount :' . strip_tags($order_discount);
        } else {

            $order_details_html .= '<table class="woo-vou-order-info-table">';
            $order_details_html .= '<tr><td style="font-weight:bold;">' . esc_html__('ID:', 'woovoucher') . '</td><td>' . $order_id_url . '</td></tr>';
            $order_details_html .= '<tr><td style="font-weight:bold;">' . esc_html__('Order Date:', 'woovoucher') . '</td><td>' . $order_date . '</td></tr>';
            $order_details_html .= '<tr class="payment_method"><td style="font-weight:bold;">' . esc_html__('Payment Method:', 'woovoucher') . '</td><td>' . $payment_method . '</td></tr>';
            $order_details_html .= '<tr class="order_total"><td style="font-weight:bold;">' . esc_html__('Order Total:', 'woovoucher') . '</td><td>' . $order_total . '</td></tr>';
            $order_details_html .= '<tr class="order_discount"><td style="font-weight:bold;">' . esc_html__('Order Discount:', 'woovoucher') . '</td><td>' . $order_discount . '</td></tr>';
            $order_details_html .= '</table>';
        }
    }

    return apply_filters('woo_vou_display_order_info_html', $order_details_html, $order_id, $type);
}

/**
 * Display product information
 * 
 * Handles to display buyers information
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.6
 */
function woo_vou_display_product_info_html($order_id, $voucode = '', $type = 'html') {
	
	global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    $product_details_html = ''; // Declare variables

    if (!empty($order_id) && !empty($voucode)) {//If not empty order id and voucher code
        //get order // don't use WC_Order as it will thow fatal error if order not exist			
        $order = wc_get_order($order_id);

        if (!empty($order)) {
            //get order items
            $order_items = $order->get_items();

            $check_code = trim($voucode);
            $item_array = $woo_vou_model->woo_vou_get_item_data_using_voucher_code($order_items, $check_code);

            $item = isset($item_array['item_data']) ? $item_array['item_data'] : array();

            $item_id = isset($item_array['item_id']) ? $item_array['item_id'] : array();

            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
            $_product = $order->get_product_from_item($item);

            //initilize variables
            $product_name = $product_price = $product_sku = '';

            if ($_product) {
                if ($_product && $_product->get_sku()) {
                    $product_sku = esc_html($_product->get_sku());
                }
                if ($type == 'html') {
                    $product_id = $woo_vou_model->woo_vou_get_item_productid_from_product( $_product );
                    $product_name .= '<a target="_blank" href="' . esc_url(admin_url('post.php?post=' . absint($product_id) . '&action=edit')) . '">' . esc_html($item['name']) . '</a>';
                } else {
                    $product_name .= esc_html($item['name']) . "\n";
                }
            } else {
                $product_name .= isset($item['name']) ? esc_html($item['name']) : '';
            }

            //Get product item meta
            $product_item_meta = isset($item['item_meta']) ? $item['item_meta'] : array();

            //Display product variations
            $product_name .= $woo_vou_model->woo_vou_display_product_item_name($product_item_meta, $_product);
            // Get Voucher price
            $vou_price = $woo_vou_model->woo_vou_get_product_price($order_id, $item_id, $item);

            $product_price = wc_price($vou_price);

            if ($type == 'csv') {

                $product_details_html .= esc_html__('Name:', 'woovoucher') . strip_tags($product_name) . "\n";
                $product_details_html .= esc_html__('Price:', 'woovoucher') . strip_tags($product_price) . "\n";
                if (!empty($product_sku))
                    $product_details_html .= esc_html__('SKU:', 'woovoucher') . $product_sku;
            } else {

                $product_details_html .= '<table>';
                $product_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Name:', 'woovoucher') . '</td><td width="77%;">' . $product_name . '</td></tr>';
                $product_details_html .= '<tr><td width="22%" style="font-weight:bold;">' . esc_html__('Price:', 'woovoucher') . '</td><td width="77%;">' . $product_price . '</td></tr>';
                if (!empty($product_sku))
                    $product_details_html .= '<tr><td width="22%" style="font-weight:bold;">' . esc_html__('SKU:', 'woovoucher') . '</td><td width="77%;">' . $product_sku . '</td></tr>';
                $product_details_html .= '</table>';
            }
        }
    }

    return apply_filters('woo_vou_display_product_info_html', $product_details_html, $order_id, $voucode, $type);
}

/**
 * Display Buyer's information
 * 
 * Handles to display buyers information
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.6
 */
function woo_vou_display_buyer_info_html($buyers_information = array(), $display = false) {

    $buyer_info_columns = apply_filters('woo_vou_buyer_info_columns', array(
																		        'buyer_name' => esc_html__('Name:', 'woovoucher'),
																		        'buyer_email' => esc_html__('Email:', 'woovoucher'),
																		        'buyer_address' => esc_html__('Address:', 'woovoucher'),
																		        'buyer_phone' => esc_html__('Address:', 'woovoucher'),
																		    ));

    $first_name = isset($buyers_information['first_name']) ? $buyers_information['first_name'] : '';
    $last_name 	= isset($buyers_information['last_name']) ? $buyers_information['last_name'] : '';
    $email 		= isset($buyers_information['email']) ? $buyers_information['email'] : '';
    $address_1 	= isset($buyers_information['address_1']) ? $buyers_information['address_1'] : '';
    $address_2 	= isset($buyers_information['address_2']) ? $buyers_information['address_2'] : '';
    $city 		= isset($buyers_information['city']) ? $buyers_information['city'] : '';
    $state 		= isset($buyers_information['state']) ? $buyers_information['state'] : '';
    $country 	= isset($buyers_information['country']) ? $buyers_information['country'] : '';
    $postcode 	= isset($buyers_information['postcode']) ? $buyers_information['postcode'] : '';
    $phone 		= isset($buyers_information['phone']) ? $buyers_information['phone'] : '';

    $buyer_details_html = '<table class="woo-vou-buyer-info-table">';
    if (!empty($buyer_info_columns)) {

        foreach ($buyer_info_columns as $col_key => $column) {

            switch ($col_key) {

                case 'buyer_name':
                    $buyer_details_html .= '<tr>';
                    $buyer_details_html .= '<td width="20%" style="font-weight:bold;">' . esc_html__('Name:', 'woovoucher') . '</td>';
                    $buyer_details_html .= '<td width="80%">' . $first_name . ' ' . $last_name . '</td>';
                    $buyer_details_html .= '</tr>';
                    break;

                case 'buyer_email' :
                    $buyer_details_html .= '<tr>';
                    $buyer_details_html .= '<td width="20%" style="font-weight:bold;">' . esc_html__('Email:', 'woovoucher') . '</td>';
                    $buyer_details_html .= '<td width="80%" style="word-break: break-all;">' . $email . '</td>';
                    $buyer_details_html .= '</tr>';
                    break;

                case 'buyer_address' :
                    $buyer_details_html .= '<tr class="buyer_address">';
                    $buyer_details_html .= '<td width="20%" style="font-weight:bold;">' . esc_html__('Address:', 'woovoucher') . '</td>';
                    $buyer_details_html .= '<td width="80%">' . $address_1 . ' ' . $address_2 . '<br />' . $city . ' ' . $state . ' ' . $country . ' - ' . $postcode . '</td>';
                    $buyer_details_html .= '</tr>';
                    break;

                case 'buyer_phone' :
                    $buyer_details_html .= '<tr class="buyer_phone">';
                    $buyer_details_html .= '<td width="20%" style="font-weight:bold;">' . esc_html__('Phone:', 'woovoucher') . '</td>';
                    $buyer_details_html .= '<td width="80%">' . $phone . '</td>';
                    $buyer_details_html .= '</tr>';
                    break;

                default :
                    $buyer_details_html .= apply_filters('woo_vou_buyer_info_columns_value', '', $col_key, $buyers_information);
                    break;
            }
        }
    }

    $buyer_details_html .= '</table>';

    $html = apply_filters('woo_vou_display_buyer_info_html', $buyer_details_html, $buyers_information);

    if ($display) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Display recipient information
 * 
 * Handles to display recipient information
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.1
 */
function woo_vou_display_recipient_info_html( $order_id, $voucode = '', $type = 'html') {
    
    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    $date_format 			= get_option( 'date_format' );
    $recipient_details_html = ''; // Declare variables

    // If not empty order id and voucher code
    if ( !empty( $order_id ) && !empty( $voucode ) ) {

        // Get order
        $order = wc_get_order($order_id);

        if (!empty($order)) {

        	// Recipient columns
        	$recipient_cols = woo_vou_voucher_recipient_details();

            //get order items
            $order_items = $order->get_items();

            $check_code = trim($voucode);
            $item_array = $woo_vou_model->woo_vou_get_item_data_using_voucher_code($order_items, $check_code);

            $item = isset($item_array['item_data']) ? $item_array['item_data'] : array();
            $item_meta  = isset( $item['item_meta'] ) ? $item['item_meta'] : array();

            // Set value of Recipient Information
            $recipient_name_value = $recipient_email_value = $recipient_message_value = $recipient_giftdate_value = '';

            if( !empty($item_meta[$prefix.'delivery_method']) ){
                $recipient_delivery_label = $item_meta[$prefix.'delivery_method']['label'];
                $recipient_delivery_value = $item_meta[$recipient_delivery_label];
            } else {
                $recipient_delivery_label = esc_html__( 'Delivery Method', 'woovoucher' );
            }

            if ($type == 'csv') {

            	foreach ( $recipient_cols as $recipient_key => $recipient_val ) {

            		if( !empty($item_meta[$prefix.$recipient_key]) ){

		                $recipient_col_label = $item_meta[$prefix.$recipient_key]['label'];
		                $recipient_col_value = $item_meta[$prefix.$recipient_key]['value'];

		                if( !empty( $recipient_val ) && is_array( $recipient_val )
	                    	&& array_key_exists( 'type', $recipient_val ) && $recipient_val['type'] == 'date' ) {

	                    	// Get date format from global setting
							$date_format = get_option( 'date_format' );
	                    	$recipient_col_value = date( $date_format, strtotime( $item_meta[$prefix.$recipient_key]['value'] ) );
	                    }
		                if( !empty($recipient_col_value) ) {

		                	$recipient_details_html .= strip_tags($recipient_col_label) . ':' . strip_tags($recipient_col_value) . "\n";
		                }
		            }
            	}
                
                if( !empty($recipient_delivery_value) )
                    $recipient_details_html .= strip_tags($recipient_delivery_label) . ':' . strip_tags($recipient_delivery_value) . "\n";

            } else {

                $recipient_details_html .= '<table>';

                foreach ( $recipient_cols as $recipient_key => $recipient_val ) {

            		$recipient_col_value = $recipient_val;
            		if( !empty($item_meta[$prefix.$recipient_key]) ){

		                $recipient_col_label = $item_meta[$prefix.$recipient_key]['label'];
		                $recipient_col_value = $item_meta[$prefix.$recipient_key]['value'];

		                if( !empty( $recipient_val ) && is_array( $recipient_val )
	                    	&& array_key_exists( 'type', $recipient_val ) && $recipient_val['type'] == 'date' ) {

	                    	// Get date format from global setting
							$date_format = get_option( 'date_format' );
	                    	$recipient_col_value = date( $date_format, strtotime( $item_meta[$prefix.$recipient_key]['value'] ) );
	                    }

		                if( !empty($recipient_col_value) ) {

		                	$recipient_details_html .= '<tr><td width="30%" style="font-weight:bold;">' . $recipient_col_label . ':</td><td width="67%;">' . $recipient_col_value . '</td></tr>';
		                }
		            }
            	}

                if (!empty($recipient_delivery_value))
                    $recipient_details_html .= '<tr><td width="30%" style="font-weight:bold;">' . $recipient_delivery_label . ':</td><td width="67%;">' . $recipient_delivery_value . '</td></tr>';

                $recipient_details_html .= '</table>';
            }
        }
    }

    return apply_filters('woo_vou_display_recipient_info_html', $recipient_details_html, $order_id, $voucode, $type);
}

/**
 * Display voucher information
 * 
 * Handles to display voucher information
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.6
 */
function woo_vou_display_voucher_info_html($order_id, $voucode_id, $voucode = '', $type = 'html') {
    
    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    $voucher_details_html = ''; // Declare variables

    if (!empty($order_id) && !empty($voucode)) {//If not empty order id and voucher code
        //get order // don't use WC_Order as it will thow fatal error if order not exist            
        $order = wc_get_order($order_id);

        if (!empty($order)) {
            //get order items
            $order_items = $order->get_items();

            $check_code = trim($voucode);
            $item_array = $woo_vou_model->woo_vou_get_item_data_using_voucher_code($order_items, $check_code);
            $item 		= isset($item_array['item_data']) ? $item_array['item_data'] : array();
            $item_id 	= isset($item_array['item_id']) ? $item_array['item_id'] : '';

            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
            $_product = $order->get_product_from_item($item);

            // Default vendor address
	        $vendor_address_data  = esc_html__( 'N/A', 'woovoucher' );
            $primary_vendor_data = $secondary_vendors = $seconday_vendorIds = array();

            // If product is variation product
            if( $_product ) {

	            // get product id. In case of variation get parent product id
	            $product_id 		= $_product->get_id(); 
	            $parent_product_id 	= $woo_vou_model->woo_vou_get_item_productid_from_product( $_product );
	
	            // get orderdata
	            $allorderdata   = $woo_vou_model->woo_vou_get_all_ordered_data( $order_id );
	
	            //get all voucher details from order meta
	            $allvoucherdata = isset( $allorderdata[$parent_product_id] ) ? $allorderdata[$parent_product_id] : array();

                if( $_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['vendor_address']) ){

                    if( isset($allvoucherdata['vendor_address'][$product_id]) && !empty($allvoucherdata['vendor_address'][$product_id]) ){

                        $vendor_address_data = nl2br( $allvoucherdata['vendor_address'][$product_id] );
                    }
                } elseif( isset($allvoucherdata['vendor_address']) && !empty($allvoucherdata['vendor_address']) && is_string($allvoucherdata['vendor_address']) ) {

                    $vendor_address_data = nl2br( $allvoucherdata['vendor_address'] );
                }

                $exp_date = !empty($allvoucherdata['exp_date']) ? $woo_vou_model->woo_vou_get_date_format($allvoucherdata['exp_date'], true) : esc_html__('Never Expire', 'woovoucher');

                // get primary vendor data
                $primary_vendor_user = get_post_meta($parent_product_id, $prefix . 'vendor_user', true);
                if (!empty($primary_vendor_user)) {
                    $user_data = get_userdata($primary_vendor_user);
                    if ( !empty($user_data) ) {
                        $primary_vendor_data = array(
                            'id' => $primary_vendor_user,
                            'user_email' => $user_data->user_email,
                            'display_name' => $user_data->display_name
                        );
                    }
                }

                // get secondary vendor data
                $sec_vendor_users = get_post_meta($voucode_id, $prefix . 'sec_vendor_users', true);
                if (!empty($sec_vendor_users)) {
                    $seconday_vendorIds = explode(",", $sec_vendor_users);
                    if (!empty($seconday_vendorIds)) {
                        foreach ($seconday_vendorIds as $key => $vendor_id) {
                            $user_data = get_userdata($vendor_id);
                            if( !empty($user_data) ){
                                $secondary_vendors[] = array('id' => $vendor_id, 'user_email' => $user_data->user_email, 'display_name' => $user_data->display_name);
                            }
                        }
                    }
                }

                //PDF Selection Data
                $pdf_template   = ( isset($allvoucherdata['pdf_template']) ) ? $allvoucherdata['pdf_template'] : '' ;
                if ( isset( $items[$item_id]['woo_vou_pdf_template_selection'] ) ) {

                    $pdf_template_data  = maybe_unserialize( $items[$item_id]['woo_vou_pdf_template_selection'] );
                    $pdf_template       = $pdf_template_data['value'];
                }

                $global_pdf_template    = get_option( 'vou_pdf_template' );
                if( !empty( $pdf_template ) ){
                    if( is_array($pdf_template) ){
                        if( isset($pdf_template[$product_id]) && !empty($pdf_template[$product_id]) ){
                            $pdf_template = $pdf_template[$product_id];
                        } else {
                            $pdf_template = $global_pdf_template;
                        }
                    } else {
                        $pdf_template = $pdf_template;
                    }
                } else {
                    $pdf_template = $global_pdf_template;
                }
            }

            if ($type == 'csv') {

                if (!empty($allvoucherdata['vendor_logo']['src']))
                    $voucher_details_html 	.= esc_html__( 'Logo URL:', 'woovoucher') . $allvoucherdata['vendor_logo']['src'] . "\n";

                if (!empty($vendor_address_data)){

                    $vendor_address_data 	= strip_tags( $vendor_address_data); 
                    $voucher_details_html 	.= esc_html__( 'Vendor\'s Address:', 'woovoucher') . $vendor_address_data . "\n";
		        }

                if (!empty($allvoucherdata['website_url']))
                    $voucher_details_html .= esc_html__('Site URL:', 'woovoucher') . $allvoucherdata['website_url'] . "\n";
                
                if ( !empty( $allvoucherdata['redeem'] ) )
                    $voucher_details_html .= esc_html__('Redeem Instructions:', 'woovoucher') . $allvoucherdata['redeem'] . "\n";

                if ( !empty( $allvoucherdata['avail_locations'] ) ) {
                    $locations = '';
                    foreach ( $allvoucherdata['avail_locations'] as $location ) {
                        
                        if( !empty( $location[$prefix.'locations'] ) ) {
                            
                            $voucher_details_html .= esc_html__('Locations:', 'woovoucher') . $location[$prefix.'locations'] . "\n";
                            if( !empty( $location[$prefix.'map_link'] ) ) {
                                $voucher_details_html .= __('Map Link:', 'woovoucher') . $location[$prefix.'map_link'] . "\n";
                            }
                        }
                    }
                }

                if (!empty($primary_vendor_data)) {

                    $voucher_details_html .= esc_html__('Primary Vendor:', 'woovoucher') . "\n";
                    $voucher_details_html .= $primary_vendor_data['display_name'] . "(#" . $primary_vendor_data['id'] . " - " . $primary_vendor_data['user_email'] . ")" . "\n";
                }

                if (!empty($secondary_vendors)) {

                    foreach ($secondary_vendors as $secondary_vendor) {
                        $vendorData[] = $secondary_vendor['display_name'] . "(#" . $secondary_vendor['id'] . " - " . $secondary_vendor['user_email'] . ")";
                    }
                    $secondary_vendors = implode(",", $vendorData);

                    $voucher_details_html .= esc_html__('Secondary Vendors:', 'woovoucher') . "\n";
                    $voucher_details_html .= $secondary_vendors . "\n";
                }

                if (!empty($exp_date))
                    $voucher_details_html .= esc_html__('Expires:', 'woovoucher') . $exp_date . "\n";

                if (!empty($pdf_template))
                    $voucher_details_html .= esc_html__('PDF Template:', 'woovoucher') . get_the_title($pdf_template) . "\n";

            } else {

                $voucher_details_html .= '<table>';

                if (!empty($allvoucherdata['vendor_logo']['src']))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__( 'Logo URL:', 'woovoucher') . '</td><td width="77%;">' . $allvoucherdata['vendor_logo']['src'] . '</td></tr>';

                if (!empty($vendor_address_data))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__( 'Vendor\'s Address:', 'woovoucher') . '</td><td width="77%;">' . $vendor_address_data . '</td></tr>';

                if (!empty($allvoucherdata['website_url']))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Site URL:', 'woovoucher') . '</td><td width="77%;">' . $allvoucherdata['website_url'] . '</td></tr>';
                
                if (!empty($allvoucherdata['redeem']))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Redeem Instructions:', 'woovoucher') . '</td><td width="77%;">' . $allvoucherdata['redeem'] . '</td></tr>';

                if (!empty($allvoucherdata['avail_locations'])){
                        $locations = '';
                    foreach ( $allvoucherdata['avail_locations'] as $location ) {
                        
                        if( !empty( $location[$prefix.'locations'] ) ) {
                            
                            $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Locations:', 'woovoucher') . '</td><td width="77%;">' . $location[$prefix.'locations'] . '</td></tr>';
                            if( !empty( $location[$prefix.'map_link'] ) ) {
                                $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Map Link:', 'woovoucher') . '</td><td width="77%;">' . $location[$prefix.'map_link'] . '</td></tr>';
                            }
                        }
                    }
                }

                if (!empty($primary_vendor_data)) {
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Primary Vendor:', 'woovoucher') . '</td>'; 
                    $voucher_details_html .= '<td width="77%;">' . $primary_vendor_data['display_name'] . "(#" . $primary_vendor_data['id'] . " - " . $primary_vendor_data['user_email'] . ")" . '</td></tr>';
                }

                if (!empty($secondary_vendors)) {
                    foreach ($secondary_vendors as $secondary_vendor) {
                        $vendorData[] = $secondary_vendor['display_name'] . "(#" . $secondary_vendor['id'] . " - " . $secondary_vendor['user_email'] . ")";
                    }
                    $secondary_vendors = implode(",", $vendorData);

                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Secondary Vendors:', 'woovoucher') . '</td>';
                    $voucher_details_html .= '<td width="77%;">' . $secondary_vendors . '</td></tr>';
                }

                if (!empty($exp_date))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('Expires:', 'woovoucher') . '</td><td width="77%;">' . $exp_date . '</td></tr>';

                if (!empty($pdf_template))
                    $voucher_details_html .= '<tr><td width="22%;" style="font-weight:bold;">' . esc_html__('PDF Template:', 'woovoucher') . '</td><td width="77%;">' . get_the_title($pdf_template) . '</td></tr>';

                $voucher_details_html .= '</table>';
            }
        }
    }

    return apply_filters('woo_vou_display_voucher_info_html', $voucher_details_html, $order_id, $voucode, $type);
}

/**
 * Get more purchased voucher information
 * 
 * Handles to getting voucher information for product meta popup 
 * of purchased voucher code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.3
 */
function woo_vou_load_more_purchased_voucode(){

	// Get global variables
	global $woo_vou_model, $woo_vou_voucher;

    // Declaring variables
    $prefix                     = WOO_VOU_META_PREFIX;
    $html                       = '';
    $response                   = array();
    $postid                     = $_POST['purchased_post_id'];
    $purchased_paged            = ( (int)$_POST['purchased_paged'] ) + 1;
    $purchased_posts_per_page   = $_POST['purchased_postsperpage'];    

    $purchasedcodes = woo_vou_get_purchased_codes_by_product_id( $postid, $purchased_posts_per_page, $purchased_paged );

    //purchase codes table columns
    $purchasedcodes_columns = apply_filters( 'woo_vou_product_purchasedcodes_columns', array(
                                                'voucher_code'  => esc_html__( 'Voucher Code', 'woovoucher' ),
                                                'buyer_info'    => esc_html__( 'Buyer\'s Information', 'woovoucher' ),
                                                'order_info'    => esc_html__( 'Order Information', 'woovoucher' ),
                                            ), $postid );

    // If purchased codes is not empty
    if( !empty( $purchasedcodes ) &&  count( $purchasedcodes ) > 0 ) { 

    	// Looping on all codes for current page
        foreach ( $purchasedcodes as $key => $voucodes_data ) { 
            
            // Voucher order id
            $orderid        = $voucodes_data['order_id'];

            if( !empty( $purchasedcodes_columns ) ) {

                $html .= '<tr>';
                foreach ( $purchasedcodes_columns as $column_key => $column ) {
                    
                    $column_value = '';                    
                    switch( $column_key ) {
                        
                        case 'voucher_code' : // voucher code purchased
                            $column_value   = $voucodes_data['vou_codes'];
                            break;
                        case 'buyer_info' : // buyer's info who has purchased voucher code
                            $column_value = '<div id="buyer_voucher_'.$voucodes_data['voucode_id'].'">';
                            $buyer_info = $woo_vou_model->woo_vou_get_buyer_information( $orderid );
                            $column_value .= woo_vou_display_buyer_info_html( $buyer_info );
                            $column_value .= '<a class="woo-vou-show-buyer" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
                            $column_value .= '</div>';
                            break;
                        case 'order_info' : // voucher order info
                            $column_value = '<div id="order_voucher_'.$voucodes_data['voucode_id'].'">';
                            $column_value .= woo_vou_display_order_info_html( $orderid );
                            $column_value .= '<a class="woo-vou-show-order" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
                            $column_value .= '</div>';
                            break;
                    }

                    // Apply filter to allow changing it from 3rd party plugins
                    $column_value = apply_filters( 'woo_vou_product_purchasedcodes_column_value', $column_value, $voucodes_data, $postid );
                    $html .= '<td>'. $column_value .'</td>';
                }
                $html .= '</tr>';
            }
        }

        $response['html'] = $html;
    } else {

    	$response['norecfound'] = true;
    }

    echo json_encode( $response );
    exit;
}

/**
 * Get more used voucher information
 * 
 * Handles to getting voucher information for product meta popup 
 * of used voucher code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.3
 */
function woo_vou_load_more_used_voucode(){

	global $woo_vou_model, $woo_vou_voucher;

    $model 		= $woo_vou_model;
    $prefix 	= WOO_VOU_META_PREFIX;
    $html 		= '';
    $response 	= array();
    $postid 	= $_POST['used_post_id'];
    $used_paged = ( (int)$_POST['used_paged'] ) + 1;
    $used_posts_per_page = $_POST['used_postsperpage'];    

    $usedcodes = woo_vou_get_used_codes_by_product_id( $postid, $used_posts_per_page, $used_paged );

    //used codes table columns
	$usedcodes_columns	= apply_filters( 'woo_vou_product_usedcodes_columns', array(
													'voucher_code'	=> esc_html__( 'Voucher Code', 'woovoucher' ),
													'buyer_info'	=> esc_html__( 'Buyer\'s Information', 'woovoucher' ),
													'order_info'	=> esc_html__( 'Order Information', 'woovoucher' ),
													'redeem_info'		=> esc_html__( 'Redeem Information', 'woovoucher' )
												), $postid );

    if( !empty( $usedcodes ) &&  count( $usedcodes ) > 0 ) { 
        
        foreach ( $usedcodes as $key => $voucodes_data ) { 
            
            // voucher order id
            $orderid    = $voucodes_data['order_id'];
            
            // get user id
            $user_id    = $voucodes_data['redeem_by'];
            
            if( !empty( $usedcodes_columns ) ) {

                $html .= '<tr>';
                
                foreach ( $usedcodes_columns as $column_key => $column ) {
                    
                    $column_value = '';
                    
                    switch( $column_key ) {
                        case 'voucher_code' : // voucher code purchased
                            $column_value   = $voucodes_data['vou_codes'];
                            break;
                        case 'buyer_info' : // buyer's info who has used voucher code
                            $column_value = '<div id="buyer_voucher_'.$voucodes_data['voucode_id'].'">';
                            $buyer_info = $model->woo_vou_get_buyer_information( $orderid );
                            $column_value .= woo_vou_display_buyer_info_html( $buyer_info );
                            $column_value .= '<a class="woo-vou-show-buyer" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
                            $column_value .= '</div>';
                            break;
                        case 'order_info' : // voucher order info
                            $column_value = '<div id="order_voucher_'.$voucodes_data['voucode_id'].'">';
                            $column_value .= woo_vou_display_order_info_html( $orderid );
                            $column_value .= '<a class="woo-vou-show-order" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
                            $column_value .= '</div>';
                            break;
                        case 'redeem_info' :
                            $column_value = woo_vou_display_redeem_info_html( $voucodes_data['voucode_id'], $orderid, '' );
                            break;
                    }
                    
                    $column_value = apply_filters( 'woo_vou_product_usedcodes_column_value', $column_value, $voucodes_data, $postid );
                    
                    $html .= '<td>'. $column_value .'</td>';
                }
                $html .= '</tr>';
            }

            $response['html'] = $html;
        }
    } else {

    	$response['norecfound'] = true;
    }

    echo json_encode( $response );
    exit;
}

/**
 * Get more unused voucher information
 * 
 * Handles to getting voucher information for product meta popup 
 * of unused voucher code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.3
 */
function woo_vou_load_more_unused_voucode() {

    global $woo_vou_model, $woo_vou_voucher;

    $model                  = $woo_vou_model;
    $prefix                 = WOO_VOU_META_PREFIX;
    $html                   = '';
    $response               = array();
    $postid                 = $_POST['unused_post_id'];
    $unused_paged           = ( (int) $_POST['unused_paged'] ) + 1;
    $unused_posts_per_page  = $_POST['unused_postsperpage'];    

    $unusedcodes = woo_vou_get_unused_codes_by_product_id($postid, $unused_posts_per_page, $unused_paged);

    //used codes table columns
    $unusedcodes_columns = apply_filters('woo_vou_product_unusedcodes_columns', array(
        'voucher_code' => esc_html__('Voucher Code', 'woovoucher'),
        'buyer_info' => esc_html__('Buyer\'s Information', 'woovoucher'),
        'order_info' => esc_html__('Order Information', 'woovoucher')
            ), $postid);

    if (!empty($unusedcodes) && count($unusedcodes) > 0) {

        foreach ($unusedcodes as $key => $voucodes_data) {

            // voucher order id
            $orderid = $voucodes_data['order_id'];

            // get user id
            $user_id = $voucodes_data['redeem_by'];

            if (!empty($unusedcodes_columns)) {

                $html .= '<tr>';

                foreach ($unusedcodes_columns as $column_key => $column) {

                    $column_value = '';

                    switch ($column_key) {
                        case 'voucher_code' : // voucher code purchased
                            $column_value = $voucodes_data['vou_codes'];
                            break;
                        case 'buyer_info' : // buyer's info who has used voucher code
                            $column_value = '<div id="buyer_voucher_' . $voucodes_data['voucode_id'] . '">';
                            $buyer_info = $model->woo_vou_get_buyer_information($orderid);
                            $column_value .= woo_vou_display_buyer_info_html($buyer_info);
                            $column_value .= '<a class="woo-vou-show-buyer" data-voucherid="' . $voucodes_data['voucode_id'] . '">' . esc_html__('Show', 'woovoucher') . '</a>';
                            $column_value .= '</div>';
                            break;
                        case 'order_info' : // voucher order info
                            $column_value = '<div id="order_voucher_' . $voucodes_data['voucode_id'] . '">';
                            $column_value .= woo_vou_display_order_info_html($orderid);
                            $column_value .= '<a class="woo-vou-show-order" data-voucherid="' . $voucodes_data['voucode_id'] . '">' . esc_html__('Show', 'woovoucher') . '</a>';
                            $column_value .= '</div>';
                            break;
                    }

                    $column_value = apply_filters('woo_vou_product_unusedcodes_column_value', $column_value, $voucodes_data, $postid);

                    $html .= '<td>' . $column_value . '</td>';
                }
                $html .= '</tr>';
            }

            $response['html'] = $html;
        }
    } else {

        $response['norecfound'] = true;
    }

    echo json_encode($response);
    exit;
}