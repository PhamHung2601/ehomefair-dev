<?php

if (!defined('ABSPATH')) {
    exit;
}

$_product = wc_get_product($product);
$product_name = utf8_decode($_product->get_name());
$user_name = utf8_decode(get_user_meta($user, 'first_name', true));
$welcome = utf8_decode(__('Vendor ', 'marketplace')).' '.$user_name.' '.utf8_decode(__(' has requested to publish ', 'marketplace')).' '.'<strong>'.$product_name.'</strong> '.' '.utf8_decode(__('product', 'marketplace')).' ! ';
$msg = utf8_decode(__('Please review the request', 'marketplace'));
$review_here = sprintf(admin_url('post.php?post=%s&action=edit'), $product);
$admin = get_option('admin_email');

do_action('woocommerce_email_header', $email_heading, $email);

$result = ' <p>'.utf8_decode(__('Hi', 'marketplace')).', '.$admin.'</p>
				<h3>'.$welcome.'<h3>
			<p>'.$msg.' <a href='.$review_here.'>'.utf8_decode(__('Here', 'marketplace')).'</a></p>';

echo $result;

do_action('woocommerce_email_footer', $email);
