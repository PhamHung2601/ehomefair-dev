<?php
/**
 * Handels to show change voucher code redeem popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	global $post;
		
?>
<div class="woo-vou-popup-content woo-vou-voucher-redeem-content">
				
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php esc_html_e( 'Redeem Voucher Code', 'woovoucher' ); ?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png'; ?>" alt="<?php esc_html_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>
		
	<div class="woo-vou-popup">

		<div class="woo-vou-voucher-redeem-errors"></div>
		<form method="POST" action="" enctype="multipart/form-data" id="woo_vou_voucher_redeem">
			<table class="form-table woo-vou-voucher-voucher-redeem-table">
				<tbody>
					<tr>
						<td colspan="2" class="woo-vou-voucher-code-msg"></td>
					</tr>
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Voucher Code', 'woovoucher' ); ?></td>
						<td>
							<strong class="woo-vou-voucher-code"></strong>
						</td>
					</tr>
					<tr>
						<td scope="col" class="woo-vou-field-price"><?php esc_html_e( 'Price', 'woovoucher' ); ?></td>
						<td class="woo-vou-voucher-price">
						</td>
					</tr>
					<tr class="woo-vou-voucher-redeem-method"></tr>
					<tr class="woo-vou-voucher-redeem-amount woo-vou-partial-redeem-amount "></tr>
					<tr class="woo-vou-code-redeem-submit-wrap">
						<td scope="col"></td>
						<td>
							<input id="woo_voucher_id" name="woo_vou_voucher_id" type="hidden">
							<input id="woo_voucher_code" name="woo_vou_voucher_code" type="hidden">
							<?php echo apply_filters('woo_vou_reddem_button','<input id="woo_vou_voucher_code_redeem" type="button" class="woo-vou-voucher-redeem-btn button-primary" value="'.esc_html__( 'Redeem', 'woovoucher' ).'" />') ?>
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

<div class="woo-vou-popup-overlay woo-vou-voucher-redeem-overlay"></div>