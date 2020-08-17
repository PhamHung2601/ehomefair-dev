<?php

if( ! defined('ABSPATH') )	return;

if( ! class_exists('class-ph-shipment-tracking-third-party-compatibility.php') ) {
	class Ph_Shipment_Tracking_Third_Party_Compatibility {

		/**
		 * Constructor of Ph_Shipment_Tracking_Third_Party_Compatibility
		 */
		public function __construct() {

			// Register the Message for WPML Compatibility further it will be available in String Translation list for translation
			if( isset($_POST['wf_tracking_custom_message']) && is_admin() ) {
				$custom_message = ! empty( $_POST['wf_tracking_custom_message'] ) ? $_POST['wf_tracking_custom_message'] : 'Your order was shipped on [DATE] via [SERVICE]. To track shipment, please follow the link of shipment ID(s) [ID]';
				do_action( 'wpml_register_single_string', 'woocommerce-shipment-tracking', 'ph_shipment_tracking_message_to_display', $custom_message );
			}
			// To display the message in site language
			add_filter( 'wf_custom_tracking_message', __CLASS__."::wpml_shipment_tracking_message_compatibility" );
			
		}

		/**
		 * Get The Tracking Message in Site Language, WPML Compatibility.
		 * @param string $message Tracking Message.
		 * @return string
		 */
		public static function wpml_shipment_tracking_message_compatibility( $message ) {
			$current_language = apply_filters('wpml_current_language', null );
			$message 	= apply_filters( 'wpml_translate_single_string', $message, 'woocommerce-shipment-tracking', 'ph_shipment_tracking_message_to_display', $current_language );
			return $message;
		}
	}
}
new Ph_Shipment_Tracking_Third_Party_Compatibility();
