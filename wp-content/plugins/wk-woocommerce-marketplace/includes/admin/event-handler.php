<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*---------->>> Backend Menu <<<----------*/

add_action( 'admin_menu', 'menu_backend' );

add_action( 'admin_init', 'marketplace_mp_login_reg_function' );

add_action( 'admin_notices', 'mp_admin_notices' );





/*------Add Extra Field In User Profile Page--------*/

add_action( 'show_user_profile', 'extra_user_profile_fields', 10 );

add_action( 'edit_user_profile', 'extra_user_profile_fields' );




/*---------->>> Invoice Menu <<<----------*/

add_action( 'admin_menu', 'mp_virtual_menu_invoice_page' );

add_action( 'woocommerce_admin_order_actions_end', 'order_invoice_button' );




/*---------->>> Product Page Metabox <<<----------*/

add_action( 'add_meta_boxes', 'add_seller_metabox' );

/*---------->>> Admin Bar Notification Menu <<<----------*/

add_action( 'admin_bar_menu', 'mp_add_toolbar_items', 100 );

/**
 * Order status update hook
 */

add_action( 'woocommerce_order_status_changed', 'mp_order_status_changed_action', 10, 3 );
