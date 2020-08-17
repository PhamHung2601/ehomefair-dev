<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles front templates interface.
 */

namespace WpMarketplaceBuyerSellerChat\Templates\Admin\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Admin_Chatbox_Interface
{
    /**
     * Admin chatbox
     */
    public function mpbs_admin_chatbox();
}
