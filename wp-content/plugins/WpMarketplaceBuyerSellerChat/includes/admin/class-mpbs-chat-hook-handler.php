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

if (! class_exists('Mpbs_Chat_Hook_Handler')) {
    /**
     *
     */
    class Mpbs_Chat_Hook_Handler
    {
        public function __construct()
        { 

            $admin_chat_function_handler = new Admin\Mpbs_Chat_Function_Handler;
            
            // admin end chat template 

            add_action('init', array($admin_chat_function_handler, 'mpbs_initialize'));

            add_action('wp_login_failed', array($admin_chat_function_handler, 'mpbs_chat_box_login_failed'));

            add_action('wp_login', array($admin_chat_function_handler, 'mpbs_user_online_change_status'), 10, 2);

            add_action('mpbs_save_profile_data_hook', array($admin_chat_function_handler, 'mpbs_save_profile_data'));

            

        }
    }
}
