<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Vendor Sale Notification
 * 
 * Type : Plain
 * 
 * $site_name		  : displays the site name
 * $product_title	  : displays the product title
 * $voucher_code	  : displays the voucher code
 * $product_price	  : displays the product price
 * $order_id		  : displays the order id
 * $product_quantity  : displays the product quantity
 * $customer_name 	  : displays the customer name
 * $shipping_address  : displays the shipping address
 * $shipping_postcode : displays the shipping postcode
 * $shipping_city 	  : displays the shipping city
 * $recipient_name 	  : displays the recipient name
 * $recipient_email   : displays the recipient email
 * $order_date 		  : displays the order date
 * $vou_exp_date 	  : displays the voucher expiry date
 * $vendor_first_name : displays the vendor first name
 * $vendor_last_name  : displays the vendor last name
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */


echo esc_html__( "Hello,", 'woovoucher' ) . "\n\n";

echo sprintf( esc_html__( "A new sale on %s", 'woovoucher' ), $site_name ) . "\n\n";

echo sprintf( esc_html__( "Product Title: %s", 'woovoucher' ), $product_title ) . "\n\n";

echo sprintf( esc_html__( "Voucher Code: %s", 'woovoucher' ), $voucher_code ) . "\n\n";

echo esc_html__( "Thank you", 'woovoucher' );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );