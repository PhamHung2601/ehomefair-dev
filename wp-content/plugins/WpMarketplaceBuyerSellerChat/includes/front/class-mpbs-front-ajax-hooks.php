<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles all front end ajax actions.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front;

use WpMarketplaceBuyerSellerChat\Includes\Front;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Front_Ajax_Hooks')) {
    /**
     *
     */
    class Mpbs_Front_Ajax_Hooks
    {
        public function __construct()
        {
            $ajax_functions = new Front\Mpbs_Front_Ajax_Functions;

            add_action('wp_ajax_nopriv_mpbs_update_user_status', array($ajax_functions, 'mpbs_update_user_status'));

            add_action('wp_ajax_mpbs_update_user_status', array($ajax_functions, 'mpbs_update_user_status'));

            add_action('wp_ajax_nopriv_mpbs_check_seller_is_available', array($ajax_functions, 'mpbs_check_user_is_available'));

            add_action('wp_ajax_mpbs_check_seller_is_available', array($ajax_functions, 'mpbs_check_user_is_available'));

            add_action('wp_ajax_nopriv_mpbs_initialize_chat_user_meta', array($ajax_functions, 'mpbs_initialize_chat_user_meta'));

            add_action('wp_ajax_mpbs_initialize_chat_user_meta', array($ajax_functions, 'mpbs_initialize_chat_user_meta'));

            add_action('wp_ajax_nopriv_mpbs_save_profile_data', array($ajax_functions, 'mpbs_save_profile_data'));

            add_action('wp_ajax_mpbs_save_profile_data', array($ajax_functions, 'mpbs_save_profile_data'));

            add_action('wp_ajax_nopriv_mpbs_fetch_chat_history', array($ajax_functions, 'mpbs_fetch_chat_history'));

            add_action('wp_ajax_mpbs_fetch_chat_history', array($ajax_functions, 'mpbs_fetch_chat_history'));

            add_action('wp_ajax_nopriv_mpbs_save_chat_data', array($ajax_functions, 'mpbs_save_chat_data'));

            add_action('wp_ajax_mpbs_save_chat_data', array($ajax_functions, 'mpbs_save_chat_data'));

            add_action('wp_ajax_nopriv_mpbs_get_customer_config_in_js', array( $ajax_functions, 'mpbs_get_customer_config_in_js'));

            add_action('wp_ajax_mpbs_get_customer_config_in_js', array( $ajax_functions, 'mpbs_get_customer_config_in_js'));
        }
    }
}
