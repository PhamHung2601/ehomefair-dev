<?php

/**
 * Aramex Live tracking, Sample Tracking ID - 32615433093
 */
class PHTrackingAramex extends PH_ShipmentTrackingAbstract {

	/**
	 * Wordpress date format.
	 */
	public static $wp_date_format;
	/**
	 * Wordpress time format.
	 */
	public static $wp_time_format;

	/**
	 * Get Live tracking Status.
	 * @param $shipment_id string Tracking Id.
	 * @param $api_uri (Optional) URL.
	 * @return object Tracking Status
	 */
	protected function get_api_tracking_status( $shipment_id, $api_uri ='' ) {

		$apiTracking = new ApiTracking();
		$apiTracking->status 	= '';
		$apiTracking->error 	= '';

		// To send Mail based on Status
		$apiTracking->livestatus 	= '';

		if( !$this->is_soap_available() ) {

			$apiTracking->error = "SoapClient is not enabled for your website. Contact your Hosting Provider to enable SoapClient and try again.";

			return $apiTracking;
		}

		$response 					= $this->ph_get_aramex_response( $shipment_id );

		if( is_object($response) && !($response->HasErrors) ) {

			if( isset($response->TrackingResults) && isset($response->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY) ) {

				$shipmentInfo 	= $response->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
				$shipmentInfo 	= json_encode($shipmentInfo);
				$activities 	= json_decode($shipmentInfo,TRUE);

				$apiTracking->status 	= (string) $activities[0]['UpdateDescription'];
				$statusDate 			= new DateTime($activities[0]['UpdateDateTime']);

				// Assign Latest Event Code
				$apiTracking->livestatus 	= (string) $activities[0]['UpdateCode'];

				if( empty(self::$wp_date_format) ) {
					self::$wp_date_format = get_option('date_format');
				}

				if( empty(self::$wp_time_format) ) {
					self::$wp_time_format = get_option('time_format');
				}

				if( $statusDate instanceof DateTime ) {

					$apiTracking->status .= '<br/>'.$statusDate->format( self::$wp_date_format.' '.self::$wp_time_format);
				}

				// Shipment progress
				$apiTracking->shipment_progress = new stdClass();
				$activityHistory 				= array();

				foreach( $activities as $activity ) {

					$location 		= (string) $activity['UpdateLocation'];
					$activityDate 	= new DateTime($activity['UpdateDateTime']);

					$activityHistory[] = array(
						'location'	=>	$location,
						'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
						'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
						'status'	=>	(string)$activity['UpdateDescription'],
					);
				}

				$apiTracking->shipment_progress = $activityHistory;

			} elseif ( isset($response->NonExistingWaybills) && !empty($response->NonExistingWaybills) ) {

				$apiTracking->error = __( 'Invalid Shipment Id', 'woocommerce-shipment-tracking' );

			}
		} elseif ( isset($response->Notifications) && !empty($response->Notifications) ) {

			$notification 		= $response->Notifications->Notification;

			if( is_array($notification) ) {

				$error 				= (string) $notification[0]->Message;
				$apiTracking->error = __( $error, 'woocommerce-shipment-tracking' );

			} else {

				$error 				= (string) $notification->Message;
				$apiTracking->error = __( $error, 'woocommerce-shipment-tracking' );
			}
		}
		
		return apply_filters( 'ph_woocommerce_shipment_tracking_aramex_api_tracking', $apiTracking, $shipment_id, $response );
	}

	/**
	 * Track the shipment.
	 * @param $shipment_id Shipment Id or Tracking Id.
	 * @return object FedEx response.
	 */
	private function ph_get_aramex_response( $shipment_id ) {

		$request 	= $this->ph_aramex_request( $shipment_id );
		$response 	= null;

		$client = $this->ph_create_soap_client( __DIR__.'/wsdl/shipments-tracking-api-wsdl.wsdl' );
		
		try {
			$response = $client->TrackShipments($request);
		}
		catch( Exception $e ) {

		}

		return $response;
	}

	/**
	 * Check whether Soap is active or not.
	 * @return boolean true if soap is loaded else false.
	 */
	private function is_soap_available() {

		if( extension_loaded( 'soap' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Create Soap object.
	 * @param $wsdl string Path to wsdl file.
	 * @return object Soap object.
	 */
	private function ph_create_soap_client( $wsdl ) {
		
		$soapclient = new SoapClient( $wsdl, 
			array(
				'trace' =>	true,
			)
		);

		return $soapclient;
	}
	
	/**
	 * Create Aramex Tracking request as array.
	 * @param $tracking_id string Tracking Id that has to be tracked.
	 * @return array Tracking Request.
	 */
	private function ph_aramex_request( $tracking_id ) {

		$settings		= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$aramexUserName 	= isset($settings['aramex']) && isset($settings['aramex']['user_name']) ? $settings['aramex']['user_name'] : '';
		$aramexPassword 	= isset($settings['aramex']) && isset($settings['aramex']['password']) ? $settings['aramex']['password'] : '';
		$aramexAccountNum 	= isset($settings['aramex']) && isset($settings['aramex']['account_num']) ? $settings['aramex']['account_num'] : '';
		$aramexAccountPin 	= isset($settings['aramex']) && isset($settings['aramex']['account_pin']) ? $settings['aramex']['account_pin'] : '';
		$aramexEntity 		= isset($settings['aramex']) && isset($settings['aramex']['entity']) ? $settings['aramex']['entity'] : '';
		$aramexCountryCode 	= isset($settings['aramex']) && isset($settings['aramex']['country_code']) ? $settings['aramex']['country_code'] : '';

		$request = array(
			'ClientInfo'  			=> array(
				'UserName'			 	=> $aramexUserName,
				'Password'			 	=> $aramexPassword,
				'Version'			 	=> 'v1.0',
				'AccountNumber'		 	=> $aramexAccountNum,
				'AccountPin'		 	=> $aramexAccountPin,
				'AccountEntity'		 	=> $aramexEntity,
				'AccountCountryCode'	=> $aramexCountryCode,
			),
			'Transaction' 			=> array(
				'Reference1'			=> '001' 
			),
			'Shipments'				=> array( $tracking_id ),
			'GetLastTrackingUpdateOnly'	=> 0,
		);

		return $request;

	}
}