<?php


if ( isset($_GET['invoice_nonce']) && wp_verify_nonce( $_GET['invoice_nonce'], 'generate_invoice' ) ) {

	global $wpdb;

	$order = new WC_Order( $order_id );

	$order_detail_by_order_id = array();

	$get_item = $order->get_items();

	$cur_symbol = get_woocommerce_currency_symbol( $order->get_currency() );

	foreach ($get_item as $key => $value) {

		$value_data = $value->get_data();

		$product_id = $value->get_product_id();

		$variable_id = $value->get_variation_id();

		$product_total_price = $value_data['total'];

		$qty = $value_data['quantity'];

		$post = get_post($product_id);

		$order_detail_by_order_id[$product_id][] = array('product_name'=>$value['name'],'qty'=>$qty,'variable_id'=>$variable_id,'product_total_price'=>$product_total_price);

	}

	$shipping_method = ($order->get_shipping_method()) ? $order->get_shipping_method() : 'N.A';

	$payment_method = $order->get_data()['payment_method_title'];

	$total_payment = 0;

	$current_user = wp_get_current_user();

	?>

	<!DOCTYPE html>

	<html>

	<head>

		<title><?php echo __('Order Invoice', 'marketplace'); ?></title>

		<link rel="stylesheet" href="<?php echo WK_MARKETPLACE . 'assets/css/invoice-style.css'; ?>">

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

	</head>

	<body>

		<div class="mp-invoice-wrapper">

			<button onclick="javascript:window.print()"><i class="fa fa-print"></i></button>

			<h1><?php echo __('Invoice Of Order', 'marketplace'); ?> #<?php echo $order_id; ?></h1>

			<table class="table table-bordered">

						<thead>

						<tr>

								<td colspan="2"><b><?php echo _e("Order Information", "marketplace"); ?></b></td>

						</tr>

						</thead>

						<tbody>

						<tr>

								<td style="width: 50%;">

									<table>

						<tbody>

							<tr>

											<td colspan="2"><b><?php echo _e("Buyer Details", "marketplace"); ?></b></td>

									</tr>

							<tr>

								<td><b><?php echo _e("Name", "marketplace"); ?> :</b></td>

								<td data-title="Name"><?php echo $order->get_billing_first_name().' '.$order->get_billing_last_name(); ?></td>

							</tr>

							<tr>

								<td><b><?php echo _e("Email", "marketplace"); ?> :</b></td>

								<td data-title="Email"><?php echo $order->get_billing_email(); ?></td>

							</tr>

							<tr class="alt-table-row">

								<td><b><?php echo _e("Telephone", "marketplace"); ?> :</b></td>

								<td data-title="Telephone"><?php echo $order->get_billing_phone(); ?></td>

							</tr>

						</tbody>

						</table>

								</td>

								<td style="width: 50%;">

									<table>

						<tbody>

							<tr>

								<td><b><?php echo _e("Order Date", "marketplace"); ?> :</b></td>

								<td data-title="Order date"><?php echo date('F j, Y', strtotime($order->get_date_created()) ); ?></td>

							</tr>

							<tr>

								<td><b><?php echo _e("Order ID", "marketplace"); ?> :</b></td>

								<td data-title="Order ID"><?php echo $order_id; ?></td>

							</tr>

							<tr class="alt-table-row">

								<td><b><?php echo _e("Payment Method", "marketplace"); ?> :</b></td>

								<td data-title="Payment Method"><?php echo $payment_method; ?></td>

							</tr>

							<tr class="alt-table-row">

								<td><b><?php echo _e("Shipping Method", "marketplace"); ?> :</b></td>

								<td data-title="Shipping Method"><?php echo $shipping_method; ?></td>

							</tr>

						</tbody>

						</table>

								</td>

						</tr>

						</tbody>

				</table>

				<table class="table table-bordered">

						<thead>

						<tr>

								<td colspan="2"><b><?php echo _e("Address Information", "marketplace"); ?></b></td>

						</tr>

						<tr>

							<td style="width: 50%;"><b><?php echo _e("Billing Address", "marketplace"); ?></b></td>

							<td style="width: 50%;"><b><?php echo _e("Shipping Address", "marketplace"); ?></b></td>

						</tr>

						</thead>

						<tbody>

						<tr>

							<td>
								<address>
							<?php echo $order->get_billing_first_name().' '.$order->get_billing_last_name().'<br>'.$order->get_billing_address_1().'<br>';
							if($order->get_billing_address_2()!=''){
								echo $order->get_billing_address_2().'<br>';
							}
							if ($order->get_billing_country())
							echo $order->get_billing_city().' - '.$order->get_billing_postcode().'<br>'.$order->get_billing_state().', '.WC()->countries->countries[$order->get_billing_country()]; ?>
						</address>
							</td>

							<td>
								<address>
							<?php echo $order->get_shipping_first_name().' '.$order->get_shipping_last_name().'<br>'.$order->get_shipping_address_1().'<br>';
							if($order->get_shipping_address_2()!=''){
								echo $order->get_shipping_address_2().'<br>';
							}
							if ($order->get_shipping_country())
							echo $order->get_shipping_city().' - '.$order->get_shipping_postcode().'<br>'.$order->get_shipping_state().', '.WC()->countries->countries[$order->get_shipping_country()]; ?>
						</address>
							</td>

						</tr>

						</tbody>

				</table>

				<table class="table table-bordered">

						<thead>

						<tr>

								<td colspan="4"><b><?php echo _e("Items to Invoice", "marketplace"); ?></b></td>

						</tr>

						<tr>

							<td><b><?php echo __('Product', 'marketplace'); ?></b></td>

							<td class="text-right"><b><?php echo __('Quantity', 'marketplace'); ?></b></td>

							<td class="text-right"><b><?php echo __('Unit Price', 'marketplace'); ?></b></td>

							<td class="text-right"><b><?php echo __('Total', 'marketplace'); ?></b></td>

						</tr>

						</thead>

						<tbody>

							<?php

							foreach ($order_detail_by_order_id as $product_id => $details) {

						for ($i=0; $i < count($details); $i++) {

							$total_payment = $total_payment + intval($details[$i]['product_total_price']);

							if($details[$i]['variable_id'] == 0) {

								?>

								<tr>

												<td><?php echo $details[$i]['product_name']; ?></td>

												<td class="text-right"><?php echo $details[$i]['qty']; ?></td>

												<td class="text-right"><?php echo $cur_symbol.$details[$i]['product_total_price']/$details[$i]['qty']; ?></td>

												<td class="text-right"><?php echo $cur_symbol.$details[$i]['product_total_price']; ?></td>

										</tr>

								<?php

							}

							else {

								$product = new WC_Product($product_id);

								$attribute = $product->get_attributes();

								$attribute_name = '';

								foreach ($attribute as $key => $value) {

									$attribute_name = $value['name'];

								}

								$variation = new WC_Product_Variation($details[$i]['variable_id']);

								$aaa = $variation->get_variation_attributes();

								$attribute_prop= strtoupper($aaa['attribute_'.strtolower($attribute_name)]);

								?>

								<tr>

												<td>

													<?php echo $details[$i]['product_name']; ?><br>
													<b><?php echo $attribute_name.': '; ?></b>
													<?php echo $attribute_prop; ?>

												</td>

												<td class="text-right"><?php echo $details[$i]['qty']; ?></td>

												<td class="text-right"><?php echo $cur_symbol.$details[$i]['product_total_price']/$details[$i]['qty']; ?></td>

												<td class="text-right"><?php echo $cur_symbol.$details[$i]['product_total_price']; ?></td>

										</tr>

								<?php

							}

						}

					}

					?>
							<tr>

									<td class="text-right" colspan="3"><b><?php echo __('SubTotal', 'marketplace'); ?></b></td>

									<td class="text-right"><?php echo $cur_symbol.$total_payment; ?></td>

							</tr>

							<tr>

									<td class="text-right" colspan="3"><b><?php echo __('Shipping', 'marketplace'); ?></b></td>

									<td class="text-right"><?php echo $cur_symbol.$order->get_total_shipping(); ?></td>

							</tr>

								<tr>

									<td class="text-right" colspan="3"><b><?php echo __('Total', 'marketplace'); ?></b></td>

									<td class="text-right"><?php echo $cur_symbol.($order->get_total_shipping()+$total_payment); ?></td>

							</tr>

					 </tbody>

				</table>

		</div>

	</body>

	</html>

	<?php

}

else {
	wp_die( '<h1>' . __( 'Cheatin’ uh?', 'marketplace' ) . '</h1><p>' . __( 'Sorry, you are not allowed to access invoice.', 'marketplace' ) . '</p>' );
}
