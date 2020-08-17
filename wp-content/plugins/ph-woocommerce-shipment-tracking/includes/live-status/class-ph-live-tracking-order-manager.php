<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PH_Live_Tracking_Order_Manager class.
**/
class PH_Live_Tracking_Order_Manager {

	/**
	 * Constructor
	**/
	public function __construct() {
		
	}

	/**
	* Get Orders
	* @param array $args Order Arguments
	**/
	public static function get_orders( $args=array() ) {

		$orders 	= array();
		
		if(!empty($args)) {

			$orders = wc_get_orders($args);
		}

		return $orders;
	}

	/**
	* To get Shipment Ids
	* @param int $orderId Order Number
	* @param string $metaKey Order Meta
	**/
	public static function get_shipment_source_data( $orderId='', $metaKey = '' ) {

		$sourceData 	= '';

		if( !empty($orderId) ) {

			$sourceData = self::get_post_meta( $orderId , $metaKey );
		}

		return $sourceData;
	}

	public static function get_post_meta( $orderId, $key='' ) {

		return get_post_meta( $orderId , $key, true );
	}

	/**
	* Send Tracking Mail on Major Status Change
	* @param int $orderId Order Number
	* @param string $name Customer Name
	* @param string $email Customer Email
	* @param string $carrierName Carrier Name
	* @param object $trackingData Tracking Details
	* @param array $settings Settings
	**/
	public static function send_tracking_mail_based_on_status( $orderId, $name, $email, $carrierName, $trackingData, $settings ) {

		$trackingId 	= $trackingData->tracking_id;
		$trackingStatus = $trackingData->api_tracking_status;
		$statusCode 	= $trackingData->api_live_status;
		$progressData 	= $trackingData->shipment_progress;

		$savedList 		= get_option('ph_shipment_tracking_saved_carrier_list');
		$displayName 	= isset($savedList[$carrierName]) ? $savedList[$carrierName]['name'] : $carrierName;

		$senderEmailName 		= isset($settings['sender_email_name']) && !empty($settings['sender_email_name']) ? $settings['sender_email_name'] : '';
		$senderEmailAddress 	= isset($settings['sender_email_address']) && !empty($settings['sender_email_address']) ? $settings['sender_email_address'] : '';
		$trackingMailSubject 	= isset($settings['tracking_mail_subject']) && !empty($settings['tracking_mail_subject']) ? $settings['tracking_mail_subject'] : $defaultSubject;
		$trackingMailTemplate 	= isset($settings['tracking_mail_template']) && !empty($settings['tracking_mail_template']) ? $settings['tracking_mail_template'] : '';

		$currentStatus 	= PH_Live_Tracking_Status_Mapper::get_live_tracking_status( $statusCode, $carrierName );
		$savedStatus 	= self::get_post_meta( $orderId, 'ph_saved_tracking_status_of'.$trackingId );

		if( $carrierName == 'aramex' ) {

			$currentStatus = $statusCode;
		}

		if( !empty($currentStatus) && $currentStatus != $savedStatus ) {

			update_post_meta( $orderId, 'ph_saved_tracking_status_of'.$trackingId, $currentStatus );

			$progressTable 	= '';
			$headers 		= array();
			$attachments 	= array();

			$headers[] 		= 'Content-Type: text/html; charset=UTF-8';

			if( !empty($senderEmailName) && !empty($senderEmailAddress) ) {

				$headers[] 		= 'From: '.$senderEmailName.' <'.$senderEmailAddress.'>';
			}

			if( empty($trackingMailSubject) ) {

				$subject 	= "Tracking Details for Your Order [ORDER_NUM]";
			} else {
				$subject 	= $trackingMailSubject;
			}

			if( empty($trackingMailTemplate) ) {

				$content 	= 'Hi [CUSTOMER_NAME],<br/><br/>Your Order #[ORDER_NUM] placed using [EMAIL_ID] is Shipped via [CARRIER_NAME] and is [TRACKING_STATUS].<br/><br/>Track your orders in real-time using the following details.<br/><br/>Tracking ID - [TRACKING_ID]<br/>Or,<br/>Find the Shipment Progress below.<br/><br/>[SHIPMENT_PROGRESS]';
			} else {
				$content 	= $trackingMailTemplate;
			}

			if( !empty( $progressData) ) {

				$progressTable 		.= "<table style='width: 100%; margin: 5px; border: 1px solid #ddd'>";
				$progressTable 		.= "<tr>";
				$progressTable 		.= "<th style='padding: 5px;'>". __( 'Location', 'woocommerce-shipment-tracking'). "</th>";
				$progressTable 		.= "<th style='padding: 5px;'>". __( 'Date', 'woocommerce-shipment-tracking') ."</th>";
				$progressTable 		.= "<th style='padding: 5px;'>". __( 'Summary', 'woocommerce-shipment-tracking'). "</th>";
				$progressTable 		.= "</tr>";

				foreach( $progressData as $shipmentProgress ) {
					$progressTable 		.= "<tr>";

					$progressTable 		.= "<td style='padding: 5px;'>". $shipmentProgress['location']. "</td>";
					$progressTable 		.= "<td style='padding: 5px;'>". $shipmentProgress['date'] ." - ". $shipmentProgress['time']. "</td>";
					$progressTable 		.= "<td style='padding: 5px;'>". $shipmentProgress['status']. "</td>";
					$progressTable 		.= "</tr>";
				}

				$progressTable 		.= "</table><br/>";
			}

			$subject 	= str_replace( array( "[ORDER_NUM]", "[TRACKING_ID]" ), array( $orderId, $trackingId ), $subject );
			$content 	= str_replace( array( "[CUSTOMER_NAME]", "[EMAIL_ID]", "[ORDER_NUM]", "[CARRIER_NAME]", "[TRACKING_ID]", "[TRACKING_STATUS]", "[SHIPMENT_PROGRESS]" ), array( $name, $email, $orderId, $displayName, $trackingId, $trackingStatus, $progressTable ), $content );
			
			$response 	= wp_mail( $email, $subject, $content, $headers, $attachments );

		}
	}

	/**
	 * Change Order Status when Live Tracking Status becomes Delivered
	 * @param int $orderId Order Num
	 * @param string $carrierName Carrier Name
	 * @param object $trackingInfo Live Tracking Details
	 * @param string $toStatus Order Status
	**/
	public static function change_order_status( $orderId, $carrierName, $trackingInfo, $toStatus ) {

		if( !empty($trackingInfo) && is_array($trackingInfo) && !empty($toStatus) ) {

			$totalDelivered 	= 0;

			foreach ( $trackingInfo as $trackingData ) {

				$statusCode 	= $trackingData->api_live_status;
				$currentStatus 	= PH_Live_Tracking_Status_Mapper::get_live_tracking_status( $statusCode, $carrierName );

				if( $currentStatus == 'DELIVERED' ) {
					$totalDelivered++;
				}
			}

			// Change Order Status when all the available Tracking Numbers are Delivered
			if( (count($trackingInfo) == $totalDelivered) && !empty($toStatus) ) {
				
				$order 		= wc_get_order($orderId);
				$order->update_status($toStatus);
			}
		}
	}

}