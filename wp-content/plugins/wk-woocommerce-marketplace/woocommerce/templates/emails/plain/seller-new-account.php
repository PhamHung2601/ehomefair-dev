<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$loginurl = $data['user_login'];
$welcome = sprintf(utf8_decode(__('Welcome to ')).get_option('blogname').'!')."\r\n\n";
$msg = utf8_decode(__('Your account has been created awaiting for admin approval.', 'marketplace'))."\n\n\r\n\r\n\n\n";
$username = utf8_decode(__('User :- ', 'marketplace')).$data['user_email'];
$password = utf8_decode(__('User Password :- ', 'marketplace')).$data['user_pass'];
$admin = get_option('admin_email');
$reference = utf8_decode(__('If you have any problems, please contact me at', 'marketplace'))."\r\n\r\n";

echo '= '.utf8_decode(esc_html($email_heading))." =\n\n";
echo sprintf(utf8_decode(esc_html__('Hi %s,', 'marketplace')), esc_html($loginurl))."\n\n";

echo $welcome."\n";
echo $msg."\n";
echo $username."\n";
echo $password."\n";
echo $reference."\n";
echo '<a href="mailto:'.$admin.'">'.$admin.'</a>';

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
