<?php

/**
 * Query Answered email.
 *
 * @author Webkul
 *
 * @version 4.7.1
 */
if (!defined('ABSPATH')) {
    exit;
}

if ($data) {

    do_action('woocommerce_email_header', $email_heading, $email);

    $result = '<p>'.esc_html__('Hi', 'marketplace').',</p>
            <p>'.$data.'</p>';
    
    echo $result;
    do_action('woocommerce_email_footer', $email);
    
}
