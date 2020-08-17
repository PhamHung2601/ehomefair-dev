<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$loginurl = $data['user_login'];
$welcome = sprintf(utf8_decode(__('Welcome to ', 'marketplace')).get_option('blogname').'!')."\r\n\n";
$msg = utf8_decode(__('Your account has been created awaiting for admin approval.', 'marketplace'))."\n\n\r\n\r\n\n\n";
$username = utf8_decode(__('User :- ', 'marketplace')).$data['user_email'];
$password = utf8_decode(__('User Password :- ', 'marketplace')).$data['user_pass'];
$admin = get_option('admin_email');
$reference = utf8_decode(__('If you have any problems, please contact me at', 'marketplace'))."\r\n\r\n";
do_action('woocommerce_email_header', $email_heading, $email);
$result = '<p>'.utf8_decode(esc_html__('Hi','marketplace')).', '.$loginurl.'</p>
			<h2> <strong>'.$welcome.'</strong><h2>
			<p>'.$msg.'</p>
			<p>'.$username.'</p>
			<p>'.$password.'</p>
			<h3>'.$reference.' :-</h3>
			<h3><a href="mailto:'.$admin.'">'.$admin.'</a></h3>';

echo $result;
do_action('woocommerce_email_footer', $email);
