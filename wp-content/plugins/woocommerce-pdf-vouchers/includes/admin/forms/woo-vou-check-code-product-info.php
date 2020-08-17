<?php
/**
 * Handles to get product detail
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6.2
 */

global $woo_vou_vendor_role, $current_user,$woo_vou_wc_currency_switch, $woo_vou_voucher,$woo_vou_model;

// Get prefix
$prefix = WOO_VOU_META_PREFIX;

//get order
$order 			= wc_get_order( $order_id );

//get voucher admin roles
$admin_roles	= woo_vou_assigned_admin_roles();
//Get User roles
$user_roles		= isset( $current_user->roles ) ? $current_user->roles : array();
$user_role		= array_shift( $user_roles );
//Get custo
$order_customer	= get_post_meta( $order_id , '_customer_user' , true );

// Get "Check Voucher Code for all logged in users" option
$vou_enable_logged_user_check_voucher_code 	= get_option('vou_enable_logged_user_check_voucher_code');
$vou_enable_logged_user_redeem_vou_code 	= get_option('vou_enable_logged_user_redeem_vou_code');
// Get option whether to allow all vendor to redeem voucher codes
$vou_enable_vendor_access_all_voucodes 		= get_option('vou_enable_vendor_access_all_voucodes');

//get order items
$order_items 	= $order->get_items();

$order_date = $woo_vou_model->woo_vou_get_order_date_from_order($order); // Get order date
$payment_method = $woo_vou_model->woo_vou_get_payment_method_from_order($order); // Get payment method

//get buyer details
$buyer_detail	= $woo_vou_model->woo_vou_get_buyer_information( $order_id );

//get buyer name
$buyername		= isset( $buyer_detail['first_name'] ) ? $buyer_detail['first_name'] : '';
$buyername		.= isset( $buyer_detail['last_name'] ) ? ' '.$buyer_detail['last_name'] : '';

//Get voucher redeem methods
$redeem_methods	= apply_filters( 'woo_vou_redeem_methods', array(
						'full'		=> esc_html__( 'Full', 'woovoucher' ),
						'partial'	=> esc_html__( 'Partial', 'woovoucher' )
					));

$check_code	= trim( $voucode );
$item_array	= $woo_vou_model->woo_vou_get_item_data_using_voucher_code( $order_items, $check_code );

$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();
$_product 	= $order->get_product_from_item( $item );
$product_id = woo_vou_get_product_id( $_product ); // get product id

// get partial redeem
$enable_partial_redeem = apply_filters( 'woo_vou_enable_partial_redeem_during_check_voucher', woo_vou_check_partial_redeem_by_product_id( $product_id ), $order_id, $voucode );
$enable_partial_redeem = !empty( $enable_partial_redeem ) && $enable_partial_redeem == 1 ? 'yes' : 'no';

// Allow unlimited redeem
$allow_unlimited_redeem = get_option('vou_allow_unlimited_redeem_vou_code');

// check need to allow redeem for expired vouchers
$allow_redeem_expired_voucher = get_option( 'vou_allow_redeem_expired_voucher' );

// Get option whether to allow secondary vendor to redeem primary vendor voucher codes
$vou_allow_secondary_vendor_redeem_primary_voucher = get_option('vou_allow_secondary_vendor_redeem_primary_voucher');

//product info key parameter
$product_info_columns	= array(
								'item_name'		=> esc_html__( 'Item Name', 'woovoucher' ),
								'item_price'	=> esc_html__( 'Price ( Voucher Price )', 'woovoucher' )
							);

// add redeemable price column
$product_info_columns['redeemable_price'] = esc_html__( 'Redeemable Price', 'woovoucher' );

// redeem information key parameter
$redeem_info_columns	= apply_filters( 'woo_vou_check_vou_partial_redeem_info_fields', array(
												'item_name'		=> esc_html__( 'Item Name', 'woovoucher' ),
												'redeem_price'	=> esc_html__( 'Redeem Amount', 'woovoucher' ),
												'redeem_by'		=> esc_html__( 'Redeem By', 'woovoucher' ),
												'redeem_date'	=> esc_html__( 'Redeem Date', 'woovoucher')
											), $order_id, $voucode );



//product info parameter filters
$product_info_columns	= apply_filters( 'woo_vou_check_vou_productinfo_fields', $product_info_columns, $order_id, $voucode );

//product voucher information columns
$voucher_info_columns = apply_filters( 'woo_vou_check_vou_voucherinfo_fields', array(
													'logo' 			=> esc_html__( 'Logo', 'woovoucher' ),
													'voucher_data' 	=> esc_html__( 'Voucher Data', 'woovoucher' ),
													'expires' 		=> esc_html__( 'Expires', 'woovoucher' )
												), $order_id, $voucode );

//buyer info key parameter
$buyer_info_columns	= apply_filters( 'woo_vou_check_vou_buyerinfo_fields', array(
													'buyer_name'		=> esc_html__( 'Name', 'woovoucher' ),
													'buyer_email'		=> esc_html__( 'Email', 'woovoucher' ),
													'billing_address'	=> esc_html__( 'Billing Address', 'woovoucher' ),
													'shipping_address'	=> esc_html__( 'Shipping Address', 'woovoucher' ),
													'buyer_phone'		=> esc_html__( 'Phone', 'woovoucher' )
												), $order_id, $voucode );

//order info key parameter
$order_info_columns	= apply_filters( 'woo_vou_check_vou_orderinfo_fields', array(
													'order_id'			=> esc_html__( 'Order ID', 'woovoucher' ),
													'order_date'		=> esc_html__( 'Order Date', 'woovoucher' ),
													'payment_method'	=> esc_html__( 'Payment Method', 'woovoucher' ),
													'order_total'		=> esc_html__( 'Order Total', 'woovoucher' ),
													'order_discount'	=> esc_html__( 'Order Discount', 'woovoucher' ),
												), $order_id, $voucode );

$item_meta	= isset( $item['item_meta'] ) ? $item['item_meta'] : array();

//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
$billing_address	= $order->get_formatted_billing_address();
$shipping_address	= $order->get_formatted_shipping_address();

$recipient_details 	= $woo_vou_model->woo_vou_get_recipient_data_using_item_key($item_id);
if(isset($recipient_details['pdf_template_selection'])){

	unset($recipient_details['pdf_template_selection']);
}

// Recipient details
$recipient_details 	= apply_filters( 'woo_vou_check_vou_recipient_details', $recipient_details, $order_id, $voucode );

// Recipient Data Columns
$recipient_data_cols = woo_vou_voucher_recipient_details();

// get total price of voucher code

$vou_code_total_price 	=  $woo_vou_model->woo_vou_get_product_price( $order_id, $item_id, $item );

// get remaining price for redeem
$vou_code_remaining_redeem_price = number_format( (float)($vou_code_total_price), 2, '.', '' );

// if partial redeem is enabled
if( $enable_partial_redeem == "yes" || ( !empty( $allow_unlimited_redeem ) && $allow_unlimited_redeem == "yes" ) ) {
	
	// Get partially used voucher code data
	$args = $partially_redeemed_data = $redeemed_infos = array();
	$args = array(
				'woo_vou_list' => true,
				'post_parent' => $voucodeid
			);	
	
	//get partially used voucher codes data from database
	$redeemed_data 				= woo_vou_get_partially_redeem_details( $args );
	$partially_redeemed_data	= isset( $redeemed_data['data'] ) ? $redeemed_data['data'] : '';
	$redeemed_data_cnt 			= isset( $redeemed_data['total'] ) ? $redeemed_data['total'] : '';

	if( !empty( $partially_redeemed_data ) ) {
		
		foreach ( $partially_redeemed_data as $key => $value ) {

			$user_id 	  = get_post_meta( $value['ID'], $prefix.'redeem_by', true );
			if($user_id == '0'){
				$display_name = esc_html('Guest User','woovoucher');
			}else{
				$user_detail  = get_userdata( $user_id );
				$display_name = isset( $user_detail->display_name ) ? $user_detail->display_name : '';
			}
			
						
			$redeemed_amount	= get_post_meta( $value['ID'], $prefix . 'partial_redeem_amount', true );
			$redeem_date 		= get_post_meta( $value['ID'], $prefix . 'used_code_date', true );
			
			$redeemed_infos[$key] = apply_filters( 'woo_vou_check_partial_voucher_column_redeemed_info', array(
				"redeem_id" 		=> $value['ID'],
				"redeem_by"			=> $display_name,
				"redeem_amount" 	=> $woo_vou_wc_currency_switch->woo_vou_multi_currency_from_default_price($redeemed_amount),
				"redeem_date" 		=> $redeem_date,
			), $value, $voucodeid, $item_id, $order_id );
		}
	} else {

    	$is_code_used 	= get_post_meta( $voucodeid, $prefix.'used_codes', true );
    	$redeem_by		= get_post_meta( $voucodeid, $prefix.'redeem_by', true );
    	$redeem_date 	= get_post_meta( $voucodeid, $prefix . 'used_code_date', true);
    	if( !empty( $is_code_used ) && !empty( $redeem_by ) ) {

    		$user_detail 	= get_userdata( $redeem_by );
        	$display_name 	= $display_name; //isset( $user_detail->display_name ) ? $user_detail->display_name : '';
        	if( $redeem_by == '0'){
        		$display_name = esc_html('Guest User','woovoucher');
        	}

    		$redeemed_infos[] = array(
                "redeem_by" 	=> $display_name,
                "redeem_amount" => $woo_vou_wc_currency_switch->woo_vou_multi_currency_from_default_price($vou_code_total_price),
                "redeem_date" 	=> $woo_vou_model->woo_vou_get_date_format($redeem_date, true),
            );
    	}
    }

	// get total redeemed price
	$vou_code_total_redeemed_price = $woo_vou_voucher->woo_vou_get_total_redeemed_price_for_vouchercode( $voucodeid );
	$vou_code_total_redeemed_price = $woo_vou_wc_currency_switch->woo_vou_multi_currency_from_default_price($vou_code_total_redeemed_price );

	// get remaining price for redeem
	$vou_code_remaining_redeem_price = number_format( (float)( $vou_code_total_price - $vou_code_total_redeemed_price), 2, '.', '' );
}

?>

<div class="woo_vou_product_details"><?php
	
	// if partial redeem enabled then show partial redeem option
	if( !empty( $redeem_info_columns ) && !empty( $redeemed_infos ) && is_array( $redeemed_infos ) ) {
				
		do_action( 'woo_vou_before_redeeminfo', $voucodeid, $item_id, $order_id ); ?>
	
		<h2><?php echo esc_html__( 'Redeem Information', 'woovoucher' );?></h2>
		
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_pro_tit">
				<?php
				// Get product columns
				$product_col = count( $redeem_info_columns );
				$product_col = 'col-' . $product_col;
				
				foreach ( $redeem_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				} ?>
			</div>
			<?php

			foreach( $redeemed_infos as $key => $redeemed_info ) {
				
				if( $key != ( $redeemed_data_cnt - 1 ) )
					$margin_bottom_0 = 'woo-vou-margin-bottom-0';
				else
					$margin_bottom_0 = '';
					
				echo '<div class="woo_pdf_vou_pro_lst ' . $margin_bottom_0 . '">';
				
				foreach ( $redeem_info_columns as $col_key => $column ) { ?>
				
					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php

						$column_value = $sku_value	= '';

						switch ( $col_key ) {

							case 'item_name' : 									
								$column_value = '<div class="woo_pdf_res_vou"> ' . esc_html__( 'Item Name', 'woovoucher' ) . '</div>'; 
								
								if ( $_product && $_product->get_sku() ) {
									$sku_value	= esc_html( $_product->get_sku() ).' - ';
								}
								if ( $_product ) {
									$column_value .= $sku_value.'<a target="_blank" href="'. get_permalink( $product_id ) . '">' . esc_html( $item['name'] ) . '</a>';
								} else {
									$column_value .= $sku_value.esc_html( $item['name'] );
								}

								//Get product item meta
								$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();
								$column_value .= $woo_vou_model->woo_vou_display_product_item_name( $item, $_product, true );
								break;

							case 'redeem_price' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Price', 'woovoucher' ) . '</div>'; 
								
								if ( isset( $redeemed_info['redeem_amount'] ) ) {
									$column_value .= wc_price( $redeemed_info['redeem_amount'] , array( 'currency' => woo_vou_get_order_currency( $order ) ) ) ;											
								}
								break;
								
							case 'redeem_by' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Redeem By', 'woovoucher' ) . '</div>'; 
								
								if ( isset( $redeemed_info['redeem_by'] ) && !empty($redeemed_info['redeem_by']) ) {
									$redeem_by =  $redeemed_info['redeem_by'] ;
								}else{
									$redeem_by = '-';
								}
								$column_value .= $redeem_by;
								break;
								
							case 'redeem_date' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Redeem Date', 'woovoucher' ) . '</div>'; 
								
								if ( isset( $redeemed_info['redeem_date'] ) ) {
									$column_value .= $woo_vou_model->woo_vou_get_date_format( $redeemed_info['redeem_date'], true ) ;
								}
								
								break;

							default:
								$column_value .= '';
						}

						echo apply_filters( 'woo_vou_check_partial_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id, $redeemed_info );

						?>
				</div><?php 
			} 
			echo '</div>';
		}
		echo '</div>';
	}
	
	do_action( 'woo_vou_before_productinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $product_info_columns ) ) { //if product info is not empty ?>
		
		<h2><?php echo esc_html__( 'Product Information', 'woovoucher' );?></h2>
		<div class="woo_pdf_vou_main  woo-vou-product-info">
			<div class="woo_pdf_pro_tit">
				<?php
				// Get product columns
				$product_col = count($product_info_columns);
				$product_col = 'col-'.$product_col;
				
				foreach ( $product_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				} ?>
			</div>
			<div class="woo_pdf_vou_pro_lst"><?php
				
				foreach ( $product_info_columns as $col_key => $column ) {?>

					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php

						$column_value = $sku_value	= '';

						switch ( $col_key ) {

							case 'item_name' : 
							
							$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Item Name', 'woovoucher') . '</div>'; 
								
								if ( $_product && $_product->get_sku() ) {
									$sku_value	= esc_html( $_product->get_sku() ).' - ';
								}
								if ( $_product ) {
									$column_value .= $sku_value.'<a target="_blank" href="'. get_permalink( $product_id ) . '">' . esc_html( $item['name'] ) . '</a>';
								} else {
									$column_value .= $sku_value.esc_html( $item['name'] );
								}

								//Get product item meta
								$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();

								//Display product variations
								$column_value .= $woo_vou_model->woo_vou_display_product_item_name( $item, $_product, true );
								break;

							case 'item_price' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Price', 'woovoucher') . '</div>'; 
								
								// Get voucher price from order item meta
								$vou_price		= 	$woo_vou_model->woo_vou_get_product_price( $order_id, $item_id, $item );

								if( isset($vou_price) && !empty($vou_price)){
									$column_value	.= 	wc_price( $vou_price );
								}
								break;
								
							case 'redeemable_price' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Redeemable Price', 'woovoucher' ) . '</div>';
								$column_value .= wc_price( $vou_code_remaining_redeem_price );
								break;
								
							default:
								$column_value .= '';
						}

						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );

						?>
					</div><?php 
				} ?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_productinfo', $voucodeid, $item_id, $order_id );
	if(!empty($recipient_details)){ 

		$product_col = 'col-'.count($recipient_details);

		// Get date format from global setting
		$date_format = get_option( 'date_format' );
		?>

		<h2><?php echo esc_html__( 'Recipient Information', 'woovoucher' );?></h2>
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_pro_tit">
				<?php

				// Looping on recipient data cols
				foreach( $recipient_data_cols as $recipient_col_key => $recipient_col_val ) {

					if( !empty( $item_meta[$prefix.$recipient_col_key] ) ) {

                		echo '<div class="' . $product_col . ' woo_vou_padding">' . $item_meta[$prefix.$recipient_col_key]['label'] . '</div>';
                	}
				}
				?>
			</div>
			<div class="woo_pdf_vou_pro_lst">
			<?php

			// Looping on recipient data cols
			foreach( $recipient_data_cols as $recipient_col_key => $recipient_col_val ) {

				if( !empty( $item_meta[$prefix.$recipient_col_key] ) ) {

					$recipient_col_value = $item_meta[$prefix.$recipient_col_key]['value'];
					if( !empty( $recipient_col_val ) && is_array( $recipient_col_val )
                    	&& array_key_exists( 'type', $recipient_col_val )
                    	&& !empty( $recipient_col_value ) ) {

                    	if( $recipient_col_val['type'] == 'date' ) {
	                    	// Get date format from global setting
							$date_format = get_option( 'date_format' );
	                    	$recipient_col_value = date( $date_format, strtotime( $recipient_col_value ) );
                    	} else if ( $recipient_col_val['type'] == 'textarea' ) {

                    		$recipient_col_value = nl2br( $recipient_col_value );
                    	}
                    }

					echo '<div class="' . $product_col . ' woo_vou_padding">';
                    echo '<div class="woo_pdf_res_vou">'. $item_meta[$prefix.$recipient_col_key]['label'] . '</div>';
            		echo !empty($recipient_col_value) ? $recipient_col_value : '&nbsp;';
            		echo '</div>';
				}
			}
			?>
			</div>
		</div><?php 
	}

	do_action( 'woo_vou_after_recipientinfo', $voucodeid, $item_id, $order_id );

	if( !empty( $voucher_info_columns ) ) { //if voucher info column is not empty ?>
		
		<h2><?php echo esc_html__( 'Voucher Information', 'woovoucher' ); ?></h2>
		
		<div class="woo_pdf_vou_main  woo-vou-voucher-info">
			<div class="woo_pdf_pro_tit">
				<?php
				// Get product columns
				$voucher_col = count($voucher_info_columns);
				$voucher_col = 'col-'.$voucher_col;
			
				foreach ( $voucher_info_columns as $col_key => $column ) { ?>
					
					<div class="<?php echo $voucher_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				}?>
			</div>
			<div class="woo_pdf_vou_pro_lst"><?php 
				
				// get orderdata
				$allorderdata	= $woo_vou_model->woo_vou_get_all_ordered_data( $order_id );
                
				// Default vendor address
				$vendor_address_data  = esc_html__( 'N/A', 'woovoucher' );
				
				// If product is variation product
                if( $_product ) {

					// get product id. In case of variation get parent product id
	                $parent_product_id = $woo_vou_model->woo_vou_get_item_productid_from_product( $_product );
	                $allvoucherdata = apply_filters( 'woo_vou_order_voucher_metadata', isset( $allorderdata[$parent_product_id] ) ? $allorderdata[$parent_product_id] : array(), $order_id, $item_id, $parent_product_id );
	                
                    if( $_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['vendor_address']) ){

                        if( isset($allvoucherdata['vendor_address'][$product_id]) && !empty($allvoucherdata['vendor_address'][$product_id]) ){
                            $vendor_address_data = nl2br( $allvoucherdata['vendor_address'][$product_id] );
                        }
                    } elseif( isset($allvoucherdata['vendor_address']) && !empty($allvoucherdata['vendor_address']) ) {

                        $vendor_address_data = nl2br( $allvoucherdata['vendor_address'] );
                    }
					
					
					
					 if( $_product->is_type('variation') && isset($allvoucherdata) && is_array($allvoucherdata['exp_date']) ){

                        if( isset($allvoucherdata['exp_date'][$product_id]) && !empty($allvoucherdata['exp_date'][$product_id]) ){
                            $exp_date = !empty( $allvoucherdata['exp_date'][$product_id] ) ? $woo_vou_model->woo_vou_get_date_format( $allvoucherdata['exp_date'][$product_id], true ) : esc_html__( 'N/A', 'woovoucher' );
                        }
                    } elseif( isset($allvoucherdata['exp_date']) && !empty($allvoucherdata['exp_date']) ) {

                        $exp_date = !empty( $allvoucherdata['exp_date'] ) ? $woo_vou_model->woo_vou_get_date_format( $allvoucherdata['exp_date'], true ) : esc_html__( 'N/A', 'woovoucher' );
                    }
					
					
	            }
				
				foreach ( $voucher_info_columns as $col_key => $column ) { ?>

					<div class="<?php echo $voucher_col; ?> woo_vou_padding">
						<?php
						$column_value = '';

						switch ( $col_key ) {

							case 'logo' :									
								if( !empty(  $allvoucherdata['vendor_logo']['src'] ) )
									$column_value .= '<div class="woo_pdf_res_vou">' . esc_html__( 'Logo', 'woovoucher') . '</div>' . '<img src="' . esc_url($allvoucherdata['vendor_logo']['src']) . '" alt="" width="70" height="70" />';
								else 
									$column_value .= '<div class="woo_pdf_res_vou">' . esc_html__( 'Logo', 'woovoucher') . '</div> &nbsp;';
								break;
							case 'voucher_data' : 
								ob_start(); ?>									
								<span><strong><?php esc_html_e( 'Vendor\'s Address', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo $vendor_address_data; ?></span><br />
								<span><strong><?php esc_html_e( 'Site URL', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo !empty( $allvoucherdata['website_url'] ) ? $allvoucherdata['website_url'] : esc_html__( 'N/A', 'woovoucher' ); ?></span><br />
								<span><strong><?php esc_html_e( 'Redeem Instructions', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo !empty( $allvoucherdata['redeem'] ) ? nl2br( $allvoucherdata['redeem'] ) : esc_html__( 'N/A', 'woovoucher' ); ?></span><br /><?php
								
								if( !empty( $allvoucherdata['avail_locations'] ) ) {
									
									echo '<span><strong>' . esc_html__( 'Locations', 'woovoucher' ) . '</strong></span><br />';
									
									foreach ( $allvoucherdata['avail_locations'] as $location ) {
										
										if( !empty( $location[$prefix.'locations'] ) ) {
											
											if( !empty( $location[$prefix.'map_link'] ) ) {
												echo '<span><a class="woo-vou-voucher-location-link" target="_blank" href="' . esc_url($location[$prefix.'map_link']) . '">' . $location[$prefix.'locations'] . '</a></span><br />';
											} else {
												echo '<span>' . $location[$prefix.'locations'] . '</span><br />';
											}
										}
									}
								}
								$column_value = '<div class="woo_pdf_res_vou">'. esc_html__( 'Voucher Data', 'woovoucher') . '</div> <div class="woo_pdf_val">'. ob_get_clean() . '</div>';
								break;
							case 'expires' :
								$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Expires', 'woovoucher') . '</div>'; 
								
								$column_value .= ( isset( $exp_date ) && !empty( $exp_date ) ) ? $exp_date: esc_html__( 'Never Expire', 'woovoucher' );	
							default:
								$column_value .= '';
						}

						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );
						?>
					</div><?php
				}?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_voucherinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $buyer_info_columns ) ) { //if product info is not empty ?>
		
		<h2><?php echo esc_html__( 'Buyer\'s Information', 'woovoucher' ); ?></h2>
		<div class="woo_pdf_vou_main woo-vou-buyer-info">
			<div class="woo_pdf_vou_tit">
				<?php 
				// Get product columns
				$buyer_col = count($buyer_info_columns);
				$buyer_col = 'col-'.$buyer_col;
				
				foreach ( $buyer_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $buyer_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				} ?>
			</div>
			
			<div class="woo_pdf_vou_pro_lst">
				<?php
				foreach ( $buyer_info_columns as $col_key => $column ) { ?>
					
					<div class="<?php echo $buyer_col; ?> woo_vou_padding">
						<?php
						$column_value = '';
						
						switch ( $col_key ) { 
							
							case 'buyer_name' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Name', 'woovoucher') . '</div>';
                                $payment_user_info = $woo_vou_model->woo_vou_get_buyer_information($order_id); // Get payment information
						        $column_value .= $payment_user_info['first_name']; // Get billing first name
						        if(!empty($payment_user_info['last_name'])){
						        	$column_value .=  ' '.$payment_user_info['last_name'];
						        } else {
						        	$column_value .= '<div class="woo_pdf_val">&nbsp;</div>';
						        }
                                
                                if( empty( $column_value ) ) $column_value = '&nbsp;';								
								break;
							
							case 'buyer_email' : 
								$column_value .='<div class="woo_pdf_res_buyer">' . esc_html__( 'Email', 'woovoucher') . '</div>';
                                
                                if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                                    $column_value .= $order->billing_email;
                                else
                                    $column_value .= $order->get_billing_email();
                                
                                if( empty( $column_value ) ) $column_value = '&nbsp;';
								break;
							
							case 'billing_address' :
                                if( !empty( $billing_address ) )
                                    $column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Billing Address', 'woovoucher') . '</div> <div class="woo_pdf_val">' . $billing_address . "</div>";
                                else
                                    $column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Billing Address', 'woovoucher') . '</div> <div class="woo_pdf_val">&nbsp;</div>';
								break;
							
							case 'shipping_address' : 
                                if(!empty($shipping_address))
                                    $column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Shipping Address', 'woovoucher') . '</div> <div class="woo_pdf_val">' . $shipping_address . '</div>';
                                else 
                                    $column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Shipping Address', 'woovoucher') . '</div> <div class="woo_pdf_val">&nbsp;</div>';
								break;
							
							case 'buyer_phone' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . esc_html__( 'Phone', 'woovoucher') . '</div>';
                                
                                if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                                    $column_value .= $order->billing_phone;
                                else
                                    $column_value .= $order->get_billing_phone();
                                
                                if( empty( $column_value ) ) $column_value = '&nbsp;';
								break;
							
							default:
								$column_value .= '';
						}
						
						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );
						?>
						<input type="hidden" value="<?php echo $enable_partial_redeem; ?>" name="vou_enable_partial_redeem" id="vou_enable_partial_redeem" />
					</div><?php 
				}?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_buyerinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $order_info_columns ) ) { //if product info is not empty ?>
	
		<h2><?php echo esc_html__( 'Order Information', 'woovoucher' );?></h2>
		
		<div class="woo_pdf_vou_main woo-vou-order-info">
			<div class="woo_pdf_vou_tit">
				<?php
				// Get product columns
				$order_col = count($order_info_columns);
				$order_col = 'col-'.$order_col;
				
				foreach ( $order_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $order_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				}?>
			</div>
			<div class="woo_pdf_vou_pro_lst">
				<?php
				foreach ( $order_info_columns as $col_key => $column ) {?>
					
					<div class="<?php echo $order_col; ?> woo_vou_padding"><?php
					
						$column_value = '';
						
						switch ( $col_key ) { 
							
							case 'order_id' :
								$column_value .= '<div class="woo_pdf_res_order">' . esc_html__( 'Order ID', 'woovoucher') . '</div>'. $order_id;
								break;
							
							case 'order_date' :
								$column_value .=  '<div class="woo_pdf_res_order">' . esc_html__( 'Order Date', 'woovoucher') . ' </div>' . $woo_vou_model->woo_vou_get_date_format( $order_date, true );
								break;
							
							case 'payment_method' : 
								$column_value .=  '<div class="woo_pdf_res_order">' . esc_html__( 'Payment Method', 'woovoucher') . '</div>' . $payment_method;
								break;
							
							case 'order_total':
								$column_value .=  '<div class="woo_pdf_res_order">' . esc_html__( 'Order Total', 'woovoucher') . '</div>' . wc_price($woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total(), $order_id)); // order total;
								break;
							
							case 'order_discount' : 
								$column_value .=  '<div class="woo_pdf_res_order">' . esc_html__( 'Order Discount', 'woovoucher') . '</div>' . wc_price( $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($order->get_total_discount() , $order_id ), array('currency' => woo_vou_get_order_currency($order)));
								break;
							
							default:
								$column_value .= '';
						}
						
						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id ); ?>
					</div><?php 
				}?>
			</div>
		</div><?php
	}
	
	do_action( 'woo_vou_after_orderinfo', $voucodeid, $item_id, $order_id ); 

	?>
	<input type="hidden" value="<?php echo $vou_code_total_price; ?>" name="vou_code_total_price" id="vou_code_total_price" />
	<input type="hidden" value="<?php echo $vou_code_total_redeemed_price; ?>" name="vou_code_total_redeemed_price" id="vou_code_total_redeemed_price" />
	<input type="hidden" value="<?php echo $vou_code_remaining_redeem_price; ?>" name="vou_code_remaining_redeem_price" id="vou_code_remaining_redeem_price" />

	<?php 
        $expiry_Date = (!empty($allvoucherdata['exp_date'])) ? $allvoucherdata['exp_date'] : '' ;
        $allow_expire = true;

        if( !empty( $expiry_Date ) ) {

            if( $expiry_Date < $woo_vou_model->woo_vou_current_date() ) {

                if( $allow_redeem_expired_voucher == "yes" ) { 
                	$allow_expire = true; 
                } else { 
                	$allow_expire = false; 
                }
            }
        }

        $voucher_data 		= get_post( $voucodeid ); // Get voucher data from voucher id
        $vou_used_codes 	= get_post_meta( $voucodeid, $prefix.'used_codes', true ); // Check voucher code is used voucher meta        

		/*
		* If user role is admin
		* If user role is voucher vendor then check the user is voucher post author 
		* OR checks whether "Enable Vendor to access all voucher codes" is tick
		* If user is customer of this voucher's order and check logged in users can Check and Redeem allowed
		*/
        $allow_user_redeem 	= false;
		if( in_array( $user_role, $admin_roles ) ) {
			$allow_user_redeem = true;
		} elseif( in_array( $user_role, $woo_vou_vendor_role ) ) {

			if ( ( $voucher_data->post_author == $current_user->ID ) || ( $vou_enable_vendor_access_all_voucodes == 'yes' ) || ( $vou_allow_secondary_vendor_redeem_primary_voucher == 'yes' && $voucher_data->post_author != $current_user->ID  ) ){
				$allow_user_redeem = true;
			}
		} elseif( $order_customer == $current_user->ID ) {
			if( ( $vou_enable_logged_user_check_voucher_code == 'yes' ) && ( $vou_enable_logged_user_redeem_vou_code == 'yes' ) ){
				$allow_user_redeem = true;
			}
		}

        /**
         * If voucher code allow expire
         * Check allow user redeem
         * If voucher code is not used
         * If partial redeem enabled then show partial redeem option
		 * If condition is satisifed than logged-in user is non-admin and either non-vendor or not allowed to access that voucher code
		 * If condition is satisfied than it hide redeem button else shows it
		 */
		
        if( $allow_expire && $allow_user_redeem
	        && ( empty($vou_used_codes) )
	        && ( $enable_partial_redeem == "yes" ) 
	        && ( (int)$vou_code_remaining_redeem_price > 0 )
			||  ( $enable_partial_redeem == "yes" && apply_filters('woo_vou_access_partial_redeem_without_login', false))
         ) {
	?>
		
		<div class="woo_pdf_vou_main">
			<h2><?php esc_html_e( 'Redeem Options', 'woovoucher' ); ?></h2>
			<div class="woo_pdf_vou_pro_lst woo-vou-margin-bottom-0">
				<div class="col-6 woo_vou_padding">
					<label for="vou_redeem_method"><?php esc_html_e( 'Redeem Method', 'woovoucher' ); ?></label>
				</div>
				<div class="col-2 woo_vou_padding">
					<select name="vou_redeem_method" id="vou_redeem_method">
					<?php
						foreach ( $redeem_methods as $key => $value ) {
							echo '<option value="'. $key .'">'. $value .'</option>';
						}?>
					</select><br/>
					<?php
					$partially_redeemed = get_post_meta( $voucodeid, $prefix . 'redeem_method', true );
					if( !empty( $partially_redeemed ) && $partially_redeemed == 'partial' ) { ?>
						<span class="description"><?php echo sprintf( esc_html__( 'If you select %sFull%s method then it will redeem remaining amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
					<?php } else { ?>
						<span class="description"><?php echo sprintf( esc_html__( 'If you select %sFull%s method then it will redeem full amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
					<?php } ?>					
				</div>
			</div>			
			<div class="woo_pdf_vou_pro_lst woo-vou-partial-redeem-amount woo-vou-margin-bottom-0">
				<div class="col-6 woo_vou_padding">
					<label for="vou_partial_redeem_amount"><?php esc_html_e( 'Redeem Amount', 'woovoucher' ); ?></label>
				</div>
				<div class="col-2 woo_vou_padding">
					<input type="number" name="vou_partial_redeem_amount" id="vou_partial_redeem_amount" value="<?php echo $vou_code_remaining_redeem_price; ?>" max="<?php echo $vou_code_remaining_redeem_price; ?>" step="any"/><br />
					<span class="description"><?php esc_html_e( 'Enter the amount you want to redeem.', 'woovoucher' ); ?></span>
					<div class="woo-vou-voucher-code-msg woo-vou-voucher-code-error"></div>
				</div>
			</div>
		</div>
		<?php
		
		do_action( 'woo_vou_after_redeem_options', $voucodeid, $item_id, $order_id ); 		
	} ?>
		
</div>