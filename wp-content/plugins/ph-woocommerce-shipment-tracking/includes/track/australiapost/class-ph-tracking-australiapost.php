<?php

class PHTrackingAustraliaPost extends PH_ShipmentTrackingAbstract {

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
	protected function get_api_tracking_status( $shipmentId, $apiURL ) {

		$apiTracking 			= new ApiTracking();
		$apiTracking->status 	= '';
		$apiTracking->error 	= '';

		// To send Mail based on Status
		$apiTracking->livestatus 	= '';

		$response 				= $this->ph_get_australiapost_track_response( $shipmentId, $apiURL );

		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();
		
		} else {

			$responseBody	= isset( $response['body'] ) ?  $response['body'] : '';
			$responseCode	= isset( $response['response']['code'] ) ?  $response['response']['code'] : '';

			if( $responseBody && $responseCode == '200' ) {

				$responseBodyObj = json_decode($responseBody);

				if( isset($responseBodyObj->tracking_results) && isset($responseBodyObj->tracking_results[0]) && isset($responseBodyObj->tracking_results[0]->trackable_items) ) {

					$activities 			= $responseBodyObj->tracking_results[0]->trackable_items[0]->events;
					$apiTracking->status 	= (string) $responseBodyObj->tracking_results[0]->status;
					
					// Assign Status
					$apiTracking->livestatus 	= (string) $responseBodyObj->tracking_results[0]->status;

					// Shipment Progress
					$apiTracking->shipment_progress = new stdClass();
					$activityHistory 				= array();

					if( empty(self::$wp_date_format) ) {

						self::$wp_date_format = get_option('date_format');
					}
					
					if( empty(self::$wp_time_format) ) {

						self::$wp_time_format = get_option('time_format');
					}

					foreach( $activities as $activity ) {

						$location 		= (string) $activity->location;
						$activity_date 	= new DateTime($activity->date);

						$activityHistory[] = array(
							'location'	=>	$location,
							'date'		=>	(string)$activity_date->format( self::$wp_date_format ),
							'time'		=>	(string)$activity_date->format( self::$wp_time_format ),
							'status'	=>	(string)$activity->description,
						);
					}

					$apiTracking->shipment_progress = $activityHistory;

				} else if( isset($responseBodyObj->tracking_results) && isset($responseBodyObj->tracking_results[0]) && isset($responseBodyObj->tracking_results[0]->errors) ) {

					$activities 		= $responseBodyObj->tracking_results[0]->errors;

					foreach( $activities as $activity ) {

						$apiTracking->error = (string) $activity->message.' ['.$activity->code.']';
					}

				}

			} elseif( $responseCode == '404' ) {

				$errorNumber 			= $responseCode;
				$description 			= "Server Not Found";
				$apiTracking->error 	= $description.' ['.$errorNumber.']';

			}else{

				$errorNumber 			= $responseCode;
				$description 			= $response['response']['message'];
				$apiTracking->error 	= (string) $description.' ['.$errorNumber.']';

			}

		}

		return $apiTracking;
	}

	private function ph_get_australiapost_track_response( $shipmentId, $apiURL ) {

		$settings	= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$accountNumber 		= isset($settings['australia-post']) && isset($settings['australia-post']['accountnum']) ? $settings['australia-post']['accountnum'] : '';
		$apiKey 			= isset($settings['australia-post']) && isset($settings['australia-post']['apikey']) ? $settings['australia-post']['apikey'] : '';
		$apiPassword 		= isset($settings['australia-post']) && isset($settings['australia-post']['password']) ? $settings['australia-post']['password'] : '';

		$endPoint 			= $apiURL.'/track?tracking_ids='.$shipmentId;

		$response = wp_remote_get( $endPoint, array(
			'headers'	=> array(
				'Accept' 					=> 'application/json',
				'content-type'				=> 'application/json',
				'Account-Number'			=> $accountNumber,
				'Authorization' 			=> 'Basic ' . base64_encode( $apiKey . ':' . $apiPassword ),
			),
			'timeout'	=>	20,
			'body'		=>	array(),
		));

		return $response;
	}
	
}