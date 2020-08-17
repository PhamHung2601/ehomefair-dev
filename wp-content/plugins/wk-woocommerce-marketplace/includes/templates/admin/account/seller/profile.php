<?php
/**
 * The file contains seller profile detail template.
 *
 * @package Woocommerce Marketplace.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$seller          = get_user_by( 'ID', $seller_id );
$payment_details = get_user_meta( $seller_id, 'mp_seller_payment_details', true );
if ( empty( $payment_details ) ) {
	$payment_details = 'No info. provided.';
}
?>
<div class="mp-seller-detail">
	<div class="mp-seller-data-wrapper">
		<div class="mp-seller-data">
			<table>
				<thead>
				</thead>
				<tbody>
					<tr>
						<td>
							<p>
								<b><?php echo esc_html__( 'Username :', 'marketplace' ); ?></b>
							</p>
						</td>
						<td>
							<p>
								<?php echo esc_html( $seller->user_login ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>
								<b><?php echo esc_html__( 'Email :', 'marketplace' ); ?></b>
							</p>
						</td>
						<td>
							<p>
								<?php echo esc_html( $seller->user_email ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>
								<b><?php echo esc_html__( 'Display name :', 'marketplace' ); ?></b>
							</p>
						</td>
						<td>
							<p>
								<?php echo esc_html( $seller->display_name ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>
								<b><?php echo esc_html__( 'Shop Address :', 'marketplace' ); ?></b>
							</p>
						</td>
						<td>
							<p>
								<?php echo get_user_meta( $seller_id, 'shop_address', true ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td class="top-align">
							<p>
								<b><?php echo esc_html__( 'Payment Details :', 'marketplace' ); ?></b>
							</p>
						</td>
						<td>
							<p>
								<?php echo esc_html( $payment_details ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
