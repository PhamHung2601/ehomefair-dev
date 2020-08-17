<?php

global $wpdb, $woocommerce;

$table_name = $wpdb->prefix . 'mporders';

$check_query = $wpdb->prepare("SELECT * FROM $table_name WHERE order_id = %d", $order_id);

if ($wpdb->get_var($check_query) == 0) {

    $order = new WC_Order($order_id);

    $choosen_ship_method = WC()->session->get('chosen_shipping_methods');
    
    $items = $order->get_items();
    
    // for calculation for advance commission.
    if (class_exists('wk_advanced_commission') && (1 == get_option('advanced_commission_enabled'))) {
        $mp_comision = new Process_Commission();
    } else {
        $mp_comision = new MP_Commission();
    }
    
    foreach ($items as $key => $item) {
    
        $item_id = $item->get_id();
    
        $assigned_seller = wc_get_order_item_meta($item_id, 'assigned_seller', true);
    
        $installation_charges = wc_get_order_item_meta($item_id, 'installation_charges', true);
    
        if (isset($item['variation_id']) && $item['variation_id']) {
            $product_id = $item['variation_id'];
    
            $commission_data = $mp_comision->calculate_product_commission($item['variation_id'], $item['quantity'], $item['line_total'], $assigned_seller);
        } else {
            $product_id = $item['product_id'];
    
            $commission_data = $mp_comision->calculate_product_commission($item['product_id'], $item['quantity'], $item['line_total'], $assigned_seller);
        }
    
        $seller_id = $commission_data['seller_id'];
    
        $amount = (float) $item['line_total'];
    
        $product_qty = $item['quantity'];
    
        $discount_applied = number_format((float) ($item->get_subtotal() - $item->get_total()), 2, '.', '');
    
        $admin_amount = $commission_data['admin_commission'];
    
        $seller_amount = $commission_data['seller_amount'];
    
        $comm_applied = $commission_data['commission_applied'];
    
        $comm_type = $commission_data['commission_type'];
    
        if (!empty($installation_charges)) {
            $amount = (float) $item['line_total'] + (float) $installation_charges;
            $seller_amount = $commission_data['seller_amount'] + (float) $installation_charges;
        }
    
        $data = array(
            'order_id' => $order_id,
    
            'product_id' => $product_id,
    
            'seller_id' => $seller_id,
    
            'amount' => number_format((float) $amount, 2, '.', ''),
    
            'admin_amount' => number_format((float) $admin_amount, 2, '.', ''),
    
            'seller_amount' => number_format((float) $seller_amount, 2, '.', ''),
    
            'quantity' => $product_qty,
    
            'commission_applied' => number_format((float) $comm_applied, 2, '.', ''),
    
            'discount_applied' => $discount_applied,
    
            'commission_type' => $comm_type,
        );
    
        $wpdb->insert("{$wpdb->prefix}mporders", $data);
    }
    
    foreach ( $order->get_items('shipping') as $item_id => $shipping_item_obj ) {
        $shipping_method[$shipping_item_obj->get_method_id()] = $shipping_item_obj->get_method_title();
    }
    
    
    // shipping calculation.
    $ship_sess = WC()->session->get('shipping_sess_cost');
    
    $ship_sess = apply_filters('wk_mp_modify_shipping_session', $ship_sess, $order_id);
    
    WC()->session->__unset('shipping_sess_cost');
    
    $ship_cost = 0;
    
    if (!empty($ship_sess)) {
        foreach ($ship_sess as $sel_id => $sel_detail) {
            if (in_array($sel_detail['title'], $choosen_ship_method)) {
    
                $shiping_cost = $sel_detail['cost'];
        
                $shiping_cost = number_format((float) $shiping_cost, 2, '.', '');
        
                $ship_cost = $ship_cost + $shiping_cost;
        
                $push_arr = array(
                'shipping_method_id' => !empty($sel_detail['title']) ? $sel_detail['title'] : '',
        
                'shipping_cost' => $shiping_cost,
                );
        
                foreach ($push_arr as $key => $value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'mporders_meta',
                        array(
                        'seller_id' => $sel_id,
        
                        'order_id' => $order_id,
        
                        'meta_key' => $key,
        
                        'meta_value' => $value,
                        )
                    );
                }
            }
        }
    }
    
    $coupon_detail = WC()->cart->get_coupons();
    
    if ($coupon_detail) {
        foreach ($coupon_detail as $key => $value) {
            $coupon_code = $key;
    
            $coupon_cost = $value->get_amount();
    
            $coupon_post_obj = get_page_by_title($coupon_code, OBJECT, 'shop_coupon');
    
            $coupon_create = $coupon_post_obj->post_author;
    
            $wpdb->insert(
                $wpdb->prefix . 'mporders_meta',
                array(
                    'seller_id' => $coupon_create,
    
                    'order_id' => $order_id,
    
                    'meta_key' => 'discount_code',
    
                    'meta_value' => $coupon_code,
                )
            );
        }
    }
    
    if (get_post_meta($order_id, '_wkmpsplit_order', true) != 'yes' ) {
    
        $mkt_comision = new MP_Commission();
        $mkt_comision->update_seller_order_info($order_id);
    
    }
}
