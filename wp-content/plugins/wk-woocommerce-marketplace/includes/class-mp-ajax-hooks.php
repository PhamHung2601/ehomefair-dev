<?php
/**
 * File for the ajax hooks
 *
 * @package wk-wooCommerce-marketplace/includes
 */

if (! defined('ABSPATH') ) {
    exit;
}

// Get All Countries.
add_action('wp_ajax_nopriv_get_all_countries', 'get_all_countries');
add_action('wp_ajax_get_all_countries', 'get_all_countries');

// Get All States.
add_action('wp_ajax_nopriv_country_get_state', 'country_get_state');
add_action('wp_ajax_country_get_state', 'country_get_state');

// remove favourite seller.
add_action('wp_ajax_nopriv_delete_favourite_seller', 'delete_favourite_seller');
add_action('wp_ajax_delete_favourite_seller', 'delete_favourite_seller');


// delete favorite customers.
add_action('wp_ajax_nopriv_change_favorite_status', 'change_favorite_status');
add_action('wp_ajax_change_favorite_status', 'change_favorite_status');


// Send mail to selected customers.
add_action('wp_ajax_nopriv_send_mail_to_customers', 'send_mail_to_customers');
add_action('wp_ajax_send_mail_to_customers', 'send_mail_to_customers');


// Add Shipping Cost.
add_action('wp_ajax_nopriv_save_shipping_cost', 'save_shipping_cost');
add_action('wp_ajax_save_shipping_cost', 'save_shipping_cost');


// Delete Shipping Classes.
add_action('wp_ajax_nopriv_delete_shipping_class', 'delete_shipping_class');
add_action('wp_ajax_delete_shipping_class', 'delete_shipping_class');


// Add Shipping Classes.
add_action('wp_ajax_nopriv_add_shipping_class', 'add_shipping_class');
add_action('wp_ajax_add_shipping_class', 'add_shipping_class');

// Add Shipping Zone Method.
add_action('wp_ajax_nopriv_add_shipping_method', 'add_shipping_method');
add_action('wp_ajax_add_shipping_method', 'add_shipping_method');

// change the seller dashboard settings.
add_action('wp_ajax_nopriv_change_seller_dashboard', 'change_seller_dashboard');
add_action('wp_ajax_change_seller_dashboard', 'change_seller_dashboard');

// Delete Shipping Zone Method.
add_action('wp_ajax_nopriv_delete_shipping_method', 'delete_shipping_method');
add_action('wp_ajax_delete_shipping_method', 'delete_shipping_method');

// Delete zone details.
add_action('wp_ajax_nopriv_del_zone', 'del_zone');
add_action('wp_ajax_del_zone', 'del_zone');


// selller approvement end.
add_action('wp_ajax_nopriv_wk_admin_seller_approve', 'wk_admin_seller_approve');
add_action('wp_ajax_wk_admin_seller_approve', 'wk_admin_seller_approve');

// sku.
add_action('wp_ajax_nopriv_product_sku_validation', 'product_sku_validation');
add_action('wp_ajax_product_sku_validation', 'product_sku_validation');


// image gallary.
add_action('wp_ajax_nopriv_productgallary_image_delete', 'productgallary_image_delete');
add_action('wp_ajax_productgallary_image_delete', 'productgallary_image_delete');


// user registration.
add_action('wp_ajax_nopriv_existing_user', 'existing_user');
add_action('wp_ajax_user_existing_user', 'existing_user');


// user email.
add_action('wp_ajax_nopriv_seller_email_availability', 'seller_email_availability');
add_action('wp_ajax_user_seller_email_availability', 'seller_email_availability');


/*login with facebook in*/
add_action('wp_ajax_nopriv_mp_login_with_facebook', 'mp_login_with_facebook');
add_action('wp_ajax_user_mp_login_with_facebook', 'mp_login_with_facebook');


// Ajax check for store name.
add_action('wp_ajax_nopriv_wk_check_myshop', 'wk_check_myshop_value');
add_action('wp_ajax_wk_check_myshop', 'wk_check_myshop_value');


/*variation*/
add_action('wp_ajax_nopriv_marketplace_attributes_variation', 'marketplace_attributes_variation');
add_action('wp_ajax_marketplace_attributes_variation', 'marketplace_attributes_variation');


/***************************
* Remove
************************************/
add_action('wp_ajax_nopriv_mpattributes_variation_remove', 'mpattributes_variation_remove');
add_action('wp_ajax_mpattributes_variation_remove', 'mpattributes_variation_remove');


/*************************************
* Addfile
***********************/
add_action('wp_ajax_nopriv_mp_downloadable_file_add', 'mp_downloadable_file_add');
add_action('wp_ajax_mp_downloadable_file_add', 'mp_downloadable_file_add');


/* payment method */
add_action('wp_ajax_marketplace_statndard_payment', 'marketplace_statndard_payment');
add_action('wp_ajax_marketplace_mp_make_payment', 'marketplace_mp_make_payment');


add_action('wp_ajax_nopriv_wk_search_group', 'wk_search_group');
add_action('wp_ajax_wk_search_group', 'wk_search_group');

/*******
 * Product bulk delete 
 *****************/
add_action('wp_ajax_nopriv_mp_bulk_delete_product', 'mp_bulk_delete_product');
add_action('wp_ajax_mp_bulk_delete_product', 'mp_bulk_delete_product');

/*******
 * Order Manual Transaction 
************/

add_action('wp_ajax_nopriv_mp_order_manual_payment', 'mp_order_manual_payment');
add_action('wp_ajax_mp_order_manual_payment', 'mp_order_manual_payment');

/**
* Mail to seller 
**/

add_action('wp_ajax_nopriv_send_mail_to_seller', 'send_mail_to_seller');
add_action('wp_ajax_send_mail_to_seller', 'send_mail_to_seller');

