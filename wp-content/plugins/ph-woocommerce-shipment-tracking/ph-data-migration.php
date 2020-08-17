<?php

if( ! defined('ABSPATH') )	exit;

// Data Migration March 2020 - v2.5.0

if( ! class_exists("PH_Tracking_Data_Migration") ) {

	class PH_Tracking_Data_Migration {

		public function __construct() {
			
			// Migrate data from old plugin settings to new - One Time
			$data_migrated = get_option( "ph_shipment_tracking_data_migrated", false );

			if( !$data_migrated ) {

				$this->savedListSettingsId 	= "ph_shipment_tracking_saved_carrier_list"; 	// Added Carriers List
				$this->carrierSettingsId 	= "ph_shipment_tracking_carrier_data"; 			// All the Carrier List - New
				$this->displayCarriersId 	= "wf_tracking_tracking_data"; 					// All Carrier Tracking List for Old Settings

				$this->oldTrackingData 		= get_option( $this->displayCarriersId );
				$this->upsCred 				= get_option( "wf_tracking_ups" );
				$this->fedexCred 			= get_option( "wf_tracking_fedex" );

				$this->tracking_data_migration();
			}
		}

		private function tracking_data_migration() {
			
			// Get All the Supported Carrier List from the File
			$inputTrackingData	= include( 'includes/track/data-wf-tracking.php' );
			$trackingData 		= array();

			// Form a array from the file data
			foreach ( $inputTrackingData as $trackingDetails ) {

				$name 		= $trackingDetails[ 'name' ];
				$new_key 	= sanitize_title( $name );

				$trackingData[ $new_key ]	= $trackingDetails;
			}

			$this->newTrackingData 		= $trackingData;
			$this->savedTrackingData 	= [];

			if( !empty($trackingData) && !empty($this->oldTrackingData) ) {

				// Loop all existing tracking carrier list from file
				foreach ($trackingData as $carrierKey => $carrierData) {

					// Loop customer added tracking carrier list
					foreach ($this->oldTrackingData as $addedKey => $addedData) {
						
						// If Customer Added Tracking Carrier Matches/Exists in the File Carrier List, Add tracking details from the File Data
						// Else take customer added tracking data
						if( $carrierKey == $addedKey || array_key_exists($addedKey, $trackingData) ) {

							$this->newTrackingData[$carrierKey] = array();
							$this->newTrackingData[$carrierKey] = $carrierData;

						} else {

							$this->newTrackingData[$addedKey] = array();
							$this->newTrackingData[$addedKey] = $addedData;
						}

						// To store added tracking carriers as a table in new view
						$this->savedTrackingData[$addedKey] = [];
						$carrierDetails 					= [];

						// Take UPS Credentials if it is added previously
						if ( $addedKey == 'ups' ) {

							$carrierDetails 	= array(
								'name'		=> $addedData['name'],
								'url'		=> '',
								'userid'	=> $this->upsCred['user_id'],
								'password'	=> $this->upsCred['password'],
								'accesskey'	=> $this->upsCred['access_key'],
							);

						// Take FedEx Credentials if it is added previously
						} else if ( $addedKey == 'fedex' ) {

							$carrierDetails 	= array(
								'name'			=> $addedData['name'],
								'url'			=> '',
								'accountnum'	=> $this->fedexCred['account_number'],
								'meternum'		=> $this->fedexCred['meter_number'],
								'servicekey'	=> $this->fedexCred['web_services_key'],
								'servicepass'	=> $this->fedexCred['password'],
							);

						// Add if the tracking carrier is availble in file list - w/o URL
						} else if( array_key_exists($addedKey, $trackingData) ) {

						 	$carrierDetails 	= array(
								'name'		=> $addedData['name'],
								'url'		=> '',
							);

						// Add custom carriers with URL which was added by Customers
						} else {

							$carrierDetails 	= array(
								'name'		=> $addedData['name'],
								'url'		=> $addedData['tracking_url'],
							);
						}

						$this->savedTrackingData[$addedKey] = $carrierDetails;

					}

				}
			}

			// Save all the Tracking Carrier List
			update_option( "ph_shipment_tracking_carrier_data", $this->newTrackingData );

			// Save all the Display Carrier List
			update_option( "ph_shipment_tracking_saved_carrier_list", $this->savedTrackingData );

			// Get all the Settings from old view
			$custompage 		= get_option( "wf_tracking_custom_page_url" );
			$customMessage 		= get_option( "wf_tracking_custom_message" );
			$turnOffApi 		= get_option( "wf_tracking_turn_off_api" );
			$autoRefresh 		= get_option( "wf_tracking_automatic_tracking_live_status_refresh" );
			$upsIntegration 	= get_option( "wf_tracking_ph_ups_integration" );
			$shippingEasy 		= get_option( "wf_tracking_third_party" );
			$goShippo 			= get_option( "wf_tracking_ph_go_shippo" );
			$trackingToCustomer = get_option( "wf_tracking_shipment_tracking_customer" );
			$trackingToEmail 	= get_option( "wf_tracking_shipment_tracking_email_customer" );

			$settings 		= array(
				'custom_page_url'		=> $custompage,
				'custom_message'		=> $customMessage,
				'turn_on_api'			=> $turnOffApi,
				'auto_refresh'			=> $autoRefresh,
				'ups_integration'		=> $upsIntegration,
				'shipping_easy'			=> $shippingEasy,
				'go_shippo'				=> $goShippo,
				'tracking_to_customer'	=> $trackingToCustomer,
				'tracking_to_mail'		=> $trackingToEmail,
			);

			// Save the Settings Data into new option
			update_option( "ph_shipment_tracking_settings_data", $settings );

			// Mark Data Migration as complete
			update_option( "ph_shipment_tracking_data_migrated", true );

			if( function_exists('wc_get_logger') ) {
				
				$log = wc_get_logger();
				$log->debug( '-------------- All Carrier List --------------'.PHP_EOL, array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( print_r($this->newTrackingData, 1), array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( '-------------- Added Carrier List --------------'.PHP_EOL, array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( print_r($this->savedTrackingData, 1), array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( '-------------- Settings Data --------------'.PHP_EOL, array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( print_r($settings, 1), array('source' => 'PH Shipment Tracking Data Migration') );
				$log->debug( '-------------- Data Migration Completed --------------'.PHP_EOL, array('source' => 'PH Shipment Tracking Data Migration') );

			}
		}

	}
}