<?php

class Ph_Shipment_Tracking_Shortcodes {

	function __construct() {

		if( !is_admin() )
		{
			add_shortcode( 'ph-shipment-tracking-page',array($this, 'shipment_tracking_page_shortcodes') );
		}
	}

	/**
	 * Shipment Tracking Page
	**/
	public static function shipment_tracking_page_shortcodes()
	{
		ob_start();

		require_once ('front/class-tracking-page.php');
		PH_Shipment_Tracking_Page::submit_tracking_number();
		
		return ob_get_clean();
	} 

}