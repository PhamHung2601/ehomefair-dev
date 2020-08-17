<?php
/**
 * Templates Functions
 * 
 * Handles to manage templates of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !function_exists( 'woo_vou_get_templates_dir' ) ) { 
	
	/**
	 * Returns the path to the pdf vouchers templates directory
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_get_templates_dir() {
		
		return apply_filters( 'woo_vou_get_templates_dir', WOO_VOU_DIR . '/includes/templates/' );
	}
}

if( !function_exists( 'woo_vou_get_template' ) ) {
	
	/**
	 * Get other templates
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		
		$plugin_absolute	= woo_vou_get_templates_dir();
		
		if ( ! $template_path ) {
			$template_path = WC()->template_path() . WOO_VOU_PLUGIN_BASENAME . '/';
		}

		wc_get_template( $template_name, $args, $template_path, $plugin_absolute );
	}
}

if( !function_exists( 'woo_vou_recipient_fields_content' ) ) {
	
	/**
	 * Recipient Fields
	 * 
	 * Handles to display Recipient Fields
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_recipient_fields_content() {

		global $product , $woo_vou_model, $woo_vou_voucher;

		$prefix = WOO_VOU_META_PREFIX;

		// store product in reset variable
		$reset_product	= $product;
		
		//Initilize products
		$products = array();
		
		if ( is_object( $product ) && $product->is_type( 'variable' ) ) { //for variable product
			foreach ( $product->get_children() as $variation_product_id ) {
				$products[] = wc_get_product( $variation_product_id );
			}
		} else {
			$products[] = $product;
		}

		$enable_pdf_preview 		= get_option( 'vou_enable_voucher_preview' );
		$product_enable_pdf_preview	= ( !empty( $product ) && is_object( $product ) ) ? get_post_meta( $product->get_id(), $prefix.'enable_pdf_preview', true ) : '';
	
		foreach ( $products as $product ) {//For all products

			if( !is_object( $product ) )
				continue;

			//Get prefix
			$prefix			= WOO_VOU_META_PREFIX;

			// Get product ID
			$product_id = $variation_id = woo_vou_get_product_id( $product );

			if( $product->is_type( 'variation' ) ) {
				// Get product ID
				$product_id 	= $woo_vou_model->woo_vou_get_item_productid_from_product($product);
				// Get variation ID
				$variation_id 	= woo_vou_get_product_variation_id( $product );
			}	

			//voucher enable or not
			$voucher_enable	= $woo_vou_voucher->woo_vou_check_enable_voucher( $product_id, $variation_id );

			if( $voucher_enable ) { // if voucher is enable
				
				//Get product recipient meta setting
				$recipient_data	= $woo_vou_model->woo_vou_get_product_recipient_meta( $product_id );

				// Get all recipient columns from
    			$recipient_columns = woo_vou_voucher_recipient_details();

    			// Set flag to show recipient template
    			$recipient_template_flag = false;

    			foreach( $recipient_columns as $recipient_key => $recipient_val ) {

    				if( !empty( $recipient_data['enable_'.$recipient_key] ) && $recipient_data['enable_'.$recipient_key] == 'yes' ) {

    					$recipient_template_flag = true;
    					break;
    				}
    			}

				// Pdf Template Selection fields
				$enable_pdf_template_selection	= $recipient_data['enable_pdf_template_selection'];
				$pdf_template_selection_label	= $recipient_data['pdf_template_selection_label'];
				$pdf_template_desc				= $recipient_data['pdf_template_desc'];

				// Recipient Columns
				$recipient_columns				= $recipient_data['recipient_columns'];

				// Recipient Order details
				$recipient_detail_order			= $recipient_data['recipient_detail_order'];
				array_pop( $recipient_detail_order );

				// Recipient Delivery Method
				$recipient_delivery_method			= $recipient_data['recipient_delivery_method'];
				$default_delivery_method			= woo_vou_voucher_delivery_methods();
				$enable_recipient_delivery_method	= $recipient_data['enable_recipient_delivery_method'];
				$recipient_delivery_label			= $recipient_data['recipient_delivery_label'];
				$individual_recipient_details		= $recipient_data['individual_recipient_details'];

				// check if enable Recipient Detail
				if( $recipient_template_flag || $enable_pdf_template_selection == 'yes' ) {

					$delivery_method			= isset( $_POST[$prefix.'delivery_method'][$variation_id] ) ? $_POST[$prefix.'delivery_method'][$variation_id] : '';
					$pdf_template_selection		= isset( $_POST[$prefix.'pdf_template_selection'][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.'pdf_template_selection'][$variation_id] ) : '';
					
	  				$product_templates = array();	  		
	  				$product_templates = get_post_meta( $product_id, $prefix.'pdf_template_selection', true );
	  		
	  				if( empty( $product_templates ) ){
	  					$product_templates = get_option( 'vou_pdf_template_selection' );
	  				}
	  		
			  		if( empty( $product_templates ) ){
						
			  			$args = array(
							'posts_per_page'   => -1,
							'orderby'          => 'date',
							'order'            => 'DESC',
							'post_type'        => WOO_VOU_POST_TYPE,
							'post_status'      => 'publish',
						);
						$posts_array = get_posts( $args ); 
			  			
						foreach( $posts_array as $key ){
							$product_templates[] = $key->ID;
						}
			  		}

			  		$vou_exp_start_date = $vou_exp_end_date = '';

					$expiration_date_type = get_post_meta($product_id, $prefix.'exp_type', true); // Get expiration date type

			  		// If expiration date type is specific date
					if(!empty($expiration_date_type) && $expiration_date_type == 'specific_date') { // If expiration date is set to specific date
		
						$vou_exp_start_date = get_post_meta($product_id, $prefix.'start_date', true); // Get voucher start date
						$vou_exp_end_date 	= get_post_meta($product_id, $prefix.'exp_date', true); // Get voucher expiry date
					} else if(!empty($expiration_date_type) && $expiration_date_type == 'based_on_purchase') { // If expiration date is set to based on purchase
		
						$vou_exp_days_diff_meta = get_post_meta($product_id, $prefix.'days_diff', true); // Get days difference
						if($vou_exp_days_diff_meta == 'cust') { // If days difference is custom
		
							$vou_exp_days_diff = get_post_meta($product_id, $prefix.'custom_days', true); // Get custom days from meta
						} else {
		
							$vou_exp_days_diff = $vou_exp_days_diff_meta;
						}
					} else if( ($expiration_date_type == 'default')) { // If expiration date is set to default
		
						$exp_type = get_option('vou_exp_type'); //get expiration type 
		
		                if ($exp_type == 'specific_date') { //If expiry type specific date
		
		                	$vou_exp_start_date = get_option('vou_start_date'); // start date
			                $vou_exp_start_date = !empty($vou_exp_start_date) ? date('Y-m-d H:i:s', strtotime($vou_exp_start_date)) : ''; // format start date
			                $vou_exp_end_date = get_option('vou_exp_date'); // expiration date
			                $vou_exp_end_date = !empty($vou_exp_end_date) ? date('Y-m-d H:i:s', strtotime($vou_exp_end_date)) : ''; // format exp date
		                } elseif ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
		                    //get days difference
		                    $days_diff = get_option('vou_days_diff');
		
		                    if ($days_diff == 'cust') {
		                        $custom_days = get_option('vou_custom_days');
		                        $custom_days = isset($custom_days) ? $custom_days : '';
		                        if (!empty($custom_days)) {
		                            $add_days = '+' . $custom_days . ' days';
		                            $vou_exp_end_date = date('Y-m-d H:i:s', strtotime( $add_days ));
		                        } else {
		                            $vou_exp_end_date = date('Y-m-d H:i:s', current_time('timestamp'));
		                        }
		                    } else {
		                        $custom_days = $days_diff;
		                        $add_days = '+' . $custom_days . ' days';
		                        $vou_exp_end_date = date('Y-m-d H:i:s', strtotime( $add_days ));
		                    }
		                }
					}

					$vou_min_date = !empty($vou_exp_start_date) ? date('Y-m-d', strtotime($vou_exp_start_date)) : (!empty( $vou_exp_days_diff) ? date('Y-m-d') : 0); // Format Voucher start date
					$vou_max_date = !empty($vou_exp_end_date) ? date('Y-m-d', strtotime($vou_exp_end_date)) : (!empty( $vou_exp_days_diff) ? date('Y-m-d', strtotime("+".$vou_exp_days_diff." days")) : ''); // Format Voucher expiry date
		
					// Get global time to send Gift Notification email
					$vou_gift_notification_time = get_option('vou_gift_notification_time');
		
					// If gift notification time is not empty
					if( !empty( $vou_gift_notification_time ) && empty( $vou_min_date ) ) {
		
						$current_hour = current_time('G'); // Get current hour
		
						// If notification time is passed or it is same as current hour
						if( $vou_gift_notification_time <= $current_hour ) {
		
							$vou_min_date = "+1"; // Say minimum date as next day
						}
					}

					$args = array();
					foreach( $recipient_columns as $recipient_key => $recipient_val ) {

						$args['enable_'.$recipient_key]						= $recipient_data['enable_'.$recipient_key];
						$args[$recipient_key]['enable_'.$recipient_key] 	= $recipient_data['enable_'.$recipient_key];
						$args[$recipient_key][$recipient_key.'_label']		= $recipient_data[$recipient_key.'_label'];
						$args[$recipient_key][$recipient_key.'_max_length']	= $recipient_data[$recipient_key.'_max_length'];
						$args[$recipient_key][$recipient_key.'_required'] 	= $recipient_data[$recipient_key.'_is_required'];
						$args[$recipient_key][$recipient_key.'_desc'] 		= $recipient_data[$recipient_key.'_desc'];
						$args[$recipient_key]['variation_id'] 				= $variation_id;
						$args[$recipient_key]['type'] 						= !empty( $recipient_val ) && is_array( $recipient_val ) && array_key_exists( 'type', $recipient_val ) && !empty( $recipient_val['type'] ) ? $recipient_val['type'] : 'text';
						$args[$recipient_key][$recipient_key]				= isset( $_POST[$prefix.$recipient_key][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.$recipient_key][$variation_id] ) : '';

						if( $recipient_key == 'recipient_giftdate' ) {

							$args[$recipient_key]['vou_min_date'] = $vou_min_date;
							$args[$recipient_key]['vou_max_date'] = $vou_max_date;
						}
					}

					$args = array_merge( $args, array(
											'recipient_columns'					=> 	$recipient_columns,
											'variation_id'				   		=>	$variation_id,
											'enable_pdf_template_selection'		=>	$enable_pdf_template_selection,
											'pdf_template_selection_label' 		=>  $pdf_template_selection_label,
											'pdf_template_selection'	   		=>	$pdf_template_selection,
											'pdf_template_desc'					=>  $pdf_template_desc,
											'product_templates'	   		   		=>	$product_templates,
											'recipient_detail_order'	   		=> 	$recipient_detail_order,
											'recipient_delivery_method'	   		=>	$recipient_delivery_method,
											'enable_recipient_delivery_method' 	=>	$enable_recipient_delivery_method,
											'default_delivery_method'			=> 	$default_delivery_method,
											'recipient_delivery_label'			=>  $recipient_delivery_label,
											'delivery_method'					=>	$delivery_method,
											'individual_recipient_details'		=>  $individual_recipient_details
										) );

			  		// Create array
					$args	= apply_filters( 'woo_vou_recipient_fields_content', $args, $product_id );

					woo_vou_get_template( 'woo-vou-recipient-fields.php', $args );
				}

				if( ( !empty( $product_enable_pdf_preview ) && $product_enable_pdf_preview == 'yes' )
					|| ( empty($product_enable_pdf_preview) && !empty( $enable_pdf_preview ) && $enable_pdf_preview == 'yes' ) ) {

					$preview_args = array(
											'product_id' 	=> $product_id,
											'variation_id'	=> $variation_id
										);
	
					woo_vou_get_template( 'woo-vou-preview-pdf.php', $preview_args );
				}
			}
		}
		
		// restore product
		$product	= $reset_product;
	}
}

if( !function_exists( 'woo_vou_check_qrcode_content' ) ) {
	
	/**
	 * Load qrcode template
	 * 
	 * Handles to load check voucher code using qrcode form
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.7.1
	 */
	function woo_vou_check_qrcode_content() {

		global $woo_vou_voucher;
			
		$redeem_response	= '';
		$voucodes           = '';
		$template_path      = '';
		
		if( !empty( $_POST['woo_vou_voucher_code_submit'] ) ) { // if form is submited

			// save voucher code
			$redeem_response = $woo_vou_voucher->woo_vou_save_voucher_code();
		}
		
		
		do_action('woo_vou_check_qrcode_content_before_template');
		
		// if multiple voucher codes exist then split it
		$voucodes = explode( ",", $_GET['woo_vou_code'] );
		
		// pass arguments so we can use in tempelate 
		$args = array(
					'redeem_response' 	=>	$redeem_response,
					'voucodes' 	        =>	$voucodes
		);
		
		// call our function to go for tempelate
		woo_vou_get_template( 'qrcode/woo-vou-check-qrcode.php', $args );
	}
}

if( !function_exists( 'woo_vou_display_expiry_product' ) ) {
	
	/**
	 * expired/upcoming product
	 * 
	 * Handles to Remove add to cart product button and display expired/upcoming product
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.2
	 */
	function woo_vou_display_expiry_product() {
		
		global $product, $woo_vou_model, $woo_vou_voucher;
		
		$expired = $woo_vou_voucher->woo_vou_check_product_is_expired( $product );
		
		if ( $expired == 'upcoming' ) {
			
	    	// remove add to cart button from single product page
	    	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	    	
	    	// get expired/upcoming template
			woo_vou_get_template( 'expired/expired.php', array( 'expired' => $expired ) );
			
	    } elseif ( $expired == 'expired' ) {
	    	
	    	// remove add to cart button from single product page
	    	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	    	
	    	// get expired/upcoming template
	    	woo_vou_get_template( 'expired/expired.php', array( 'expired' => $expired ) );
	    }
	}
}

if( !function_exists( 'woo_vou_used_voucher_codes_content' ) ) {
	
	/**
	 * Used Voucher Code
	 * 
	 * Handles to show used voucher codes on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	function woo_vou_used_voucher_codes_content() {
		
		// Get used codes tempelate to get data
		woo_vou_get_template( 'voucher-codes/woo-vou-used-voucher-codes.php' );
	}
}

if( !function_exists( 'woo_vou_purchased_voucher_codes_content' ) ) {
	
	/**
	 * Used Voucher Code
	 * 
	 * Handles to show purchased voucher codes on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.7
	 */
	function woo_vou_purchased_voucher_codes_content() {
		
		// Get purchased codes tempelate to get data
		woo_vou_get_template( 'voucher-codes/woo-vou-purchased-voucher-codes.php' );
	}
}

if( !function_exists( 'woo_vou_unused_voucher_codes_content' ) ) {
	
	/**
	 * Unused Voucher Code
	 * 
	 * Handles to show unused voucher codes on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	function woo_vou_unused_voucher_codes_content() {
		
		// Get used codes tempelate to get data
		woo_vou_get_template( 'voucher-codes/woo-vou-unused-voucher-codes.php' );
	}
}

if( !function_exists( 'woo_vou_used_voucher_codes_listing_content' ) ) {

	/**
	 * Used Voucher Code listing table 
	 * 
	 * Handles to load listing for used voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	function woo_vou_used_voucher_codes_listing_content( $result_arr, $paging ) {
		
		//used codes listing template
		woo_vou_get_template( 'voucher-codes/used-codes-listing/used-codes-listing.php', array(	'result_arr' => $result_arr, 'paging' => $paging ) );
	}
}

if( !function_exists( 'woo_vou_purchased_voucher_codes_listing_content' ) ) {

	/**
	 * Purchased Voucher Code listing table 
	 * 
	 * Handles to load listing for purchased voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.7
	 */
	function woo_vou_purchased_voucher_codes_listing_content( $result_arr, $paging ) {
		
		// Purchased codes listing template
		woo_vou_get_template( 'voucher-codes/purchased-codes-listing/purchased-codes-listing.php', array(	'result_arr' => $result_arr, 'paging' => $paging ) );
	}
}

if( !function_exists( 'woo_vou_unused_voucher_codes_listing_content' ) ) {

	/**
	 * Unused Voucher Code listing table 
	 * 
	 * Handles to load listing for unused voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	function woo_vou_unused_voucher_codes_listing_content( $result_arr, $paging ) {
		
		//unused codes listing template
		woo_vou_get_template( 'voucher-codes/unused-codes-listing/unused-codes-listing.php', array(	'result_arr' => $result_arr, 'paging' => $paging ) );
	}
}

if( !function_exists( 'woo_vou_get_voucher_details_content' ) ) {

    /**
	 * Voucher Code details
	 * 
	 * Handles to show voucher code details
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
    function woo_vou_get_voucher_details_content($vou_code_data) {

        woo_vou_get_template( 'voucher-codes/woo-vou-code-details.php', array('vou_code_data' => $vou_code_data));
    }
}

if( !function_exists( 'woo_vou_recipient_name_html' ) ) {

	/**
	 * Recipient Name
	 * 
	 * Handles to show recipient name field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.5
	 */
	function woo_vou_recipient_name_html( $recipient_name_args ){

		woo_vou_get_template( 'recipient-fields/woo-vou-recipient-name.php', $recipient_name_args);
	}
}

if( !function_exists( 'woo_vou_recipient_email_html' ) ) {

	/**
	 * Recipient Email
	 * 
	 * Handles to show recipient email field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.5
	 */
	function woo_vou_recipient_email_html( $recipient_email_args ){

		woo_vou_get_template( 'recipient-fields/woo-vou-recipient-email.php', $recipient_email_args);
	}
}

if( !function_exists( 'woo_vou_recipient_message_html' ) ) {

	/**
	 * Recipient Message
	 * 
	 * Handles to show recipient message field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.5
	 */
	function woo_vou_recipient_message_html( $recipient_message_args ){

		woo_vou_get_template( 'recipient-fields/woo-vou-recipient-message.php', $recipient_message_args);
	}
}

if( !function_exists( 'woo_vou_recipient_giftdate_html' ) ) {

	/**
	 * Recipient Giftdate
	 * 
	 * Handles to show recipient giftdate field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.5
	 */
	function woo_vou_recipient_giftdate_html( $recipient_giftdate_args ){

		woo_vou_get_template( 'recipient-fields/woo-vou-recipient-giftdate.php', $recipient_giftdate_args);
	}
}

if( !function_exists( 'woo_vou_cstm_recipient_html' ) ) {

	/**
	 * Recipient Name
	 * 
	 * Handles to show recipient name field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.5
	 */
	function woo_vou_cstm_recipient_html( $cstm_recipient_details_args, $recipient_column ){

		woo_vou_get_template( 'recipient-fields/woo-vou-recipient-cstm.php', array( 
																					'cstm_recipient_details_args' => $cstm_recipient_details_args, 
																					'recipient_column' => $recipient_column
																					));
	}
}

if( !function_exists( 'woo_vou_preview_pdf_popup_html' ) ) {

	/**
	 * Preview PDF popup
	 * 
	 * Handles to show preview pdf popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.5.4
	 */
	function woo_vou_preview_pdf_popup_html(){

		woo_vou_get_template( 'woo-vou-preview-pdf-popup.php' );
	}
}