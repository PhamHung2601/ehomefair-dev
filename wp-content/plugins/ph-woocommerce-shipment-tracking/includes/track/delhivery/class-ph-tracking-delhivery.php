<?php

class PHTrackingDelhivery extends PH_ShipmentTrackingAbstract {

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

		$response 				= $this->ph_get_delhivery_track_response( $shipmentId, $apiURL );

		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();
		
		} else {

			$responseBody	= isset( $response['body'] ) ?  $response['body'] : '';
			$responseCode	= isset( $response['response']['code'] ) ?  $response['response']['code'] : '';

			if( $responseBody && $responseCode == '200' ) {

				$responseBodyObj = json_decode($responseBody);

				if( isset($responseBodyObj->ShipmentData) && isset($responseBodyObj->ShipmentData[0]) && isset($responseBodyObj->ShipmentData[0]->Shipment) && isset($responseBodyObj->ShipmentData[0]->Shipment->Scans) ) {

					$activities 			= $responseBodyObj->ShipmentData[0]->Shipment->Scans;
					$apiTracking->status 	= (string) $responseBodyObj->ShipmentData[0]->Shipment->Status->Instructions;

					// Assign Status Type
					$apiTracking->livestatus 	= (string) $responseBodyObj->ShipmentData[0]->Shipment->Status->StatusType;

					// Shipment Progress
					$apiTracking->shipment_progress = new stdClass();
					$activityHistory 				= array();

					$statusDate 			= new DateTime($responseBodyObj->ShipmentData[0]->Shipment->Status->StatusDateTime);

					if( $statusDate instanceof DateTime ) {

						if( empty(self::$wp_date_format) ) {
							self::$wp_date_format = get_option('date_format');
						}
						if( empty(self::$wp_time_format) ) {
							self::$wp_time_format = get_option('time_format');
						}
						$apiTracking->status .= '<br/>'.$statusDate->format( self::$wp_date_format.' '.self::$wp_time_format);
					}

					$activities 	= array_reverse($activities);

					foreach( $activities as $activity ) {

						$location 		= (string) $activity->ScanDetail->ScannedLocation;
						$activityDate 	= new DateTime($activity->ScanDetail->ScanDateTime);
						$status 		= isset($activity->ScanDetail->Instructions) && !empty($activity->ScanDetail->Instructions) ? $activity->ScanDetail->Instructions : $activity->ScanDetail->Scan;

						$activityHistory[] = array(
							'location'	=>	$location,
							'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
							'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
							'status'	=>	$status,
						);
					}

					$apiTracking->shipment_progress = $activityHistory;

				} else if( isset($responseBodyObj->Error) ) {

					$apiTracking->error = (string) $responseBodyObj->Error;

				}

			} elseif( $responseCode == '404' ) {

				$errorNumber 			= $responseCode;
				$description 			= "Server Not Found";
				$apiTracking->error 	= $description.' ['.$errorNumber.']';

			}else{

				$errorNumber 			= $responseCode;
				$description 			= $response['response']['message'];
				$apiTracking->error 	= $description.' ['.$errorNumber.']';

			}
		}

		return $apiTracking;
	}

	private function ph_get_delhivery_track_response( $shipmentId, $apiURL ) {

		$settings	= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$userId 		= isset($settings['delhivery']) && isset($settings['delhivery']['userid']) ? $settings['delhivery']['userid'] : '';
		$apiKey 		= isset($settings['delhivery']) && isset($settings['delhivery']['apikey']) ? $settings['delhivery']['apikey'] : '';

		$base 			= '/packages/json/?waybill='.$shipmentId.'&verbose=2&token='.$apiKey;
		$endPoint 		= $apiURL.$base;

		$response = wp_remote_get( $endPoint,
			array(
				'headers'	=> array(
					'Authorization' 	=> 'Token ' . $apiKey,
				),
				'timeout'   => 50,
			)
		);

		return $response;
	}
	
}