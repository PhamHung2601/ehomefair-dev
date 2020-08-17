<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Yith Booking and Appointment Compability Class
 * 
 * Handles Yith Booking Compability
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 4.1.2
 */
class WOO_Vou_Yith_Booking {

	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/**
	 * Show Downloadable Option
	 * 
	 * Handle to show downloadable option for booking product type
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.2
	 */
	public function woo_vou_yith_booking_product_type_options($options) {
	    $options['downloadable']['wrapper_class'] .= ' show_if_booking';
	    return $options;
	}


	/**
	 * Replace Yith Booking's shortcodes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.2
	 */
	public function woo_vou_pdf_yith_template_replace_shortcodes( $woo_vou_details, $orderid, $item_key, $items, $voucodes, $productid ) {
			
		// Declare varible for booking shortcodes
		$booking_start_date = $booking_start_time = $booking_end_time = '';
		$person_html_array  = array();
		$yith_bk_helper = new YITH_WCBK_Booking_Helper();
		
		$booking_obj_id = $yith_bk_helper->get_bookings_by_order($orderid,$item_key);


		if ( !empty($booking_obj_id) ) {
			
			$booking_id = $booking_obj_id[0]->id;

			// Getting booking post and product
			$booking 		= new YITH_WCBK_Booking( $booking_id );
			$product_id 	= $booking->get_product_id();
			$product    	= $booking->get_product( $product_id );

			$booking_start 	= $booking->from;
			$booking_end	= $booking->to;
			
			// Getting start date and time
			$booking_start_date = ( !empty($booking_start) )? date( 'd.m.Y', $booking_start ) : '' ;
			$booking_start_time = ( !empty($booking_start) )? date( 'H:i', $booking_start ) : '' ;
			$booking_end_time 	= ( !empty($booking_end) )? date( 'H:i', $booking_end ) : '' ;
			
			// Getting the person
			$person_types 		= $booking->get_person_types_html();
			
			if ( !empty( $person_types ) ) {
				$person_html_array = $person_types;
			}
	
		}

		// Adding the booking shortcodes
		$woo_vou_details['booking_date'] 	= ( !empty( $booking_start_date ) ) ? $booking_start_date : '';
		$woo_vou_details['booking_time'] 	= ( !empty( $booking_start_time ) && !empty( $booking_end_time ) ) ? ( $booking_start_time . ' - ' . $booking_end_time ) : '' ;
		$woo_vou_details['booking_persons'] = ( !empty($person_html_array) )? $person_html_array: '';

		return $woo_vou_details;
	}

	/**
	 * Replace WC Booking's shortcodes in Preview
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.2
	 */
	public function woo_vou_pdf_yith_template_preview_replace_shortcodes( $woo_vou_details, $voucher_template_id ) {

		// Generate booking time
		$booking_date = mktime( date("H"), date("i"), date("s"), date("m"),   date("d")+3,   date("Y") );
		$woo_vou_details['booking_date'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y', $booking_date ) ); // Value for booking date
		$woo_vou_details['booking_time'] 	= $this->model->woo_vou_get_date_format( date('H:i', $booking_date ), true ); // Value for booking time
		$woo_vou_details['booking_persons'] = '<b>'.esc_html__( 'No. of persons = 5','woovoucher' ).'</b>'; // Value for booking persons

		return $woo_vou_details;
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the Yith Booking compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 4.1.2
	 */
	public function add_hooks() {

		//Add downloadable option for Yith-Booking plugin
        add_filter( 'product_type_options', array( $this, 'woo_vou_yith_booking_product_type_options' ) );

        //Filter to replace booking shortcodes
        add_filter( 'woo_vou_pdf_template_replace_shortcodes', array( $this, 'woo_vou_pdf_yith_template_replace_shortcodes' ), 10, 6 );

        //Filter to replace booking shortcodes in preview
        add_filter( 'woo_vou_pdf_template_preview_replace_shortcodes', array( $this, 'woo_vou_pdf_yith_template_preview_replace_shortcodes' ), 10, 2 );
	}
}