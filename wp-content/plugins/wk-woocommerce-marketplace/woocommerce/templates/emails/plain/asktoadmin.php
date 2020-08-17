<?php


if (!defined('ABSPATH')) {
    exit;
}

$username = utf8_decode(__('Email: ', 'marketplace'));
$username_mail = utf8_decode($data['email']);

$user_obj = get_user_by( 'email', $username_mail );
$user_name = $user_obj->first_name ? $user_obj->first_name . ' '. $user_obj->last_name : __('Someone','marketplace');
$msg = utf8_decode($user_name . ' '. __('asked a query from following account:', 'marketplace'));
$admin = utf8_decode(__('Message: ', 'marketplace'));
$admin_message = utf8_decode($data['ask']);
$reference = utf8_decode(__('Subject : ', 'marketplace'));
$reference_message = utf8_decode($data['subject']);

echo '= ' . utf8_decode(esc_html( $email_heading )) . " =\n\n";

echo utf8_decode(__('Hi', 'marketplace')) . ', ' . $admin_email . "\n\n";

echo $msg . "\n\n";

echo "<strong>$username</strong>" . $username_mail . "\n\n";
echo "<strong>$reference</strong>" . $reference_message . "\n\n";
echo "<strong>$admin</strong>" . $admin_message . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
