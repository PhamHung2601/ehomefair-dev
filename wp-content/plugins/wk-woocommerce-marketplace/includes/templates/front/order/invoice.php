<?php
/**
 * Template Name: Invoice Page
 *
 * @package wk-woocommerce-marketplace/includes/templates/front/order
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

global $wpdb;

$order_id = base64_decode($order_id);

$user_id = get_current_user_id();

if ($user_id == 1) {

	global $wp_query;

	$wp_query->set_404();

	status_header(404);

	get_template_part(404);

	exit();
}

try {
	$order = new WC_Order($order_id);

	$order_refund = new MP_Order_Refund();

	$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );

	$order_detail_by_order_id = array();

	$get_item = $order->get_items();

	$cur_symbol = get_woocommerce_currency_symbol($order->get_currency());

	$order_detail = $wpdb->get_results("select DISTINCT woitems.order_id from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author='" . $user_id . "' order by woitems.order_id DESC");

	foreach ($order_detail as $order_dtl) {

		$orderr_id[] = $order_dtl->order_id;
	}

	if (!in_array($order_id, $orderr_id, true)) {

		global $wp_query;

		$wp_query->set_404();

		status_header(404);

		get_template_part(404);

		exit();
	}

	foreach ($get_item as $key => $value) {

		$value_data = $value->get_data();

		$product_id = $value->get_product_id();

		$variable_id = $value->get_variation_id();

		$product_total_price = $value_data['total'];

		$qty = $value_data['quantity'];
		$item_data = array();

		$post = get_post( $product_id );
		$meta_data = $value->get_meta_data();

		if ( ! empty( $meta_data ) ) {
			foreach ( $meta_data as $key1 => $value1 ) {
				$item_data[] = $meta_data[ $key1 ]->get_data();
			}
		}

		if ($post->post_author == $user_id) {

			$order_detail_by_order_id[$product_id][] = array(
				'product_name' => $value['name'],
				'qty' => $qty,
				'variable_id' => $variable_id,
				'product_total_price' => $product_total_price,
				'meta_data'           => $item_data,
			);
		}
	}

	$shipping_method = $order->get_shipping_method();

	$payment_method = $order->get_data()['payment_method_title'];

	$total_payment = 0;

	$current_user = wp_get_current_user();

	$wpmp_invoice_obj = new MP_Form_Handler();

	$seller_detail = get_seller_details($current_user->ID);

	?>

	<!DOCTYPE html>

	<html>

	<head>

		<title><?php echo esc_html__('Seller Order Invoice', 'marketplace'); ?></title>

		<link rel="stylesheet" href="<?php echo WK_MARKETPLACE . 'assets/css/invoice-style.css'; ?>">

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

	</head>

	<body>

		<div class="mp-invoice-wrapper">

			<button onclick="javascript:window.print()"><i class="fa fa-print"></i></button>

			<h1><?php echo esc_html__('Seller Order Invoice Of Order', 'marketplace') . ' #'; ?><?php echo $order_id; ?></h1>

			<table class="table table-bordered">

				<thead>

					<tr>

						<td colspan="2"><b><?php echo esc_html__('Order Information', 'marketplace'); ?></b></td>

					</tr>

				</thead>

				<tbody>

					<tr>

						<td style="width: 50%;">
							<b><?php echo ucfirst($seller_detail['shop_name'][0]); ?></b><br>
							<?php echo ucfirst($current_user->user_firstname) . '&nbsp;' . ucfirst($current_user->user_lastname); ?><br>

							<?php if (isset($seller_detail['billing_city'][0]) && $seller_detail['billing_city'][0]) {
								echo $seller_detail['billing_city'][0] . ',';
							}
							?>
							<?php if (isset($seller_detail['billing_country'][0]) && $seller_detail['billing_country'][0]) {
								echo $seller_detail['billing_country'][0] . '<br>';
							}
							?>

							<b><?php echo esc_html__('Email', 'marketplace') . ' :'; ?></b> <?php echo $current_user->user_email; ?><br>

							<b><?php echo esc_html__('Profile Link', 'marketplace') . ' :'; ?></b> <a href="<?php echo site_url() . '/seller/' . get_option( 'mp_store', 'store' ) . '/' . $seller_detail['shop_address'][0]; ?>" target="_blank"><?php echo esc_url(site_url() . '/seller/' . get_option( 'mp_store', 'store' ) . '/' . $seller_detail['shop_address'][0]); ?></a>

						</td>

						<td style="width: 50%;">

							<b><?php echo esc_html__('Order Date', 'marketplace') . ' :'; ?></b> <?php echo $order->get_date_created(); ?><br>

							<b><?php echo esc_html__('Order ID', 'marketplace') . ' :'; ?> </b> <?php echo $order_id; ?><br>

							<b><?php echo esc_html__('Payment Method', 'marketplace') . ' :'; ?></b> <?php echo $payment_method; ?><br>

							<?php if (!empty($shipping_method)) : ?>
								<b><?php echo esc_html__('Shipping Method', 'marketplace') . ' :'; ?></b> <?php echo $shipping_method; ?><br>
							<?php endif; ?>

						</td>

					</tr>

				</tbody>

			</table>

			<table class="table table-bordered">

				<tbody>

					<tr>

						<td colspan="2"><b><?php echo esc_html__("Buyer Details", "marketplace"); ?></b></td>

					</tr>

					<tr>

						<td><b><?php echo esc_html__("Name", "marketplace"); ?></b></td>

						<td data-title="Name"><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>

					</tr>

					<tr>

						<td><b><?php echo esc_html__("Email", "marketplace"); ?></b></td>

						<td data-title="Email"><?php echo $order->get_billing_email(); ?></td>

					</tr>

					<tr class="alt-table-row">

						<td><b><?php echo esc_html__("Telephone", "marketplace"); ?></b></td>

						<td data-title="Telephone"><?php echo $order->get_billing_phone(); ?></td>

					</tr>

				</tbody>

			</table>

			<table class="table table-bordered">

				<thead>

					<tr>

						<td style="width: 50%;"><b><?php esc_html_e("Billing Address", "marketplace"); ?></b></td>

						<td style="width: 50%;"><b><?php esc_html_e("Shipping Address", "marketplace"); ?></b></td>

					</tr>

				</thead>

				<tbody>

					<tr>

						<td>
							<address>
								<?php
								if (empty($order->get_billing_first_name()) && empty($order->get_billing_last_name()) && empty($order->get_billing_address_1()) && empty($order->get_billing_address_2()) && empty($order->get_billing_country())) {
									echo esc_html__('No billing address set.', 'marketplace');
								}

								echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '<br>' . $order->get_billing_address_1() . '<br>';
								if ($order->get_billing_address_2() != '') {
									echo $order->get_billing_address_2() . '<br>';
								}
								echo $order->get_billing_city() . ' - ' . $order->get_billing_postcode() . '<br>' . $order->get_billing_state() . ', ' . WC()->countries->countries[$order->get_billing_country()];
								?>
							</address>
						</td>

						<td>
							<address>
								<?php
								if (empty($order->get_shipping_first_name()) && empty($order->get_shipping_last_name()) && empty($order->get_shipping_address_1()) && empty($order->get_shipping_address_2()) && empty($order->get_shipping_country())) {
									echo 'No shipping address set.';
								}
								echo $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() . '<br>' . $order->get_shipping_address_1() . '<br>';
								if ($order->get_shipping_address_2() != '') {
									echo $order->get_shipping_address_2() . '<br>';
								}
								if ($order->get_shipping_country()) {
									echo $order->get_shipping_city() . ' - ' . $order->get_shipping_postcode() . '<br>' . $order->get_shipping_state() . ', ' . WC()->countries->countries[$order->get_shipping_country()];
								}
								?>
							</address>
						</td>

					</tr>

				</tbody>

			</table>

			<table class="table table-bordered">

				<thead>

					<tr>

						<td><b><?php echo esc_html__('Product', 'marketplace'); ?></b></td>

						<td class="text-right"><b><?php echo esc_html__('Quantity', 'marketplace'); ?></b></td>

						<td class="text-right"><b><?php echo esc_html__('Unit Price', 'marketplace'); ?></b></td>

						<td class="text-right"><b><?php echo esc_html__('Total', 'marketplace'); ?></b></td>

					</tr>

				</thead>

				<tbody>

					<?php

					foreach ($order_detail_by_order_id as $product_id => $details) {

						for ($i = 0; $i < count($details); $i++) {

							$total_payment = $total_payment + intval($details[$i]['product_total_price']);

							if ($details[$i]['variable_id'] == 0) {
								?>

								<tr>

									<td>
										<?php echo $details[$i]['product_name']; ?>
										<dl class="variation">
											<?php
											if ( ! empty( $details[ $i ]['meta_data'] ) ) {
												foreach ( $details[ $i ]['meta_data'] as $m_data ){
													echo '<dt class="variation-size">' . wc_attribute_label( $m_data['key'] ) . ' : ' . $m_data['value'] . '</dt>';
												}
											}
											?>
										</dl>
									</td>

									<td class="text-right"><?php echo $details[$i]['qty']; ?></td>

									<td class="text-right"><?php echo $cur_symbol . $details[$i]['product_total_price'] / $details[$i]['qty']; ?></td>

									<td class="text-right"><?php echo $cur_symbol . $details[$i]['product_total_price']; ?></td>

								</tr>

							<?php

						} else {

							$product = new WC_Product($product_id);

							$attribute = $product->get_attributes();

							$attribute_name = '';

							foreach ($attribute as $key => $value) {

								$attribute_name = $value['name'];
							}

							$variation = new WC_Product_Variation($details[$i]['variable_id']);

							$aaa = $variation->get_variation_attributes();

							$attribute_prop = strtoupper($aaa['attribute_' . strtolower($attribute_name)]);

							?>

								<tr>

									<td>

										<?php echo $details[$i]['product_name']; ?><br>
										<b><?php echo $attribute_name . ': '; ?></b>
										<?php echo $attribute_prop; ?>

									</td>

									<td class="text-right"><?php echo $details[$i]['qty']; ?></td>

									<td class="text-right"><?php echo $cur_symbol . $details[$i]['product_total_price'] / $details[$i]['qty']; ?></td>

									<td class="text-right"><?php echo $cur_symbol . $details[$i]['product_total_price']; ?></td>

								</tr>

							<?php

						}
					}
				}

				?>
					<tr>

						<td class="text-right" colspan="3"><b><?php echo esc_html__('SubTotal', 'marketplace'); ?></b></td>

						<td class="text-right"><?php echo $cur_symbol . wc_format_decimal( $total_payment, 2 ); ?></td>

					</tr>
					<?php if ('null' !== $order->get_total_shipping()) :

						$ship_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $user_id, $order_id));

						if (!empty($ship_data)) {

							$shipping_cost = wc_format_decimal( $ship_data[0]->meta_value, 2 );
						} else {

							$shipping_cost = '0.00';
						}
						?>
						<tr>

							<td class="text-right" colspan="3"><b><?php echo esc_html__('Shipping', 'marketplace'); ?></b></td>

							<td class="text-right"><?php echo $cur_symbol . $shipping_cost; ?></td>

						</tr>
					<?php endif; ?>

					<tr>

						<td class="text-right" colspan="3"><b><?php echo esc_html__('Total', 'marketplace'); ?></b></td>

						<?php

						if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) ) {

							?>

							<td class="text-right"><strong><del><?php echo $cur_symbol . wc_format_decimal( ( $shipping_cost + $total_payment ), 2 ); ?></del></strong><?php echo $cur_symbol . wc_format_decimal( ( $shipping_cost + $total_payment - $seller_order_refund_data[ 'refunded_amount' ] ), 2 ); ?></td>

							<?php

						} else {

							?>

							<td class="text-right"><?php echo $cur_symbol . wc_format_decimal( ( $shipping_cost + $total_payment ), 2 ); ?></td>

							<?php

						}

						?>

					</tr>

					<?php

					if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) ) {

						?>

						<tr>

							<td class="text-right" colspan="3"><b><?php esc_html_e('Refunded', 'marketplace'); ?></b></td>

							<td class="text-right"><?php echo $cur_symbol . wc_format_decimal( $seller_order_refund_data[ 'refunded_amount' ], 2 ); ?></td>

						</tr>

						<?php

					}

					?>

				</tbody>

			</table>

		</div>

	</body>

	</html>

<?php
} catch (Exception $e) {
	get_header();
	wc_print_notice($e->getMessage(), 'error');
	get_footer();
}