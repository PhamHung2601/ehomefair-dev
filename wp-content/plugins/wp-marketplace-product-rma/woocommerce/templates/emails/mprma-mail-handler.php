<?php
/**
 * RMA Notification email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wallet-notification.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s Customer email */ ?>

<p><?php esc_html_e( 'Hello,', 'marketplace-rma' ); ?></p>
<?php 

if( !empty( $email_message ) ) {

	foreach( $email_message as $key => $message ) {
		?>
			<p><?php echo esc_html( $message ); ?></p>
		<?php
	}

}

?>

<p><?php esc_html_e( 'Thanks.', 'marketplace-rma' ); ?></p>

<?php
do_action( 'woocommerce_email_footer', $email );
