<?php

if (!defined('ABSPATH')) {
    exit;
}

$loginurl = $data;

$msg = utf8_decode(__('Your account has been Disapproved by admin ', 'marketplace'));
$admin = get_option('admin_email');
$reference = utf8_decode(__('If you have any query, please contact us at -', 'marketplace'));
$thnksmsg = utf8_decode(__('Thanks for choosing Marketplace.', 'marketplace'));

do_action( 'woocommerce_email_header', $email_heading, $email );

$result = '<p>'.utf8_decode(__('Hi', 'marketplace')).', '.utf8_decode($user_email).'</p>
		<p>'.$msg.'.</p>
		<p>'.$reference.' <a href="mailto:'.$admin.'">'.$admin.'</a></p>
		<p>'.$thnksmsg.'</p>';

echo $result;

do_action('woocommerce_email_footer', $email);