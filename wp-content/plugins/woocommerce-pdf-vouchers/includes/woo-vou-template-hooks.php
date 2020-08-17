<?php

/**
 * Template Hooks
 * 
 * Handles to add all hooks of template
 * 
 * @package WooCommerce -  PDF Vouchers
 * @since 2.5.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Start Expired/Upcoming Product

// add action to remove add to cart button on single product page if product is expire/upcoming
add_action( 'woocommerce_single_product_summary', 'woo_vou_display_expiry_product' );

//Template Hooks For add to PDF Vouchers Recipient fields

// add action to display PDF Vouchers Recipient fields html
add_action( 'woo_vou_product_recipient_fields', 'woo_vou_recipient_fields_content' );

// add action to display qrcode and barcode form html 
add_action( 'woo_vou_check_qrcode_content', 'woo_vou_check_qrcode_content' );

// add action to display used voucher codes form html on frontend
add_action( 'woo_vou_used_voucher_codes', 'woo_vou_used_voucher_codes_content' );

// add action to display purchased voucher codes form html on frontend
add_action( 'woo_vou_purchased_voucher_codes', 'woo_vou_purchased_voucher_codes_content' );

// add action to display unused voucher codes form html on frontend
add_action( 'woo_vou_unused_voucher_codes', 'woo_vou_unused_voucher_codes_content' );

// add_action to show used voucher codes listing table
add_action( 'woo_vou_used_voucher_codes_table', 'woo_vou_used_voucher_codes_listing_content', 10, 2 );

// add_action to show used voucher codes listing table
add_action( 'woo_vou_purchased_voucher_codes_table', 'woo_vou_purchased_voucher_codes_listing_content', 10, 2 );

// add_action to show unused voucher codes listing table
add_action( 'woo_vou_unused_voucher_codes_table', 'woo_vou_unused_voucher_codes_listing_content', 10, 2 );

// add_action to show voucher details
add_action( 'woo_vou_get_voucher_details_custom', 'woo_vou_get_voucher_details_content' );

// Add action to show recipient name html
add_action( 'woo_vou_recipient_name_html', 'woo_vou_recipient_name_html' );

// Add action to show recipient email html
add_action( 'woo_vou_recipient_email_html', 'woo_vou_recipient_email_html' );

// Add action to show recipient message
add_action( 'woo_vou_recipient_message_html', 'woo_vou_recipient_message_html' );

// Add action to show recipient gift date
add_action( 'woo_vou_recipient_giftdate_html', 'woo_vou_recipient_giftdate_html' );

// Add action to show custom recipient field html
add_action( 'woo_vou_cstm_recipient_html', 'woo_vou_cstm_recipient_html', 10, 2 );

// Add action to generate pdf preview popup
add_action( 'woo_vou_preview_pdf_popup', 'woo_vou_preview_pdf_popup_html' );