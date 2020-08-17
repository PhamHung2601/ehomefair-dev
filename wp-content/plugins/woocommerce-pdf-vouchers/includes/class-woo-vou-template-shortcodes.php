<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcodes Class
 * 
 * Handles shortcodes functionality of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Template_Shortcodes {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model	= $woo_vou_model;
	}
	
	/**
	 * Download PDF form frontend OR backend - Replace shortcodes with value
	 * 
	 * Adding All Shortcodes with value in voucher template html
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_pdf_template_replace_shortcodes( $voucher_template_html, $orderid, $item_key, $items, $voucodes, $productid, $woo_vou_details ) {
		
		// Creating order object for order id
		$woo_order = new WC_Order( $orderid );
		
		// Getting product name
		$woo_product_details = $this->model->woo_vou_get_product_details( $orderid, $items );		
		// get product
		$product = wc_get_product( $productid );				

		// Order id
		$woo_vou_details['orderid'] = $orderid;
		$payment_method_title = $this->model->woo_vou_get_payment_method_from_order($woo_order);
		
		// get payment method title
		$woo_vou_details['payment_method']	= $payment_method_title;

		// Get billing details
		$woo_vou_billing_details = $this->model->woo_vou_get_buyer_information($orderid);
		$billing_email = $woo_vou_billing_details['email'];
		$billing_first_name = $woo_vou_billing_details['first_name'];
		$billing_last_name = $woo_vou_billing_details['last_name'];
		$billing_phone = $woo_vou_billing_details['phone'];
		$billing_postcode = $woo_vou_billing_details['postcode'];
        $billing_city = $woo_vou_billing_details['city'];
        $billing_address = $woo_vou_billing_details['address_1'] . ' ' . $woo_vou_billing_details['address_2'];

		// Getting Buyer details
		$woo_vou_details['buyeremail'] 	= $billing_email;		
		$buyer_fname 	= $billing_first_name;
		$buyer_lname 	= $billing_last_name;
		$woo_vou_details['buyername'] = $buyer_fname .' '. $buyer_lname;			
		$woo_vou_details['buyerphone'] = $billing_phone;

		// Get date format from global setting
		$date_format = get_option( 'date_format' );

		// Get recipient data
		$recipient_data	= $this->model->woo_vou_get_recipient_data_using_item_key( $item_key );
		$woo_vou_details['recipientname']		= isset( $recipient_data['recipient_name'] ) ? $recipient_data['recipient_name'] 	: '';
		$woo_vou_details['recipientphone']		= isset( $recipient_data['recipient_phone'] ) ? $recipient_data['recipient_phone'] 	: '';
		$woo_vou_details['recipientemail']		= isset( $recipient_data['recipient_email'] ) ? $recipient_data['recipient_email'] 	: '';
		$woo_vou_details['recipientmessage']	= isset( $recipient_data['recipient_message'] ) ? $recipient_data['recipient_message'] 	: '';
		$woo_vou_details['recipientgiftdate']   = isset( $recipient_data['recipient_giftdate'] ) ? date( $date_format, strtotime( $recipient_data['recipient_giftdate'] ) ) : '';

		// Getting Billing details
        $woo_vou_details['billing_postcode'] = $billing_postcode;
        $woo_vou_details['billing_city'] = $billing_city;	
        $woo_vou_details['billing_address'] = $billing_address;

        //Filter which allow third party to modify data
        $woo_vou_details = apply_filters( 'woo_vou_pdf_template_replace_shortcodes', $woo_vou_details, $orderid, $item_key, $items, $voucodes, $productid );

		
		$voucher_template_html = woo_vou_replace_all_shortcodes_with_value( $voucher_template_html, $woo_vou_details );
	 	
	  	return $voucher_template_html;
	}
	
	/**
	 * Preview PDF - Replace shortcodes with value
	 * 
	 * Adding All Shortcodes with value in voucher template html
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_pdf_template_preview_replace_shortcodes( $voucher_template_html, $voucher_template_id ) {				
		
		global $pdf_voucodes, $woo_vou_model;
		
		$model = $woo_vou_model;
		
		// Check if relative path is enabled
		$vou_relative_path_option	= get_option('vou_enable_relative_path');
		$woo_vou_img_path			= !empty( $vou_relative_path_option ) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL;
		
		$woo_vou_details = array();
		
		// site url
		$woo_vou_details['siteurl'] = 'www.bebe.com';
		$currency_symb = get_woocommerce_currency_symbol();
		
		// site logo
		$vousitelogohtml = '';
		$vou_site_url = get_option( 'vou_site_logo' );
		if( !empty( $vou_site_url ) ) {
			if( !empty( $vou_relative_path_option ) && $vou_relative_path_option == 'yes' ) {
				
				$vou_site_attachment_id = $model->woo_vou_get_attachment_id_from_url( $vou_site_url ); 			// Get attachment _id from attachment_url
				$vousitelogohtml = '<img src="' . esc_url(get_attached_file( $vou_site_attachment_id )) . '" alt="" />'; // Get relative path and append in image tag
			} else {
				$vousitelogohtml = '<img src="' . esc_url($vou_site_url) . '" alt="" />';
			}
		}
		$woo_vou_details['sitelogo'] = $vousitelogohtml;
		
		// vendor's logo
		$vou_url = get_option('vou_vendor_default_logo');
		$vou_url = !empty( $vou_url ) ? $vou_url : $woo_vou_img_path . '/vendor-logo.png';
		$voulogohtml = '<img src="' . esc_url($vou_url) . '" alt="" />';
		$woo_vou_details['vendorlogo'] = $voulogohtml;
		
		// Vendor address
		$vendor_address = '<table><tr><td>'.esc_html__( 'Infiniti Mall Malad', 'woovoucher' ) . "</td></tr>";
		$vendor_address .= "<tr><td>" . esc_html__( 'GF 9 & 10, Link Road, Mindspace, Malad West', 'woovoucher' ) . "</td></tr>";
		$vendor_address .= "<tr><td>" . esc_html__( 'Mumbai, Maharashtra 400064', 'woovoucher' ) . "</td></tr></table>";
		$woo_vou_details['vendoraddress'] = $vendor_address;
		
		// Vendor Email
		$vendor_email 	= 'vendor_email@gmail.com';
		$woo_vou_details['vendoremail'] = nl2br( $vendor_email );
		
		// next month
		$nextmonth = mktime( date("H"),  date("i"), date("s"), date("m")+1,   date("d"),   date("Y") );
		$woo_vou_details['expiredate'] 		= $this->model->woo_vou_get_date_format( date('d-m-Y', $nextmonth ) );
		$woo_vou_details['expiredatetime'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y H:i:s', $nextmonth ), true );
		
		// previous month
		$previousmonth = mktime( date("H"), date("i"), date("s"), date("m")-1,   date("d"),   date("Y") );
		$woo_vou_details['startdate'] 		= $this->model->woo_vou_get_date_format( date('d-m-Y', $previousmonth ) );
		$woo_vou_details['startdatetime'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y H:i:s', $previousmonth ), true );
		
		// Redeem instruction
		$redeem_instruction = esc_html__( 'Redeem Instructions:', 'woovoucher' );
		$redeem_instruction .= esc_html__( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.', 'woovoucher' );
		$woo_vou_details['redeem'] = $redeem_instruction;
		
		// vendor locations
		$locations = '<strong>' . esc_html__( 'DELHI:', 'woovoucher' ) . '</strong> ' . esc_html__( 'Dlf Promenade Mall & Pacific Mall', 'woovoucher' );
		$locations .= "\n".'<strong>' . esc_html__( 'MUMBAI:', 'woovoucher' ) . '</strong> ' . esc_html__( 'Infiniti Mall, Malad & Phoenix MarketCity', 'woovoucher' );
		$locations .= "\n".'<strong>' . esc_html__( 'BANGALORE:', 'woovoucher' ) . '</strong> ' . esc_html__( 'Phoenix MarketCity Mall', 'woovoucher' );
		$locations .= "\n".'<strong>' . esc_html__( 'PUNE:', 'woovoucher' ) . '</strong> ' . esc_html__( 'Phoenix MarketCity Mall', 'woovoucher' );
		$woo_vou_details['location'] = nl2br($locations);
		
		// buyer information
		$woo_vou_details['buyername'] 	= esc_html__('WpWeb', 'woovoucher');
		$woo_vou_details['buyeremail'] = 'web101@gmail.com';
		$woo_vou_details['buyerphone'] = esc_html__( '9999999999','woovoucher' );

		// billing information
        $woo_vou_details['billing_postcode'] = esc_html__( '110070','woovoucher' );
        $woo_vou_details['billing_city'] = esc_html__( 'Delhi','woovoucher' );
        $woo_vou_details['billing_address'] = esc_html__( 'DLF Promenade, Plot No 3, Nelson Mandela Road','woovoucher' );

		// order & product related information
		$woo_vou_details['orderid'] = '101';
		$woo_vou_details['orderdate'] = date("d-m-Y");
		
		$woo_vou_details['productname']			= esc_html__('Test Product', 'woovoucher');
		$woo_vou_details['producttitle']		= esc_html__('Test Product', 'woovoucher');
		$woo_vou_details['variationname']		= esc_html__('Test Variation', 'woovoucher');
		$woo_vou_details['variationdesc']		= esc_html__('Test Variation Description', 'woovoucher');
		$woo_vou_details['productprice']		= $currency_symb.number_format('10', 2);
		$woo_vou_details['regularprice']		= $currency_symb.number_format('15', 2);
		$woo_vou_details['discounted_amount']  	= $currency_symb.number_format('5', 2);
		$woo_vou_details['payment_method']		= 'Test Payment Method';
		$woo_vou_details['quantity']			= 1;
		$woo_vou_details['sku']    				= 'WooSKU';
		$woo_vou_details['productshortdesc']	= 'Product Short Description';	
		$woo_vou_details['productfulldesc']		= 'Product Full Description';
		
		// Voucher related information
		$recipient_gift_date = mktime( date("H"),  date("i"), date("s"), date("m"),   date("d")+7,   date("Y") );
		$codes 			= esc_html__( '[The voucher code will be inserted automatically here]', 'woovoucher' );
		$pdf_voucodes	= $codes;
		$woo_vou_details['codes'] 				= $codes;
		$woo_vou_details['recipientname']		= 'Test Name';
		$woo_vou_details['recipientemail']		= 'recipient@example.com';
		$woo_vou_details['recipientmessage']	= 'Test message';
		$woo_vou_details['recipientphone']		= '999999999';
		$woo_vou_details['recipientgiftdate'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y', $recipient_gift_date ) );;

        //Filter which allow third party to modify data
        $woo_vou_details = apply_filters( 'woo_vou_pdf_template_preview_replace_shortcodes', $woo_vou_details, $voucher_template_id );

		// Assign default value for shop name
		$woo_vou_details['wc_vendor_shopname'] = apply_filters( 'woo_vou_vendor_shopname_pdf_preview_shortcode', '' );

		
		$voucher_template_html = woo_vou_replace_all_shortcodes_with_value( $voucher_template_html, $woo_vou_details );				
		  
	  	return $voucher_template_html;
	}

	public function woo_vou_pdf_preview_template_replace_shortcodes( $voucher_template_inner_html, $voucodes, $productid, $woo_vou_details ){

		global $pdf_voucodes, $woo_vou_model;
		
		$model = $woo_vou_model;

		// Get date format from global setting
		$date_format = get_option( 'date_format' );

		// Check if relative path is enabled
		$vou_relative_path_option	= get_option('vou_enable_relative_path');
		$woo_vou_img_path			= !empty( $vou_relative_path_option ) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL;
		
		// buyer information
		$woo_vou_details['buyername'] 	= esc_html__( '{buyername} - This will be replaced with original value after placing the order.', 'woovoucher' );
		$woo_vou_details['buyeremail'] 	= esc_html__( '{buyeremail} - This will be replaced with original value after placing the order.', 'woovoucher' );
		$woo_vou_details['buyerphone'] 	= esc_html__( '{buyerphone} - This will be replaced with original value after placing the order.', 'woovoucher' );

		// billing information
        $woo_vou_details['billing_postcode'] 	= esc_html__( '{billing_postcode} - This will be replaced with original value after placing the order.', 'woovoucher' );
        $woo_vou_details['billing_city'] 		= esc_html__( '{billing_city} - This will be replaced with original value after placing the order.', 'woovoucher' );
        $woo_vou_details['billing_address'] 	= esc_html__( '{billing_address} - This will be replaced with original value after placing the order.', 'woovoucher' );

		// order & product related information
		$woo_vou_details['orderid'] 	= esc_html__( '{orderid} - This will be replaced with original value after placing the order.', 'woovoucher' );
		$woo_vou_details['orderdate'] 	= esc_html__( '{orderdate} - This will be replaced with original value after placing the order.', 'woovoucher' );
		
		$woo_vou_details['payment_method']	= esc_html__( '{payment_method} - This will be replaced with original value after placing the order.', 'woovoucher' );

		$voucher_template_inner_html = woo_vou_replace_all_shortcodes_with_value( $voucher_template_inner_html, $woo_vou_details );				
		  
	  	return $voucher_template_inner_html;
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the shortcodes.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		// Add filter to replace all voucher template shortcodes
		add_filter( 'woo_vou_pdf_template_inner_html', array( $this, 'woo_vou_pdf_template_replace_shortcodes' ), 10, 7 );

		// Add filter to replace all voucher template shortcodes with suitable values
		add_filter( 'woo_vou_pdf_preview_template_inner_html', array( $this, 'woo_vou_pdf_preview_template_replace_shortcodes' ), 10, 4 );
		
		// Add filter to replace all voucher template shortcodes in preview pdf
		add_filter( 'woo_vou_pdf_template_preview_html', array( $this, 'woo_vou_pdf_template_preview_replace_shortcodes' ), 10, 2 );		
	}
}