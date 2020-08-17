<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WF_Shipment_Tracking_Importers' ) ) :

class WF_Shipment_Tracking_Importers {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_importers' ) );
		add_action( 'import_start', array( $this, 'post_importer_compatibility' ) );		
	}

	/**
	 * Add menu items
	 */
	public function register_importers() {
		register_importer( 'import_shipment_tracking_csv', __( 'Import Shipment Tracking (CSV)', 'woocommerce-shipment-tracking' ), __( 'Import <strong>Shipment Tracking</strong> to your store via a csv file.', 'woocommerce-shipment-tracking'), array( $this, 'shipment_tracking_importer' ) );
                register_importer( 'import_shipment_tracking_csv_cron', __( 'Import Shipment Tracking cron(CSV)', 'woocommerce-shipment-tracking' ), __( 'Import <strong>Shipment Tracking</strong> to your store via a csv file.', 'woocommerce-shipment-tracking'), array( $this, 'WF_ShipmentTracking_ImportCron::shipment_tracking_cron_importer' ) );
	}

	/**
	 * Add menu item
	 */
	public function shipment_tracking_importer() {
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require $class_wp_importer;
		}

		// includes
		require 'class-wf-shipment-tracking-importer.php';

		// Dispatch
		$importer = new WF_Shipment_Tracking_Importer();
		$importer->dispatch();
	}	
}

endif;

return new WF_Shipment_Tracking_Importers();
