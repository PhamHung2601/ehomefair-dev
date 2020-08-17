<?php

global $ph_active_plugins;

if( empty($ph_active_plugins) ) {
	$ph_active_plugins = PH_Shipment_Tracking_Common::get_active_plugins();
}

$this->active_plugins = $ph_active_plugins;

$settingsData 		= get_option( 'ph_shipment_tracking_settings_data' );
$ups_integration 	= isset($settingsData['ups_integration']) ? $settingsData['ups_integration'] : 'no';
$fedex_integration 	= isset($settingsData['fedex_integration']) ? $settingsData['fedex_integration'] : 'no';

if ( in_array( 'ups-woocommerce-shipping/ups-woocommerce-shipping.php', $this->active_plugins) && $ups_integration  == 'yes' ) {

	add_action('ph_ups_shipment_tracking_detail_ids', 'ph_update_ups_shipment_id_into_metabox', 20, 2);

	if( ! function_exists('ph_update_ups_shipment_id_into_metabox') ) {
		function ph_update_ups_shipment_id_into_metabox($shipment_ids, $order_id)
		{
			$shipping_service 		= 'ups';
			$order_date				= date('Y-m-d');
			$tracking_description 	= '';

			$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $order_id, $shipment_ids, $shipping_service, $order_date, $tracking_description );

			$shipment_result 	= get_pluginhive_shipment_info( $order_id, $shipment_source_data );

			if ( null != $shipment_result && is_object( $shipment_result ) ) {

				$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
				update_post_meta( $order_id, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, $shipment_result_array );
			}
			else {
				update_post_meta( $order_id, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, '' );
			}

		}
	}
}


if ( in_array( 'fedex-woocommerce-shipping/fedex-woocommerce-shipping.php', $this->active_plugins) && $fedex_integration  == 'yes' ) {

	add_action('ph_fedex_shipment_tracking_detail_ids', 'ph_update_fedex_shipment_id_into_metabox', 20, 2);

	if( ! function_exists('ph_update_fedex_shipment_id_into_metabox') ) {

		function ph_update_fedex_shipment_id_into_metabox($shipment_ids, $order_id)
		{
			$shipping_service 		= 'fedex';
			$order_date				= date('Y-m-d');
			$tracking_description 	= '';

			$shipment_ids 	= rtrim($shipment_ids,',');
			$exsisting_ids 	= get_post_meta( $order_id, WF_Tracking_Admin::SHIPMENT_SOURCE_KEY, true );

			if( isset($exsisting_ids['shipment_id_cs']) && !empty($exsisting_ids['shipment_id_cs'])  && $exsisting_ids['shipment_id_cs'] == $shipment_ids ) {

				return;
			}

			$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $order_id, $shipment_ids, $shipping_service, $order_date, $tracking_description );

			$shipment_result 	= get_pluginhive_shipment_info( $order_id, $shipment_source_data );
			
			if ( null != $shipment_result && is_object( $shipment_result ) ) {

				$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
				update_post_meta( $order_id, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, $shipment_result_array );
			} else {
				update_post_meta( $order_id, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, '' );
			}

			wp_redirect( admin_url( '/post.php?post='.$order_id.'&action=edit' ) );

		}
	}
}

// Similar function in WF_Tracking_Admin - get_shipment_info (Parent) Any Changes to that function should be updated here
// Added seperate function to avoid execution of all the hooks defined in the constructer ( Duplication )
function get_pluginhive_shipment_info( $post_id, $shipment_source_data ) {

	if( empty( $post_id ) ) {
		$wftrackingmsg = 0;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.WF_Tracking_Admin::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
		exit;
	}

	if( '' == $shipment_source_data['shipping_service'] ) {
		update_post_meta( $post_id, WF_Tracking_Admin::SHIPMENT_SOURCE_KEY, $shipment_source_data );
		update_post_meta( $post_id, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, '' );

		$wftrackingmsg = 6;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.WF_Tracking_Admin::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
		exit;
	}

	update_post_meta( $post_id, WF_Tracking_Admin::SHIPMENT_SOURCE_KEY, $shipment_source_data );

	try {
		$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );
	}catch( Exception $e ) {
		$wftrackingmsg = 0;
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.WF_Tracking_Admin::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
		exit;
	}

	return $shipment_result;
}