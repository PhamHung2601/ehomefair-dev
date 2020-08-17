<?php
/**
 * File for adding seller metabox.
 *
 * @package wk-woocommerce-marketplace/includes/templates/admin/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Selle metabox.
 */
function seller_metabox() {
	require_once 'single-product/metabox.php';
}

/**
 * Add seller metabox.
 */
function add_seller_metabox() {
	global $current_user;
	if ( ! in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
		add_meta_box( 'seller-meta-box', __('Seller', 'marketplace'), 'seller_metabox', 'product', 'side', 'low', null );
	}
}
