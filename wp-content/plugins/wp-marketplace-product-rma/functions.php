<?php
/**
 * Plugin Name: Marketplace Product Return RMA
 * Plugin URI: http://store.webkul.com
 * Description: Marketplace Product Return RMA module allows you to organize a system for customers to request a return without any efforts. RMA is very useful for product return and order return. With the help of this module, a customer can return the products, have them exchanged or refunded within the admin specified time limit.
 * Version: 1.3.0
 * Author: Webkul
 * Author URI: https://webkul.com
 * License: GNU/GPL for more info see license.txt included with plugin
 * Text Domain: marketplace-rma
 * License URI: https://store.webkul.com/license.html
 *
 **/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

!defined('MP_RMA_PATH') && define('MP_RMA_PATH', plugin_dir_path(__FILE__));
!defined('MP_RMA_URL') && define('MP_RMA_URL', plugin_dir_url(__FILE__));

if (!function_exists('mp_rma_install')) {
    function mp_rma_install()
    {
        load_plugin_textdomain('marketplace-rma', false, basename(dirname(__FILE__)));
        if (!class_exists('Marketplace')) {
            add_action('admin_notices', 'mp_rma_install_marketplace_admin_notice');
        } else {
            new MP_Woo_RMA();

            do_action('mp_rma_init');

            $wk_obj = new MP_RMA_Install();

            $wk_obj->mp_rma_activation();
        }
    }

    add_action('plugins_loaded', 'mp_rma_install', 11);
}

function mp_rma_install_marketplace_admin_notice()
{
    ?>
    <div class="error">
        <p><?php _ex('WooCommerce Marketplace Product Return RMA is enabled but not effective. It requires <a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408" target="_blank">Marketplace Plugin</a> in order to work.', 'Alert Message: Marketplace requires', 'marketplace-rma');?></p>
    </div>
    <?php
}

if (!class_exists('MP_Woo_RMA')) {
    /**
     *     Seller rma: Main Class.
     */
    class MP_Woo_RMA
    {
        protected $page_title_display_rma = 1;

        public function __construct()
        {
            ob_start();

            add_action('mp_rma_init', array($this, 'mp_rma_init'));

            add_action('init', array($this, 'mp_rma_add_endpoints'));

            add_filter('the_title', array($this, 'mp_rma_hide_page_title'));

            add_filter('woocommerce_email_classes', array($this, 'wk_mp_rma_add_new_email_notification'), 10, 1);

            add_filter('woocommerce_email_actions', array($this, 'wk_mp_rma_add_rma_notification_actions'));
        }

        public function wk_mp_rma_add_rma_notification_actions($actions)
        {
            $actions[] = 'woocommerce_mp_rma_mail';

            return $actions;
        }

        public function wk_mp_rma_add_new_email_notification($email_classes)
        {
            $email_classes['WC_Email_RMA_Notification'] = include 'class-wc-email-rma-notification.php';

            return $email_classes;
        }

        /**
         * Register new endpoint to use inside My Account page.
         */
        public function mp_rma_add_endpoints()
        {
            add_rewrite_endpoint('rma', EP_ROOT | EP_PAGES);
        }

        public function mp_rma_init()
        {
            require_once sprintf('%s/includes/class-mp-rma-ajax-functions.php', dirname(__FILE__));
            require_once sprintf('%s/includes/class-mp-rma.php', dirname(__FILE__));

            new MP_Wk_RMA();

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'mp_rma_plugin_settings_link'));

            add_action('admin_enqueue_scripts', array($this, 'mp_rma_admin_scripts'));

            add_action('wp_enqueue_scripts', array($this, 'mp_rma_front_scripts'));
        }

        public function mp_rma_plugin_settings_link($links)
        {
            $url = 'https://wordpressdemo.webkul.com';

            $settings_link = '<a href="' . $url . '" target="_blank" style="color:green;">' . __('More Add-ons', 'marketplace-rma') . '</a>';

            $links[] = $settings_link;

            return $links;
        }

        public function mp_rma_admin_scripts()
        {
            wp_enqueue_media();

            wp_enqueue_script('mp-rma-admin-js', MP_RMA_URL . 'assets/js/plugin-admin.js', array('jquery'));

            wp_localize_script(
                'mp-rma-admin-js',
                'adminobj',
                array(
                    'rma_arr' => array(
                        'rma1' => esc_html__('Warning: Delete reason also affect RMA Order and you can lost some data related to these reasons!!', 'marketplace-rma'),
                        'rma2' => esc_html__('Are you sure, you want to do this..?', 'marketplace-rma'),
                        'rma3' => esc_html__('Upload Shipping Label', 'marketplace-rma'),
                        'rma4' => esc_html__('Select', 'marketplace-rma'),
                    ),
                )
            );

            wp_enqueue_style('mp_rma_admin_css', MP_RMA_URL . 'assets/css/style.css');
        }

        public function mp_rma_front_scripts()
        {
            wp_enqueue_script('mp_rma_front_js', MP_RMA_URL . 'assets/js/plugin.js', array('jquery'));

            wp_enqueue_style('mp_rma_front_css', MP_RMA_URL . 'assets/css/style.css');

            wp_localize_script(
                'mp_rma_front_js',
                'mp_rma_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('rma_ajax_nonce'),
                    'rma_frr' => array(
                        'rmaf1' => esc_html__('Upload Shipping Label', 'marketplace-rma'),
                        'rmaf2' => esc_html__('Select', 'marketplace-rma'),
                        'rmaf3' => esc_html__('This is a required field.', 'marketplace-rma'),
                        'rmaf4' => esc_html__('Please enter quantity for selected order(s).', 'marketplace-rma'),
                        'rmaf5' => esc_html__('Please select reason for selected order(s).', 'marketplace-rma'),
                        'rmaf6' => esc_html__('Please select order(s).', 'marketplace-rma'),
                        'rmaf7' => esc_html__('Are you sure you want to cancel the rma.', 'marketplace-rma'),
                        'rmaf8' => esc_html__('Please upload image with jpeg or png extention.', 'marketplace-rma'),
                        'rmaf9' => esc_html__("You can create rma only for one seller's product at a time.", 'marketplace-rma'),
                    ),
                )
            );
        }

        // hide page entry tile for seller page
        public function mp_rma_hide_page_title($title)
        {
            global $wpdb, $wp_query;
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            if (in_the_loop() && is_page($page_name) && $this->page_title_display_rma == 1) {
                $this->page_title_display_rma = 0;

                if (null !== get_query_var('main_page')) {
                    $main_page = get_query_var('main_page');
                    switch ($main_page) {
                        case 'manage-rma':
                            return __('RMA System', 'marketplace-rma');
                            break;

                        case 'rma-reason':
                            return __('RMA Reason', 'marketplace-rma');
                            break;

                        case 'add-reason':
                            return __('New Reason', 'marketplace-rma');
                            break;

                        case 'rma':
                            return __('RMA Details', 'marketplace-rma');
                            break;

                        default:
                            return '';
                            break;
                    }
                }
            }

            return $title;
        }
    }
}
