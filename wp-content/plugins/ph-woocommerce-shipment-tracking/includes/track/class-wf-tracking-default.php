<?php

/**
 * Default
 */
class PH_ShipmentTrackingDefault extends PH_ShipmentTrackingAbstract {
	protected function get_api_tracking_status( $shipment_id, $api_uri ) { return new ApiTracking(); }
}