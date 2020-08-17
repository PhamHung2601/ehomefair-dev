<?php

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Variation product attributes.
 *
 * @param int $var_id variation id.
 * @param int $wk_pro_id product id id.
 */
function attribute_variation_data( $var_id, $wk_pro_id ) {
	require 'single-product/variations.php';
}

/**
 * Add Product.
 */
function add_product() {
	require_once 'single-product/add-product.php';
}

/**
 * Edit product.
 */
function edit_product() {
	require_once 'single-product/edit-product.php';
}
