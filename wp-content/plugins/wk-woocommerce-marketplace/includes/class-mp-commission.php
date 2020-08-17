<?php
/**
 * This file handles commission related functions.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_Commission')) {
    /**
     * Commision Handler.
     */
    class MP_Commission
    {
        /**
         * Base Function.
         */
        public function __construct()
        {
            global $commission;

            $commission = __CLASS__;
        }

        /**
         * Get Commission per order item.
         *
         * @param int $order_id   order ID
         * @param int $product_id product ID
         * @param int $quantity   product Quantity
         */
        public function get_order_item_commission($order_id, $product_id, $quantity = '')
        {
            $order = wc_get_order($order_id);
        }

        /**
         * Get admin commission rate.
         *
         * @param int $seller_id seller ID
         */
        public function get_admin_rate($seller_id)
        {
            global $wpdb;
            $admin_rate = 1;

            $admin_commission = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}mpcommision  where seller_id=%d", $seller_id));

            if ($admin_commission) {
                $admin_rate = floatval($admin_commission[0]->commision_on_seller) / 100;
            }

            return $admin_rate;
        }

        /**
         * Update Seller Commission data.
         *
         * @param int $seller_id seller ID
         * @param int $order_id  order ID
         */
        public function update_seller_commission($seller_id, $order_id)
        {

            $sel_ord_data = $this->get_seller_final_order_info($order_id, $seller_id);

            $exchange_rate = get_post_meta( $order_id, 'mpmc_exchange_rate', true );

            $exchange_rate = !empty( $exchange_rate ) ? $exchange_rate : 1;

            $sel_pay_amount = $sel_ord_data['total_seller_amount'] / $exchange_rate;

            $response = $this->update($seller_id, $sel_pay_amount);

            if ($response['error'] == 0) {
                return $sel_pay_amount;
            } else {
                return false;
            }
        }

        /**
         * Seller commision updation.
         *
         * @param int $seller_id  seller ID
         * @param int $pay_amount admin Commission Rate
         */
        public function update($seller_id, $pay_amount)
        {
            $result = array(
                'error' => 1,
            );

            $seller_id = intval($seller_id);

            if (!empty($seller_id) && !empty($pay_amount)) {
                global $wpdb;

                $seller_data = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}mpcommision where seller_id=%d", $seller_id));

                if (!empty($seller_data)) {
                    $paid_ammount = $seller_data[0]->paid_amount + $pay_amount;

                    $last_paid_ammount = $pay_amount;

                    $res = $wpdb->update(
                        "{$wpdb->prefix}mpcommision",
                        array(
                            'paid_amount' => wc_format_decimal( $paid_ammount, 2 ),
                            'last_paid_ammount' => wc_format_decimal( $last_paid_ammount, 2 ),
                        ),
                        array('seller_id' => $seller_id),
                        array(
                            '%f',
                            '%f',
                            '%f',
                        ),
                        array('%d')
                    );
                    if ($res) {
                        $result = array(
                            'error' => 0,
                            'msg' => __('Amount Transfered Successfully.!', 'marketplace'),
                        );
                    }
                }
            }

            return $result;
        }

        /**
         * Calculate product commission.
         *
         * @param int $product_id      product is
         * @param int $pro_qty         product quantity
         * @param int $pro_price       product price
         * @param int $assigned_seller seller field
         */
        public function calculate_product_commission($product_id = '', $pro_qty = '', $pro_price = '', $assigned_seller = '')
        {
            $data = array();
            
            if (!empty($product_id)) {
                global $wpdb;

                $product = get_post($product_id);

                if (empty($assigned_seller)) {
                    $seller_id = $product->post_author;
                } else {
                    $seller_id = $assigned_seller;
                }

                $marketplace_commission = $wpdb->get_results($wpdb->prepare("Select commision_on_seller from {$wpdb->prefix}mpcommision where seller_id = %d", $seller_id));
                
                $marketplace_commission = apply_filters('wkmp_alter_seller_commission', $marketplace_commission, $product_id, $pro_qty, $seller_id);

                $product_price = $pro_price;
                
                if (empty($marketplace_commission[0]->commision_on_seller)) {
                    if (user_can($seller_id, 'administrator')) {
                        $admin_commission = $product_price;

                        $seller_amount = $product_price - $admin_commission;

                        $commission_applied = 0;

                        $comm_type = 'fixed';
                    } else {
                        if (get_option('wkmpcom_minimum_com_onseller')) {
                            $default_commission = get_option('wkmpcom_minimum_com_onseller');
                        } else {
                            $default_commission = 0;
                        }

                        $admin_commission = ($product_price / 100) * $default_commission;

                        $seller_amount = $product_price - $admin_commission;

                        $commission_applied = ($default_commission) ? $default_commission : 0;

                        $comm_type = 'percent';
                    }
                } else {
                    $admin_commission = ($product_price / 100) * $marketplace_commission[0]->commision_on_seller;

                    $seller_amount = $product_price - $admin_commission;

                    $commission_applied = $marketplace_commission[0]->commision_on_seller;

                    $comm_type = 'percent';
                }

                $data = array(
                    'seller_id' => $seller_id,
    
                    'total_amount' => $product_price,
    
                    'admin_commission' => $admin_commission,
    
                    'seller_amount' => $product_price - $admin_commission,
    
                    'commission_applied' => $commission_applied,
    
                    'commission_type' => $comm_type,
                );

            }

            return $data;
        }

        /**
         * Get seller ids regarding order id.
         *
         * @param int $order_id order id
         */
        public function get_sellers_in_order( $order_id = '' )
        {
            global $wpdb;

            $sel_arr = array();

            $sel_id = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT seller_id from {$wpdb->prefix}mporders where order_id = %d", $order_id));

            foreach ($sel_id as $key => $value) {
                $sel_arr[] = $value->seller_id;
            }

            return $sel_arr;
        }

        /**
         * Returns final seller data according to order id.
         *
         * @param int $order_id  order id
         * @param int $seller_id seller id
         *
         * @return array
         */
        public function get_seller_final_order_info($order_id, $seller_id)
        {
            global $wpdb;
            // $order_id = apply_filters('mp_set_parent_order', $order_id);
            $or_status = wc_get_order($order_id)->get_status();
            $sel_ord_data = array();
            $discount_arr = array();
            $sel_ord_data = $this->get_seller_order_info($order_id, $seller_id);

            $sel_amt = 0;
            $admin_amt = 0;
            if (!empty($sel_ord_data)) {
                $sel_amt = $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'];
                $admin_amt = $sel_ord_data['total_comision'];
            }

            $rwd_data = array();

            $reward_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'order_reward' ", $seller_id, $order_id));

            if (!empty($reward_data)) {
                $sel_amt = $sel_amt - $reward_data[0]->meta_value;

                $rwd_data['seller'] = $reward_data[0]->meta_value;
            }

            $walt_data = array();

            $wallet_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'order_wallet_amt' ", $seller_id, $order_id));

            if (!empty($wallet_data)) {
                $sel_amt = $sel_amt - $wallet_data[0]->meta_value;

                $walt_data['seller'] = $wallet_data[0]->meta_value;
            }

            $pay_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'paid_status' ", $seller_id, $order_id));
            if (!empty($pay_data)) {
                $act_status = 'paid';
            } else {
                $act_status = 'not_paid';
            }

            $data = array(
                'id' => $order_id.'-'.$seller_id,
                'order_id' => $order_id,
                'product' => $sel_ord_data['pro_info'],
                'quantity' => $sel_ord_data['total_qty'],
                'product_total' => $sel_ord_data['pro_total'],
                'total_seller_amount' => $sel_amt,
                'total_commission' => $admin_amt,
                'status' => $or_status,
                'reward_data' => $rwd_data,
                'wallet_data' => $walt_data,
                'shipping' => $sel_ord_data['ship_data'],
                'discount' => $sel_ord_data['discount'],
                'action' => $act_status,
            );

            $data = apply_filters('wk_marketplace_final_seller_ord_info', $data);

            return $data;
        }

        /**
         * Update seller data according to order id.
         *
         * @param int $order_id order id
         */
        public function update_seller_order_info($order_id)
        {
            global $wpdb;

            if ($order_id) {
                $sellers = $this->get_sellers_in_order($order_id);
                
                $order = wc_get_order($order_id);
                
                do_action('wkmp_manage_order_fee', $order_id);

                if (!empty($sellers)) {
                    foreach ($sellers as $seller_id) {

                        $sel_ord_data = $this->get_seller_order_info($order_id, $seller_id);
                        $sel_amt = 0;
                        $admin_amt = 0;

                        $sel_ord_data = apply_filters('wk_marketplace_manage_order_fee', $sel_ord_data, $order_id, $seller_id);

                        if (!empty($sel_ord_data)) {
                            $sel_amt = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'], $order->get_currency());
                            $admin_amt = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_comision'], $order->get_currency());
                        }

                        $sel_com_data = $wpdb->get_results($wpdb->prepare(" SELECT * from {$wpdb->prefix}mpcommision WHERE seller_id = %d", $seller_id));

                        if ($sel_com_data) {
                            $sel_com_data = $sel_com_data[0];
                            $admin_amount = (floatval($sel_com_data->admin_amount) + $admin_amt);
                            $seller_amount = (floatval($sel_com_data->seller_total_ammount) + $sel_amt);
                            $wpdb->get_results($wpdb->prepare(" UPDATE {$wpdb->prefix}mpcommision set admin_amount = %f, seller_total_ammount = %f, last_com_on_total = %f WHERE seller_id = %d", $admin_amount, $seller_amount, $seller_amount, $seller_id));
                        } else {
                            $wpdb->insert(
                                $wpdb->prefix.'mpcommision',
                                array(
                                    'seller_id' => $seller_id,
                                    'admin_amount' => wc_format_decimal($admin_amt, 2),
                                    'seller_total_ammount' => wc_format_decimal($sel_amt, 2),
                                )
                            );
                        }
                    }
                }
            }
        }

        /**
         * Returns seller data according to order id.
         *
         * @param int $order_id  order id
         * @param int $seller_id seller id
         *
         * @return array
         */
        public function get_seller_order_info($order_id, $seller_id)
        {
            global $wpdb;

            $data = false;

            $discount = array(
                'seller' => 0,
                'admin' => 0,
            );

            $product_info = array();
            $quantity = 0;
            $product_total = 0;
            $total_seller_amount = 0;
            $total_commission = 0;
            $shipping = 0;

            $sel_order = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mporders WHERE seller_id = %d AND order_id = %d", $seller_id, $order_id));

            if (!empty($sel_order)) {
                foreach ($sel_order as $ord_info) {
                    if (!empty($ord_info->product_id)) {
                        $product_info[ $ord_info->product_id ] = array(
                            'id' => $ord_info->product_id,
                            'title' => get_the_title($ord_info->product_id),
                            'quantity' => $ord_info->quantity,
                            'line_total' => $ord_info->amount,
                            'discount' => $ord_info->discount_applied,
                            'commission' => $ord_info->admin_amount,
                        );
                    }

                    if (!empty($ord_info->quantity)) {
                        $quantity = $quantity + $ord_info->quantity;
                    }

                    if (!empty($ord_info->amount)) {
                        $product_total = $product_total + $ord_info->amount;
                    }

                    if (!empty($ord_info->seller_amount)) {
                        $total_seller_amount = $total_seller_amount + $ord_info->seller_amount;
                    }

                    if (!empty($ord_info->admin_amount)) {
                        $total_commission = $total_commission + $ord_info->admin_amount;
                    }

                    if (!empty($ord_info->discount_applied)) {
                        $discount_data = $wpdb->get_results($wpdb->prepare("Select * from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'discount_code' ", $seller_id, $ord_info->order_id));
                        if (!empty($discount_data)) {
                            $discount['seller'] = $discount['seller'] + $ord_info->discount_applied;
                        } elseif ($ord_info->discount_applied > 0) {
                            $discount['admin'] = $discount['admin'] + $ord_info->discount_applied;
                        }
                    }

                    $ship_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $seller_id, $ord_info->order_id));

                    if (!empty($ship_data)) {
                        $shipping = $ship_data[0]->meta_value;
                    }
                }
                $data = array(
                    'pro_info' => $product_info,
                    'total_qty' => $quantity,
                    'pro_total' => $product_total,
                    'total_sel_amt' => $total_seller_amount,
                    'total_comision' => $total_commission,
                    'discount' => $discount,
                    'ship_data' => $shipping,
                );

            }

            return $data;
        }

        public function get_sel_comission_via_order($order_id, $seller_id)
        {
            global $wpdb, $commission;

            $data = array();

            $i = 0;

            $ord_id = array();

            $product = array();

            $quantity = array();

            $product_total = array();

            $total_seller_amount = array();

            $total_commission = array();

            $status = array();

            $paid_status = array();

            $sel_order = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mporders WHERE seller_id = %d AND order_id = %d", $seller_id, $order_id));

            if (!empty($sel_order)) {
                $i = 0;

                $order_arr = array();

                foreach ($sel_order as $value) {
                    $discount_arr = array();

                    $o_id = $value->order_id;

                    $order = wc_get_order($o_id);

                    $product_id = $value->product_id;

                    if (in_array($o_id, $order_arr, true)) {
                        $key = array_search($o_id, $order_arr, true);

                        $product_info = get_the_title($product_id).'( #'.$product_id.' )';

                        $quantity_info = $value->quantity;

                        $product_total_info = $value->amount;

                        $total_seller_amount_info = $value->seller_amount;

                        $total_commission_info = $value->admin_amount;

                        $discount_by = '';

                        if (0 !== $value->discount_applied) {
                            $discount_data = $wpdb->get_results($wpdb->prepare("Select * from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'discount_amt' ", $seller_id, $o_id));

                            if (!empty($discount_data)) {
                                $discount_by = 'S';

                                $total_seller_amount_info = $total_seller_amount_info - $value->discount_applied;

                                $o_discount = $value->discount_applied;
                            } else {
                                $discount_by = 'A';

                                $total_commission_info = $total_commission_info - $value->discount_applied;

                                $o_discount = $value->discount_applied;
                            }
                        } else {
                            $o_discount = 0;
                        }

                        $discount_arr = $data[$key]['discount'];

                        if ('' !== $discount_by && 0 !== $o_discount) {
                            array_push(
                                $discount_arr,
                                array(
                                    'by' => $discount_by,
                                    'amount' => $o_discount,
                                )
                            );
                        }

                        $data[$key]['product'] = $data[$key]['product'].' + '.$product_info;

                        $data[$key]['quantity'] = $data[$key]['quantity'] + $quantity_info;

                        $data[$key]['discount'] = $discount_arr;

                        $data[$key]['product_total'] = $data[$key]['product_total'] + $product_total_info;

                        $data[$key]['total_seller_amount'] = $data[$key]['total_seller_amount'] + $total_seller_amount_info;

                        $data[$key]['total_commission'] = $data[$key]['total_commission'] + $total_commission_info;

                        continue;
                    } else {
                        $order_arr[$i] = $o_id;
                    }

                    $product_id = $value->product_id;

                    $id[] = $o_id.'-'.$seller_id;

                    $ord_id[] = $o_id;

                    $product[] = get_the_title($product_id).'( #'.$product_id.' )';

                    $quantity[] = $value->quantity;

                    $product_total[] = $value->amount;

                    $total_seller_amount[] = $value->seller_amount;

                    $total_commission[] = $value->admin_amount;

                    $status[] = $order->get_status();

                    $ship_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $seller_id, $o_id));

                    if (!empty($ship_data)) {
                        $shipping[] = $ship_data[0]->meta_value;
                    } else {
                        $shipping[] = 0;
                    }

                    $discount_by = '';

                    if (0 !== $value->discount_applied) {
                        $discount_data = $wpdb->get_results($wpdb->prepare("Select * from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'discount_amt' ", $seller_id, $o_id));

                        if (!empty($discount_data)) {
                            $discount_by = 'S';
                            $discount[] = $value->discount_applied;
                        } else {
                            $discount_by = 'A';
                            $discount[] = $value->discount_applied;
                        }
                    } else {
                        $discount[] = 0;
                    }

                    $pay_data = $wpdb->get_results($wpdb->prepare("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'paid_status' ", $seller_id, $o_id));

                    if (!empty($pay_data)) {
                        $paid_status[] = $pay_data[0]->meta_value;
                    } else {
                        $paid_status[] = 'Not Paid';
                    }

                    if ('paid' === $paid_status[$i]) {
                        $action[] = '<button class="button button-primary" class="admin-order-pay" disabled>'.esc_html__('Paid', 'marketplace').'</button>';
                    } else {
                        $action[] = '<a href="javascript:void(0)" data-id="'.$id[$i].'" class="page-title-action admin-order-pay">'.esc_html__('Pay', 'marketplace').'</a>';
                    }

                    if ('S' === $discount_by) {
                        $total_seller_amount[$i] = $total_seller_amount[$i] + $shipping[$i] - $discount[$i];

                        $final_discount[] = $discount[$i];
                    } else {
                        $total_seller_amount[$i] = $total_seller_amount[$i] + $shipping[$i];
                    }

                    if ('A' === $discount_by) {
                        $total_commission[$i] = $total_commission[$i] - $discount[$i];

                        $final_discount[] = $discount[$i];
                    } else {
                        $total_commission[$i] = $total_commission[$i];
                    }

                    if ('' === $discount_by) {
                        $final_discount[] = $discount[$i];
                    }
                    if ('' !== $discount_by && 0 !== $final_discount[$i]) {
                        array_push(
                            $discount_arr,
                            array(
                                'by' => $discount_by,
                                'amount' => $final_discount[$i],
                            )
                        );
                    }

                    $data[] = array(
                        'id' => $id[$i],
                        'order_id' => $ord_id[$i],
                        'product' => $product[$i],
                        'quantity' => $quantity[$i],
                        'product_total' => $product_total[$i],
                        'total_seller_amount' => $total_seller_amount[$i],
                        'total_commission' => $total_commission[$i],
                        'status' => $status[$i],
                        'shipping' => $shipping[$i],
                        'discount' => $discount_arr,
                        'paid_status' => $paid_status[$i],
                        'action' => $action[$i],
                    );
                    ++$i;
                }
            }

            return $data[0];
        }
    }
}
