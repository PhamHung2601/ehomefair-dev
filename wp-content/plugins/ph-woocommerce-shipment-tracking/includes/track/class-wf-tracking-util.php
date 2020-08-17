<?php

class Ph_Shipment_Tracking_Util {
	const TRACKING_SETTINGS_TAB_KEY 		= "wf_tracking";
	const TRACKING_DATA_KEY 				= "_tracking_data";
	const TRACKING_MESSAGE_KEY 				= "_custom_message";
	const TRACKING_TURN_OFF_API_KEY				= "_turn_off_api";
	const TRACKING_TURN_OFF_CSV_IMPORT_KEY		= "_turn_off_csv_import";
	const TRACKING_TURN_OFF_EMAIL_STATUS_KEY	= "_turn_off_email_status";
	const TAG_SHIPMENT_SERVICE				= "[SERVICE]";
	const TAG_SHIPMENT_DATE					= "[DATE]";
	const TAG_SHIPMENT_ID					= "[ID]";
	const TAG_SHIPPING_POST_CODE			= "[PIN]";

	const TRACKING_SHIPMENT_ID		= 'ph_shipment_tracking_ids';
	const TRACKING_SHIPMENT_SERVICE = 'ph_shipment_tracking_shipping_service';
	const TRACKING_SHIPMENT_ORDER_DATE  = 'ph_shipment_tracking_order_date';
	const TRACKING_SHIPMENT_DESCRIPTION = 'ph_shipment_tracking_descriptions';
	
	const TRACKING_LIVE_API_ORDER = 'ph_shipment_tracking_live_api_order';

	public static function convert_shipment_result_obj_to_array ( $shipment_result_obj ) {
		$shipment_result_array 				= array();
		$shipment_result_array['message']	= $shipment_result_obj->message;

		$tracking_info_array = array();
		if( !empty( $shipment_result_obj->tracking_info_obj_array ) ) {
			foreach ( $shipment_result_obj->tracking_info_obj_array as $tracking_info_obj ) {
				$tracking_info					= array();
				$tracking_info['tracking_link']	= $tracking_info_obj->tracking_link;
				$tracking_info['tracking_id']	= $tracking_info_obj->tracking_id;
				$tracking_info_array[] 			= $tracking_info;
			}
			
			$shipment_result_array['tracking_info'] = $tracking_info_array;
		}
		
		$tracking_info_api_array = array();
		if( !empty( $shipment_result_obj->tracking_info_api_obj_array ) ) {
			foreach ( $shipment_result_obj->tracking_info_api_obj_array as $tracking_info_api_obj ) {
				$tracking_info_api							= array();
				$tracking_info_api['tracking_link']			= $tracking_info_api_obj->tracking_link;
				$tracking_info_api['tracking_id']			= $tracking_info_api_obj->tracking_id;
				$tracking_info_api['api_tracking_status']	= $tracking_info_api_obj->api_tracking_status;
				$tracking_info_api['api_tracking_error']	= $tracking_info_api_obj->api_tracking_error;
				$tracking_info_api['shipment_progress']		= ! empty($tracking_info_api_obj->shipment_progress) ? $tracking_info_api_obj->shipment_progress : null;
				$tracking_info_api_array[]					= $tracking_info_api;
			}

			$shipment_result_array['tracking_info_api'] = $tracking_info_api_array;
		}

		return $shipment_result_array;
	}
	
	public static function load_tracking_data( $sort = false, $force_default =  false ) {
		
		$tracking_data		= include( 'data-wf-tracking.php' );
		$tracking_data		= self::transform_tracking_data( $tracking_data );

		if( !$force_default ) {
			$tracking_data = get_option( 'ph_shipment_tracking_carrier_data' , $tracking_data );
		}

		if( $sort) {
			ksort( $tracking_data );
		}

		return $tracking_data;
	}
	
	public static function transform_tracking_data( $input_tracking_data ) {
		$tracking_data = array();
		foreach ( $input_tracking_data as $key => $tracking_ele ) {
			$name = $tracking_ele[ 'name' ];
			$new_key = sanitize_title( $name );
			$tracking_data[ $new_key ]	= $tracking_ele;
		}
		
		return $tracking_data;
	}

	public static function convert_tracking_data_to_piped_text( $tracking_data ) {
		$tracking_data_txt = '';
		foreach ( $tracking_data as $key => $tracking_ele ) {
			$tracking_data_txt .= $tracking_ele[ 'name' ];
			$tracking_data_txt .= ' | ';
			$tracking_data_txt .= $tracking_ele[ 'tracking_url' ];
			$tracking_data_txt .= "\n";
		}

		return $tracking_data_txt;
	}
	
	/**
	 * default_tracking_data can be obtained by calling load_tracking_data by setting force_default param true.
	 */
	public static function convert_piped_text_to_tracking_data( $tracking_data_txt, $default_tracking_data ) {
		$data_txt_array	= explode( "\n", $tracking_data_txt );
		$tracking_data 	= array();
		
		foreach ( $data_txt_array as  $data_txt ) {
			$name			= '';
			$tracking_url 	= '';
			$api_url		= '';
			
			$data_elem = explode( "|", $data_txt );
			if( isset( $data_elem[0] ) && '' != trim( $data_elem[0] ) ) {
				$name = trim( $data_elem[0] );
				if ( isset( $data_elem[1]) ) {
					$tracking_url = trim( $data_elem[1] );
				}
				
				$key = sanitize_title( $name );
				$api_url = '';
				if( isset( $default_tracking_data[$key]['api_url'] ) ) {
					$api_url = $default_tracking_data[$key]['api_url'];
				}
			}

			if ( '' != $name ) {
				$tracking_data_val = array();
				$tracking_data_val['name'] = $name;
				$tracking_data_val['tracking_url'] = $tracking_url;
				$tracking_data_val['api_url'] = $api_url;
				$tracking_data[ $key ] = $tracking_data_val;
			}
		}
		
		return $tracking_data;
	}
	
	public static function get_default_shipment_message_placeholder() {
		$message = 'Your order was shipped on [DATE] via [SERVICE]. To track shipment, please follow the link of shipment ID(s) [ID]';
		return $message;
	}

	public static function get_shipment_custom_message() {
		
		global $post;

		$settings 					= get_option( 'ph_shipment_tracking_settings_data' );
		$shipment_custom_message 	= isset($settings['custom_message']) ? $settings['custom_message'] : '';

		if( empty($shipment_custom_message) ) {
			$shipment_custom_message = __( 'Your order was shipped on [DATE] via [SERVICE]. To track shipment, please follow the link of shipment ID(s) [ID]', 'woocommerce-shipment-tracking');
		}

		return apply_filters('wf_custom_tracking_message', $shipment_custom_message, get_locale(), $post );
	}

	public static function get_shipment_display_custom_message( $shipment_result_array, $shipment_source_data, $lookup_page = false ) {

		$store_id 				= '';
		$live_order_packages	= array();

		$settings 	= get_option( 'ph_shipment_tracking_settings_data' );
		$url_link 	= isset($settings['custom_page_url']) ? $settings['custom_page_url'] : '';
		$order_id 	= isset($shipment_source_data['order_id']) && !empty($shipment_source_data['order_id']) ? $shipment_source_data['order_id'] : '';
		

		if( !empty($order_id) ) {
			
			$live_order_packages 	= get_post_meta( $order_id, self::TRACKING_LIVE_API_ORDER, true );
			$store_id 				= get_option( self::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		}

		$shipment_display_message = '';
		if ( isset( $shipment_result_array['tracking_info'] ) ) {

			$shipment_custom_message = self::get_shipment_custom_message();
		
			$tracking_id_substr = '';

			if( !empty($store_id) && !empty($url_link) && !empty($live_order_packages) && is_array($live_order_packages) ) {

				foreach ($live_order_packages as $package) {
					
					$url_link_with_query = $url_link.'?tracking_number='.$package['trackingId'];
					$tracking_id_substr .= ' <a href="'.$url_link_with_query.'" target="_blank" class="ph_tracking_link">'.$package['trackingId'].'</a>,';
				}

			} else if( !empty($url_link) && !empty($order_id) && !$lookup_page ) {

				foreach ( $shipment_result_array['tracking_info'] as $tracking_info ) {
					
					$url_link_with_query = $url_link.'?OTNum='.base64_encode($order_id.'|'.$tracking_info['tracking_id']);
					$tracking_id_substr .= ' <a href="'.$url_link_with_query.'" target="_blank" class="ph_tracking_link">'.$tracking_info['tracking_id'].'</a>,';
				}

			} else {

				foreach ( $shipment_result_array['tracking_info'] as $tracking_info ) {

					if( '' == $tracking_info['tracking_link'] ) {
						$tracking_id_substr .= $tracking_info['tracking_id'].',';
					} else {
						$tracking_id_substr .= ' <a href="'.$tracking_info['tracking_link'].'" target="_blank" class="ph_tracking_link">'.$tracking_info['tracking_id'].'</a>,';
					}
				}
			}

			$tracking_id_substr = rtrim( $tracking_id_substr, ',' );
			$tracking_id_substr = trim( $tracking_id_substr );

			// To display Date in Wordpress Format
			if( !empty($shipment_source_data['order_date']) ) {
				$wp_date_format = get_option('date_format');
				$order_date 	= new DateTime($shipment_source_data['order_date']);
				$order_date 	= $order_date->format($wp_date_format);
			} else {
				$order_date 	= $shipment_source_data['order_date'];
			}
			
			$tracking_data = self::load_tracking_data();
			$shipping_service_key		= $shipment_source_data['shipping_service'];
			$shipping_service_substr	= $tracking_data[ $shipping_service_key ]['name'];
			$order_date_substr			= $order_date;

			$shipment_display_message	= $shipment_custom_message;
			$shipment_display_message 	= str_replace(self::TAG_SHIPMENT_ID, $tracking_id_substr, $shipment_display_message);
			$shipment_display_message 	= str_replace(self::TAG_SHIPMENT_SERVICE, $shipping_service_substr, $shipment_display_message);
			$shipment_display_message 	= str_replace(self::TAG_SHIPMENT_DATE, $order_date_substr, $shipment_display_message);
		}

		return $shipment_display_message;
	}
	
	public static function get_shipment_display_default_message( $shipment_result_array ) {
		$message  = '';
		if ( isset( $shipment_result_array['tracking_info'] ) ) {
			$message .= $shipment_result_array['message'];
			$sub_message_1 = ' To track shipment, please follow the shipment ID(s)';
			$sub_message_2 = '';

			foreach ( $shipment_result_array['tracking_info'] as $tracking_info ) {
				if( '' != trim($tracking_info['tracking_id']) ) {
					$sub_message_2 .= ' ';
					if( '' == $tracking_info['tracking_link'] ) {
						$sub_message_2 .= $tracking_info['tracking_id'].',';
					}
					else {
						$sub_message_2 .= ' <a href="'.$tracking_info['tracking_link'].'" target="_blank">'.$tracking_info['tracking_id'].'</a>,';
					}
				}
			}

			$sub_message_2 = rtrim( $sub_message_2, ',' );
			$trimmed_sub_message_2 = trim( $sub_message_2 );
			if( '' != $trimmed_sub_message_2 ) {
				$message .= $sub_message_1;
				$message .= $sub_message_2;
				$message .= '.';
			}
		}

		return $message;
	}
	
	public static function prepare_shipment_source_data( $order_id, $shipment_id_cs, $shipping_service, $order_date, $description = null, $import = false ) {

		$shipment_source_data						= array();
		$shipment_source_data['shipment_id_cs']		= $shipment_id_cs;
		$shipment_source_data['shipping_service']	= $shipping_service;
		$shipment_source_data['order_date']			= $order_date;
		$shipment_source_data['order_id']			= $order_id;
		$shipment_source_data['tracking_shipment_descriptions']	= sanitize_textarea_field($description);

		update_post_meta($order_id, self::TRACKING_SHIPMENT_ID, $shipment_source_data['shipment_id_cs']);
		update_post_meta($order_id, self::TRACKING_SHIPMENT_SERVICE, $shipment_source_data['shipping_service']);
		update_post_meta($order_id, self::TRACKING_SHIPMENT_ORDER_DATE, $shipment_source_data['order_date']);
		update_post_meta($order_id, self::TRACKING_SHIPMENT_DESCRIPTION, $shipment_source_data['tracking_shipment_descriptions']);

		
		$order	=  ( WC()->version < '2.7.0' ) ? new WC_Order( $order_id ) : new wf_order( $order_id );    

		// Create order at Server Side only when indivisual Order Update
		// Imported CSV Order creation using Cron Job
		if( !$import ) {

			$store_id 	= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
			$result		= new PH_Shipment_Tracking_API();

			if( !empty($store_id) && is_object($result) )
			{
				$result->ph_server_side_order_creation($order_id, $shipment_id_cs, $shipping_service, $order);
			}
		}

		if( isset( $order->shipping_postcode ) ) {
			$shipment_source_data['shipping_postcode'] = $order->shipping_postcode;
		}
		else {
			$shipment_source_data['shipping_postcode'] = '';
		}
		
		return $shipment_source_data;
	}
	
	public static function get_shipping_service_key( $service_name ) {
		return sanitize_title( $service_name );
	}

	public static function update_tracking_data ( 	$order_id, 
													$shipment_id_cs, 
													$shipping_service, 
													$shipment_source_key, 
													$shipment_result_key, 
													$order_date='', $tracking_description = null, $import = false ) {

		$shipment_source_data = get_post_meta( $order_id, $shipment_source_key, true);
		if( isset( $shipment_tracking_source['shipment_id_cs'] ) ) {
			$shipment_source_data['shipment_id_cs'] = $shipment_id_cs;
			$shipment_source_data['shipping_service']	= $shipping_service;
			$shipment_source_data['order_date']			= $order_date;
			$shipment_source_data['order_id']			= $order_id;
		}
		else {
			$shipment_source_data = self::prepare_shipment_source_data( $order_id, $shipment_id_cs, $shipping_service, $order_date, $tracking_description, $import );
		}

		update_post_meta( $order_id, $shipment_source_key, $shipment_source_data );

		$shipment_result	= self::get_shipment_result( $shipment_source_data );

		if ( null != $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = self::convert_shipment_result_obj_to_array ( $shipment_result );
			update_post_meta( $order_id, $shipment_result_key, $shipment_result_array );
			$message = self::get_shipment_display_message( $shipment_result_array, $shipment_source_data );
		}
		else {
			$message = __( 'Unable to update tracking info.', 'woocommerce-shipment-tracking' );
			update_post_meta( $order_id, $shipment_result_key, '' );
		}
		
		return $message;
	}

	public static function get_shipment_result( $shipment_source_data ) {
		PH_ShipmentTrackingFactory::init();
		$shipment_source_obj					= new ShipmentSource();
		$shipment_source_obj->shipment_id_cs	= isset ( $shipment_source_data['shipment_id_cs'] ) ? $shipment_source_data['shipment_id_cs'] : '';
		$shipment_source_obj->shipping_service	= isset ( $shipment_source_data['shipping_service'] ) ? $shipment_source_data['shipping_service'] : '';
		$shipment_source_obj->order_date		= isset ( $shipment_source_data['order_date'] ) ? $shipment_source_data['order_date'] : '';
		$shipment_source_obj->shipping_postcode	= isset ( $shipment_source_data['shipping_postcode'] ) ? $shipment_source_data['shipping_postcode'] : '';

		$wf_tracking 			= PH_ShipmentTrackingFactory::create( $shipment_source_obj );

		if( empty($wf_tracking) ) {

			$shipment_result = '';
			return $shipment_result;
		}
		
		$shipment_result	= $wf_tracking->get_shipment_info();

		return $shipment_result;
	}
	
	/**
	 * Get Shipment Tracking Message.
	 */
	public static function get_shipment_display_message( $shipment_result_array, $shipment_source_data ) {
		$shipment_custom_message	= self::get_shipment_custom_message();
		$message = self::get_shipment_display_custom_message( $shipment_result_array, $shipment_source_data );
		return $message;
	}

	/**
	 * Shipment Description Message.
	 */
	public static function get_shipment_description_as_message($shipment_source_data) {

		$tracking_description	= null;
		if( isset($shipment_source_data['tracking_shipment_descriptions']) && ! empty($shipment_source_data['tracking_shipment_descriptions']) &&  ! empty($shipment_source_data['shipment_id_cs'])  ) {
			
			$table_data_style = "style='border: 1px solid #dddddd;text-align: left;padding: 8px;'";
			$tracking_description	= "<table style='border-collapse: collapse; width: 100%;'>
											<tr>
												<th $table_data_style>".__( 'Tracking No.', 'woocommerce-shipment-tracking' )."</th>
												<th $table_data_style>".__( 'Description', 'woocommerce-shipment-tracking' )."</th>
											</tr>";
			$tracking_id_arr		= explode( ',', $shipment_source_data['shipment_id_cs'] );
			$tracking_desc_arr		= explode( '|', $shipment_source_data['tracking_shipment_descriptions'] );

			foreach( $tracking_id_arr as $tracking_id ) {
				$tracking_description .= "<tr>
											<td $table_data_style>".$tracking_id."</td>
											<td $table_data_style>".array_shift($tracking_desc_arr)."</td>
										</tr>";
			}
			$tracking_description .= "</table>";
		}
		return apply_filters( 'ph_shipment_tracking_descriptions',$tracking_description, $shipment_source_data );
	}
}
