<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate PDF for Voucher
 * 
 * Handles to Generate PDF on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

function woo_vou_generate_voucher_code_pdf() {

	$prefix = WOO_VOU_META_PREFIX;

	// Getting voucher character support
	$voucher_char_support = get_option( 'vou_char_support' );

	// Get PDF Title, Author name and Creater name 
    $vou_pdf_title 	 = get_option( 'vou_pdf_title' );
    $vou_pdf_author  = get_option( 'vou_pdf_author' );
    $vou_pdf_creator = get_option( 'vou_pdf_creator' );

    $vou_pdf_author  = !empty( $vou_pdf_author ) ? $vou_pdf_author : esc_html__('WooCommerce', 'woovoucher');
    $vou_pdf_creator = !empty( $vou_pdf_creator ) ? $vou_pdf_creator : esc_html__('WooCommerce', 'woovoucher');
    $vou_pdf_title 	 = !empty( $vou_pdf_title ) ? $vou_pdf_title : esc_html__('WooCommerce Voucher', 'woovoucher');

	// Taking pdf fonts	
	$pdf_font = 'helvetica'; // This is default font

	if( !empty( $voucher_char_support ) ) {	// if character support is checked
		
		$pdf_font = apply_filters( 'woo_vou_pf_tcpdf_font', '' );
		
		if( empty( $pdf_font ) ){
			$pdf_font = 'freeserif';
		}	
	}

	if( isset( $_GET['woo-vou-used-gen-pdf'] ) && !empty( $_GET['woo-vou-used-gen-pdf'] )
		&& $_GET['woo-vou-used-gen-pdf'] == '1' && isset($_GET['product_id']) 
		&& !empty($_GET['product_id']) && current_user_can ( 'publish_products' ) ) {

		global $current_user,$woo_vou_model, $woo_vou_voucher, $post;

		//Create html for PDF
		$html = '';

		//model class
		$model = $woo_vou_model;

		$postid = $_GET['product_id'];

		if( !class_exists( 'WPWEB_TCPDF' ) ) { //If class not exist

			//include tcpdf file
			require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
		}

		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {

		 	//Get Voucher Details by post id
		 	$voucodes = woo_vou_get_used_codes_by_product_id( $postid );

		 	$voucher_heading 	= esc_html__( 'Redeemed Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= esc_html__( 'No voucher codes redeemed yet.', 'woovoucher' );

			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
		} elseif (isset($_GET['woo_vou_action']) && $_GET['woo_vou_action'] == 'expired') {
            
            // Get Unused Voucher Details by post id
            $voucodes = woo_vou_get_unused_codes_by_product_id( $postid );

            $voucher_heading 	= esc_html__('Expired Voucher Codes', 'woovoucher');
            $voucher_empty_msg 	= esc_html__('No expired voucher codes yet.', 'woovoucher');

            $vou_file_name = 'woo-unused-voucher-codes-{current_date}';
		} else {

		 	//Get Voucher Details by post id
		 	$voucodes = woo_vou_get_purchased_codes_by_product_id( $postid );

		 	$voucher_heading 	= esc_html__( 'Unredeemed Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= esc_html__( 'No voucher codes unredeemed yet.', 'woovoucher' );

			$vou_pdf_name = get_option( 'vou_pdf_name' );
			$vou_file_name = !empty( $vou_pdf_name )? $vou_pdf_name : 'woo-purchased-voucher-codes-{current_date}';
		}

		$pdf = new WPWEB_TCPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, WPWEB_PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// remove default header
		$pdf->setPrintHeader(false);

		// remove default footer
		$pdf->setPrintFooter(false);

		$pdf->AddPage( 'L', 'A4' );

		// Auther name and Creater name 
	    $pdf->SetCreator(utf8_decode(apply_filters('woo_vou_set_pdf_creator', $vou_pdf_creator )));
	    $pdf->SetAuthor(utf8_decode(apply_filters('woo_vou_set_pdf_author', $vou_pdf_author )));
	    $pdf->SetTitle(utf8_decode(apply_filters('woo_vou_set_pdf_title', $vou_pdf_title )));

		// Set margine of pdf (float left, float top , float right)
		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );

		// Font size set
		$pdf->SetFont( $pdf_font, '', 18 );
		$pdf->SetTextColor( 50, 50, 50 );

		$pdf->Cell( 270, 5, utf8_decode( $voucher_heading ), 0, 2, 'C', false );
		$pdf->Ln(5);
		$pdf->SetFont( $pdf_font, '', 12 );
		$pdf->SetFillColor( 238, 238, 238 );

		//voucher logo
		if( !empty( $voulogo ) ) {
			$pdf->Image( $voulogo, 95, 25, 20, 20 );
			$pdf->Ln(35);
		}

		// if generate pdf for used code add and extra column
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
			$columns =  array(
								'voucode' 			=> array('name' => esc_html__('Voucher Code', 'woovoucher'), 'width' => 12),
								'buyerinfo' 		=> array('name' => esc_html__('Buyer\'s Information', 'woovoucher'), 'width' => 23),
								'orderinfo' 		=> array('name' => esc_html__('Order Information', 'woovoucher'), 'width' => 23),
								'recipientinfo'		=> array('name' => esc_html__('Recipient Information', 'woovoucher'), 'width' => 21),
								'redeeminfo' 		=> array('name' => esc_html__('Redeem Information', 'woovoucher'), 'width' => 21),
						);
		} else {
			$columns =  array(
								'voucode' 			=> array('name' => esc_html__('Voucher Code', 'woovoucher'), 'width' => 14),
								'buyerinfo' 		=> array('name' => esc_html__('Buyer\'s Information', 'woovoucher'), 'width' => 30),
								'orderinfo' 		=> array('name' => esc_html__('Order Information', 'woovoucher'), 'width' => 28),
								'recipientinfo'		=> array('name' => esc_html__('Recipient Information', 'woovoucher'), 'width' => 28),
						);
		}

		$html .= '<table style="line-height:1.5;" border="1"><thead><tr style="line-height:2;font-weight:bold;background-color:#EEEEEE;">';

		// Table head Code
		foreach ($columns as $column) {

			$html .= '<th width="'.$column['width'].'%" style="margin:10px;">'.$column['name'].'</th>';
		}

		$html .= '</tr></thead>';
		$html .= '<tbody>';

		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) {
			foreach ( $voucodes as $key => $voucodes_data ) {
                
                $voucher_codes = explode(',', $voucodes_data['vou_codes'] );
                foreach ( $voucher_codes as $voucher_code ) { 
                    $html .= '<tr>';

                    //voucher order id
                    $orderid 		= $voucodes_data['order_id'];

                    //voucher order date
                    $orderdate 		= $voucodes_data['order_date'];
                    $orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';

                    //buyer's name who has purchased/used voucher code
                    $buyername 		=  $voucodes_data['buyer_name'];

                    //voucher code purchased/used
                    $voucode 		= $voucher_code;

                    $buyer_details 	= $model->woo_vou_get_buyer_information( $orderid );
                    $buyerinfo		= woo_vou_display_buyer_info_html( $buyer_details );
                    $orderinfo 		= woo_vou_display_order_info_html( $orderid,'pdf' );
                    $recipientinfo 	= woo_vou_display_recipient_info_html( $orderid, $voucode, 'pdf' );

                    $html .= '<td width="'.$columns['voucode']['width'].'%">'.$voucode.'</td>';
                    $html .= '<td width="'.$columns['buyerinfo']['width'].'%">'.$buyerinfo.'</td>';
                    $html .= '<td width="'.$columns['orderinfo']['width'].'%">'.$orderinfo.'</td>';
                    $html .= '<td width="'.$columns['recipientinfo']['width'].'%">'.$recipientinfo.'</td>';

                    if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column

                        $redeeminfo = woo_vou_display_redeem_info_html( $voucodes_data['voucode_id'], $orderid, '' );
                        $redeeminfo 	= strip_tags( $redeeminfo, '<table><tr><td>' );	
                        $html .= '<td width="'.$columns['redeeminfo']['width'].'%">'.$redeeminfo.'</td>';
                    }

                    $html .= '</tr>';
                }
			}
		} else {

			if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
				$colspan = 5;
			} else {
				$colspan = 4;
			}

			$title = ( $voucher_empty_msg );
			$html .= '<tr><td colspan="'.$colspan.'">'.$title.'</td></tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		// output the HTML content
		$pdf->writeHTML( $html, true, 0, true, 0 );

		// reset pointer to the last page
		$pdf->lastPage();

		//voucher code
		$pdf->SetFont( $pdf_font, 'B', 14 );

		// Apply filter to allow 3rd party people to change it
		$date_format = apply_filters( 'woo_vou_voucher_date_format', 'Y-m-d' );

		$vou_file_name = str_replace( '{current_date}', date( $date_format ), $vou_file_name );
		$pdf->Output( $vou_file_name . '.pdf', 'D' );
		exit;
	}

	// generate pdf for voucher code
	if( isset( $_GET['woo-vou-voucher-gen-pdf'] ) && !empty( $_GET['woo-vou-voucher-gen-pdf'] )
		&& $_GET['woo-vou-voucher-gen-pdf'] == '1' ) {

		$prefix = WOO_VOU_META_PREFIX;

		global $current_user,$woo_vou_model, $woo_vou_voucher, $post, $woo_vou_vendor_role;

		// Taking parameter
		$orderby 	= isset( $_GET['orderby'] ) ? urldecode( $_GET['orderby'] ) : 'ID';
		$order		= isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';

		$args = array(
						'orderby'			=> $orderby,
						'order'				=> $order,
					);

		$admin_roles	= woo_vou_assigned_admin_roles();

		//Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		if ( empty ( $current_user->ID ) || empty ( $current_user->roles ) ) {

		    return;
		}

		//model class
		$model = $woo_vou_model;

		// include tcpdf library
		require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';

		// Get option whether to allow all vendor to redeem voucher codes
	    $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {

			$args['meta_query'] = array(
											array(
														'key'		=> $prefix.'used_codes',
														'value'		=> '',
														'compare'	=> '!=',
													)
										);

			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role = array_shift( $user_roles );

			if( in_array( $user_role, $woo_vou_vendor_role ) && 
				( empty($vou_enable_vendor_access_all_voucodes) || $vou_enable_vendor_access_all_voucodes == 'no' ) ) { // Check vendor user role

				$args['author'] = $current_user->ID;

			} elseif( !in_array( $user_role, $woo_vou_vendor_role ) && !in_array( $user_role, $admin_roles ) ) {
		
				$args['meta_query'] =	array(
									'relation' => 'AND',
									($args['meta_query']),
									array(
										array(
												'key'		=> $prefix.'customer_user',
												'value'		=> $current_user->ID,
												'compare'	=> '=',
											)
									)
								);
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}
			
			if( isset( $_GET['woo_vou_user_id'] ) && !empty( $_GET['woo_vou_user_id'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'redeem_by',
											'value'		=> $_GET['woo_vou_user_id'],
											'compare'	=> '=',
										)
								)
							);
			}
			
			if( isset( $_GET['woo_vou_start_date'] ) && !empty( $_GET['woo_vou_start_date'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'used_code_date',
											'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_start_date'] ) ),
											'compare'	=> '>=',
										)
								)
							);
			}
			
			if( isset( $_GET['woo_vou_end_date'] ) && !empty( $_GET['woo_vou_end_date'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'used_code_date',
											'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_end_date'] ) ),
											'compare'	=> '<=',
										)
								)
							);
			}

			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {

				$args['meta_query'] = array(
											'relation' => 'AND',
											($args['meta_query']),
											array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'used_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											)
										);
			}

			// If Partially Used checkbox is ticked than only show voucher codes which are used partially
			if( !empty( $_GET['woo_vou_partial_used_voucode'] ) && $_GET['woo_vou_partial_used_voucode'] == 'yes' ){

				// Search for code having meta key _woo_vou_redeem_method and meta value partial
				$args['meta_query'] = array_merge(array( array( 
														'key' 		=> $prefix . 'redeem_method',
														'value'		=> 'partial',
														) ), $args['meta_query']);
			}

			$args = apply_filters('woo_vou_used_codes_gen_pdf', $args);

		 	//Get Voucher Details by post id
		 	$voucodes = woo_vou_get_voucher_details( $args );

		 	$voucher_heading 	= esc_html__( 'Redeemed Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= esc_html__( 'No voucher codes redeemed yet.', 'woovoucher' );

			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
		} else {
			
			if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expired') {
		 		
		 			$args['meta_query'] = array(
											array(
											'key'			=> $prefix . 'purchased_codes',
											'value'			=> '',
											'compare'		=> '!='
										),
										array(
												'key'			=> $prefix . 'used_codes',
												'compare'		=> 'NOT EXISTS'
											),
										array(
													'key'		=> $prefix .'exp_date',
													'compare'	=> '<=',
		                  							//'type'		=> 'DATE',
		                  							'value'		=> $model->woo_vou_current_date()
											),
										array(
													'key'		=> $prefix .'exp_date',
													'value'		=> '',
													'compare'	=> '!='
											)
										);
		 	} else {
				$args['meta_query'] = array(
										array(
												'key' 		=> $prefix . 'purchased_codes',
												'value'		=> '',
												'compare' 	=> '!='
											),
										array(
													'key'     	=> $prefix . 'used_codes',
													'compare' 	=> 'NOT EXISTS'
											 ),
										array(
											'relation' => 'OR', // Optional, defaults to "AND"
											array(
												'key'     => $prefix .'exp_date',
												'value'   => '',
												'compare' => '='
											),
											array(
												'key' =>  $prefix .'exp_date',
												'compare' => '>=',
	                  							'value' => $model->woo_vou_current_date()
											)
									   )	
									);
			}

			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role = array_shift( $user_roles );

			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( in_array( $user_role, $woo_vou_vendor_role ) 
				&& ( empty($vou_enable_vendor_access_all_voucodes) || $vou_enable_vendor_access_all_voucodes == 'no' ) ) {// voucher admin can redeem all codes

				$args['author'] = $current_user->ID;

			} elseif( !in_array( $user_role, $woo_vou_vendor_role ) && !in_array( $user_role, $admin_roles ) ) {
		
				$args['meta_query'] =	array(
									'relation' => 'AND',
									($args['meta_query']),
									array(
										array(
												'key'		=> $prefix.'customer_user',
												'value'		=> $current_user->ID,
												'compare'	=> '=',
											)
									)
								);
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}

			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {

				$args['meta_query'] = array(
										'relation' => 'AND',
										($args['meta_query']),
										array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'purchased_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											)
										);
			}

			if( isset( $_GET['woo_vou_start_date'] ) && !empty( $_GET['woo_vou_start_date'] ) ) {

				$args['date_query'][] = array(
												'column' => 'post_date',
												'after'  => $_GET['woo_vou_start_date'],
											);
			}

			if( isset( $_GET['woo_vou_end_date'] ) && !empty( $_GET['woo_vou_end_date'] ) ) {

				$args['date_query'][] = array(
												'column' => 'post_date',
												'before'  => $_GET['woo_vou_end_date'],
											);
			}

			// If Partially Used checkbox is ticked than only show voucher codes which are used partially
			if( !empty( $_GET['woo_vou_partial_used_voucode'] ) && $_GET['woo_vou_partial_used_voucode'] == 'yes' ){

				// Search for code having meta key _woo_vou_redeem_method and meta value partial
				$args['meta_query'] = array_merge(array( array( 
														'key' 		=> $prefix . 'redeem_method',
														'value'		=> 'partial',
														) ), $args['meta_query']);
			}

			$args = apply_filters('woo_vou_purchased_codes_gen_pdf', $args);

		 	//Get Voucher Details by post id
		 	$voucodes = woo_vou_get_voucher_details( $args );

		 	if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expired') {
		 	
			 	$voucher_heading 	= esc_html__( 'Expired Voucher Codes','woovoucher' );
			 	$voucher_empty_msg	= esc_html__( 'No expired voucher codes yet.', 'woovoucher' );
				$vou_file_name = 'woo-unused-voucher-codes-{current_date}';
		 	} else {

			 	$voucher_heading 	= esc_html__( 'Unredeemed Voucher Codes','woovoucher' );
			 	$voucher_empty_msg	= esc_html__( 'No voucher codes purchased yet.', 'woovoucher' );
			 	
			 	$vou_pdf_name = get_option( 'vou_pdf_name' );
				$vou_file_name = !empty( $vou_pdf_name )? $vou_pdf_name : 'woo-purchased-voucher-codes-{current_date}';
		 	}
		}
		$pdf = new WPWEB_TCPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, WPWEB_PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// remove default header
		$pdf->setPrintHeader(false);

		// remove default footer
		$pdf->setPrintFooter(false);

		$pdf->AddPage( 'L', 'A4' );

		// Auther name and Creater name 
	    $pdf->SetCreator(utf8_decode(apply_filters('woo_vou_set_pdf_creator', $vou_pdf_creator )));
	    $pdf->SetAuthor(utf8_decode(apply_filters('woo_vou_set_pdf_author', $vou_pdf_author )));
	    $pdf->SetTitle(utf8_decode(apply_filters('woo_vou_set_pdf_title', $vou_pdf_title )));

		// Set margine of pdf (float left, float top , float right)
		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );

		// Font size set
		$pdf->SetFont( $pdf_font, '', 18 );
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->Ln(3);

		$pdf->Cell( 270, 5, apply_filters( 'woo_vou_codes_pdf_title', utf8_decode( $voucher_heading ) ), 0, 2, 'C', false );
		$pdf->Ln(5);
		$pdf->SetFont( $pdf_font, '', 10 );
		$pdf->SetFillColor( 238, 238, 238 );

		//voucher logo
		if( !empty( $voulogo ) ) {
			$pdf->Image( $voulogo, 95, 25, 20, 20 );
			$pdf->Ln(35);
		}

		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
			$columns =  array(
								'vou_code' 			=> array('name' => esc_html__('Voucher Code', 'woovoucher'), 'width' => 10),
								'product_info' 		=> array('name' => esc_html__('Product Information', 'woovoucher'), 'width' => 18),
								'buyer_info' 		=> array('name' => esc_html__('Buyer\'s Information', 'woovoucher'), 'width' => 19),
								'order_info' 		=> array('name' => esc_html__('Order Information', 'woovoucher'), 'width' => 19),
								'recipient_info'	=> array('name' => esc_html__('Recipient Information', 'woovoucher'), 'width' => 19),
								'redeem_by' 		=> array('name' => esc_html__('Redeem Information', 'woovoucher'), 'width' => 15),
						);
		} else {
			$columns =  array(
								'vou_code' 			=> array('name' => esc_html__('Voucher Code', 'woovoucher'), 'width' => 11),
								'product_info' 		=> array('name' => esc_html__('Product Information', 'woovoucher'), 'width' => 20),
								'buyer_info' 		=> array('name' => esc_html__('Buyer\'s Information', 'woovoucher'), 'width' => 25),
								'order_info' 		=> array('name' => esc_html__('Order Information', 'woovoucher'), 'width' => 22),
								'recipient_info'	=> array('name' => esc_html__('Recipient Information', 'woovoucher'), 'width' => 22),
						);
		}

		$pdf_type	= isset( $_GET['woo_vou_action'] ) ? $_GET['woo_vou_action'] : 'purchased';

		$columns	= apply_filters( 'woo_vou_generate_pdf_columns', $columns, $pdf_type );

		$html = '';
		$html .= '<table style="line-height:1.5;" border="1"><thead><tr style="line-height:2;font-weight:bold;background-color:#EEEEEE;">';

		// Table head Code
		foreach( $columns as $column ) {

			$html .= '<th width="'.$column['width'].'%" style="margin:10px;"> '.$column['name'].' </th>';
		}

		$html .= '</tr></thead>';
		$html .= '<tbody>';
		
		if( count( $voucodes ) > 0 ) {

			foreach ( $voucodes as $key => $voucodes_data ) {

				$html .= '<tr>';

				//voucher order id
				$orderid 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_id', true );

				//voucher order date
				$orderdate 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_date', true );
				$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';

				//voucher code purchased/used
				$voucode 		= get_post_meta( $voucodes_data['ID'], $prefix.'purchased_codes', true );

				$redeeminfo 	= woo_vou_display_redeem_info_html( $voucodes_data['ID'], $orderid, '' );
				$redeeminfo 	= strip_tags( $redeeminfo, '<table><tr><td>' );				

				$product_desc 	= woo_vou_display_product_info_html( $orderid, $voucode, 'pdf' );
				$order_desc 	= woo_vou_display_order_info_html( $orderid,'pdf' );
				$recipient_desc = woo_vou_display_recipient_info_html( $orderid, $voucode, 'pdf' );
	
				$buyer_details	= $model->woo_vou_get_buyer_information( $orderid );
				$buyerinfo		= woo_vou_display_buyer_info_html( $buyer_details );
				
				$html .= '<td width="'.$columns['vou_code']['width'].'%"> '.$voucode.' </td>';
				$html .= '<td width="'.$columns['product_info']['width'].'%"> '.$product_desc.' </td>';
				$html .= '<td width="'.$columns['buyer_info']['width'].'%">'.$buyerinfo.' </td>';
				$html .= '<td width="'.$columns['order_info']['width'].'%"> '. $order_desc .' </td>';
				$html .= '<td width="'.$columns['recipient_info']['width'].'%"> '. $recipient_desc .' </td>';

				if( isset( $_GET['woo_vou_action'] ) && ( $_GET['woo_vou_action'] == 'used' ||  $_GET['woo_vou_action'] == 'partially' ) ) { // if generate pdf for used code add and extra column
					
					$html .= '<td width="'.$columns['redeem_by']['width'].'%"> '.( $redeeminfo ).' </td>';
				} 

				ob_start();
				do_action( 'woo_vou_generate_pdf_add_column_after', $orderid, $voucode, $pdf_type );
				$added_column = ob_get_clean();

				$html .= $added_column;

				$html .= '</tr>';
			}
		} else {

			if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
				$colspan = 6;
			} else {
				$colspan = 5;
			}

			$title = ( $voucher_empty_msg );
			$html .= '<tr><td colspan="'.$colspan.'"> '.$title.' </td></tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		
		
		// output the HTML content
		$pdf->writeHTML( $html, true, 0, true, 0 );

		// reset pointer to the last page
		$pdf->lastPage();

		//voucher code
		$pdf->SetFont( $pdf_font, 'B', 10 );

		// Apply filter to allow 3rd party people to change it
		$date_format = apply_filters( 'woo_vou_voucher_date_format', 'Y-m-d' );

		$vou_file_name = str_replace( '{current_date}', date( $date_format ), $vou_file_name );
		$pdf->Output( $vou_file_name . '.pdf', 'D' );
		exit;
	}
}
add_action( 'init', 'woo_vou_generate_voucher_code_pdf' );