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
    $adm_msg = utf8_decode($data['adm_msg']);
    $query = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where id = %d", $query_id));

    if ($query) {
        $q_data = $query[0];
        $msg = utf8_decode(__('We received your query about: ', 'marketplace'));
        $admin = utf8_decode(__('Message : ', 'marketplace'));
        $admin_message = utf8_decode($q_data->message);
        $reference = utf8_decode(__('Subject : ', 'marketplace'));
        $reference_message = utf8_decode($q_data->subject);
        $adm_ans = utf8_decode(__('Answer : ', 'marketplace'));
        $closing_msg = utf8_decode(__('Please, do contact us if you have additional queries. Thanks again!', 'marketplace'));

        echo '= '.utf8_decode(esc_html($email_heading))." =\n\n";
        /* translators: %s Customer first name */
        echo sprintf(utf8_decode(esc_html__('Hi %s,', 'marketplace')), utf8_decode(esc_html($customer_email)))."\n\n";

        echo $msg."\n";
        echo $reference_message."\n";
        echo $admin.' '.$admin_message."\n";
        echo $adm_ans.' '.$adm_msg."\n";
        echo $closing_msg."\n";

        echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

        echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
    }
}
