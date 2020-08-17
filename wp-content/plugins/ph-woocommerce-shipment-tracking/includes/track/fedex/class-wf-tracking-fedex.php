<?php

/**
 * FedEx Live tracking, Sample Tracking ID - 425858748793, 782481287663, 782481114621
 */
class WfTrackingFedEx extends PH_ShipmentTrackingAbstract {

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

		$this->soap_method 			= $this->is_soap_available() ? 'soap' : 'nusoap';

		$response 					= $this->wf_get_trackv2_response( $shipment_id );

		if( is_object($response) && isset($response->HighestSeverity) ) {

			if( $response->HighestSeverity == 'ERROR' || $response->HighestSeverity == 'FAILURE' ) {

				$error_number 			= $response->Notifications->Code;
				$description 			= $response->Notifications->Message;
				$apiTracking->error 	= $description.' ['.$error_number.']';

			} elseif( $response->HighestSeverity == 'SUCCESS' || $response->HighestSeverity == 'WARNING' ) {

				// If Tracking Number is Invalid
				if( ! empty($response->CompletedTrackDetails->TrackDetails->Notification->Code) && $response->CompletedTrackDetails->TrackDetails->Notification->Code == '9040' ) {

					$error_number 			= $response->CompletedTrackDetails->TrackDetails->Notification->Code;
					$description 			= $response->CompletedTrackDetails->TrackDetails->Notification->Message;
					$apiTracking->error 	= $description.' ['.$error_number.']';

				} else {

					$apiTracking->status 		= (string) $response->CompletedTrackDetails->TrackDetails->StatusDetail->Description;
					
					// Assign Status Detail Code
					$apiTracking->livestatus 	= (string) $response->CompletedTrackDetails->TrackDetails->StatusDetail->Code;

					// Shipment progress
					$status_details 				= $response->CompletedTrackDetails->TrackDetails->StatusDetail;
					$apiTracking->shipment_progress = new stdClass();

					if( ! empty($response->CompletedTrackDetails->TrackDetails->Events) ) {

						if( empty(self::$wp_date_format) ) {
							self::$wp_date_format = get_option('date_format');
						}

						if( empty(self::$wp_time_format) ) {
							self::$wp_time_format = get_option('time_format');
						}

						// Object if only one status
						if( is_object($response->CompletedTrackDetails->TrackDetails->Events) ) {
							$response->CompletedTrackDetails->TrackDetails->Events = array($response->CompletedTrackDetails->TrackDetails->Events);
						}

						foreach( $response->CompletedTrackDetails->TrackDetails->Events as $activity) {

							$location 			= null;
							$activity_status 	= $activity->EventDescription;
							$activityDate 		= new DateTime($activity->Timestamp);
							// Location of current Activity
							if( ! empty($activity->Address->City) ) {
								$location = $activity->Address->City;
							}

							if( ! empty($activity->Address->StateOrProvinceCode) ) {
								$location = ! empty($location) ? $location.', '.$activity->Address->StateOrProvinceCode : $activity->Address->StateOrProvinceCode;
							}
							if( ! empty($activity->Address->CountryName) ) {
								$location = ! empty($location) ? $location.', '.$activity->Address->CountryName : $activity->Address->CountryName;
							}
							
							// Set in few cases only
							if( ! empty($activity->StatusExceptionDescription) ) {
								$activity_status .= '<br/>'.(string)$activity->StatusExceptionDescription;
							}

							$activity_history[] = array(
								'location'	=>	$location,
								'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
								'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
								'status'	=>	$activity_status
							);
						}

						$apiTracking->shipment_progress = $activity_history;

					} else {
						$apiTracking->shipment_progress = array();
					}
					
					// To show delivery time if the shipment has been delivered
					if( $response->CompletedTrackDetails->TrackDetails->StatusDetail->Code == 'DL') {
						foreach( $response->CompletedTrackDetails->TrackDetails->DatesOrTimes as $dateortimes_obj ) {
							if( $dateortimes_obj->Type == 'ACTUAL_DELIVERY' ) {
								$delivery_date_obj 		= date_create($dateortimes_obj->DateOrTimestamp);
								$date_format 			= get_option('date_format');
								$time_format 			= get_option('time_format');
								$delivery_date 			= $delivery_date_obj->format( $date_format.' '.$time_format);
								$apiTracking->status 	= $apiTracking->status. ' <br/>'.$delivery_date_obj->format( $date_format.' '.$time_format);
							}
						}
					}
				}
			}
		}
		
		return apply_filters( 'ph_woocommerce_shipment_tracking_fedex_api_tracking', $apiTracking, $shipment_id, $response );
	}

	/**
	 * Track the shipment.
	 * @param $shipment_id Shipment Id or Tracking Id.
	 * @return object FedEx response.
	 */
	private function wf_get_trackv2_response( $shipment_id ) {

		$request 	= $this->wf_trackv2_request( $shipment_id );
		$response 	= null;

		$client = $this->wf_create_soap_client( __DIR__.'/wsdl/TrackService_v14.wsdl' );
		// If Soap is available
		if( $this->soap_method == 'soap' ) { 
			try {
				$response = $client ->track($request);
			}
			catch( Exception $e ) {

			}
		}
		// If soap is not available
		else {
			try{
				$result = $client->call( 'track', array( 'TrackRequest' => $request ) );
				$response = json_decode(json_encode( $result ), false);
			}
			catch( Exception $e ) {

			}
		}

		return $response;
	}

	/**
	 * Check whether Soap is active or not.
	 * @return boolean true if soap is loaded else false.
	 */
	private function is_soap_available(){
		if( extension_loaded( 'soap' ) ){
			return true;
		}
		return false;
	}

	/**
	 * Create Soap or Nusoap object.
	 * @param $wsdl string Path to wsdl file.
	 * @return object Soap or Nusoap object.
	 */
	private function wf_create_soap_client( $wsdl ){
		if( $this->soap_method == 'nusoap' ){
			if( ! class_exists('nusoap_client') ) {
				require_once PH_SHIPMENT_TRACKING_PLUGIN_PATH.'/includes/nusoap/lib/nusoap.php';
			}
			$soapclient = new nusoap_client( $wsdl, 'wsdl' );
		}else{
			$soapclient = new SoapClient( $wsdl, 
				array(
					'trace' =>	true
				)
			);
		}
		return $soapclient;
	}
	
	/**
	 * Create FedEx Tracking request as array.
	 * @param $tracking_id string Tracking Id that has to be tracked.
	 * @return array Tracking Request.
	 */
	private function wf_trackv2_request( $tracking_id ) {

		$settings		= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$account_number 	= isset($settings['fedex']) && isset($settings['fedex']['accountnum']) ? $settings['fedex']['accountnum'] : '';
		$meter_number 		= isset($settings['fedex']) && isset($settings['fedex']['meternum']) ? $settings['fedex']['meternum'] : '';
		$web_services_key 	= isset($settings['fedex']) && isset($settings['fedex']['servicekey']) ? $settings['fedex']['servicekey'] : '';
		$password 			= isset($settings['fedex']) && isset($settings['fedex']['servicepass']) ? $settings['fedex']['servicepass'] : '';

		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
				'Key' 		=> $web_services_key, 
				'Password' 	=> $password
			)
		);
		$request['ClientDetail'] = array(
			'AccountNumber' => $account_number, 
			'MeterNumber' 	=> $meter_number
		);
		$request['TransactionDetail'] = array(
			'CustomerTransactionId' => '*** Track Request ***'
		);
		$request['Version'] = array(
			'ServiceId' 	=> 'trck', 
			'Major' 		=> '14', 
			'Intermediate' 	=> '0', 
			'Minor' 		=> '0'
		);
		$request['SelectionDetails'] = array(
			'PackageIdentifier' => array(
				'Type' 	=> 'TRACKING_NUMBER_OR_DOORTAG',
				'Value'	=> $tracking_id
			)
		);
		// For Complete history
		$request['ProcessingOptions'] = array(
			'INCLUDE_DETAILED_SCANS'
		);

		return $request;

	}
}