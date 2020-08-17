<?php

class PHTrackingDHLExpress extends PH_ShipmentTrackingAbstract {

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

		$response 				= $this->ph_get_dhlexpress_track_response( $shipmentId, $apiURL );
		
		if ( is_wp_error( $response ) ) {

		    $apiTracking->error = $response->get_error_message();

		} else if( isset( $response["body"] ) ) {

			$xmlResponse 		= simplexml_load_string( $response['body'] );

			if( isset($xmlResponse->AWBInfo) && isset($xmlResponse->AWBInfo->Status) && isset($xmlResponse->AWBInfo->Status->ActionStatus) && $xmlResponse->AWBInfo->Status->ActionStatus == 'success' ) {

				$shipmentInfo 	= $xmlResponse->AWBInfo->ShipmentInfo;
				$shipmentInfo 	= json_encode($shipmentInfo);
				$shipmentInfo 	= json_decode($shipmentInfo,TRUE);

				$activities 	= $shipmentInfo['ShipmentEvent'];
				$activities 	= array_reverse($activities);

				$apiTracking->status 	= (string) $activities[0]['ServiceEvent']['Description'];
				$statusDate 			= new DateTime($activities[0]['Date'].$activities[0]['Time']);

				// Assign Latest Event Code
				$apiTracking->livestatus 	= (string) $activities[0]['ServiceEvent']['EventCode'];

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

					$location 		= (string) $activity['ServiceArea']['ServiceAreaCode'].', '.$activity['ServiceArea']['Description'];
					$activityDate 	= new DateTime($activity['Date'].$activity['Time']);

					$activityHistory[] = array(
						'location'	=>	$location,
						'date'		=>	(string)$activityDate->format( self::$wp_date_format ),
						'time'		=>	(string)$activityDate->format( self::$wp_time_format ),
						'status'	=>	(string)$activity['ServiceEvent']['Description'],
					);
				}

				$apiTracking->shipment_progress = $activityHistory;
			} else {

				if( isset($xmlResponse->AWBInfo) && isset($xmlResponse->AWBInfo->Status) ) {
					$apiTracking->error 	= (string) $xmlResponse->AWBInfo->Status->ActionStatus;
				} else if( isset($xmlResponse->Response) && isset($xmlResponse->Response->Status) && isset($xmlResponse->Response->Status->Condition) ) {
					$apiTracking->error 	= (string) $xmlResponse->Response->Status->Condition->ConditionData;
				}
				
			}
		}

		return $apiTracking;
	}

	private function ph_get_dhlexpress_track_response( $shipmentId, $apiURL ) {

		$settings	= get_option( 'ph_shipment_tracking_saved_carrier_list', true );

		$siteId 	= isset($settings['dhl-express']) && isset($settings['dhl-express']['siteid']) ? $settings['dhl-express']['siteid'] : '';
		$password 	= isset($settings['dhl-express']) && isset($settings['dhl-express']['apikey']) ? $settings['dhl-express']['apikey'] : '';

		$request 			= $this->ph_dhlexpress_track_request( $siteId, $password, $shipmentId );

		$response 	= wp_remote_post( $apiURL,
			array(
				'timeout'   => 70,
				'sslverify' => 0,
				'body'      => $request
			)
		);

		return $response;
	}

	private function ph_dhlexpress_track_request( $siteId, $password, $shipmentId ) {

		$currentWPTime = date_create(current_time('c',false));

		$xml_request 	 = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml_request 	.= '<req:KnownTrackingRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown.xsd">'; 
		$xml_request 	.= '<Request><ServiceHeader>';
		$xml_request 	.= '<MessageTime>'.$currentWPTime->format('c').'</MessageTime>';
		$xml_request 	.= '<MessageReference>1234567890123456789012345678901</MessageReference>';
		$xml_request 	.= '<SiteID>'.$siteId.'</SiteID>';
		$xml_request 	.= '<Password>'.$password.'</Password>';
		$xml_request 	.= '</ServiceHeader></Request>';
		$xml_request 	.= '<LanguageCode>en</LanguageCode>';

		if( strstr($shipmentId, 'JD') ) {

			$xml_request 	.= '<LPNumber>'.$shipmentId.'</LPNumber>';

		} else {

			$xml_request 	.= '<AWBNumber>'.$shipmentId.'</AWBNumber>';
		}

		$xml_request 	.= '<LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>'; 
		$xml_request 	.= '</req:KnownTrackingRequest>';

		$request 		= str_replace( array( "\n", "\r" ), '', $xml_request );
		
		return $request;
	}
}