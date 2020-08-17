<?php

global $wp_query;
$page_no = get_query_var('pagenum');
$current_page = !empty($page_no) ? intval($page_no) : 1; 

$offset = ($current_page -1) * 10;

?>
<div class="woocommerce-account">
    <?php do_action('mp_get_wc_account_menu', 'marketplace'); ?>
    <div id="main_container" class="woocommerce-MyAccount-content">
        <form method="get" id="wkmp-order-list-form">
            <div class="wkmp-order-search-wrap">
                <input type="text" name="wkmp_search" placeholder="<?php esc_html_e('Search by Order ID', 'marketplace'); ?>">
                    <?php wp_nonce_field('wcqp_nonce_order_search', 'wcqp_nonce_order_search_nonce'); ?>
                <input type="submit" value="<?php esc_html_e('Search', 'marketplace'); ?>" data-action="search"/>
            </div>
            <table class="orderhistory" width="100%">
                <thead>
                    <tr>
                        <th width="15%"><?php esc_html_e('Order', 'marketplace'); ?></th>
                        <th width="20%"><?php esc_html_e('Status', 'marketplace'); ?></th>
                        <th width="25%"><?php esc_html_e('Date', 'marketplace'); ?></th>
                        <th width="20%"><?php esc_html_e('Total', 'marketplace'); ?></th>
                        <th width="15%"><?php esc_html_e('View Order', 'marketplace'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
    
                    global $wpdb;
    
                    $commission = new MP_Commission();
    
                    $wpmp_obj5 = new MP_Form_Handler();
    
                    $user_id = get_current_user_id();
    
                    $page_id = $wpmp_obj5->get_page_id(get_option('wkmp_seller_page_title'));
    
                    $order_by_table = array();
    
                    $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
                    
                    $post_data = $_GET;
                    
                    $total_order_query = "SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . "'";
                    
                    if (isset($post_data['wkmp_search']) && !empty($post_data['wkmp_search']) ) {
                        $o_id  = intval($post_data['wkmp_search']);
                        if ($o_id > 0 && wp_verify_nonce(wp_unslash($post_data['wcqp_nonce_order_search_nonce']), 'wcqp_nonce_order_search')) {
                            $total_order_query = "SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . " AND order_id = $o_id' ORDER BY order_id DESC LIMIT $offset, 10";
                            $order_query = "SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . "' AND order_id = $o_id ORDER BY order_id DESC ";
                        } else {
                            $order_query = "SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . "' ORDER BY order_id DESC LIMIT $offset, 10 ";
                        }
                    } else {
                        $order_query = "SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . "' ORDER BY order_id DESC LIMIT $offset, 10 ";
                    }
                    
                    $total_orders = $wpdb->get_results($total_order_query);
                    
                    $order_count = 0;
                    
                    if (!empty($total_orders)) {
                        $order_count = count($total_orders);
                    }
        
                    $order_detail = $wpdb->get_results($order_query);
    
                    $order_detail = apply_filters('mp_vendor_split_orders', $order_detail, $user_id);
                    
                    $all_order_details = array();
                    $order_id_list = array();
                    
                    foreach ($order_detail as $order_dtl) {
                        $order_status = $query_result = '';
                        $order_id = $order_dtl->order_id;
                        $order_id_list[] = $order_id;
                        $order = wc_get_order($order_id);
                        if (!empty($order) ) {
    
                            $cur_symbol = get_woocommerce_currency_symbol($order->get_currency());
                            $get_item = $order->get_items();
        
                            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s;', $wpdb->prefix.'mpseller_orders')) === $wpdb->prefix.'mpseller_orders') {
                                $query = $wpdb->prepare("SELECT order_status from {$wpdb->prefix}mpseller_orders where order_id = '%d' and seller_id = '%d'", $order_id, $user_id);
                                $query_result = $wpdb->get_results($query);
                            }
                
                            if ($query_result) {
                                $order_status = $query_result[0]->order_status;
                            }
                            if (!$order_status) {
                                $order_status = get_post_field('post_status', $order_id);
                            }
        
                            $status_array = wc_get_order_statuses();
        
                            foreach ($get_item as $key => $value) {
                                $product_id = $value->get_product_id();
                                $variable_id = $value->get_variation_id();
                                $post = get_post($product_id, ARRAY_A);
                                if ($post['post_author'] == $user_id) {
                                    $price_id = $product_id;
                                    $type = 'simple';
                                    $qty = $value->get_quantity();
                                    $product = new WC_Product($price_id);
                                    if ($variable_id != 0) {
                                        $price_id = $variable_id;
                                        $type = 'variable';
                                        $product = new WC_Product_Variation($price_id);
                                    }
                                    $product_price = $product->get_price();
                                    $display_status = isset($status_array[$order_status]) ? $status_array[$order_status] : '-';
        
                                    $all_order_details[$order_id][] = array(
                                        'order_date' => date_format($order->get_date_created(), 'Y-m-d H:i:s'),
                                        'order_status' => $display_status,
                                        'product_price' => $product_price,
                                        'qty' => $qty,
                                    );
                                }
                            }
    
                        }
                    }
                    
                    for ($counter = 0; $counter < count($order_id_list); ++$counter) {
                        $order_id = $order_id_list[$counter];
                        foreach ($all_order_details as $key => $value) {
                            if ($order_id == $key) {
                                $order = wc_get_order($order_id);
                                if (!empty($order) ) {
                                    
                                    $cur_symbol = get_woocommerce_currency_symbol($order->get_currency());
                                    foreach ($value as $index => $val) {
                                        $qty = $val['qty'];
                                        $total_price = $val['product_price'];
                                        $status = $val['order_status'];
                                        $date = $val['order_date'];
        
                                        if (isset($order_by_table[$key])) {
                                            $total_price = $order_by_table[$key]['total_price'] + $total_price;
                                            $total_qty = $order_by_table[$key]['total_qty'] + $qty;
        
                                            $order_by_table[$key] = array(
                                                'symbol' => $cur_symbol,
                                                'status' => $status,
                                                'date' => $date,
                                                'total_price' => $total_price,
                                                'total_qty' => $total_qty,
                                            );
                                        } else {
                                            $order_by_table[$key] = array(
                                                'symbol' => $cur_symbol,
                                                'status' => $status,
                                                'date' => $date,
                                                'total_price' => $total_price,
                                                'total_qty' => $qty,
                                            );
                                        }
        
                                        $ord_info = $commission->get_seller_order_info($key, $user_id);
        
                                        $order_by_table[$key]['total_qty'] = $ord_info['total_qty'];
                                        $order_by_table[$key]['total_price'] = $ord_info['total_sel_amt'] + $ord_info['ship_data'];
                                    }
    
                                }
                            }
                        }
                    }
                    if (!empty($order_by_table)) {
                    
                        foreach ($order_by_table as $key => $value) {
                            $seller_order_refund_data = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $user_id, $key, '_wkmp_refund_status')));
        
                            if (!empty($seller_order_refund_data[ 'refunded_amount' ]) ) {
        
                                $total = '<del>' . $value['total_price'] . '</del> ' . $value['symbol'] . round($value['total_price'] - $seller_order_refund_data[ 'refunded_amount' ], 2);
        
                            } else {
        
                                $total = $value['total_price'];
        
                            }
        
                            $reward_used = get_post_meta($key, '_wkmpreward_points_used', true);
        
                            if (!empty($reward_used) ) {
                                $total = $value['total_price'] + -$reward_used;
                            }
        
                            ?>
                         <tr>
                            <td data-tab="<?php esc_html_e('Order', 'marketplace'); ?>"><a href="<?php echo esc_url(home_url($page_name . '/' . get_option('mp_order_history', 'order-history') . '/' . $key)); ?>"><?php echo '#' . $key; ?></a></td>
                            <td data-tab="<?php esc_html_e('Status', 'marketplace'); ?>"><?php echo ucfirst($value['status']); ?></td>
                            <td data-tab="<?php esc_html_e('Date', 'marketplace'); ?>"><?php echo $value['date']; ?></td>
                            <td data-tab="<?php esc_html_e('Total', 'marketplace'); ?>"><?php echo $value['symbol'] . $total . ' ' . esc_html__('for', 'marketplace') . ' ' . $value['total_qty'] . ' ' . esc_html__(' items', 'marketplace'); ?></td>
                            <td class="wkmp-view-wrap" data-tab="<?php esc_html_e('View Order', 'marketplace'); ?>"><a href="<?php echo esc_url(home_url($page_name . '/' . get_option('mp_order_history', 'order-history') . '/' . $key)); ?>" class="button"><?php echo esc_html_e('View', 'marketplace'); ?><span class="wkmp-view"></span></a></td>
                         </tr>
                        <?php
                        } 
                    } else {
                        ?>
                            <tr>
                                <td colspan="6" class="wkmp-nodata-td" width="100%"><?php esc_html_e('No Data Found', 'marketplace'); ?></td>
                            </tr>
                        <?php
                    }?>
                </tbody>
            </table>
        <?php
        $maxpage_count = ceil($order_count / 10);
            
        if (1 < $maxpage_count ) : 
        ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php
            if (1 !== $current_page ) :
            ?>
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_order_history', 'order-history').'/page/'.($current_page - 1))); ?>">
                <?php _e('Previous', 'marketpalce'); ?>
            </a>
            <?php
            endif;
            if (intval($maxpage_count) !== $current_page ) :
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_order_history', 'order-history').'/page/'.($current_page + 1))); ?>">
                <?php _e('Next', 'marketplace'); ?>
            </a>
            <?php 
            endif;
            ?>
        </div>
        <?php
        endif;
    ?>
    </form>
    </div>
</div>