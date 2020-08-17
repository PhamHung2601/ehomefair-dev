<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles front templates interface.
 */

namespace WpMarketplaceBuyerSellerChat\Templates\Front\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Seller_Chatbox_Interface
{
    /**
     * Seller chatbox
     */
    public function mpbs_seller_chatbox();
}
