<?php
/**
 * Display Voucher Data within order meta
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model, $post;

//model class
$model			= $woo_vou_model;
$prefix = WOO_VOU_META_PREFIX;
$order_id		= isset( $post->ID ) ? $post->ID : '';

$allorderdata	= $model->woo_vou_get_all_ordered_data( $order_id );

//get cart details
$cart_details 	= new Wc_Order( $order_id );
$order_items	= $cart_details->get_items();

$woo_vou_order_lang = get_post_meta( $order_id, 'wpml_language', true );

$simple_product_post_type 		= 'product';
$variation_product_post_type 	= 'product_variation';

//get meta prefix
$prefix = WOO_VOU_META_PREFIX;

if( !empty( $order_items ) ) {// Check cart details are not empty

?>
	<table class="widefat woo-vou-history-table">
		<tr class="woo-vou-history-title-row">
			<th width="8%"><?php echo esc_html__( 'Logo', 'woovoucher' ); ?></th>
			<th width="17%"><?php echo esc_html__( 'Product Title', 'woovoucher' ); ?></th>
			<th width="15%"><?php echo esc_html__( 'Code', 'woovoucher' ); ?></th>
			<th width="45%"><?php echo esc_html__( 'Vendor Data', 'woovoucher' ); ?></th>
			<?php do_action( 'woo_vou_history_table_before_expires_title' ); ?>
			<th width="10%"><?php echo esc_html__( 'Expires', 'woovoucher' ); ?></th>
			<th width="5%"><?php echo esc_html__( 'Qty', 'woovoucher' ); ?></th>
		</tr><?php
		
		foreach ( $order_items as $item_id => $product_data ) {
			
			
			//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
			$_product	= apply_filters( 'woocommerce_order_item_product', $cart_details->get_product_from_item( $product_data ), $product_data );

			if( $product_data['variation_id'] > 0 ) {
				// Replace "variation_id" from product_data, if it's greater than 0
				$product_data['variation_id'] = apply_filters( 'woo_vou_before_admin_vou_download_link', $product_data['variation_id'], $variation_product_post_type, $woo_vou_order_lang );
			} else {
				// Else Replace "product_id" from product_data
				$product_data['product_id'] = apply_filters( 'woo_vou_before_admin_vou_download_link', $product_data['product_id'], $simple_product_post_type, $woo_vou_order_lang );
			}

			// Get download files
			if( !$_product ) { //If product deleted
				$download_file_data = array();
			} else {
			 	$download_file_data = $model->woo_vou_get_item_downloads_from_order($cart_details, $product_data);
			}

			//Get product ID
			$product_id			= $product_data['product_id'];
			
			//get all voucher details from order meta
			$allvoucherdata = apply_filters( 'woo_vou_order_voucher_metadata', isset( $allorderdata[$product_id] ) ? $allorderdata[$product_id] : array(), $order_id, $item_id, $product_id );
			
			//Get product item meta
			$product_item_meta	= isset( $product_data['item_meta'] ) ? $product_data['item_meta'] : array();
			
			//Get voucher code from item meta "Now we store voucher codes in item meta fields"
			$codes_item_meta	= wc_get_order_item_meta( $item_id, $prefix.'codes' );

			//Get vendor address
			$vendor_address_data = esc_html__( 'N/A', 'woovoucher' );
			if( isset($product_data['variation_id']) && !empty($product_data['variation_id']) && !empty($allvoucherdata['vendor_address']) && is_array($allvoucherdata['vendor_address']) ){

			    $vendor_address_data = nl2br( $allvoucherdata['vendor_address'][$product_data['variation_id']] );
				
			} elseif( !empty($allvoucherdata['vendor_address']) ) {
			    $vendor_address_data = nl2br( $allvoucherdata['vendor_address'] );
			}

			if( isset($product_data['variation_id']) && !empty($product_data['variation_id'] ) ) {
				$exp_date = !empty($allvoucherdata['exp_date'][$product_data['variation_id']] ) ? $model->woo_vou_get_date_format( $allvoucherdata['exp_date'][$product_data['variation_id']], true ) : esc_html__( 'N/A', 'woovoucher' );
			} else{
				
				$exp_date = !empty( $allvoucherdata['exp_date'] ) ? $model->woo_vou_get_date_format( $allvoucherdata['exp_date'], true ) : esc_html__( 'Never Expire', 'woovoucher' );
			}

			if( !empty( $codes_item_meta ) ) { // Check Voucher Data are not empty ?>
				
				<tr>
					<td class="woo-vou-history-td"><?php if( !empty( $allvoucherdata['vendor_logo']['src'] ) ){?><img src="<?php echo esc_url($allvoucherdata['vendor_logo']['src']); ?>" alt="" width="70" height="30" /><?php } else{ echo esc_html__( 'N/A', 'woovoucher' );}?></td>
					<td class="woo-vou-history-td"><?php
							
							if( !empty( $_product ) ) {
									echo '<a href="'.esc_url( admin_url( 'post.php?post=' . absint( $product_id ) . '&action=edit' ) ).'">' . $product_data['name'] . '</a>';
							} else {
								echo $product_data['name'];
							}

							echo $model->woo_vou_display_product_item_name( $product_data, $_product );
							
							if( !empty( $download_file_data ) ) {
								foreach ( $download_file_data as $key => $download_file ){
									
									$check_key = strpos( $key, 'woo_vou_pdf_' );
									
									if( !empty( $download_file ) && $check_key !== false ) {
										
										//Get download URL
										$download_url	= $download_file['download_url'];
										
										//Remove order query arguments
										$download_url	= remove_query_arg( 'order', $download_url );
										
										//add arguments array
										$add_arguments	= array(
																'woo_vou_admin'		=> true,
																'woo_vou_order_id'	=> $order_id,
																'item_id'	=> $item_id
															);
										
										//PDF Download URL
										$download_url	= add_query_arg( $add_arguments, $download_url );
										
										echo '<div><a href="'.esc_url($download_url).'" target="_blank">'.$download_file['name'].'</a></div>';
									}
								}
							}
						?>
					</td>
					<td class="woo-vou-history-td"><?php

					$voucher_codes = explode(',', $codes_item_meta);

					foreach ( $voucher_codes as $key => $voucher_code) {

						$vou_code_args = array('fields' => 'ids');
						$vou_code_args['meta_query'] = array(
										'relation' => 'OR',
						                array(
						                    'key' => $prefix . 'purchased_codes',
						                    'value' => trim($voucher_code)
						                ),
						                array(
						                    'key' => $prefix . 'used_codes',
						                    'value' => trim($voucher_code)
						                )
						            );

							$voucodedata = woo_vou_get_voucher_details($vou_code_args);
							if( !empty( $voucodedata ) ) {
								//add arguments array
								$add_arguments	= array(
														'page'		=> 'woo-vou-codes',
														'vou_code'	=> $voucodedata[0],
													);
													
													//PDF Download URL
								$voucher_url = add_query_arg( $add_arguments, admin_url('admin.php') );
								
								 echo '<a href="'.$voucher_url.'" target="_blank">'.apply_filters ( 'woo_vou_admin_order_meta_voucodes', $voucher_code, $allvoucherdata, $product_data, $item_id ).'</a><br>';
							} else{

								echo apply_filters ( 'woo_vou_admin_order_meta_voucodes', $voucher_code, $allvoucherdata, $product_data, $item_id ).'<br>';
							}
						}
						?>						 	
						 </td>
					<td class="woo-vou-history-td">
						<p><strong><?php esc_html_e( 'Vendor\'s Address', 'woovoucher' ); ?></strong></p>
						<p><?php echo $vendor_address_data; ?></p>
						<p><strong><?php esc_html_e( 'Site URL', 'woovoucher' ); ?></strong></p>
						<p class="woo-vou-voucher-website-url"><?php echo !empty( $allvoucherdata['website_url'] ) ? $allvoucherdata['website_url'] : esc_html__( 'N/A', 'woovoucher' ); ?></p>
						<p><strong><?php esc_html_e( 'Redeem Instructions', 'woovoucher' ); ?></strong></p>
						<p><?php echo !empty( $allvoucherdata['redeem'] ) ? nl2br( $allvoucherdata['redeem'] ) : esc_html__( 'N/A', 'woovoucher' ); ?></p><?php
						
						if( !empty( $allvoucherdata['avail_locations'] ) ) {
							
							echo '<p><strong>' . esc_html__( 'Locations', 'woovoucher' ) . '</strong></p>';
							
							foreach ( $allvoucherdata['avail_locations'] as $location ) {
								
								if( !empty( $location[$prefix.'locations'] ) ) {
									
									if( !empty( $location[$prefix.'map_link'] ) ) {
										echo '<p><a target="_blank" class="woo-vou-voucher-location-link" href="' . esc_url($location[$prefix.'map_link']) . '">' . $location[$prefix.'locations'] . '</a></p>';
									} else {
										echo '<p>' . $location[$prefix.'locations'] . '</p>';
									}
								}
							}
						}
						do_action('woo_vou_meta_history_vou_data', $item_id);
						?>
					</td>
					<?php do_action( 'woo_vou_history_table_before_expires', $codes_item_meta ); ?>
					<td class="woo-vou-history-td">
					<?php	echo $exp_date; ?>
					
					</td>
					<td class="woo-vou-history-td"><?php echo $product_data['qty']; ?></td>
				</tr><?php 
			}
		}?>
	</table><?php
	do_action( 'woo_vou_after_history_table', $codes_item_meta );
}