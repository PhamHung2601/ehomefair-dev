<?php

/**
 * UPS Live tracking, Sample Tracking Id - 1ZRA41980198576880
 */
class WfTrackingUPS extends PH_ShipmentTrackingAbstract {

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
	 * @param $api_uri string Tracking API URL.
	 * @return object Tracking Status
	 */
	protected function get_api_tracking_status( $shipment_id, $api_uri ) {

		$apiTracking 			= new ApiTracking();
		$apiTracking->status 	= '';
		$apiTracking->error 	= '';

		// To send Mail based on Status
		$apiTracking->livestatus 	= '';

		$response 				= $this->wf_get_trackv2_response( $shipment_id, $api_uri );

		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();

		} else if( isset($response["body"]) ) {

			$xml_response 		= simplexml_load_string( $response['body'] );
			
			if( isset($xml_response->Response->Error) && !empty($xml_response->Response->Error) ) {

				$error_number 			= (string) $xml_response->Response->Error->ErrorCode;
				$description 			= (string) $xml_response->Response->Error->ErrorDescription;
				$apiTracking->error 	= $description.' ['.$error_number.']';

			} elseif( $xml_response->Response->ResponseStatusCode == 1 ) {

				$activities 			= $xml_response->Shipment->Package->Activity;
				$apiTracking->status 	= (string) $activities->Status->StatusType->Description;
				$activity_date 			= new DateTime($activities->Date.$activities->Time);

				// Assign Status Code
				$apiTracking->livestatus 	= (string) $activities->Status->StatusType->Code;

				if( empty(self::$wp_date_format) ) {
					self::$wp_date_format = get_option('date_format');
				}

				if( empty(self::$wp_time_format) ) {
					self::$wp_time_format = get_option('time_format');
				}

				if( $activity_date instanceof DateTime ) {

					$apiTracking->status .= '<br/>'.$activity_date->format( self::$wp_date_format.' '.self::$wp_time_format);
				}

				// Shipment progress
				$apiTracking->shipment_progress 	= new stdClass();
				$activity_history 					= array();

				foreach( $activities as $activity ) {

					$location 		= null;
					$city 			= (string) $activity->ActivityLocation->Address->City;
					$state 			= (string) $activity->ActivityLocation->Address->StateProvinceCode;
					$country 		= (string) $activity->ActivityLocation->Address->CountryCode;
					$activityDate 	= new DateTime($activity->Date.$activity->Time);

					if( ! empty($city) ) {
						$location = $city;
					}

					if( ! empty($state) ) {
						$location = ! empty($location) ? $location. ', '.$state : $state;
					}
					
					if( ! empty($country) ) {
						$location = ! empty($location) ? $location. ', '.$country : $country;
					}

					$activity_history[] = array(
						'location'	=>	$location,
						'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
						'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
						'status'	=>	(string)$activity->Status->StatusType->Description
					);
				}
				
				$apiTracking->shipment_progress = $activity_history;
			}
		}

		return $apiTracking;
	}

	/**
	 * Track the shipment.
	 * @param $shipment_id Shipment Id or Tracking Id.
	 * @param $api_uri string Tracking API url
	 * @return object UPS Tracking response.
	 */
	private function wf_get_trackv2_response( $shipment_id, $api_uri ) {

		$settings		= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$user_id 			= isset($settings['ups']) && isset($settings['ups']['userid']) ? $settings['ups']['userid'] : '';
		$password 			= isset($settings['ups']) && isset($settings['ups']['password']) ? $settings['ups']['password'] : '';
		$access_key 		= isset($settings['ups']) && isset($settings['ups']['accesskey']) ? $settings['ups']['accesskey'] : '';

		$request 	= $this->wf_ups_trackv2_request( $api_uri, $shipment_id, $user_id, $password, $access_key );

		$response 	= wp_remote_post( $api_uri,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $request
			)
		);
 
		return $response;
	}
	
	/**
	 * Create UPS Tracking request as xml.
	 * @param $tracking_api_uri string Tracking API URL.
	 * @param $shipment_id string Tracking Id that has to be tracked.
	 * @param $user_id string UPS user id.
	 * @param $password string UPS Password.
	 * @param $access_key string UPS access key.
	 * @return array Tracking Request.
	 */
	private function wf_ups_trackv2_request( $tracking_api_uri, $shipment_id, $user_id, $password, $access_key ) {
		$xml_request 	 = '<?xml version="1.0" ?>';
		$xml_request 	.= '<AccessRequest xml:lang="en-US">'; 
		$xml_request 	.= '<AccessLicenseNumber>'.$access_key.'</AccessLicenseNumber>';
		$xml_request 	.= '<UserId>'.$user_id.'</UserId>';
		$xml_request 	.= '<Password>'.$password.'</Password>';
		$xml_request 	.= '</AccessRequest>';
		$xml_request 	.= '<?xml version="1.0" ?>';
		$xml_request 	.= '<TrackRequest>';
		$xml_request 	.= '<Request>';
		$xml_request 	.= '<RequestAction>Track</RequestAction>';
		$xml_request	.= '<RequestOption>1</RequestOption>';				// For shipment tracking history
		$xml_request 	.= '</Request>'; 
		$xml_request 	.= '<TrackingNumber>'.$shipment_id.'</TrackingNumber>';

		// Mail Innovation Tracking ID contains all numeric characters
		// ctype_digit() - Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise
		if( ctype_digit($shipment_id) && strlen($shipment_id) > 18 ) {
			$xml_request 	.= '<IncludeMailInnovationIndicator></IncludeMailInnovationIndicator>';
		}
		
		$xml_request 	.= '</TrackRequest>';

		$request 		= str_replace( array( "\n", "\r" ), '', $xml_request );
		
		return $request;
	}
}