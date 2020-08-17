<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles all file includes.
 */

use WpMarketplaceBuyerSellerChat\Includes\Front;
use WpMarketplaceBuyerSellerChat\Includes\Admin;

if (!defined('ABSPATH')) {
    exit;
}

require_once(MPBS_FILE . 'inc/autoload.php');

$script_loader = new Front\Mpbs_Script_Loader();

$script_loader->wkcInit();

if (! is_admin()) {
    new Front\Mpbs_Front_Hook_Handler();
} else {
    new Admin\Mpbs_Hook_Handler();
    new Admin\Mpbs_Chat_Hook_Handler();
}

new Front\Mpbs_Front_Ajax_Hooks();
