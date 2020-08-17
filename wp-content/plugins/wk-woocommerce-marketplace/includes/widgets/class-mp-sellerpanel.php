<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class for marketpalce seller widget.
 */
class MP_Widget_Seller extends WP_Widget
{
    /**
     * Constructor function.
     */
    public function __construct()
    {
        parent::__construct(
            'mp_marketplace-widget',
            esc_html__('Display seller panel.', 'marketplace'),
            array(
                'classname' => 'mp_marketplace',
                'description' => esc_html__('Marketplace Seller Panel.', 'marketplace'),
            )
        );
    }

    /**
     * Widget data.
     *
     * @param array $args args.
     * @param array $instance instance.
     */
    public function widget($args, $instance)
    {
        global $wpdb;

        $user_id = get_current_user_id();

        $shop_address = get_user_meta($user_id, 'shop_address', true);

        $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

        $seller_info = $wpdb->get_var("SELECT user_id FROM " . $wpdb->prefix . "mpsellerinfo WHERE user_id = '" . $user_id . "' and seller_value='seller'");

        if ($seller_info > 0) {
            do_action('chat_with_me');
            echo '<div class="wkmp_seller"><h2>' . get_option('wkmp_seller_menu_tile') . '</h2>';
            echo '<ul class="wkmp_sellermenu">';

            echo '<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_profile', 'profile' ) ) . '">';
            echo __('My Profile', 'marketplace');
            echo '</a></li>
							 <li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_add_product', 'add-product' ) ) . '">';
            echo __('Add Product', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_product_list', 'product-list') ) . '">';
            echo __('Product List', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_order_history', 'order-history' ) ) . '">';
            echo __('Order History', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . $shop_address . '/' . get_option( 'mp_shipping', 'shipping' ) ) . '">';
            echo __('Manage Shipping', 'marketplace');
            echo '</a></li>';

            do_action('marketplace_list_seller_option', $page_name);

            echo '<li class="wkmp-selleritem"><a href="' . home_url("/" . $page_name . "/" . get_option( "mp_shop_follower", "shop-follower" ) ). '">';
            echo __('Shop Follower', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_dashboard', 'dashboard' ) ) . '">';
            echo __('Dashboard', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_change_password', 'change-password' ) ) . '">';
            echo __('Change Password', 'marketplace');
            echo '</a></li>
							<li class="wkmp-selleritem"><a href="' . home_url('/' . $page_name . '/' . get_option( 'mp_to', 'to' ) ) . '">';
            echo __('Ask To Admin', 'marketplace');
            echo '</a></li></ul></div>';
        }

        if ($user_id > 0 && $seller_info == 0) {
            echo '<div class="wkmp_seller"><h2>' . __('Buyer Menu', 'marketplace') . '</h2>';
            echo '<ul class="wkmp_sellermenu">';
            echo '<li class="wkmp-selleritem"><a href="' . home_url('/' . get_option('wkmp_seller_page_title') . '/' . get_option( 'mp_profile', 'profile' ) . '') . '">';
            echo __('My Profile', 'marketplace');
            echo '</a></li></ul></div>';
        }
    }
}

register_widget('MP_Widget_Seller');
