<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Booking Compability Class
 * 
 * Handles WC Booking Compability
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.7.10
 */
class WOO_Vou_EML {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/**
	 * Dequeue Scripts which not needed.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function woo_vou_dequeue_scripts() {

        if ( function_exists( 'get_current_screen' ) ) {

	        // Get current screen
			$screen = get_current_screen();
			
			// If screen post type is of Voucher Template page
			if( isset( $screen->post_type ) && $screen->post_type == WOO_VOU_POST_TYPE ) {

				//Dequeue Scripts( For Enhanced Media Library plugin )
		   		wp_dequeue_script( 'wpuxss-eml-media-views-script' );
		   	}
		}
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the WC Booking compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function add_hooks() {

		//Action to dequeue scripts
		add_action( 'wp_print_scripts', array( $this, 'woo_vou_dequeue_scripts' ), 100 );
	}
}