<?php

if (!defined('ABSPATH')) {
	exit;
}

class WF_Shipment_Tracking_Settings {

	/**
	 * Product Exporter Tool
	 */
	public static function save_settings() {
		global $wpdb;

		$enable_ftp_ie		= !empty($_POST['enable_ftp_ie']) ? true : false;
		$ftp_or_sftp		= ! empty($_POST['ftp_or_sftp']) ? $_POST['ftp_or_sftp'] : '';
		$ftp_server 		= !empty($_POST['ftp_server']) ? $_POST['ftp_server'] : '';
		$ftp_server_path 	= !empty($_POST['ftp_server_path']) ? $_POST['ftp_server_path'] : '';
		$ftp_user 			= !empty($_POST['ftp_user']) ? $_POST['ftp_user'] : '';
		$ftp_password 		= !empty($_POST['ftp_password']) ? $_POST['ftp_password'] : '';
		$use_ftps 			= !empty($_POST['use_ftps']) ? true : false;
		$use_passive_mode 	= ! empty($_POST['use_passive_mode']) ? true : false;
		$ftp_port 			= !empty($_POST['ftp_port']) ? $_POST['ftp_port'] : '';
		$ftp_timeout 		= !empty($_POST['ftp_timeout']) ? $_POST['ftp_timeout'] : '90';
		
		$shipment_tracking_auto_import 				= !empty($_POST['shipment_tracking_auto_import']) ? $_POST['shipment_tracking_auto_import'] : 'Disabled';
		$shipment_tracking_auto_import_start_time 	= !empty($_POST['shipment_tracking_auto_import_start_time']) ? $_POST['shipment_tracking_auto_import_start_time'] : '';
		$shipment_tracking_auto_import_interval 	= !empty($_POST['shipment_tracking_auto_import_interval']) ? $_POST['shipment_tracking_auto_import_interval'] : '';
		$shipment_tracking_auto_import_delimiter 	= !empty($_POST['shipment_tracking_auto_import_delimiter']) ? $_POST['shipment_tracking_auto_import_delimiter'] : ',';
		$shipment_tracking_auto_import_merge 		= !empty($_POST['shipment_tracking_auto_import_merge']) ? true : false;
		
		$settings = array(
			'ftp_or_sftp'       =>  $ftp_or_sftp,
			'ftp_server'        =>  $ftp_server,
			'ftp_user'          =>  $ftp_user,
			'ftp_password'      =>  $ftp_password,
			'use_ftps'          =>  $use_ftps,
			'ftp_port'          =>  $ftp_port,
			'use_passive_mode'  =>  $use_passive_mode,
			'ftp_timeout'       =>  $ftp_timeout,
			'enable_ftp_ie'     =>  $enable_ftp_ie,
			'ftp_server_path'   =>  $ftp_server_path
		);

		$auto_settings = array();
		$auto_settings['shipment_tracking_auto_import'] = $shipment_tracking_auto_import;
		$auto_settings['shipment_tracking_auto_import_start_time'] = $shipment_tracking_auto_import_start_time;
		$auto_settings['shipment_tracking_auto_import_interval'] = $shipment_tracking_auto_import_interval;
		$auto_settings['shipment_tracking_auto_import_delimiter'] = $shipment_tracking_auto_import_delimiter;
		$auto_settings['shipment_tracking_auto_import_merge'] = $shipment_tracking_auto_import_merge;

		update_option('wf_shipment_tracking_importer_ftp', $settings);

		$auto_settings_db = get_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', null);

		$orig_import_start_inverval = '';

		if (isset($auto_settings_db['shipment_tracking_auto_import_start_time']) && isset($auto_settings_db['shipment_tracking_auto_import_interval'])) {
			$orig_import_start_inverval = $auto_settings_db['shipment_tracking_auto_import_start_time'] . $auto_settings_db['shipment_tracking_auto_import_interval'];
		}

		update_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', $auto_settings);

		// clear scheduled import event in case import interval was changed
		if ($orig_import_start_inverval !== $auto_settings['shipment_tracking_auto_import'] . $auto_settings['shipment_tracking_auto_import_interval']) {
			// note this resets the next scheduled execution time to the time options were saved + the interval
			wp_clear_scheduled_hook('wf_shipment_tracking_csv_im_ex_auto_import');
		}

		wp_redirect(admin_url('/admin.php?page=' . WF_SHIPMENT_TRACKING_CSV_IM_EX . '&tab=settings'));
		exit;
	}

}