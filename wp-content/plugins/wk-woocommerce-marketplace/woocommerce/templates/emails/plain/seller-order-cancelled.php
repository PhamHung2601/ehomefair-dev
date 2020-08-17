<?php
/**
 * Seller New Order email.
 *
 * @author Webkul
 *
 * @version 4.8.2
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (is_array($data)) {
    $order = new WC_Order($data[0]->get_order_id());
} else {
    $order = new WC_Order($data->get_order_id());
}
foreach ($data as $key => $value) {
    $product_id = $value->get_product_id();
    $variable_id = $value->get_variation_id();
    $item_data = array();
    $meta_dat = $value->get_meta_data();
    $post = get_post($product_id);
    $qty = $value->get_data()['quantity'];

    $product_total_price = $value->get_data()['subtotal'];
    if (!empty($meta_dat)) {
        foreach ($meta_dat as $key1 => $value1) {
            $item_data[] = $meta_dat[$key1]->get_data();
        }
    }

    $order_detail_by_order_id[$product_id][] = array(
        'product_name' => $value['name'],
        'qty' => $qty,
        'variable_id' => $variable_id,
        'product_total_price' => $product_total_price,
        'meta_data' => $item_data,
    );
}

$total_payment = 0;

$shipping_method = $order->get_shipping_method();

$payment_method = $order->get_payment_method_title();

$fees = $order->get_fees();

$total_discount = $order->get_total_discount();

echo '= '.utf8_decode(esc_html($email_heading))." =\n\n";

$date = $order->get_date_created();

$arr_day = array(
    'Monday' => utf8_decode(__('Monday', 'marketplace')),
    'Tuesday' => utf8_decode(__('Tuesday', 'marketplace')),
    'Wednesday' => utf8_decode(__('Wednesday', 'marketplace')),
    'Thursday' => utf8_decode(__('Thursday', 'marketplace')),
    'Friday' => utf8_decode(__('Friday', 'marketplace')),
    'Saturday' => utf8_decode(__('Saturday', 'marketplace')),
    'Sunday' => utf8_decode(__('Sunday', 'marketplace')),
);
$arr_month = array(
    'January' => utf8_decode(__('January', 'marketplace')),
    'February' => utf8_decode(__('February', 'marketplace')),
    'March' => utf8_decode(__('March', 'marketplace')),
    'April' => utf8_decode(__('April', 'marketplace')),
    'May' => utf8_decode(__('May', 'marketplace')),
    'June' => utf8_decode(__('June', 'marketplace')),
    'July' => utf8_decode(__('July', 'marketplace')),
    'August' => utf8_decode(__('August', 'marketplace')),
    'September' => utf8_decode(__('September', 'marketplace')),
    'October' => utf8_decode(__('October', 'marketplace')),
    'November' => utf8_decode(__('November', 'marketplace')),
    'December' => utf8_decode(__('December', 'marketplace')),
);
$order_day = date('l', strtotime($date));
$order_month = date('F', strtotime($date));

$date_string = $arr_day[$order_day].', '.$arr_month[$order_month].', '.date('j, Y', strtotime($date));

$result = utf8_decode( sprintf( esc_html__( 'Alas. Just to let you know — order #%d belonging to %s has been cancelled:', 'marketplace' ), esc_html( $order->get_order_number() ), esc_html( $order->get_formatted_billing_full_name() ) ) )."\n\n".'Order #'.$order->get_ID().' ('.$date_string.')
			'."\n\n";

foreach ($order_detail_by_order_id as $product_id => $details) {
    $product = new WC_Product($product_id);
    $detailc = 0;
    if (count($details) > 0) {
        $detailc = count($details);
    }
    for ($i = 0; $i < $detailc; ++$i) {
        $total_payment = floatval($total_payment) + floatval($details[$i]['product_total_price']) + floatval($order->get_total_shipping());
        if ($details[$i]['variable_id'] == 0) {
            $result .= utf8_decode($details[$i]['product_name']).utf8_decode(__('SKU: ', 'marketplace')).$product->get_sku().' X '.$details[$i]['qty'].' = '.$order->get_currency().' '.$details[$i]['product_total_price']."\n\n";
        } else {
            $attribute = $product->get_attributes();

            $attribute_name = '';
            foreach ($attribute as $key => $value) {
                $attribute_name = $value['name'];
            }
            $result .= utf8_decode($details[$i]['product_name']).' ('.utf8_decode(__('SKU: ', 'marketplace')).$product->get_sku().' )';
            if (!empty($details[$i]['meta_data'])) {
                foreach ($details[$i]['meta_data'] as $m_data) {
                    $result .= '('.wc_attribute_label($m_data['key']).' : '.strtoupper($m_data['value']).')';
                }
            }

            $result .= ' X '.$details[$i]['qty'].' = '.$order->get_currency().' '.$details[$i]['product_total_price']."\n\n";
        }
    }
}

if( !empty( $total_discount ) ) {
    $total_payment -= $total_discount;
    $result .= utf8_decode(__('Discount', 'marketplace')).' : -'.wc_price( $total_discount, array('currency' => $order->get_currency() ) )."\n\n";
}

if (!empty($shipping_method)) :
    $result .= utf8_decode(__('Shipping', 'marketplace')).' : '.wc_price(($order->get_total_shipping() ? $order->get_total_shipping() : 0), array('currency' => $order->get_currency()))."\n\n";
endif;

$total_fee_amount = 0;

if( !empty( $fees ) ) {

    foreach ($fees as $key => $fee) {

        $fee_name = $fee->get_data()['name'];

        $fee_amount = floatval( $fee->get_data()['total'] );

        $total_fee_amount += $fee_amount;

        $result .= utf8_decode($fee_name).' : '.wc_price( $fee_amount, array('currency' => $order->get_currency() ) )."\n\n";

    }

}

$total_payment += $total_fee_amount;

if (!empty($payment_method)) :
    $result .= utf8_decode(__('Payment Method', 'marketplace')).' : '.$payment_method."\n\n";
endif;

$result .= utf8_decode(__('Total', 'marketplace')).' : '.wc_price($total_payment, array('currency' => $order->get_currency()))."\n\n";

$text_align = is_rtl() ? 'right' : 'left';
$result .= utf8_decode(__('Billing address', 'marketplace')).' : '."\n\n";
foreach ($order->get_address('billing') as $add) {
    if ($add) {
        $result .= utf8_decode($add)."\n";
    }
}
if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) :

    $shipping = '';

    if ($order->get_formatted_shipping_address()) :

        $shipping = utf8_decode($order->get_formatted_shipping_address());
    endif;

    if (!empty($shiping)) {
        $result .= utf8_decode(__('Shipping address', 'marketplace')).' : '."\n\n";
        foreach ($order->get_address('billing') as $add) {
            if ($add) {
                $result .= utf8_decode($add)."\n";
            }
        }
    }
endif;

echo $result;
do_action('woocommerce_email_footer', $email);
