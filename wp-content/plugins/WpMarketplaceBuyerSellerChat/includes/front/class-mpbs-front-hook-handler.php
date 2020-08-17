<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles all front end actions.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front;

use WpMarketplaceBuyerSellerChat\Includes\Front;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Front_Hook_Handler')) {
    /**
     *
     */
    class Mpbs_Front_Hook_Handler
    {
        public function __construct()
        {
            $function_handler = new Front\Mpbs_Front_Function_Handler;

            add_action('init', array($function_handler, 'mpbs_initialize'));

            add_action('wp_login_failed', array($function_handler, 'mpbs_chat_box_login_failed'));

            add_action('wp_login', array($function_handler, 'mpbs_user_online_change_status'), 10, 2);

            add_action('mpbs_save_profile_data_hook', array($function_handler, 'mpbs_save_profile_data'));
        }
    }
}
