<?php

class PH_Shipment_Tracking_Page {

	function __construct() {}

	public function init_field() {}

	public static function submit_tracking_number() {

		ob_start();

		// To support Lookup Page using Access Token
		if( isset($_GET['tracking_number']) && !empty($_GET['tracking_number']) ) {

			$tracking_data = self::get_shipment_tracking_details_from_api();

			if( is_array($tracking_data) && !empty($tracking_data) ) {

				return wc_get_template('custom_tracking_details.php',$tracking_data,'',plugin_dir_path(__FILE__));
			}
		}

		// Tracking Link in My Account Page & Email
		if( isset($_GET['OTNum']) && !empty($_GET['OTNum']) ) {

			$tracking_data = self::get_shipment_tracking_details_from_tracking_num();

			if( is_array($tracking_data) && !empty($tracking_data) ) {

				return wc_get_template('custom_order_tracking_details.php',$tracking_data,'',plugin_dir_path(__FILE__));
			}
		}

		// Lookup Page Tracking using Order Number & Order Email
		if( isset($_POST['order_number']) && !empty($_POST['order_number']) ) {
			
			$tracking_data = self::get_shipment_tracking_details_from_order();

			if( is_array($tracking_data) && !empty($tracking_data) ) {

				return wc_get_template('custom_order_tracking_details.php',$tracking_data,'',plugin_dir_path(__FILE__));
			}
		}

		return require_once('custom_tracking_page.php');

		return ob_get_clean();
	}

	public static function get_shipment_tracking_details_from_order() {

		$order_number 		= sanitize_user( $_POST['order_number'] );
		$order_email 		= sanitize_user( $_POST['order_email'] );

		// To support the WooCommerce Sequential Order Number & Other plugins which modify the Order Number
		$order_number 		= apply_filters( 'xa_tracking_importer_order_id', $order_number);

		$order 	= wc_get_order( $order_number );
		$result = [];

		if( $order instanceof WC_Order ) {

			$meta_key 	= 'wf_wc_shipment_source';
			$email 		= $order->get_billing_email();

			if( $order_email == $email ) {

				$shipment_source_data 	= get_post_meta( $order_number, $meta_key, true );
				$shipment_result_array 	= get_post_meta( $order_number , 'wf_wc_shipment_result', true );
				$tracking_notice 		= Ph_Shipment_Tracking_Util::get_shipment_display_custom_message ( $shipment_result_array, $shipment_source_data, true );

				if( !empty($shipment_source_data) ) {

					$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );
					$trackingInfo 		= $shipment_result->tracking_info_obj_array;

					if( isset($shipment_result->tracking_info_api_obj_array) && !empty($shipment_result->tracking_info_api_obj_array) ) {

						$trackingApiInfo 	= $shipment_result->tracking_info_api_obj_array;

						$result		= array(
							'message'		=>	$tracking_notice,
							'trackingInfo'	=>	$trackingInfo,
							'liveTracking'	=>	$trackingApiInfo,
						);

					} else {

						$result		= array(
							'message'		=>	$tracking_notice,
							'trackingInfo'	=>	$trackingInfo,
							'liveTracking'	=>	null,
						);
					}
				} else {

					$result		= array(
						'message'		=>	"No tracking number found for the Order. Please contact your Shipper.",
						'trackingInfo'	=>	null,
						'liveTracking'	=>	null,
					);
				}
			} else {

				$result		= array(
					'message'		=>	"Invalid Email. Please enter a valid Email Id used for placing the order and try again.",
					'trackingInfo'	=>	null,
					'liveTracking'	=>	null,
				);

			}
		} else {

			$result		= array(
				'message'		=>	"Incorrect Order Number. Please enter a valid Order Number and try again.",
				'trackingInfo'	=>	null,
				'liveTracking'	=>	null,
			);
		}

		return $result;
	}

	public static function get_shipment_tracking_details_from_tracking_num() {

		$order_track_arr 	= explode( '|', base64_decode( $_GET['OTNum'] ) );
		$order_number 		= $order_track_arr[0];
		$tracking_num 		= $order_track_arr[1];

		// To support the WooCommerce Sequential Order Number & Other plugins which modify the Order Number
		$order_number 		= apply_filters( 'xa_tracking_importer_order_id', $order_number);

		$order 	= wc_get_order( $order_number );
		$result = [];

		if( $order instanceof WC_Order ) {

			$meta_key 	= 'wf_wc_shipment_source';
			
			$shipment_source_data 	= get_post_meta( $order_number, $meta_key, true );
			$shipment_result_array 	= get_post_meta( $order_number , 'wf_wc_shipment_result', true );
			$tracking_notice 		= Ph_Shipment_Tracking_Util::get_shipment_display_custom_message ( $shipment_result_array, $shipment_source_data, true );

			if( !empty($shipment_source_data) ) {

				$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );

				$trackingInfo 		= $shipment_result->tracking_info_obj_array;

				if( isset($shipment_result->tracking_info_api_obj_array) && !empty($shipment_result->tracking_info_api_obj_array) ) {

					$trackingApiInfo 	= $shipment_result->tracking_info_api_obj_array;

					$result		= array(
						'message'		=>	$tracking_notice,
						'trackingInfo'	=>	$trackingInfo,
						'liveTracking'	=>	$trackingApiInfo,
					);

				} else {

					$result		= array(
						'message'		=>	$tracking_notice,
						'trackingInfo'	=>	$trackingInfo,
						'liveTracking'	=>	null,
					);
				}
			} else {

				$result		= array(
					'message'		=>	"No tracking number found for the Order. Please contact your Shipper.",
					'trackingInfo'	=>	null,
					'liveTracking'	=>	null,
				);
			}
			
		} else {

			$result		= array(
				'message'		=>	"Incorrect Order Number. Please enter a valid Order Number and try again.",
				'trackingInfo'	=>	null,
				'liveTracking'	=>	null,
			);
		}

		return $result;
	}

	public static function get_shipment_tracking_details_from_api() {
		
		$tracking_number 	= sanitize_user( $_GET['tracking_number'] );

		$store_id 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$access_key 		= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id' );
		
		if( isset($_GET['idType']) && !empty($_GET['idType']) )
		{
			$url 				= PH_SHIPMENT_TRACKING_STORE_ID_URL."/api/v1/shipment/history/".$tracking_number."?idType='phtrackingId'"; 	// Tracking URL
		}else{
			$url 				= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/shipment/history/'.$tracking_number; 	// Tracking URL
		}

		$result 			= array();

		if( !empty($store_id) && !empty($access_key) )
		{
			$response = wp_remote_get( $url, array(
				'headers'	=> array(
					'Authorization' 			=> 'Barrer ' . $access_key,
					'x-ph-wc-track-store-id' 	=> $store_id,
				),
				'timeout'	=>	20,
				'body'		=>	array(),
			));

			if( is_wp_error($response) ) {

				$error_code = $response->get_error_code();

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$error_code,
					'Message'	=>	$response->get_error_message($error_code),
					'TrackingHistory'	=>	Null,
					'Order'		=> Null,
				);

			}else{

				$response_body	= isset( $response['body'] ) ?  $response['body'] : '';
				$response_code	= isset( $response['response']['code'] ) ?  $response['response']['code'] : '';

				if( $response_body && $response_code == '200' )
				{
					$response_body_obj = json_decode($response_body);

					if( $response_body_obj->success )
					{

						$result		= array(
							'Status'	=>	true,
							'Code'		=>	$response_code,
							'Message'	=>	'',
							'TrackingHistory'	=>	$response_body_obj->trackingHistory,
							'Order'		=> $response_body_obj->order,
						);
					}else{

						$result		= array(
							'Status'	=>	false,
							'Code'		=>	$response_code,
							'Message'	=>	$response_body_obj->message,
							'TrackingHistory'	=>	Null,
							'Order'		=> Null,
						);
					}

				}elseif( $response_code == '404' ){

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	"Server Not Found",
						'TrackingHistory'	=>	Null,
						'Order'		=> Null,
					);

				}else{

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response['response']['message'],
						'TrackingHistory'	=>	Null,
						'Order'		=> Null,
					);
				}

			}	
		}

		return $result;
	}
}