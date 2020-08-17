<?php
/**
 * RMA Notification email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/wallet-notification.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails/Plain
 * @version 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo '= ' . esc_html( $email_heading ) . " =\n\n";

/* translators: %s Customer email */
echo esc_html__( 'Hello,', 'marketplace-rma' ) . "\n\n";

if( !empty( $email_message ) ) {

    foreach( $email_message as $key => $message ) {
        echo esc_html( $message ) . "\n";
    }

}

echo esc_html__( 'Thanks.', 'marketplace-rma' ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
