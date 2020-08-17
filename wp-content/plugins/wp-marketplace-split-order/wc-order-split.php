<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @see    https://webkul.com
 * @since   1.2.0
 *
 * @wordpress-plugin
 * Plugin Name: WC Marketplace Split Order
 * Plugin URI:
 * Description: Small plugin to split failed orders into smaller one through an admin dashboard widget.
 * Version:     1.2.0
 * Author:      Webkul
 * Author URI:  http://webkul.com
 * Text Domain: wkmp-split-order
 * License:     GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-mp-split-activator.php.
 */
function activate_wc_order_split()
{
    require_once plugin_dir_path(__FILE__).'includes/class-wc-order-split-activator.php';
    Wc_order_Split_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-order-split-deactivator.php.
 */
function deactivate_wc_order_split()
{
    require_once plugin_dir_path(__FILE__).'includes/class-wc-order-split-deactivator.php';
    Wc_order_Split_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wc_order_split');
register_deactivation_hook(__FILE__, 'deactivate_wc_order_split');

add_action('woocommerce_checkout_order_review','woocommerce_order_review');

    if ( ! function_exists( 'woocommerce_order_review' ) ) {

        /**
         * Output the Order review table for the checkout.
         *
         * @subpackage  Checkout
         */
        function woocommerce_order_review( $deprecated = false ) {

           $template_name = 'checkout/review-order.php';

            // default args
            $args = array( 'checkout' => WC()->checkout() );


            // default path (look in plugin file!)
            $default_path = untrailingslashit( plugin_dir_path(__FILE__) ) . '/woocommerce/templates/';

            wc_get_template( $template_name, $args, $default_path, $default_path  );
        }
    }

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

if( !class_exists( 'WKMP_Split_Order' ) ) {

    class WKMP_Split_Order {

        function __construct() {

            $this->define_constants();

            add_filter('wc_get_template', array( $this, 'wk_get_template' ), 10, 5);

            add_filter('pre_get_posts', array( $this, 'woo_split_filter_orders' ), 99, 1);

            add_action('wkmpso_create_suborders', array( $this, 'woocommerce_e' ));

            if( !is_admin() ) {
                add_action('woocommerce_checkout_create_order', array( $this, 'wkmpso_add_order_meta_for_split_order' ), 1, 1);
            }

            add_filter('mp_vendor_split_orders', array( $this, 'return_vendor_suborders' ), 10, 2);

            add_filter('mp_set_parent_order', array( $this, 'mp_return_parent_order' ), 10, 1);

            add_action('wkmp_after_seller_update_order_status', array( $this, 'woocommerce_e_udpate_order_status' ), 99, 2);

            add_filter('wkmp_send_notification_mail_to_seller_for_new_order', array( $this, 'wkmpso_disable_seller_mails_for_main_order' ), 10, 2);

            add_filter('wkmp_modify_order_refund_user_id', array( $this, 'wkmpso_modify_order_refund_user_id' ), 10, 2);

            add_filter('woocommerce_my_account_my_orders_query', array( $this, 'wkmpso_modify_my_account_orders_query' ));


        }

        function wkmpso_modify_my_account_orders_query( $query ) {

            $query[ 'meta_key' ] = '_wkmpsplit_order';
            $query[ 'meta_value' ] = 'yes';
            $query[ 'meta_compare' ] = 'NOT EXISTS';

            return $query;

        }

        function wkmpso_modify_order_refund_user_id( $user_id, $order_id ) {

            $seller_id = get_post_field( 'post_author', $order_id );

            return !empty( $seller_id ) ? $seller_id : $user_id;

        }

        function wkmpso_disable_seller_mails_for_main_order( $send_mail_to_seller, $order ) {

            $sellers_in_orders = array();

            foreach ( $order->get_items() as $key => $item ) {

                if( !empty( $item['variation_id'] ) ) {
                    $product_id = $item['variation_id'];
                } else {
                    $product_id = $item['product_id'];
                }
                
                $seller_id = get_post_field( 'post_author', $product_id );

                if ( !in_array( $seller_id, $sellers_in_orders ) ) {
                    $sellers_in_orders[] = $seller_id;
                }
    
            }

            $seller_count = ! empty( $sellers_in_orders ) ? count( $sellers_in_orders ) : 0;

            if ( $seller_count > 1 ) {
                return false;
            }

            return $send_mail_to_seller;

        }

        function woocommerce_e_udpate_order_status($order, $data)
        {
            require plugin_dir_path(__FILE__).'includes/class-wc-order-split.php';

            $checkout = new Wc_order_Split($order->get_id());

            $checkout->udapte_master_order_status($order);
        }

        function mp_return_parent_order($order_id)
        {
            global $wpdb;

            $table = $wpdb->prefix.'posts';

            $master_order_id = $wpdb->get_row("SELECT post_parent FROM $table WHERE $table.ID =".$order_id);

            if (!empty($master_order_id) && isset($master_order_id->post_parent) && $master_order_id->post_parent > 0) {
                $order_id = $master_order_id->post_parent;
            }

            return $order_id;
        }

        function return_vendor_suborders($ord_detail, $user_id)
        {
            global $wpdb;

            $order_detail = $wpdb->get_results("select DISTINCT woitems.order_id from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts post on woi.meta_value=post.ID join {$wpdb->prefix}posts order_post on woitems.order_id = order_post.ID where woi.meta_key='_product_id' and order_post.ID NOT IN (SELECT post_parent from {$wpdb->prefix}posts where post_type='shop_order') and post.ID=woi.meta_value and post.post_author='".$user_id."' order by woitems.order_id DESC");

            return $order_detail;
        }

        function wkmpso_add_order_meta_for_split_order( $order ) {

            $sellers_in_orders = array();

            foreach ( $order->get_items() as $key => $item ) {

                if( !empty( $item['variation_id'] ) ) {
                    $product_id = $item['variation_id'];
                } else {
                    $product_id = $item['product_id'];
                }
                
                $seller_id = get_post_field( 'post_author', $product_id );

                if ( !in_array( $seller_id, $sellers_in_orders ) ) {
                    $sellers_in_orders[] = $seller_id;
                }
    
            }

            $seller_count = ! empty( $sellers_in_orders ) ? count( $sellers_in_orders ) : 0;

            if ( $seller_count > 1 ) {

                $order->update_meta_data( '_wkmpsplit_create_suborders', 'yes' );
                $order->update_meta_data( '_wkmpsplit_order', 'yes' );

                remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_cancelled_to_completed_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );
                remove_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( WC()->mailer()->emails['WC_Email_New_Order'], 'trigger' ) );

            }

        }

        function woocommerce_e($order_id)
        {

            if( !empty( $order_id ) && get_post_meta( $order_id, '_wkmpsplit_create_suborders', true ) == 'yes' ) {

                require_once plugin_dir_path(__FILE__) . 'includes/class-wc-order-split.php';
    
                $checkout = new Wc_order_Split( $order_id );
    
                $checkout->run();
            }
        }

        function woo_split_filter_orders($query)
        {
            global $pagenow;
            global $wp_query;
            
            $qv = $wp_query->query_vars;
            
            if ($pagenow == 'edit.php' && isset($qv['post_type']) && $qv['post_type'] == 'shop_order') {
                $meta_query = array(
                    'relation' => 'OR',
                    array(
                        'key'       => '_wkmpsplit_order',
                        'value'     => 'yes',
                        'compare'   => 'NOT EXISTS',
                    ),
                    array(
                        'key'       => '_wkmpsplit_order',
                        'value'     => 'yes',
                        'compare'   => '!=',
                    )
                );
                $query->set( 'meta_query', $meta_query );

            }

            return $wp_query;
        }

        function wk_get_template($located, $template_name, $args, $template_path, $default_path)
        {
            if ('checkout/form-checkout.php' == $template_name) {
                $located = plugin_dir_path(__FILE__).'woocommerce/templates/checkout/form-checkout.php';
            } elseif ('checkout/thankyou.php' == $template_name) {
                $located = plugin_dir_path(__FILE__).'woocommerce/templates/checkout/thankyou.php';
            }

            return $located;
        }

        public function define_constants() {
            define('WK_ORDER_SPLIT', plugin_dir_url(__FILE__));
        }

    }

}

add_action('plugins_loaded', function () {

    load_plugin_textdomain('wkmp-split-order', false, basename(dirname(__FILE__)).'/languages');

    if( class_exists( 'Marketplace' ) ) {
        new WKMP_Split_Order();

    } else {
        add_action('admin_notices', 'wkmpso_activate_marketplace_admin_notice');
    }

} );

/**
 * Admin notice function for Marketplace not found
 */
function wkmpso_activate_marketplace_admin_notice()
{
    ?>
    <div class="error">
        <p><?php echo sprintf( esc_html__('WC Marketplace Split Order is enabled but not effective. It requires %sMarketplace Plugin%s in order to work.', 'wkmp-split-order'), '<a href="' . esc_url('https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408?s_rank=27') . '" target="_blank">', '</a>' ); ?></p>
    </div>
    <?php
}
