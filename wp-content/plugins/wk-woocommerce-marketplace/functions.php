<?php
/**
 * Plugin Name: Marketplace
 * Plugin URI: https://store.webkul.com/Wordpress-Woocommerce-Marketplace.html
 * Description: WordPress WooCommerce Marketplace convert your WordPress WooCommerce store in to Marketplace with separate seller product collection and separate seller.
 * Version: 4.9.1
 * Author: Webkul
 * Author URI: http://webkul.com
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 * Text Domain: marketplace
 * WC requires at least: 3.0.0
 * WC tested up to: 3.6.x.
 */

// BACKEND
/*---------------------------------------------------------------------------------------------*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
add_action('admin_init', 'wkmp_check_woocommerce_is_installed');

/**
 * Check if woocommerce plugin is already installed.
 */
function wkmp_check_woocommerce_is_installed()
{
    ob_start();
    if (!function_exists('WC')) {
        add_action('admin_notices', 'wkmp_woocommerce_missing_notice');
    }
}

add_action(
    'admin_head', function () {
    global $post;
    if (get_current_screen() && 'product' === get_current_screen()->post_type && get_current_screen()->id == 'product' && get_post_status() == 'draft' && (!get_post_meta($post->ID, 'mp_admin_view'))) {

        $author_id = get_post_field('post_author', $post->ID);
        if (!is_super_admin($author_id)) {

            update_post_meta($post->ID, 'mp_admin_view', true);
            update_option('wkmp_approved_product_count', (int)(get_option('wkmp_approved_product_count', 1) - 1));


        }

    }
}
);
add_action(
    'admin_menu', function () {
    global $menu;
    if (get_option('wkmp_approved_product_count')) {
        foreach ($menu as $key => $value) {
            if ($menu[$key][2] == 'edit.php?post_type=product') {
                $menu[$key][0] .= '<span class="wp-ui-notification wk-mp-product-noti" >' . esc_attr(get_option('wkmp_approved_product_count', 0)) . '</span>';
                return;
            }
        }
    }
}
);


/**
 * Function to show message if woocommerce is not installed.
 */
function wkmp_woocommerce_missing_notice()
{
    echo '<div class="error"><p>' . sprintf(esc_html__('WooCommerce Marketplace depends on the last version of %s or later to work!', 'marketplace'), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . esc_html__('WooCommerce', 'marketplace') . '</a>') . '</p></div>';
}

define('MP_VERSION', '4.8.3');

define('MP_SCRIPT_VERSION', '1.0.0');

define('MP_PLUGIN_FILE', __FILE__);

define('MARKETPLACE_VERSION', MP_VERSION);

define('WK_MARKETPLACE', plugin_dir_url(__FILE__));

define('WK_MARKETPLACE_DIR', plugin_dir_path(__FILE__));

if (!class_exists('Marketplace')) :
    /**
     * Marketplace main class.
     */
    final class Marketplace
    {
        /**
         * Variable to declase instance.
         *
         * @var instance
         */
        private static $_instance = null;
        /**
         * Variable for session.
         *
         * @var session variable
         */
        public $session = null;

        /**
         * Variable for query.
         *
         * @var query variable
         */
        public $query = null;

        /**
         * Variable for MP_Seller.
         *
         * @var MP_Seller variable
         */
        public $MP_Seller = null;

        /**
         * Variable for MP_login.
         *
         * @var MP_login variable
         */
        private $MP_login = null;

        /**
         * Variable for page_title_display.
         *
         * @var page_title_display variable
         */
        private $page_title_display = 1;

        /**
         * Making a instance of itself.
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Main include function.
         */
        private function includes()
        {

            include_once 'includes/class-mp-install.php';

            include_once 'includes/class-mp-uninstall.php';

            include_once 'includes/class-mp-query-functions.php';

            include_once 'includes/class-mp-form-handler.php';

            include_once 'includes/class-mp-ajax-hooks.php';

            include_once 'includes/class-mp-ajax-functions.php';

            include_once 'includes/class-mp-post-data-handler.php';

            include_once WK_MARKETPLACE_DIR . 'includes/class-mp-order-refund.php';

            include_once 'includes/class-mp-save-notifications.php';

            $enable_shiping_methord = get_option('wk_mp_shipping_plugin');

            if (true != $enable_shiping_methord) {
                include_once 'includes/class-mp-flat-rate-shipping.php';
            }

            include_once 'includes/class-mp-commission.php';

            include_once 'includes/class-mp-transaction.php';

            $this->mp_classes();

            if (is_admin()) {
                include_once 'includes/templates/admin/class-mp-product-templates.php';

                include_once 'includes/templates/admin/class-mp-order-templates.php';

                include_once 'includes/templates/admin/class-mp-profile-templates.php';

                include_once 'includes/admin/index.php';

                add_action('admin_enqueue_scripts', array($this, 'admin_load_style'));

                include_once 'includes/admin/event-handler.php';

                include_once 'includes/admin/mp-function-handler.php';

                include_once 'includes/admin/mp-order-functions.php';
            }

            // FRONTEND.
            if (!is_admin()) {
                $this->frontend_includes();

                if (isset($_GET['act'])) {
                    include_once 'includes/front/profile.php';
                } else {
                    include_once 'includes/front/index.php';
                }
            }

            include_once 'includes/class-mp-global-hooks.php';
        }

        /**
         * Initialize MP Classes.
         */
        public function mp_classes()
        {
            global $commission, $transaction;

            $commission = new MP_Commission();
            $transaction = new MP_Transaction();
        }

        /**
         * Load admin side style.
         */
        public function admin_load_style()
        {
            wp_register_style('marketplace', WK_MARKETPLACE . 'assets/css/admin.css');

            wp_enqueue_style('marketplace');
        }

        /**
         * Load frontend files.
         */
        public function frontend_includes()
        {
            include_once 'includes/class-favourite-seller.php';

            include_once 'includes/class-mp-frontend-scripts.php';

            include_once 'includes/front/class-mp-product-functions.php';

            include_once 'includes/front/class-mp-user-functions.php';

            include_once 'includes/front/class-mp-order-functions.php';

            include_once 'includes/front/mp-account-functions.php';

            include_once 'includes/templates/front/class-mp-shipping-functions.php';

            include_once 'includes/templates/front/class-mp-product-templates.php';

            include_once 'includes/templates/front/class-mp-user-functions.php';

            include_once 'includes/templates/front/myaccount/register.php';

            include_once 'includes/front/handlers/class-mp-login-handler.php';

            include_once 'includes/front/handlers/class-mp-register-handler.php';

            include_once 'includes/templates/front/single-product/favourite-seller.php';

            include_once 'includes/templates/front/single-product/product-author.php';

            include_once 'includes/front/event-handler.php';
        }

        /**
         * Function to include widget.
         */
        public function include_widgets()
        {
            include_once 'includes/widgets/class-mp-sellerpanel.php';

            include_once 'includes/widgets/class-mp-sellerlist.php';
        }

        /**
         * Marketplace constructor.
         */
        public function __construct()
        {
            // Auto-load classes on demand.
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload');
            }
            $this->includes();

            add_action('plugins_loaded', array($this, 'myplugin_load_textdomain'));

            add_action('widgets_init', array($this, 'include_widgets'));

            add_action('init', array($this, 'init'), 0);

            add_action('admin_enqueue_scripts', array($this, 'user_load_script'));

            add_action('wp_enqueue_scripts', array($this, 'front_enqueue_script'));

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wk_mp_plugin_settings_link'));

            add_filter('woocommerce_email_classes', array($this, 'mp_add_new_email_notification'), 10, 1);

            add_action('woocommerce_shipping_zone_method_added', array($this, 'mp_after_add_admin_shipping_zone'), 10, 3);

            add_action('woocommerce_delete_shipping_zone', array($this, 'mp_action_woocommerce_delete_shipping_zone'), 10, 1);

            add_action('woocommerce_shipping_classes_save_class', array($this, 'mp_after_add_admin_shipping_class'), 10, 2);

            add_filter('the_title', array($this, 'mp_hide_page_title'));

            add_filter('sidebars_widgets', array($this, 'mp_remove_sidebar_seller_page'));

            add_action('admin_init', array($this, 'mp_redirect_seller_tofront'));

            add_action('template_redirect', array($this, 'mp_redirect_seller_tofront'));

            add_action('woocommerce_checkout_order_processed', array($this, 'mp_add_order_commission_data'), 1, 1);

            add_action('woocommerce_order_status_cancelled', array($this, 'mp_action_on_order_cancel'), 10, 1);

            add_action('woocommerce_order_status_failed', array($this, 'mp_action_on_order_changed_mails'), 10, 1);

            add_action('woocommerce_order_status_on-hold', array($this, 'mp_action_on_order_changed_mails'), 10, 1);

            add_action('woocommerce_order_status_processing', array($this, 'mp_action_on_order_changed_mails'), 10, 1);

            add_action('woocommerce_order_status_completed', array($this, 'mp_action_on_order_changed_mails'), 10, 1);

            add_action('woocommerce_order_status_refunded', array($this, 'mp_action_on_order_changed_mails'), 10, 1);

            add_action('draft_to_publish', array($this, 'mp_action_on_product_approve'), 10, 1);

            add_action('wp_trash_post', array($this, 'mp_action_on_product_disapprove'), 10, 1);

            do_action('marketplace_loaded');

            add_filter('plugin_row_meta', array($this, 'mp_plugin_row_meta'), 10, 2);

            add_filter('woocommerce_email_actions', array($this, 'wkmp_add_woocommerce_email_actions'));

            add_filter('woocommerce_screen_ids', array($this, 'wkmp_set_wc_screen_ids'), 10, 1);

            add_action('woocommerce_order_status_refunded', array($this, 'wkmp_add_seller_refund_data_on_order_fully_refunded'), 10, 1);

            add_action('woocommerce_refund_created', array($this, 'wkmp_add_seller_refund_data_on_order_refund'), 10, 2);

        }

        public function wkmp_add_seller_refund_data_on_order_refund($refund_id, $refund_args)
        {

            if (is_admin() && isset($_GET['page']) && $_GET['page'] != 'order-history' && !empty($refund_id)) {

                $refund_line_items = $refund_args['line_items'];

                $refund_total_tax_amount = 0;

                foreach ($refund_line_items as $key => $refund_line_item) {

                    $refund_total_tax_amount += !empty($refund_line_item['refund_tax']) ? array_sum($refund_line_item['refund_tax']) : 0;

                }

                $refund_args['amount'] -= $refund_total_tax_amount;

                $order_refund = new MP_Order_Refund();

                $order_refund->wkmp_set_refund_args($refund_args);

                $order_refund->wkmp_set_seller_order_refund_data();

            }

        }

        public function wkmp_add_seller_refund_data_on_order_fully_refunded($order_id)
        {

            if (!empty($order_id)) {

                global $wpdb;

                $sellers_order_data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT seller_id, seller_amount FROM {$wpdb->prefix}mporders WHERE order_id = %d", $order_id));

                $seller_data = array();

                if (!empty($sellers_order_data)) {

                    foreach ($sellers_order_data as $key => $seller_order_data) {

                        $seller_id = $seller_order_data->seller_id;

                        if (array_key_exists($seller_id, $seller_data)) {

                            $seller_data[$seller_id] += $seller_order_data->seller_amount;

                        } else {

                            $seller_data[$seller_id] = $seller_order_data->seller_amount;

                        }

                    }

                }

                foreach ($seller_data as $seller_id => $total_seller_amount) {

                    $shipping_cost = $wpdb->get_var($wpdb->prepare("SELECT meta_value from {$wpdb->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $seller_id, $order_id));

                    if (!empty($shipping_cost)) {
                        $total_seller_amount += $shipping_cost;
                    }

                    $seller_order_refund_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $seller_id, $order_id, '_wkmp_refund_status'));

                    if (empty($seller_order_refund_data)) {

                        $seller_order_refund_data = array(
                            'line_items' => array(),
                            'refunded_amount' => wc_format_decimal($total_seller_amount)
                        );

                        $wpdb->insert(

                            "{$wpdb->prefix}mporders_meta",
                            array(
                                'seller_id' => $seller_id,
                                'order_id' => $order_id,
                                'meta_key' => '_wkmp_refund_status',
                                'meta_value' => maybe_serialize($seller_order_refund_data),
                            ),
                            array('%d', '%d', '%s', '%s')
                        );

                    } else {

                        $seller_order_refund_data = maybe_unserialize($seller_order_refund_data);

                        $seller_order_refund_data['refunded_amount'] = wc_format_decimal($total_seller_amount);

                        $wpdb->update(

                            "{$wpdb->prefix}mporders_meta",
                            array(
                                'meta_value' => maybe_serialize($seller_order_refund_data),
                            ),
                            array(
                                'seller_id' => $seller_id,
                                'order_id' => $order_id,
                                'meta_key' => '_wkmp_refund_status',
                            ),
                            array('%s'),
                            array('%d', '%d', '%s')
                        );

                    }

                }

            }

        }

        public function wkmp_set_wc_screen_ids($array)
        {
            array_push($array, strtolower(esc_html__('Marketplace', 'marketplace')) . '_page_sellers', 'marketplace_page_Settings');

            return $array;
        }

        public function wkmp_add_woocommerce_email_actions($actions)
        {
            $actions[] = 'asktoadmin_mail';
            $actions[] = 'woocommerce_product_notifier_admin';
            $actions[] = 'woocommerce_product_approve_disapprove';
            $actions[] = 'woocommerce_approve_seller';
            $actions[] = 'woocommerce_disapprove_seller';
            $actions[] = 'woocommerce_seller_new_order';
            $actions[] = 'woocommerce_seller_order_cancelled';
            $actions[] = 'woocommerce_seller_order_failed';
            $actions[] = 'woocommerce_seller_order_onhold';
            $actions[] = 'woocommerce_seller_order_processing';
            $actions[] = 'woocommerce_seller_order_completed';
            $actions[] = 'woocommerce_seller_order_refunded_partially';
            $actions[] = 'woocommerce_seller_order_refunded_completely';
            $actions[] = 'woocommerce_admin_reply_to_seller';
            $actions[] = 'woocommerce_shop_follower';
            $actions[] = 'new_seller_registration';
            $actions[] = 'new_user_registration_link';

            return $actions;
        }

        public function mp_plugin_row_meta($links, $file)
        {
            if (plugin_basename(__FILE__) === $file) {
                $row_meta = array(
                    'docs' => '<a href="' . esc_url(apply_filters('wk_marketplace_docs_url', 'https://webkul.com/blog/wordpress-woocommerce-marketplace/')) . '" aria-label="' . esc_attr__('View Marketplace documentation', 'marketplace') . '">' . esc_html__('Docs', 'marketplace') . '</a>',
                    'support' => '<a href="' . esc_url(apply_filters('wk_marketplace_support_url', 'https://webkul.uvdesk.com/')) . '" aria-label="' . esc_attr__('Visit customer support', 'marketplace') . '">' . esc_html__('Support', 'marketplace') . '</a>',
                );

                return array_merge($links, $row_meta);
            }

            return (array)$links;
        }

        /**
         * Action_on_order_cancel.
         *
         * @param int $ord_id order id
         */
        public function mp_action_on_order_cancel($ord_id)
        {
            global $wpdb, $woocommerce, $commission;

            $order = wc_get_order($ord_id);

            $seller_list = $commission->get_sellers_in_order($ord_id);

            foreach ($seller_list as $seller_id) {
                $sel_info = $commission->get_sel_comission_via_order($ord_id, $seller_id);

                $seller_amt = $sel_info['total_seller_amount'];

                $admin_amt = $sel_info['total_commission'];

                $seller = $wpdb->get_results($wpdb->prepare(" SELECT * from {$wpdb->prefix}mpcommision WHERE seller_id = %d", $seller_id));

                if ($seller) {
                    $seller = $seller[0];

                    $admin_amount = floatval($seller->admin_amount) - $admin_amt;

                    $seller_amount = floatval($seller->seller_total_ammount) - $seller_amt;

                    $s = $wpdb->get_results($wpdb->prepare(" UPDATE {$wpdb->prefix}mpcommision set admin_amount = %f, seller_total_ammount = %f WHERE seller_id = %d", $admin_amount, $seller_amount, $seller_id));
                }
            }

            $this->send_mail_to_inform_seller_for_order_status($order);

        }

        public function mp_action_on_order_changed_mails($ord_id)
        {

            $order = wc_get_order($ord_id);
            $send_mail_to_seller = apply_filters('wkmp_send_notification_mail_to_seller_for_new_order', true, $order);
            if ($send_mail_to_seller) {
                $this->send_mail_to_inform_seller_for_order_status($order);
            }

        }

        public function mp_action_on_product_approve($post)
        {
            if ($post->post_type == "product") {
                $author_id = get_post_field('post_author', $post->ID);
                if (!is_super_admin($author_id)) {
                    if (!get_post_meta($post->ID, 'mp_admin_view')) {
                        update_option('wkmp_approved_product_count', (int)(get_option('wkmp_approved_product_count', 1) - 1));
                    }
                    do_action('woocommerce_product_approve_disapprove', $author_id, $post->ID);

                }

            }
        }

        public function mp_action_on_product_disapprove($post_id)
        {
            $post_type = get_post_type($post_id);
            if ($post_type == "product") {
                $author_id = get_post_field('post_author', $post_id);
                if (!is_super_admin($author_id)) {
                    if (!get_post_meta($post->ID, 'mp_admin_view')) {
                        update_option('wkmp_approved_product_count', (int)(get_option('wkmp_approved_product_count', 1) - 1));
                    }
                    do_action('woocommerce_product_approve_disapprove', $author_id, $post_id, 'disapprove');
                }
            }
        }

        public function send_mail_to_inform_seller_for_order_status($order)
        {
            $items = $order->get_items();
            $per_seller_items = $this->product_from_diffrent_seller($items);
            $recent_user = wp_get_current_user();
            $cur_email = $recent_user->user_email;
            $order_status = $order->get_status();
            foreach ($per_seller_items as $key => $items) {
                if ($order_status == 'cancelled') {
                    do_action('woocommerce_seller_order_cancelled', $items, $key);
                } elseif ($order_status == 'failed') {
                    do_action('woocommerce_seller_order_failed', $items, $key);
                } elseif ($order_status == 'on-hold') {
                    do_action('woocommerce_seller_order_onhold', $items, $key);
                } elseif ($order_status == 'processing') {
                    do_action('woocommerce_seller_order_processing', $items, $key);
                } elseif ($order_status == 'completed') {
                    do_action('woocommerce_seller_order_completed', $items, $key);
                } elseif ($order_status == 'refunded') {
                    do_action('woocommerce_seller_order_refunded_completely', $items, $key);
                }

            }
        }

        public function product_from_diffrent_seller($items)
        {
            $mp_product_author = array();
            foreach ($items as $key => $item) {
                $item_id = $item['product_id'];
                $author_email = $this->inform_marketplace_seller($item_id);
                $send_to = $author_email[0]->user_email;
                if (in_array($send_to, $mp_product_author)) {
                    $mp_product_author[$send_to][] = $item;
                } else {
                    $mp_product_author[$send_to][] = $item;
                }
            }
            return $mp_product_author;
        }

        public function inform_marketplace_seller($pid)
        {
            global $wpdb;
            $query = "select user_email from $wpdb->users as user join {$wpdb->prefix}posts as post on post.post_author=user.ID where post.ID=$pid";

            return $wpdb->get_results($query);
        }

        /**
         * Calculate the commission, discount and shipping for the order at processing.
         *
         * @param int $order_id order which is been processed
         */
        public function mp_add_order_commission_data($order_id)
        {
            include WK_MARKETPLACE_DIR . 'includes/admin/mp-on-order-processing.php';
        }

        /**
         * Function to redirect seller.
         */
        public function mp_redirect_seller_tofront()
        {
            global $wp_query, $wpdb;
            $current_user = wp_get_current_user();
            $role_name = $current_user->roles;
            $sep_dash = get_user_meta($current_user->ID, 'wkmp_seller_backend_dashboard', true);
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            $allowed_pages = array(
                get_option('mp_store', 'store'),
                'profile',
                'add-feedback',
                'feedback',
                get_option('mp_seller_product', 'seller-product')
            );
            if (!empty(get_option('wkmp_enable_seller_seperate_dashboard')) && !empty($sep_dash) && in_array('wk_marketplace_seller', $role_name, true) && (get_query_var('pagename') == $page_name) && !in_array(get_query_var('main_page'), $allowed_pages, true)) {
                if (!is_admin()) {
                    wp_safe_redirect(admin_url('admin.php?page=seller'));
                    exit;
                }
            } elseif (empty(get_option('wkmp_enable_seller_seperate_dashboard')) || empty($sep_dash) && !in_array(get_query_var('main_page'), $allowed_pages, true)) {
                $role = get_role('wk_marketplace_seller');
                $role->remove_cap('manage_woocommerce');
                $role->remove_cap('read_product');
                $role->remove_cap('edit_product');
                $role->remove_cap('delete_product');
                $role->remove_cap('edit_products');
                $role->remove_cap('publish_products');
                $role->remove_cap('read_private_products');
                $role->remove_cap('delete_products');
                $role->remove_cap('edit_published_products');
                $role->remove_cap('assign_product_terms');

                if (defined('DOING_AJAX') || '/wp-admin/async-upload.php' === $_SERVER['PHP_SELF']) {
                    return;
                }

                if (in_array('wk_marketplace_seller', $role_name, true) && is_admin()) {
                    wp_safe_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')));
                    exit;
                }
            }
        }

        /**
         * Hide sidebar for seller page.
         *
         * @param array $sidebars_widgets list of widgets
         */
        public function mp_remove_sidebar_seller_page($sidebars_widgets)
        {
            global $wpdb;
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            $page_array = array(
                get_option('mp_store', 'store'),
                get_option('mp_seller_product', 'seller-product'),
                'feedback',
                'add-feedback',
            );
            if (is_page($page_name) && !in_array(get_query_var('main_page'), $page_array, true)) {
                $sidebars_widgets = array(false);
            }

            return $sidebars_widgets;
        }

        /**
         * Hide page entry tile for seller page.
         *
         * @param string $title title
         */
        public function mp_hide_page_title($title)
        {
            global $wpdb, $wp_query;
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            if (in_the_loop() && is_page($page_name) && $this->page_title_display == 1) {
                $this->page_title_display = 0;

                if ((null !== get_query_var('ship_page') && get_option('mp_shipping', 'shipping') === get_query_var('ship_page')) || (null !== get_query_var('ship') && get_option('mp_shipping', 'shipping') === get_query_var('ship'))) {
                    return esc_html(get_option('mp_shipping_name', esc_html__('Shipping Zone', 'marketplace')));
                }

                if (null !== get_query_var('main_page')) {
                    $main_page = get_query_var('main_page');
                    switch ($main_page) {
                        case get_option('mp_to', 'to'):
                            return esc_html(get_option('mp_to_name', esc_html__('Ask to Admin', 'marketplace')));

                        case get_option('mp_product_list', 'product-list'):
                            return esc_html(get_option('mp_product_list_name', esc_html__('Products', 'marketplace')));

                        case get_option('mp_add_product', 'add-product'):
                            return esc_html(get_option('mp_add_product_name', esc_html__('Add Product', 'marketplace')));

                        case get_option('mp_order_history', 'order-history'):
                            return esc_html(get_option('mp_order_history_name', esc_html__('Order History', 'marketplace')));

                        case get_option('mp_notification', 'notification'):
                            return esc_html(get_option('mp_notification_name', esc_html__('Notification', 'marketplace')));

                        case get_option('mp_shop_follower', 'shop-follower'):
                            return esc_html(get_option('mp_shop_follower_name', esc_html__('Shop Follower', 'marketplace')));

                        case get_option('mp_dashboard', 'dashboard'):
                            return esc_html(get_option('mp_dashboard_name', esc_html__('Dashboard', 'marketplace')));

                        case get_option('mp_profile', 'profile'):
                            return esc_html(get_option('mp_profile_name', esc_html__('Profile', 'marketplace')));

                        case get_option('mp_product', 'product'):
                            return esc_html(get_option('mp_product_name', esc_html__('Product', 'marketplace')));

                        case get_option('mp_transaction', 'transaction'):
                            return esc_html(get_option('mp_transaction_name', esc_html__('Transaction', 'marketplace')));

                        default:
                            return '';
                    }
                }
            }

            return $title;
        }

        /**
         * Delete mapped zone.
         *
         * @param int $id shipping zone id
         */
        public function mp_action_woocommerce_delete_shipping_zone($id)
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'mpseller_meta';

            if ($id) {
                $wpdb->delete($table_name, array('zone_id' => $id), array('%d'));
            }
        }

        /**
         * Map admin shipping zone with sellers.
         *
         * @param int $instance_id instance id
         * @param string $type type
         * @param int $id id
         */
        public function mp_after_add_admin_shipping_zone($instance_id, $type, $id)
        {
            global $wpdb;
            $result = '';
            $sql = '';
            $user_id = get_current_user_id();
            if (!empty($id)) {
                $table_name = $wpdb->prefix . 'mpseller_meta';
                $sql = $wpdb->prepare("SELECT count(*) as total from $table_name where zone_id = '%s'", $id);
                $result = $wpdb->get_results($sql);
                if ($result && intval($result[0]->total) < 1) {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'seller_id' => $user_id,
                            'zone_id' => $id,
                        )
                    );
                }
            }
        }

        /**
         * Add class data as user meta.
         *
         * @param int $term_id term id
         * @param array $data data
         */
        public function mp_after_add_admin_shipping_class($term_id, $data)
        {
            global $current_user;

            $seller_sclass = array();

            $seller_sclass = get_user_meta($current_user->ID, 'shipping-classes', true);

            $seller_sclass = maybe_unserialize($seller_sclass);

            array_push($seller_sclass, $term_id);

            $seller_sclass_update = maybe_serialize($seller_sclass);

            update_user_meta($current_user->ID, 'shipping-classes', $seller_sclass_update);
        }

        /**
         * Load plugin textdomain.
         *
         * @since 1.0.0
         */
        public function myplugin_load_textdomain()
        {
            load_plugin_textdomain('marketplace', false, basename(dirname(__FILE__)) . '/languages');

            $seprate_dash = get_user_meta(get_current_user_id(), 'wkmp_seller_backend_dashboard', true);

            if (get_option('wkmp_enable_seller_seperate_dashboard') && $seprate_dash) {

                include_once 'includes/separate-seller-dashboard/class-seller-backend-hooks.php';
            }

            global $wpdb;

            $table_name = $wpdb->prefix . 'mpfeedback';
            if ($wpdb->get_var("show tables like '$table_name'") === $table_name) {
                $s = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'status'");

                if (isset($s) && !$s) {
                    $wpdb->query("ALTER TABLE $table_name ADD status int(1) NOT NULL DEFAULT 0");
                }
            }

            if (current_user_can('wk_marketplace_seller')) {
                show_admin_bar(false);
            }
        }

        /**
         * Load the link of the addon link.
         *
         * @param array $links list of links at plugin list page
         */
        public function wk_mp_plugin_settings_link($links)
        {
            $url = 'https://wordpressdemo.webkul.com';

            $settings_link = '<a href="' . $url . '" target="_blank" style="color:green;">' . esc_html__('Add-ons', 'marketplace') . '</a>';

            $links[] = $settings_link;

            return $links;
        }

        /**
         * Adds marketplace email classes.
         *
         * @param array $email default mail array
         */
        public function mp_add_new_email_notification($email)
        {
            $email['WC_Email_AskToAdmin'] = include 'class-wc-email-asktoadmin.php';

            $email['WC_Email_ProductApprove'] = include 'class-wc-email-product-approve.php';

            $email['MP_Product_Approve_Disapprove_Email'] = include 'class-wc-email-product-approve-disapprove.php';

            $email['WC_Email_sellerApproval'] = include 'class-wc-email-seller-approve.php';

            $email['WC_Email_sellerdisApproval'] = include 'class-wc-email-seller-disapprove.php';

            $email['WC_Email_Seller_register'] = include 'class-wc-email-seller-register.php';

            $email['WC_Email_Seller_order_placed'] = include 'class-wc-email-seller-order-placed.php';

            $email['WC_Email_Seller_order_cancelled'] = include 'class-wc-email-seller-order-cancelled.php';

            $email['WC_Email_Seller_order_failed'] = include 'class-wc-email-seller-order-failed.php';

            $email['WC_Email_Seller_order_onhold'] = include 'class-wc-email-seller-order-onhold.php';

            $email['WC_Email_Seller_order_processing'] = include 'class-wc-email-seller-order-processing.php';

            $email['WC_Email_Seller_order_completed'] = include 'class-wc-email-seller-order-completed.php';

            $email['WC_Email_Seller_order_refunded'] = include 'class-wc-email-seller-order-refunded.php';

            $email['WC_Email_Seller_Query_Reply'] = include 'class-wc-email-seller-query-reply.php';

            $email['WC_Email_Shop_Follower_Notification'] = include 'class-wc-email-seller-shop-follower-notification.php';

            return $email;
        }

        /**
         * Register and enqueue a script for use.
         *
         * @param string $handle
         * @param string $path
         * @param array $localize_data
         * @param string[] $deps
         * @param string $version
         * @param boolean $in_footer
         * @uses   wp_enqueue_script()
         * @access public
         */
        public static function enqueue_script($handle, $path = '', $localize_data = array(), $deps = array('jquery'), $version = SUMO_PP_PLUGIN_VERSION, $in_footer = false)
        {
            wp_register_script($handle, $path, $deps, $version, $in_footer);

            $name = str_replace('-', '_', $handle);
            wp_localize_script($handle, $name, $localize_data);
            wp_enqueue_script($handle);
        }

        /**
         * Register and enqueue a styles for use.
         *
         * @param string $handle
         * @param string $path
         * @param string[] $deps
         * @param string $version
         * @param string $media
         * @param boolean $has_rtl
         * @uses   wp_enqueue_style()
         * @access public
         */
        public static function enqueue_style($handle, $path = '', $deps = array(), $version = SUMO_PP_PLUGIN_VERSION, $media = 'all', $has_rtl = false)
        {
            wp_register_style($handle, $path, $deps, $version, $media, $has_rtl);
            wp_enqueue_style($handle);
        }

        /**
         * Enqueue Footable.
         */
        public static function enqueue_footable_scripts()
        {

            self::enqueue_script('sumo-pp-footable', plugins_url() . '/sumopaymentplans/assets/js/footable/footable.js');
            self::enqueue_script('sumo-pp-footable-sort', plugins_url() . '/sumopaymentplans/assets/js/footable/footable.sort.js');
            self::enqueue_script('sumo-pp-footable-paginate', plugins_url() . '/sumopaymentplans/assets/js/footable/footable.paginate.js');
            self::enqueue_script('sumo-pp-footable-filter', plugins_url() . '/sumopaymentplans/assets/js/footable/footable.filter.js');
            self::enqueue_script('sumo-pp-footable-action', plugins_url() . '/sumopaymentplans/assets/js/footable/sumo-pp-footable.js');

            self::enqueue_style('sumo-pp-footable-core', plugins_url() . '/sumopaymentplans/assets/css/footable/footable.core.css');
            self::enqueue_style('sumo-pp-footable-standalone', plugins_url() . '/sumopaymentplans/assets/css/footable/footable.standalone.css');
            self::enqueue_style('sumo-pp-footable-bootstrap', plugins_url() . '/sumopaymentplans/assets/css/footable/bootstrap.css');
            self::enqueue_style('sumo-pp-footable-chosen', plugins_url() . '/sumopaymentplans/assets/css/footable/chosen.css');

            self::enqueue_style('sumo-pp-jquery', plugins_url() . '/sumopaymentplans/assets/css/sumo-pp-jquery.tipTip.css');
            self::enqueue_style('sumo-pp-single-product', plugins_url() . '/sumopaymentplans/assets/css/sumo-pp-single-product-page.css');
            self::enqueue_style('sumo-pp-date-time-picker', plugins_url() . '/woocommerce-pdf-vouchers/includes/meta-boxes/css/datetimepicker/date-time-picker.css');
            self::enqueue_style('sumo-pp-custom-css', plugins_url() . '/wk-woocommerce-marketplace/assets/css/product-pp.css');
        }

        /**
         * Enqueue WC Multiselect field
         */
        public static function enqueue_wc_multiselect()
        {
            wp_register_script('wc-enhanced-select',plugins_url() . '/woocommerce/assets/js/admin/wc-enhanced-select.min.js');
            wp_localize_script(
                'wc-enhanced-select',
                'wc_enhanced_select_params',
                array(
                    'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
                    'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_long_1'     => _x( 'as Please delete 1 character', 'enhanced select', 'woocommerce' ),
                    'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
                    'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
                    'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
                    'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
                    'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
                    'ajax_url'                  => admin_url( 'admin-ajax.php' ),
                    'search_products_nonce'     => wp_create_nonce( 'search-products' ),
                    'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
                    'search_categories_nonce'   => wp_create_nonce( 'search-categories' ),
                )
            );
            wp_enqueue_script('wc-enhanced-select','', array('jquery'));
        }

        /**
         * Include front scripts.
         */
        public function front_enqueue_script()
        {
            wp_enqueue_media();

            global $wpdb;

            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            self::enqueue_wc_multiselect();
            self::enqueue_script('sumo-pp-admin-product', WK_MARKETPLACE . 'assets/js/sumo-pp-admin-product.js', array(
                'decimal_sep' => get_option('woocommerce_price_decimal_sep', '.'),
                'plan_search_nonce' => wp_create_nonce('sumo-pp-get-payment-plan-search-field'),
            ));
            self::enqueue_footable_scripts();
            self::enqueue_script('sumo-pp-admin-bulk-action-settings', plugins_url() . '/sumopaymentplans/assets/js/admin/sumo-pp-admin-bulk-action-settings.js', array(
                'decimal_sep' => get_option('woocommerce_price_decimal_sep', '.'),
                'plan_search_nonce' => wp_create_nonce('sumo-pp-get-payment-plan-search-field'),
                'is_custom_frontend' => true
            ));
            self::enqueue_script('sumo-pp-admin-general-settings', plugins_url() . '/sumopaymentplans/assets/js/admin/sumo-pp-admin-general-settings.js', array(
                'decimal_sep' => get_option('woocommerce_price_decimal_sep', '.'),
                'plan_search_nonce' => wp_create_nonce('sumo-pp-get-payment-plan-search-field'),
            ));

            add_action( 'wp_enqueue_scripts', 'yith_add_select2_scripts', 99 );
            function yith_add_select2_scripts() {

                wp_enqueue_script( 'selectWoo' );
                wp_enqueue_style( 'select2' );

            }

            wp_enqueue_script('marketplace', WK_MARKETPLACE . 'assets/js/plugin.js', array('jquery'), '');

            wp_enqueue_script('mp-front-ajax', WK_MARKETPLACE . 'assets/js/front-ajax-handler.js', '', '');

            wp_enqueue_script('marketplace-shipping', WK_MARKETPLACE . 'assets/js/shipping-class.js', array('jquery'));

            if (is_page($page_name)) {
                wp_dequeue_style('bootstrap-css');
                wp_enqueue_script('select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js');
                wp_enqueue_style('select2-css', plugins_url() . '/woocommerce/assets/css/select2.css');
            }

            $ship_arr = array(
                'ship1' => esc_html__('Remove', 'marketplace'),
                'ship2' => esc_html__('Shipping Class Name', 'marketplace'),
                'ship3' => esc_html__('Cancel changes', 'marketplace'),
                'ship4' => esc_html__('Slug', 'marketplace'),
                'ship5' => esc_html__('Description for your reference', 'marketplace'),
                'ship6' => esc_html__('Are you sure you want to delete this zone?', 'marketplace'),
            );

            wp_localize_script(
                'marketplace-shipping',
                'the_mpajax_shipping_script',
                array(
                    'shippingajaxurl' => admin_url('admin-ajax.php'),
                    'shippingNonce' => wp_create_nonce('shipping-ajaxnonce'),
                    'ship_tr' => $ship_arr,
                )
            );

            $mkt_tr_arr = array(
                'mkt1' => esc_html__('Please select customer from the list', 'marketplace'),
                'mkt2' => esc_html__('this field could not be left blank', 'marketplace'),
                'mkt3' => esc_html__('please enter valid product sku, it shoud be equal or larger than 3 characters', 'marketplace'),
                'mkt4' => esc_html__('Please Enter SKU', 'marketplace'),
                'mkt5' => esc_html__('Sale Price cannot be greater than Regular Price.', 'marketplace'),
                'mkt6' => esc_html__('Invalid Price.', 'marketplace'),
                'mkt7' => esc_html__('Invalid input.', 'marketplace'),
                'mkt8' => esc_html__('Please Enter Product Name!!!', 'marketplace'),
                'mkt9' => esc_html__('First name is not valid', 'marketplace'),
                'mkt10' => esc_html__('Last name is not valid', 'marketplace'),
                'mkt11' => esc_html__('E-mail is not valid', 'marketplace'),
                'mkt12' => esc_html__('Shop name is not valid', 'marketplace'),
                'mkt13' => esc_html__('Phone number length must not exceed 10.', 'marketplace'),
                'mkt14' => esc_html__('Phone number not valid.', 'marketplace'),
                'mkt15' => esc_html__('Field left blank!!!', 'marketplace'),
                'mkt16' => esc_html__('Seller User Name is not valid', 'marketplace'),
                'mkt17' => esc_html__('user name available', 'marketplace'),
                'mkt18' => esc_html__('User Name Already Taken', 'marketplace'),
                'mkt19' => esc_html__('Cannot Leave Field Blank', 'marketplace'),
                'mkt20' => esc_html__('Email Id Already Registered', 'marketplace'),
                'mkt21' => esc_html__('Email adress is not valid', 'marketplace'),
                'mkt22' => esc_html__('select seller option', 'marketplace'),
                'mkt23' => esc_html__('seller store name is too short,contain white space or empty', 'marketplace'),
                'mkt24' => esc_html__('address is too short or empty', 'marketplace'),
                'mkt25' => esc_html__('Subject field can not be blank.', 'marketplace'),
                'mkt26' => esc_html__('Subject not valid.', 'marketplace'),
                'mkt27' => esc_html__('Ask Your Question (Message length should be less than 500).', 'marketplace'),
                'mkt28' => esc_html__('Online', 'marketplace'),
                'mkt29' => esc_html__('Attribute name', 'marketplace'),
                'mkt30' => esc_html__('attribue value by seprating comma eg. a|b|c', 'marketplace'),
                'mkt31' => esc_html__('Attribute Value eg. a|b|c', 'marketplace'),
                'mkt32' => esc_html__('Remove', 'marketplace'),
                'mkt33' => esc_html__('Visible on the product page', 'marketplace'),
                'mkt34' => esc_html__('Used for variations', 'marketplace'),
                'mkt35' => esc_html__('Price, Value, Quality rating cannot be empty.', 'marketplace'),
                'mkt36' => esc_html__('Required field.', 'marketplace'),
                'mkt37' => esc_html__('Please enter username or email address.', 'marketplace'),
                'mkt38' => esc_html__('Please enter password.', 'marketplace'),
                'mkt39' => esc_html__('Please enter username', 'marketplace'),
                'fajax1' => esc_html__('Are You sure you want to delete this Seller..?', 'marketplace'),
                'fajax2' => esc_html__('Are You sure you want to delete this Customer..?', 'marketplace'),
                'fajax3' => esc_html__('No Sellers Available.', 'marketplace'),
                'fajax4' => esc_html__('No Followers Available.', 'marketplace'),
                'fajax5' => esc_html__('There was some issue in process. Please try again.!', 'marketplace'),
                'fajax6' => esc_html__('Are You sure you want to delete customer(s) from list..?', 'marketplace'),
                'fajax7' => esc_html__('select customers to delete from list.!', 'marketplace'),
                'fajax8' => esc_html__('Subject field cannot be empty.', 'marketplace'),
                'fajax9' => esc_html__('Message field cannot be empty.', 'marketplace'),
                'fajax10' => esc_html__('Mail Sent Successfully', 'marketplace'),
                'fajax11' => esc_html__('Error Sending Mail.', 'marketplace'),
                'fajax12' => esc_html__('Not Available', 'marketplace'),
                'fajax13' => esc_html__('Already Exists', 'marketplace'),
                'fajax14' => esc_html__('Available', 'marketplace'),
                'fajax15' => esc_html__('No Group found', 'marketplace'),
                'fajax16' => esc_html__('Refund Cancel', 'marketplace'),
                'fajax17' => esc_html__('Refund', 'marketplace'),
            );

            wp_localize_script(
                'marketplace',
                'the_mpajax_script',
                array(
                    'mpajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('ajaxnonce'),
                    'seller_page' => $page_name,
                    'site_url' => site_url(),
                    'mkt_tr' => $mkt_tr_arr,
                )
            );
        }

        public function user_load_script()
        {
            wp_enqueue_media();

            wp_enqueue_style('wp-color-picker');

            wp_enqueue_script('marketplace', WK_MARKETPLACE . 'assets/js/mpadminajax.js', array('jquery', 'wp-color-picker'));

            $admin_arr = array(
                'aajax1' => esc_html__('This field cannot be left blank', 'marketplace'),
                'aajax2' => esc_html__('Please enter the valid template name', 'marketplace'),
                'aajax3' => esc_html__('Please enter template name.', 'marketplace'),
                'aajax4' => esc_html__('Please enter the valid template name.', 'marketplace'),
                'aajax5' => esc_html__('Please select the base color.', 'marketplace'),
                'aajax6' => esc_html__('Please select the body color.', 'marketplace'),
                'aajax7' => esc_html__('Please select the background color.', 'marketplace'),
                'aajax8' => esc_html__('Please select the text color.', 'marketplace'),
                'aajax9' => esc_html__('Please enter the page width.', 'marketplace'),
                'aajax10' => esc_html__('Are you sure you want to update the status of seller', 'marketplace'),
                'aajax11' => esc_html__('Disapprove', 'marketplace'),
                'aajax12' => esc_html__('Approve', 'marketplace'),
                'aajax13' => esc_html__('Please fill shop name.', 'marketplace'),
                'aajax14' => esc_html__('Not Available', 'marketplace'),
                'aajax15' => esc_html__('Already Exists', 'marketplace'),
                'aajax16' => esc_html__('Available', 'marketplace'),
                'aajax17' => esc_html__('Select or Upload Media Of Your Chosen Persuasion', 'marketplace'),
                'aajax18' => esc_html__('Use this media', 'marketplace'),
                'aajax19' => esc_html__('Enter valid amount', 'marketplace'),
                'aajax20' => esc_html__('Sorry Account Balance is Low', 'marketplace'),
                'aajax21' => esc_html__('Processing...', 'marketplace'),
                'aajax22' => esc_html__('Paid', 'marketplace'),
                'aajax23' => esc_html__('Payment has been successfully done.', 'marketplace'),
                'aajax25' => esc_html__('Payment has been already done.', 'marketplace'),
                'aajax26' => esc_html__('Please enter valid page width.', 'marketplace'),
                'aajax27' => esc_html__('Error', 'marketplace'),
                'aajax28' => esc_html__('Success', 'marketplace'),
                'aajax29' => esc_html__('Oops, Unable to send mail to the seller.', 'marketplace'),
            );

            wp_localize_script(
                'marketplace', 'the_mpadminajax_script', array(
                    'mpajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('ajaxnonce'),
                    'adajax_tr' => $admin_arr,
                )
            );

            if (isset($_GET['page']) && ($_GET['page'] == 'products' || ($_GET['page'] == 'Settings' && isset($_GET['tab']) && $_GET['tab'] == 'products_setting') || ($_GET['page'] == 'sellers' && ((isset($_GET['action']) && $_GET['action'] == 'delete') || (isset($_GET['tab']) && $_GET['tab'] == 'assign_category'))))) {
                wp_enqueue_script('select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js');

                wp_enqueue_style('select2-css', plugins_url() . '/woocommerce/assets/css/select2.css');
            }

            if (get_option('wkmp_enable_seller_seperate_dashboard') && isset($_GET['page']) && $_GET['page'] == 'seller') {
                wp_enqueue_script(
                    'google_chart', "//www.google.com/jsapi?autoload={
						'modules':[
							{
								'name':'visualization',
							 	'version':'1',
								'packages':[
									'geochart'
								]
							}
						]
					}"
                );

                wp_enqueue_script('mp_chart_script', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');

                wp_register_script('mp-chart-js', WK_MARKETPLACE . '/assets/js/chart_script.js');

                wp_enqueue_script('mp-chart-js');
            }
        }

        public function make_seller_existing_user()
        {
            global $wpdb;

            $query = "select ID from $wpdb->users";

            $user_id = $wpdb->get_results($query);

            $mp_seller_query = "select user_id from {$wpdb->prefix}mpsellerinfo";

            $seller_id = $wpdb->get_results($mp_seller_query);

            $mp_seller = array();

            foreach ($seller_id as $seller) {
                $mp_seller[] = $seller->user_id;
            }

            foreach ($user_id as $id) {
                $user_query = new WP_User($id->ID);

                $mp_user_role = $user_query->roles[0];

                if (!in_array($id->ID, $mp_seller) && $mp_user_role == 'wk_marketplace_seller') {
                    $wpdb->get_results("insert into {$wpdb->prefix}mpsellerinfo (user_id,seller_key,seller_value)VALUES ($id->ID,'role','seller')");
                }

                if (in_array($id->ID, $mp_seller, true) && $mp_user_role != 'wk_marketplace_seller') {
                    $wpdb->get_results("update {$wpdb->prefix}mpsellerinfo set seller_value='0' where user_id=$id->ID");
                }
                if (in_array($id->ID, $mp_seller, true) && $mp_user_role == 'wk_marketplace_seller') {
                    $wpdb->get_results("update {$wpdb->prefix}mpsellerinfo set seller_value='seller' where user_id=$id->ID");
                }
            }
        }

        /**
         * Marketplace init function.
         */
        public function init()
        {
            add_action('pre_get_posts', array($this, 'marketplace_restrict_media_library'));

            do_action('before_marketplace_init');

            do_action('marketplace_init');

            add_filter('update_option_mp_shipping', array($this, 'wkmp_flush_rewrite_rules'));

            add_filter('update_option_mp_order_history', array($this, 'wkmp_flush_rewrite_rules'));

            add_filter('update_option_mp_seller_product', array($this, 'wkmp_flush_rewrite_rules'));

            add_filter('update_option_mp_store', array($this, 'wkmp_flush_rewrite_rules'));
        }

        public function wkmp_flush_rewrite_rules()
        {

            flush_rewrite_rules();

        }

        /**
         * Function to restrict media.
         *
         * @param obj $wp_query_obj query object
         */
        public static function marketplace_restrict_media_library($wp_query_obj)
        {
            global $current_user, $pagenow;

            if (!is_a($current_user, 'WP_User')) {
                return;
            }

            if ('admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments') {
                return;
            }

            if (!in_array($pagenow, array('upload.php', 'admin-ajax.php'), true)) {
                return;
            }

            if (!current_user_can('delete_pages')) {
                $wp_query_obj->set('author', $current_user->ID);
            }
        }
    }

endif;

/**
 * Check for WooCommerce.
 */
function MP()
{
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    if (is_plugin_active('woocommerce/woocommerce.php')) {
        return marketplace::instance();
    } else {
        add_shortcode('marketplace', 'woocommerce_not_installed');
    }
}

/**
 * Shows error when woooconerce not found.
 */
function woocommerce_not_installed()
{
    echo '<div class="error"><p>' . sprintf(__('WooCommerce Marketplace depends on the last version of %s or later to work!', 'marketplace') . '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . esc_html__('WooCommerce 3.0', 'woocommerce-colors') . '</a>') . '</p></div>';
}

$GLOBALS['marketplace'] = MP();
// seller approvement.
$mp_obj = MP();

/**
 * To get product image.
 *
 * @param int $pro_id product id
 * @param string $meta_value meta value
 */
function get_product_image_mp($pro_id, $meta_value)
{
    global $wpdb;

    $p = get_post_meta($pro_id, $meta_value, true);

    if (is_null($p)) {
        return '';
    }

    $product_image = get_post_meta($p, '_wp_attached_file', true);

    return $product_image;
}

add_filter('woocommerce_cart_needs_shipping', 'cart_transient_updation', 10, 1);
/**
 * Check cart for seller shipping zone.
 *
 * @param bool $needs_shipping cart need shipping
 */
function cart_transient_updation($needs_shipping)
{
    global $wpdb;

    $count = 0;

    if (!is_admin()) {

        $table_name = $wpdb->prefix . 'mpseller_meta';

        $items = WC()->cart->get_cart();

        foreach ($items as $item => $values) {

            if (!empty($values['variation_id'])) {
                $product_id = $values['variation_id'];
            } else {
                $product_id = $values['product_id'];
            }

            if (get_post_meta($product_id, '_virtual', true) == 'yes' || get_post_meta($product_id, '_downloadable', true) == 'yes') {
            } else {

                if (isset($values["assigned-seller-$product_id"])) {
                    $vendor = $values["assigned-seller-$product_id"];
                } else {
                    $vendor = get_post_field('post_author', $product_id);
                }

                $seller_zones = $wpdb->get_results("SELECT zone_id FROM $table_name where seller_id = '$vendor'");

                if (!empty($seller_zones)) {
                    ++$count;
                }
            }
        }

        if (0 === $count) {
            return false;
        }

        return $needs_shipping;
    }
}
