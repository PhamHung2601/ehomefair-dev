<?php

/**
 * USPS Live tracking, Sample Tracking Id - 9405809699937747605537
 */
class WfTrackingUSPS extends PH_ShipmentTrackingAbstract {

	/**
	 * Wordpress date format.
	 */
	public static $wp_date_format;
	/**
	 * Wordpress time format.
	 */
	public static $wp_time_format;

	protected function get_api_tracking_status( $shipment_id, $api_uri ) {

		$apiTracking = new ApiTracking();
		$apiTracking->status 	= '';
		$apiTracking->error 	= '';

		// To send Mail based on Status
		$apiTracking->livestatus 	= '';

		$settings		= get_option( 'ph_shipment_tracking_saved_carrier_list', true );
		$usps_user_id 	= isset($settings['united-states-postal-service-usps']) && isset($settings['united-states-postal-service-usps']['userid']) ? $settings['united-states-postal-service-usps']['userid'] : '';

		if( empty($usps_user_id) ) {

			$apiTracking->error 	= 'Please enter USPS Credentials to get Tracking History';
			
			return $apiTracking;
		}

		$response 				= $this->wf_get_trackv2_response( $shipment_id, $usps_user_id, $api_uri );
		
		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();

		} else if( isset( $response["body"] ) ) {

			$xml_response 	= simplexml_load_string( $response['body'] );

			$trackinfo 		= $xml_response->TrackInfo;
			
			if( isset($trackinfo->Error ) ) {

				$description 			= (string) $trackinfo->Error->Description;
				$error_number			= (string) $trackinfo->Error->Number;
				$apiTracking->error 	= $description.' ['.$error_number.']';
			
			} else {

				$trackSummary 	= $trackinfo->TrackSummary;
				$trackDetail 	= $trackinfo->TrackDetail;
				
				$apiTracking->status 	= (string) $trackSummary->Event;
				$summaryDate 			= new DateTime($trackSummary->EventDate.$trackSummary->EventTime);

				// Assign Event Code
				$apiTracking->livestatus 	= (string) $trackSummary->EventCode;

				if( empty(self::$wp_date_format) ) {
					self::$wp_date_format = get_option('date_format');
				}

				if( empty(self::$wp_time_format) ) {
					self::$wp_time_format = get_option('time_format');
				}

				if( $summaryDate instanceof DateTime ) {

					$apiTracking->status .= '<br/>'.$summaryDate->format( self::$wp_date_format.' '.self::$wp_time_format);
				}

				// Shipment progress
				$apiTracking->shipment_progress = new stdClass();
				$activity_history 				= array();
				$location 						= null;

				$city 	= isset($trackSummary->EventCity) ? $trackSummary->EventCity . ", " : '';
				$state 	= isset($trackSummary->EventState) ? $trackSummary->EventState . ", " : '';
				$zip 	= isset($trackSummary->EventZIPCode) ? $trackSummary->EventZIPCode : '';

				$location 	= $city.$state.$zip;

				$activity_history[] = array(

					'location'	=>	$location,
					'date'		=>	null,
					'time'		=>	null,
					'status'	=> (string) $trackSummary->Event,
				);

				if( ! empty($trackDetail) ) {

					foreach( $trackDetail as $activity ) {

						$location 		= null;
						$activityDate 	= new DateTime($activity->EventDate.$activity->EventTime);
						$city 			= isset($trackSummary->EventCity) ? $trackSummary->EventCity . ", " : '';
						$state 			= isset($trackSummary->EventState) ? $trackSummary->EventState . ", " : '';
						$zip 			= isset($trackSummary->EventZIPCode) ? $trackSummary->EventZIPCode : '';
						$location 		= $city.$state.$zip;

						$activity_history[] = array(
							'location'	=> $location,
							'date'		=> (string) $activityDate->format( self::$wp_date_format ),
							'time'		=> (string) $activityDate->format( self::$wp_time_format ),
							'status'	=> (string) $activity->Event,
						);
					}
				}

				$apiTracking->shipment_progress = $activity_history;
			}
			
		}

		return $apiTracking;
	}

	private function wf_get_trackv2_response( $shipment_id, $usps_user_id, $api_uri ) {

		$request = $this->wf_trackv2_request( $api_uri, $shipment_id, $usps_user_id );

		$response = wp_remote_get( $request,
			array(
				'timeout'   => 70,
			)
		);
 
		return $response;
	}
	
	private function wf_trackv2_request( $tracking_api_uri, $shipment_id, $usps_user_id ) {
		
		$xml_request 	 = '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml_request 	.= '<TrackFieldRequest USERID="'.$usps_user_id.'">';
		$xml_request 	.= '<Revision>1</Revision>';
		$xml_request 	.= '<ClientIp>0.0.0.0</ClientIp>';
		$xml_request 	.= '<SourceId>PH</SourceId>';
		$xml_request 	.= '<TrackID ID="'.$shipment_id.'"></TrackID>';
		$xml_request 	.= '</TrackFieldRequest>';

		$request 		 = $tracking_api_uri.'?API=TrackV2&XML='.str_replace( array( "\n", "\r" ), '', $xml_request );
		
		return $request;
	}
}