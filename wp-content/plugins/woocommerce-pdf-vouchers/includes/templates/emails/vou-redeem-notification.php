<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Gift Notification
 * 
 * Type : HTML
 * 
 * $voucode				: displays the voucher code
 * $first_name			: displays the first name of vendor
 * $last_name			: displays the last name of vendor
 * $redeem_date			: displays redeem date
 * $redeem_method		: displays the redeem method
 * $redeem_amount		: displays the redeem amount
 * $vou_redeem_method	: displays voucher redeem method
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php esc_html_e( 'Hello,', 'woovoucher' ); ?></p>

<p><?php echo sprintf( esc_html__('A voucher code %4$s%1$s%5$s has been redeemed by %2$s %3$s!', 'woovoucher'), $voucode, $first_name, $last_name, '<b>','</b>');?></p>

<p><?php echo sprintf( esc_html__('Voucher Redeem Method: %s', 'woovoucher'), $vou_redeem_method ); ?></p>

<p><?php echo sprintf( esc_html__('Redeem Date & Time: %s', 'woovoucher'), $redeem_date ); ?></p>

<p><?php echo sprintf( esc_html__('Redeem Type: %s', 'woovoucher'), !empty( $redeem_method ) ? $redeem_method : esc_html__('Full', 'woovoucher') ); ?></p>

<?php if(!empty($redeem_method) && strtolower( $redeem_method ) == 'partial') { ?>
	<p><?php echo sprintf( esc_html__('Redeem Amount: %s', 'woovoucher'), $redeem_amount ); ?></p>
<?php } ?>

<?php do_action( 'woocommerce_email_footer' ); ?>