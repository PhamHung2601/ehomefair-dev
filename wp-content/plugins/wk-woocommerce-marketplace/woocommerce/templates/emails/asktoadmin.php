<?php


if (!defined('ABSPATH')) {
    exit;
}

$username = utf8_decode(__('Email : ', 'marketplace'));
$username_mail = $data['email'];
$user_obj = get_user_by( 'email', $username_mail );
$user_name = $user_obj->first_name ? $user_obj->first_name . ' '. $user_obj->last_name : __('Someone','marketplace');
$msg = utf8_decode($user_name . ' '. __('asked a query from following account:', 'marketplace'));
$admin = utf8_decode(__('Message : ', 'marketplace'));
$admin_message = utf8_decode($data['ask']);
$reference = utf8_decode(__('Subject : ', 'marketplace'));
$reference_message = utf8_decode($data['subject']);

do_action( 'woocommerce_email_header', $email_heading, $email );

$result = '
			<p>'.utf8_decode(__('Hi', 'marketplace')).', '.$admin_email.'</p>
			<p>'.$msg.'</p>
			<p><strong>'.$username.'</strong>'.$username_mail.'</p>
			<p><strong>'.$reference.'</strong>'.$reference_message.'</p>
			<p><strong>'.$admin.'</strong>'.$admin_message. '</p>';

echo $result;

do_action('woocommerce_email_footer', $email);
