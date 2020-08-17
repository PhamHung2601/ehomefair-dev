<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Handles to update voucher details in order data
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_product_purchase($order_id) {

	global $woo_vou_model, $woo_vou_voucher;

    //Get Prefix
    $prefix = WOO_VOU_META_PREFIX;

    $changed = false;
    $voucherdata = $vouchermetadata = $recipient_order_meta = array();

    $order = wc_get_order($order_id);

    if( empty( $order ) ){
        return '';
    }

    //Get user data from order
    $userdata = $woo_vou_model->woo_vou_get_buyer_information($order_id);

    //Get buyers information
    $userfirstname = isset($userdata['first_name']) ? trim($userdata['first_name']) : '';
    $userlastname = isset($userdata['last_name']) ? trim($userdata['last_name']) : '';
    $useremail = isset($userdata['email']) ? $userdata['email'] : '';
    $buyername = str_replace(' ', '_', $userfirstname);

    $order_items = $order->get_items();
    $order_date = $woo_vou_model->woo_vou_get_order_date_from_order($order);

    if (is_array($order_items)) {

        // Check cart details
        foreach ($order_items as $item_id => $item) {

            //get product id
            $productid = $item['product_id'];

			$productdata =  wc_get_product( $productid );
			
            //get product quantity
            $productqty = apply_filters('woo_vou_order_item_qty', $item['qty'], $item);

            // Taking variation id
            $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';

            // If product is variable product take variation id else product id
            $data_id = (!empty($variation_id) ) ? $variation_id : $productid;

            //Get voucher code from item meta "Now we store voucher codes in item meta fields"
            $codes_item_meta = wc_get_order_item_meta($item_id, $prefix.'codes');

            if (empty($codes_item_meta)) {// If voucher data are not empty so code get executed once only
                
                //voucher codes
                $vou_codes = woo_vou_get_voucher_code($productid, $variation_id);

                //vendor user
                $vendor_user = get_post_meta($productid, $prefix.'vendor_user', true);

                //get vendor detail
                $vendor_detail = $woo_vou_model->woo_vou_get_vendor_detail($productid, $variation_id, $vendor_user );

                //using type of voucher
                $using_type = isset($vendor_detail['using_type']) ? $vendor_detail['using_type'] : '';

                $allow_voucher_flag = true;

                // if using type is one time and voucher code is empty or quantity is zero
                if (empty($using_type) && (empty($vou_codes) )) { 
                    $allow_voucher_flag = false;
                }

                //Check voucher enable or not
                $order_args = array(
                    'order_item' => $item,
                    'order' => $order
                );

                //check enable voucher & is downlable & total codes are not empty
                if ($woo_vou_voucher->woo_vou_check_enable_voucher($productid, $variation_id, $order_args ) && $allow_voucher_flag == true) {
						
					$start_date = ''; //format start date						
					
				
					//$is_variable  = $productdata->is_type('variable');
					
					$variable_exp_type = get_post_meta($variation_id, $prefix . 'variable_voucher_expiration_date_type', true);
					                    
                    
					$manual_expire_date = get_post_meta($productid, $prefix.'exp_date', true); // expiration date
					
					$variable_manual_expire_date = get_post_meta($variation_id, $prefix.'variable_voucher_expiration_end_date', true); // start date					
					if(!empty($variation_id) && !empty($variable_manual_expire_date)  ){
						$manual_expire_date = $variable_manual_expire_date;
					}
								
                    $exp_date = !empty($manual_expire_date) ? date('Y-m-d H:i:s', strtotime($manual_expire_date)) : ''; // format exp date
                    $disable_redeem_days = get_post_meta($productid, $prefix.'disable_redeem_day', true); // Disable redeem days
				   
                    if (empty($disable_redeem_days))
                        $disable_redeem_days = '';

                    $exp_type = get_post_meta($productid, $prefix . 'exp_type', true); //get expiration tpe
					$variable_exp_type = get_post_meta($variation_id, $prefix . 'variable_voucher_expiration_date_type', true);
					if(!empty($variation_id)  && !empty($variable_exp_type)  ){
						$exp_type  =  get_post_meta($variation_id, $prefix . 'variable_voucher_expiration_date_type', true);
					}
					if($exp_type == 'specific_date'){
						
						$start_date = get_post_meta($productid, $prefix.'start_date', true); // start date					
						
						$variable_start_date = get_post_meta($variation_id, $prefix.'variable_voucher_expiration_start_date', true); // start date	
						
						if(!empty($variation_id) && !empty($variable_start_date)  ){
							$start_date = $variable_start_date;
						}
						
						$start_date = !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : ''; // format start date
					}
					
                    $custom_days = $allcodes = ''; //custom days

                    if ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
                        //get days difference
                        $days_diff = get_post_meta($productid, $prefix.'days_diff', true);
						$variable_days_diff = get_post_meta($variation_id, $prefix.'variable_voucher_day_diff', true);
						if(!empty($variation_id)  && !empty($variable_days_diff) ){
							 $days_diff = $variable_days_diff;
						}
						
                        if ($days_diff == 'cust') {
                            
							$custom_days = get_post_meta($productid, $prefix . 'custom_days', true);
							$variable_custom_days = get_post_meta($variation_id, $prefix . 'variable_voucher_expiration_custom_day', true);
							if(!empty($variation_id)  && !empty($variable_custom_days)){
								$custom_days = $variable_custom_days;
							}
							
                            $custom_days = isset($custom_days) ? $custom_days : '';

                            if (!empty($custom_days)) {

                                $add_days = '+' . $custom_days . ' days';
                                $exp_date = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
                            } else {

                            	$exp_date = '';
                            }
                        } else {
                            $custom_days = $days_diff;
                            $add_days = '+' . $custom_days . ' days';
                            $exp_date = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
                        }

						$start_date = !empty($order_date) ? date('Y-m-d H:i:s', strtotime($order_date)) : ''; // start adding start date in voucher meta if based on purchase
						
                    } else if ($exp_type == 'based_on_gift_date') { //If expiry type based Recipient Gift Date

                        $recipient_giftdate = $order_date;
                        //get days difference
                        $days_diff = get_post_meta($productid, $prefix.'days_diff', true);
						$variable_days_diff = get_post_meta($variation_id, $prefix.'variable_voucher_day_diff', true);
						if(!empty($variation_id)  && !empty($variable_days_diff) ){
							 $days_diff = $variable_days_diff;
						}
						
                        // Get giftdate from order item meta
                        $recipient_giftdate_item_meta = wc_get_order_item_meta($item_id, $prefix.'recipient_giftdate');
                        if( !empty($recipient_giftdate_item_meta) && !empty($recipient_giftdate_item_meta['value']) ){
                            $recipient_giftdate = $recipient_giftdate_item_meta['value'];
                        }

                        if ($days_diff == 'cust') {
                            $custom_days = get_post_meta($productid, $prefix . 'custom_days', true);
							$variable_custom_days = get_post_meta($variation_id, $prefix . 'variable_voucher_expiration_custom_day', true);
							if(!empty($variation_id)  && !empty($variable_custom_days)){
								$custom_days = $variable_custom_days;
							}
							
                            $custom_days = isset($custom_days) ? $custom_days : '';

                            if (!empty($custom_days)) {
                                $add_days = '+' . $custom_days . ' days';
                                $exp_date = date('Y-m-d H:i:s', strtotime($recipient_giftdate . $add_days));
                            } else {

                                $exp_date = '';
                            }
                        } else {
                            $custom_days = $days_diff;
                            $add_days = '+' . $custom_days . ' days';
                            $exp_date = date('Y-m-d H:i:s', strtotime($recipient_giftdate . $add_days));
                        }

                        $start_date = !empty($recipient_giftdate) ? date('Y-m-d H:i:s', strtotime($recipient_giftdate)) : ''; // start adding start date in voucher meta if receipient gift date
                    } else if( ($exp_type == 'default')) { // If product meta is set to default
						
					   $exp_type = get_option('vou_exp_type'); //get expiration type 
						
						if($exp_type == 'specific_date') { //If expiry type specific date

							$start_date = get_option('vou_start_date'); // start date
							$start_date = !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : ''; // format start date
							$manual_expire_date = get_option('vou_exp_date'); // expiration date
							$exp_date = !empty($manual_expire_date) ? date('Y-m-d H:i:s', strtotime($manual_expire_date)) : ''; // format exp date

						} else if ($exp_type == 'based_on_purchase') { //If expiry type based in purchase

							//get days difference
							$days_diff = get_option('vou_days_diff');
	
							if ($days_diff == 'cust') {
								$custom_days = get_option('vou_custom_days');
								$custom_days = isset($custom_days) ? $custom_days : '';

								if (!empty($custom_days)) {
									$add_days = '+' . $custom_days . ' days';
									$exp_date = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
								} else {
									$exp_date = date('Y-m-d H:i:s', current_time('timestamp'));
								}
							} else {
								$custom_days = $days_diff;
								$add_days = '+' . $custom_days . ' days';
								$exp_date = date('Y-m-d H:i:s', strtotime($order_date . $add_days));
							}

                            $start_date = !empty($order_date) ? date('Y-m-d H:i:s', strtotime($order_date)) : ''; // start adding start date in voucher meta if based on purchase
						}
                    }

                    $vouchercodes = trim($vou_codes, ','); //voucher code
                    $salecode = !empty($vouchercodes) ? explode(',', $vouchercodes) : array(); //explode all voucher codes

                    // trim code
                    foreach ($salecode as $code_key => $code) {
                        $salecode[$code_key] = trim($code);
                    }

                    //if voucher using type is more than one time then generate voucher codes
                    if (!empty($using_type)) {

                        //if user buy more than 1 quantity of voucher
                        if (isset($productqty) && $productqty > 1) {
                            for ($i = 1; $i <= $productqty; $i++) {

                                $voucode = $code_prefix = '';

                                if ( !empty($salecode) ) {
                                	//make voucher code
	                                $randcode = array_rand($salecode);

	                                if (!empty($salecode[$randcode])) {
	                                    $code_prefix = $salecode[$randcode];
	                                }
                                }

                                $vou_argument = array(
                                    'buyername' => $buyername,
                                    'code_prefix' => $code_prefix,
                                    'order_id' => $order_id,
                                    'data_id' => $data_id,
                                    'item_id' => $item_id,
                                    'counter' => $i
                                );
                                $voucode = woo_vou_unlimited_voucher_code_pattern($vou_argument);
                                $allcodes .= $voucode . ', ';
                            }
                        } else {

                            $voucode = $code_prefix = '';

                            if ( !empty($salecode) ) {
	                            //make voucher code when user buy single quantity
	                            $randcode = array_rand($salecode);
	
	                            if (!empty($salecode[$randcode]) && trim($salecode[$randcode]) != '') {
	                                $code_prefix = trim($salecode[$randcode]);
	                            }
                            }

                            //voucher codes arguments for create unlinited voucher
                            $vou_argument = array(
                                'buyername' => $buyername,
                                'code_prefix' => $code_prefix,
                                'order_id' => $order_id,
                                'data_id' => $data_id,
                                'item_id' => $item_id
                            );

                            $voucode = woo_vou_unlimited_voucher_code_pattern($vou_argument);

                            $allcodes .= $voucode . ', ';
                        }
                    } else {

                        $loop_product_qty = apply_filters('woo_vou_one_time_voucher_qty', $productqty, $item);

                        for ($i = 0; $i < $loop_product_qty; $i++) {

                            //get first voucher code
                            $voucode = $salecode[$i];

                            //unset first voucher code to remove from all codes
                            unset($salecode[$i]);
                            $allcodes .= $voucode . ', ';
                        }

                        //after unsetting first code make one string for other codes
                        $lessvoucodes = implode(',', $salecode);
                        woo_vou_update_voucher_code($productid, $variation_id, $lessvoucodes);

                        //Reduce stock quantity when order created and voucher deducted
                        $woo_vou_model->woo_vou_update_product_stock($productid, $variation_id, $salecode);
                    }

                    $allcodes = apply_filters( 'woo_vou_order_item_codes', trim($allcodes, ', '), $item_id, $order_id, $item );

                    //add voucher codes item meta "Now we store voucher codes in item meta fields"
                    //And Remove "order_details" array from here
                    wc_add_order_item_meta($item_id, $prefix.'codes', $allcodes);

                    // Getting Voucher Delivery
                    $voucher_delivery        = get_post_meta($data_id, $prefix . 'voucher_delivery', true);
                    $global_voucher_delivery = get_option('vou_voucher_delivery_options'); // Getting voucher delivery option

                    // Checking the product is variation then set data into array with variation id
                    if(!empty($variation_id)){

                    	// Declare variables
                        $pdf_template_data = $voucher_delivery_data = $vendor_detail_data = array();

                        // If product id is already set in voucher meta data
                        if(isset($vouchermetadata[$productid])){

                            $pdf_template_data 		= $vouchermetadata[$productid]['pdf_template']; // Get pdf template
                            $voucher_delivery_data 	= $vouchermetadata[$productid]['voucher_delivery']; // Get voucher delivery
                            $vendor_detail_data 	= $vouchermetadata[$productid]['vendor_address']; // Get vendor address
                        }

                        $pdf_template_data[$variation_id] 		= $vendor_detail['pdf_template']; // Insert new value to pdf template array
                        $voucher_delivery_data[$variation_id] 	= ($voucher_delivery)? $voucher_delivery: ( !empty( $global_voucher_delivery ) ? $global_voucher_delivery : 'email' ); // Insert new value to voucher delivery array
                        $vendor_detail_data[$variation_id] 		= $vendor_detail['vendor_address']; // Insert new value to vendor address array
						
						 $start_date_data[$variation_id] = $start_date;
						 $exp_date_data[$variation_id] = $exp_date;
						
                    } else {

                        $pdf_template_data 		= $vendor_detail['pdf_template']; // Set pdf template
                        $voucher_delivery_data 	= ($voucher_delivery)? $voucher_delivery: ( !empty( $global_voucher_delivery ) ? $global_voucher_delivery : 'email' ); // Set voucher delivery
                        $vendor_detail_data 	= $vendor_detail['vendor_address']; // Set vendor address
						
						$start_date_data = $start_date;
						$exp_date_data = $exp_date;
                    }

                    //Append for voucher meta data into order
                    $productvoumetadata = array(
                        'user_email' 		=> $useremail,
                        'pdf_template' 		=> $pdf_template_data,
                        'vendor_logo' 		=> $vendor_detail['vendor_logo'],
                        'start_date' 		=> $start_date_data,
                        'exp_date' 			=> $exp_date_data,
                        'exp_type' 			=> $exp_type,
                        'custom_days' 		=> $custom_days,
                        'using_type' 		=> $using_type,
                        'voucher_delivery' 	=> $voucher_delivery_data,
                        'vendor_address' 	=> $vendor_detail_data,
                        'website_url' 		=> $vendor_detail['vendor_website'],
                        'redeem' 			=> $vendor_detail['how_to_use'],
                        'avail_locations' 	=> $vendor_detail['avail_locations']
                    );
					
                    $vouchermetadata[$productid] = apply_filters( 'woo_vou_meta_order_voucher_detail', $productvoumetadata, $order_id, $item_id, $productid );
                    
                    $vou_code_post_data = apply_filters( 'woo_vou_voucode_generation_detail', array(
                    	'productid' 		=> $productid,
                    	'vendor_user' 		=> $vendor_user,
                    	'sec_vendor_users' 	=> get_post_meta($productid, $prefix.'sec_vendor_users', true),
                    	'userfirstname' 	=> $userfirstname,
                    	'userlastname' 		=> $userlastname,
                    	'order_date' 		=> $order_date,
                    	'start_date' 		=> $start_date,
                    	'exp_date' 			=> $exp_date,
                    	'disable_redeem_days' => $disable_redeem_days,
                    	'item_id' 			=> $item_id,
                    	'data_id' 			=> $data_id
                    ), $order_id, $item_id, $productid );				
					
                    woo_vou_generate_posts_from_vou_codes($allcodes, $order_id, $vou_code_post_data); 
                }
				
                // Save voucher per pdf settings
                woo_vou_update_multiple_pdf_settings( $order_id, $productid );
            }
        }        
		
        if (!empty($vouchermetadata)) { // Check voucher meta data are not empty

        	$vouchermetadata = apply_filters( 'woo_vou_order_vou_metadata', $vouchermetadata, $order_id );

            //update voucher order details with all meta data
            update_post_meta($order_id, $prefix . 'meta_order_details', $vouchermetadata);
        }
    }

    // If coupon codes are present
    if ($order->get_used_coupons()) {
		
        $used_coupons = array();

        foreach ($order->get_used_coupons() as $coupon) {

            $_POST['voucode'] = $coupon;

            // check coupon code is valid or not
            $check_voucher_code = $woo_vou_voucher->woo_vou_check_voucher_code($coupon);
			

            // Get voucher code status
            $isSuccess = isset($check_voucher_code['success']) ? $check_voucher_code['success'] : 0;

            if (!empty($isSuccess)) { // If coupon code is valid
                // Redeem voucher code
                $redeemCode = $woo_vou_voucher->woo_vou_save_voucher_code($coupon, $order);
            }
        }
    }
}

function woo_vou_update_multiple_pdf_settings( $order_id, $product_id ) {
    
    $prefix = WOO_VOU_META_PREFIX;
    
    // get value from order
    $order_multiple_pdf = get_post_meta( $order_id, $prefix . 'multiple_pdf', true );
    if( empty( $order_multiple_pdf ) ) {
        $order_multiple_pdf = array();
    }
    
    // get value from product
    $multiple_pdf = get_post_meta( $product_id, $prefix . 'enable_multiple_pdf', true );        
    
    if( !empty( $multiple_pdf ) ) {
        $order_multiple_pdf[$product_id] = $multiple_pdf;
        update_post_meta( $order_id, $prefix . 'multiple_pdf', $order_multiple_pdf );
    } else {
        $multiple_pdf = get_option('multiple_pdf'); // get global settings            
        $order_multiple_pdf[$product_id] = $multiple_pdf;
        update_post_meta( $order_id, $prefix . 'multiple_pdf', $order_multiple_pdf );
    }
}

/**
 * Add custom email notification to woocommerce
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
function woo_vou_add_email_notification($email_actions) {

    $email_actions[] = 'woo_vou_vendor_sale_email'; // Add vendor sale email
    $email_actions[] = 'woo_vou_gift_email'; // Add gift email
    $email_actions[] = 'woo_vou_redeem_email'; // Add voucher redeem email

    return apply_filters('woo_vou_add_email_notification', $email_actions);
}

/**
 * Insert pdf voucher files
 * 
 * Handles to insert pdf voucher
 * files in database
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_insert_downloadable_files($order_id) {

	global $woo_vou_voucher, $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;
    $downloadable_files = array();
    $order = wc_get_order($order_id); //Get Order

    if (sizeof($order->get_items()) > 0) { //Get all items in order
        foreach ($order->get_items() as $item_id => $item) {

            $_product = $order->get_product_from_item($item); //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
            $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : ''; // Taking variation id

            if ($_product && $_product->exists()) { 

                $product_id = $woo_vou_model->woo_vou_get_item_productid_from_product($_product);

                $data_id = (!empty($variation_id) ) ? $variation_id : $product_id; // If product is variable product take variation id else product id

                //Check voucher enable or not
                $order_args = array(
                    'order_item' => $item,
                    'order' => $order
                );
                if ($woo_vou_voucher->woo_vou_check_enable_voucher($product_id, $variation_id, $order_args)) {//Check voucher is enabled or not
					
                    $downloadable_files = $woo_vou_voucher->woo_vou_get_vouchers_download_key($order_id, $data_id, $item_id, $item); //Get vouchers downlodable pdf files

                    foreach (array_keys($downloadable_files) as $download_id) {

                        //Insert pdf vouchers in downloadable table
                        wc_downloadable_file_permission($download_id, $data_id, $order);
                    }
                }
            }
        }
    }

    // Status update from pending to publish when voucher is get completed or processing
    $args = array(
        'post_status' => array('pending'),
        'meta_query' => array(
            array(
                'key' => $prefix.'order_id',
                'value' => $order_id,
            )
        )
    );

    // Get vouchers code of this order
    $purchased_vouchers = woo_vou_get_voucher_details($args);

    if (!empty($purchased_vouchers)) { // If not empty voucher codes
        //For all possible vouchers
        foreach ($purchased_vouchers as $voucher) {

            // Get voucher data
            $current_post = get_post($voucher['ID'], 'ARRAY_A');
            //Change voucher status
            $current_post['post_status'] = 'publish';
            //Update voucher post
            wp_update_post($current_post);
        }
    }
}

/**
 * Download Process
 *
 * Handles to product process
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_download_process($email, $order_key, $product_id, $user_id, $download_id, $order_id) {
	
	global $woo_vou_model, $woo_vou_voucher;

    if (!empty($_GET['item_id'])) {

    	// Get parameter
	    $woo_vou_screen     = isset($_GET['woo_vou_screen']) ? $_GET['woo_vou_screen'] : '';
	    $woo_vou_admin      = isset($_GET['woo_vou_admin']) ? $_GET['woo_vou_admin'] : '';
	    $woo_vou_pdf_action = isset($_GET['woo_vou_pdf_action']) ? $_GET['woo_vou_pdf_action'] : '';
	
	    //check voucher download permission
	    $vou_download_processing_mail   = get_option('vou_download_processing_mail');
	    $vou_download_gift_mail         = get_option('vou_download_gift_mail');
	    $vou_download_dashboard         = get_option('vou_download_dashboard');
	
	    /*
	    * If is not pdf template preview AND voucher view from backend order
	    * AND screen parameter is not set, download processing mail is disabled
	    * OR screen parameter is gift, download gift mail is disabled
	    * OR screen parameter is download, download dashboard is disabled
	    */
	    if ( empty($woo_vou_admin) && empty($woo_vou_pdf_action) && (
	     ( empty($woo_vou_screen) && !empty($vou_download_processing_mail) && $vou_download_processing_mail == 'no' )
	        || ( !empty($woo_vou_screen) && ($woo_vou_screen == 'gift') && !empty($vou_download_gift_mail) && ($vou_download_gift_mail == 'no') )
	        || ( !empty($woo_vou_screen) && ($woo_vou_screen == 'download') && !empty($vou_download_dashboard) && $vou_download_dashboard == 'no' ) ) ) {
	        
	        wp_die( 
	            '<h1>' . esc_html__( 'You have not permission to download voucher.', 'woovoucher' ) . '</h1>' .
	            '<p>' . esc_html__( 'Sorry, you are not allowed to download voucher code PDF.', 'woovoucher' ) . '</p>',
	            403
	        );
	    }

        $item_id = $_GET['item_id'];
        $woo_vou_voucher->woo_vou_generate_pdf_voucher($email, $product_id, $download_id, $order_id, $item_id); //Generate PDF

        // Added support for download pdf count
        $downlod_data = $woo_vou_model->woo_vou_get_download_data(array(
            'product_id' => $product_id,
            'order_key' => wc_clean($_GET['order']),
            'email' => sanitize_email(str_replace(' ', '+', $_GET['email'])),
            'download_id' => wc_clean(isset($_GET['key']) ? preg_replace('/\s+/', ' ', $_GET['key']) : '')
        ));

        $woo_vou_model->woo_vou_count_download($downlod_data);
        exit;
    }
}

/**
 * Allow admin access to vendor user
 *
 * Handles to allow admin access to vendor user
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_prevent_admin_access($prevent_access) {

    global $current_user, $woo_vou_vendor_role;

    //Get User roles
    $user_roles = isset($current_user->roles) ? $current_user->roles : array();
    $user_role = array_shift($user_roles);

    if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
        $prevent_access = false;
    }

    return apply_filters('woo_vou_prevent_admin_access', $prevent_access);
}

/**
 * Set Order As Global Variable
 * 
 * Handles to set order as global variable
 * when order links displayed in email
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_email_before_order_table($order) {

    global $vou_order;

    //Create global varible for order
    $vou_order = woo_vou_get_order_id($order);
}

/**
 * Set Order Product As Global Variable
 * 
 * Handles to set order product as global variable
 * when complete order mail fired or Order Details page is at front side
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6
 */
function woo_vou_order_item_product($product, $item) {

    global $woo_vou_order_item;

    $woo_vou_order_item = $item; // Making global of order product item

    return $product;
}

/**
 * Display Recipient HTML
 * 
 * Handles to display the Recipient HTML after/before add to cart button
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.0
 */
function woo_vou_after_before_add_to_cart_button() {

    do_action('woo_vou_product_recipient_fields');

}

/**
 * add to cart in item data
 * 
 * Handles to add to cart in item data
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id) {

	global $woo_vou_model;

    $data_id = !empty($variation_id) ? $variation_id : $product_id;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    // Get all Recipient Columns
    $recipient_columns 	= woo_vou_voucher_recipient_details();

    foreach( $recipient_columns as $recipient_key => $recipient_val ) {

    	//If recipient column is set in POST
    	if (isset($_POST[$prefix.$recipient_key]) && is_array( $_POST[$prefix.$recipient_key] ) 
	    	&& array_key_exists( $data_id, $_POST[$prefix.$recipient_key] ) && !empty( $_POST[$prefix.$recipient_key][$data_id] )) {

	    	if( $recipient_key == 'recipient_giftdate' ) {

	    		$recipient_data_val = strtotime($_POST[$prefix.'recipient_giftdate'][$data_id]);
	    	} else {

	    		$recipient_data_val = $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix.$recipient_key][$data_id]);
	    	}
	        $cart_item_data[$prefix.$recipient_key] = $recipient_data_val;
	        unset( $_POST[$prefix.$recipient_key] );
	    }
    }

    if (isset($_POST[$prefix.'pdf_template_selection']) && is_array( $_POST[$prefix.'pdf_template_selection'] ) 
        && array_key_exists( $data_id, $_POST[$prefix.'pdf_template_selection'] ) && !empty( $_POST[$prefix.'pdf_template_selection'][$data_id] )) {//If pdf template is set

        $cart_item_data[$prefix.'pdf_template_selection'] = $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix.'pdf_template_selection'][$data_id]);
        unset( $_POST[$prefix.'pdf_template_selection'] );
    }

    if (isset($_POST[$prefix.'delivery_method']) && is_array( $_POST[$prefix.'delivery_method'] ) 
        && array_key_exists( $data_id, $_POST[$prefix.'delivery_method'] ) && !empty( $_POST[$prefix.'delivery_method'][$data_id] )) {

    	$cart_item_data[$prefix.'delivery_method'] = $_POST[$prefix.'delivery_method'][$data_id];
    	unset( $_POST[$prefix.'delivery_method'] );
    }

    return $cart_item_data;
}

/**
 * add to cart in item data from session
 * 
 * Handles to add to cart in item data from session
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_get_cart_item_from_session($cart_item, $values) {

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    // Get all Recipient Columns
    $recipient_columns 	= woo_vou_voucher_recipient_details();

    foreach( $recipient_columns as $recipient_key => $recipient_val ) {

    	if (!empty($values[$prefix.$recipient_key])) {//Recipient Name
	        $cart_item[$prefix.$recipient_key] = $values[$prefix.$recipient_key];
	    }
    }

    if (!empty($values[$prefix.'pdf_template_selection'])) {//PDF Template Selection
        $cart_item[$prefix.'pdf_template_selection'] = $values[$prefix.'pdf_template_selection'];
    }

    if (!empty($values[$prefix.'delivery_method'])) {//PDF Delivery Method
        $cart_item[$prefix.'delivery_method'] = $values[$prefix.'delivery_method'];
    }

    return $cart_item;
}

/**
 * Get to cart in item data to display in cart page
 * 
 * Handles to get to cart in item data to display in cart page
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_woocommerce_get_item_data($data, $item) {

	global $woo_vou_model, $woo_vou_voucher;

	// Get date format from global setting
	$date_format = get_option( 'date_format' );

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    //Get Product ID
    $product_id = isset($item['product_id']) ? $item['product_id'] : '';

    //Get product recipient meta setting
    $recipient_data = $woo_vou_model->woo_vou_get_product_recipient_meta($product_id);

    // Get all Recipient Columns
    $recipient_columns 	= woo_vou_voucher_recipient_details();

    foreach( $recipient_columns as $recipient_key => $recipient_val ) {

    	// Recipient column label
    	${$recipient_key.'_label'} 	= $recipient_data[$recipient_key.'_label'];

    	if (!empty($item[$prefix.$recipient_key])) {

    		$recipient_col_val = $item[$prefix.$recipient_key];

	    	if( $recipient_key == 'recipient_giftdate' ) {
	
	    		$recipient_col_val = date( $date_format, $item[$prefix.$recipient_key] );
	    	}

	        $data[] = array(
	            'name' => ${$recipient_key.'_label'},
	            'display' => $recipient_col_val,
	            'hidden' => false,
	            'value' => ''
	        );
	    }
    }

    //pdf template selection label
    $pdf_template_selection_label = $recipient_data['pdf_template_selection_label'];

    //recipient delivery method label
    $recipient_delivery_label = $recipient_data['recipient_delivery_label'];

    if (!empty($item[$prefix.'delivery_method'])) {

        $data[] = array(
            'name' => $recipient_delivery_label,
            'display' => $recipient_data['recipient_delivery_method']['label_'.$item[$prefix.'delivery_method']],
            'hidden' => false,
            'value' => ''
        );
    }

    if (!empty($item[$prefix.'pdf_template_selection'])) {

        $data[] = array(
            'name' => $pdf_template_selection_label,
            'display' => $item[$prefix.'pdf_template_selection'],
            'hidden' => true,
            'value' => ''
        );

        // enable display
        $enable_template_display = woo_vou_enable_template_display_features();

        if ($enable_template_display) { // if enabling the display template selection
            // pdf template preview image
            $pdf_template_preview_img = wp_get_attachment_url(get_post_thumbnail_id($item[$prefix.'pdf_template_selection']));

            if (empty($pdf_template_preview_img)) { // if preview image not available
                $pdf_template_preview_img = WOO_VOU_IMG_URL.'/no-preview.png';
            }

            $pdf_template_preview_img_title = get_the_title($item[$prefix.'pdf_template_selection']);

            $data[] = array(
                'name' => $pdf_template_selection_label,
                'display' => '<img class="woo-vou-variation-pdf-template-img" src="' . esc_url($pdf_template_preview_img) . '" title="' . $pdf_template_preview_img_title . '">',
                'hidden' => false,
                'value' => ''
            );
        }
    }

    return $data;
}

/**
 * add cart item to the order.
 * 
 * Handles to add cart item to the order.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_add_order_item_meta($item, $cart_item_key, $values, $order) {

	global $woo_vou_model, $woo_vou_voucher;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    //Initilize recipients labels
    $woo_vou_recipient_labels = array();

    //Get product ID
    $_product_id = isset($values['variation_id']) && !empty($values['variation_id']) ? $values['variation_id'] : $values['product_id'];

    $recipient_labels = $woo_vou_model->woo_vou_get_product_recipient_meta($values['product_id']);

    // Get voucher price 
    $voucher_price = woo_vou_get_voucher_price($cart_item_key, $_product_id, $values);

    // Get all Recipient Columns
    $recipient_columns 	= woo_vou_voucher_recipient_details();

    // Looping on all recipient columns
    foreach( $recipient_columns as $recipient_key => $recipient_val ) {

    	// Add recipient column field
    	if ( !empty( $values[$prefix.$recipient_key] ) ) {

    		$column_val = $values[$prefix.$recipient_key];
    		if( $recipient_key == 'recipient_giftdate' ) {

    			$column_val = date( 'd-m-Y', $values[$prefix . 'recipient_giftdate'] );
    		}

	        $item->add_meta_data( $prefix.$recipient_key, array(
	            'label' => $recipient_labels[$recipient_key.'_label'],
	            'value' => $column_val
	        ), true );

	        $item->add_meta_data( $recipient_labels[$recipient_key.'_label'], $column_val, true );
	    }
    }
	
	if (!empty($voucher_price)) { // Add voucher price in order item meta

        $item->add_meta_data( $prefix.'voucher_price', $voucher_price, true );
    }

	if (!empty($values[$prefix . 'delivery_method'])) {//Add recipient name field

		$item->add_meta_data( $prefix.'delivery_method', array(
            'label' => $recipient_labels['recipient_delivery_label'],
            'value' => $values[$prefix . 'delivery_method']
        ), true );

        $item->add_meta_data( $recipient_labels['recipient_delivery_label'], $recipient_labels['recipient_delivery_method']['label_'.$values[$prefix . 'delivery_method']], true );
	}

    if (!empty($values[$prefix . 'pdf_template_selection'])) {//Add pdf template selection field

        $item->add_meta_data( $prefix.'pdf_template_selection', array(
            'label' => $recipient_labels['pdf_template_selection_label'],
            'value' => $values[$prefix . 'pdf_template_selection']
        ), true );

        // check if template display is anable or not
        $enable_display_template = woo_vou_enable_template_display_features();

        if ($enable_display_template) { // if enable template preview image display
            //pdf template preview image
            $pdf_template_preview_img = wp_get_attachment_url(get_post_thumbnail_id($values[$prefix . 'pdf_template_selection']));

            if (empty($pdf_template_preview_img)) {
                $pdf_template_preview_img = WOO_VOU_IMG_URL . '/no-preview.png';
            }

            $pdf_template_preview_img_title = get_the_title($values[$prefix . 'pdf_template_selection']);

            $item->add_meta_data( $recipient_labels['pdf_template_selection_label'], $pdf_template_preview_img_title, true );
        }
    }
}

/**
 * Hide Recipient Itemmeta
 * 
 * Handle to hide recipient itemmeta
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_hide_recipient_itemmeta($item_meta = array()) {

    $prefix = WOO_VOU_META_PREFIX;

    $item_meta = array_merge( $item_meta, array( 
    								$prefix . 'recipient_name',
    								$prefix . 'recipient_email',
    								$prefix . 'recipient_message',
    								$prefix . 'recipient_giftdate',
    								$prefix . 'recipient_gift_method',
    								$prefix . 'pdf_template_selection',
    								$prefix . 'voucher_price',
    								$prefix . 'codes',
    								$prefix . 'partial_redeem',
    								$prefix . 'unlimited_redeem',
    								$prefix . 'recipient_gift_email_send_item'
    							) );

    return $item_meta;
}

/**
 * Handles the functionality to attach the voucher pdf in mail
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_attach_voucher_to_email($attachments, $status, $order, $order_item_id = false ) {
	
	// Global variables
    global $post, $woo_vou_voucher, $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    // Declare variables
    $order_id = '';

    // Taking status array
    $vou_status = apply_filters('woo_vou_add_email_attachment_order_status', array('customer_processing_order', 'customer_completed_order', 'customer_invoice'));

    // Taking order status array
    $vou_order_status = array('wc-completed', 'completed'); // 'completed' status added for adding WC 3.0 compatibility
    // If woocommerce version is less than 3.0.0
    if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {

        //get product id from prduct data
        $order_status = !empty($order->post_status) ? $order->post_status : ''; // Order status
        $order_id = !empty($order->id) ? $order->id : ''; // Taking order id
    } else { // If version is greater than 3.0.0
		
        if (is_array($order)) { // If order is an array

            if (isset($order['order_id'])) { // If order_id is set in order array ( Happens when order is created through REST )

                $order_id = $order['order_id'];
            } else if(is_object($post)) {

                $order_id = $post->ID;
            }

            if($order_id){
	            $_order = wc_get_order($order_id);
	            $order_status = $_order->get_status();
            }
            
        } else if (is_object($order) && $order instanceof WC_Order ) {

            $order_status = $order->get_status();
            $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : ''; // Taking order id
        }
    }

    if(!$order_id) {
    	return $attachments;
    }

    //Get and check if order's voucher data need hide
    if( get_post_meta($order_id, $prefix .'order_hide_voucher_data', true) ) return $attachments;

    $vou_attach_processing_mail = get_option('vou_attach_processing_mail'); // Getting voucher attach option
    $grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment'); // Get option to access downloads in processing email
    if ( !empty($order) && ( ( in_array($status, $vou_status) && in_array($order_status, $vou_order_status) ) 
         || ($status == 'customer_processing_order' && $grant_access_after_payment == 'yes' && ( $order_status != 'wc-on-hold' && $order_status != 'on-hold' ) ) ) ) { // check $order_status != 'on-hold' to fix the issue with woocommerce germanize plugin
			
		
        $vou_attachments = array();
        $cart_details = wc_get_order($order_id);
        $order_items = $cart_details->get_items();

        if (!empty($order_items)) {//not empty items
            //foreach items
            foreach ($order_items as $item_id => $download_data) {
                
                // if we are re-sending email then attachment only send for voucher that are purchased.
                if( $order_item_id !== false && $order_item_id != $item_id ) {
                    continue;
                }
                
                $product_id = !empty($download_data['product_id']) ? $download_data['product_id'] : '';
                $variation_id = !empty($download_data['variation_id']) ? $download_data['variation_id'] : '';

                //get product quantity
                $productqty = apply_filters('woo_vou_order_item_qty', $download_data['qty'], $download_data);

                //Get data id vriation id or product id
                $data_id = !empty($variation_id) ? $variation_id : $product_id;

                //Check voucher enable or not
                $order_args = array(
                    'order_item' => $download_data,
                    'order' => $cart_details
                );
                $enable_voucher = $woo_vou_voucher->woo_vou_check_enable_voucher($product_id, $variation_id, $order_args );

                // Declare Voucher Delivery
                $vou_voucher_delivery_type = 'email';

                // Getting the order meta data
                $order_all_data = $woo_vou_model->woo_vou_get_all_ordered_data( $order_id );
                $vou_using_type = isset( $order_all_data[$product_id]['using_type'] ) ? $order_all_data[$product_id]['using_type'] : '';

                // If this variation then get it's product id
                $variation_pro      = wc_get_product( $data_id );
                $parent_product_id	= $woo_vou_model->woo_vou_get_item_productid_from_product( $variation_pro );
                if( !empty($variation_id) && isset($order_all_data[$parent_product_id]['voucher_delivery']) && is_array($order_all_data[$parent_product_id]['voucher_delivery']) ){

                    $vou_voucher_delivery_type 	= $order_all_data[$parent_product_id]['voucher_delivery'][$variation_id];
                } elseif( isset($order_all_data[$product_id]['voucher_delivery']) ) {

                    $vou_voucher_delivery_type 	= $order_all_data[$product_id]['voucher_delivery'];
                }

                // Get user selected voucher delivery
                // This will override voucher delivery selected by admin
                $user_selected_delivery_type = $download_data->get_meta( $prefix.'delivery_method', true );
                if( !empty( $user_selected_delivery_type ) && is_array( $user_selected_delivery_type )
                	&& !empty( $user_selected_delivery_type['value'] ) ) {

                	$vou_voucher_delivery_type = $user_selected_delivery_type['value'];
                }

                if( ($enable_voucher) && ($vou_voucher_delivery_type == 'email') ) {

                    // Get mutiple pdf option from order meta
                    $multiple_pdf = !empty($order_id) ? get_post_meta($order_id, $prefix.'multiple_pdf', true) : '';
                    // Old order doesn't save data as array, so need to check for array.
                    if( is_array( $multiple_pdf ) ) {
                        $multiple_pdf = !empty( $multiple_pdf[$product_id] ) ? $multiple_pdf[$product_id] : '';
                    }
                    
                    $orderdvoucodes = array();

                    if ($multiple_pdf == 'yes') {
                        $orderdvoucodes = woo_vou_get_multi_voucher($order_id, $data_id, $item_id);
                    } else {
                        $orderdvoucodes['woo_vou_pdf_1'] = '';
                    }

					
                    // If order voucher codes are not empty
                    if (!empty($orderdvoucodes)) {

                        foreach ($orderdvoucodes as $orderdvoucode_key => $orderdvoucode_val) {

                            if (!empty($orderdvoucode_key)) {

                                $attach_pdf_file_name = get_option('attach_pdf_name');

                                // Apply filter to allow 3rd party people to change it
								$date_format = apply_filters( 'woo_vou_voucher_date_format', 'Y-m-d' );
                                if( !empty($attach_pdf_file_name) ){
                                    
                                    $product_title = get_the_title($product_id);
                                    $product_title = str_replace(" ", "-", $product_title);

                                    // Find and replace shortcodes
                                    $find					= array( "{current_date}", "{product_title}" );
                                    $replace				= array( date($date_format), $product_title );
                                    $attach_pdf_file_name 	= str_replace( $find, $replace, $attach_pdf_file_name );
                                } else {

                                    $attach_pdf_file_name = 'woo-voucher-';
                                }

                                //Get Pdf Key
                                $pdf_vou_key = $orderdvoucode_key;

                                // Add filter for PDF attach name
                                $pdf_file_args = array(
                                    'order_id'      => $order_id,
                                    'product_id'    => $product_id,
                                    'item_id'       => $item_id,
                                    'pdf_vou_key'   => $pdf_vou_key,
                                );
                                $attach_pdf_file_name = apply_filters( 'woo_vou_attach_pdf_file_name', $attach_pdf_file_name, $pdf_file_args );

                                // Remove forward slash from name
                                $attach_pdf_file_name = str_replace( '/', '', $attach_pdf_file_name );

                                // Voucher pdf path and voucher name
                                $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $attach_pdf_file_name . $order_id . '-' . $data_id . '-' . $item_id; // Voucher pdf path

                                // Replacing voucher pdf name with given value
                                $orderdvoucode_key = str_replace('woo_vou_pdf_', '', $orderdvoucode_key);

                                //if user buy more than 1 quantity of voucher
                                if (isset($productqty) && $productqty > 1) {
                                    $vou_pdf_path .= '-' . $orderdvoucode_key;
                                }

                                //if voucher using type is more than one time then generate voucher codes
                                if (!empty($vou_using_type)) {

                                    // Get vouche code postfix from option
                                    $vou_code_postfix = get_option('vou_code_postfix');

                                    if( isset($productqty) && !empty($vou_code_postfix) ){
                                        $vou_code_postfix = (int)$vou_code_postfix - ( $productqty - $orderdvoucode_key ) - 1;
                                        $vou_pdf_path .= '-'.$vou_code_postfix;
                                    }
                                }
                                // set PDF path with extension
                                $vou_pdf_name = $vou_pdf_path . '.pdf';

                                // If voucher pdf does not exist in folder
                                if (!file_exists($vou_pdf_name)) {

                                    $pdf_args = array(
                                        'pdf_vou_key' => $pdf_vou_key,
                                        'pdf_name' => $vou_pdf_path,
                                        'save_file' => true
                                    );

                                    //Generatin pdf
                                    woo_vou_process_product_pdf($data_id, $order_id, $item_id, $orderdvoucodes, $pdf_args);
                                }
								
								$recipient_details = $woo_vou_model->woo_vou_get_recipient_data($download_data);
								$allow_recipient_to_get_voucher_info = get_option('vou_allow_recipient_to_get_voucher_info');
								
                                // If voucher pdf exist in folder
                                if ( file_exists($vou_pdf_name) && ( !empty( $vou_attach_processing_mail ) && $vou_attach_processing_mail == 'yes' ) ) {
																		
									if( ( empty($allow_recipient_to_get_voucher_info) || $allow_recipient_to_get_voucher_info == 'no' ) || ( $allow_recipient_to_get_voucher_info == 'yes' && empty($recipient_details) ) ){ // will only attach the pdf if the only receipient  receive voucher setting is off and not the gift voucher
										
										$attachments[] = apply_filters('woo_vou_email_attachments', $vou_pdf_name, $order_id, $item_id, $download_data); // Adding the voucher pdf in attachment array
									}
								
								}
                            }
                        }
                    } // End of orderdvoucodes
                }
            }
        } // End of order item
    }

    return $attachments;
}

/**
 * Check voucher code using qrcode and barcode
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0.3
 */
function woo_vou_check_qrcode() {

    if (isset($_GET['woo_vou_code']) && !empty($_GET['woo_vou_code'])) {

        // Add action to add check voucher code from
        do_action('woo_vou_check_qrcode_content');
    }
}

/**
 * Add Voucher When Add Order Manually
 * 
 * Haldle to add voucher codes
 * when add order manually from backend
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.2.1
 */
function woo_vou_process_shop_order_manually($order_id) {

    if (!empty($_POST['order_item_id'])) {//If order item are not empty
        //Process voucher code functionality
        woo_vou_product_purchase($order_id);
    }
}

/**
 * Hide recipient variation from product name field
 * 
 * Handle to hide recipient variation from product name field
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
function woo_vou_hide_recipients_item_variations($product_variations = array(), $product_item_meta = array()) {
	
	global $woo_vou_model, $woo_vou_voucher;

    $prefix = WOO_VOU_META_PREFIX;

    $recipient_string = '';

    //Get product ID
    $product_id = isset($product_item_meta['_product_id']) ? $product_item_meta['_product_id'] : '';

    //Get product recipient lables
    $product_recipient_lables = $woo_vou_model->woo_vou_get_product_recipient_meta($product_id);

    if (isset($product_item_meta[$prefix . 'recipient_name']) && !empty($product_item_meta[$prefix . 'recipient_name'])) {

        $recipient_name_label = isset($product_item_meta[$prefix . 'recipient_name']['label']) ? $product_item_meta[$prefix . 'recipient_name']['label'] : $product_recipient_lables['recipient_name_label'];
        if (isset($product_variations[$recipient_name_label])) {
            unset($product_variations[$recipient_name_label]);
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_email']) && !empty($product_item_meta[$prefix . 'recipient_email'])) {

        $recipient_email_label = isset($product_item_meta[$prefix . 'recipient_email']['label']) ? $product_item_meta[$prefix . 'recipient_email']['label'] : $product_recipient_lables['recipient_email_label'];
        if (isset($product_variations[$recipient_email_label])) {
            unset($product_variations[$recipient_email_label]);
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_message']) && !empty($product_item_meta[$prefix . 'recipient_message'])) {

        $recipient_msg_label = isset($product_item_meta[$prefix . 'recipient_message']['label']) ? $product_item_meta[$prefix . 'recipient_message']['label'] : $product_recipient_lables['recipient_message_label'];
        if (isset($product_variations[$recipient_msg_label])) {
            unset($product_variations[$recipient_msg_label]);
        }
    }

    if (isset($product_item_meta[$prefix . 'pdf_template_selection']) && !empty($product_item_meta[$prefix . 'pdf_template_selection'])) {

        $pdf_temp_selection_label = isset($product_item_meta[$prefix . 'pdf_template_selection']['label']) ? $product_item_meta[$prefix . 'pdf_template_selection']['label'] : $product_recipient_lables['pdf_template_selection_label'];
        if (isset($product_variations[$pdf_temp_selection_label])) {
            unset($product_variations[$pdf_temp_selection_label]);
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_giftdate']) && !empty($product_item_meta[$prefix . 'recipient_giftdate'])) {

        $recipient_giftdate_selection_label = isset($product_item_meta[$prefix . 'recipient_giftdate']['label']) ? $product_item_meta[$prefix . 'recipient_giftdate']['label'] : $product_recipient_lables['recipient_giftdate_label'];
        if (isset($product_variations[$recipient_giftdate_selection_label])) {
            unset($product_variations[$recipient_giftdate_selection_label]);
        }
    }

    return $product_variations;
}

/**
 * Set Global Item ID For Voucher Key Generater
 * 
 * Handle to Set global item id for voucher key generater
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.2
 */
function woo_vou_set_global_item_id($product, $item) {

    global $woo_vou_item_id;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    $product_item_meta = isset($item['item_meta']) ? $item['item_meta'] : array();

    $voucher_codes = isset($product_item_meta[$prefix . 'codes']) ? $product_item_meta[$prefix . 'codes'] : '';

    if (!empty($voucher_codes)) {

        $item_id = $item->get_id();

        //Get voucher codes
        $codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

        if ($codes == $voucher_codes) {//If voucher code matches
            $woo_vou_item_id = $item_id;
        }
    }

    return $product;
}

/**
 * Add downlodable files for woocommerce version >= 3.0
 * 
 * Add Item Id In generate pdf download URL	 
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
function woo_vou_get_item_pdf_downloads($files, $item, $abs_order) {

	// Define global variables to use them in our function
    global $woo_vou_item_id, $woo_vou_voucher, $woo_vou_model;

    // Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    //Get and check if order's voucher data need hide
    $order_hide_vou = ( !empty( $abs_order ) && is_object( $abs_order ) ) ? get_post_meta( $abs_order->get_id(), $prefix .'order_hide_voucher_data', true ) : '';
    
    if( $order_hide_vou ) return array();

    // for woocommere version >= 3.0. If older version then 3.0, not call this
    if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") != -1) {

        $product = $item->get_product();

        if (!( $product && $abs_order && $product->is_downloadable() && $abs_order->is_download_permitted() ))
            return $files;

        $product_id 	= $woo_vou_model->woo_vou_get_item_productid_from_product( $product ); // Get product id
        $woo_vou_enable = get_post_meta( $product_id, $prefix.'enable', true ); // Check if Enable Vouchers plugin is ticked
        
        $woo_vou_enable = apply_filters('woo_vou_check_enable_voucher_get_download_item', $woo_vou_enable, $product_id);

        // If enable voucher option is not ticked
        if ( empty( $woo_vou_enable ) || $woo_vou_enable == 'no' ) {

        	return $files;
        }

        // Taking product/variation id
        $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];

        //Get vouchers download files
        $pdf_downloadable_files = $woo_vou_voucher->woo_vou_get_vouchers_download_key($abs_order->get_id(), $variation_id, $woo_vou_item_id, $item);

        if (!empty($pdf_downloadable_files)) {

            foreach ($pdf_downloadable_files as $pdf_key => $pdf_file_array) {

            	$vou_codes 			= $item->get_meta($prefix.'codes', true); // Get voucher codes from meta
            	$vou_code 			= explode(',', $vou_codes); // Explode voucher code in case it is having multiple voucher codes
				$vou_code_id 		= woo_vou_get_voucodeid_from_voucode($vou_code[0]); // Get voucher code id
				$vou_expiry_date 	= ''; // Declare variable
				if(!empty($vou_code_id)){

					// Get voucher expiry date
					$vou_expiry_date = get_post_meta($vou_code_id, $prefix.'exp_date', true);
				}
            	
                // Add download url to voucher downlodable files
                $pdf_downloadable_files[$pdf_key]['download_url'] = $item->get_item_download_url($pdf_key);
                $pdf_downloadable_files[$pdf_key]['id'] = $pdf_key;
                $pdf_downloadable_files[$pdf_key]['access_expires'] = $vou_expiry_date;
                $pdf_downloadable_files[$pdf_key]['downloads_remaining'] = '';

                // Merge downlodable file to files
                $files = array_merge($files, array($pdf_key => $pdf_downloadable_files[$pdf_key]));
            }
        }
    }
	
    // Add item id in download pdf url
    if (!empty($files)) { //If files not empty
        foreach ($files as $file_key => $file_data) {

            //Check key is for pdf voucher
            $check_key = strpos($file_key, 'woo_vou_pdf_');
			
            if ($check_key !== false) {
		
                //Get download URL
                $download_url = isset($files[$file_key]['download_url']) ? $files[$file_key]['download_url'] : '';

                //Add item id in download URL
                $download_url = add_query_arg(array('item_id' => $woo_vou_item_id), $download_url);

                //Store download URL agaiin
                $files[$file_key]['download_url'] = $download_url;

                // add filter to remove voucher download link
                $files = apply_filters('woo_vou_remove_download_link', $files, $file_key, $woo_vou_item_id);
            }
        }
    }

    return $files;
}

/**
 * Adding Hooks
 * 
 * Adding proper hoocks for the discount codes
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.1
 */
function woo_vou_my_pdf_vouchers_download_link($downloads = array()) {

	global $woo_vou_model, $woo_vou_voucher;
	
    //get prefix
    $prefix = WOO_VOU_META_PREFIX;
	$vou_download_dashboard = get_option('vou_download_dashboard');
    $grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment'); // Get option to access downloads in processing order

    if ( is_user_logged_in() && ( $vou_download_dashboard == 'yes') ) {//If user is logged in AND download enable for customer dashboard
        //Get user ID
        $user_id = get_current_user_id();

        //Get User Order Arguments
        $args = array(
            'numberposts' => -1,
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'post_type' => WOO_VOU_MAIN_SHOP_POST_TYPE,
            'post_status' => array('wc-completed'),
            'meta_query' => array(
                array(
                    'key' => $prefix . 'meta_order_details',
                    'compare' => 'EXISTS',
                )
            )
        );
        // Get the processing & completed orders
        if( $grant_access_after_payment == 'yes' ){
            $args['post_status'] = array('wc-processing', 'wc-completed');
        }

        //user orders
        $user_orders = get_posts($args);

        if (!empty($user_orders)) {//If orders are not empty
            foreach ($user_orders as $user_order) {

                //Get order ID
                $order_id = isset($user_order->ID) ? $user_order->ID : '';

                if (!empty($order_id)) {//Order it not empty
                    global $vou_order;

                    //Set global order ID
                    $vou_order = $order_id;

                    //Get cart details
                    $cart_details = wc_get_order($order_id);
                    $order_items = $cart_details->get_items();
                    $order_date = $woo_vou_model->woo_vou_get_order_date_from_order($cart_details); // Get order date
                    $order_date = date('F j, Y', strtotime($order_date));

                    if (!empty($order_items)) {// Check cart details are not empty
                        foreach ($order_items as $item_id => $product_data) {

                            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
                            $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);

                            if (!$_product) {//If product deleted
                                $download_file_data = array();
                            } else {
                                $download_file_data = $woo_vou_model->woo_vou_get_item_downloads_from_order($cart_details, $product_data);
                            }

                            //Get voucher codes
                            $codes = wc_get_order_item_meta($item_id, $prefix.'codes');

                            if (!empty($download_file_data) && !empty($codes)) {//If download exist and code is not empty
                                foreach ($download_file_data as $key => $download_file) {
                                    //check download key is voucher key or not
                                    $check_key = strpos($key, 'woo_vou_pdf_');

                                    //get voucher number
                                    $voucher_number = str_replace('woo_vou_pdf_', '', $key);

                                    if (empty($voucher_number)) {//If empty voucher number
                                        $voucher_number = 1;
                                    }

                                    if (!empty($download_file) && $check_key !== false) {

                                        //Get download URL
                                        $download_url = $download_file['download_url'];

                                        //add arguments array
                                        $add_arguments = array('item_id' => $item_id, 'woo_vou_screen' => 'download');

                                        //PDF Download URL
                                        $download_url = add_query_arg($add_arguments, $download_url);

                                        // To make compatible with previous versions of 3.0.0
                                        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
                                            //Get product ID
                                            $product_id = isset($_product->post->ID) ? $_product->post->ID : '';
                                            //get product name
                                            $product_name = isset($_product->post->post_title) ? $_product->post->post_title : '';
                                        } else {
                                            //Get product ID
                                            $product_id = $_product->get_id();
                                            //get product name
                                            $product_name = $_product->get_title();
                                        }

                                        $vou_codes 			= $codes; // Get voucher codes from meta
						            	$vou_code 			= explode(',', $vou_codes); // Explode voucher code in case it is having multiple voucher codes
										$vou_code_id 		= woo_vou_get_voucodeid_from_voucode($vou_code[0]); // Get voucher code id
										$vou_expiry_date 	= ''; // Declare variable
										if(!empty($vou_code_id)){
						
											// Get voucher expiry date
											$vou_expiry_date = get_post_meta($vou_code_id, $prefix.'exp_date', true);
										}

                                        //Download file arguments
                                        $download_args = array(
                                            'product_id' 	=> $product_id,
                                            'product_url' 	=> get_permalink( $product_id ),
                                            'product_name' 	=> $product_name,
                                            'download_url' 	=> $download_url,
                                            'download_name' => apply_filters( 'woo_vou_download_page_vou_download_btn', $product_name . ' - ' . $download_file['name'] . ' ' . $voucher_number . ' ( ' . $order_date . ' )', $product_id, $product_name, $download_file, $voucher_number, $order_date),
                                            'downloads_remaining' => '',
                                            'access_expires' => $vou_expiry_date,
                                            'file' => array(
                                                'name' => $download_file['name'],
                                                'file' => $download_file['file'],
                                            ),
                                        );

                                        //append voucher download to downloads array
										$recipient_details = $woo_vou_model->woo_vou_get_recipient_data($product_data);
										$allow_recipient_to_get_voucher_info = get_option('vou_allow_recipient_to_get_voucher_info');
										
										
										
										if( !empty($recipient_details) && $allow_recipient_to_get_voucher_info == 'yes'){
                                        }                                            
										else{	
											$downloads[] = $download_args;
										}
                                    }
                                }
                            }
                        }
                    }

                    //reset global order ID
                    $vou_order = 0;
                }
            }
        }
    }

    return $downloads;
}

/**
 * Update product stock as per voucher codes when woocommerce deduct stock
 * 
 * As woocommrece reduce stock quantity on product purchase and so we have to update stock
 * to no of voucher codes
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
function woo_vou_update_order_stock($order) {
	
	global $woo_vou_voucher, $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    // loop for each item
    foreach ($order->get_items() as $item) {

        if ($item['product_id'] > 0) {

            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
            $_product = $order->get_product_from_item($item);

            if ($_product && $_product->exists() && $_product->managing_stock()) {

                $product_id = $item['product_id'];
                $variation_id = isset($item['variation_id']) ? $item['variation_id'] : '';

                //Check voucher enable or not
                $order_args = array(
                    'order_item' => $item,
                    'order' => $order
                );

                // check voucher is enabled for this product
                if ($woo_vou_voucher->woo_vou_check_enable_voucher($product_id, $variation_id, $order_args)) {

                    //vendor user
                    $vendor_user = get_post_meta($product_id, $prefix . 'vendor_user', true);

                    //get vendor detail
                    $vendor_detail = $woo_vou_model->woo_vou_get_vendor_detail($product_id, $variation_id, $vendor_user);

                    //using type of voucher
                    $using_type = isset($vendor_detail['using_type']) ? $vendor_detail['using_type'] : '';

                    // if using type is one time only
                    if (empty($using_type)) {

                        //voucher codes
                        $vou_codes = woo_vou_get_voucher_code($product_id, $variation_id);

                        // convert voucher code comma seperate string into array
                        $vou_codes = !empty($vou_codes) ? explode(',', $vou_codes) : array();

                        // update stock quanity
                        $woo_vou_model->woo_vou_update_product_stock($product_id, $variation_id, $vou_codes);
                    }
                }
            }
        }
    }
}

/**
 * Expired/Upcoming product on shop page
 * 
 * Handles to Remove add to cart product button on shop page when product is upcomming or expired
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.2
 */
function woo_vou_shop_add_to_cart($add_to_cart_html) {

    global $product, $woo_vou_voucher;

    $expired = $woo_vou_voucher->woo_vou_check_product_is_expired($product);

    if ($expired == 'upcoming' || $expired == 'expired') {
        return ''; // do not display add to cart button
    }

    return $add_to_cart_html;
}

/**
 * Remove voucher download link
 * 
 * Hanles to remove voucher download link if voucher is
 * "used" 		- voucher code is redeemed
 * "exipred" 	- voucher date is expired and its not used
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.6.4
 * 
 * @TODO Add support for remove voucher download link at product level
 */
function woo_vou_remove_voucher_download_link($files, $file_key, $woo_vou_item_id) {

	global $woo_vou_voucher;
	
    //get prefix
    $prefix = WOO_VOU_META_PREFIX;

    $multiple_pdf = get_option('multiple_pdf');
    $revoke_voucher_download_link_access = get_option('revoke_voucher_download_link_access');
	
    // check multiple voucher and remove download voucher link is enabled
    if ($multiple_pdf == "yes" && $revoke_voucher_download_link_access == "yes") {

        /**
         ** Code to determine voucher code from file_key
         ** Get id from $file_key, i.e. Get 1 from woo_vou_pdf_1woo_vou_pdf_1
         ** Get code from order line item id
         ** Voucher Code will be id position in array of codes
         **/
        $codes = wc_get_order_item_meta($woo_vou_item_id, $prefix . 'codes'); // Get voucher codes for order item id
        $code_arr = explode(', ', $codes); // Convert $codes to array for codes
        $file_key_arr = explode('_', $file_key); // Explode $file_key to get download id
        $code_id = end($file_key_arr); // Get Download ID
        // get voucher code status
        $voucher_code_status = $woo_vou_voucher->woo_vou_get_voucher_code_status($code_arr[$code_id - 1]);
        if ($voucher_code_status === 'expired' || $voucher_code_status === 'used') {
            unset($files[$file_key]); // remove voucher download link
        }
    }
	
    return $files;
}

/**
 * Allow To add Admin email in BCC
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.6.8
 */
function woo_vou_allow_admin_to_bcc($headers, $email_id, $object) {

    $admin_email = get_option('admin_email');
    $admin_premission = get_option('vou_allow_bcc_to_admin');
    $woovou_gift_settings = get_option( 'woocommerce_woo_vou_gift_notification_settings' );

    // Get recipient email from email settings
    if ( !empty($woovou_gift_settings['recipient']) ) {

        $admin_email = $woovou_gift_settings['recipient'];
    }
    if ($admin_premission == "yes" && !empty($admin_email)) {

        switch ($email_id) {
            case 'customer_processing_order':
            case 'customer_completed_order':
            case 'woo_vou_gift_notification':
                $headers .= 'Bcc: ' . $admin_email . "\r\n";
                break;
            default:
        }
    }

    return apply_filters('woo_vou_allow_admin_to_bcc', $headers, $email_id, $object);
}

/**
 * Validate Coupon
 * 
 * Handles to validate coupon on Cart and Checkout page
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.2
 */
function woo_vou_validate_coupon($valid, $coupon) {
	
	global $woo_vou_model;

    // Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    if ($coupon) {

        // Get coupon_id
        $coupon_id = $woo_vou_model->woo_vou_get_coupon_id_from_coupon($coupon);

        // Get Coupon's start date
        $coupon_start_date = get_post_meta($coupon_id, $prefix . 'start_date', true);

        // Get coupon's restriction days
        $coupon_rest_days = get_post_meta($coupon_id, $prefix . 'disable_redeem_day', true);

        // Check start date validation
        if ( !empty( $coupon_start_date ) ) {

            if( current_time('timestamp') < strtotime($coupon_start_date) ){
                throw new Exception($error_code = $prefix . 'start_date_err'); // throw error
                return false; // return false
            }
        }

        // Check coupon restriction days
        if (!empty($coupon_rest_days)) {

            // Get current day
            $current_day = date('l');

            // check current day redeem is enable or not
            if (in_array($current_day, $coupon_rest_days)) {

                throw new Exception($error_code = $prefix . 'day_err'); // Throw error
                return false; // Return false
            }
        }
    }

    // Return
    return true;
}

/**
 * Get order details and coupon details then send it to create WC coupon code
 * If voucher code is used as copuon code, redeem it. 
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.3
 */
function woo_vou_generate_couponcode_from_vouchercode($order_id) {

	global $woo_vou_voucher, $woo_vou_model;
	
    $prefix = WOO_VOU_META_PREFIX; // Get prefix
    // Get "Generate Coupon Code" option
    $generate_coupon_code = get_option('vou_enable_coupon_code');

    $order = wc_get_order($order_id); // Get order details
    // Declare variables
    $vou_code_array = array();
    $product_qty = 0;
    $voucode = $vou_amount = $exp_date = $start_date = "";

    foreach ($order->get_items() as $item_id => $item) {

        //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
        $_product = $order->get_product_from_item($item);

        // Taking variation id
        $variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';

        if ($_product && $_product->exists()) { // && $_product->is_downloadable()
            if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
                //get product id from prduct data
                $product_id = isset($_product->id) ? $_product->id : '';
            } else {
                if ( $_product->is_type( 'variable' ) || $_product->is_type( 'variation' ) ) {
                    $product_id = $_product->get_parent_id();
                } else {
                    $product_id = $_product->get_id();    
                }
            }

            $enable_coupon_code_meta = get_post_meta( $product_id, $prefix.'enable_coupon_code', true );

            // If product is variable product take variation id else product id
            $data_id = (!empty($variation_id) ) ? $variation_id : $product_id;

            //Check voucher enable or not
            $order_args = array(
                'order_item' => $item,
                'order' => $order
            );
            if ($woo_vou_voucher->woo_vou_check_enable_voucher($product_id, $variation_id, $order_args)) {//Check voucher is enabled or not

            	if( ( !empty( $enable_coupon_code_meta ) && $enable_coupon_code_meta == 'yes' )
            		|| ( empty( $enable_coupon_code_meta ) && !empty( $generate_coupon_code ) && $generate_coupon_code == 'yes') ) {

                    $product_qty = $item['qty'];       // Get order quantity
                    $voucode = $item['woo_vou_codes'];     // Get used voucher/coupon code
                    $vou_amount = $woo_vou_model->woo_vou_get_product_price( $order_id, $item_id, $item ); // Get voucher amount
                    $vou_amount = apply_filters('woo_vou_get_voucher_coupon_price', $vou_amount, $variation_id, $item);

                    // Check product quantity
                    if ($product_qty > 1) {

                        $woo_vou_codes = explode(',', $voucode); // If order qty greater then 1, get value in array
                    } else {

                        $woo_vou_codes[] = $voucode; // Get Voucher codes in an array
                    }

                    // loop through voucher code
                    foreach ($woo_vou_codes as $woo_vou_code) {

                        $vou_code_array['vou_code'] = trim($woo_vou_code); // Get voucher codes
                        $vou_code_array['vou_amount'] = $vou_amount; // Get voucher amount
                        // Generate args to get voucher code id
                        $vou_code_args['fields'] = 'ids';
                        $vou_code_args['meta_query'] = array(
                            array(
                                'key' => $prefix . 'purchased_codes',
                                'value' => $woo_vou_code
                            ),
                            array(
                                'key' => $prefix . 'used_codes',
                                'compare' => 'NOT EXISTS'
                            )
                        );

                        // This always return array
                        $voucodedata = woo_vou_get_voucher_details($vou_code_args);

                        if (!empty($voucodedata)) {

                            // Get voucher and product id
                            $voucher_code_id = $voucodedata[0];
                            $product_id = wp_get_post_parent_id($voucher_code_id);

                            $voucher_start_date = get_post_meta($voucher_code_id, $prefix . 'start_date', true); // Get Voucher start date
                            $voucher_exp_date = get_post_meta($voucher_code_id, $prefix . 'exp_date', true); // Get voucher expiry date
                            $disable_redeem_day = get_post_meta($product_id, $prefix . 'disable_redeem_day', true); // Get voucher restriction days

                            $start_date = !empty($voucher_start_date) ? $voucher_start_date : ""; // Convert start date to specified format
                            $exp_date = !empty($voucher_exp_date) ? $voucher_exp_date : ""; // Convert expiry date to specified format
                            // Assign data to array
                            $vou_code_array['vou_start_date'] = $start_date;
                            $vou_code_array['vou_exp_date'] = $exp_date;
                            $vou_code_array['vou_rest_days'] = $disable_redeem_day;
                        }

                        //Create WC coupon code
                        $woo_vou_voucher->woo_vou_create_wc_coupon_code($vou_code_array, $order, $product_id);
                    }
                }
            }
        }
    }
}

/**
 * Changing the Recipient Form Position on Selecting Check Box
 * Adding it below the Add to Cart Button
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.4
 */
function woo_vou_enable_after_add_to_cart_button() {
	
    $prefix = WOO_VOU_META_PREFIX; // Get prefix
    // Enable to change the position
    $vou_recipient_form_position = get_option('vou_recipient_form_position');
	
    // If enable to change the for position
    if (!empty($vou_recipient_form_position) && $vou_recipient_form_position == 2) {

        //remove custom html to single product page before add to cart button
        remove_action('woocommerce_before_add_to_cart_button', 'woo_vou_after_before_add_to_cart_button');

        // Add Recipient form below cart button
        add_action('woocommerce_after_add_to_cart_button', 'woo_vou_after_before_add_to_cart_button');
    }
}

/**
 * Adding hook to allow images to display on thankyou page,
 * removed in WC 3.0
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_display_item_meta($html, $item, $args) {

	/**
	 * Code from woocommerce/includes/wc-template-functions.php
	 * Function Name ;- wc_display_item_meta
	 */	
	$strings = array();

	foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
		$value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
		$strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post( $meta->display_key ) . ':</strong> ' . $value;
	}

	if ( $strings ) {
		$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
	}
	
	return apply_filters('woo_vou_display_item_meta', $html, $item, $args);
}

/**
 * Adding hook to modify author args for voucher code pages
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.1.0
 */
function woo_vou_check_vendor_author_args($args) {

	// Get global variables
	global $current_user, $woo_vou_vendor_role;
	
	// Get "Enable Vendor to access all Voucher Codes" option
    $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

    // If option for "Enable Vendor to access all Voucher Codes" is set
    if(!empty($vou_enable_vendor_access_all_voucodes) && $vou_enable_vendor_access_all_voucodes == 'yes') {
    
		// Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );
	
		// Get voucher admin roles
		$admin_roles	= woo_vou_assigned_admin_roles();
	
		if( !in_array( $user_role, $admin_roles ) && in_array( $user_role, $woo_vou_vendor_role ) ) {
	
			unset($args['author']);
		}
    }

    // return $args
	return $args;
}


/**
 * Removed woocommerce_email_order_details action
 * Added custom function to display order downloads in email
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */
function woo_vou_email_order_details( $order, $sent_to_admin, $plain_text, $email ){
    
    $downloads = $order->get_downloadable_items();
    if( !empty( $downloads ) ) {
        
        $remove_action = false;
        foreach ( $downloads as $key => $download ) {
            if( strpos( $download['download_id'], 'woo_vou_pdf_' ) !== false ) {
                $remove_action  = true;
                break;
            }
        }
        
        if( $remove_action ) {
            $mailer = WC()->mailer(); // get the instance of the WC_Emails class
            remove_action( 'woocommerce_email_order_details', array( $mailer, 'order_downloads' ), 10, 4 );    
            add_action( 'woocommerce_email_order_details', 'woo_vou_order_downloads', 9, 4 );
        }        
    }   
}

/**
 * Show order downloads in a table.
 * Override function WC_Emails->order_downloads()
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */
function woo_vou_order_downloads( $order, $sent_to_admin = false, $plain_text = false, $email = '' ) {

    // Get order status
    if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
        $order_status = $order->post_status;
    } else {
        $order_status = $order->get_status();
    }

    $grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment');
    // If order has voucher code and downloadsble permissions are given
    if( $order_status == 'completed' || $order_status == 'wc-completed' 
    	|| ( $order_status == 'processing' && $grant_access_after_payment == "yes" ) ) {
        
        $downloads = $order->get_downloadable_items();
        $columns   = apply_filters( 'woocommerce_email_downloads_columns', array(
            'download-product' => esc_html__( 'Product', 'woocommerce' ),
            'download-expires' => esc_html__( 'Expires', 'woocommerce' ),
            'download-file'    => esc_html__( 'Download', 'woocommerce' ),
        ) );

        if ( $plain_text ) {
            wc_get_template( 'emails/plain/email-downloads.php', array( 'order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email, 'downloads' => $downloads, 'columns' => $columns ) );
        } else {
            wc_get_template( 'emails/email-downloads.php', array( 'order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email, 'downloads' => $downloads, 'columns' => $columns ) );
        }
    }
}


/**
 * 
 * Handles to update the Voucher Information from front end
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
function woo_vou_update_voucher_information() {

    global $woo_vou_model;
    
    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    // If update the recipient details
    if( isset($_REQUEST['woo_vou_voucher_information_update']) && isset($_REQUEST[ 'woo_vou_order_id' ]) ){
		
	
		
		$new_avail_locations = array();
        // Get data from submited form
        $woo_vou_order_id               = $_REQUEST[ 'woo_vou_order_id'];
        $woo_vou_item_id                = $_REQUEST[ 'woo_vou_item_id'];
        $woo_vou_product_id             = $_REQUEST[ 'woo_vou_product_id'];
        $woo_vou_code_id                = $_REQUEST[ 'woo_vou_code_id'];
        $voucher_parent_id              = wp_get_post_parent_id($woo_vou_code_id);
        $woo_new_vendor_logo            = $_REQUEST[ $prefix.'logo'];
        $woo_new_voucher_pdf_template   = $_REQUEST[ $prefix.'pdf_template'];
        $woo_new_vendor_address         = $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[ $prefix.'vendor_address' ] );
        $woo_new_voucher_website_url    = $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[ $prefix.'voucher_website_url' ] );
        $woo_new_voucher_redeem         = $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[ $prefix.'voucher_redeem' ] );
        $woo_new_voucher_expires_date   = $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[ $prefix.'voucher_expires_date' ] );
        $woo_new_voucher_start_date     = $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[ $prefix.'voucher_start_date' ] );
        $exp_type     =  $_REQUEST[ $prefix.'exp_type'];
       	
		$disable_redeem_day   =  isset($_REQUEST[ $prefix.'disable_redeem_day'])?$_REQUEST[ $prefix.'disable_redeem_day']:array();
	
		$locations   =  $_REQUEST[ $prefix.'locations'];
		$map_link    =  $_REQUEST[ $prefix.'map_link'];
		
		if(!empty($map_link) && !empty($locations)){
			
			foreach($locations as $loc_key => $location){
				$new_avail_locations[] = array($prefix.'locations'=>$location,$prefix.'map_link'=>$map_link[$loc_key]);
			}
		}
			 
        // Get order and order meta
        $woo_update_order   = new Wc_Order( $woo_vou_order_id );
        $woo_order_items    = $woo_update_order->get_items();
        $woo_vou_codes      = get_post_meta( $woo_vou_code_id, $prefix . 'purchased_codes', true );
        $woo_check_code     = trim( $woo_vou_codes );
        $woo_item_array     = $woo_vou_model->woo_vou_get_item_data_using_voucher_code( $woo_order_items, $woo_check_code );

        $woo_item           = isset( $woo_item_array['item_data'] ) ? $woo_item_array['item_data'] : array();
        $woo_item_id        = isset( $woo_item_array['item_id'] ) ? $woo_item_array['item_id'] : array();

        $meta_order_details = get_post_meta($woo_vou_order_id, $prefix . 'meta_order_details', true);
        if( isset($meta_order_details[$woo_vou_item_id]) ){
            
            // Replace the vocher information
            $meta_order_details[$woo_vou_item_id]['vendor_logo']    = $woo_new_vendor_logo;
            $meta_order_details[$woo_vou_item_id]['website_url']    = $woo_new_voucher_website_url;
            $meta_order_details[$woo_vou_item_id]['pdf_template']   = $woo_new_voucher_pdf_template;
            $meta_order_details[$woo_vou_item_id]['redeem']         = $woo_new_voucher_redeem;
            $meta_order_details[$woo_vou_item_id]['exp_date']       = $woo_new_voucher_expires_date;
            $meta_order_details[$woo_vou_item_id]['avail_locations'] = $new_avail_locations;
			if($exp_type != 'based_on_gift_date'){
				$meta_order_details[$woo_vou_item_id]['start_date']       = $woo_new_voucher_start_date;
			}

            // Replace vender address if is the simple product
            if( $woo_vou_product_id == $woo_vou_item_id ){

                $meta_order_details[$woo_vou_item_id]['vendor_address'] = $woo_new_vendor_address;
            } elseif( !empty($woo_vou_product_id) ) { // Replace the vender address with product variation id if is the variable

                $meta_order_details[$woo_vou_item_id]['vendor_address'][$woo_vou_product_id] = $woo_new_vendor_address;
            }
        }
		
        // Update all meta details in order meta
        update_post_meta( $woo_vou_order_id, $prefix . 'meta_order_details', $meta_order_details );
        
        // Update expire date in voucher meta
        $woo_vou_expires_date_meta = !empty($woo_new_voucher_expires_date) ? date( 'Y-m-d G:H:s', strtotime($woo_new_voucher_expires_date) ) : '';

        update_post_meta( $woo_vou_code_id, $prefix . 'exp_date', $woo_vou_expires_date_meta );
		
		
		
		// Update start date in voucher meta
		$woo_vou_start_date_meta = !empty($woo_new_voucher_start_date) ? date( 'Y-m-d G:H:s', strtotime($woo_new_voucher_start_date) ) : '';

		update_post_meta( $woo_vou_code_id, $prefix . 'start_date', $woo_vou_start_date_meta );
		
		// Update Voucher redeem days data
		update_post_meta( $woo_vou_code_id, $prefix . 'disable_redeem_day', $disable_redeem_day );

        if (!empty( $woo_vou_order_id) && !empty($voucher_parent_id)) {

            $woo_shop_coupon_posts_args = array(
                'post_type' => 'shop_coupon',
                'posts_per_page' => -1,
                'title' => $woo_vou_codes,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => $prefix . 'order_id',
                        'value' => $woo_vou_order_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => $prefix . 'coupon_type',
                        'value' => 'voucher_code',
                        'compare' => '=',
                    ),
                ),
            );
            $woo_shop_coupon_posts = get_posts($woo_shop_coupon_posts_args);

            if (!empty($woo_shop_coupon_posts)) {

                foreach ($woo_shop_coupon_posts as $woo_shop_coupon_post_data) {

                    $woo_shop_coupon_id = $woo_shop_coupon_post_data->ID;

                    $woo_shop_coupon_expiry_date = get_post_meta($woo_shop_coupon_id, 'expiry_date', true);
					
                    update_post_meta($woo_shop_coupon_id, 'expiry_date', $woo_vou_expires_date_meta, $woo_shop_coupon_expiry_date);
					
					
					 update_post_meta($woo_shop_coupon_id,  $prefix.'disable_redeem_day', $disable_redeem_day);
					
					
					//if($exp_type != 'based_on_gift_date'){
					// Update start date in voucher meta
					$woo_vou_start_date_meta = !empty($woo_new_voucher_start_date) ? date( 'Y-m-d G:H:s', strtotime($woo_new_voucher_start_date) ) : '';
					if(!empty($woo_vou_start_date_meta)){
						 update_post_meta($woo_shop_coupon_id, $prefix.'start_date', $woo_vou_start_date_meta);
					}
					
					
                }
            }
        }

        // Get Recipient data from order item
        $woo_pdf_template = $woo_item->get_meta( $prefix.'pdf_template_selection' );
        if( !empty( $woo_pdf_template ) ) {

            $woo_pdf_template['value'] = $woo_new_voucher_pdf_template; // change the value

            // Updating item meta
            $woo_item->update_meta_data( $prefix.'pdf_template_selection', $woo_pdf_template);
            $woo_item->update_meta_data( $woo_pdf_template['label'], $woo_new_voucher_pdf_template);

            // Save updated meta
            $woo_item->save_meta_data();
        }
		
		 // Get Recipient gift from order item
		if( $exp_type == 'based_on_gift_date'){
			$recipient_giftdate = $woo_item->get_meta( $prefix.'recipient_giftdate' );
			if( !empty( $recipient_giftdate ) ) {

				$recipient_giftdate['value'] = $woo_new_voucher_start_date; // change the value

				// Updating item meta
				$woo_item->update_meta_data( $prefix.'recipient_giftdate', $recipient_giftdate);
				$woo_item->update_meta_data( $recipient_giftdate['label'], $woo_new_voucher_start_date);

				// Save updated meta
				$woo_item->save_meta_data();
			}
		}

        // Add message argument for voucher information and reload page 
        wp_redirect( add_query_arg( array( 'message' => 'woo_vou_voucher_information_changed' ) ) );
        exit;
    }
}

/**
 * 
 * Handles to check recipient details before updating
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
function woo_vou_check_redeem_info(){

	global $woo_vou_model;

	$prefix = WOO_VOU_META_PREFIX; // Get prefix

	// Declare variables
	$response 	= array();
	$valid 		= true;

	// Get recipient data for product id
	$recipient_data = $woo_vou_model->woo_vou_get_product_recipient_meta($_REQUEST['product_id']);

	// Get all recipient columns
	$recipient_columns = woo_vou_voucher_recipient_details();

	foreach( $recipient_columns as $recipient_key => $recipient_val ) {

		if( !empty( $recipient_val ) && is_array( $recipient_val ) && array_key_exists( 'type', $recipient_val )
			&& !empty( $recipient_val['type'] ) && !empty( $_POST[$recipient_key] ) ) {

			if( $recipient_val['type'] == 'email' && !is_email($_POST['recipient_email']) ) {

				$response['not_valid_fields'][$recipient_key] = '<p class="woo-vou-recipient-error woo-vou-recipient-invalid-email-err-message">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key.'_label'] . '.</p>';
        		$valid = false;
			}

			if( $recipient_val['type'] == 'date' ) {

				$is_date_valid = $woo_vou_model->woo_vou_check_date( $_POST[$recipient_key], $_POST['product_id'] );

		        if( $is_date_valid['error'] && array_key_exists( 'error_type', $is_date_valid ) ) {
		            if ( $is_date_valid['error_type'] == 'date_not_proper' ) {
		                $response['not_valid_fields'][$recipient_key] = '<p class="woo-vou-recipient-error woo-vou-recipient-invalid-giftdate-err-message">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key.'_label'] . '.</p>';
		                $valid = false;
		            }
		    	}
			}
		}
	}

    $response['valid'] = $valid;
    echo json_encode($response);
    exit;
}

/**
 * 
 * Handles to update the Recipient Information from front end
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
function woo_vou_update_recipient_details() {

    global $woo_vou_model;
    
    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    // If update the recipient details
    if( isset($_REQUEST['woo_vou_voucodeid']) && !empty($_POST['woo_vou_update_recipient_info']) ){

        // Get data from submited form
        $woo_voucodeid              = $_POST['woo_vou_voucodeid'];

        // Get order id and voucher code from voucher meta
        $woo_order_id       = get_post_meta( $woo_voucodeid, $prefix . 'order_id', true);
        $woo_vou_codes      = get_post_meta( $woo_voucodeid, $prefix . 'purchased_codes', true );

        // Get order and order meta
        $woo_update_order   = new Wc_Order( $woo_order_id );
        $woo_order_items    = $woo_update_order->get_items();

        $woo_check_code = trim( $woo_vou_codes );
        $woo_item_array = $woo_vou_model->woo_vou_get_item_data_using_voucher_code( $woo_order_items, $woo_check_code );

        $woo_item       = isset( $woo_item_array['item_data'] ) ? $woo_item_array['item_data'] : array();
        $woo_item_id    = isset( $woo_item_array['item_id'] ) ? $woo_item_array['item_id'] : array();

        $recipient_cols = woo_vou_voucher_recipient_details();

        foreach( $recipient_cols as $recipient_col_key => $recipient_col_val ) {

        	$woo_new_recipient_col	= $woo_vou_model->woo_vou_escape_slashes_deep( $_REQUEST[$prefix.$recipient_col_key] );
        	if( $recipient_col_key == 'recipient_giftdate' && !empty( $woo_new_recipient_col ) ) {

        		$woo_new_recipient_col = date( 'd-m-Y', strtotime( $woo_new_recipient_col ) );
        	}

        	// Get Recipient data from order item
        	$woo_recipient_col 	= $woo_item->get_meta( $prefix.$recipient_col_key );

        	// Update Recipient Name
	        if( isset($woo_recipient_col['value']) && ($woo_new_recipient_col != $woo_recipient_col['value']) ){ // if value not exists
	
	            $woo_recipient_col['value'] = $woo_new_recipient_col; // change the value
	        } elseif( !isset($woo_recipient_col['value']) && !isset($woo_recipient_col['label']) ) { // if detail not exists


	        	if( !empty( $recipient_col_val ) && array_key_exists( 'label', $recipient_col_val )
	        		&& !empty( $recipient_col_val['label'] ) ) {

	        		$recipient_col_val = $recipient_col_val['label'];
	        	}
	            $woo_recipient_col = array( 'label' => $recipient_col_val, 'value' => $woo_new_recipient_col );
	        }
	        $woo_item->update_meta_data( $prefix.$recipient_col_key, $woo_recipient_col);
	        $woo_item->update_meta_data( $woo_recipient_col['label'], $woo_new_recipient_col);
        }

        // Save updated meta
        $woo_item->save_meta_data();

        // Add message argument for Recipient Information and reload page 
        wp_redirect( add_query_arg( array( 'message' => 'woo_vou_recipient_details_changed' ) ) );
        exit;
    }
}

/**
 * Voucher Code details data
 * 
 * Handles to get voucher code detail data
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
function woo_vou_get_vou_details_data() {

    $voucodeid  = $_GET['vou_code']; // Get vou_code param

    // include file to get voucher code data
    include_once( WOO_VOU_DIR . '/includes/public/woo-vou-code-details-info.php' ); 
}

/**
 * Handles to bypass change Coupon Expiry date error message
 * when coupon code is type voucher_code and 
 * Allow Redeem For expired voucher code is enabled
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.2
 */
function woo_vou_get_coupon_expiry_date( $expiry_date, $coupon ){

	// Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    // If user is on frontend and expiry date is not empty and coupon is not empty
	if( !is_admin() && !empty( $expiry_date ) && !empty( $coupon )) {

		$coupon_id 		= $coupon->get_id(); // Get coupon id
		$_coupon_post 	= get_post( $coupon_id ); // Get coupon post

		// If post type is shop_coupon
		if( $_coupon_post->post_type == 'shop_coupon' ) {

			
			$is_voucher_type = get_post_meta( $coupon_id, $prefix.'coupon_type', true );
			$vou_allow_redeem_expired_voucher = get_option( 'vou_allow_redeem_expired_voucher' );

			// If coupon is voucher code and Allow Redeem for Expired voucher code is enabled
			if( !empty( $is_voucher_type ) && $is_voucher_type == 'voucher_code'
				&& !empty( $vou_allow_redeem_expired_voucher ) && $vou_allow_redeem_expired_voucher == 'yes' ) {

	    		$expiry_date = ''; // Empty expiry date
			}
		}
	}

	// Return expiry date
	return $expiry_date;
}

/**
 * Handles to allow pdf voucher to be shown in downloads page
 * to recipient email user
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.1
 */
function woo_vou_permission_download_recipient_user( $order_id ){

    global $woo_vou_model, $woo_vou_voucher;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;
   
    // Get permission option
    $vou_permission_vou_download_recipient_user = get_option( 'vou_enable_permission_vou_download_recipient_user' );

    if( !empty($vou_permission_vou_download_recipient_user) && ( $vou_permission_vou_download_recipient_user == 'yes' ) ){

        $cart_details = wc_get_order($order_id); // get order details
        $order_items = $cart_details->get_items(); // get order items
        $order_status = woo_vou_get_order_status($cart_details); // Get order status

        if (!empty($order_items)) { // If item is empty
            foreach ($order_items as $product_item_key => $product_data) {

                $product_id 	= isset($product_data['product_id']) ? $product_data['product_id'] : '';
                $variation_id 	= isset($product_data['variation_id']) ? $product_data['variation_id'] : '';

                $recipient_details 	= array(); //Initilize recipient detail
                $product_item_meta 	= isset($product_data['item_meta']) ? $product_data['item_meta'] : array(); //Get product item meta
                $recipient_details 	= $woo_vou_model->woo_vou_get_recipient_data($product_item_meta); // get recipient details
                $recipient_email 	= isset($recipient_details['recipient_email']) ? $recipient_details['recipient_email'] : '';
                $voucodes 			= wc_get_order_item_meta( $product_item_key, $prefix.'codes' ); // get voucher code from order item
                
                // If voucher code is not exist
                if( !empty($voucodes) ){

                    $vou_codes = explode( ', ', $voucodes );
                    $voucode_args = array(
                        'meta_query' => array(
                            array(
                                'key'       => $prefix . 'purchased_codes',
                                'value'     => $vou_codes,
                                'compare'   => 'IN'
                            ),
                        )
                    );

                    // Get purchased voucher codes data from database
                    $woo_data   = woo_vou_get_voucher_details( $voucode_args );

                    if( !empty( $woo_data ) ){

                        foreach ($woo_data as $woo_post_data ) {
                            
                            $voucodeid = $woo_post_data['ID'];

                            // Get voucher code
                            $vou_code = get_post_meta($voucodeid, $prefix.'purchased_codes', true);

                            if( email_exists( $recipient_email ) ) { // If user is exist

                                $user = get_user_by('email', $recipient_email);
                                update_post_meta($voucodeid, $prefix.'recipient_userid', $user->ID ); // Save userid in to voucher meta
                            } else {

                                // Get nonuser recipient email
                                $vou_nonuser_recipient_email = get_option('vou_nonuser_recipient_email');
                                if( !empty( $vou_nonuser_recipient_email ) ) {

                                    $email_flag = false;
                                    foreach( $vou_nonuser_recipient_email as $email => $vou_codes ) {

                                        if( $email == $recipient_email ) {

                                            array_push( $vou_nonuser_recipient_email[$recipient_email], $voucodeid );
                                            $email_flag = true;
                                            break;
                                        }
                                    }

                                    if( !$email_flag ) {

                                        $vou_nonuser_recipient_email[$recipient_email] = array($voucodeid);
                                    }
                                } else {

                                    $vou_nonuser_recipient_email[$recipient_email] = array($voucodeid);
                                }

                                // Make voucher id unique
                                $vou_nonuser_recipient_email[$recipient_email] = array_unique( $vou_nonuser_recipient_email[$recipient_email] );

                                // Update nonuser recipient email
                                update_option('vou_nonuser_recipient_email', $vou_nonuser_recipient_email);
                            }
                        }
                    } // End if check empty woo_data
                }
            }
        } // End if item is empty
    } // End if permission option check 
}


/**
 * Check voucher code exist
 * 
 * Handles when voucher code does not exist, manipulation URL, wrong voucher id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.0
 */
function woo_vou_check_voucher_code_exist(){
    global $post;

    // check if is voucher code not avaible and is voucher codes list page
    if ( !empty( $_GET['vou_code'] ) && ( has_shortcode( $post->post_content, 'woo_vou_used_voucher_codes' ) 
    	|| has_shortcode( $post->post_content, 'woo_vou_purchased_voucher_codes' ) || has_shortcode( $post->post_content, 'woo_vou_unused_voucher_codes' ) ) ) {

        // Get vouchercodes data 
        $voucodeid      = $_GET['vou_code'];
        $voucher_data   = get_post($voucodeid);

        if ( empty($voucher_data) || ($voucher_data->post_type != WOO_VOU_CODE_POST_TYPE) ) {

            wp_die( sprintf( esc_html__( "%sYou attempted to view an voucher that doesn't exist. %s %s Perhaps it was deleted?%s", 'woovoucher' ), '<h1>', '</h1>', '<p>', '</p>' ), 403 );
        }
    }
}




/**
 * Get voucher redeem limit
 * 
 * Handles get voucher uses limit
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.0
 */
function woo_vou_get_voucher_uses_limit_by_voucher_id($voucher_id = null,$product_id = null ) {
	
    $allow_unlimited_redeem = get_option('vou_allow_unlimited_redeem_vou_code');
    $voucher_uses_limit  = '';

    if (!empty($allow_unlimited_redeem) && $allow_unlimited_redeem == 'yes') { // If allow unlimited redeem is set to true 
        
        $product_voucher_uses_limit = '';

    	$prefix 	= WOO_VOU_META_PREFIX;

    	if( !empty($voucher_id) ){		
    		$product_id = wp_get_post_parent_id($voucher_id);
    	}
    	
    	$voucher_uses_limit = get_option('vou_allow_unlimited_limit_vou_code');
        
    	$product_voucher_uses_limit  =  get_post_meta($product_id,$prefix.'voucher_uses_limit',true);
    	
    	if(( isset($product_voucher_uses_limit) && !empty($product_voucher_uses_limit) )){
    		$voucher_uses_limit = $product_voucher_uses_limit;
    	}
    }
    	
	return $voucher_uses_limit; 	
}
