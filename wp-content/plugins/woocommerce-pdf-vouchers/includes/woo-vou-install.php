<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Contain all functions that require on plugin activation
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.9
 */

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_install() {
	
	global $wpdb, $user_ID;

	//register post type
	woo_vou_register_post_types();
	
	//IMP Call of Function
	//Need to call when custom post type is being used in plugin
	flush_rewrite_rules();
	
	// Flush Cron Jobs
	wp_clear_scheduled_hook( 'woo_vou_flush_upload_dir_cron' );
	
	// Schedule Cron
	if ( !wp_next_scheduled('woo_vou_flush_upload_dir_cron') ) {
		wp_schedule_event( time(), 'twicedaily', 'woo_vou_flush_upload_dir_cron' );
	}
	
	// Flush Cron Jobs for gift notification email
	wp_clear_scheduled_hook( 'woo_vou_send_gift_notification' );
		
	// Schedule Cron for send gift notification email
	if ( !wp_next_scheduled('woo_vou_send_gift_notification') ) {
		wp_schedule_event( time(), 'daily', 'woo_vou_send_gift_notification' );
	}
	
	//Pdf cache Dir and create directory on activation
	woo_vou_create_cache_folder();
	
	//get option for when plugin is activating first time
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( empty( $woo_vou_set_option ) ) { //check plugin version option
		
		//update default options
		woo_vou_default_settings();
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.0' );
		update_option( 'woo_vou_plugin_version', WOO_VOU_PLUGIN_VERSION );
	}
	
	//get option for when plugin is activating first time
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.0' ) { //check set option for plugin is set 1.0
		
		//get vendor role
		$vendor_role = get_role( WOO_VOU_VENDOR_ROLE );
		if( empty( $vendor_role ) ) { //check vendor role
			
			$capabilities  = array(
										WOO_VOU_VENDOR_LEVEL	=> true,  // true allows add vendor level
										'read' 					=> true
									);
			add_role( WOO_VOU_VENDOR_ROLE,esc_html__( 'Voucher Vendor', 'woovoucher' ), $capabilities );
		} else {
			
			$vendor_role->add_cap( WOO_VOU_VENDOR_LEVEL );
		}
		
		$role = get_role( 'administrator' );
		$role->add_cap( WOO_VOU_VENDOR_LEVEL );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.1.0' );
	} //check plugin set option value is 1.0
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.1.0' ) {
		
		// update default order pdf name
		update_option( 'order_pdf_name', 'woo-voucher-{current_date}' );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.1.1' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.1.1' ) {
		
		update_option( 'vou_pdf_usability', '0' );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.2' );
	} // check plugin set option value is 1.1.1
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.2' ) {
	
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.3' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.3' ) {
		
		// Get attachment pdf file name
		$attach_pdf_file_name = get_option( 'attach_pdf_name' );
		
		if( empty( $attach_pdf_file_name ) ) {
			// update default value for attchment pdf file name
			update_option( 'attach_pdf_name', 'woo-voucher-' );
		}
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.4' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.4' ) {
		
		update_option( 'vou_allow_bcc_to_admin', 'no' );
		update_option( 'woo_vou_set_option', '1.5' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.5' ) {

		//Add capabilities to roles
		woo_vou_add_role_capabilities();

		// Get attachment pdf file name
		$vou_gift_notification_time = get_option( 'vou_gift_notification_time' );
		
		if( $vou_gift_notification_time === '' ) {
			// update default value for attchment pdf file name
			update_option( 'vou_gift_notification_time', 0 );
		}

		update_option( 'woo_vou_set_option', '3.3.4' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );

	if( $woo_vou_set_option == '3.3.4' ) {

		// Get attachment pdf title name
		$vou_pdf_title = get_option( 'vou_pdf_title' );
		
		if( empty( $vou_pdf_title ) ) {
			// update default value for attchment pdf title name
			update_option( 'vou_pdf_title', 'WooCommerce Voucher' );
		}

		// Get attachment pdf author name
		$vou_pdf_author = get_option( 'vou_pdf_author' );
		
		if( empty( $vou_pdf_author ) ) {
			// update default value for attchment pdf author name
			update_option( 'vou_pdf_author', 'WooCommerce' );
		}

		// Get attachment pdf creator name
		$vou_pdf_creator = get_option( 'vou_pdf_creator' );
		
		if( empty( $vou_pdf_creator ) ) {
			// update default value for attchment pdf creator name
			update_option( 'vou_pdf_creator', 'WooCommerce' );
		}

		update_option( 'woo_vou_set_option', '3.3.5' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );

	if( $woo_vou_set_option == '3.3.5' ) {

		// Get attachment mail option
		$vou_attach_mail = get_option( 'vou_attach_mail' );
		
		if (get_option('vou_attach_mail') == 'yes') { 

			update_option( 'vou_attach_processing_mail', 'yes' );
			update_option( 'vou_attach_gift_mail', 'yes' );
			delete_option( 'vou_attach_mail' );
		} else { 

			update_option( 'vou_attach_processing_mail', 'no' );
			update_option( 'vou_attach_gift_mail', 'no' );
			delete_option( 'vou_attach_mail' );
		}

		// update default value for download from processing or completed order mail
		update_option( 'vou_download_processing_mail', 'yes' );
		// update default value for download from gift notification mail
		update_option( 'vou_download_gift_mail', 'yes' );
		// update default value for download from download page and order thank you page
		update_option( 'vou_download_dashboard', 'yes' );
		// update default value for voucher downlaod text
		update_option( 'vou_download_text', esc_html__( 'Voucher Download', 'woovoucher' ) );

		update_option( 'woo_vou_set_option', '3.5.0' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );

	if( $woo_vou_set_option == '3.5.0' ) {

		update_option( 'vou_allow_vendor_access_voucher_settings', 'no' );

		update_option( 'woo_vou_set_option', '3.5.4' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	if( $woo_vou_set_option == '3.5.4' ) {

		// Update default option for password protected PDF
		update_option( 'vou_enable_pdf_password_protected', 'no' );
		update_option( 'vou_pdf_password_pattern', 'woovou-{order_id}' );
		update_option( 'woo_vou_set_option', '3.5.5' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	if( $woo_vou_set_option == '3.5.5' ) {
		update_option( 'vou_enable_voucher_preview', 'no' );
		update_option( 'woo_vou_set_option', '3.5.6' );
	}

	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	if( $woo_vou_set_option == '3.5.6' ) {

		
	}
	
}

/**
 * Change pdf cache Dir and
 * create directory on activation
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.2
 */
function woo_vou_create_cache_folder() {
	
	$files	= array(
				array(
					'base' 		=> WOO_VOU_UPLOAD_DIR,
					'file' 		=> '.htaccess',
					'content' 	=> 'deny from all'
				),
				array(
					'base' 		=> WOO_VOU_UPLOAD_DIR,
					'file' 		=> 'index.html',
					'content' 	=> ''
				),
				array(
					'base' 		=> WOO_VOU_PREVIEW_UPLOAD_DIR,
					'file' 		=> '',
					'content' 	=> ''
				)
			);
	
	foreach ( $files as $file ) {
		if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
			if ( $file_handle = fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
				fwrite( $file_handle, $file['content'] );
				fclose( $file_handle );
			}
		}
	}
}

/**
 * Default Settings
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_default_settings() {
	
	// Create default templates
	$default_templates = woo_create_default_templates();
	
	// Get default template page id
	$default_template_page_id = isset( $default_templates['default_template'] ) ? $default_templates['default_template'] : '';
	
	$options = array(
					'vou_site_logo'							=> '',
					'vou_pdf_name'							=> esc_html__( 'woo-purchased-voucher-codes-{current_date}', 'woovoucher' ),
					'vou_pdf_title'							=> esc_html__( 'WooCommerce Voucher', 'woovoucher' ),
					'vou_pdf_author'						=> esc_html__( 'WooCommerce', 'woovoucher' ),
					'vou_pdf_creator'						=> esc_html__( 'WooCommerce', 'woovoucher' ),
					'vou_csv_name'							=> esc_html__( 'woo-purchased-voucher-codes-{current_date}', 'woovoucher' ),
					'vou_pdf_template'						=> $default_template_page_id,
					'vou_char_support'						=> '',
					'vou_attach_processing_mail'			=> '',
					'vou_attach_gift_mail'					=> '',
					'vou_download_processing_mail'			=> 'yes',
					'vou_download_gift_mail'				=> 'yes',
					'vou_download_dashboard'				=> 'yes',
					'vou_enable_vendor_access_all_voucodes' => '',
					'vou_change_expiry_date'				=> 'yes',
					'vou_voucher_delivery_options' 			=> 'email',
					'vou_exp_type'							=> 'based_on_purchase',
					'vou_days_diff'							=> '7',
					'vou_download_text'						=> esc_html__( 'Voucher Download', 'woovoucher' ),
					'vou_enable_wcmp_vendor_acess_pdf_vou_meta' => 'yes',
					'vou_allow_vendor_access_voucher_settings'	=> 'no',
					'vou_enable_voucher_preview'			=> 'no'
				);
	
	foreach ($options as $key => $value) {
		update_option( $key, $value );
	}
}