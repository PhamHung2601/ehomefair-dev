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
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */

esc_html_e( 'Voucher Redeemed', 'woovoucher' );

echo sprintf( esc_html__( 'A voucher code %s has been redeemed by %s %s!', 'woovoucher' ), $voucode, $first_name, $last_name );

echo sprintf( esc_html__('Voucher Redeem Method: %s', 'woovoucher'), $vou_redeem_method );

echo sprintf( esc_html__('Redeem Date & Time: %s', 'woovoucher'), $redeem_date );

echo sprintf( esc_html__('Redeem Type: %s', 'woovoucher'), !empty( $redeem_method ) ? $redeem_method : __('Full', 'woovoucher') );

if(!empty($redeem_method) && strtolower( $redeem_method ) == 'partial') {
	echo sprintf( esc_html__('Redeem Amount: %s', 'woovoucher'), $redeem_amount );
}