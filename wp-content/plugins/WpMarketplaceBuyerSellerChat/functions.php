<?php

/**
 * Plugin Name: WooCommerce Marketplace Buyer Seller Chat
 * Plugin URI: https://store.webkul.com/WordPress-WooCommerce-Marketplace-Buyer-Seller-Chat.html
 * Description: WordPress WooCommerce Marketplace Buyer Seller Chat plugin.
 * Version: 2.3.0
 * Author: WebKul
 * Author URI: https://webkul.com
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: http://www.gnu.org/licenseses/gpl-2.0.html
 * Text Domain: mp_buyer_seller_chat
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 3.3.x
 */

if (!defined('ABSPATH')) {
    exit;
}

!defined('MPBS_URL') && define('MPBS_URL', plugin_dir_url(__FILE__));

!defined('MPBS_FILE') && define('MPBS_FILE', plugin_dir_path(__FILE__));

!defined('MPBS_SCRIPT_VERSION') && define('MPBS_SCRIPT_VERSION', '1.0.0');


if (!function_exists('mpbs_buyer_seller_install')) {
    function mpbs_buyer_seller_install()
    {
        if (!class_exists('Marketplace')) {
            add_action('admin_notices', 'mpbs_install_marketplace_admin_notice');
        } else {
            new WkBuyerSellerChat();
            load_plugin_textdomain('mp_buyer_seller_chat', false, basename(dirname(__FILE__)) . '/languages');
            do_action('mpbs_init');
        }
    }

    add_action('plugins_loaded', 'mpbs_buyer_seller_install', 11);
}

/**
 * Admin notice function for Marketplace not found
 */
function mpbs_install_marketplace_admin_notice() {
    ?>
	<div class="error">
		<p><?php echo sprintf( esc_html__( 'Woocommerce Marketplace Buyer Seller Chat depends on the latest version of %s plugin in order to work!', 'mp_buyer_seller_chat' ), '<a href="https://store.webkul.com/Wordpress-Woocommerce-Marketplace.html" target="_blank">' . esc_html__( 'Marketplace', 'mp_buyer_seller_chat' ) . '</a>' ); ?></p>
	</div>
    <?php
}

if (!function_exists('mpbs_install_schema')) {
    /**
     * Schema install callback
     */
    function mpbs_install_schema()
    {
        require_once MPBS_FILE . 'install.php';
        $obj = new Mpbs_Install_Schema();
        $obj->mpbs_create_tables();
    }

    register_activation_hook(__FILE__, 'mpbs_install_schema');
}

if (!class_exists('WkBuyerSellerChat')) {
    class WkBuyerSellerChat
    {
        public function __construct()
        {
            add_action('mpbs_init', array($this, 'mpbs_includes'));
        }

        public function mpbs_includes()
        {
            require_once MPBS_FILE . 'includes/mpbs-file-handler.php';
        }
    }
}

function mpbs_change_status_to_offline($user_id = null, $status = 0)
{
    global $wpdb;

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $row_exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}user_table WHERE user_id = '%d'", $user_id));

    if ($row_exists) {
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}user_table SET status = 0 WHERE user_id ='%d'", $user_id));
    }
}

add_action('wp_logout', 'mpbs_change_status_to_offline');
