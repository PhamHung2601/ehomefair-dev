<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contain all functions that require on plugin deactivation
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.9
 */

/**
 * Plugin Setup (On Deactivation)
 * 
 * Delete  plugin options.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_uninstall() {

   global $wpdb;

   // Get prefix
   $prefix = WOO_VOU_META_PREFIX;

   //IMP Call of Function
   //Need to call when custom post type is being used in plugin
   flush_rewrite_rules();

   // Flush Cron Jobs
   wp_clear_scheduled_hook( 'woo_vou_flush_upload_dir_cron' );

   // Flush Cron Jobs for gift notification email
   wp_clear_scheduled_hook( 'woo_vou_send_gift_notification' );

   // Getting delete option
   $woo_vou_delete_options = get_option( 'vou_delete_options' );

   // If option is set
   if( isset( $woo_vou_delete_options ) && !empty( $woo_vou_delete_options ) && $woo_vou_delete_options == 'yes' ) {

       // Delete vouchers data
       $post_types = array( 'woovouchers', 'woovouchercodes', 'woovoupartredeem', 'shop_coupon' );

       foreach ( $post_types as $post_type ) {

           $args = array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => '-1' );
           if ( $post_type == 'shop_coupon' ) {
               $args = array_merge( array( 'meta_key' => $prefix . 'coupon_type', 'meta_value' => 'voucher_code' ) , $args );
           }
           $all_posts = get_posts( $args );
           foreach ( $all_posts as $post ) {
               wp_delete_post( $post->ID, true);
           }
       }

       //Items need to delete
       $options	= array(
                       'vou_voucher_price_options',
                       'vou_site_logo',
                       'vou_pdf_name',
                       'vou_pdf_title',
                       'vou_pdf_author',
                       'vou_pdf_creator',
                       'vou_csv_name',
                       'order_pdf_name',
                       'vou_voucher_delivery_options',
                       'vou_change_expiry_date',
                       'vou_exp_type',
                       'vou_start_date',
                       'vou_exp_date',
                       'vou_days_diff',
                       'vou_custom_days',
                       'vou_pdf_usability',
                       'multiple_pdf',
                       'vou_pdf_template',
                       'woo_vou_set_option',
                       'vou_delete_options',
                       'vou_enable_partial_redeem',
                       'vou_partial_redeem_product_ids',
                       'vou_enable_coupon_code',
                       'vou_char_support',
                       'vou_enable_logged_user_check_voucher_code',
                       'vou_enable_guest_user_check_voucher_code',
                       'vou_attach_processing_mail',
                       'vou_attach_gift_mail',
                       'vou_download_processing_mail',
                       'vou_download_gift_mail',
                       'vou_download_dashboard',
                       'vou_allow_bcc_to_admin',
                       'vou_disable_variations_auto_downloadable',
                       'vou_enable_vendor_access_all_voucodes',
                       'vou_enable_wcmp_vendor_acess_pdf_vou_meta',
                       'vou_code_postfix',
                       'vou_download_text',
                       'vou_enable_pdf_password_protected',
                       'vou_pdf_password_pattern',
                   );

       // Delete all options
       foreach ( $options as $option ) {
           delete_option( $option );
       }
   } // End of if

  // Remove rules for Htaccess file
  woo_vou_remove_rules_from_htaccess();
}