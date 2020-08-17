<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles core config interface.
 */

namespace WpMarketplaceBuyerSellerChat\Helper\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Core_Config_Interface
{
    /**
     * Get current user id
     */
    public function mpbs_get_core_config();
}
