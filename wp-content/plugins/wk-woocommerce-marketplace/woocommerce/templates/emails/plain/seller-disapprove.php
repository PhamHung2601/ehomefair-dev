<?php

if (!defined('ABSPATH')) {
    exit;
}

$loginurl = $data;

$msg = utf8_decode(__('Your account has been Disapproved by admin ', 'marketplace'));
$admin = get_option('admin_email');
$reference = utf8_decode(__('If you have any query, please contact us at -', 'marketplace'));
$thnksmsg = utf8_decode(__('Thanks for choosing Marketplace.', 'marketplace'));

echo '= ' . utf8_decode(esc_html( $email_heading )) . " =\n\n";

echo utf8_decode(__('Hi', 'marketplace')).', '. utf8_decode($user_email) . " \n\n";

echo $msg . "\n\n";

echo $reference . "\n\n";

echo '<a href="mailto:' . $admin . '">' . $admin . '</a>' . "\n\n";

echo $thnksmsg . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );