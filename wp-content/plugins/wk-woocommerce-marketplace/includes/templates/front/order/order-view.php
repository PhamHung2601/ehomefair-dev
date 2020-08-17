<?php

global $wpdb;

$commission = new MP_Commission();

$order = '';

$order_id = get_query_var('order_id');

$reward_point_weightage = !empty( $GLOBALS['reward'] ) ?  $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;

if (is_admin() && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['oid']) && !empty($_GET['oid'])) {
	$order_id = filter_input(INPUT_GET, 'oid', FILTER_SANITIZE_NUMBER_INT);
}

$user_id = get_current_user_id();

$order_refund = new MP_Order_Refund();

try {

	$order = new WC_Order( $order_id );
	
	$mp_ord_data = $commission->get_seller_final_order_info($order_id, $user_id);

	$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );

	if (isset($_POST['mp-submit-status'])) {
		
		if( !empty( $_POST['mp-order-status'] ) && $_POST['mp-order-status'] === 'wc-refunded' ) {

			$refund_amount = !empty( $seller_order_refund_data['refunded_amount'] ) ? $mp_ord_data['total_seller_amount'] - $seller_order_refund_data['refunded_amount'] : $mp_ord_data['total_seller_amount'];

			if( !empty( $refund_amount ) ) {
	
				$args = array(
					'amount'         => $refund_amount,
					'reason'         => esc_html__( 'Order fully refunded by Seller.', 'marketplace' ),
					'order_id'       => $order_id,
					'line_items'     => array(),
				);

				$order_refund->wkmp_set_refund_args( $args );
	
				// require_once( WK_MARKETPLACE_DIR . 'includes/class-mp-order-refund.php' );
				
				$order_refund->wkmp_process_refund();

			}
			
		}

		mp_order_update_status( $_POST );

	}
	
	if( isset( $_POST['refund_manually'] ) || isset( $_POST['do_api_refund'] ) ) {
		
		$line_items = array();
	
		$order_items = !empty( $_POST['item_refund_amount'] ) ? $_POST['item_refund_amount'] : [];
		$order_item_total = !empty( $_POST['refund_line_total'] ) ? $_POST['refund_line_total'] : [];
		$refund_reason = !empty( $_POST['refund_reason'] ) ? sanitize_text_field( strip_tags( $_POST['refund_reason'] ) ) : '';
		$order_id = !empty( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : '';
		$restock_refunded_items = !empty( $_POST['restock_refunded_items'] ) && $_POST['restock_refunded_items'] == 1;
	
		$api_refund = isset( $_POST['do_api_refund'] );
	
		$total_refund_amount = 0;
	
		foreach ( $order_items as $item_id => $order_item ) {
	
			$qty = !empty( $order_item_total[ $item_id ] ) ? $order_item_total[ $item_id ] : 0;
	
			if( $qty > 0 ) {
	
				$line_items[ $item_id ]['qty'] = $qty;
		
				$line_items[ $item_id ]['refund_total'] = round( floatval( $order_item ) * $qty, 2 );
		
				$total_refund_amount += round( $line_items[ $item_id ]['refund_total'], 2 );
	
			}
	
		}

		if( !empty( $total_refund_amount ) ) {

			$args = array(
				'amount'         => $total_refund_amount,
				'reason'         => $refund_reason,
				'order_id'       => $order_id,
				'line_items'     => $line_items,
				'refund_payment' => $api_refund,
				'restock_items'  => $restock_refunded_items,
			);

			$order_refund->wkmp_set_refund_args( $args );
				
			$order_refund->wkmp_process_refund();

			$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );

			if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) && trim( $seller_order_refund_data[ 'refunded_amount' ] ) == trim( $mp_ord_data['total_seller_amount'] ) ) {

				$wpdb->update(
					$wpdb->prefix . 'mpseller_orders',
					array(
						'order_status' => 'wc-refunded',
					),
					array(
						'order_id' => $order_id,
					),
					array(
						'%s',
					),
					array(
						'%d',
					)
				);

			}
			
		} else {
			wc_print_notice( esc_html__( 'Please select items to refund.', 'marketplace' ), 'error' );
		}
	
	}
	
	$seller_order_refund_data = $order_refund->wkmp_get_seller_order_refund_data( $order_id );
	
	$order_status = '';
	$query_result = '';

	if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s;', $wpdb->prefix . 'mpseller_orders')) === $wpdb->prefix . 'mpseller_orders') {
		$query = $wpdb->prepare("SELECT order_status from {$wpdb->prefix}mpseller_orders where order_id = '%d' and seller_id = '%d'", $order_id, $user_id);
		$query_result = $wpdb->get_results($query);
	}

	if ($query_result) {
		$order_status = $query_result[0]->order_status;
	}
	if (!$order_status) {
		$order_status = get_post_field('post_status', $order_id);
	}

	$payment_gateway = wc_get_payment_gateway_by_order( $order );
	
	$gateway_name  = false !== $payment_gateway ? ( ! empty( $payment_gateway->method_title ) ? $payment_gateway->method_title : $payment_gateway->get_title() ) : __( 'Payment gateway', 'marketplace' );

	$order_detail_by_order_id = array();

	$get_item = $order->get_items();

	$order_currency = $order->get_currency();

	$cur_symbol = get_woocommerce_currency_symbol( $order_currency );

	$order_detail_by_order_id = array();

	foreach ($get_item as $key => $value) {
		$item_data = array();
		$product_id = $value->get_product_id();
		$variable_id = $value->get_variation_id();
		$product_total_price = $value->get_data()['total'];
		$qty = $value->get_data()['quantity'];
		$post = get_post( $product_id );
		$meta_data = $value->get_meta_data();

		if ( ! empty( $meta_data ) ) {
			foreach ( $meta_data as $key1 => $value1 ) {
				$item_data[] = $meta_data[ $key1 ]->get_data();
			}
		}

		if ($post->post_author == $user_id) {
			$order_detail_by_order_id[$product_id][] = array(
				'product_name'        => $value['name'],
				'qty'                 => $qty,
				'variable_id'         => $variable_id,
				'item_key'            => $key,
				'product_total_price' => $product_total_price,
				'meta_data'           => $item_data,
			);
		}
	}

	$shipping_method = $order->get_shipping_method();

	$payment_method = $order->get_payment_method_title();

	$total_payment = 0; ?> <div class="woocommerce-account">
		<?php

		do_action('mp_get_wc_account_menu', 'marketplace');

		if (!empty($order_detail_by_order_id)) :

			?>

			<div class="woocommerce-MyAccount-content mp-order-view wrap">

				<div id="order_data_details">

					<?php do_action('wkmp_before_seller_print_invoice_button', $order); ?>
					
					<?php

					if( $order_status !== 'wc-refunded' && ( empty( $seller_order_refund_data ) || !empty( $seller_order_refund_data ) && trim( $seller_order_refund_data['refunded_amount'] ) < trim( $mp_ord_data[ 'total_seller_amount' ] ) ) ) {
						?>

						<button class="button wkmp-order-refund-button"><?php esc_html_e( 'Refund', 'marketplace' ); ?></button>
						<?php
					}
					
					?>

					<a href="<?php echo esc_url(site_url() . '/seller/invoice/' . base64_encode($order_id)); ?>" target="_blank" class="button print-invoice"><?php echo esc_html__('Print Invoice', 'marketplace'); ?></a>

					<?php do_action('wkmp_after_seller_print_invoice_button', $order); ?>

					<h3><?php echo esc_html__('Order', 'marketplace') . ' #' . $order_id; ?></h3>

					<div class="wkmp_order_data_detail">
						<form method="post" id="wkmp-order-view-form">
							<table class="widefat">
								<thead>
									<tr>
										<th class="product-name"><b><?php echo esc_html_e('Product', 'marketplace'); ?></b></th>
										<th class="product-total"><b><?php echo esc_html_e('Total', 'marketplace'); ?></b></th>
										<th class="product-refund wkmp-order-refund" style="display:none;"><b><?php echo esc_html_e('Refund Quantity', 'marketplace'); ?></b></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($order_detail_by_order_id as $product_id => $details) {
										for ($i = 0; $i < count($details); ++$i) {

											$total_payment = round(floatval($mp_ord_data['total_seller_amount']), 2);
											if ($details[$i]['variable_id'] == 0) {
												?>
												<tr class="order_item alt-table-row">
													<td class="product-name toptable">
														<a target="_blank" href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo $details[$i]['product_name']; ?></a>
														<strong class="product-quantity">× <?php echo $details[$i]['qty']; ?>
														<?php

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] ) ) {
															?>
															<br>
															<span class="wkmp-refund wkmp-green"><?php echo esc_attr( -$seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] ); ?></span>
															<?php
														}

														?>
														</strong>
														<dl class="variation">
															<?php
															if ( ! empty( $details[ $i ]['meta_data'] ) ) {
																foreach ( $details[ $i ]['meta_data'] as $m_data ){
																	echo '<dt class="variation-size">' . wc_attribute_label( $m_data['key'] ) . ' : ' . $m_data['value'] . '</dt>';
																}
															}
															?>
														</dl>
														<?php
														do_action('wk_mp_append_order_meta_data', $product_id, $details, $order_id);
														?>
													</td>
													<td class="product-total toptable">
														<?php echo wc_price( $details[0]['product_total_price'], array( 'currency' => $order_currency ) ); ?>
														
														<?php
														
														if( !empty( $mp_ord_data['product'][$product_id]['discount'] ) ) {
															
															?>
															<br>
															<span class="wkmp-order-discount"><?php echo wc_price( $mp_ord_data['product'][$product_id]['discount'], array( 'currency' => $order_currency ) ) . ' ' . esc_html__( 'discount', 'marketplace' ); ?></span>
															<?php

														}
														

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['refund_total'] ) ) {
															?>
															<br>
															<span class="wkmp-refund wkmp-green"><?php echo wc_price( -$seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['refund_total'], array( 'currency' => $order_currency ) ); ?></span>
															<?php
														}

														?>
													</td>
													<td class="product-refund toptable wkmp-order-refund" style="display:none;">

														<?php

														$product_qty = $details[$i]['qty'];

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ] ) && $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] >= $product_qty ) {

															?>

															<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'marketplace' ); ?></p>
															
															<?php

														} else if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ] ) && $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] < $product_qty ) {

															$refund_qty = $product_qty - $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'];

															$product_amount = ( $details[0]['product_total_price'] - $mp_ord_data['product'][$product_id]['commission'] ) / $product_qty;

															?>

															<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">

															<input type="number" name="refund_line_total[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details[$i]['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $refund_qty ); ?>">

															<?php

														} else {

															$product_amount = ( $details[0]['product_total_price'] - $mp_ord_data['product'][$product_id]['commission'] ) / $product_qty;

															?>

															<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">

															<input type="number" name="refund_line_total[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details[$i]['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $product_qty ); ?>">

															<?php

														}
														
														?>

													</td>
												</tr>
											<?php
										} else {
											$product = new WC_Product($product_id);
											$attribute = $product->get_attributes();

											$attribute_name = '';

											$variation = new WC_Product_Variation($details[$i]['variable_id']);
											$aaa = $variation->get_variation_attributes(); ?>
												<tr class="order_item alt-table-row">
													<td class="product-name toptable">
														<a target="_blank" href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo $details[$i]['product_name']; ?></a>
														<strong class="product-quantity">× <?php echo $details[$i]['qty']; ?>
														<?php

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] ) ) {
															?>
															<br>
															<span class="wkmp-refund wkmp-green"><?php echo esc_attr( -$seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] ); ?></span>
															<?php
														}

														?>
														</strong>
														<dl class="variation">
															<?php
															foreach ($attribute as $key => $value) {
																$attribute_name = $value['name'];
																$attribute_prop = strtoupper($aaa['attribute_' . strtolower($attribute_name)]); ?>
																<dt class="variation-size"><?php echo $attribute_name . ' : ' . $attribute_prop; ?></dt>
															<?php
														} ?>
														</dl>
													</td>
													<td class="product-total toptable">
														<?php echo wc_price( $mp_ord_data['product_total'], array( 'currency' => $order_currency ) ); ?><br>


														<?php
														
														if( !empty( $mp_ord_data['product'][$details[$i]['variable_id']]['discount'] ) ) {
															
															?>
															<br>
															<span class="wkmp-order-discount"><?php echo wc_price( $mp_ord_data['product'][$details[$i]['variable_id']]['discount'], array( 'currency' => $order_currency ) ) . ' ' . esc_html__( 'discount', 'marketplace' ); ?></span>
															<?php

														}

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['refund_total'] ) ) {
															?>
															<br>
															<span class="wkmp-refund wkmp-green"><?php echo wc_price( -$seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['refund_total'], array( 'currency' => $order_currency ) ); ?></span>
															<?php
														}

														?>
													</td>
													<td class="product-refund toptable wkmp-order-refund">

														<?php

														$product_qty = $details[$i]['qty'];

														if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ] ) && $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] >= $product_qty ) {

															?>

															<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'marketplace' ); ?></p>
															
															<?php

														} else if( !empty( $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ] ) && $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'] < $product_qty ) {

															$refund_qty = $product_qty - $seller_order_refund_data[ 'line_items' ][ $details[$i]['item_key'] ]['qty'];

															$product_amount = ( $mp_ord_data['product_total'] - $mp_ord_data['product'][$details[$i]['variable_id']]['commission'] ) / $product_qty;

															?>

															<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">

															<input type="number" name="refund_line_total[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details[$i]['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $refund_qty ); ?>">

															<?php

														} else {

															$product_amount = ( $mp_ord_data['product_total'] - $mp_ord_data['product'][$details[$i]['variable_id']]['commission'] ) / $product_qty;

															?>

															<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">

															<input type="number" name="refund_line_total[<?php echo esc_attr( $details[$i]['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details[$i]['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $product_qty ); ?>">

															<?php

														}
														
														?>

													</td>
												</tr>
											<?php
										}
									}
								}

								$sel_rwd_note = '';
								if (!empty($mp_ord_data['reward_data'])) {
									if (!empty($mp_ord_data['reward_data']['seller'])) {
										$sel_rwd_note = ' ' . round($mp_ord_data['reward_data']['seller'] * $reward_point_weightage, 2) . '( ' . __('Reward', 'marketplace') . ' )';
									}
								}

								$sel_walt_note = '';
								if (!empty($mp_ord_data['wallet_data'])) {
									if (!empty($mp_ord_data['wallet_data']['seller'])) {
										$sel_walt_note = ' ' . round($mp_ord_data['wallet_data']['seller'], 2) . '( ' . __('Wallet', 'marketplace') . ' )';
									}
								}

								if ($mp_ord_data['product_total'] != $mp_ord_data['total_seller_amount']) {
									$tip = $total_payment;
									$tip .= ' = ';
									$tip .= ($mp_ord_data['product_total']) . ' ( ' . __('Subtotal', 'marketplace') . ' ) ';
									if ($mp_ord_data['shipping'] > 0) {
										$tip .= ' + ';

										$tip .= ($mp_ord_data['shipping']) . ' ( ' . __('Shipping', 'marketplace') . ' ) ';
									}
									if ($mp_ord_data['total_commission'] > 0) {
										$tip .= ' - ';

										$tip .= ($mp_ord_data['total_commission']) . ' ( ' . __('Commission', 'marketplace') . ' ) ';
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
								}

								$shipping_cost = $mp_ord_data['shipping'];
								
								$fees = $order->get_fees();
								
								?>
								</tbody>
								<tfoot>

									<?php
									
									if( !empty( $mp_ord_data['discount'] ) && !empty( array_sum( $mp_ord_data['discount'] ) ) ) {

										?>

										<tr>
											<th scope="row"><b><?php esc_html_e('Discount', 'marketplace'); ?>:</b></th>
											<td class="toptable"><?php echo wc_price( array_sum( $mp_ord_data['discount'] ), array( 'currency' => $order_currency ) ) ?></td>
											
										</tr>

										<?php

									}

									foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
										$shipping_method_title = $shipping_item_obj->get_method_title();
										$shipping_method_total = $shipping_item_obj->get_total();

										?>

										<tr>
											<th scope="row"><b><?php esc_html_e('Shipping', 'marketplace'); ?>:</b></th>
											<td class="toptable">
												<?php echo $cur_symbol . ($shipping_cost ? $shipping_cost : 0); ?><i> via <?php echo $shipping_method_title; ?></i>
												<?php

												if( !empty( $seller_order_refund_data[ 'line_items' ][ $item_id ]['refund_total'] ) ) {
													?>
													<br>
													<span class="wkmp-refund wkmp-green"><?php echo wc_price( -$seller_order_refund_data[ 'line_items' ][ $item_id ]['refund_total'], array( 'currency' => $order_currency ) ); ?></span>
													<?php
												}

												?>
											</td>
											<td class="toptable wkmp-order-refund">

												<?php

												if( !empty( $seller_order_refund_data[ 'line_items' ][ $item_id ] ) ) {

													?>

													<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'marketplace' ); ?></p>
													
													<?php

												} else {

													?>

													<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $shipping_cost ? $shipping_cost : 0 ); ?>">

													<input type="checkbox" name="refund_line_total[<?php echo esc_attr( $item_id ); ?>]" id="refund_line_total[<?php echo esc_attr( $item_id ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $item_id ); ?>" value="1">

													<label for="refund_line_total[<?php echo esc_attr( $item_id ); ?>]"><?php esc_html_e( 'Check to Refund', 'marketplace' ); ?></label>

													<?php

												}
												
												?>

											</td>
										</tr>

										<?php
									}

									if ( ! empty( $fees ) ) {
										foreach ( $fees as $key => $fee ) {

											$fee_name = $fee->get_data()['name'];
											if( $key == 'reward' ){
												if($com_data['reward_data']){
													
													$fee_amount = -1 * round(floatval(apply_filters( 'mpmc_get_converted_price', ($com_data['reward_data'] * $reward_point_weightage))));
												}else{
													continue;
												}
											}else{
												$fee_amount = floatval( $fee->get_data()['total'] );
											}

											if( false ) {

											?>

											<tr>
												<th scope="row"><b><?php echo utf8_decode( $fee_name ); ?>:</b></th>
												<td class="td">
													<?php echo wc_price( $fee_amount, array( 'currency' => $order_currency ) );

													if( !empty( $seller_order_refund_data[ 'line_items' ][ $key ]['refund_total'] ) ) {
														?>
														<br>
														<span class="wkmp-refund wkmp-green"><?php echo wc_price( -$seller_order_refund_data[ 'line_items' ][ $key ]['refund_total'], array( 'currency' => $order_currency ) ); ?></span>
														<?php
													}

													?>
												</td>
												<td class="toptable wkmp-order-refund">

													<?php

													if( !empty( $seller_order_refund_data[ 'line_items' ][ $key ] ) ) {

														?>

														<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'marketplace' ); ?></p>
														
														<?php

													} else {

														?>

														<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $fee_amount ); ?>">

														<input type="checkbox" name="refund_line_total[<?php echo esc_attr( $key ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $key ); ?>" value="1">

														<label for="refund_line_total[<?php echo esc_attr( $key ); ?>]"><?php esc_html_e( 'Check to Refund', 'marketplace' ); ?></label>

														<?php

													}
													
													?>

												</td>
											</tr>

											<?php
											}
										}
									}

									$reward_used = get_post_meta( $order_id, '_wkmpreward_points_used', true );

									if( !empty( $reward_used ) ) {
										?>
										<tr>

											<th scope="row"><b><?php echo utf8_decode( __( 'Reward Points', 'marketplace' ) ); ?>:</b></th>
											<td class="td">
												<?php echo wc_price( -$reward_used, array( 'currency' => $order_currency ) );
												?>
											</td>

										</tr>
										<?php
									}

									$wallet_amount_used = get_post_meta( $order_id, '_wkmpwallet_amount_used', true );

									if( !empty( $wallet_amount_used ) ) {

										?>

										<tr>
											<th scope="row"><b><?php echo utf8_decode( __( 'Payment via Wallet', 'marketplace' ) ); ?>:</b></th>
											<td class="td"><?php echo wc_price( -$wallet_amount_used, array( 'currency' => $order_currency ) ); ?></td>
										</tr>

										<tr>
											<th scope="row"><b><?php echo utf8_decode( __( 'Remaining Payment', 'marketplace' ) ); ?>:</b></th>
											<td class="td"><?php echo wc_price( $total_payment + $mp_ord_data['total_commission'] + $wallet_amount_used, array( 'currency' => $order_currency ) ); ?></td>
										</tr>

										<?php
										
									}
									
									?>
									<?php if (!empty($payment_method)) : ?>
										<tr>
											<th scope="row"><b><?php echo esc_html_e('Payment Method', 'marketplace'); ?>:</b></th>
											<td class="toptable"><?php echo $payment_method; ?></td>
										</tr>
									<?php endif; ?>
									<?php if (!empty($mp_ord_data['total_commission']) && $mp_ord_data['total_commission'] > 0) : ?>
										<tr class="alt-table-row">
											<th scope="row"><b><?php echo esc_html_e('Admin Commission', 'marketplace'); ?>:</b></th>
											<td class="toptable">
												<span class="amount"><?php echo $cur_symbol . $mp_ord_data['total_commission']; ?></span>
											</td>
										</tr>
									<?php endif; ?>
									<tr class="alt-table-row">
										<th scope="row"><b><?php echo esc_html_e('Total', 'marketplace'); ?>:</b></th>
										<td class="toptable" colspan="2">

											<?php
											
											if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) ) {

												?>

												<span class="amount"><strong><del><?php echo wc_price( $total_payment, array( 'currency' => $order_currency ) ); ?></del></strong></span>

												<?php

												if (!empty($tip)) {
													?>
													<span class="dashicons dashicons-editor-help" title="<?php echo $tip; ?>"></span>
													<?php
												}
												?>

												<span class="amount"> <?php echo wc_price ( $total_payment - $seller_order_refund_data[ 'refunded_amount' ], array( 'currency' => $order_currency ) ); ?></span>

												<?php


											} else {

												?>

												<span class="amount"><?php echo wc_price( $total_payment, array( 'currency' => $order_currency ) ); ?></span>

												<?php

												if (!empty($tip)) {
													?>
													<span class="dashicons dashicons-editor-help" title="<?php echo $tip; ?>"></span>
													<?php
												}
												?>

												<?php

											}
											
											?>
											
										</td>
									</tr>

									<?php
									
									if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) ) {

										?>

										<tr class="alt-table-row wkmp-green">
											
											<th scope="row"><b><?php echo esc_html_e( 'Refunded', 'marketplace'); ?>:</b></th>

											<td class="toptable" colspan="3">
												<p class="amount"><?php echo wc_price ( $seller_order_refund_data[ 'refunded_amount' ], array( 'currency' => $order_currency ) ); ?></p>
											</td>

										</tr>

										<?php

									}
									
									?>

									<tr class="wkmp-order-refund" style="border: solid; display:none;">
										
										<th scope="row"><b><?php echo esc_html_e('Refund Reason (Optional)', 'marketplace'); ?>:</b></th>

										<td class="toptable">
											<input type="text" name="refund_reason" id="refund-reason" class="form-control">
										</td>

										<td class="toptable">
											<input type="checkbox" id="restock_refunded_items" name="restock_refunded_items" class="form-control" value="1">
											<label for="restock_refunded_items"><?php echo esc_html_e('Restock Refunded items', 'marketplace'); ?></label>
										</td>


									</tr>
									<tr class="wkmp-order-refund" style="display:none;">
										
										<th scope="row"><b><?php echo esc_html_e('Refund Amount', 'marketplace'); ?>:</b></th>

										<td class="toptable">
											<input type="hidden" id="order_id" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
											<input type="number" name="refund_total" id="refund-amount" class="form-control" disabled="disabled" step="0.01">
											<label for="refund-amount"><?php echo esc_html( $cur_symbol ); ?></label>
										</td>

									</tr>

									<tr class="wkmp-order-refund" style="display:none;">
										
										<th scope="row"></th>

										<td class="toptable" colspan="2">
											<input type="submit" name="refund_manually" class="button form-control" value="<?php esc_attr_e( 'Refund Manually', 'marketplace' ); ?>">
											<?php
											
											if ( false !== $payment_gateway && $payment_gateway->can_refund_order( $order ) ) {

												?>

												<input type="submit" name="do_api_refund" class="button form-control" value="<?php echo esc_attr__( 'Refund via', 'marketplace' ) . ' ' . esc_html( $gateway_name ); ?>">

												<?php

											}
											
											?>
										</td>

									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<header>
						<h3><?php echo esc_html_e('Customer details', 'marketplace'); ?></h3>
					</header>
					<table class="shop_table shop_table_responsive customer_details widefat">
						<tbody>
							<tr>
								<th><b><?php echo esc_html_e('Email', 'marketplace'); ?>:</b></th>
								<td data-title="Email" class="toptable"><?php echo $order->get_billing_email(); ?></td>
							</tr>
							<tr class="alt-table-row">
								<th><b><?php echo esc_html_e('Telephone', 'marketplace'); ?>:</b></th>
								<td data-title="Telephone" class="toptable"><?php echo $order->get_billing_phone(); ?></td>
							</tr>
						</tbody>
					</table>
				</form>
				<div class="col2-set addresses">
					<div class="col-1">
						<header class="title">
							<h3><?php echo esc_html_e('Billing Address', 'marketplace'); ?></h3>
						</header>
						<address>
							<?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'marketplace'))); ?>
						</address>
					</div><!-- /.col-1 -->
					<div class="col-2">
						<header class="title">
							<h3><?php echo esc_html_e('Shipping Address', 'marketplace'); ?></h3>
						</header>
						<address>
							<?php echo wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'marketplace'))); ?>
						</address>
					</div><!-- /.col-2 -->
				</div>

				<!-- Order status form  -->
				<div class="mp-status-manage-class">
					<header class="title">
						<h3><?php esc_html_e('Order Status', 'marketplace'); ?></h3>
					</header>

					<?php

					$translated_order_status = array(
						'on-hold' => __('on-hold', 'marketplace'),
						'pending' => __('pending payment', 'marketplace'),
						'processing' => __('processing', 'marketplace'),
						'completed' => __('completed', 'marketplace'),
						'cancelled' => __('cancelled', 'marketplace'),
						'refunded' => __('refunded', 'marketplace'),
						'failed' => __('failed', 'marketplace'),
					);

					if ( true || $order_status != 'wc-completed') :
						?>
						<form method="POST">
							<table class="shop_table shop_table_responsive customer_details widefat">
								<tbody>
									<tr>
										<td><label for="mp-status"><?php esc_html_e('Status', 'marketplace'); ?>:</label></td>
										<td>
											<select name="mp-order-status" id="mp-status" class="mp-select form-control">
												<?php
												foreach (wc_get_order_statuses() as $key => $value) {
													?>
													<option value="<?php echo $key; ?>" <?php if ($order_status == $key) {
																						echo 'selected';
																						} ?>><?php echo $value; ?></option>
												<?php
											} ?>
											</select>
										</td>
									</tr>
									<tr>
										<?php
										wp_nonce_field('mp_order_status_nonce_action', 'mp_order_status_nonce');
										echo "<input type='hidden' name='mp-order-id' value={$order_id} />";
										echo "<input type='hidden' name='mp-seller-id' value={$user_id} />";
										echo "<input type='hidden' name='mp-old-order-status' value={$order_status} />"; ?>
										<td><input type="submit" name="mp-submit-status" class="button" value="<?php echo esc_html__('Save', 'marketplace'); ?>" /></td>
									</tr>
								</tbody>
							</table>
						</form>
					<?php else : ?>
						<p><?php echo esc_html__('Status: Order status is', 'marketplace') . ' ' . $translated_order_status[$order->get_status()] . '.'; ?></p>
					<?php endif; ?>
				</div>

				<?php
				
				$refunds = $order->get_refunds();

				if ( !empty( $refunds ) ) {

					echo '<div class="mp-order-refunds">'; ?><h3><?php esc_html_e('Order Refunds', 'marketplace'); ?> </h3> <?php
					
					echo '<ul class="order_refunds">';

					foreach ( $refunds as $refund ) {
						$who_refunded = new WP_User( $refund->get_refunded_by() );
						?>
						<li>
							<div>
								<?php
								if ( $who_refunded->exists() ) {
									printf(
										/* translators: 1: refund id 2: refund date 3: username */
										esc_html__( 'Refund #%1$s - %2$s by %3$s', 'marketplace' ),
										esc_html( $refund->get_id() ),
										esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ),
										sprintf(
											'<abbr class="refund_by" title="%1$s">%2$s</abbr>',
											/* translators: 1: ID who refunded */
											sprintf( esc_attr__( 'ID: %d', 'marketplace' ), absint( $who_refunded->ID ) ),
											esc_html( $who_refunded->display_name )
										)
									);
								} else {
									printf(
										/* translators: 1: refund id 2: refund date */
										esc_html__( 'Refund #%1$s - %2$s', 'marketplace' ),
										esc_html( $refund->get_id() ),
										esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) )
									);
								}

								?>
								<span>
									<?php
									echo wp_kses_post(
										wc_price( '-' . $refund->get_amount(), array( 'currency' => $refund->get_currency() ) )
									);
									?>
								</span>
								
							</div>

							<?php
							
							if ( $refund->get_reason() ) {
								?>
								<span class="description"><?php echo esc_html( $refund->get_reason() ); ?></span>
								<?php
							}
							
							?>
							
						</li>
						<?php
					}

				}
				echo '</ul>';

				echo '</div>';

				$args = array(
					'post_id' => $order_id,
					'orderby' => 'comment_ID',
					'order' => 'DESC',
					'approve' => 'approve',
					'type' => 'order_note',
				);

				remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);

				$notes = get_comments($args);

				add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);

				echo '<div class="mp-order-notes">'; ?><h3><?php esc_html_e('Order Notes', 'marketplace'); ?> </h3> <?php
				
				echo '<ul class="order_notes">';

				if ($notes) {
				
					foreach ($notes as $note) {
						
						?>
						<li>
							<div class="note_content">
								<?php echo wpautop(wptexturize(wp_kses_post($note->comment_content))); ?>
							</div>
							<p class="meta">
								<abbr class="exact-date" title="<?php echo $note->comment_date; ?>"><?php printf(__('added on %1$s at %2$s', 'marketplace'), date_i18n(wc_date_format(), strtotime($note->comment_date)), date_i18n(wc_time_format(), strtotime($note->comment_date))); ?></abbr>
								<?php
								if (__('WooCommerce', 'marketplace') !== $note->comment_author) :
									/* translators: %s: note author */
									printf(' ' . __('by %s', 'marketplace'), $note->comment_author);
								endif; ?>
							</p>
						</li>
					<?php
				}
			} else {
				echo '<li>' . esc_html__('There are no notes yet.', 'marketplace') . '</li>';
			}

			echo '</ul>';

			echo '</div>'; ?>

			</div>

		<?php else : ?>

			<h1><?php echo esc_html__('Cheat\'n huh ???', 'marketplace'); ?></h1>
			<p><?php echo esc_html__('Sorry, You can\'t access other seller\'s orders.', 'marketplace'); ?></p>

		<?php
	endif; ?>
	</div> <?php
	} catch (Exception $e) {
		if (is_admin()) {
			?>
		<div class="wrap">
			<div class="notice notice-error">
				<p><?php echo $e->getMessage(); ?></p>
			</div>
		</div>
	<?php
} else {
	wc_print_notice($e->getMessage(), 'error');
}
}
