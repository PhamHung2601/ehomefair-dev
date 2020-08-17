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
class WOO_Vou_WC_Booking {
	
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
	 * @since 2.1.3
	 */
	public function woo_vou_booking_product_type_options($options) {
	    $options['downloadable']['wrapper_class'] .= ' show_if_booking';
	    return $options;
	}

	/**
	 * Replace WC Booking's shortcodes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function woo_vou_pdf_template_replace_shortcodes( $woo_vou_details, $orderid, $item_key, $items, $voucodes, $productid ) {
			
		// Declare varible for booking shortcodes
		$booking_start_date = $booking_start_time = $booking_end_time = '';
		$person_html_array  = array();

		// Getting booking ids
		$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_key );
		if ( !empty($booking_ids[0]) ) {
			
			// Getting booking post and product
			$booking 		= new WC_Booking( $booking_ids[0] );
			$product_id 	= $booking->get_product_id();
			$product    	= $booking->get_product( $product_id );
			$booking_start 	= $booking->get_start();
			$booking_end	= $booking->get_end();
			
			// Getting start date and time
			$booking_start_date = ( !empty($booking_start) )? date( 'd.m.Y', $booking_start ) : '' ;
			$booking_start_time = ( !empty($booking_start) )? date( 'H:i', $booking_start ) : '' ;
			$booking_end_time 	= ( !empty($booking_end) )? date( 'H:i', $booking_end ) : '' ;
			
			// Getting the person
			$person_counts 		= $booking->get_person_counts();
			$person_types 		= $product ? $product->get_person_types() : array();
			
			if ( count( $person_counts ) > 0 || count( $person_types ) > 0 ) {
	
				foreach ( $person_counts as $person_id => $person_count ) {
					$person_type = null;

					try {
						// Getting the person object
						$person_type = new WC_Product_Booking_Person_Type( $person_id );
					} catch ( Exception $e ) {
						// This person type was deleted from the database.
						unset( $person_counts[ $person_id ] );
					}
	
					if ( $person_type ) {
						$person_html_array[] = '<b>'.$person_type->get_name().': '.$person_count.'</b>';
					}
				}		
			}
	
		}

		// Adding the booking shortcodes
		$woo_vou_details['booking_date'] 	= ( !empty( $booking_start_date ) ) ? $booking_start_date : '';
		$woo_vou_details['booking_time'] 	= ( !empty( $booking_start_time ) && !empty( $booking_end_time ) ) ? ( $booking_start_time . ' - ' . $booking_end_time ) : '' ;
		$woo_vou_details['booking_persons'] = ( !empty($person_html_array) )? (implode( '<br>', $person_html_array )): '';

		// Adding WC Vendor shop name shortcode
		$woo_vou_details['wc_vendor_shopname'] = !empty( $wc_vendor_shopname ) ? $wc_vendor_shopname : '';

		return $woo_vou_details;
	}

	/**
	 * Replace WC Booking's shortcodes in Preview
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function woo_vou_pdf_template_preview_replace_shortcodes( $woo_vou_details, $voucher_template_id ) {

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
	 * Adding proper hooks for the WC Booking compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.10
	 */
	public function add_hooks() {

        //Add downloadable option for Woocommerce-Booking plugin
        add_filter( 'product_type_options', array( $this, 'woo_vou_booking_product_type_options' ) );

        //Filter to replace booking shortcodes
        add_filter( 'woo_vou_pdf_template_replace_shortcodes', array( $this, 'woo_vou_pdf_template_replace_shortcodes' ), 10, 6 );

        //Filter to replace booking shortcodes in preview
        add_filter( 'woo_vou_pdf_template_preview_replace_shortcodes', array( $this, 'woo_vou_pdf_template_preview_replace_shortcodes' ), 10, 2 );
	}
}