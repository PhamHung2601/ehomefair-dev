<?php

class PHTrackingBlueDart extends PH_ShipmentTrackingAbstract {

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

		$response 				= $this->ph_get_bluedart_track_response( $shipmentId, $apiURL );

		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();
		
		} else if( isset($response["body"]) && !empty($response["body"]) ) {

			$xmlResponse 		= simplexml_load_string( $response['body'] );

			if( isset($xmlResponse->Error) ) {

				$description 			= (string) $xmlResponse->Error;
				$apiTracking->error 	= $description;

			} else if( isset($xmlResponse->Shipment) && isset($xmlResponse->Shipment->StatusType) && $xmlResponse->Shipment->StatusType == 'NF' ) {

				$description 			= (string) $xmlResponse->Shipment->Status;
				$apiTracking->error 	= $description;

			} else if( isset($xmlResponse->Shipment) && isset($xmlResponse->Shipment->Scans) ) {

				$activities 			= $xmlResponse->Shipment->Scans->ScanDetail;
				$apiTracking->status 	= (string) $xmlResponse->Shipment->Status;
				
				// Assign Status Type
				$apiTracking->livestatus 	= (string) $xmlResponse->Shipment->StatusType;
				
				$statusDate 			= new DateTime($xmlResponse->Shipment->StatusDate.$xmlResponse->Shipment->StatusTime);

				if( empty(self::$wp_date_format) ) {
					self::$wp_date_format = get_option('date_format');
				}

				if( empty(self::$wp_time_format) ) {
					self::$wp_time_format = get_option('time_format');
				}

				if( $statusDate instanceof DateTime ) {

					$apiTracking->status .= '<br/>'.$statusDate->format( self::$wp_date_format.' '.self::$wp_time_format);
				}

				// Shipment Progress
				$apiTracking->shipment_progress = new stdClass();
				$activityHistory 				= array();

				foreach( $activities as $activity ) {

					$location 		= (string) ( $activity->ScannedLocationCode.', '.$activity->ScannedLocation );
					$activityDate 	= new DateTime($activity->ScanDate.$activity->ScanTime);

					$activityHistory[] = array(
						'location'	=>	$location,
						'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
						'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
						'status'	=>	(string)$activity->Scan,
					);
				}

				$apiTracking->shipment_progress = $activityHistory;
			}
		} else {

			$apiTracking->error 	= "Service temporarily unavailable. Please try after sometime!";
		}

		return $apiTracking;
	}

	private function ph_get_bluedart_track_response( $shipmentId, $apiURL ) {

		$settings	= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$userId 		= isset($settings['blue-dart']) && isset($settings['blue-dart']['userid']) ? $settings['blue-dart']['userid'] : '';
		$apiKey 		= isset($settings['blue-dart']) && isset($settings['blue-dart']['apikey']) ? $settings['blue-dart']['apikey'] : '';

		$base 			= '/RoutingServlet?handler=tnt&action=custawbquery&loginid='.$userId.'&awb=awb&numbers='.$shipmentId.'&format=xml&lickey='.$apiKey.'&verno=1.3&scan=1';
		$endPoint 		= $apiURL.$base;

		$response = wp_remote_get( $endPoint,
			array(
				'timeout'   => 50,
			)
		);

		return $response;
	}
	
}