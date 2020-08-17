<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles assets interface.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Assets_Interface
{
    public function wkcInit();
    public function wkcEnqueueScripts();
}
