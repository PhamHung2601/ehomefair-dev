<?php
/**
 * File for user profile.
 *
 * @package wk-woocommerce-marketplace/includes/templates/admin/account
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*----------*/ /*---------->>> User Profile Fields <<<----------*/ /*----------*/
$disable = '';
$address = '';
$style   = '';

$payment_information = array();

foreach ( $user->roles as $key => $value ) {
	if ( $value == 'wk_marketplace_seller' ) {
		$address = get_user_meta( $user->ID, 'shop_address', true );
		if ( $address ) {
			$style   = '';
			$disable = 'disabled';
		}
	} else {
		$style   = 'style=display:none;';
		$disable = '';
	}
}

if ( null !== get_user_meta( $user->ID, 'mp_seller_payment_details') ) {
	$payment_information = get_user_meta( $user->ID, 'mp_seller_payment_details', true );
}

?>
<div class="mp-seller-details" <?php echo esc_html( $style ); ?>>
	<h3 class="heading"><?php esc_html_e( 'Marketplace Seller Details', 'marketplace' ); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="company-name">
					<?php esc_html_e( 'Shop Name', 'marketplace' ); ?> <span  style="display:inline-block;" class="required">*</span>
				</label>
			</th>
			<td>
				<input type="text" class="input-text form-control" name="shopname" id="org-name" value="<?php echo get_user_meta( $user->ID, 'shop_name', true ); ?>" required="required"/>
			</td>
		</tr>

		<tr>
			<th><label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'marketplace' ); ?> <span class="required" style="display:inline-block;">*</span></label></th>
			<td>
				<input type="text" class="input-text form-control" name="shopurl" placeholder="<?php esc_html_e('eg- webkul', 'marketplace'); ?>" id="seller-shop" value="<?php echo get_user_meta( $user->ID, 'shop_address', true ); ?>" required="required" <?php echo esc_html( $disable ); ?>>
				<p><strong id="seller-shop-alert-msg" class="pull-right"></strong></p>
			</td>
		</tr>

		<?php
		if ( !empty($payment_information) ) 
		{
			?>
			<tr>
				<th>
					<label for="seller-payment-info" class="pull-left">
						<?php esc_html_e( 'Payment Information', 'marketplace' ); ?>
					</label>
				</th>
				<td>
					<?php echo esc_html( $payment_information ); ?><br>
				</td>
			</tr>
		<?php 
		}
		?>
	</table>
</div>
