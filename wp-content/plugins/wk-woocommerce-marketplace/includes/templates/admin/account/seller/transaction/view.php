<?php
/**
 * This file handles single transaction detail view template.
 *
 * @package Woocommerce Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$order_ids = array();
$ord_id    = maybe_unserialize( $order_id );

$order      = wc_get_order( $ord_id );
$currency = ($order->get_currency()) ? $order->get_currency() : get_woocommerce_currency();
?>
<div class="woocommerce-account">
	<?php do_action( 'mp_get_wc_account_menu', 'marketplace' ); ?>
	<div class="wk-transaction-view woocommerce-MyAccount-content">
		<div class="wk-mp-transaction-info-box">
				<div>
					<h3>
						<?php echo esc_html__( 'Transaction Id', 'marketplace' ); ?> - <?php echo esc_html( $transaction_id ); ?>
					</h3>
						<div class="box">
							<div class="box-title">
									<h3><?php echo esc_html__( 'Information', 'marketplace' ); ?></h3>
							</div>
							<fieldset>
								<div class="box-content">
										<div class="wk_row">
											<span class="label"><?php echo esc_html__( 'Date', 'marketplace' ); ?> : </span>
											<span class="value"><?php echo date( 'F j, Y', strtotime( $transaction_date ) ); ?></span>
										</div>
										<div class="wk_row">
											<span class="label"><?php echo esc_html__( 'Amount', 'marketplace' ); ?> : </span>
											<span class="value"><span class="price"><?php echo wc_price( $amount, array('currency' => $currency ) ); ?></span></span>
										</div>
										<div class="wk_row">
											<span class="label"><?php echo esc_html__( 'Type', 'marketplace' ); ?> : </span>
											<span class="value"><?php echo esc_html( $type ); ?></span>
										</div>
										<div class="wk_row">
											<span class="label"><?php echo esc_html__( 'Method', 'marketplace' ); ?> : </span>
											<span class="value"><?php echo esc_html( $method ); ?></span>
										</div>
								</div>
							</fieldset>
						</div>
				</div>
		</div>
		<div class="transaction-details">
			<div class="table-wrapper">
				<h3 class="table-caption">
					<?php echo esc_html__( 'Detail', 'marketplace' ); ?>
				</h3>
				<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
					<thead>
						<tr>
							<?php foreach ( $columns as $column_id => $column_name ) : ?>
								<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
							<?php endforeach; ?>
						</tr>
					</thead>

					<tbody>
						<?php
						global $commission;
						$current_user  = wp_get_current_user();
						$role_name     = $current_user->roles;
						if ( ! in_array( 'wk_marketplace_seller', $role_name, true ) ) {
							$seller_id  = $_GET['sid'];
						}else {
							$seller_id = get_current_user_id();
						}

						$item_count = $order->get_items();

						$sel_info     = $commission->get_seller_final_order_info( $ord_id, $seller_id );
						$product_name = '';

						foreach ( $sel_info['product'] as $pro_nme ) {
							if ( ! empty( $product_name ) ) {
								$product_name = $product_name . ' + ';
							}
							$product_name = $product_name . $pro_nme['title'];
						}

						$sel_rwd_note = '';
						if ( ! empty( $sel_info['reward_data'] ) ) {
							if ( ! empty( $sel_info['reward_data']['seller'] ) ) {
								$sel_rwd_note = ' ' . wc_price( $sel_info['reward_data']['seller'], array('currency' => $currency ) ) . '( ' . __( 'Reward', 'marketplace' ) . ' )';
							}
						}

						$admin_rwd_note = '';
						if ( ! empty( $sel_info['reward_data'] ) ) {
							if ( ! empty( $sel_info['reward_data']['admin'] ) ) {
								$admin_rwd_note = ' + ' . wc_price( $sel_info['reward_data']['admin'], array('currency' => $currency ) ) . '( ' . __( 'Reward', 'marketplace' ) . ' )';
							}
						}

						$sel_walt_note = '';
						if (!empty($sel_info['wallet_data'])) {
							if (!empty($sel_info['wallet_data']['seller'])) {
								$sel_walt_note = ' ' . wc_price($sel_info['wallet_data']['seller'], array('currency' => $currency )) . '( ' . __('Wallet', 'marketplace') . ' )';
							}
						}

						$quantity          = $sel_info['quantity'];
						$line_total        = $sel_info['product_total'] + $sel_info['shipping'];
						$commission_amount = $sel_info['total_commission'];
						$subtotal          = $sel_info['total_seller_amount'];
						?>
						<tr>
							<td>
								<?php echo esc_html_x( '#', 'hash before order number', 'marketplace' ) . intval( $order->get_order_number() ); ?>
							</td>
							<td>
								<?php echo esc_html( $product_name ); ?>
							</td>
							<td>
								<?php echo esc_html( $quantity ); ?>
							</td>
							<td>
								<?php echo wc_price( $line_total, array('currency' => $currency ) ); ?>
							</td>
							<td>
								<?php echo wc_price( $commission_amount, array('currency' => $currency ) ) . ' ' . $admin_rwd_note; ?>
							</td>
							<td>
								<?php
								echo wc_price($subtotal, array('currency' => $currency ));
								if ($subtotal != $line_total) {
									$tip = '<p>';
									$tip .= wc_price($subtotal, array('currency' => $currency ));
									$tip .= ' = ';
									$tip .= wc_price($line_total, array('currency' => $currency ));
									if (!empty($commission_amount)) {
										$tip .= ' - ';
										$tip .= wc_price( $commission_amount, array('currency' => $currency ) ) . ' ( ' . __('Commission', 'marketplace') . ' ) ';
									}
									if (!empty($sel_rwd_note)) {
										$tip .= ' - ';
										$tip .= $sel_rwd_note;
									}
									if (!empty($sel_walt_note)) {
										$tip .= ' - ';
										$tip .= $sel_walt_note;
									}
									$tip .= ' ';
									$tip .= '</p>';
									echo wc_help_tip($tip, true);
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
