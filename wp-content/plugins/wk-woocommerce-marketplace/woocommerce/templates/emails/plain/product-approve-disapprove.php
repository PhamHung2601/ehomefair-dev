<?php

if (!defined('ABSPATH')) {
    exit;
}
$_product = wc_get_product($product);
$product_name = utf8_decode($_product->get_name());
$user_name = utf8_decode(get_user_meta($user, 'first_name', true));
$msg = $review_here = '';
if( $status ) {

    $welcome = utf8_decode(__('Congrats! Your product ( ', 'marketplace')).' '.'<strong>'.$product_name.'</strong> '.' '.utf8_decode(__(' ) has been published', 'marketplace')).' ! ';
    $msg = utf8_decode(__( 'Click here to view it ', 'marketplace'));
    $review_here = get_the_permalink($product);
    $review_here = ' <a href='.$review_here.'>'.utf8_decode(__('Here', 'marketplace')).'</a>';

} else {
    $welcome = utf8_decode(__('Unfortunately! Your product ( ', 'marketplace')).' '.'<strong>'.$product_name.'</strong> '.' '.utf8_decode(__(' ) has been rejected by Admin', 'marketplace' ) ).' ! ';
}

echo '= ' . utf8_decode(esc_html( $email_heading )) . " =\n\n";

echo utf8_decode(__('Hi', 'marketplace')) . ', ' . $user_name . "\n\n";

echo $welcome;

echo $msg . "\n\n" . $review_here . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );