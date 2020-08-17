<?php
/**
 * Used Voucher Code
 * 
 * The html markup for the used voucher code popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model, $woo_vou_voucher;

$prefix = WOO_VOU_META_PREFIX;
$postid = apply_filters( 'woo_vou_edit_product_id', $postid, get_post( $postid ) );

//Get Voucher Details by post id
$used_posts_per_page 	= apply_filters( 'woo_vou_used_code_popup_per_page', 10 ); // Apply filter to change per page records
$used_paged 			= 1; // Declare paged to default 1

// Get used codes for current page and total
$usedcodes 		= woo_vou_get_used_codes_by_product_id( $postid, $used_posts_per_page, $used_paged );
?>
<!-- HTML for USED codes popup -->
<div class="woo-vou-popup-content woo-vou-used-codes-popup">
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php echo esc_html__( 'Redeemed Voucher Codes', 'woovoucher' );?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png';?>" alt="<?php echo esc_html__( 'Close', 'woovoucher' );?>"></a></div>
	</div>
	<?php
	$generatpdfurl 	= add_query_arg( array( 'woo-vou-used-gen-pdf' => '1', 'product_id' => $postid, 'woo_vou_action'	=> 'used' ) );
	$exportcsvurl 	= add_query_arg( array( 'woo-vou-used-exp-csv' => '1', 'product_id' => $postid, 'woo_vou_action' => 'used' ) );

	//used codes table columns
	$usedcodes_columns	= apply_filters( 'woo_vou_product_usedcodes_columns', array(
													'voucher_code'	=> esc_html__( 'Voucher Code', 'woovoucher' ),
													'buyer_info'	=> esc_html__( 'Buyer\'s Information', 'woovoucher' ),
													'order_info'	=> esc_html__( 'Order Information', 'woovoucher' ),
													'redeem_info'		=> esc_html__( 'Redeem Information', 'woovoucher' )
												), $postid );
	?>
	<div class="woo-vou-popup used-codes">
		<div>
			<a href="<?php echo esc_url($exportcsvurl);?>" id="woo-vou-export-csv-btn" class="button-secondary" title="<?php echo esc_html__( 'Export CSV', 'woovoucher' );?>"><?php echo esc_html__( 'Export CSV', 'woovoucher' );?></a>
			<a href="<?php echo esc_url($generatpdfurl);?>" id="woo-vou-pdf-btn" class="button-secondary" title="<?php echo esc_html__( 'Generate PDF', 'woovoucher' );?>"><?php echo esc_html__( 'Generate PDF', 'woovoucher' );?></a>
		</div>
		<div class="woo-vou-table-wrap">
			<table id="woo_vou_used_codes_table" class="form-table" border="1">
				<tbody>
					<tr>
					<?php
						if( !empty( $usedcodes_columns ) ) {
							foreach ( $usedcodes_columns as $column_key => $column ) {
								echo '<th scope="row" class="' . $column_key . '">' . $column . '</th>';
							}
						}?>
					</tr><?php 
					
					if( !empty( $usedcodes ) &&  count( $usedcodes ) > 0 ) { 
						
						foreach ( $usedcodes as $key => $voucodes_data ) { 

							$orderid	= $voucodes_data['order_id']; // voucher order id
							$user_id	= $voucodes_data['redeem_by']; // get user id

							if( !empty( $usedcodes_columns ) ) {
								echo '<tr>';

								// Looping on used codes array
								foreach ( $usedcodes_columns as $column_key => $column ) {
									
									$column_value = '';
									
									switch( $column_key ) {

										case 'voucher_code': // voucher code purchased
											$column_value	= $voucodes_data['vou_codes'];
											break;
										case 'buyer_info': // buyer's info who has used voucher code
											$column_value 	= '<div id="buyer_voucher_'.$voucodes_data['voucode_id'].'">';
											$buyer_info 	= $woo_vou_model->woo_vou_get_buyer_information( $orderid );
											$column_value 	.= woo_vou_display_buyer_info_html( $buyer_info );
											$column_value 	.= '<a class="woo-vou-show-buyer" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
											$column_value 	.= '</div>';
											break;
										case 'order_info': // voucher order info
											$column_value 	= '<div id="order_voucher_'.$voucodes_data['voucode_id'].'">';
											$column_value 	.= woo_vou_display_order_info_html( $orderid );
											$column_value 	.= '<a class="woo-vou-show-order" data-voucherid="'.$voucodes_data['voucode_id'].'">'.esc_html__( 'Show', 'woovoucher' ).'</a>';
											$column_value 	.= '</div>';
											break;
										case 'redeem_info' :
											$column_value 	= woo_vou_display_redeem_info_html( $voucodes_data['voucode_id'], $orderid, '' );
											break;
									}
									
									$column_value = apply_filters( 'woo_vou_product_usedcodes_column_value', $column_value, $voucodes_data, $postid );?>
									<td><?php echo $column_value;?></td><?php 
								}
								echo '</tr>';
							}
						}
					} else { ?>
						<tr>
							<td colspan="4"><?php echo esc_html__( 'No voucher codes used yet.', 'woovoucher' );?></td>
						</tr><?php
					}?>
				</tbody>
			</table>
		</div>
		<?php 

		// Generating HTML for loading more used codes with all required information
		if( !empty( $usedcodes ) ) { ?>

			<div class="woo-vou-used-load-more woo-vou-load-more-wrap">
				<input type="hidden" id="woo_vou_used_post_id" value="<?php echo $postid; ?>">
				<input type="hidden" id="woo_vou_used_paged" value="<?php echo $used_paged; ?>">
				<input type="hidden" id="woo_vou_used_postsperpage" value="<?php echo $used_posts_per_page; ?>">
				<input id="woo_vou_used_load_more_btn" class="woo-vou-used-load-more-btn button-primary" value="<?php echo esc_html__( 'Load More', 'woovoucher' );?>" id="woo_vou_used_load_more_btn" type="button">
				<div class="woo-vou-used-popup-loader"><img src="<?php echo esc_url(WOO_VOU_IMG_URL).'/ajax-loader-2.gif' ?>"></div>
				<div class="woo-vou-used-popup-overlay"></div>
			</div>
		<?php } ?>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-used-codes-popup-->
<div class="woo-vou-popup-overlay woo-vou-used-codes-popup-overlay"></div>