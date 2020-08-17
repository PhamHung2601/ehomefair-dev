<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @see       http://richpress.org
 * @since      1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 *
 * @author     webkul
 */
if( ! class_exists( 'Wc_Order_Split' ) ) {

    class Wc_Order_Split
    {

        /** @var object The avaiable payment methods being used. */
        private $available_gateways = array();

        /** @var object The selected payment methods being used. */
        private $payment_method;

        /** @var int ID of customer. */
        private $customer_id;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         *
         * @var string the string used to uniquely identify this plugin
         */
        protected $master_order_id;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct($master_order_id)
        {
            $this->master_order_id = $master_order_id;
        }

        public function run()
        {

            $seller_array = $split_cart_items = $vendor_id_array = array();

            $commission_obj = new MP_Commission();

            $master_order = new WC_Order($this->master_order_id);

            $this->payment_method = $master_order->get_payment_method();

            $this->available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            // get master Order details
            $this->customer_id = $master_order->get_customer_id();

            foreach ($master_order->get_items() as $cart_item_key => $values) {

                if( !empty( $values['variation_id'] ) ) {
                    $product_id = $values['variation_id'];
                } else {
                    $product_id = $values['product_id'];
                }
                
                $vendor = get_post_field( 'post_author', $product_id );

                if (in_array($vendor, $vendor_id_array)) {

                    $build_array = array(
                        'cart_data' => $values->get_data(),
                    );
    
                    array_push(
                        $seller_array[$vendor],
                        $build_array
                    );

                } else {
                    
                    if (!in_array($vendor, $vendor_id_array)) {
                        $split_cart_items = array();
                    }
    
                    array_push($split_cart_items, array(
                        'cart_data' => $values->get_data(),
                    ));
    
                    $seller_array[$vendor] = $split_cart_items;
                }
    
                array_push($vendor_id_array, $vendor);
            }

            $seller_count = !empty($seller_array) ? count(array_keys($seller_array)) : 0;

            if ($seller_count > 1) {
                $child_order = $this->create_order($seller_array, $master_order, $commission_obj);
            }
        }

        public function gcd($a, $b)
        {
            if ($a == 0 || $b == 0) {
                return abs(max(abs($a), abs($b)));
            }

            $r = $a % $b;

            return ($r != 0) ?
            $this->gcd($b, $r) :
            abs($b);
        }

        public function gcd_array($array, $a = 0)
        {
            $b = array_pop($array);

            return ($b === null) ?
            (int) $a :
            $this->gcd_array($array, $this->gcd($a, $b));
        }

        public function create_order($seller_array, $master_order, $commission_obj)
        {
            
            global $wpdb, $reward;

            $vendor_array = $vendor_tax_array = $vendor_discount_array = $vendor_ship_array = $vendor_rwd_array = $order_fee_array = array();

            $shipping_session = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value as shipping_cost, seller_id FROM {$wpdb->prefix}mporders_meta WHERE order_id=%d AND meta_key=%s", $master_order->get_id(), 'shipping_cost' ), ARRAY_A );

            $reward_session = get_post_meta( $master_order->get_id(), '_wkmpreward_points_session_data', true );

            $master_total_tax = $master_order->get_total_tax();

            $master_total_discount = $master_order->get_total_discount();

            $shipping_tax = $master_order->get_shipping_tax();

            $master_fee = $master_order->get_fees();

            $wlt_tax = $rwd_tax = $order_fee_array = array();

            foreach ( $master_fee as $key => $value ) {

                $value_data = $value->get_data();

                if( __( 'Reward Point', 'wkmp-split-order' ) == $value_data['name'] ){
                    $rwd_sel_totl = (-1)*$value_data['total'];
                    $rwd_tax = $value_data['taxes']['total'];
                }

                $order_fee_array[$key] = array(
                    'name' => $value_data['name'],
                    'total' => $value_data['total'],
                    'tax' => $value_data['taxes']['total'],
                );
            }

            $extra = 0;

            $master_total_tax += $extra;

            foreach ($seller_array as $vkey => $value) {
                $vendor_total = 0;

                foreach ($value as $pkey => $pvalue) {
                    $pvalues = $pvalue['cart_data'];
                    $vendor_total += $pvalues['subtotal'];
                    $vendor_array[$vkey] = $vendor_total;
                }
            }

            $ship_sel_totl = (float) $master_order->get_total_shipping();

            $shipping_details = array();
                    
            foreach ( $master_order->get_items( 'shipping' ) as $key => $shipping_item ) {

                $shipping_instance_id = $shipping_item->get_data()['instance_id'];
                $shipping_taxes = $shipping_item->get_data()['taxes']['total'];

                $shipping_details = array(
                    'key' => $key,
                    'shipping_method_title' => $master_order->get_shipping_method(),
                    'shipping_instance_id' => $shipping_instance_id,
                );

            }

            if(!empty($shipping_session)){
                foreach ($shipping_session as $vkey => $vvalue) {
                    $cur_cost = (float) $vvalue['shipping_cost'];
                    $s_tax = array();
                    foreach ($shipping_taxes as $key => $value) {
                    	$s_tax[$key] = $cur_cost * $value / $ship_sel_totl;
                    }
                    $vendor_ship_array[$vvalue['seller_id']] = $s_tax;
                }
            }

            $ratio = $this->gcd_array($vendor_array, 0);

            foreach ($vendor_array as $vkey => $vvalue) {
                $vendor_array[$vkey] = $vvalue / $ratio;
            }

            $ratio_total = array_sum($vendor_array);
            $vendor_fee_array = array();

            foreach ($order_fee_array as $key => $value) {
                $fee_ratio_multiplier = 0;
                $fee_ratio_multiplier =  $value['total'] / $ratio_total;
                $v_tol_arr = 0;
                $v_tax_tol_arr = array();

                if( $value['name'] != __( 'Reward Point', 'wkmp-split-order' ) ) {

                    foreach ($vendor_array as $vkey => $vvalue) {
                        $vendor_fee_array[$key][$vkey]['total'] = floatval(number_format($vvalue * $fee_ratio_multiplier, 2, '.', ''));
                        $v_tol_arr += $vendor_fee_array[$key][$vkey]['total'];
                        if(!empty($value['tax'])){
                            $tax_tmp = array();
                            foreach ($value['tax'] as $tkey => $tvalue) {
                                $tax_tmp[$tkey] =(float) number_format((float)($vendor_fee_array[$key][$vkey]['total'] * $tvalue) / $value['total'], 2, '.', '');
                            }
                            $vendor_fee_array[$key][$vkey]['tax'] = $tax_tmp;
                        }
    
                        $vendor_fee_array[$key][$vkey]['name'] = $value['name'];
    
                    }

                } else {

                    if( !empty( $reward_session ) ) {

                        $reward_point_weightage = $reward->get_woocommerce_reward_point_weightage();

                        foreach ( $reward_session as $seller_id => $reward_points ) {

                            $vendor_fee_array[$key][$seller_id]['name'] = $value['name'];
                            $vendor_fee_array[$key][$seller_id]['total'] = floatval( apply_filters( 'mpmc_get_converted_price', floatval( $reward_points * $reward_point_weightage ) ) );

                        }

                    }

                }

            }

            $order_default_args = array(
                '_order_key' => '',
                '_customer_user' => '',
                '_payment_method' => '',
                '_payment_method_title' => '',
                '_customer_ip_address' => '',
                '_customer_user_agent' => '',
                '_created_via' => '',
                '_cart_hash' => '',
                '_billing_first_name' => '',
                '_billing_last_name' => '',
                '_billing_company' => '',
                '_billing_address_1' => '',
                '_billing_address_2' => '',
                '_billing_city' => '',
                '_billing_state' => '',
                '_billing_postcode' => '',
                '_billing_country' => '',
                '_billing_email' => '',
                '_billing_phone' => '',
                '_shipping_first_name' => '',
                '_shipping_last_name' => '',
                '_shipping_company' => '',
                '_shipping_address_1' => '',
                '_shipping_address_2' => '',
                '_shipping_city' => '',
                '_shipping_state' => '',
                '_shipping_postcode' => '',
                '_shipping_country' => '',
                '_order_currency' => '',
                '_cart_discount' => '',
                '_cart_discount_tax' => '',
                '_order_shipping' => '',
                '_order_shipping_tax' => '',
                '_order_tax' => '',
                '_order_total' => '',
                '_order_version' => '',
                '_prices_include_tax' => '',
                '_billing_address_index' => '',
                '_shipping_address_index' => '',
                'is_vat_exempt' => '',
                '_edit_lock' => '',
                '_wkmpreward_points_used' => '',
                '_wkmpwallet_amount_used' => '',
                '_wkmpsplit_order' => '',
                '_wkmpsplit_create_suborders' => '',
            );

            $master_order_meta = get_post_meta( $master_order->get_id() );

            $master_order_meta = array_diff_key( $master_order_meta, $order_default_args );

            foreach ($seller_array as $key => $value) {

                $commission_data = $commission_obj->get_seller_final_order_info($master_order->get_id(), $key);

                $shipping_cost = $commission_data['shipping'] ? $commission_data['shipping'] : 0;

                $discount_total = $commission_data['discount']['admin'] + $commission_data['discount']['seller'];

                $vendor_discount = 0;

                $reward_point = isset( $reward_session[ $key ] ) ? $reward_session[ $key ] : 0;

                if(isset($vendor_ship_array[$key])){
                    $tax_shipping_vendor = $vendor_ship_array[$key];
                }else{
                    $tax_shipping_vendor = array();
                }

                try {
                    // Start transaction if available
                    wc_transaction_query('start');

                    $order_data = array(
                        'customer_id' => $this->customer_id,
                        'customer_note' => $master_order->get_customer_note(),
                        'created_via' => $master_order->get_created_via(),
                    );

                    
                    // Insert or update the post data
                    $order_id = absint(WC()->session->order_awaiting_payment);

                    /*
                    * If there is an order pending payment, we can resume it here so
                    * long as it has not changed. If the order has changed, i.e.
                    * different items or cost, create a new order. We use a hash to
                    * detect changes which is based on cart items + order total.
                    */
                    if ($order_id && ($order = wc_get_order($order_id)) && $order->has_status(array('pending', 'failed'))) {
                        $order_data['order_id'] = $order_id;
                        $order = wc_update_order($order_data);
                        if (is_wp_error($order)) {
                            throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wkmp-split-order'), 522));
                        } else {
                            $order->remove_order_items();
                            do_action('woocommerce_resume_order', $order_id);
                        }				
                    } else {
                        $order = $this->wc_create_order($order_data);
                        
                        if (is_wp_error($order)) {
                            throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wkmp-split-order'), 520));
                        } elseif (false === $order) {
                            throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wkmp-split-order'), 521));
                        } else {
                            $order_id = $order->get_id();
                        }
                    }

                    $order_total = 0;
                    $total_sel_tax = array();

                    // Store the line items to the new/resumed order
                    foreach ($value as $skey => $svalue) {
                        $values = $svalue['cart_data'];
                        $item_id = $order->add_product(
                            wc_get_product ( $values['product_id'] ),
                            $values['quantity'],
                            array(
                                'variation_id' => $values['variation_id'],
                                'totals' => array(
                                    'subtotal' => $values['subtotal'],
                                    'subtotal_tax' => $values['subtotal_tax'],
                                    'total' => $values['total'],
                                    'tax' => $values['total_tax'],
                                    'tax_data' => $values['taxes'],
                                ),
                            )
                        );

                        if(empty($total_sel_tax)){
                            $total_sel_tax = $values['taxes']['total'];
                        }else if( !empty( $values['taxes']['total'] ) ){
                            foreach($values['taxes']['total'] as $skey => $sval){

                                if( !empty( $total_sel_tax[$skey] ) ) {
                                    $total_sel_tax[$skey] += $sval;
                                } else {
                                    $total_sel_tax[$skey] = $sval;
                                }
                            }
                        }

                        if( !empty( $values['variation_id'] ) ) {
                            $pro_id = $values['variation_id'];
                        } else {
                            $pro_id = $values['product_id'];
                        }

                        $product_price = wc_get_product($pro_id) ? wc_get_price_excluding_tax(wc_get_product($pro_id)) : 0;

                        $product_price = apply_filters('wkmp_modify_product_price', $product_price, $pro_id);

                        $vendor_discount += number_format( (float) (($values['quantity'] * $product_price) - $values['total'] ), 2, '.', '');
                        $order_total += $values['subtotal'];

                        if (!$item_id) {
                            throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wkmp-split-order'), 525));
                        }
                    }
                    
                    // shipping tax
                    if(!empty($tax_shipping_vendor)){
                        foreach ($tax_shipping_vendor as $vkey => $vvalue) {

                            if( !empty( $total_sel_tax[$vkey] ) ) {
                                $total_sel_tax[$vkey] += $vvalue;
                            }
                            
                        }
                    }

                    // manage fee according to seller
                    $fee_total = 0;

                    foreach ($vendor_fee_array as $feekey => $feevalue) {

                        $set_flag  = false;
                        $itmw = new WC_Order_Item_Fee();
                        $itmw->set_order_id($order->get_id());

                        if( isset( $feevalue[$key]['name'] ) ) {
                            
                            if( $feevalue[$key]['name'] == __( 'Reward Point', 'wkmp-split-order' ) && $reward_point > 0 ){
                                
                                $reward_point_weightage = $reward->get_woocommerce_reward_point_weightage();
                                $itmw->set_name(__('Reward Point', 'wkmp-split-order'));
                                $reward_total = apply_filters( 'mpmc_get_converted_price', floatval($reward_point * $reward_point_weightage) );
                                $itmw->set_total( -$reward_total );
                                $itmw->set_taxes( array(
                                    'total' => 0,
                                ));
                                
                                $fee_total = $fee_total + -$reward_total;
                                $order->update_meta_data( '_wkmpreward_points_used', $reward_total );
                                $set_flag = true;
    
                            }else{
                                $set_flag = true;
                                if(!empty($feevalue[$key]['tax'])){
                                    $itmw->set_taxes(array(
                                        'total' => $feevalue[$key]['tax'],
                                    ));
                                    foreach($total_sel_tax as $fky => $fval){
                                        $total_sel_tax[$fky] += $feevalue[$key]['tax'][$fky];
                                    }	
                                }
                                if($feekey == 'wallet'){
                                    $itmw->set_name(__( 'Payment via Wallet', 'wkmp-split-order' ));
                                    $wallet_count = 1;
                                    $order->update_meta_data( '_wkmpwallet_amount_used', $feevalue[$key]['total'] );	
                                } else {
                                    $itmw->set_name( $feevalue[$key]['name'] );
                                }
                                $itmw->set_total(floatval($feevalue[$key]['total']));
                                $fee_total = $fee_total + floatval($feevalue[$key]['total']);
                            }

                        }

                        if($set_flag ){
                            $order->add_item( $itmw );
                        }
                    }

                    $order_total +=(float) $shipping_cost + array_sum($total_sel_tax) - $vendor_discount + $fee_total;

                    if( !empty( $wallet_count ) ) {
                        $order->update_meta_data( '_wkmpwallet_remaining_order_amount', $order_total );	
                    }

                    $billing_address = $master_order->get_address( 'billing' );
                    $shipping_address = $master_order->get_address( 'shipping' );

                    $order->set_parent_id( $master_order->get_id() );

                    // setting final data.
                    $order->set_address($billing_address, 'billing');
                    $order->set_address($shipping_address, 'shipping');
                    $order->set_payment_method( isset($this->available_gateways[$this->payment_method]) ? $this->available_gateways[$this->payment_method] : $this->payment_method );
                    $order->set_transaction_id( $master_order->get_transaction_id() );
                    $order->set_discount_total($vendor_discount);
                    $order->set_total($order_total);
                    $order->set_shipping_total($shipping_cost);

                    $this->create_split_order_tax_lines( $master_order, $order, $total_sel_tax);

                    if( !empty( $shipping_details ) ) {
                        $this->create_split_order_shipping_lines($order, WC()->session->get('chosen_shipping_methods'), $shipping_details, $shipping_cost, $tax_shipping_vendor);
                    }
                    $this->create_split_order_coupon_lines( $master_order, $order, $vendor_discount );

                    $order->save();

                    $arg = array(
                        'ID' => $order->get_id(),
                        'post_author' => $key,
                    );
                    wp_update_post( $arg );

                    if( !empty( $master_order_meta ) ) {

                        $query = "INSERT INTO $wpdb->postmeta( meta_id, post_id, meta_key, meta_value ) VALUES";

                        $meta_query = array();

                        foreach ( $master_order_meta as $meta_key => $meta_value ) {

                            $meta_query[] = $wpdb->prepare( "( NULL, %d, %s, %s )", $order->get_id(), $meta_key, $meta_value[0] );

                        }

                        $meta_query = implode( ',', $meta_query );

                        $query .= $meta_query;

                        $wpdb->query( $query );

                    }

                    if ( is_wp_error( $order->get_id() ) ) {
                        throw new Exception( $order->get_error_message() );
                    }

                    if( empty( $parent_order ) ) {
                        $parent_order = $order->get_id();
                    } else {
                        $order_meta = get_post_meta( $parent_order, '_sub_order', true);
                        if( !empty($order_meta)) {
                            array_push($order_meta, $order->get_id());
                            update_post_meta( $parent_order, '_sub_order', $order_meta );
                        } else {
                            update_post_meta( $parent_order, '_sub_order', array($order->get_id()) );

                        }

                    }

                    $push_arr = array(
                        'shipping_method_id' => !empty($shipping_details['shipping_method_title']) ? $shipping_details['shipping_method_title'] : '',
                        'shipping_cost' => $shipping_cost,
                    );
                    foreach ($push_arr as $key1 => $value1) {
                        $wpdb->insert(
                            $wpdb->prefix . 'mporders_meta',
                            array(
                                'seller_id' => $key,
                                'order_id' => $order->get_id(),
                                'meta_key' => $key1,
                                'meta_value' => $value1,
                            )
                        );
                    }

                    if ($reward_point > 0) {
                        $table2 = $wpdb->prefix . 'mp_reward_cust_sel_details';
                        $res = $wpdb->insert(
                            $wpdb->prefix.'mporders_meta',
                            array(
                                'seller_id' => $key,
                                'order_id' => $order->get_id(),
                                'meta_key' => 'order_reward',		
                                'meta_value' => $reward_point * $reward_point_weightage,
                            )
                        );
                        if($res){
                            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table2 WHERE seller_id = %d AND customer_id = %d", $key, $this->customer_id));
                            if( !empty($result) ){
                                $rew_data = $result[0];

                                $wpdb->update(
                                    $table2,
                                    array(
                                        'used_ref_point' => $rew_data->used_ref_point + $reward_point,
                                        'remaining_rwd_point' => $rew_data->remaining_rwd_point - $reward_point,
                                    ), 
                                    array(
                                        'customer_id' => $this->customer_id,
                                        'seller_id' => $key,
                                    )
                                );
                            }
                        }
                    }
                    
                    do_action( 'woocommerce_checkout_create_order', $order, array() );
                    
                    do_action( 'woocommerce_checkout_order_processed', $order->get_id(), array() );

                    $order->set_status( $master_order->get_status() );

                    $order->save();
                    
                    // If we got here, the order was created without problems!
                    wc_transaction_query('commit');
                    
                } catch (Exception $e) {
                    // There was an error adding order data!
                    wc_transaction_query('rollback');
                    return new WP_Error('checkout-error', $e->getMessage());
                }
            }

            delete_post_meta( $master_order->get_id(), '_wkmpsplit_create_suborders' );

            return $master_order->get_id();
        }

        public function create_split_order_tax_lines( $master_order, $order, $tax_total )
        {

            foreach ( array_keys( $tax_total ) as $tax_rate_id ) {
                if ( $tax_rate_id && apply_filters('woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated') !== $tax_rate_id ) {
                    $item = new WC_Order_Item_Tax();
                    $item->set_props(
                        array(
                            'rate_id' => $tax_rate_id,
                            'tax_total' => $tax_total[$tax_rate_id],
                            'shipping_tax_total' => 0,
                            'rate_code' => WC_Tax::get_rate_code($tax_rate_id),
                            'label' => WC_Tax::get_rate_label($tax_rate_id),
                            'compound' => WC_Tax::is_compound($tax_rate_id),
                        )
                    );

                    // Add item to order and save.
                    $order->add_item($item);
                }
            }

        }

        public function create_split_order_shipping_lines( $order, $chosen_shipping_methods, $shipping_details, $shipping_total, $tax_shipping_vendor)
        {

            $item = new WC_Order_Item_Shipping();
            $item->legacy_package_key = $shipping_details['key']; // @deprecated For legacy actions.
            $item->set_props(
                array(
                    'method_title' => $shipping_details['shipping_method_title'],
                    'method_id' => $chosen_shipping_methods[0],
                    'instance_id' => $shipping_details['shipping_instance_id'],
                    'total' => !empty( $shipping_total ) ? wc_format_decimal( $shipping_total ) : 0,
                    'taxes' => array(
                        'total' => $tax_shipping_vendor,
                    ),
                )
            );

            // Add item to order and save.
            $order->add_item($item);

        }

        /**
         * Add coupon lines to the order.
         *
         * @param WC_Order $order order instance
         * @param WC_Cart  $cart  cart instance
         */
        public function create_split_order_coupon_lines( $master_order, $order, $discount_total)
        {

            foreach ( $master_order->get_used_coupons() as $key => $coupon_code ) {
                $item = new WC_Order_Item_Coupon();
                $item->set_props(
                    array(
                        'code' => $coupon_code,
                        'discount' => $discount_total,
                        'discount_tax' => 0,
                    )
                );

                // Add item to order and save.
                $order->add_item($item);
            }

        }

        /**
         * Create a new order programmatically.
         *
         * Returns a new order object on success which can then be used to add additional data.
         *
         * @param array $args
         *
         * @return WC_Order|WP_Error WC_Order on success, WP_Error on failure
         */
        public function wc_create_order($args = array())
        {
            $default_args = array(
                'status' => '',
                'customer_id' => null,
                'customer_note' => null,
                'order_id' => 0,
                'created_via' => '',
                'cart_hash' => ''
            );

            try {
                $args = wp_parse_args($args, $default_args);
                $order = new WC_Order($args['order_id']);
                
                if (!is_null($args['customer_note'])) {
                    $order->set_customer_note($args['customer_note']);
                }
                
                if (!is_null($args['customer_id'])) {
                    $order->set_customer_id(is_numeric($args['customer_id']) ? absint($args['customer_id']) : 0);
                }
                
                if (!is_null($args['created_via'])) {
                    $order->set_created_via(sanitize_text_field($args['created_via']));
                }
                
                if (!is_null($args['cart_hash'])) {
                    $order->set_cart_hash(sanitize_text_field($args['cart_hash']));
                }
                
                // Set these fields when creating a new order but not when updating an existing order.
                if (!$args['order_id']) {
                    $order->set_currency(get_woocommerce_currency());
                    $order->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));
                    $order->set_customer_ip_address(WC_Geolocation::get_ip_address());
                    $order->set_customer_user_agent(wc_get_user_agent());
                }

                // Update other order props set automatically.

            } catch (Exception $e) {
                return new WP_Error('error', $e->getMessage());
            }

            return $order;
        }

        public function udapte_master_order_status($order)
        {
            global $wpdb;

            $table = $wpdb->prefix . 'posts'; //Good practice

            $master_order_id = $wpdb->get_row("SELECT $table.post_parent FROM $table WHERE $table.ID =" . $this->master_order_id);

            if (!empty($master_order_id)) {

                $main_order_id = $master_order_id->post_parent;

                $orders = $wpdb->get_results("SELECT $table.ID, $table.post_status FROM $table WHERE $table.post_parent =" . $main_order_id);

                $statuses = wp_list_pluck($orders, 'post_status');

                if (!empty($statuses)) {
                    $status = count(array_filter($statuses, function ($n) {return $n == 'wc-completed';}));

                    if (count($statuses) == $status) {
                        $main_order = wc_get_order($main_order_id);

                        $main_order->update_status('wc-completed', 'All orders are completed');
                    }
                }

            }

            return $a_author;
        }

    }

}