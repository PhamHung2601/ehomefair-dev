<?php

if (!defined('ABSPATH')) {
	exit;
}

class PH_Shipment_Tracking_API {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_action( 'wp_ajax_ph_shipment_tracking_get_store_id', array( $this, 'ph_shipment_tracking_get_store_id') );
		add_action( 'wp_ajax_ph_shipment_tracking_get_order_packages', array( $this, 'ph_shipmint_tracking_get_order_packages') );
	}

	public function ph_shipment_tracking_get_store_id()
	{	
		$url 			= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/stores'; 	// Store ID URL
		$store_url 		= get_site_url();
		$access_key 	= isset($_POST['access_key']) 	? $_POST['access_key'] 	 : '';
		$current_date 	= isset($_POST['current_date']) ? $_POST['current_date'] : '';
		$result 		= array();

		$response = wp_remote_post( $url, array(
			'headers'	=> array(
				'Authorization' => 'Barrer ' . $access_key,
			),
			'timeout'	=>	20,
			'body'		=>	array(
				'storeUrl'		=> $store_url,
				'date'			=> $current_date,
			)
		));
		
		if( is_wp_error($response) ) {

			$error_code = $response->get_error_code();

			$result		= array(
				'Status'	=>	false,
				'Code'		=>	$error_code,
				'Message'	=>	$response->get_error_message($error_code),
				'StoreId'	=>	Null
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
						'StoreId'	=>	$response_body_obj->storeId,
					);

					update_option( 'wf_tracking_ph_store_id', $response_body_obj->storeId );

				}else{
					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response_body_obj->message,
						'StoreId'	=>	Null
					);

					update_option( 'wf_tracking_ph_store_id', Null );
				}

			}elseif( $response_code == '404' ){

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	"Server Not Found",
					'StoreId'	=>	Null
				);

			}else{

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	$response['response']['message'],
					'StoreId'	=>	Null
				);

			}
		}
		
		echo print_r( json_encode($result),true);

		exit;
	}

	public function ph_server_side_order_creation($order_id, $tracking_number, $carrier_name, $order)
	{
		$debug_log 				= wc_get_logger();
		$url 					= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/stores/order'; 	// Create Order URL
		$store_id 				= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$access_key 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id' );
		$result 				= array();
		$packages 				= array();
		$supported_carriers 	= array(
			'dhl-express' 		=> 'DHL_EXPRESS',
			'fedex' 			=> 'FEDEX',
			'ups' 				=> 'UPS',
			'canada-post' 		=> 'CANADA_POST',
			'delhivery' 		=> 'DELHIVERY',
			'australian-post'	=> 'AUSTRALIA_POST',
			'australia-post'	=> 'AUSTRALIA_POST',
			'blue-dart'			=> 'BLUE_DART',
			'usps' 				=> 'USPS',
			'united-states-postal-service-usps' => 'USPS',
			'usps-united-states-postal-service' => 'USPS',
			'united-states-postal-service' 		=> 'USPS',
		);

		if( is_a( $order, 'WC_Order') && !empty($order_id) && array_key_exists($carrier_name, $supported_carriers) )
		{
			$email_id 			= $order->get_billing_email();
			$phone_no 			= $order->get_billing_phone();

			$s_first_name 		= $order->get_shipping_first_name();
			$s_last_name 		= $order->get_shipping_last_name();
			$s_company 			= $order->get_shipping_company();
			$s_address_line1 	= $order->get_shipping_address_1();
			$s_address_line2 	= $order->get_shipping_address_2();
			$s_city 			= $order->get_shipping_city();
			$s_state			= $order->get_shipping_state();
			$s_postcode			= $order->get_shipping_postcode();
			$s_country			= $order->get_shipping_country();
			
			$tracking_number_array 	= explode( ',', $tracking_number);

			if( is_array($tracking_number_array) && !empty($tracking_number_array) )
			{
				foreach ($tracking_number_array as $tracking_id)
				{
					$packages[] = array(
						'carrierType'	=> $supported_carriers[$carrier_name],
						'trackingId'	=> $tracking_id,
					);
				}
			}

			$order_array = array(
				"orderId"			=> $order_id,
				"orderDisplayId"	=> $order_id,
				"carrier"			=> $supported_carriers[$carrier_name],
				"carrierType" 		=> $supported_carriers[$carrier_name],

				"shipping" 			=> array(
					"firstName"		=> $s_first_name,
					"lastName"		=> $s_last_name,
					"company"		=> $s_company,
					"addressLine1"	=> $s_address_line1,
					"addressLine2"	=> $s_address_line2,
					"city"			=> $s_city,
					"state"			=> $s_state,
					"postcode"		=> $s_postcode,
					"email" 		=> $email_id,
					"country" 		=> $s_country,
					"phone" 		=> $phone_no,
				),

				"shipFromAddress" 	=> array(
					"firstName"		=> '',
					"lastName"		=> '',
					"company"		=> '',
					"addressLine1"	=> '',
					"addressLine2"	=> '',
					"city"			=> '',
					"state"			=> '',
					"postcode"		=> '',
					"email"			=> '',
					"country"		=> '',
					"phone"			=> '',
				),

				"packages" 			=> $packages,
			);

			$response = wp_remote_post( $url, array(
				'headers'	=> array(
					'Authorization' 			=> 'Barrer ' . $access_key,
					'x-ph-wc-track-store-id' 	=> $store_id,
					'Content-Type'				=> 'application/json',
				),
				'timeout'	=>	20,
				'body'		=>	json_encode($order_array),

			));

			if( is_wp_error($response) ) {

				$error_code = $response->get_error_code();

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$error_code,
					'Message'	=>	$response->get_error_message($error_code),
					'RequestId'	=>	Null
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
							'RequestId'	=>	$response_body_obj->requestId,
						);

						update_post_meta( $order_id, Ph_Shipment_Tracking_Util::TRACKING_LIVE_API_ORDER, $packages );

					}else{

						$result		= array(
							'Status'	=>	false,
							'Code'		=>	$response_code,
							'Message'	=>	$response_body_obj->message,
							'RequestId'	=>	Null
						);
					}

				}elseif( $response_code == '404' ){

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	"Server Not Found",
						'RequestId'	=>	Null
					);

				}else{

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response['response']['message'],
						'RequestId'	=>	Null
					);
				}
			}

			$debug_log->debug( print_r('----------------- #'.$order_id.' Order Creation Response -----------------'.PHP_EOL.json_encode($result).PHP_EOL,true), array('source' => 'PluginHive-Shipment-Tracking-Order-API'));
		}
	}

	public function ph_shipmint_tracking_get_order_packages()
	{	
		$orderUUID 		= isset($_POST['orderUUID']) 	? $_POST['orderUUID'] 	 : '';	
		$url 			= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/shipment/package/'.$orderUUID;	// Order Package URL
		$store_id 		= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$access_key 	= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id' );
		$result 		= array();

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
				'Packages'	=>	Null
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
						'Packages'	=>	$response_body_obj->packages,
					);

				}else{
					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response_body_obj->message,
						'Packages'	=>	Null
					);
				}

			}elseif( $response_code == '404' ){

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	"Server Not Found",
					'Packages'	=>	Null
				);

			}else{

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	$response['response']['message'],
					'Packages'	=>	Null
				);

			}
		}
		
		echo print_r( json_encode($result),true);

		exit;
	}

	public function ph_shipmint_tracking_get_order_count()
	{	
		
		$url 			= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/stores/orders/count';	// Order Count URL
		$store_id 		= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$access_key 	= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id' );
		$result 		= array();

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
				'Count'		=>	Null
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
						'Count'		=>	array(
							'allOrders' => $response_body_obj->allOrders,
							'initial' => $response_body_obj->initial,
							'inTransit' => $response_body_obj->inTransit,
							'delivered' => $response_body_obj->delivered,
							'exceptions' => $response_body_obj->exceptions,
							'outForDelivery' => $response_body_obj->outForDelhivery,
						),
					);

				}else{
					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response_body_obj->message,
						'Count'		=>	Null
					);
				}

			}elseif( $response_code == '404' ){

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	"Server Not Found",
					'Count'		=>	Null
				);

			}else{

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$response_code,
					'Message'	=>	$response['response']['message'],
					'Count'		=>	Null
				);

			}
		}
		
		return $result;
	}

}

new PH_Shipment_Tracking_API();