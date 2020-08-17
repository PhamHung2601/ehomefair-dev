<?php
/**
 * Seller New Order email.
 *
 * @author Webkul
 *
 * @version 4.8.2
 */
defined( 'ABSPATH' ) || exit;

if ( is_array( $data ) ) {
	$order = new WC_Order( $data[0]->get_order_id() );
} else {
	$order = new WC_Order( $data->get_order_id() );
}

$commission_obj = new MP_Commission();
$reward_point_weightage = !empty( $GLOBALS['reward'] ) ?  $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;
$seller_id = get_user_by('email', $customer_email)->ID;

if ( is_array( $data ) ) {
	foreach ( $data as $key => $value ) {
		$product_id = $value->get_product_id();
		$variable_id = $value->get_variation_id();
		$item_data = array();
		$meta_dat = $value->get_meta_data();
		$post = get_post( $product_id );
		$qty = $value->get_data()['quantity'];

		$product_total_price = $value->get_data()['subtotal'];
		if ( ! empty( $meta_dat ) ) {
			foreach ( $meta_dat as $key1 => $value1 ) {
				$item_data[] = $meta_dat[ $key1 ]->get_data();
			}
		}

		$order_detail_by_order_id[ $product_id ][] = array(
			'product_name'        => $value['name'],
			'qty'                 => $qty,
			'variable_id'         => $variable_id,
			'product_total_price' => $product_total_price,
			'meta_data'           => $item_data,
			'tax'                 => $value->get_total_tax(),
		);
	}
} else {
	$product_id = $data->get_product_id();
	$variable_id = $data->get_variation_id();
	$item_data = array();
	$meta_dat = $data->get_meta_data();
	$post = get_post( $product_id );
	$qty = $data->get_data()['quantity'];
	$product_total_price = $data->get_data()['subtotal'];

	if ( ! empty( $meta_dat ) ) {
		foreach ( $meta_dat as $key1 => $value1 ) {
			$item_data[] = $meta_dat[ $key1 ]->get_data();
		}
	}

	$order_detail_by_order_id[ $product_id ][] = array(
		'product_name'        => $data['name'],
		'qty'                 => $qty,
		'variable_id'         => $variable_id,
		'product_total_price' => $product_total_price,
		'meta_data'           => $item_data,
		'tax'                 => $data->get_total_tax(),
	);
}

$com_data = $commission_obj->get_seller_final_order_info($order->get_id(), $seller_id);

$subtotal = 0;
$total_tax = 0;
$total_payment = 0;

$fees = $order->get_fees();

$total_discount = $order->get_total_discount();
$shipping_method = $order->get_shipping_method();
$payment_method = $order->get_payment_method_title();

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_header', $email_heading, $email );

$date = $order->get_date_created();

$arr_day = array(
	'Monday'    => utf8_decode( esc_html__( 'Monday', 'marketplace' ) ),
	'Tuesday'   => utf8_decode( esc_html__( 'Tuesday', 'marketplace' ) ),
	'Wednesday' => utf8_decode( esc_html__( 'Wednesday', 'marketplace' ) ),
	'Thursday'  => utf8_decode( esc_html__( 'Thursday', 'marketplace' ) ),
	'Friday'    => utf8_decode( esc_html__( 'Friday', 'marketplace' ) ),
	'Saturday'  => utf8_decode( esc_html__( 'Saturday', 'marketplace' ) ),
	'Sunday'    => utf8_decode( esc_html__( 'Sunday', 'marketplace' ) ),
);

$arr_month = array(
	'January'   => utf8_decode( esc_html__( 'January', 'marketplace' ) ),
	'February'  => utf8_decode( esc_html__( 'February', 'marketplace' ) ),
	'March'     => utf8_decode( esc_html__( 'March', 'marketplace' ) ),
	'April'     => utf8_decode( esc_html__( 'April', 'marketplace' ) ),
	'May'       => utf8_decode( esc_html__( 'May', 'marketplace' ) ),
	'June'      => utf8_decode( esc_html__( 'June', 'marketplace' ) ),
	'July'      => utf8_decode( esc_html__( 'July', 'marketplace' ) ),
	'August'    => utf8_decode( esc_html__( 'August', 'marketplace' ) ),
	'September' => utf8_decode( esc_html__( 'September', 'marketplace' ) ),
	'October'   => utf8_decode( esc_html__( 'October', 'marketplace' ) ),
	'November'  => utf8_decode( esc_html__( 'November', 'marketplace' ) ),
	'December'  => utf8_decode( esc_html__( 'December', 'marketplace' ) ),
);

$order_day = date( 'l', strtotime( $date ) );
$order_month = date( 'F', strtotime( $date ) );

$date_string = $arr_day[ $order_day ] . ', ' . $arr_month[ $order_month ] . ' ' . date( 'j, Y', strtotime( $date ) );

$result = '
	<div style="margin-bottom: 40px;">
		<p>' . utf8_decode( esc_html__( 'We have finished processing your order.', 'marketplace' ) ) . '</p>
		<h3>' . utf8_decode( __( 'Order', 'marketplace' ) ) . ' #' . $order->get_ID() . ' ( ' . $date_string . ' )</h3>
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;" border="1">
			<tr>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Product', 'marketplace' ) ) . '</th>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Quantity', 'marketplace' ) ) . '</th>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Price', 'marketplace' ) ) . '</th>
			</tr>
			<tr>';

			foreach ( $order_detail_by_order_id as $product_id => $details ) {
				$product = new WC_Product( $product_id );
				$detailc = 0;

				if ( count( $details ) > 0 ) {
					$detailc = count( $details );
				}

				for ( $i = 0; $i < $detailc; ++$i ) {
					$total_tax = floatval( $total_tax ) + floatval( $details[$i]['tax'] );
					$subtotal = floatval( $subtotal ) + floatval( $details[ $i ]['product_total_price'] );
					$total_payment = floatval( $total_payment ) + floatval( $details[ $i ]['product_total_price'] ) + floatval( $order->get_total_shipping() );

					if ( $details[ $i ]['variable_id'] == 0 ) {
						$result .=
							'<tr class="order_item alt-table-row" style="border-bottom-width: 2px;">
								<td class="product-name td">
									<span>' . utf8_decode( $details[ $i ]['product_name']) . '</span><br />';
									if ( ! empty( $details[ $i ]['meta_data'] ) ) {
										foreach ( $details[ $i ]['meta_data'] as $m_data ) {
											$result .= '<b>' . wc_attribute_label( $m_data['key'] ) . '</b> : ' . strtoupper( $m_data['value'] ) . '<br>';
										}
									}
								$result .= '</td><td class="td">' . $details[$i]['qty'] . '</td>
									<td class="product-total td">
									' . wc_price( $details[ $i ]['product_total_price'], array( 'currency' => $order->get_currency() ) ) . '
								</td>
							</tr>';
					} else {
						$attribute = $product->get_attributes();
						$attribute_name = '';

						foreach ( $attribute as $key => $value ) {
							$attribute_name = $value['name'];
						}

						$result .= 
						'<tr class="order_item alt-table-row td" style="border-bottom-width: 2px;">
							<td class="product-name td">
								<span>' . utf8_decode( $details[ $i ]['product_name']) . '</span>';
								if ( ! empty( $details[ $i ]['meta_data'] ) ) {
									foreach ( $details[ $i ]['meta_data'] as $m_data ) {
										$result .= '<b>' . wc_attribute_label( $m_data['key'] ) . '</b> : ' . strtoupper( $m_data['value'] ) . '<br>';
									}
								}

						$result .= '</td>
							<td class="td">' . $details[$i]['qty'] . '</td>
							<td class="product-total td">
								' . wc_price( $details[ $i ]['product_total_price'], array( 'currency' => $order->get_currency() ) ) . '
							</td>
						</tr>';
					}
				}
			}

			if ( ! empty( $subtotal ) ) {
				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Subtotal', 'marketplace' ) ) . ' : </th>
						<td class="td">' . wc_price( $subtotal ) . '</td>
    				</tr>';
			}

			if ( ! empty( $total_discount ) ) {
				$total_payment -= $total_discount;
				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Discount', 'marketplace' ) ) . ' : </th>
						<td class="td">-' . wc_price( $total_discount, array( 'currency' => $order->get_currency() ) ) . '</td>
					</tr>';
			}

			if ( ! empty( $shipping_method ) ) :
				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Shipping', 'marketplace' ) ) . ' : </th>
						<td class="td">' . wc_price( $com_data['shipping'] ? $com_data['shipping'] : 0, array( 'currency' => $order->get_currency() ) ) . '</td>
					</tr>';
			endif;

			$total_fee_amount = 0;

			// if ( ! empty( $fees ) ) {
			// 	foreach ( $fees as $key => $fee ) {
			// 		$fee_name = $fee->get_data()['name'];
			// 		if($key=='punto-de-recompensa'){
			// 			if($com_data['reward_data']){
			// 				$fee_amount = -1 * round(floatval(apply_filters( 'mpmc_get_converted_price', ($com_data['reward_data'] * $reward_point_weightage))));
			// 			}else{
			// 				continue;
			// 			}
			// 		}else{
			// 			$fee_amount = floatval( $fee->get_data()['total'] );
			// 		}

			// 		if( $fee_name !== __('Payment via Wallet', 'marketplace') ) {
			// 			$total_fee_amount += $fee_amount;
			// 		}

			// 	}
			// }

			$reward_used = get_post_meta( $order->get_id(), '_wkmpreward_points_used', true );

			if( !empty( $reward_used ) ) {
				
				$total_fee_amount = -$reward_used;

				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Reward Points', 'marketplace' ) ) . ' : </th>
						<td class="td">' . wc_price( -$reward_used, array( 'currency' => $order->get_currency() ) ) . '</td>
					</tr>';
			}

			$total_payment += $total_fee_amount;

			$wallet_amount_used = get_post_meta( $order->get_id(), '_wkmpwallet_amount_used', true );

			if( !empty( $wallet_amount_used ) ) {
				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Payment via Wallet', 'marketplace' ) ) . ' : </th>
						<td class="td">' . wc_price( -$wallet_amount_used, array( 'currency' => $order->get_currency() ) ) . '</td>
					</tr>
					<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Remaining Payment', 'marketplace' ) ) . ' : </th>
						<td class="td">' . wc_price( $total_payment + $wallet_amount_used, array( 'currency' => $order->get_currency() ) ) . '</td>
					</tr>';
			}

			if ( ! empty( $payment_method ) ) :
				$result .=
					'<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . utf8_decode( __( 'Payment Method', 'marketplace' ) ) . ' : </th>
						<td class="td">' . $payment_method . '</td>
					</tr>';
			endif;

			$result .=
				'<tr>
					<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . __( 'Total', 'marketplace' ) . ' : </th>
					<td class="td">' . wc_price( $total_payment, array( 'currency' => $order->get_currency() ) ) . '</td>
				</tr>';

			$result .=
			'</tr>
		</table>';

		$result .=
			'<table id="addresses" style="width:100%">
				<tr>
					<td class="td" valign="top" width="49%">
						<h3>' . utf8_decode( __( 'Billing address', 'marketplace' ) ) . '</h3>
						<p class="text">' . utf8_decode( $order->get_formatted_billing_address() ) . '</p>
					</td>';

					if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) :
						$shipping = '';
						if ( $order->get_formatted_shipping_address() ) :
							$shipping = utf8_decode( $order->get_formatted_shipping_address() );
						endif;

						if ( ! empty( $shipping ) ) {
							$result .=
								'<td class="td" valign="top" width="49%">
									<h3>' . utf8_decode( __( 'Shipping address', 'marketplace' ) ) . '</h3>
									<p class="text">' . $shipping . '</p>
								</td>';
						}
					endif;

		$result .=
			'</tr>
		</table>';

	$result .=
'</div>';

echo $result;

do_action( 'woocommerce_email_footer', $email );
