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
class WOO_Vou_Shortcodes {
	
	public $model;
	function __construct(){
		
		global $woo_vou_model;
		$this->model	= $woo_vou_model;
	}
	
	/**
	 * Voucher Code Title Container
	 * 
	 * Handles to display voucher code title content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0 
	 */
	public function woo_vou_code_title_container( $atts, $content ) {
		
		$html = $voucher_codes_html = '';
		$codes = array();
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 		=> '',
									 		'color' 		=> '#000000',
									 		'fontsize' 		=> '10',
									 		'textalign' 	=> 'left',
								 		), $atts ) );
		
		$bgcolor_css = $color_css = $textalign_css = $fontsize_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		if( !empty( $textalign ) ) {
			$textalign_css = 'text-align: ' . $textalign . ';';
		}
		if( !empty( $fontsize ) ) {
			$fontsize_css = 'font-size: ' . $fontsize . 'pt;';
		}
		
		if( !empty( $content ) && trim( $content ) != '' ) {
			
			$html .= '<table class="woo_vou_textblock" style="padding: 0px 5px; ' . $textalign_css . $bgcolor_css . $color_css . $fontsize_css . '">
						<tr>
							<td>
								' . wpautop( $content ) . '
							</td>
						</tr>
					</table>';
		}
		
		return apply_filters( 'woo_vou_code_title_container_shortcode', $html, $atts, $content );
	}
	
	/**
	 * Voucher Code Container
	 * 
	 * Handles to display voucher code content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_code_container( $atts, $content ) {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		$html	 = $voucher_codes_html = '';
		$codes	 = array();
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 		=> '',
									 		'color' 		=> '#000000',
									 		'fontsize' 		=> '10',
									 		'textalign' 	=> 'left',
									 		'codeborder'	=> '',
									 		'codetextalign'	=> 'left',
									 		'codecolumn'	=> '1',
								 		), $atts ) );
	 	
		$codeborder_attr = $codetextalign_css = '';
		if( !empty( $codeborder ) ) {
			$codeborder_attr .= 'border="' . $codeborder . '"';
		}
		if( !empty( $codetextalign ) ) {
			$codetextalign_css .= 'text-align: ' . $codetextalign . ';';
		}
		
		return apply_filters( 'woo_vou_code_container_shortcode', '<table width="100%" ' . $codeborder_attr . 'style="padding: 5px; ' . $codetextalign_css . '">
					<tr>
						<td>
							' . wpautop($content) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Voucher Redeem Container
	 * 
	 * Handles to display voucher redeem instructions
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_redeem_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_redeem_container_shortcode', '<table class="woo_vou_messagebox" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Voucher Site Logo Container
	 * 
	 * Handles to display voucher site logo container
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_site_logo_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
		 		), $atts ) );
		 
		 return apply_filters( 'woo_vou_site_logo_container_shortcode', '<table class="woo_vou_sitelogobox" style="text-align: center">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Voucher Logo Container
	 * 
	 * Handles to display voucher logo container
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_logo_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(	
		 		), $atts ) );
		 
		 return apply_filters( 'woo_vou_logo_container_shortcode', '<table class="woo_vou_logobox" style="text-align: center">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Voucher Expire Date Container
	 * 
	 * Handles to display voucher expire date content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_expire_date_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_expire_date_container_shortcode', '<table class="woo_vou_expireblock" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Vendor's Address Container
	 * 
	 * Handles to display vendor's address content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0 
	 */
	public function woo_vou_vendor_address_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_vendor_address_container_shortcode', '<table class="woo_vou_venaddrblock" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Website URL Container
	 * 
	 * Handles to display website URL content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_siteurl_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_siteurl_container_shortcode', '<table class="woo_vou_siteurlblock" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Voucher Locations Container
	 * 
	 * Handles to display voucher locations content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0 
	 */
	public function woo_vou_location_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_location_container_shortcode', '<table class="woo_vou_locblock" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Custom Container
	 * 
	 * Handles to display custom content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_custom_container( $atts, $content ) {
		
		$content = str_replace( '<p></p>', '', $content );
		
		extract( shortcode_atts( array(	
									 		'bgcolor' 	=> ''
								 		), $atts ) );
		
		$bgcolor_css = '';
		if( !empty( $bgcolor ) ) {
			$bgcolor_css = 'background-color: ' . $bgcolor . ';';
		}
		
		return apply_filters( 'woo_vou_custom_container_shortcode', '<table class="woo_vou_customblock" style="padding: 0px 5px; ' . $bgcolor_css . '">
					<tr>
						<td>
							' . wpautop( $content ) . '
						</td>
					</tr>
				</table>', $atts, $content );
	}
	
	/**
	 * Check Voucher Code
	 * 
	 * Handles to display check voucher code
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_code( $attr, $content ) {
		
		ob_start();

		// Get "Check Voucher Code for guest users" option
	    $vou_enable_guest_user_check_voucher_code = get_option('vou_enable_guest_user_check_voucher_code');

		ob_start();
		if ( is_user_logged_in() || ($vou_enable_guest_user_check_voucher_code == "yes") ) { // check is user loged in
			
			do_action( 'woo_vou_check_code_content' );
			
		} else {
			
			esc_html_e( 'You need to be logged in to your account to see check voucher code.', 'woovoucher' );
		}
		$content .= ob_get_clean();
		
		return apply_filters( 'woo_vou_check_code_shortcode', $content, $attr );
	}
	
	/**
	 * Voucher Code Shortcode
	 * Handles to replace the voucher code in purchase note
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	function woo_vou_codes_replace( $atts, $content ) {
		
		global $woo_vou_order_item, $woo_vou_item_id;
		
		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		// If order item is not empty
		if( !empty( $woo_vou_order_item ) ) {
			$is_only_receipient_access = get_option('vou_allow_recipient_to_get_voucher_info');

			//Get voucher codes
			$codes	= wc_get_order_item_meta( $woo_vou_item_id, $prefix.'codes' );
			
			if( !empty( $is_only_receipient_access ) && $is_only_receipient_access == 'yes' ){
				$recipient_email = wc_get_order_item_meta( $woo_vou_item_id, $prefix.'recipient_email' );
				if( !empty( $recipient_email ) ){
					$codes = '';	
				}
			}

			$content .= $codes;
		}
		return apply_filters( 'woo_vou_codes_replace_shortcode', $content, $atts );
	}
	
	/**
	 * Voucher Redeem Instruction Shortcode
	 * Handles to replace the voucher redeem instruction in purchase note
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	function woo_vou_redeem_instruction_replace( $atts, $content ) {
		
		global $woo_vou_order_item;

		$order_id		= $this->model->woo_vou_get_orderid_for_page(); // Getting order id
		$allorderdata	= $this->model->woo_vou_get_all_ordered_data( $order_id );
		
		// If order item and order data is not empty
		if( !empty($woo_vou_order_item) && !empty($allorderdata) ) {
			
			$download_id	= $woo_vou_order_item['product_id'];
			
			// Get all voucher details from order meta
			$allvoucherdata	= isset( $allorderdata[$download_id] ) ? $allorderdata[$download_id] : array();
			
			$voucherdata_redeem = !empty($allvoucherdata['redeem']) ? nl2br($allvoucherdata['redeem']) : '';
			
			$content .= $voucherdata_redeem;
		}
		
		return apply_filters( 'woo_vou_redeem_instruction_replace_shortcode', $content, $atts );
	}
	
	/**
	 * Handle QRCode
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.6
	 */
	public function woo_vou_qrcode_code( $atts, $content ) {

		global $pdf_template_id, $pdf_voucodes;

		$arguments = shortcode_atts( array(
						 		'qrcode_width'	  		=> '',
						 		'qrcode_height'	  		=> '',
						 		'qrcode_color'	  		=> '',
						 		'qrcode_symbol_type'	=> 'QRCODE,H',
						 		'qrcode_border'	  		=> '',
						 		'qrcode_response' 		=> ''
					 		), $atts );

		//add content to qrcodes
		$arguments['content']	= $content;
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//get pdf voucher template id
		$voucher_template_id	= ( !empty( $_GET['woo_vou_pdf_action'] ) && $_GET['woo_vou_pdf_action'] = 'preview' && !empty( $_GET['voucher_id'] ) ) ? $_GET['voucher_id'] : $pdf_template_id;

		$content = woo_vou_qrcode_html( $pdf_template_id, $pdf_voucodes, $arguments );

		return apply_filters( 'woo_vou_qrcode_code', $content, $atts );
	}
	
	/**
	 * Handle QRCodes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.6
	 */
	public function woo_vou_qrcodes_code( $atts, $content ) {

		global $pdf_template_id, $pdf_voucodes;

		$arguments = shortcode_atts( array(
						 		'qrcode_width'    		=> '',
						 		'qrcode_height'   		=> '',
						 		'qrcode_color'	  		=> '',
						 		'qrcode_symbol_type'	=> 'QRCODE,H',
						 		'qrcode_type'     		=> 'vertical',
						 		'qrcode_border'	  		=> '',
						 		'qrcode_response' 		=> ''
					 		), $atts );

		//add content to qrcodess
		$arguments['content']	= $content;
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//get pdf voucher template id
		$voucher_template_id	= ( !empty( $_GET['woo_vou_pdf_action'] ) && $_GET['woo_vou_pdf_action'] = 'preview' && !empty( $_GET['voucher_id'] ) ) ? $_GET['voucher_id'] : $pdf_template_id;

		$content = woo_vou_qrcode_html( $pdf_template_id, $pdf_voucodes, $arguments );

		return apply_filters( 'woo_vou_qrcodes_code', $content, $atts );
	}
	
	/**
	 * Handle Barcode
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.6
	 */
	public function woo_vou_barcode_code( $atts, $content ) {

		global $pdf_template_id, $pdf_voucodes;

		$arguments = shortcode_atts( array(
						 		'barcode_width'  => '',
						 		'barcode_height' => '',
						 		'barcode_color'  => '',
						 		'barcode_type'  => 'C128',
						 		'barcode_border' => ''
					 		), $atts );

		//add content to barcodes
		$arguments['content']	= $content;
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//get pdf voucher template id
		$voucher_template_id	= ( !empty( $_GET['woo_vou_pdf_action'] ) && $_GET['woo_vou_pdf_action'] = 'preview' && !empty( $_GET['voucher_id'] ) ) ? $_GET['voucher_id'] : $pdf_template_id;

		$content = woo_vou_barcode_html( $pdf_template_id, $pdf_voucodes, $arguments );

		return apply_filters( 'woo_vou_barcode_code', $content, $atts );
	}

	/**
	 * Handle Barcodes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.6
	 */
	public function woo_vou_barcodes_code( $atts, $content ) {

		global $pdf_template_id, $pdf_voucodes;

		$arguments = shortcode_atts( array(
						 		'barcode_width'  	=> '',
						 		'barcode_height' 	=> '',
						 		'barcode_color'  	=> '',
						 		'barcode_type'   	=> 'C128',
						 		'barcode_disp_type' => 'vertical',
						 		'barcode_border' 	=> ''
					 		), $atts );

		//add content to barcodes
		$arguments['content']	= $content;
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//get pdf voucher template id
		$voucher_template_id	= ( !empty( $_GET['woo_vou_pdf_action'] ) && $_GET['woo_vou_pdf_action'] = 'preview' && !empty( $_GET['voucher_id'] ) ) ? $_GET['voucher_id'] : $pdf_template_id;

		$content = woo_vou_barcode_html( $pdf_template_id, $pdf_voucodes, $arguments );

		return apply_filters( 'woo_vou_barcode_code', $content, $atts );
	}

	/**
	 * Display used Voucher Code
	 * 
	 * Handles to display used voucher code on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	public function woo_vou_used_voucher_codes_code( $atts, $content ) {
		
		ob_start();

	    if(!empty($_GET['vou_code'])) {
	
	        do_action( 'woo_vou_code_details_data' ); // do_action to add content through add_action
	    } else { 
	
	        do_action( 'woo_vou_used_voucher_codes' ); // do_action to add content through add_action
	    }
	
	    $content .= ob_get_clean();
	
	    return apply_filters( 'woo_vou_used_voucher_codes_shortcode_content', $content, $atts );
	}

	/**
	 * Display purchased Voucher Code
	 * 
	 * Handles to display purchased voucher code on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.7
	 */
	public function woo_vou_purchased_voucher_codes_code( $atts, $content ) {

	    ob_start();

	    if(!empty($_GET['vou_code'])) {
	
	        do_action( 'woo_vou_code_details_data' ); // do_action to add content through add_action
	    } else {
	
	        do_action( 'woo_vou_purchased_voucher_codes' ); // do_action to add content through add_action
	    }
	
	    $content .= ob_get_clean();
	
	    return apply_filters( 'woo_vou_purchased_voucher_codes_shortcode_content', $content, $atts );
	}

	/**
	 * Display unused Voucher Code
	 * 
	 * Handles to display unused voucher code on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function woo_vou_unused_voucher_codes_code( $atts, $content ) {
		
		ob_start();

	    if(!empty($_GET['vou_code'])) {
	
	        do_action( 'woo_vou_code_details_data' ); // do_action to add content through add_action
	    } else {
		
			do_action( 'woo_vou_unused_voucher_codes' ); // do_action to add content through add_action
		}
		
		$content .= ob_get_clean();
		
		return apply_filters( 'woo_vou_unused_voucher_codes_shortcode_content', $content, $atts );
	}

	/**
	 * Display product image on PDF
	 * 
	 * Handles to display Product Image on PDF
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function woo_vou_productimage_image( $atts, $content ) {

		// Get global variables
		global $pdf_template_id, $vou_product_id;

		// Get arguments from shortcode
		$arguments = shortcode_atts( array(
						 		'product_image_width'	  => '',
						 		'product_image_height'	  => ''
					 		), $atts );

		$arguments['content']	= $content; // Add content to product image array argments
		$content = woo_vou_productimage_html( $pdf_template_id, $vou_product_id, $arguments ); // Get content for product image

		return apply_filters( 'woo_vou_productimage_image', $content, $atts ); // Return data
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
		
		add_shortcode( 'woo_vou_code_title', array( $this, 'woo_vou_code_title_container' ) ); // for voucher code title
		add_shortcode( 'woo_vou_code', array( $this, 'woo_vou_code_container' ) ); // for voucher code
		add_shortcode( 'woo_vou_redeem', array( $this, 'woo_vou_redeem_container' ) ); //for redeem instruction
		add_shortcode( 'woo_vou_site_logo', array( $this, 'woo_vou_site_logo_container' ) ); //for voucher site logo
		add_shortcode( 'woo_vou_logo', array( $this, 'woo_vou_logo_container' ) ); //for voucher logo
		add_shortcode( 'woo_vou_expire_date', array( $this, 'woo_vou_expire_date_container' ) ); //for voucher expire date
		add_shortcode( 'woo_vou_vendor_address', array( $this, 'woo_vou_vendor_address_container' ) ); //for vendor's address
		add_shortcode( 'woo_vou_siteurl', array( $this, 'woo_vou_siteurl_container' ) ); //for website url
		add_shortcode( 'woo_vou_location', array( $this, 'woo_vou_location_container' ) ); //for voucher locations
		add_shortcode( 'woo_vou_custom', array( $this, 'woo_vou_custom_container' ) ); //for custom
		add_shortcode( 'woo_vou_check_code', array( $this, 'woo_vou_check_code' ) ); //for check voucher code
		
		// Shortcode for voucher codes replacement
		add_shortcode( 'vou_codes', array( $this, 'woo_vou_codes_replace' ) );
		
		// Shortcode for voucher redeem instruction replacement
		add_shortcode( 'vou_redeem_instruction', array( $this, 'woo_vou_redeem_instruction_replace' ) );
		
		// Shortcode for QRCode display
		add_shortcode( 'woo_vou_qrcode', array( $this, 'woo_vou_qrcode_code' ) ); //for qrcode

		// Shortcode for QRCodes display
		add_shortcode( 'woo_vou_qrcodes', array( $this, 'woo_vou_qrcodes_code' ) ); //for qrcodes
		
		// Shortcode for Barcode display
		add_shortcode( 'woo_vou_barcode', array( $this, 'woo_vou_barcode_code' ) ); //for barcode
		
		// Shortcode for Barcodes display
		add_shortcode( 'woo_vou_barcodes', array( $this, 'woo_vou_barcodes_code' ) ); //for barcodes

		// Shortcode for Product Image display
		add_shortcode( 'woo_vou_product_image', array( $this, 'woo_vou_productimage_image' ) ); //for product image

		// Shortcode for displaying used voucher codes on frontend
		add_shortcode( 'woo_vou_used_voucher_codes', array( $this, 'woo_vou_used_voucher_codes_code' ) ); //for used voucher code
		
		// Shortcode for displaying purchased voucher codes on frontend
		add_shortcode( 'woo_vou_purchased_voucher_codes', array( $this, 'woo_vou_purchased_voucher_codes_code' ) ); //for purchased voucher code

		// Shortcode for displaying unused voucher codes on frontend
		add_shortcode( 'woo_vou_unused_voucher_codes', array( $this, 'woo_vou_unused_voucher_codes_code' ) ); //for unused voucher code
	}
}