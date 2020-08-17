<?php

/**
 * Canada Post
 */
class WfTrackingCanadaPost extends PH_ShipmentTrackingAbstract {
	
	/**
	 * Wordpress date format.
	 */
	public static $wp_date_format;
	/**
	 * Wordpress time format.
	 */
	public static $wp_time_format;

	protected function get_api_tracking_status( $shipment_id, $api_uri ) { 

		$apiTracking = $this->wf_cp_tracking_response( $shipment_id, $api_uri );

		return $apiTracking;
	}

	private function wf_cp_tracking_response( $shipment_id, $api_uri ) {

		$apiTracking 			= new ApiTracking( );
		$apiTracking->status 	= '';
		$apiTracking->error 	= '';

		// To send Mail based on Status
		$apiTracking->livestatus 	= '';
	
		$endpoint = str_replace( "rs/" , "", $api_uri ) . 'vis/track/pin/'.$shipment_id.'/detail';
		
		$response	= wp_remote_post( $endpoint,
			array(
				'method'	=> 'GET',
				'timeout'	=> 70, 
				'sslverify'	=> 0,
				'headers'	=> $this->wf_get_request_header('application/vnd.cpc.track+xml','application/vnd.cpc.track+xml')					
			)
		);

		if ( is_wp_error( $response ) ) {

			$apiTracking->error = $response->get_error_message();

		} else if ( !empty( $response['body'] ) ) {

			$response = $response['body'];

			libxml_use_internal_errors(true);

			$xml = simplexml_load_string($response);

			if ( !$xml ) {

				$apiTracking->error .= 'Failed loading XML' . "</br>";

				foreach( libxml_get_errors() as $error ) {

					$apiTracking->error .=  $error->message . "</br>";
				}

			} else {

				$trackingSummary = $xml->children( 'http://www.canadapost.ca/ws/track' );

				if ( isset($trackingSummary->{'significant-events'}) ) {

					$activities 			= $trackingSummary->{'significant-events'};

					$apiTracking->status 	= (string) $activities->occurrence->{'event-description'};

				// Assign Status Type
					$apiTracking->livestatus = (string) $activities->occurrence->{'event-identifier'};

					$statusDate 			= new DateTime($activities->occurrence->{'event-date'}.$activities->occurrence->{'event-time'});

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

					foreach ( $activities->occurrence as $activity ) {

						$location 		= null;
						$eventSite 		= $activity->{'event-site'} ? (string) $activity->{'event-site'} : '';
						$eventProvince 	= $activity->{'event-province'} ? (string) $activity->{'event-province'} : '';
						$activityDate 	= new DateTime($activity->{'event-date'}.$activity->{'event-time'});

						if( !empty($eventSite) ) {

							$location 	= (string) $eventSite;
						}

						if( !empty($eventProvince) ) {

							$location 	.= (string) ', '.$eventProvince;
						}

						$activityHistory[] = array(
							'location'	=>	$location,
							'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
							'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
							'status'	=>	(string)$activity->{'event-description'},
						);
					}

					$apiTracking->shipment_progress = $activityHistory;

				} else {

					$messages = $xml->children( 'http://www.canadapost.ca/ws/messages' );

					if( !empty($messages) ) {

						foreach ( $messages as $message ) {

							$apiTracking->error .=  'Error Code: ' . $message->code . "</br>";
							$apiTracking->error .=  'Error Msg: ' . $message->description . "</br>";
						}

					} else {

						$messages = $xml->children('http://www.canadapost.ca/ws/track');

						if(!empty($messages)) {

							foreach ( $messages as $message ) {
								$apiTracking->error .=  'Error Code: ' . $message->code . "</br>";
								$apiTracking->error .=  'Error Msg: ' . $message->description . "</br>";
							}
						}
					}

				}
			}
		}

		return 	$apiTracking;
	}
	
	private function wf_get_request_header( $accept, $content_type ) {

		$settings	= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$userid 	= isset($settings['canada-post']) && isset($settings['canada-post']['userid']) ? $settings['canada-post']['userid'] : '';
		$password 	= isset($settings['canada-post']) && isset($settings['canada-post']['password']) ? $settings['canada-post']['password'] : '';
		
		return array(
			'Accept' 			=> $accept,
			'Content-Type' 		=> $content_type,
			'Authorization'		=> 'Basic ' . base64_encode( $userid. ':' .$password ),
			'Accept-language'	=> 'en-CA',
		);
    }
}
