<?php

if (! defined('ABSPATH') ) {
    exit;
}

$mp_form_handler = new MP_Form_Handler();

$mp_login_handler = new MP_Login_Handler();

$mp_register_handler = new MP_Register_Handler();

add_action('wp', array( $mp_form_handler, 'calling_pages' ));

add_action('woocommerce_created_customer', array( $mp_register_handler, 'process_registration' ), 10, 2);

add_action('init', array( $mp_login_handler, 'process_mp_login' )); // process Popup login form.

add_filter('woocommerce_new_customer_data', array( $mp_register_handler, 'marketplace_new_customer_data' ));

add_action('woocommerce_register_form', 'mp_seller_reg_form_fields');

// Redirect to specific page after login.
add_filter('woocommerce_login_redirect', array( $mp_login_handler, 'mp_login_redirect' ), 10, 2);

add_filter('woocommerce_process_registration_errors', array( $mp_register_handler, 'mp_seller_registration_errors' ));

add_filter('registration_errors', array( $mp_register_handler, 'mp_seller_registration_errors' ));

// Product by Feature on product page.
add_action('woocommerce_single_product_summary', 'woocommerce_product_by', 11);

add_action('woocommerce_single_product_summary', 'add_favourite_seller_btn', 32);


/*----------*/ /*---------->>> MP USER DATA <<<----------*/ /*----------*/

add_action('set_user_role', 'mp_set_user_role', 10, 3);

/**
 * Map new order seller involved
 */

add_action('woocommerce_checkout_order_processed', 'mp_new_order_map_seller', 10, 1);

/**
 *  Seller collection pagination
 */
add_action('marketplace_after_shop_loop', 'mp_seller_collection_pagination');
add_action('marketplace_before_shop_loop', 'mp_seller_collection_pagination');

/**
 *  Seller ID based on shop url
 */
add_filter('mp_get_seller_id', 'mp_return_seller_id');

/**
 *  Add seller panel items to my account menu
 */
add_filter('woocommerce_account_menu_items', 'mp_seller_menu_items_my_account');

/**
 *  My account menu for seller pages
 */
add_filter('mp_get_wc_account_menu', 'mp_return_wc_account_menu');

add_action('mp_get_wc_account_menu', 'mp_return_wc_account_menu');

/**
 * Account menu shpping style.
 */
add_action('wp_head', 'mp_shipping_icon_style');

/**
 *  Add active class to current menu for seller pages
 */
add_filter('woocommerce_account_menu_item_classes', 'mp_add_menu_active_class', 10, 2);
