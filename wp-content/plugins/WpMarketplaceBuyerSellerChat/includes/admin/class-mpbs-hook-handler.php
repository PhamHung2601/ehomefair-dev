<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles all admin end actions.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Admin;

use WpMarketplaceBuyerSellerChat\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Hook_Handler')) {
    /**
     *
     */
    class Mpbs_Hook_Handler
    {
        public function __construct()
        {
            $function_handler = new Admin\Mpbs_Function_Handler;

            $function_handler->mpbs_init();

            add_action('admin_menu', array($function_handler, 'mpbs_add_dashboard_menu'), 99);

            add_action('admin_init', array($function_handler, 'mpbs_register_settings'));

            add_action('wp_ajax_no_priv_mpbs_start_server', array($function_handler, 'mpbs_start_server'));

            add_action('wp_ajax_mpbs_start_server', array($function_handler, 'mpbs_start_server'));

            add_action('wp_ajax_no_priv_mpbs_stop_server', array($function_handler, 'mpbs_stop_server'));

            add_action('wp_ajax_mpbs_stop_server', array($function_handler, 'mpbs_stop_server'));

            add_action('mpbs_save_configuration', array($function_handler, 'mpbs_save_configuration'));

            add_action('wp_ajax_mpbs_get_buyer_list', array($function_handler, 'mpbs_get_buyer_list'));

            add_filter('woocommerce_screen_ids', array($function_handler, 'mpbs_add_screen_id'), 10, 1);

           
        }
    }
}
