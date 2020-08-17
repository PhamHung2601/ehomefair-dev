<?php
/**
 * Handels to show change voucher code expry date popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	global $post;
		
?>
<div class="woo-vou-popup-content woo-vou-expiry-date-content">
				
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php esc_html_e( 'Change Expiry Date', 'woovoucher' ); ?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png'; ?>" alt="<?php esc_html_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>
		
	<div class="woo-vou-popup">

		<div class="woo-vou-expiry-errors"></div>
		<form method="POST" action="" enctype="multipart/form-data" id="woo_vou_voucher_expiry_date">
			<table class="form-table woo-vou-voucher-expiry-date-table">
				<tbody>
					<tr>
						<td colspan="2"> </td>
					</tr>
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Voucher Code', 'woovoucher' ); ?></td>
						<td>
							<strong class="woo-vou-voucher-code"></strong>
						</td>
					</tr>
					<tr>
						<td colspan="2"> </td>
					</tr>
					
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Expiry Date', 'woovoucher' ); ?></td>
						<td>
							<input id="woo_vou_exp_datetime" name="woo_vou_exp_datetime" class="woo-vou-change-exp-datetime" rel="yy-mm-dd" placeholder="YYYY-MM-DD H:I A" value="" type="text">
							<input id="woo_voucher_id" name="woo_vou_voucher_id" type="hidden">
						</td>
					</tr>
					<tr>
						<td colspan="2"> </td>
					</tr>
					
					<tr>
						<td scope="col"></td>
						<td>
							<input type="button" class="woo-vou-voucher-expiry-btn button-secondary" value="<?php esc_html_e( 'Change Date', 'woovoucher' ); ?>" />
							<img class="woo-vou-loader" src="<?php echo esc_url(WOO_VOU_URL) . 'includes/images/ajax-loader.gif'; ?>" alt="<?php esc_html_e('Loading...', 'woovoucher'); ?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2"> </td>
					</tr>
				</tbody>
				
			</table>
		</form>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-popup-content-->

<div class="woo-vou-popup-overlay woo-vou-expiry-date-overlay"></div>