<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Gift Notification
 * 
 * Type : HTML
 * 
 * $first_name			: displays the first name of customer
 * $last_name			: displays the last name of customer
 * $recipient_name		: displays the recipient name
 * $product_price	    : displays the product price
 * $voucher_link		: displays the voucher download link
 * $recipient_message	: displays the recipient message
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<p><?php esc_html_e('Hello,', 'woovoucher'); ?></p>

<p><?php esc_html_e("You've been sent a voucher!", 'woovoucher'); ?></p>

<p><?php echo $recipient_message; ?></p>

<p><?php echo sprintf(esc_html__('You can find your voucher: %s', 'woovoucher'), $voucher_link); ?></p>

<?php do_action('woocommerce_email_footer'); ?>