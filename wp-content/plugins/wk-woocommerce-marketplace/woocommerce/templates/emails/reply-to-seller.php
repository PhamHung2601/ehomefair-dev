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

global $wpdb;

if ($data) {
    $query_id = $data['q_id'];
    $adm_msg = $data['adm_msg'];
    $query = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where id = %d", $query_id));

    if ($query) {
        $q_data = $query[0];
        $msg = utf8_decode(esc_html__('We received your query about: ', 'marketplace'))."\r\n\r\n";
        $admin = utf8_decode(esc_html__('Message : ', 'marketplace'));
        $admin_message = $q_data->message;
        $reference = utf8_decode(esc_html__('Subject : ', 'marketplace'));
        $reference_message = $q_data->subject;
        $adm_ans = utf8_decode(esc_html__('Answer : ', 'marketplace'));
        $closing_msg = utf8_decode(esc_html__('Please, do contact us if you have additional queries. Thanks again!', 'marketplace'));

        do_action('woocommerce_email_header', $email_heading, $email);

        $result = '
				<p>'.utf8_decode(esc_html__('Hi', 'marketplace')).',</p>
				<p>'.$msg.'</p>
				<p><strong>'.$reference.'</strong>'.$reference_message.'</p>
				<p><strong>'.$admin.'</strong>'.$admin_message.'</p>
				<p><strong>'.$adm_ans.'</strong>'.$adm_msg.'</p>
				<p>'.$closing_msg.'</p>';
    }
}

echo $result;
do_action('woocommerce_email_footer', $email);
