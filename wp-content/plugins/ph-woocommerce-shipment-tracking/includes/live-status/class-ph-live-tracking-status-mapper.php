<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PH_Live_Tracking_Status_Mapper class.
 */
class PH_Live_Tracking_Status_Mapper {

	public static $dhlTrackingStatus = [

		'AD' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Agreed delivery'	],
		'AF' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Arrived facility'	],
		'AR' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Arrival in delivery facility'	],
		'BA' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Bad address'	],
		'BL' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Bond location'	],
		'BN' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Customer broker notified'	],
		'BR' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Broker release'	],
		'CA' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Closed on arrival'	],
		'CC' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Awaiting cnee collection'	],
		'CD' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Controllable clearance delay'	],
		'CI' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Facility check-in'	],
		'CM' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Customer moved'	],
		'CR' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Clearance release'	],
		'CS' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Closed shipment'	],
		'CU' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Confirm uplift'	],
		'DD' => [ 'status' => 'EXCEPTION_2', 		'statusDesc'  		=> 'Delivered damaged'	],
		'DF' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Depart facility'	],
		'DI' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Duty invoice'	],
		'DM' => [ 'status' => 'EXCEPTION_2', 		'statusDesc'  		=> 'Damaged'	],
		'DP' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Denied parties'	],
		'DS' => [ 'status' => 'EXCEPTION_2', 		'statusDesc'  		=> 'Destroyed / disposal'	],
		'ES' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Entry submitted'	],
		'FD' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Forward destination (DDs expected)'	],
		'HI' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Lodged into hic'	],
		'HN' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Handover'	],
		'HO' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Lodged out of held inventory control'	],
		'HP' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Held for payment'	],
		'IA' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Image available'	],
		'IC' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'In clearance processing'	],
		'IR' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Incorrect route'	],
		'LV' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Load vehicle'	],
		'MC' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Miscode'	],
		'MD' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Missed delivery cycle'	],
		'MS' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Mis-sort'	],
		'NA' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Not arrived'	],
		'ND' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Not delivered'	],
		'NH' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Not home'	],
		'OH' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'On hold'	],
		'OK' => [ 'status' => 'DELIVERED', 			'statusDesc'  		=> 'Delivery'	],
		'PD' => [ 'status' => 'DELIVERED', 			'statusDesc'  		=> 'Delivered'	],
		'PL' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Processed at location'	],
		'PU' => [ 'status' => 'INITIAL', 				'statusDesc'  		=> 'Shipment pick up'	],
		'PY' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Payment'	],
		'RD' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Refused delivery'	],
		'RR' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Response received'	],
		'RT' => [ 'status' => 'EXCEPTION_3', 		'statusDesc'  		=> 'Returned to consignor'	],
		'RW' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Weigh & dimension'	],
		'SA' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Shipment acceptance'	],
		'SC' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Service changed'	],
		'SI' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Shipment inspection'	],
		'SM' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Scheduled movement'	],
		'SS' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Shipment stopped'	],
		'ST' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Shipment intercept'	],
		'TD' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Transport delay'	],
		'TI' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Trace initiated'	],
		'TP' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Forwarded to 3rd party - no DDs'	],
		'TR' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Record of transfer'	],
		'TT' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Trace terminated'	],
		'UD' => [ 'status' => 'EXCEPTION_1', 		'statusDesc'  		=> 'Uncontrollable clearance delay'	],
		'UV' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'Unload vehicle'	],
		'WC' => [ 'status' => 'IN_TRANSIT', 		'statusDesc'  		=> 'With delivering courier'	],
		'DUMMY_PU' => [ 'status' => 'INITIAL', 	'statusDesc'  		=> 'Shipment information received'	],
	];

	public static $fedexTrackingStatus = [

		'AA' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At Airport'	],
		'AC' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At Canada Post facility'	],
		'AD' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At Delivery'	],
		'AF' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At FedEx Facility'	],
		'AP' => 	[ 'status' 	=> 'INITIAL', 				'statusDesc' 	=> 'At Pickup'	],
		'AR' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Arrived at'	],
		'AX' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At USPS facility'	],
		'CA' => 	[ 'status' 	=> 'EXCEPTION_3', 		'statusDesc' 	=> 'Shipment Cancelled'	],
		'CH' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Location Changed'	],
		'DD' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Delivery Delay'	],
		'DE' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Delivery Exception'	],
		'DL' => 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 	=> 'Delivered'	],
		'DP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Departed'	],
		'DR' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Vehicle furnished but not used'	],
		'DS' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Vehicle Dispatched'	],
		'DY' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Delay'	],
		'EA' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Enroute to Airport'	],
		'ED' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Enroute to Delivery'	],
		'EO' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Enroute to Origin Airport'	],
		'EP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Enroute to Pickup'	],
		'FD' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At FedEx Destination'	],
		'HL' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Hold at Location'	],
		'IT' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'In Transit'	],
		'IX' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'In transit (see Details)'	],
		'LO' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Left Origin'	],
		'OC' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Order Created'	],
		'OD' => 	[ 'status' 	=> 'OUT_FOR_DELIVERY','statusDesc' 	=> 'Out for Delivery'	],
		'OF' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At FedEx origin facility'	],
		'OX' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Shipment information sent to USPS'	],
		'PD' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Pickup Delay'	],
		'PF' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Plane in Flight'	],
		'PL' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Plane Landed'	],
		'PM' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'In Progress'	],
		'PU' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Picked Up'	],
		'PX' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Picked up (see Details)'	],
		'RR' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'CDO requested'	],
		'RM' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'CDO Modified'	],
		'RC' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'CDO Cancelled'	],
		'RS' => 	[ 'status' 	=> 'EXCEPTION_3', 		'statusDesc' 	=> 'Return to Shipper'	],
		'RP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Return label link emailed to return sender'	],
		'LP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Return label link cancelled by shipment originator'	],
		'RG' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Return label link expiring soon'	],
		'RD' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Return label link expired'	],
		'SE' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Shipment Exception'	],
		'SF' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'At Sort Facility'	],
		'SP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Split Status'	],
		'TR' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Transfer'	],
		'CC' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Cleared Customs'	],
		'CD' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Clearance Delay'	],
		'CP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Clearance in Progress'	],
		'EA' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Export Approved'	],
		'RC' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Recipient'	],
		'SH' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Shipper'	],
		'CU' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Customs'	],
		'BR' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Broker'	],
		'TP' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Transfer Partner'	],
	];

	public static $upsTrackingStatus = [

		'I' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'In Transit'	],
		'D' => 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 	=> 'Delivered'	],
		'X' => 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 	=> 'Exception'	],
		'P' => 	[ 'status' 	=> 'IN_TRANSIT', 			'statusDesc' 	=> 'Pickup'	],
		'M' => 	[ 'status' 	=> 'INITIAL', 				'statusDesc' 	=> 'Manifest Pickup'	],
	];

	public static $uspsTrackingStatus = [

		'MA' 	=> [ 'status' 			=> 	'INITIAL', 					'statusDesc' 	=> 'Manifest Acknowledgment'	],
		'3' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Accept or Pickup (by carrier)'	],
		'TM' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Truck manifest, provided as “Shipment Acceptance”'	],
		'4' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Refused'	],
		'5' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Undeliverable as Addressed'	],
		'6' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Forwarded'	],
		'7' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Arrival at Unit'	],
		'8' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Missent'	],
		'9' 	=> [ 'status' 			=> 	'EXCEPTION_3', 			'statusDesc' 	=> 'Return to Sender'	],
		'10' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Processed'	],
		'PA' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Passive Acceptance, provided as “Shipment Acceptance”'	],
		'11' 	=> [ 'status' 			=> 	'EXCEPTION_3', 			'statusDesc' 	=> 'Dead Letter'	],
		'14' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Arrival at Pickup Point'	],
		'15' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Mis-shipped'	],
		'16' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Available for Pickup'	],
		'17' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Picked Up By Agent'	],
		'19' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'DC eVS Arrive'	],
		'31' 	=> [ 'status' 			=> 	'EXCEPTION_3', 			'statusDesc' 	=> 'Return to Sender/ Not Picked Up'	],
		'41' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Received at Opening Unit* (Reserved for Open & Distribute)'	],
		'42' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'USPS Handoff to Shipping Partner'	],
		'21' 	=> [ 'status' 			=> 	'INITIAL', 					'statusDesc' 	=> 'No Such Number'	],
		'22' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Insufficient Address'	],
		'23' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Moved, Left No Address'	],
		'24' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Forward Expired'	],
		'25' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Addressee Unknown'	],
		'26' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Vacant'	],
		'27' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Unclaimed'	],
		'28' 	=> [ 'status' 			=> 	'EXCEPTION_2', 			'statusDesc' 	=> 'Deceased'	],
		'29' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Other'	],
		'43' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Picked Up'	],
		'44' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Customer Recall'	],
		'51' 	=> [ 'status' 			=> 	'EXCEPTION_1', 			'statusDesc' 	=> 'Business Closed'	],
		'80' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Picked Up by Shipping Partner'	],
		'81' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Arrived Shipping Partner Facility'	],
		'82' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Departed Shipping Partner Facility'	],
		'EF' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Departed Sort Facility'	],
		'SF' 	=> [ 'status' 			=> 	'IN_TRANSIT', 			'statusDesc' 	=> 'Departed Post Office'	],
		'01' 	=> [ 'status' 			=> 	'DELIVERED', 				'statusDesc' 	=> 'Delivered'	],
		'OF' 	=> [ 'status' 			=> 	'OUT_FOR_DELIVERY', 'statusDesc' 	=> 'Out For Delivery'	],
		'IN_TRANSIT' 	=> [ 'status' 		=> 	'IN_TRANSIT', 'statusDesc' 	=> 'In Transit'	],
	];

	public static $delhiveryTrackingStatus = [

		'UD' 	=> [ 'status' 		=> 'IN_TRANSIT', 	'statusDesc' 		=> 		'In Transit'	],
		'DL' 	=> [ 'status' 		=> 'DELIVERED', 	'statusDesc' 		=> 		'Delivered'	],
		'RT' 	=> [ 'status' 		=> 'RETURNED', 		'statusDesc' 		=> 		'Returned'	],
	];

	public static $australiaPostTrackingStatus = [
		
		'Created' 						=> 	[ 'status' =>	'INITIAL', 				'statusDesc' 	=> 'Created'	],
		'Sealed' 							=> 	[ 'status' =>	'INITIAL', 				'statusDesc' 	=> 'Sealed'	],
		'Initiated' 					=> 	[ 'status' =>	'INITIAL', 				'statusDesc' 	=> 'Initiated'	],
		'In transit' 					=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'In transit'	],
		'Possible delay' 			=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Possible delay'	],
		'Held by courier' 		=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Held by courier'	],
		'Awaiting collection'	=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Awaiting collection'	],
		'Delivered' 					=> 	[ 'status' =>	'DELIVERED', 			'statusDesc' 	=> 'Delivered'	],
		'Unsuccessful pickup'	=> 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Unsuccessful pickup'	],
		'Article damaged' 		=> 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Article damaged'	],
		'Cancelled' 					=> 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Cancelled'	],
		'Cannot be delivered' => 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Cannot be delivered'	],
		'At Delivery Depot' 	=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'At Delivery Depot'	],
		'Booked In' 					=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Booked In'	],
		'Confirmed' 					=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Confirmed'	],
		'Deleted' 						=> 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Deleted'	],
		'Delivered in Full' 	=> 	[ 'status' =>	'DELIVERED', 			'statusDesc' 	=> 'Delivered in Full'	],
		'Final Shortage' 			=> 	[ 'status' =>	'DELIVERED', 			'statusDesc' 	=> 'Final Shortage'	],
		'Incomplete' 					=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Incomplete'	],
		'Partial Pickup' 			=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Partial Pickup'	],
		'Partial Delivery' 		=> 	[ 'status' =>	'DELIVERED', 			'statusDesc' 	=> 'Partial Delivery'	],
		'Re-Consigned' 				=> 	[ 'status' =>	'EXCEPTION_1', 		'statusDesc' 	=> 'Re-Consigned'	],
		'To be Re-Delivered' 	=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'To be Re-Delivered'	],
		'Ready for Pickup' 		=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Ready for Pickup'	],
		'Unconfirmed' 				=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Unconfirmed'	],
		'Picked Up' 					=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Picked Up'	],
		'Freight Handling' 		=> 	[ 'status' =>	'IN_TRANSIT', 		'statusDesc' 	=> 'Freight Handling'	],
		'On Board for Delivery' 		=> 	[ 'status' =>	'OUT_FOR_DELIVERY', 'statusDesc' 	=> 'On Board for Delivery'	],
		'Unsuccessful Delivery' 		=> 	[ 'status' =>	'EXCEPTION_1', 			'statusDesc' 	=> 'Unsuccessful Delivery'	],
		'Track items for detailed delivery information' 	=> 	[ 'status' =>	'EXCEPTION_1', 	'statusDesc' 	=> 'Track items for detailed delivery information'	],
		'Shipping information received by Australia Post' => 	[ 'status' =>	'INITIAL', 			'statusDesc' 	=> 'Shipping information received by Australia Post'	],
	];

	public static $blueDartTrackingStatus = [

		'NF' 		=> 	[ 'status' =>	'INITIAL', 					'statusDesc' 			=> 'No-Info'	],
		'IT' 		=> 	[ 'status' =>	'IN_TRANSIT', 			'statusDesc' 			=> 'In Transit'	],
		'UD' 		=> 	[ 'status' =>	'IN_TRANSIT', 			'statusDesc' 			=> 'Undelivered'	],
		'OD' 		=> 	[ 'status' =>	'OUT_FOR_DELIVERY', 'statusDesc' 			=> 'OutForDelivery' ],
		'DL' 		=> 	[ 'status' =>	'DELIVERED', 				'statusDesc' 			=> 'Delivered' ],
		'RD' 		=> 	[ 'status' =>	'IN_TRANSIT', 			'statusDesc' 			=> 'Redirected'	],
		'RT' 		=> 	[ 'status' =>	'EXCEPTION_3', 			'statusDesc' 			=> 'Returned', ],
		'PU' 		=> 	[ 'status' =>	'INITIAL', 					'statusDesc' 			=> 'Pickup'	],
	];

	public static $canadaPostTrackingStatus = [
		'20'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'SIGNATURE'	],
		'100'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'102'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'104'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'105'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'106'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'107'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'113'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'114'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'115'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'116'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'117'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'118'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'120'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'121'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'122'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INDUCTION'	],
		'123'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INDUCTION'	],
		'124'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'125'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'127'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'130'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'136'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'140'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'150'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'152'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'153'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'154'	=> 	[ 'status' 	=> 'EXCEPTION_1', 			'statusDesc' 		=> 		'ATTEMPTED'	],
		'155'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'156'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'157'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'158'	=> 	[ 'status' 	=> 'OUT_FOR_DELIVERY', 	'statusDesc' 		=> 		'OUT' ],
		'159'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'160'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'161'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'162'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'163'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'167'	=> 	[ 'status' 	=> 'EXCEPTION_1', 			'statusDesc' 		=> 		'ATTEMPTED'	],
		'168'	=> 	[ 'status' 	=> 'EXCEPTION_1', 			'statusDesc' 		=> 		'ATTEMPTED'	],
		'169'	=> 	[ 'status' 	=> 'EXCEPTION_1', 			'statusDesc' 		=> 		'ATTEMPTED'	],
		'170'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'171'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'172'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'173'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'174'	=> 	[ 'status' 	=> 'OUT_FOR_DELIVERY', 	'statusDesc' 		=> 		'OUT' ],
		'175'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'178'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INDUCTION'	],
		'179'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'181'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'ATTEMPTED'	],
		'182'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'ATTEMPTED'	],
		'183'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'ATTEMPTED'	],
		'184'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'ATTEMPTED'	],
		'190'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'200'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'CONTAINER'	],
		'300'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'DISPATCH'	],
		'400'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INCOMING'	],
		'405'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'VEHICLE_INFO'	],
		'410'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'VEHICLE_INFO'	],
		'500'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'OUT'	],
		'700'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'NOT_CUST'	],
		'710'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'ARRIVAL_IN_CANADA'	],
		'800'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'TO_CUST'	],
		'810'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'815'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'INFO'	],
		'900'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'FROM_CUST'	],
		'910'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'FROM_CUST'	],
		'1000'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TRANSFER'	],
		'1100'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'DETENTION'	],
		'1300'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'1301'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'1302'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'1303'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'1407'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1408'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1409'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1410'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1411'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1412'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1414'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1415'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1416'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1417'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1418'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1419'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1420'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1421'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1422'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1423'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1424'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1425'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1426'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1427'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1428'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1429'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1430'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1431'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1432'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1433'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1434'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1435'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1436'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1437'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1438'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1441'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1442'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1443'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1450'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1479'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1480'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1481'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1482'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1483'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1484'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1487'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1488'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1490'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1491'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1492'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1493'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1494'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1495'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'1496'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1497'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1498'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1499'	=> 	[ 'status' 	=> 'DELIVERED', 			'statusDesc' 		=> 		'Delivered' ],
		'1701'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TO_RETAIL'	],
		'1703'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TO_RETAIL'	],
		'2000'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'SIGNATURE'	],
		'2101'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO_TID'	],
		'2300'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'2300'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'2407'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'2410'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'2411'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'2412'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'2414'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'2500'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TRANSFER_ITEM'	],
		'2501'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TRANSFER_ITEM'	],
		'2600'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'RTS_LABEL_PROC'	],
		'2601'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'RTC_LABEL_PROC'	],
		'2802'	=> 	[ 'status' 	=> 'EXCEPTION_1', 		'statusDesc' 		=> 		'ATTEMPTED'	],
		'3000'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'3001'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'3002'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INDUCTION'	],
		'4000'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4100'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4202'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO_TID'	],
		'4310'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO_TID'	],
		'4311'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO_TID'	],
		'4330'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4400'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4450'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4500'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4550'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4600'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4625'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4650'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4700'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'ATTEMPTED'	],
		'4900'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'4950'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'5201'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'FOR_REVIEW'	],
		'8901'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO_TID'	],
		'0175'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'0100'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'1704'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'TO_RETAIL'	],
		'0174'	=> 	[ 'status' 	=> 'OUT_FOR_DELIVERY','statusDesc' 		=> 		'OUT' ],
		'0170'	=> 	[ 'status' 	=> 'IN_TRANSIT',			'statusDesc' 		=> 	 'INFO'	],
		'CC_ARRIVED_CONS1'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'CC_INFO'	],
		'CC_ARRIVED_CONS2'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'CC_INFO'	],
		'CC_ARRIVED_CONS3'	=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'CC_INFO'	],
		'CC_DESPATCH1'			=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'CC_INFO_W_TID'	],
		'PR01_RECEIVED'			=> 	[ 'status' 	=> 'IN_TRANSIT',				'statusDesc' 		=> 	 'PR01_RECEIVED'	],
		'CC_RELEASED_CUSTOMS1'	=> 	[ 'status' 	=> 'IN_TRANSIT',		'statusDesc' 		=> 	 'CC_PIN_IN_CONT_TRAIL'	],
	];


	/**
	* Constructor
	**/
	public function __construct() {}

	/**
	* Get Tracking Status for Carrier Name
	* @param string $carrierName Carrier Name
	**/
	public static function get_carrier_based_tracking_status( $carrierName ) {

		$carrierData 	= '';

		switch ( $carrierName ) {

			case 'united-states-postal-service-usps':

			$carrierData 	= self::$uspsTrackingStatus;
			break;

			case 'canada-post':

			$carrierData 	= self::$canadaPostTrackingStatus;
			break;

			case 'ups':

			$carrierData 	= self::$upsTrackingStatus;
			break;

			case 'fedex':

			$carrierData 	= self::$fedexTrackingStatus;
			break;

			case 'blue-dart':

			$carrierData 	= self::$blueDartTrackingStatus;
			break;

			case 'australia-post':

			$carrierData 	= self::$australiaPostTrackingStatus;
			break;

			case 'delhivery':

			$carrierData 	= self::$delhiveryTrackingStatus;
			break;

			case 'dhl-express':

			$carrierData 	= self::$dhlTrackingStatus;
			break;

			default:

			$carrierData 	= '';
			break;
		}

		return $carrierData;
	}

	/**
	* Get Live Tracking Status
	* @param string $statusCode Status Code
	* @param string $carrierName Carrier Name
	**/
	public static function get_live_tracking_status( $statusCode, $carrierName ) {

		$status 		= '';
		$carrierData 	= self::get_carrier_based_tracking_status( $carrierName );

		if( !empty($carrierData) && !empty($statusCode) && array_key_exists($statusCode, $carrierData) ) {

			$status 	= $carrierData[$statusCode];

			return $status['status'];
		}

		return $status;
	}

}