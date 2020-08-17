<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles front end ajax functions interface.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Ajax_Functions_Interface
{
    /**
     * Update user status in db
     */
    public function mpbs_update_user_status();

    /**
     * Check seller available for chat
     */
    public function mpbs_check_user_is_available();

    /**
     * initialize Chat User Meta
     */
    public function mpbs_initialize_chat_user_meta();

    /**
     * Fetch chat history
     */
    public function mpbs_fetch_chat_history();

    /**
     * save chat data in db
     */
    public function mpbs_save_chat_data();
}
