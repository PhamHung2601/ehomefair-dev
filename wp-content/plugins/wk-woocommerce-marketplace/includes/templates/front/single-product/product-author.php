<?php

if (!defined('ABSPATH')) {
    exit;
}

function woocommerce_product_by()
{

    global $wpdb, $wp_query;

    $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

    $seller_id = get_the_author_meta('ID');

    $shop_url = get_user_meta($seller_id, 'shop_address', true);

    if (get_the_author_meta('ID') != 1) {

        $review_data = get_review($seller_id);

        $num_of_stars = $total_feedback = 0;

        if ($review_data) {
            foreach ($review_data as $item) {
                $num_of_stars += $item->price_r;
                $num_of_stars += $item->value_r;
                $num_of_stars += $item->quality_r;
                $total_feedback++;
            }
        }

        if ($num_of_stars != 0) {
            $quality = $num_of_stars / ($total_feedback * 3);
            $rating = '<span class="mp-seller-rating">' . number_format($quality, 2) . '</span>';
        } else {
            $quality = 0;
            $rating = '';
        }

        echo '<p class="mp-product-author-shop">' . __('Seller', 'marketplace') . ' : <a href="' . site_url() . '/' . $page_name . '/' . get_option( 'mp_store', 'store' ) . '/' . $shop_url . '">' . ucfirst(get_user_meta(get_the_author_meta('ID'), 'shop_name', true)) . '</a> ' . $rating . '</p>';
    } else {
        echo '<p> ' . __('Seller :', 'marketplace') . ucfirst(get_the_author()) . '</p>';
    }
}
