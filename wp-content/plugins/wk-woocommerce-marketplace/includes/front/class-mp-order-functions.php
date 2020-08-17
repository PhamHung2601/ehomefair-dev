<?php

if (! defined('ABSPATH') ) {

    exit;
}


function get_orders( $per_seller_items,$order,$tax_display = '' ) 
{
    if (! $tax_display ) {
        $tax_display = $order->get_data()['cart_tax'];
    }

    $total_rows = array();
    $subtotal=0;
    if ($subtotal = get_seller_subtotal_to_display($order, $per_seller_items, false, $tax_display) ) {
        $total_rows['cart_subtotal'] = array(
        'label' => __('Cart Subtotal:', 'woocommerce'),
        'value'    => $subtotal
        );
    }

    return apply_filters('woocommerce_get_order_item_totals', $total_rows, $order);
}

function get_seller_subtotal_to_display( $order,$per_seller_items,$compound = false, $tax_display = '' )
{

    $subtotal = 0;

    if (! $compound ) {
        foreach ( $per_seller_items as $item ) {

            if (! isset($item['line_subtotal']) || ! isset($item['line_subtotal_tax']) ) {
                return '';
            }

            $subtotal += $item['line_subtotal'];

            if ('incl' == $tax_display ) {
                $subtotal += $item['line_subtotal_tax'];
            }
        }

        $subtotal = wc_price($subtotal, array('currency' => $order->get_currency()));

        $prices_require_tax=false;
        if ($tax_display == 'excl' && $prices_require_tax ) {
            $subtotal .= ' <small>' . WC()->countries->ex_tax_or_vat() . '</small>';
        }

    } else {

        if ('incl' == $tax_display ) {
            return '';
        }

        foreach ( $this->get_items() as $item ) {

            $subtotal += $item['line_subtotal'];

        }

        // Add Shipping Costs
        $subtotal += $this->get_total_shipping();

        // Remove non-compound taxes
        foreach ( $this->get_taxes() as $tax ) {

            if (! empty($tax['compound']) ) {
                continue;
            }

            $subtotal = $subtotal + $tax['tax_amount'] + $tax['shipping_tax_amount'];

        }

        // Remove discounts
        $subtotal = $subtotal - $order->get_cart_discount();

        $subtotal = wc_price($subtotal, array('currency' => $order->get_order_currency()));
    }

    return apply_filters('woocommerce_order_subtotal_to_display', $subtotal, $compound, $order);
}

/**
 *  Map seller for new order
 *
 * @param order_id
 */

function mp_new_order_map_seller( $order_id ) 
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'mpseller_orders';

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s;", $wpdb->prefix . 'mpseller_orders')) === $wpdb->prefix . 'mpseller_orders' ) {

        $order = new WC_Order($order_id);

        $items = $order->get_items();

        foreach ( $items as $key => $value ) {

            $assigned_seller = wc_get_order_item_meta($value->get_id(), 'assigned_seller', true);

            if (isset($assigned_seller) && !empty($assigned_seller) ) {
                $author_array[] = $assigned_seller;

            } else {
                $author_array[] = get_post_field('post_author', $value->get_product_id());
            }

        }

        $author_array = array_unique($author_array);

        foreach ( $author_array as $key => $value ) {
            $sql = $wpdb->insert(
                $table_name,
                array(
                'order_id'      => $order_id,
                'seller_id'     => $value
                ),
                array(
                '%d',
                '%d'
                )
            );
        }

    }
}
