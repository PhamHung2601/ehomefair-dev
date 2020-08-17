<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PH_Shipment_Tracking_General_Settings class.
 */
class PH_Shipment_Tracking_General_Settings {

	/**
	 * Constructor.
	**/
	public function __construct() {
	}

	/**
	 * Get Settings
	**/
	public static function get_settings($settings_id='') {

		$settings = get_option($settings_id);

		return $settings;
			
	}

	/**
	 * Update Settings
	**/
	public static function update_settings($settings_id='',$settings=array()) {
		
		update_option($settings_id,$settings);
	}
}