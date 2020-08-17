<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Scripts {

	public function __construct() {
		
	}
	
	/**
	 * Enqueue Scrips
	 * 
	 * Handles to enqueue script on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_popup_scripts( $hook_suffix ) {

		global $post, $wp_version;

		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		$prefix 			= WOO_VOU_META_PREFIX; // get prefix
		$coupon_type 		= '';
		$newui 				= $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
		$pages_hook_suffix 	= array( 'post.php', 'post-new.php', 'user-edit.php' );
		$post_id			= is_object($post) ? $post->ID : '';
		$wc_vou_vendor_screen 	= 'toplevel_page_woo-vou-codes'; // screen id of voucher code page when vendors role
		
		//Check pages when you needed 
  		if( in_array( $hook_suffix, array( $wc_vou_vendor_screen, $wc_screen_id.'_page_woo-vou-codes', $wc_screen_id.'_page_wc-settings', $wc_screen_id.'_page_woo-vou-check-voucher-code', $woo_vou_screen_id.'_page_woo-vou-check-voucher-code', 'toplevel_page_woo-vou-check-voucher-code', 'user-edit.php' , 'user-new.php', 'profile.php', 'post.php', 'post-new.php' ) ) ) {

  			$upload_dir   = wp_upload_dir();
  			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ) );
			wp_register_script( 'woo-vou-admin-script', WOO_VOU_URL . 'includes/js/woo-vou-admin.js', array(), WOO_VOU_PLUGIN_VERSION, true);
			
			$is_partial_redeem = get_option('vou_enable_partial_redeem');

			wp_localize_script( 'woo-vou-admin-script' , 'WooVouAdminSetOpt' , array( 
																					'is_partial_option'	=> $is_partial_redeem ) );
			wp_enqueue_script( 'woo-vou-admin-script' );
			
			// check if pdf fonts plugin is active or not
			$is_pdf_fonts_plugin_active = false;
			if( defined( 'WOO_VOU_PF_DIR') ) {
				$is_pdf_fonts_plugin_active = true;
			}
			
			if ( $post ) {
				
				// Get coupon's type
				$coupon_type = get_post_meta( $post_id, $prefix . 'coupon_type', true );
			}
			$is_addon = "";
			if(isset($_GET['section']) && $_GET['section'] == 'vou_addon'){
				$is_addon = "vou_addon";
			}
			wp_localize_script( 'woo-vou-admin-script' , 'WooVouAdminSettings' , array( 
																						'ajaxurl' 					 => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																						'new_media_ui' 				 => $newui, 
																						'is_pdf_fonts_plugin_active' => $is_pdf_fonts_plugin_active,
																						'coupon_type' 				 => $coupon_type,
																						'upload_base_url'			 =>	$upload_dir['baseurl'],
																						'code_used_success'	=> esc_html__( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ),
																						'redeem_amount_empty_error' => esc_html__( 'Please enter redeem amount.', 'woovoucher' ),
																						'redeem_amount_greaterthen_redeemable_amount' => esc_html__( 'Redeem amount should not be greater then redeemable amount.', 'woovoucher' ),
																						'is_addon' => $is_addon
																					) );

			if( !in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {

				$vou_change_expiry_date = get_option( 'vou_change_expiry_date' );

				// add js for code details in admin
				wp_register_script( 'woo-vou-code-detail-script', WOO_VOU_URL . 'includes/js/woo-vou-code-details.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_script( 'woo-vou-code-detail-script' );
	
				wp_localize_script( 'woo-vou-code-detail-script' , 'WooVouCode' , array( 
																							'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																							'new_media_ui' 		=> $newui,
																							'invalid_url' 		=> esc_html__( 'Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ),
																							'invalid_email'		=> esc_html__('Please enter valid Email ID', 'woovoucher'),
																							'mail_sent'			=> esc_html__('Mail sent successfully', 'woovoucher'),
																							'vou_change_expiry_date' => $vou_change_expiry_date
																						) );
			    wp_enqueue_media();
			}
		}
		
		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
					
			//Check vouchers & product post type
			if( in_array( $hook_suffix, array( 'user-edit.php' ) )
				|| ( isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE ) ) {

				$is_variable = $is_translated = $is_enable_coupon = ''; // Declare variables
				if(isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE) {

					$is_translated		= apply_filters('woo_vou_is_translation_product', false, $post_id); // Filter added to add compatibility with WPML
					$product = wc_get_product($post_id);
					$is_variable = (is_object($product) && ($product->is_type('variable') || $product->is_type('variation'))) ? 1 : 0;
				}

				// Get coupon code option
				$vou_enable_coupon = get_option( 'vou_enable_coupon_code' );

				wp_register_script( 'woo-vou-script-metabox', WOO_VOU_URL.'includes/js/woo-vou-metabox.js', array( 'jquery', 'jquery-form' ), WOO_VOU_PLUGIN_VERSION, true ); 
				wp_enqueue_script( 'woo-vou-script-metabox' );
				wp_localize_script( 'woo-vou-script-metabox', 'WooVouMeta', array(	
																					'invalid_url' 				=> esc_html__( 'Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ),
																					'noofvouchererror' 			=> '<div>' . esc_html__( 'Please enter Number of Voucher Codes.', 'woovoucher' ) . '</div>',
																					'patternemptyerror' 		=> '<div>' . esc_html__( 'Please enter Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'onlydigitserror' 			=> '<div>' . esc_html__( 'Please enter only Numeric values in Number of Voucher Codes.', 'woovoucher' ) . '</div>',
																					'generateerror' 			=> '<div>' . esc_html__( 'Please enter Valid Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'filetypeerror'				=> '<div>' . esc_html__( 'Please upload csv file.', 'woovoucher' ) . '</div>',
																					'fileerror'					=> '<div>' . esc_html__( 'File can not be empty, please upload valid file.', 'woovoucher' ) . '</div>',
																					'new_media_ui' 				=> $newui,
																					'enable_voucher'        	=> get_option( 'vou_enable_voucher' ), //Localize "Auto Enable Voucher" setting to use in JS 
																					'price_options'        		=> get_option( 'vou_voucher_price_options' ), //Localize "Voucher Price Options" setting to use in JS 
																					'invalid_price'         	=> esc_html__( 'You can\'t leave this empty.', 'woovoucher' ),
																					'woo_vou_nonce'				=> wp_create_nonce( 'woo_vou_pre_publish_validation' ),
																					'ajaxurl'               	=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																					'prefix_placeholder'		=> esc_html__('WPWeb', 'woovoucher'),
																					'seperator_placeholder' 	=> '-',
																					'pattern_placeholder'		=> 'LLDD',
																					'global_vou_pdf_usability'	=> get_option('vou_pdf_usability'),
																					'is_variable'				=> $is_variable,
																					'is_translated'				=> $is_translated,
																					'stock_qty_err'				=> '<span id="woo_vou_stock_error" class="woo-vou-stocks-error">'.esc_html__('Please either enter quantity for "Stock quantity" or untick "Manage stock?" option.', 'woovoucher').'</span>',
																					'deliverymetherror'			=> esc_html__( 'Please select atleast one delivery method.', 'woovoucher' ),
																					'enable_coupon_code' 		=> $vou_enable_coupon
																				) );
			}
			
			//Check vouchers post type
			if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
						
				//If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
			    if ( $wp_version >= 3.5 ) {
			        //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_script( 'wp-color-picker' );
			    }
			    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
			    else {
			        //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_script( 'farbtastic' );
			    }
				wp_enqueue_script( array( 'jquery', 'jquery-ui-tabs', 'media-upload', 'thickbox', 'tinymce','jquery-ui-accordion' ) );
				
				wp_register_script( 'woo-vou-admin-voucher-script', WOO_VOU_URL . 'includes/js/woo-vou-admin-voucher.js', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_script( 'woo-vou-admin-voucher-script' );

				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSettings' , array( 'new_media_ui' => $newui ) );
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouTranObj' , array( 
																									'onbuttontxt' => esc_html__('Voucher Builder is On','woovoucher'),
																									'offbuttontxt' => esc_html__('Voucher Builder is Off','woovoucher'),
																									'switchanswer' => esc_html__('Default WordPress editor has some content, switching to the Voucher will remove it.','woovoucher'),
																									'btnsave' => esc_html__('Save','woovoucher'),
																									'btncancel' => esc_html__('Cancel','woovoucher'),
																									'btndelete' => esc_html__('Delete','woovoucher'),
																									'btnaddmore' => esc_html__('Add More','woovoucher'),
																									'wp_version' => $wp_version
																								));
				/* this is used for text block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouTextBlock' , array( 
																									'textblocktitle' => esc_html__('Voucher Code','woovoucher'),
																									'textblockdesc' => esc_html__('Voucher Code','woovoucher'),
																									'textblockdesccodes' => '{codes}'
																								));
				/* this is used for message box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouMsgBox' , array( 
																									'msgboxtitle' => esc_html__('Redeem Instruction','woovoucher'),
																									'msgboxdesc' => '<p>' . '{redeem}' . '</p>'
																								));
				/* this is used for logo box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSiteLogoBox' , array( 
																									'sitelogoboxtitle' => esc_html__('Voucher Site Logo','woovoucher'),
																									'sitelogoboxdesc'  => '{sitelogo}'
																								));
				/* this is used for logo box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouLogoBox' , array( 
																									'logoboxtitle' => esc_html__('Voucher Logo','woovoucher'),
																									'logoboxdesc' => '{vendorlogo}'
																								));
				/* this is used for expire date block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouExpireBlock' , array( 
																									'expireblocktitle' => esc_html__('Expire Date','woovoucher'),
																									'expireblockdesc' => esc_html__('Expire:','woovoucher') . ' {expiredatetime}'
																								));
				/* this is used for vendor's address block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouVenAddrBlock' , array( 
																									'venaddrblocktitle' => esc_html__('Vendor\'s Address','woovoucher'),
																									'venaddrblockdesc' => '{vendoraddress}'
																								));
				/* this is used for website URL block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouSiteURLBlock' , array( 
																									'siteurlblocktitle' => esc_html__('Website URL','woovoucher'),
																									'siteurlblockdesc' => '{siteurl}'
																								));
				/* this is used for voucher location block section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouLocBlock' , array( 
																									'locblocktitle' => esc_html__('Voucher Locations','woovoucher'),
																									'locblockdesc' => '<p><span style="font-size: 9pt;">{location}</span></p>'
																								));
				/* this is used for blank box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouBlankBox' , array( 
																									'blankboxtitle' => esc_html__('Blank Block','woovoucher'),
																									'blankboxdesc' => esc_html__('Blank Block','woovoucher')
																								));
				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouCustomBlock' , array( 
																									'customblocktitle' => esc_html__('Custom Block','woovoucher'),
																									'customblockdesc' => esc_html__('Custom Block','woovoucher')
																								));
																								
				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouQrcodeBlock' , array( 
																									'qrcodeblocktitle' => esc_html__( 'QR Code','woovoucher' ),
																									'qrcodeblockdesc' => '{qrcode}'
																								));

				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouQrcodesBlock' , array( 
																									'qrcodesblocktitle' => esc_html__( 'QR Codes','woovoucher' ),
																									'qrcodesblockdesc' => '{qrcodes}'
																								));
																								
				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouBarcodeBlock' , array( 
																									'barcodeblocktitle' => esc_html__( 'Barcode','woovoucher' ),
																									'barcodeblockdesc' => '{barcode}'
																								));
				
				/* this is used for custom box section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouBarcodesBlock' , array( 
																									'barcodesblocktitle' => esc_html__( 'Barcodes','woovoucher' ),
																									'barcodesblockdesc' => '{barcodes}'
																								));
																								
				/* this is used for Messages section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouMessage' , array( 
																									'invalid_number' => esc_html__('Please enter valid number.','woovoucher'),
																								));

				/* this is used for feature image section */
				wp_localize_script( 'woo-vou-admin-voucher-script' , 'WooVouProductImageBlock' , array( 
																									'productimageblocktitle' => esc_html__( 'Product Image','woovoucher' ),
																									'productimageblockdesc' => '{productimage}'
																								));
			}
		}
	}
	
	/**
	 * Enqueue Styles
	 * 
	 * Handles to enqueue styles on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_popup_styles( $hook_suffix ) {

		global  $wp_scripts;
		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
		
		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();

		$pages_hook_suffix = array( 'post.php', 'post-new.php', $wc_screen_id.'_page_woo-vou-codes', 'toplevel_page_woo-vou-codes', $wc_screen_id.'_page_wc-settings' );

		
		//Check pages when you needed
		if( in_array( $hook_suffix, array( $wc_screen_id . '_page_woo-vou-check-voucher-code', $woo_vou_screen_id . '_page_woo-vou-check-voucher-code', 'toplevel_page_woo-vou-check-voucher-code', $wc_screen_id.'_page_woo-vou-codes', 'toplevel_page_woo-vou-codes', $wc_screen_id.'_page_wc-settings' ) ) ) {

			// Register admin styles
			wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );
			wp_enqueue_style( 'jquery-ui-style' );
			
			wp_register_style( 'woo-vou-font-awesome-style', WOO_VOU_URL.'includes/css/font-awesome.min.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-font-awesome-style' );
			
			wp_register_style( 'woo-vou-admin-style', WOO_VOU_URL.'includes/css/woo-vou-admin.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-admin-style' );
		}

		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
			
			global $post, $wp_version;
			
			//Check vouchers & product post type
			if( in_array( $hook_suffix, array( $wc_screen_id . '_page_woo-vou-codes', 'toplevel_page_woo-vou-codes', $wc_screen_id.'_page_wc-settings' ) )
				|| ( isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE ) ) {
				
				wp_register_style( 'woo-vou-style-metabox', WOO_VOU_URL.'includes/css/woo-vou-metabox.css', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_style( 'woo-vou-style-metabox' );
			}
			
			//Check vouchers post type
			if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
				
				//for color picker
				
				//If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
			    if ( $wp_version >= 3.5 ){
			        //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_style( 'wp-color-picker' );
			    }
			    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
			    else {
			        //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
			        wp_enqueue_style( 'farbtastic' );
			    }
			    
				wp_register_style( 'woo-vou-admin-style',  WOO_VOU_URL . 'includes/css/woo-vou-admin-voucher.css', array(), WOO_VOU_PLUGIN_VERSION );
				wp_enqueue_style( 'woo-vou-admin-style' );
			}
		}

		if( $hook_suffix == 'index.php' ) {

			wp_register_style( 'woo_vou_dashboard_style', WOO_VOU_URL.'includes/css/woo-vou-dashboard-widget.css' );
			wp_enqueue_style( 'woo_vou_dashboard_style' );
		}

		wp_register_style( 'woo-vou-common-admin-style',  WOO_VOU_URL . 'includes/css/woo-vou-admin-common.css', array(), WOO_VOU_PLUGIN_VERSION );
		
		wp_enqueue_style( 'woo-vou-common-admin-style' );
	}
		
	
	/**
	 * Enqueue Scripts
	 * 
	 * Handles to enqueue scripts on 
	 * needed pages
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_admin_drag_drop_scripts( $hook_suffix ) {
		
		global $post;
			
		//Check vouchers post type
		if( isset( $post->post_type ) && $post->post_type == WOO_VOU_POST_TYPE ) {
			
			wp_register_script( 'woo-vou-drag-script', WOO_VOU_URL . 'includes/js/dragdrop/portal.js', array( 'scriptaculous' ), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-drag-script' );
						
		}
	}
	
	/**
	 * Enqueue style for meta box page
	 * 
	 * Handles style which is enqueue in products meta box page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_metabox_styles() {
		
		global $woocommerce, $post;
		
		$allowed_post_types = apply_filters( 'woo_vou_metabox_allowed_post_types', array( 'product', 'woovouchers' ) );
		
		if( isset( $post ) && !in_array( $post->post_type, $allowed_post_types) )
			return;
		
		// Enqueue Meta Box Style
		wp_enqueue_style( 'woo-vou-meta-box', WOO_VOU_META_URL . '/css/meta-box.css', array(), WOO_VOU_PLUGIN_VERSION );
		  
		//css directory url
		$css_dir = $woocommerce->plugin_url() . '/assets/css/';
		
		// Admin styles for WC pages only
		wp_enqueue_style( 'woo_vou_admin_styles', $css_dir . 'admin.css', array(), WOOCOMMERCE_VERSION );
			
		wp_register_style( 'select2', $css_dir . 'select2.css', array(), WOOCOMMERCE_VERSION );
		wp_enqueue_style( 'select2' );
			
		// Enqueue for datepicker
		wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
		
		// Enqueu built-in style for color picker.
		if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
			wp_enqueue_style( 'wp-color-picker' );
		} else {
			wp_enqueue_style( 'farbtastic' );
		}
		
	}
	
	/**
	 * Enqueue script for meta box page
	 * 
	 * Handles script which is enqueue in products meta box page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_metabox_scripts() {
		
		global $wp_version, $woocommerce, $post;
		
		$allowed_post_types = apply_filters( 'woo_vou_metabox_allowed_post_types', array( 'product', 'woovouchers' ) );
		
		if( isset( $post ) && !in_array( $post->post_type, $allowed_post_types) )
			return;

		// Enqueue Meta Box Scripts
		wp_enqueue_script( 'woo-vou-meta-box', WOO_VOU_META_URL . '/js/meta-box.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION, true );

		//localize script
		$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
		wp_localize_script( 'woo-vou-meta-box','WooVou',array(		
																'new_media_ui'		=>	$newui,
																'one_file_min'		=>  esc_html__('You must have at least one file.','woovoucher' ),
															));

		// Enqueue for  image or file uploader
		wp_enqueue_script( 'media-upload' );
		add_thickbox();
		wp_enqueue_script( 'jquery-ui-sortable' );
		
		if ( !empty( $post ) && $post->post_type != 'product' ) {
								
			//js directory url
			$js_dir = $woocommerce->plugin_url() . '/assets/js/';
			
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
			// Select2
			wp_register_script( 'select2', $js_dir . 'select2/select2'.$suffix . '.js', array( 'jquery' ), '3.5.2' );
			wp_register_script( 'wc-enhanced-select', $woocommerce->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
			wp_enqueue_script( 'wc-enhanced-select' );	
		}
		
		// Enqueue for datepicker
		wp_enqueue_script(array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider'));
		
		wp_deregister_script( 'datepicker-slider' );
		wp_register_script('datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );
		wp_enqueue_script('datepicker-slider');

		wp_deregister_script( 'timepicker-addon' );
		wp_register_script('timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true);
		wp_enqueue_script('timepicker-addon');

		// Enqueu built-in script for color picker.
		if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
			wp_enqueue_script( 'wp-color-picker' );
		} else {
			wp_enqueue_script( 'farbtastic' );
		}
		
	}
	
	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for check code public
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_public_scripts(){

		global $woocommerce, $post, $wp_version, $woo_vou_model;

		
		$pdf_enable_preview_option = get_option('vou_enable_voucher_preview_open_option');
		$pdf_enable_preview_option = empty( $pdf_enable_preview_option ) ? 'popup':$pdf_enable_preview_option;
		
		$prefix = WOO_VOU_META_PREFIX; // Get prefix
		
		$expiration_date_type 	= $vou_exp_end_date = $vou_exp_days_diff = $vou_max_date = $vou_min_date = ''; // Declare variables
		$post_id 				= isset($post->ID) ? $post->ID : ''; // Get post id
		$post_content 			= isset($post->post_content) ? $post->post_content : '';
		$newui 					= $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
		$woo_vou_enable			= get_post_meta( $post_id, $prefix.'enable', true ); // check pdf voucher enable for this product
		
        $error_messages = array();

		// If post id is not empty
		if( !empty( $post_id ) ) {

			$min_max_date = $woo_vou_model->woo_vou_get_minmax_date_from_product($post_id);
			extract( $min_max_date );

			//Get product recipient meta setting
            $recipient_data = $woo_vou_model->woo_vou_get_product_recipient_meta($post_id);
            $recipient_columns = woo_vou_voucher_recipient_details();

            $product = wc_get_product($post_id);

            $error_messages = array(
                'vou_template_err' => '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data['pdf_template_selection_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p></li>',
                'delivery_meth_err' => '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data['recipient_delivery_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p></li>',
                'is_variable' => false
            );
            if ($product && $product->has_child()) {

                $error_messages['is_variable'] = true;
            }

            // Looping on all recipient details
            foreach ($recipient_columns as $recipient_key => $recipient_val) {

                $error_messages[$recipient_key . '_err'] = '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data[$recipient_key . '_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p></li>';
            }
		}
		

		// add js on front side
        wp_register_script('woo-vou-public-script', WOO_VOU_URL . 'includes/js/woo-vou-public.js', array(), WOO_VOU_PLUGIN_VERSION, true);        

        $public_localise_arr = apply_filters( 'woo_vou_public_script_localise_arr', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'preview_pdf' => add_query_arg(array('post_type' => 'woovouchers', 'woo_vou_pdf_action' => 'preview', 'voucher_id' => ''), get_site_url()),
            'vou_min_date' => $vou_min_date,
            'vou_max_date' => $vou_max_date,
            'is_preview_pdf_options' => $pdf_enable_preview_option,
            'time_text' => esc_html__('Time', 'woovoucher'),
			'hour_text' => esc_html__('Hour', 'woovoucher'),
			'minute_text' => esc_html__('Minute', 'woovoucher'),
			'current_text' => esc_html__('Now', 'woovoucher'),
			'close_text' => esc_html__('Close', 'woovoucher'),
        ) );

        $public_localise_arr = array_merge($public_localise_arr, $error_messages);

        wp_localize_script('woo-vou-public-script', 'WooVouPublic', $public_localise_arr);
																				
		// add css on front side
		wp_register_style( 'woo-vou-public-style', WOO_VOU_URL . 'includes/css/woo-vou-public.css', array(), WOO_VOU_PLUGIN_VERSION );		
        
        // to add js and css on archive page as has_shortcode will not work on archive page
        $is_archive = ( (get_option('show_on_front') == 'posts' && is_front_page() ) || is_archive() );
        
		// add css for check code in public        
		if(  has_shortcode( $post_content, 'woo_vou_check_code' ) || $is_archive || apply_filters( 'woo_vou_enqueue_check_code_script', false ) ) {
			
			// add css for check code in public
			wp_register_style( 'woo-vou-public-check-code-style', WOO_VOU_URL . 'includes/css/woo-vou-check-code.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-public-check-code-style' );
			
			// add js for check code in public
			wp_register_script( 'woo-vou-check-code-script', WOO_VOU_URL . 'includes/js/woo-vou-check-code.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-check-code-script' );
			$allow_guest_redeem_voucher = get_option('woo_vou_guest_user_allow_redeem_voucher');
			
			wp_localize_script( 'woo-vou-check-code-script' , 'WooVouCheck' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																						'check_code_error' 	=> esc_html__( 'Please enter voucher code.', 'woovoucher' ),
																						'code_invalid' 		=> esc_html__( 'Voucher code does not exist.', 'woovoucher' ),
																						'code_used_success'	=> esc_html__( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ),
																						'redeem_amount_empty_error' => esc_html__( 'Please enter redeem amount.', 'woovoucher' ),
																						'redeem_amount_greaterthen_redeemable_amount' => esc_html__( 'Redeem amount should not be greater then redeemable amount.', 'woovoucher' ),
																						'allow_guest_redeem_voucher' => $allow_guest_redeem_voucher
																					) );
            wp_enqueue_script('woo-vou-public-script');
            wp_enqueue_style( 'woo-vou-public-style' );
		}
		
		if( has_shortcode( $post_content, 'woo_vou_used_voucher_codes' ) || has_shortcode( $post_content, 'woo_vou_purchased_voucher_codes' ) 
			|| has_shortcode( $post_content, 'woo_vou_unused_voucher_codes' ) || $is_archive || apply_filters( 'woo_vou_enqueue_shortcode_style', false ) ) {
			
			wp_enqueue_style( array( 'list-tables', 'dashicons' ) );
			
			//js directory url
			$js_dir = $woocommerce->plugin_url() . '/assets/js/';
			
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ) );

			// Enqueue woocommerce admin style for select2
			wp_enqueue_style( 'woocommerce_public_select2_styles', WC()->plugin_url() . '/assets/css/select2.css', array(), WC_VERSION );
			
			// Select2
			wp_register_script( 'select2', $js_dir . 'select2/select2'.$suffix . '.js', array( 'jquery' ), '3.5.2' );
			wp_register_script( 'wc-enhanced-select', $woocommerce->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
			wp_enqueue_script( 'wc-enhanced-select' );

			$vou_change_expiry_date = get_option( 'vou_change_expiry_date' );

			// add js for code details in public
			wp_register_script( 'woo-vou-code-detail-script', WOO_VOU_URL . 'includes/js/woo-vou-code-details.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-code-detail-script' );

			wp_localize_script( 'woo-vou-code-detail-script' , 'WooVouCode' , array( 
			                                                                            'new_media_ui'	=>	$newui,
			                                                                            'ajaxurl'		=> admin_url('admin-ajax.php'),
			                                                                            'invalid_url' 	=> esc_html__( 'Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ),
			                                                                            'invalid_email'	=> esc_html__('Please enter valid Email ID', 'woovoucher'),
																						'mail_sent'		=> esc_html__('Mail sent successfully', 'woovoucher'),
																						'vou_change_expiry_date' => $vou_change_expiry_date
			                                                                        ) );

			wp_enqueue_media();
            
            wp_enqueue_script('woo-vou-public-script');
            wp_enqueue_style( 'woo-vou-public-style' );
		}
		
		if( has_shortcode( $post_content, 'woo_vou_used_voucher_codes' ) || 
                has_shortcode( $post_content, 'woo_vou_purchased_voucher_codes' ) || 
                has_shortcode( $post_content, 'woo_vou_unused_voucher_codes' ) || 
                ( is_product() && !empty( $woo_vou_enable ) && ( $woo_vou_enable == 'yes' ) ) || 
                has_shortcode( $post_content, 'product_page' ) || $is_archive || apply_filters( 'woo_vou_enqueue_shortcode_style', false ) ) {

			// Enqueue for datepicker
			wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
			
			// Enqueue for datepicker
			wp_enqueue_script(array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider'));
			
			wp_register_script('datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script('datepicker-slider');
			
			wp_deregister_script( 'timepicker-addon' );
			wp_register_script('timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true);
			wp_enqueue_script('timepicker-addon');
            
            wp_enqueue_script('woo-vou-public-script');
            wp_enqueue_style( 'woo-vou-public-style' );
		}					

		if( is_cart() || is_checkout() ){
            wp_enqueue_style( 'woo-vou-public-style' );
		}
		
		
		if(isset($_GET['woo_vou_code'])){
			wp_register_style( 'woo-vou-check-qrcode', WOO_VOU_URL . 'includes/css/woo-vou-check-qrcode.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-check-qrcode' );		
			wp_register_script( 'woo-vou-check-qrcode', WOO_VOU_URL . 'includes/js/woo-vou-check-qrcode.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-check-qrcode' );
		}
		
		
	}
	
	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for used codes list table on frontend
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	public function woo_vou_used_codes_public_scripts(){
		
		global $post;
		
		$post_content = isset($post->post_content) ? $post->post_content : '';
		
		// add css for check code in public
		if(  has_shortcode( $post_content, 'woo_vou_usedvoucodes' ) ) { 
			wp_enqueue_style( array( 'dashicons','admin-bar','common','forms','admin-menu','dashboard','list-tables','edit','revisions','media','themes','about','nav-menus','widgets','site-icon','wp-jquery-ui-dialog' ) );
			
		}
	}
	
	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for check code in admin
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_code_scripts( $hook_suffix ){
		
		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		
		if( $hook_suffix == $wc_screen_id . '_page_woo-vou-check-voucher-code' || $hook_suffix == $woo_vou_screen_id . '_page_woo-vou-check-voucher-code'|| $hook_suffix == $woo_vou_screen_id . '_page_woo-vou-codes' || $hook_suffix == $wc_screen_id . '_page_woo-vou-codes' || $hook_suffix == 'toplevel_page_woo-vou-codes' ) {
			
			// add css for check code in admin
			wp_register_style( 'woo-vou-check-code-style', WOO_VOU_URL . 'includes/css/woo-vou-check-code.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-check-code-style' );
			
			// Enqueue css for datepicker
			wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
			
			// Enqueue script for datepicker
			wp_enqueue_script( array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider' ) );
			
			wp_deregister_script( 'datepicker-slider' );
			wp_register_script( 'datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'datepicker-slider' );
			
			wp_deregister_script( 'timepicker-addon' );
			wp_register_script( 'timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true );
			wp_enqueue_script( 'timepicker-addon' );
			
			// add js for check code in admin
			wp_register_script( 'woo-vou-check-code-script', WOO_VOU_URL . 'includes/js/woo-vou-check-code.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-check-code-script' );
			
			wp_localize_script( 'woo-vou-check-code-script' , 'WooVouCheck' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																						'check_code_error' 	=> esc_html__( 'Please enter voucher code.', 'woovoucher' ),
																						'code_used_success'	=> esc_html__( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ),
																						'code_invalid' 		=> esc_html__( 'Voucher code doesn\'t not exist.', 'woovoucher' ),
																						'delete_code_confirm' 	=> esc_html__( 'Are you sure you want to delete this voucher code?', 'woovoucher' ),
																						'redeem_amount_empty_error' => esc_html__( 'Please enter redeem amount.', 'woovoucher' ),
																						'redeem_amount_greaterthen_redeemable_amount' => esc_html__( 'Redeem amount should not be greater then redeemable amount.', 'woovoucher' ),
																					) );
			
			// Enqueue woocommerce admin style for select2
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			// Enqueue woocommerce select2 script
			wp_enqueue_script( 'wc-enhanced-select' );
		}

		wp_register_script( 'woo-vou-plugin-updater-notice-script', WOO_VOU_URL . 'includes/js/woo-vou-plugin-updater-notice.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );

		wp_localize_script( 'woo-vou-plugin-updater-notice-script' , 'WooVouNotice' , array( 
			                                                                    'plugin_ver'	=>	WOO_VOU_PLUGIN_VERSION,
			                                                                    'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			                                                                ) );

		wp_register_script( 'woo-vou-admin-common-script', WOO_VOU_URL . 'includes/js/woo-vou-admin-common.js', array('jquery'), WOO_VOU_PLUGIN_VERSION );

		wp_localize_script( 'woo-vou-admin-common-script' , 'WooVouCom' , array( 
			                                                                    'plugin_ver'	=>	WOO_VOU_PLUGIN_VERSION,
			                                                                    'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			                                                                ) );
	}

	/**
	 * style on head of page
	 * 
	 * Handles style code display when wp head initialize 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_custom_styles() {
		
		//Get custom css code
		$custom_css	= get_option( 'vou_custom_css' );
		
		if( !empty( $custom_css ) )	{//if custom css code not available
			
			echo '<style type="text/css">' . $custom_css . '</style>';
		}
	}

    /**
	 * Remove success message
	 * 
	 * Handles to remove success message param from URL
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.7
	 */
    public function woo_vou_add_inline_scripts() {

    	// If parameter is set in URL
    	if ( isset ( $_GET['vou_success'] ) && $_GET['vou_success'] == 'true' ) {
		// Enqueue Script
		wp_register_script('woo_vou_success', WOO_VOU_URL . 'includes/js/woo_vou_success.js', array('jquery') );
		
		$woo_vou_success = [ 'woo_vou_success_admin_url' => admin_url() ];
		wp_localize_script('woo_vou_success', 'woo_vou_success', $woo_vou_success);
		
		wp_enqueue_script('woo_vou_success');
	    }
	    $messages = [
		    "woo_vou_gift_email_sent",
		    "woo_vou_recipient_details_changed",
		    "woo_vou_voucode_note_changed",
		    "woo_vou_voucher_information_changed"
	    ];
	    if ( isset ( $_GET['message'] ) && in_array($_GET['message'], $messages) ) {
			// Enqueue Script
			wp_register_script('woo_vou_message_success', WOO_VOU_URL . 'includes/js/woo_vou_message_success.js', array('jquery') );

			$new_url = remove_query_arg('message', $_SERVER['REQUEST_URI']);

			$woo_vou_message_success = array( "woo_vou_message_success_new_url" => $new_url );
			
		    wp_localize_script('woo_vou_message_success', 'woo_vou_message_success', $woo_vou_message_success);

			wp_enqueue_script('woo_vou_message_success');
	    }
    }

    /**
	 * Enqueue scripts for datepicker on settings page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.2.4
	 */
    public function woo_vou_setting_page_scripts( $hook_suffix ){


    	// If hook suffix consists our settings page
		if($hook_suffix == 'woocommerce_page_wc-settings') {

			// Enqueue for datepicker
			wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array() );
			
			// Enqueue for datepicker
			wp_enqueue_script(array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider'));
			
			wp_register_script('datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array('jquery') );
			wp_enqueue_script('datepicker-slider');
			
			wp_deregister_script( 'timepicker-addon' );
			wp_register_script('timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('jquery-ui-datepicker', 'datepicker-slider'));
			wp_enqueue_script('timepicker-addon');

			wp_register_script( 'woo-vou-admin-script', WOO_VOU_URL . 'includes/js/woo-vou-admin.js' );
			wp_enqueue_script( 'woo-vou-admin-script' );
		}
		if( $hook_suffix == 'admin_page_vou-upgrades'){
			wp_register_script( 'woo-vou-admin-upgrade-script', WOO_VOU_URL . 'includes/js/woo-vou-admin-upgrade.js', array('jquery'));

			wp_localize_script( 'woo-vou-admin-upgrade-script' , 'WooVouUpgrd' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) )
																						));
			wp_enqueue_script( 'woo-vou-admin-upgrade-script' );
		}

		if( $hook_suffix == 'dashboard_page_woo-vou-upgrades-voucher') {

			wp_register_script( 'woo-vou-admin-upgrade-voucher-script', WOO_VOU_URL . 'includes/js/woo-vou-admin-upgrade-vouchers.js', array('jquery'));

			wp_localize_script( 'woo-vou-admin-upgrade-voucher-script' , 'WooVouUpgrd' , array( 
																						'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) )
																						));
			wp_enqueue_script( 'woo-vou-admin-upgrade-voucher-script' );
		}
	}

	/**
	 * Dequeeue media scripts on voucher templates page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.2.6
	 */
	public function woo_vou_no_mediaelement_scripts(){

		// Get current screen
		$screen = get_current_screen();

		// If screen post type is of Voucher Template page
		if($screen->post_type == 'woovouchers') {

		}
	}

	/**
     * To resolve drag & drop with WordPress version >= 4.9.1
     * 
     * Reorder prototype.js to load only after woo-vou-admin-voucher-script
     * 
     * @package WooCommerce - PDF Vouchers
	 * @since 3.2.6
     */
    function woo_vou_wp_prototype_before_jquery( $js_array ) {
        
        if ( function_exists( 'get_current_screen' ) ) {

	        // Get current screen
			$screen = get_current_screen();
			
			// If screen post type is of Voucher Template page
			if( isset( $screen->post_type ) && $screen->post_type == 'woovouchers' ) {
	            
	            // if prototype not in array then return
	            if ( false === $prototype = array_search( 'prototype', $js_array, true ) )
	                return $js_array;

	            // if admin-voucher-script not in array then return
	            if ( false === $voucher_script = array_search( 'woo-vou-admin-voucher-script', $js_array, true ) )
	                return $js_array;

	            // if both script in array then check for order.
	            if ( $prototype > $voucher_script )
	                return $js_array;

	            unset($js_array[$prototype]);

	            array_splice( $js_array, $voucher_script, 0, 'prototype' );            
	        }
        }
        return $js_array;
    }

    /**
     * Handles to remove all theme styles except which needed for qrcode page
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.7.12
    */
	public function woo_vou_remove_all_theme_styles() {

		if( isset($_GET['woo_vou_code']) ){
			
			$allow = apply_filters("woo_vou_allow_style_qr_page",array('woo-vou-check-qrcode'));
			global $wp_styles;			
			$wp_styles->queue = $allow;
			
		}
	}

	/**
	 * Handles to remove all theme scripts except which needed for qrcode page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.7.12
	 */
	public function woo_vou_remove_all_theme_scripts() {

		if( isset($_GET['woo_vou_code']) ){
				
			$allow = apply_filters("woo_vou_allow_script_qr_page",array('woo-vou-check-qrcode'));		
			global $wp_scripts;						
			$wp_scripts->queue = $allow;
			
		}			
	}

	/**
	 * Adding Hooks
	 *
	 * Adding proper hoocks for the scripts.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add styles for new and edit post and purchased voucher code
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_popup_styles' ) );

		//add script for new and edit post and purchased voucher code
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_popup_scripts' ) );

		//add scripts for check code admin side
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_check_code_scripts' ), 11 );

		//drag & drop scripts for new and edit post
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_admin_drag_drop_scripts' ) );	

		if( woo_vou_is_edit_page() ) { // check metabox page
				
			//add styles for metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_metabox_styles' ) );
			
			//add styles for metaboxes
			add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_metabox_scripts' ) );			
		}

		//add scripts for check code front side
		add_action( 'wp_enqueue_scripts', array( $this, 'woo_vou_public_scripts' ) );

		//style code on wp head
		add_action( 'wp_head', array( $this, 'woo_vou_custom_styles' ) );

		// Add action to vou_success paramter
		add_action( 'admin_enqueue_scripts', array ( $this, 'woo_vou_add_inline_scripts' ) );

		// Add scripts for datepicker on admin settings page
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_setting_page_scripts' ), 11 );

		// Add filter to resolve javascript error with wp 4.9.1
		add_filter( 'print_scripts_array', array( $this, 'woo_vou_wp_prototype_before_jquery' ), 99 );

		add_action( 'wp_print_styles', array($this,'woo_vou_remove_all_theme_styles'), 999 );
		add_action( 'wp_print_scripts', array($this,'woo_vou_remove_all_theme_scripts'), 999 );
	}
}