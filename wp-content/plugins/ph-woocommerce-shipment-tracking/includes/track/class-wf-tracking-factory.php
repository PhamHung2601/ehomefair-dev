<?php

/**
 * The is the factory which creates shipment tracking objects
 */
class PH_ShipmentTrackingFactory {
	public static function init() {
		PH_ShipmentTrackingFactory::wf_include_once( 'PH_ShipmentTrackingAbstract', 'class-wf-tracking-common.php' );
		PH_ShipmentTrackingFactory::wf_include_once( 'PH_ShipmentTrackingAbstract', 'class-wf-tracking-abstract.php' );
	}

    public static function create( $shipment_source_obj ) {

		if( (! empty($_GET['import']) && $_GET['import'] == 'import_shipment_tracking_csv' ) || defined('REST_REQUEST')) {
			PH_ShipmentTrackingFactory::wf_include_once( 'PH_ShipmentTrackingDefault', 'class-wf-tracking-default.php' );
			$tracking_obj = new PH_ShipmentTrackingDefault();
		}
		else{

			switch ( $shipment_source_obj->shipping_service ) {
				case '':
					$tracking_obj = null;
					break;
				case 'united-states-postal-service-usps':
					PH_ShipmentTrackingFactory::wf_include_once( 'WfTrackingUSPS', 'usps/class-wf-tracking-usps.php' );
					$tracking_obj = new WfTrackingUSPS();
					break;
				case 'canada-post':
					PH_ShipmentTrackingFactory::wf_include_once( 'WfTrackingCanadaPost', 'canadapost/class-wf-tracking-canadapost.php' );
					$tracking_obj = new WfTrackingCanadaPost();
					break;
				case 'ups':
					PH_ShipmentTrackingFactory::wf_include_once( 'WfTrackingUPS', 'ups/class-wf-tracking-ups.php' );
					$tracking_obj = new WfTrackingUPS();
					break;
				case 'fedex':
					PH_ShipmentTrackingFactory::wf_include_once( 'WfTrackingFedEx', 'fedex/class-wf-tracking-fedex.php' );
					$tracking_obj = new WfTrackingFedEx();
					break;
				case 'blue-dart':
					PH_ShipmentTrackingFactory::wf_include_once( 'PHTrackingBlueDart', 'bluedart/class-ph-tracking-bluedart.php' );
					$tracking_obj = new PHTrackingBlueDart();
					break;
				case 'australia-post':
					PH_ShipmentTrackingFactory::wf_include_once( 'PHTrackingAustraliaPost', 'australiapost/class-ph-tracking-australiapost.php' );
					$tracking_obj = new PHTrackingAustraliaPost();
					break;
				case 'delhivery':
					PH_ShipmentTrackingFactory::wf_include_once( 'PHTrackingDelhivery', 'delhivery/class-ph-tracking-delhivery.php' );
					$tracking_obj = new PHTrackingDelhivery();
					break;
				case 'dhl-express':
					PH_ShipmentTrackingFactory::wf_include_once( 'PHTrackingDHLExpress', 'dhlexpress/class-ph-tracking-dhlexpress.php' );
					$tracking_obj = new PHTrackingDHLExpress();
					break;
				case 'aramex':
					PH_ShipmentTrackingFactory::wf_include_once( 'PHTrackingAramex', 'aramex/class-ph-tracking-aramex.php' );
					$tracking_obj = new PHTrackingAramex();
					break;
				default:
					PH_ShipmentTrackingFactory::wf_include_once( 'PH_ShipmentTrackingDefault', 'class-wf-tracking-default.php' );
					$tracking_obj = new PH_ShipmentTrackingDefault();
					break;
			}
		}

		if( null != $tracking_obj ) {
			$tracking_obj->init ( $shipment_source_obj );
		}

        return $tracking_obj;
    }

	private static function wf_include_once( $class_name, $file_name ) {
		if ( ! class_exists( $class_name ) ) {
			include_once ( $file_name );
		}
	}
}

?>