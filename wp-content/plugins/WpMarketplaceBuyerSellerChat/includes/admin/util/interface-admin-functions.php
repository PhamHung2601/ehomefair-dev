<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles front end functions interface.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Admin\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Admin_Functions_Interface
{
    /**
     * Initialize function
     */
    public function mpbs_initialize();

    /**
     * Chatbox template function
     */
    public function mpbs_chatbox_template();

    /**
     * Redirect to same page if invalid credentials with notice
     */
    function mpbs_chat_box_login_failed();

    /**
     * Insert user entry in user table if not exists
     * @param $user_id
     */
    public function mpbs_initialize_user_table($user_id);

    /**
     * Update user status on login
     * @param $user_login, $user
     */
    public function mpbs_user_online_change_status($user_login, $user);

    /**
     * Save profile data
     */
    public function mpbs_save_profile_data();
}
