<?php 
/**
 * Panel HTML Class
 *
 * To handles some small panel HTML content for backend
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.4.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class WOO_Vou_Admin_Meta { 
	
	public $model;
	public $voucher;
	
	function __construct() {
		
		global $woo_vou_model, $woo_vou_voucher;
		
		$this->model = $woo_vou_model;
		$this->voucher	= $woo_vou_voucher;		
	}
	
	/**
	 * WooCommerce custom product tab
	 * 
	 * Adds a new tab to the Product Data postbox in the admin product interface
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_write_panel_tab() {
		 
		$show = apply_filters('woo_vou_pdf_voucher_tab_show_in_product', true);

		if( $show ){		 
			echo "<li class=\"woo_vou_voucher_tab show_if_downloadable show_if_variable\"><a href=\"#woo_vou_voucher\"><span>" . esc_html__( 'PDF Vouchers', 'woovoucher' ) . "</span></a></li>";
		}
	}
	
	

	/**
	 * WooCommerce custom product tab data
	 * 
	 * Adds the panel to the Product Data postbox in the product interface
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_write_panel( $wcmp = false ) {
		
		$prefix = WOO_VOU_META_PREFIX;

		global $current_user, $woo_vou_vendor_role, $post;

		$post_id 			= apply_filters( 'woo_vou_edit_product_id', $post->ID, $post );
		$voucher_options 	= array( '' => esc_html__( 'Please Select', 'woovoucher' ) );
		$voucher_data 		= woo_vou_get_vouchers();
		foreach ( $voucher_data as $voucher ) {
			if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) { // Check voucher id is not empty
				$voucher_options[$voucher['ID']] = $voucher['post_title'];
				$multiple_voucher_options[$voucher['ID']] = $voucher['post_title'];
			}
		}
		
		$vendors_options = array( '' => esc_html__( 'Please Select', 'woovoucher' ) );
		
		if( !empty( $woo_vou_vendor_role ) ) {
			
			foreach ( $woo_vou_vendor_role as $vonder_role ) {
				
				$vendors_data = get_users( array( 'role' => $vonder_role ) );
				
				if( !empty( $vendors_data ) ) { // Check vendor users are not empty
					
					foreach ( $vendors_data as $vendors ) {
						
						$vendors_options[$vendors->ID] = $vendors->display_name . ' (#' . $vendors->ID . ' &ndash; ' . sanitize_email( $vendors->user_email ) . ')';
					}
				}
			}
		}

		$woo_vou_tab_options = array(
			'general' 		=> esc_html__( 'General', 'woovoucher' ),
			'vendor'		=> esc_html__( 'Vendor', 'woovoucher' ),
			'recipient'		=> esc_html__( 'Gift Voucher', 'woovoucher' ),
			'voutemplates'	=> esc_html__( 'Voucher Templates', 'woovoucher' ),
			'wccoupon'		=> esc_html__( 'Coupons', 'woovoucher' )
		);

		$discount_apply_options = apply_filters( 'woo_vou_product_coupon_discount_options', array(
			'' => esc_html__( 'Default', 'woovoucher' ),
			'subtotal' => esc_html__( 'Cart SubTotal', 'woovoucher' ),
			) );


		$based_on_purchase_opt  = array(
            '7' 		=> '7 Days',
            '15' 		=> '15 Days',
            '30' 		=> '1 Month (30 Days)',
            '90' 		=> '3 Months (90 Days)',
            '180' 		=> '6 Months (180 Days)',
            '365' 		=> '1 Year (365 Days)',
            'cust'		=> 'Custom',
        );
		
		$using_type_opt 		= array(
            '' 	=> esc_html__( 'Default', 'woovoucher' ), 
            '0' => esc_html__( 'One time only', 'woovoucher' ), 
            '1' => esc_html__( 'Unlimited', 'woovoucher' )
        );					

		$voucher_delivery_opt 	= array(
            'default' 	=> esc_html__( 'Default', 'woovoucher' ), 
            'email' 	=> esc_html__( 'Email', 'woovoucher' ), 
            'offline' 	=> esc_html__( 'Offline', 'woovoucher' )
        );		
        
        $voucher_preview_opt = $multiple_pdf_opt = $coupon_code_opt = array(
            '' 		=> esc_html__( 'Default', 'woovoucher' ), 
            'yes' 	=> esc_html__( 'Yes', 'woovoucher' ), 
            'no' 	=> esc_html__( 'No', 'woovoucher' )
        );
        
		
		
		// Product option for coupon use
		$coupon_products_opt  = array();
		$coupon_product_ids = array();
		
		if( !empty( $post_id ) ) {
			
			$coupon_include_product_ids = get_post_meta( $post_id, $prefix . 'coupon_products', true);
			$coupon_include_product_ids = !empty( $coupon_include_product_ids ) ? $coupon_include_product_ids : array();


			$coupon_exclude_product_ids = get_post_meta( $post_id, $prefix . 'coupon_exclude_products', true);
			$coupon_exclude_product_ids = !empty( $coupon_exclude_product_ids ) ? $coupon_exclude_product_ids : array();

			$coupon_product_ids = array_merge( $coupon_include_product_ids, $coupon_exclude_product_ids );

		}
		
		if ( !empty( $coupon_product_ids ) ) {

			// get product loop
			foreach( $coupon_product_ids as $p_key => $product_id ){
				
				$product_obj = wc_get_product( $product_id);
				
				if( !empty( $product_obj ) ) {
					$coupon_products_opt[ $product_id ] = $product_obj->get_formatted_name();
				}
			}
			
			wp_reset_postdata();
		}
		
		$coupon_products_opt = apply_filters('woo_vou_coupon_product_opt',$coupon_products_opt);
		// categories option for coupon use
		$coupon_categories_opt =	array();
		
		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => false,
		);
		$product_categories = get_terms( 'product_cat', $cat_args );
		if( !empty($product_categories) && is_array( $product_categories ) ){
			
			foreach( $product_categories as $cat_key => $cat_value ){
				
				$coupon_categories_opt[$cat_value->term_id] = $cat_value->name;
			}			
		}		
	
		// Voucher Code Error
		$vou_codes_error_class	= ' woo-vou-display-none ';
		$codes_error_msg		= '<br/><span id="woo_vou_codes_error" class="woo-vou-codes-error ' . $vou_codes_error_class . '">' . esc_html__( 'Please enter atleast 1 voucher code.', 'woovoucher' ) . '</span>';
		$exp_type_error_msg		= '<br/><span id="woo_vou_exp_type_error" class="woo-vou-exp-type-error ' . $vou_codes_error_class . '">' . esc_html__( 'Please enable Recipient Gift Date field from Gift voucher tab.', 'woovoucher' ) . '</span>';
		$days_error_msg			= '<span id="woo_vou_days_error" class="woo-vou-days-error ' . $vou_codes_error_class . '">' . esc_html__( ' Please enter valid days.', 'woovoucher' ) . '</span>';
		$website_url_error_msg	= '<br/><span id="woo_vou_website_url_error" class="woo-vou-website-url-error woo-vou-codes-error ' . $vou_codes_error_class . '">' . esc_html__( ' Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ) . '</span>';

		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );
		
		$vendor_flag = false;
		if(!empty($user_role) && in_array( $user_role, $woo_vou_vendor_role )) {  // Check vendor user role
			
			$vendor_flag = true;
		}	
		$vou_hide_vendor_options = get_option('vou_hide_vendor_options');

		$woo_vou_pro_start_end_date_format = apply_filters( 'woo_vou_pro_start_end_date_format', 'yy-mm-dd' );
		$woo_vou_vou_start_end_date_format = apply_filters( 'woo_vou_vou_start_end_date_format', 'yy-mm-dd' );

		// Get recipient detail order
		$recipient_detail_order = get_post_meta( $post_id, $prefix.'recipient_detail_order', true );
		$recipient_details 		= woo_vou_voucher_recipient_details();

		if( !empty( $recipient_detail_order ) ) {

			array_pop( $recipient_detail_order );
		} else {

			$recipient_detail_order = array();
		}

		// If recipient details are empty
		foreach( $recipient_details as $recipient_key => $recipient_val ) {

			if ( empty( $recipient_detail_order ) 
				|| ( !empty( $recipient_detail_order ) && !in_array( $prefix.'enable_'.$recipient_key, $recipient_detail_order ) ) ) {

				$recipient_detail_order[] = $prefix . 'enable_' . $recipient_key;
			}
		}

		// Else IF recipient delivery is empty
		// This will occur in case of old products as this feature is included after 3.3.4 version
		if ( !in_array( $prefix.'enable_recipient_delivery', $recipient_detail_order ) ) {

			$recipient_detail_order[] = $prefix.'enable_recipient_delivery';
		}

		// Apply filter to allow 3rd party extension to display other data
		$recipient_detail_order = apply_filters( 'woo_vou_recipient_detail_order', $recipient_detail_order );

		// Init variable for counter of recipient details
		$i = 1;
	
		// display the custom tab panel
		echo '<div id="woo_vou_voucher" class="panel wc-metaboxes-wrapper woocommerce_options_panel tabs-content hide-all">';

			// Voucher tabs
			$this->woo_vou_add_tabination( array( 'id' => $prefix . 'tabination', 'class' => 'woo-vou-add-tabination' , 'options' => $woo_vou_tab_options ) );
			
			//Enable Voucher Code
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable', 'field_type' => 'general', 'label' => esc_html__('Enable Voucher Codes:', 'woovoucher' ), 'description' => esc_html__( 'To enable the voucher for this product check the "Enable Voucher Codes" check box.', 'woovoucher' ) ) );
			
			//action to add setting here
			do_action( 'woo_vou_add_meta_setting_after_voucher_enable', $this );

			echo '<div class="woo-vou-recipient-detail-wraps" data-field-type="recipient">';

			foreach ( $recipient_detail_order as $recipient_data ) {

				$_recipient_data = str_replace( $prefix."enable_", "", $recipient_data );
				if( !empty( $recipient_details ) && array_key_exists( $_recipient_data, $recipient_details ) ) {

					$recipient_label = '';
					if( !empty( $recipient_details[$_recipient_data] ) 
						&& is_array( $recipient_details[$_recipient_data] )
						&& array_key_exists( 'label', $recipient_details[$_recipient_data] )
						&& !empty( $recipient_details[$_recipient_data]['label'] ) ) {

						$recipient_label = $recipient_details[$_recipient_data]['label'];
					}
					$this->woo_vou_add_recipient_details( array( 'id' => $recipient_data, 'field_type' => 'recipient', 'label' => $recipient_label, 'loop' => $i ) );
					do_action( 'woo_vou_add_meta_setting_after_'.$_recipient_data, $this );
				} else if ( $recipient_data == $prefix . 'enable_recipient_delivery' ) {

					$this->woo_vou_add_recipient_details( array( 'id' => $prefix . 'enable_recipient_delivery', 'field_type' => 'recipient', 'label' => esc_html__( 'Delivery Method', 'woovoucher' ), 'loop' => $i, 'class' => 'woo-vou-delivery-method' ) );
					do_action( 'woo_vou_add_meta_setting_after_recipient_delivery', $this );
				}

				// Do action to show custom recipient data from 3rd party addons
				do_action( 'woo_vou_custom_recipient_detail', $recipient_data, $i );
				$i += 1;
			}

			echo '</div>';
			
			//product start date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'product_start_date', 'field_type' => 'general', 'label' => esc_html__('Product Start Date:', 'woovoucher'), 'std' => array(''), 'description' => esc_html__('If you want to make the product valid for a specific time only, you can enter an start date here.', 'woovoucher'), 'format'=> $woo_vou_pro_start_end_date_format, 'placeholder' => 'YYYY-MM-DD H:I' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_product_start_date', $this );
			
			//product expiration date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'product_exp_date', 'field_type' => 'general', 'label' => esc_html__('Product End Date:', 'woovoucher'), 'std' => array(''), 'description' => esc_html__('If you want to make the product valid for a specific time only, you can enter an end date here.', 'woovoucher'), 'format'=> $woo_vou_pro_start_end_date_format, 'placeholder' => 'YYYY-MM-DD H:I' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_product_exp_date', $this );

			$default = sprintf(esc_html__('%sDefault:%s Discount will be applied as per default WooCommerce coupons.', 'woovoucher'), '<b>','</b>');
			$cart_subtotal = sprintf( esc_html__('%sCart Subtotal:%s Discount will be applied on cart subtotal.', 'woovoucher'), '<b>','</b>');
			// coupon discount option
			$this->woo_vou_add_select( array( 'id' => $prefix . 'discount_on_tax_type', 'field_type' => 'wccoupon','default' => '', 'label' => esc_html__('Discount on:', 'woovoucher' ),'options' => $discount_apply_options,'class' => 'wc-enhanced-select', 'style' => 'width:57%; min-width:200px;','description' => esc_html__( 'Select the option on which the discount will be applied.', 'woovoucher' ).'<br><br>'.$default.'<br>'.$cart_subtotal, ) );
			
			// Coupon products
			$this->woo_vou_add_select( array( 'id' => $prefix . 'coupon_products', 'field_type' => 'wccoupon', 'default' => '', 'style' => 'width:62%; min-width:200px;', 'class' => 'wc-product-search',  'options' => $coupon_products_opt, 'multiple' => true, 'label'=> esc_html__( 'Products:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'You can select the products on which you want to use coupon generated by this product.%s', 'woovoucher' ), '<br>' ), 'additional_label' => '<div class="woo-vou-coupon-products-sel-deselect"><a href="#" class="woo-vou-uncheck-all-coupon-products">' . esc_html__( 'Unselect All', 'woovoucher' ) . '</a></div>' ) );

			do_action( 'woo_vou_add_meta_setting_after_coupon_products', $this );

			// Coupon exclude products
			$this->woo_vou_add_select( array( 'id' => $prefix . 'coupon_exclude_products', 'field_type' => 'wccoupon', 'default' => '', 'style' => 'width:62%; min-width:200px; margin-left:200px;', 'class' => 'wc-product-search',  'options' => $coupon_products_opt, 'multiple' => true, 'label'=> esc_html__( 'Exclude Products:', 'woovoucher' ), 'description' => sprintf( esc_html__( "Select the products for which the voucher / coupon will not be applied to.%s", 'woovoucher' ),'<br>' ), 'additional_label' => '<div class="woo-vou-coupon-exclude-products-sel-deselect"><a href="#" class="woo-vou-uncheck-all-coupon-exclude-products">' . esc_html__( 'Unselect All', 'woovoucher' ) . '</a></div>' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_coupon_exclude_products', $this );
			
			//meru start
			// Coupon category
			
			$this->woo_vou_add_select( array( 'id' => $prefix . 'coupon_categories', 'field_type' => 'wccoupon', 'default' => '', 'style' => 'width:62%; min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $coupon_categories_opt, 'multiple' => true, 'label'=> esc_html__( 'Categories:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'You can select the categories on which you want to use coupon generated by this product.%s', 'woovoucher' ), '<br>' ), 'additional_label' => '<div class="woo-vou-coupon-categories-sel-deselect"><a href="#" class="woo-vou-uncheck-all-coupon-categories">' . esc_html__( 'Unselect All', 'woovoucher' ) . '</a></div>' ) );	
			
			$this->woo_vou_add_select( array( 'id' => $prefix . 'coupon_exclude_categories', 'field_type' => 'wccoupon', 'default' => '', 'style' => 'width:62%; min-width:200px; margin-left:200px;', 'class' => 'wc-enhanced-select ',  'options' => $coupon_categories_opt, 'multiple' => true, 'label'=> esc_html__( 'Exclude Categories:', 'woovoucher' ), 'description' => sprintf( esc_html__( "Select the categories for which the voucher / coupon will not be applied to.%s", 'woovoucher' ),'<br>' ), 'additional_label' => '<div class="woo-vou-coupon-exclude-categories-sel-deselect"><a href="#" class="woo-vou-uncheck-all-coupon-exclude-categories">' . esc_html__( 'Unselect All', 'woovoucher' ) . '</a></div>' ) );
			
			
			//Register setting for minimum spend amount in coupon tab
			$this->woo_vou_add_number( array( 'id' => $prefix . 'coupon_minimum_spend_amount',  'field_type' => 'wccoupon', 'class' => '', 'wrap_class' => '', 'label' => esc_html__( 'Minimum spend:', 'woovoucher' ), 'description' => esc_html__('This field allows you to set the minimum spend (subtotal) allowed to use the coupon.','woovoucher') ) );
			do_action( 'woo_vou_add_meta_setting_after_coupon_minimum_spend_amount', $this );	
			
			//Register setting for minimum spend amount in coupon tab
			$this->woo_vou_add_number( array( 'id' => $prefix . 'coupon_maximum_spend_amount', 'field_type' => 'wccoupon', 'class' => '', 'wrap_class' => '',  'label' => esc_html__( 'Maximum spend:', 'woovoucher' ), 'description' => esc_html__('This field allows you to set the maximum spend (subtotal) allowed when using the coupon.','woovoucher') ) );
			do_action( 'woo_vou_add_meta_setting_after_coupon_maximum_spend_amount', $this );	
			

			// Enable Template Selection
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_pdf_template_selection', 'field_type' => 'voutemplates', 'label' => esc_html__('Enable Template Selection:', 'woovoucher' ), 'description' => esc_html__( 'To enable the PDF template selection on the product page.', 'woovoucher' ) ) );
			echo '<div class="pdf-template-recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'pdf_template_selection_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '' ) );
				$this->woo_vou_add_select( array( 'id' => $prefix . 'pdf_template_selection', 'style' => 'width:96%; margin-bottom:20px;', 'class' => '_woo_vou_pdf_template_selection wc-enhanced-select', 'options' => $multiple_voucher_options, 'multiple' => true, 'label'=> esc_html__( 'Select PDF Template:', 'woovoucher' ), 'description' => '', 'sign' => '', 'additional_label' => '<div class="woo-vou-pdf-template-sel-deselect"><a class="woo-vou-check-all-templates" href="#">' . esc_html__( 'Select All', 'woovoucher' ) .'</a> / <a href="#" class="woo-vou-uncheck-all-templates">' . esc_html__( 'Unselect All', 'woovoucher' ) . '</a></div>' ) );
				$inline_desc_style = 'display: block; float: none; width: auto !important;';
				if( $wcmp == true){
					$inline_desc_style = '';
				}
				$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'pdf_selection_desc', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => $inline_desc_style, 'rows' => 2, 'cols' => '5' ) );
			echo '</div>';
			
			do_action( 'woo_vou_add_meta_setting_after_pdf_template_selection', $this );
			
			// if user is vendor and hide options set 
			if($vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
			
				//PDF Template
				$this->woo_vou_add_select( array( 'id' => $prefix . 'pdf_template', 'field_type' => 'voutemplates', 'default' => '', 'style' => 'width:45%; min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $voucher_options, 'label'=> esc_html__( 'PDF Template:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'Select a PDF template. This setting modifies the global PDF template setting and overrides vendor\'s PDF template value. Leave it empty to use the global/vendor settings.', 'woovoucher' ) ) ) );
				do_action( 'woo_vou_add_meta_setting_after_pdf_template', $this );
			}
			
			if( $vendor_flag == true ) {  // Check vendor user role
				if( function_exists('woocommerce_wp_hidden_input') ){
					woocommerce_wp_hidden_input( array( 'id' => $prefix . 'vendor_user', 'value' => $current_user->ID ));
				}
				
			} else {
			
				//Vendor User
   				$this->woo_vou_add_select( array( 'id' => $prefix . 'vendor_user', 'field_type' => 'vendor', 'default' => '', 'style' => 'width:45%; min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $vendors_options, 'label'=> esc_html__( 'Primary Vendor User:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'Please select the primary vendor user.', 'woovoucher' ) ) ) );

   				$sec_vendors_options	= $vendors_options;
   				unset( $sec_vendors_options[''] );

				//Secondary Vendor User
   				$this->woo_vou_add_select( array( 'id' => $prefix . 'sec_vendor_users', 'field_type' => 'vendor', 'style' => 'min-width:250px;max-width: 73.3%;width: 73.3%;', 'class' => 'wc-enhanced-select', 'options' => $sec_vendors_options, 'multiple' => true, 'label' => esc_html__( 'Secondary Vendor Users:', 'woovoucher' ), 'description' => esc_html__( 'Please select the secondary vendor users. You can select multiple users as secondary vendor users.', 'woovoucher' ), 'sign' => '' ) );
			}
			
			do_action( 'woo_vou_add_meta_setting_after_vendor_user', $this );

			$this->woo_vou_add_select( array( 'id' => $prefix . 'voucher_delivery', 'field_type' => 'general', 'default' => 'email', 'style' => 'width:45%; min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $voucher_delivery_opt, 'label'=> esc_html__( 'Voucher Delivery:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'Choose how your customer receives the "PDF Voucher" %sEmail%s - Customer receives "PDF Voucher" through email. %sOffline%s - You will have to send voucher through physical mode, via post or on-shop. %sThis setting modifies the global voucher delivery setting and overrides voucher\'s delivery value. Set delivery "%sDefault%s" to use the global/voucher settings.', 'woovoucher' ), '<br /><b>', '</b>', '<br /><b>', '</b>', '<br />', '<b>', '</b>' ) ) );
			do_action( 'woo_vou_add_meta_setting_after_voucher_delivery', $this );

			// Enable Voucher Preview
			$this->woo_vou_add_select( array( 'id' => $prefix . 'enable_pdf_preview', 'field_type' => 'general', 'default' => 'default', 'style' => 'width:45%; min-width:200px;', 'label' => esc_html__('Enable Voucher Preview:', 'woovoucher' ), 'class' => 'wc-enhanced-select', 'options' => $voucher_preview_opt, 'description' => esc_html__( 'Choose Yes / No to allow / disallow users to preview the voucher on product detail page before placing the order. Leave it empty to use global settings.', 'woovoucher' ) ) );
			do_action( 'woo_vou_add_meta_setting_after_enable_pdf_preview', $this );

			// Enable Coupon Code generation
			$this->woo_vou_add_select( array( 'id' => $prefix . 'enable_coupon_code', 'field_type' => 'general', 'default' => 'default', 'style' => 'width:45%; min-width:200px;', 'label' => esc_html__('Auto Coupon Code Generation:', 'woovoucher' ), 'class' => 'wc-enhanced-select', 'options' => $coupon_code_opt, 'description' => esc_html__( 'Choose Yes / No to allow / disallow coupon code generation when a voucher code gets generated. This will allow you to use voucher codes on online store. Leave it empty to use global settings.', 'woovoucher' ) ) );
			do_action( 'woo_vou_add_meta_setting_after_enable_coupon_code', $this );			
            
            // Enable Multiple Voucher generation
			$this->woo_vou_add_select( array( 'id' => $prefix . 'enable_multiple_pdf', 'field_type' => 'general', 'default' => 'default', 'style' => 'width:45%; min-width:200px;', 'label' => esc_html__('Enable 1 voucher per PDF:', 'woovoucher' ), 'class' => 'wc-enhanced-select', 'options' => $multiple_pdf_opt, 'description' => esc_html__( 'Choose Yes if you want to generate 1 PDF for 1 voucher code, choose No if you want to generate 1 combined PDF for all vouchers, choose Default to use global settings.', 'woovoucher' ) ) );
			do_action( 'woo_vou_add_meta_setting_after_enable_multiple_pdf', $this );
            
   			if($vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
   				
				//voucher's type to use it
				$this->woo_vou_add_select( array( 'id' => $prefix . 'using_type', 'field_type' => 'general', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $using_type_opt, 'label'=> __( 'Usability:', 'woovoucher' ), 'description' => sprintf( esc_html__( 'Choose how you wanted to use vouchers codes. %sIf you set usability "%sOne time only%s" then it will automatically set product quantity equal to a number of voucher codes entered and it will automatically decrease quantity  by 1 when it gets purchased. If you set usability "%sUnlimited%s" then the plugin will automatically generate unique voucher codes when the product purchased. %sThis setting modifies the global usability setting and overrides vendor\'s usability value. Set usability "%sDefault%s" to use the global/vendor settings.', 'woovoucher' ), '<br />', '<b>', '</b>', '<b>', '</b>', '<br />', '<b>', '</b>' ) ) );
				do_action( 'woo_vou_add_meta_setting_after_using_type', $this );
			}

			//voucher's code comma seprated
			$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'codes', 'field_type' => 'general', 'label' => esc_html__( 'Voucher Codes:', 'woovoucher' ),'description' => esc_html__( 'If you have a list of voucher codes you can copy and paste them into this option. Make sure, that they are comma separated.', 'woovoucher' ) . $codes_error_msg ) );
			
			do_action( 'woo_vou_add_meta_setting_after_codes', $this );
			
			//import to csv field
			$this->woo_vou_add_importcsv( array( 'id' => $prefix . 'import_csv', 'field_type' => 'general', 'btntext' => esc_html__( 'Generate / Import Codes', 'woovoucher' ), 'label' => esc_html__( 'Generate / Import Codes:', 'woovoucher' ), 'description' => esc_html__( 'Here you can import a CSV file with voucher codes or you can enter the prefix, pattern and extension will automatically create the voucher codes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_import_csv', $this );
			
			//purchased voucher codes field
			$this->woo_vou_add_purchasedvoucodes( array( 'id' => $prefix . 'purchased_codes', 'field_type' => 'general', 'btntext' => esc_html__( 'Unredeemed Voucher Codes', 'woovoucher' ), 'label' => esc_html__( 'Unredeemed Voucher Code:', 'woovoucher' ), 'description' => esc_html__( 'Click on the button to see a list of all unredeemed voucher codes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_purchased_codes', $this );
			
			//used voucher codes field
			$this->woo_vou_add_usedvoucodes( array( 'id' => $prefix . 'used_codes', 'field_type' => 'general', 'btntext' => esc_html__( 'Redeemed Voucher Codes', 'woovoucher' ), 'label' => esc_html__( 'Redeemed Voucher Code:', 'woovoucher' ), 'description' => esc_html__( 'Click on the button to see a list of all redeemed voucher codes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_used_codes', $this );

			$this->woo_vou_add_unusedvoucodes( array( 'id' => $prefix . 'unused_codes', 'field_type' => 'general', 'btntext' => esc_html__( 'Expired Voucher Codes', 'woovoucher' ), 'label' => esc_html__( 'Expired Voucher Code:', 'woovoucher' ), 'description' => esc_html__( 'Click on the button to see a list of all expired voucher codes.', 'woovoucher' ) ) );

			do_action( 'woo_vou_add_meta_setting_after_unused_codes', $this );
			
			//voucher uses limit
			do_action( 'woo_vou_add_meta_setting_before_uses_limit', $this );
			
			$is_unlimted_redeem = get_option('vou_allow_unlimited_redeem_vou_code');
			$is_partial_redeem = get_option('vou_enable_partial_redeem');

			if( !empty( $is_unlimted_redeem ) && $is_unlimted_redeem == 'yes' && $is_partial_redeem != 'yes' ) {

				$this->woo_vou_add_text( array( 'id' => $prefix . 'voucher_uses_limit', 'class' => 'voucher_users_limit', 'wrap_class' => 'voucher_users_limit', 'label' => __( 'Voucher Usage Limit:', 'woovoucher' ), 'description' => 'This sets the number of times the same voucher code can be used. Leave it empty to use global settings.' . $days_error_msg, 'placeholder' => esc_html__('', 'woovoucher') ) );
			}
			
			do_action( 'woo_vou_add_meta_setting_after_uses_limit', $this );
			
			
			
			//voucher expiration date type			
			$expdate_types = apply_filters('woo_vou_exp_date_types', array( 'default' => __( 'Default', 'woovoucher' ), 'specific_date' => esc_html__( 'Specific Time', 'woovoucher' ), 'based_on_purchase' => esc_html__( 'Based on Purchase', 'woovoucher' ), 'based_on_gift_date' => esc_html__( 'Based on Recipient Gift Date', 'woovoucher' ) ));
			
			$this->woo_vou_add_select( array( 'id' => $prefix . 'exp_type', 'field_type' => 'general', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select', 'options' => $expdate_types, 'default'=> array( 'specific_date' ), 'label'=> esc_html__( 'Expiration Date Type:', 'woovoucher' ),
			 'description' => sprintf( esc_html__( 'Please select expiration date type either a %sSpecific Time%s, %sBased on Purchased%s voucher date or %sBased on Recipient Gift Date%s like after 7 days, 30 days, 1 year etc. %sThis setting modifies the global voucher expiration date setting and overrides voucher\'s expiration date value. Set expiration date type "%sDefault%s" to use the global/voucher settings. %s', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>','<b>','</b>','<br />', '<b>', '</b>', $exp_type_error_msg ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_exp_type', $this );
			
			//
			$this->woo_vou_add_select( array( 'id' => $prefix . 'days_diff', 'field_type' => 'general', 'style' => 'min-width:200px;', 'class' => '_woo_vou_days_diff wc-enhanced-select', 'options' => $based_on_purchase_opt, 'label'=> esc_html__( 'Expiration Days:', 'woovoucher' ), 'description' => '', 'sign' => esc_html__( ' After Purchase or Recipient Gift Date', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_days_diff', $this );
			
			//voucher expiration date custom days
			$this->woo_vou_add_custom_text( array( 'id' => $prefix . 'custom_days', 'field_type' => '', 'class' => 'custom-days-text', 'label' => esc_html__( 'Custom Days:', 'woovoucher' ), 'description' => esc_html__( 'If you leave it empty then voucher will never expire.', 'woovoucher' ). $days_error_msg  , 'sign' => esc_html__( ' Days after purchase', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_custom_days', $this );
			
			//voucher start date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'start_date', 'field_type' => 'general', 'label' => esc_html__('Voucher Start Date:', 'woovoucher'),'std' => array(''),'description' => esc_html__('If you want to make the voucher codes valid for a specific time only, you can enter a start date here.', 'woovoucher'),'format'=> $woo_vou_vou_start_end_date_format, 'placeholder' => 'YYYY-MM-DD H:I' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_start_date', $this );
			
			//voucher expiration date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'exp_date', 'field_type' => 'general', 'label' => esc_html__('Voucher Expiration Date:', 'woovoucher'),'std' => array(''),'description' => esc_html__('If you want to make the voucher codes valid for a specific time only, you can enter a expiration date here. If the Voucher Code never expires, then leave that option blank.', 'woovoucher'),'format'=> $woo_vou_vou_start_end_date_format, 'placeholder' => 'YYYY-MM-DD H:I' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_exp_date', $this );
			
			//disable redeem voucher
			$redeem_days = array( 
				'Monday' => esc_html__( 'Monday', 'woovoucher' ), 
				'Tuesday' => esc_html__( 'Tuesday', 'woovoucher' ), 
				'Wednesday' => esc_html__( 'Wednesday', 'woovoucher' ),
				'Thursday' => esc_html__( 'Thursday', 'woovoucher' ), 
				'Friday' => esc_html__( 'Friday', 'woovoucher' ),
				'Saturday' => esc_html__( 'Saturday', 'woovoucher' ),
				'Sunday' => esc_html__( 'Sunday', 'woovoucher' )
			);
			
			$this->woo_vou_add_multiple_checkbox( array( 'id' => $prefix . 'disable_redeem_day', 'field_type' => 'general','options' => $redeem_days, 'default'=> array( 'monday' ), 'label'=> esc_html__( 'Choose Which Days Voucher cannot be Used:', 'woovoucher' ), 'description' => esc_html__( 'If you want to restrict  use of voucher codes  for specific days, you can select days here. Leave it blank for no restriction. ', 'woovoucher' ) ) );

			do_action( 'woo_vou_add_meta_setting_after_disable_redeem_day', $this );
			
			$this->woo_vou_text_input( array( 'id' => $prefix.'is_variable_voucher', 'field_type' => '', 'wrap_class' => 'woo_vou_is_variable_voucher hidden dokan-hidden', 'label' => esc_html__( 'Is Voucher Set at Variation:', 'woovoucher' ), 'description' => esc_html__( 'Whether voucher codes are entered for variations?', 'woovoucher' ) ) );

			if( $vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
				
				//add the vendor's logo
				$this->woo_vou_add_image( array( 'id' => $prefix . 'logo', 'field_type' => 'vendor', 'label' => esc_html__( 'Vendor\'s Logo:', 'woovoucher' ), 'description' => esc_html__( 'Allows you to upload a logo of the vendor for which this voucher is valid. The logo will also be displayed on the PDF document. Leave it empty to use the vendor logo from the vendor settings.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_logo', $this );
				
				//vendor's address
				$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'address_phone', 'field_type' => 'vendor', 'label' => esc_html__( 'Vendor\'s Address:', 'woovoucher' ),'description' => esc_html__( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF voucher sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_address_phone', $this );
				
				//vendor's website
				$this->woo_vou_text_input( array( 'id' => $prefix . 'website', 'field_type' => 'vendor', 'class' => 'woo_vou_siteurl_text', 'wrap_class' => '', 'label' => esc_html__( 'Website URL:', 'woovoucher' ), 'description' => esc_html__( 'Enter the vendor\'s website URL here. This will be displayed on the PDF document sent to the customer. Leave it empty to use website URL from the vendor settings.', 'woovoucher' ).$website_url_error_msg ) );
				
				
				do_action( 'woo_vou_add_meta_setting_after_website', $this );
				
				//using instructions of voucher
				$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'how_to_use', 'field_type' => 'vendor', 'label' => esc_html__( 'Redeem Instructions:', 'woovoucher' ),'description' => esc_html__( 'Within this option, you can enter instructions on how this Voucher can be redeemed. This instruction will be displayed on the PDF voucher sent to the customer after successful purchase. Limited HTML is allowed.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_how_to_use', $this );
				
				//location fields
				$voucherlocations	= apply_filters( 'woo_vou_add_meta_location_fields', array( 
												'0'	=>	array( 'id' => $prefix. 'locations', 'class' => 'woo_vou_location', 'label'=> esc_html__( 'Location:', 'woovoucher' ), 'description' => esc_html__( 'Enter the address of the location where the voucher code can be redeemed. This will be displayed on the PDF document sent to the customer. Limited HTML is allowed.', 'woovoucher' )),
												'1'	=>	array( 'id' => $prefix. 'map_link', 'class' => 'woo_vou_location', 'label'=> esc_html__( 'Location Map Link:', 'woovoucher' ), 'description' => esc_html__( 'Enter a link to a Google Map for the location here. This will be displayed on the PDF document sent to the customer.', 'woovoucher' ))
											) );
				
				//locations for voucher block is available
				$this->woo_vou_add_repeater_block( array( 'id' => $prefix. 'avail_locations', 'field_type' => 'vendor', 'label' => esc_html__( 'Locations:', 'woovoucher' ), 'description' => esc_html__( 'If the vendor of the voucher has more than one location where the voucher can be redeemed, then you can add all the locations within this option. Leave it empty to use locations from the vendor settings.', 'woovoucher' ), 'fields' => $voucherlocations ) );
				
				do_action( 'woo_vou_add_meta_setting_after_location', $this );
			}
		echo '</div>';
	}
	
	
	/**
	 * Show text Field.
	 *
	 * @param string $args
	 * @param string $echo 
	 * @since 2.8.2
	 * @access public
	 */
	function woo_vou_text_input( $args, $echo = true ){

		$html = '';	
		
		$new_field = array( 'type' => 'text', 'name' => 'Text Field', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );		
		
		$html = '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . $field['wrap_class'] . '" data-field-type="' . $field['field_type'] . '">';
		$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';		
		$html .= '<input type="text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="'. esc_attr( $meta ) . '" > ';

		if ( ! empty( $field['description'] ) ) {
	
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$html .= wc_help_tip( $field['description'] );
			} else {
				$html .= '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		$html.= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}	
		
	}	
	
	/**
	 * Show textarea Field.
	 *
	 * @param string $args
	 * @param string $echo 
	 * @since 2.8.2
	 * @access public
	 */
	function woo_vou_add_textarea_input( $args, $echo = true ) {

		$html = '';	

		$new_field = array( 'type' => 'textarea', 'name' => 'Textarea Field', 'field_type' => 'general', 'label_style' => '', 'rows' => 2, 'cols' => 20 );
		$field = array_merge( $new_field, $args );

		$meta = woo_vou_meta_value( $field );

		$html = '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . '" data-field-type="' . $field['field_type'] . '">';
		$html .= '<label style="' . $field['label_style'] . '" for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		$html .= '<textarea name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" rows="' . $field['rows'] . '" cols="' . $field['cols'] . '" >' . esc_attr( $meta ) . '</textarea> ';

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$html .= wc_help_tip( $field['description'] );
			} else {
				$html .= '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		$html.= '</p>';

		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	/**
	 * Show multiple Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 2.7.2
	 * @access public
	 */
	function woo_vou_add_multiple_checkbox($args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'checkbox', 'name' => 'Radio Field', 'field_type' =>'general', 'disabled' => array() );
		$field = array_merge( $new_field, $args );
		
		$default_meta = isset( $field['default'] ) ? $field['default'] : '';
		
		$meta = woo_vou_meta_value( $field );
		$meta = !empty( $meta ) ? $meta : $default_meta;
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}

		$html .= '<div class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-custom-meta-label woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label><div class="woo-vou-multi-check-box-wrapper">';

		foreach ( $field['options'] as $key => $value ) {
			$html .= "<div class='woo-vou-multi-checkbox-instance'>";
			$html .= "<input type='checkbox' id='{$field['id']}_{$key}' class='woo-vou-meta-radio' name='{$field['id']}[]' value='{$key}'";

			$html .= checked( in_array( $key, $meta ), true, false ) . " /> <label for='{$field['id']}_{$key}' class='woo-vou-meta-multi-checkbox-label woo_vou_radio'>{$value}</label></div>";
		}

		$html .= '</div>';
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</div>';
		
		$html = apply_filters( 'woo_vou_multiple_checkbox_html', $html, $args );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	 }

	/**
	 * Show Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0
	 * @access public
	 */
	function woo_vou_add_checkbox( $args, $echo = true ) {

		$html = '';

		$new_field = array( 'type' => 'checkbox', 'name' => 'Checkbox Field', 'field_type' => 'general', 'default' => false, 'option_name' => '', 'key_name' => '' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );

		if( empty( $meta ) ) {
			$meta = $field['default'];
		}

		
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section woo-vou-meta-checkbox-label" for="' . $field['id'] . '">' . $field['label'] . '</label>';

		$html .= "<input type='checkbox' class='woo-vou-meta-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked((!empty($meta) && $meta == 'yes'), true, false) . " />";

		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';

		$html .= '</p>';

		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0
	 * @access public
	 */
	function woo_vou_add_cust_checkbox( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'checkbox', 'name' => 'Checkbox Field', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<div class="form-field ' . $field['id'] . '_field woo-vou-inline-block-section" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-cust-checkbox-label" for="' . $field['id'] . '"><span>' . $field['label'] . '</span></label>';
		
		$html .= "<input type='checkbox' class='woo-vou-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description"></span>';
			
		$html .= '</div>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Image Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_image( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'image', 'name' => 'Image Field', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= wp_nonce_field( "woo-vou-meta-delete-mupload_{$field['id']}", "nonce-delete-mupload_".$field['id'], false, false );
		
		$meta = woo_vou_meta_value( $field );
		
		if( is_array( $meta ) ) {
			if( isset( $meta[0] ) && is_array( $meta[0] ) ) {
				$meta = $meta[0];
			}
		}
		
		if( is_array( $meta ) && isset( $meta['src'] ) && $meta['src'] != '' ) {
			$html .= "<span class='mupload_img_holder'><img src='".esc_url($meta['src'])."' /></span>";
			$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='".$meta['id']."' />";
			$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='".$meta['src']."' />";
			$html .= "<input class='woo-vou-meta-delete_image_button button-secondary' type='button' rel='".$field['id']."' value='" . esc_html__( 'Delete Image', 'woovoucher' ) . "' />";
		} else {
			$html .= "<span class='mupload_img_holder'></span>";
			$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='' />";
			$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='' />";
			$html .= "<input class='woo-vou-meta-upload_image_button button-secondary' type='button' rel='".$field['id']."' value='" . esc_html__( 'Upload Image', 'woovoucher' ) . "' />";
		}
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Import CSV.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_importcsv( $args, $echo = true ) {  
		
		$html = '';
		
		$new_field = array( 'type' => 'importcsv','name' => __( 'Import Voucher Codes Field', 'woovoucher' ), 'field_type' => 'general');
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
	
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-import-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Purchased Voucher Code.
	 *
	 * @since 1.0.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_purchasedvoucodes( $args, $echo = true ) {  
		
		global $post, $woo_vou_render;
		
		$html = '';
		
		$new_field = array( 'type' => 'purchasedvoucodes','name' => esc_html__( 'Unredeemed Voucher Codes Field', 'woovoucher' ), 'field_type' => 'general');
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-purchased-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		$html .= $woo_vou_render->woo_vou_purchased_codes_popup( $post->ID );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Used Voucher Code.
	 *
	 * @since 1.1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_usedvoucodes( $args, $echo = true ) {  
		
		global $post, $woo_vou_render;
		
		$html = '';
		
		$new_field = array( 'type' => 'usedvoucodes','name' => esc_html__( 'Redeemed Voucher Codes Field', 'woovoucher' ), 'field_type' => 'general');
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-used-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		$html .= $woo_vou_render->woo_vou_used_codes_popup( $post->ID );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Show Field Unused Voucher Code.
	 *
	 * @since 1.1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_unusedvoucodes( $args, $echo = true ) {  

		global $post, $woo_vou_render;
		
		$html = '';
		
		$new_field = array( 'type' => 'unusedvoucodes','name' => esc_html__( 'Expired Voucher Codes Field', 'woovoucher' ), 'field_type' => 'general');

		$field = array_merge( $new_field, $args );

		$meta = woo_vou_meta_value( $field );

		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';

		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-unused-data button-secondary">';

		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';

		$html .= '</p>';

		$html .= $woo_vou_render->woo_vou_unused_codes_popup( $post->ID );

		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Radio Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_radio( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'radio', 'name' => 'Radio Field', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );
		
		$default_meta = isset( $field['default'] ) ? $field['default'] : '';
		
		$meta = woo_vou_meta_value( $field );
		$meta = !empty( $meta ) ? $meta : $default_meta;
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}
	  
		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		foreach ( $field['options'] as $key => $value ) {
			$html .= "<input type='radio' id='{$field['id']}_{$key}' class='woo-vou-meta-radio' name='{$field['id']}' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> <label for='{$field['id']}_{$key}' class='woo-vou-meta-radio-label woo_vou_radio'>{$value}</label>";
		}
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show select box Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_select( $args, $echo = true ) {

		$prefix = WOO_VOU_META_PREFIX;		
		global $pagenow;

		$html = '';
		
		$new_field = array( 'type' => 'select', 'name' => 'Select Field', 'multiple' => false, 'style' => '', 'default' => '', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );

		$meta = woo_vou_meta_value( $field );
		if(empty($meta) && !empty($field['default'])){
			$meta = $field['default'];	
		}
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}

		if ( !empty ( $field['additional_label'] ) ) {
			$label_class = ' woo-vou-custom-select-label';
		} else {
			$label_class = ' woo-vou-inline-block-section';
		}
		
		$html .= '<div class="'. $field['id'] . '_field vou_clearfix woo_vou_custom_select_field" data-field-type="' . $field['field_type'] . '"> <label class="woo-vou-custom-meta-label ' . $label_class . '" for="' . $field['id'] . '"> ' . $field['label'] . '</label>';

		if ( !empty ( $field['additional_label'] ) ) {

			$html .= $field['additional_label'];
		}

		$html .= "<select id='{$field['id']}' class='woo-vou-meta-select {$field['class']} ".($field['multiple'] ? 'woo-vou-meta-multiple-select' : 'woo-vou-meta-single-select')."' name='{$field['id']}" . ( $field['multiple'] ? "[]' multiple='multiple'" : "'" ) . " style='" . esc_attr( $field['style'] ) . "'>";
		
		foreach ( $field['options'] as $key => $value ) {
			if( $field['id']  == $prefix.'using_type' &&  $pagenow == 'post-new.php'){				
				$html .= "<option value='{$key}'" . selected( in_array(' ', $meta ), true, false ) . ">{$value}</option>";
			}else{				
				$html .= "<option value='{$key}'" . selected( in_array( $key, $meta ), true, false ) . ">{$value}</option>";
			}
		}
		
		$html .= "</select>";	
		
		if ( isset( $field['sign'] ) && $field['sign'] )
			$html .= "<span class='custom-desc woo-vou-select-custom-desc'>{$field['sign']}</span>";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show custom text
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_custom_text( $args, $echo = true ) {  
		
		$html = '';
		
		$new_field = array( 'type' => 'text', 'name' => 'Text Field', 'field_type' => 'general' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<div class="'. $field['id'] . '_field" data-field-type="' . $field['field_type'] . '">';
		
		$html .= "<input type='text' onkeypress='return woo_vou_is_number_key_per_page(event)' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' /> {$field['sign']}";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description description-custom">' . $field['description'] . '</span>';
		
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show custom text
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_text( $args, $echo = true ) {  
		
		$html = '';

		$new_field = array( 'type' => 'text', 'name' => 'Text Field', 'field_type' => 'general', 'placeholder' => '' );
		$field = array_merge( $new_field, $args );

		$meta = woo_vou_meta_value( $field );

		$html .= '<div class="'. $field['id'] . '_field '. $field['wrap_class'] . ' woo-vou-inline-block-section" data-field-type="' . $field['field_type'] . '">';
		$html .= '<label class="woo-vou-cust-text-label" for="' . $field['id'] . '">' . $field['label'] . '</label>';

		$html .= "<input type='text' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' placeholder='{$field['placeholder']}' />";

		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description description-custom">' . $field['description'] . '</span>';
		
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	
	/**
	 * Show custom number
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_number( $args, $echo = true ) {  
		
		$html = ''; 

		$new_field = array( 'type' => 'number', 'name' => 'Text Field', 'field_type' => 'general', 'placeholder' => '' );
		$field = array_merge( $new_field, $args );

		$meta = woo_vou_meta_value( $field );

		$html .= '<div class="'. $field['id'] . '_field '. $field['wrap_class'] . ' woo-vou-inline-block-section" data-field-type="' . $field['field_type'] . '">';
		$html .= '<label class="woo-vou-custom-meta-label  woo-vou-custom-select-label" for="' . $field['id'] . '">' . $field['label'] . '</label>';

		$html .= "<input type='number' min='1' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' placeholder='{$field['placeholder']}' />";

		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description description-custom">' . $field['description'] . '</span>';
		
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Date Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_datetime( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array('type' => 'datetime','format'=>'d MM, yy','name' => 'Date Time Field', 'field_type' => 'general', 'placeholder' => 'YYYY-MM-DD H:I A');
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		if(isset($meta) && !empty($meta) && !is_array($meta)) { //check datetime value is set & not array & not empty
			$meta = date('Y-m-d H:i',strtotime($meta));
		} else {
			$meta = '';
		};

		$html .= '<p class="form-field ' . $field['id'] . '_field" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
	
		$html .= "<input type='text' class='woo-vou-meta-datetime' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' placeholder='{$field['placeholder']}' />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Add Repeater Block
	 * 
	 * Handles to add repeater block
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_add_repeater_block( $args, $echo = true ) {
		
		global $post,$woo_vou_model;
		
		$new_field = array( 'type' => 'repeater', 'id'=> $args['id'], 'name' => 'Reapeater Field', 'fields' => array(), 'field_type' => 'general' );
		
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html = '';
		
		$html .= '<p class="form-field ' . $field['id'] . '_field woo_vou_repeater" data-field-type="' . $field['field_type'] . '"><label class="woo-vou-block-section" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= "<div class='woo-vou-meta-repeat' id='{$field['id']}' data-field-type='" . $field['field_type'] . "'>";
		
		if( !empty( $meta ) && count( $meta ) > 0 ) {
			
			$row = '';
			
			for ( $i = 0; $i < ( count ( $meta ) ); $i++ ) {
			
				$row .= "	<div class='woo-vou-meta-repater-block'>
								<table class='repeater-table form-table'>
									<tbody>";
				
				for ( $k = 0; $k < count( $field['fields'] ); $k++ ) {
					
					$row .= '<p class="form-field ' . $field['fields'][$k]['id'] . '_field"><label class="woo-vou-block-section" for="' . $field['fields'][$k]['id'] . '">' . $field['fields'][$k]['label'] . '</label>';
					
					if ( !empty ( $field['fields'][$k]['type'] ) ) {
						$row .= apply_filters( 'woo_vou_replace_meta_field_type', $field['fields'][$k], $woo_vou_model->woo_vou_escape_attr( $meta[$i][$field['fields'][$k]['id']] ) );
					} else {
						$row .= "<input type='text' name='{$field['fields'][$k]['id']}[]' class='woo-vou-meta-text regular-text woo-vou-repeater-text' value='{$woo_vou_model->woo_vou_escape_attr( $meta[$i][$field['fields'][$k]['id']] )}'/>";
					}
					
					if ( ! empty( $field['fields'][$k]['description'] ) ) {
						$row .=  '<span class="description">' . wp_kses_post( $field['fields'][$k]['description'] ) . '</span>';
					}
					
					$row .=  '</p>';
					
				}
				
				$row .= "			</tbody>
								</table>";
				if( $i > 0 ) {
					$showremove = "woo-vou-block-section";
				} else {
					$showremove = "woo-vou-hide-section";
				}
				
				$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove ".$showremove."' title='".esc_html__('Remove', 'woovoucher')."' alt='".esc_html__('Remove', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/remove.png'>";
				
				$row .= "		</div><!--.woo-vou-meta-repater-block-->";
				
			}
			$html .= $row;
			
		} else {
			
			$row = '';
			$row .= "	<div class='woo-vou-meta-repater-block'>
								<table class='repeater-table form-table'>
									<tbody>";
					
					for ( $i = 0; $i < count ( $field['fields'] ); $i++ ) {
						
						$row .= '<p class="form-field ' . $field['fields'][$i]['id'] . '_field"><label class="woo-vou-block-section" for="' . $field['fields'][$i]['id'] . '">' . $field['fields'][$i]['label'] . '</label>';
					
						
						if ( !empty ( $field['fields'][$i]['type'] ) ) {
							$row .= apply_filters( 'woo_vou_replace_meta_field_type', $field['fields'][$i] );
						} else {
							
							$row .= "	<input type='text' name='{$field['fields'][$i]['id']}[]' class='woo-vou-meta-text regular-text woo-vou-repeater-text'/>";
						}
						
						if ( ! empty( $field['fields'][$i]['description'] ) ) {
						$row .=  '<span class="description">' . wp_kses_post( $field['fields'][$i]['description'] ) . '</span>';
					}
					
					$row .=  '</p>';
						
					}
					
				$row .= "		</tbody>
							</table>";
					
				$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove woo-vou-hide-section' title='".esc_html__('Remove', 'woovoucher')."' alt='".esc_html__('Remove', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/remove.png'>";
				
				$row .= "		</div><!--.woo-vou-meta-repater-block-->";
			
			$html .= $row;
			
		}
		
		$html .= "	<img id='add-{$args['id']}' class='woo-vou-repeater-add' title='".__( 'Add','woovoucher')."' alt='".esc_html__( 'Add', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/add.png'>";
		
		$html .= "	</div><!--.woo-vou-meta-repeat-->";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description" data-field-type="' . $field['field_type'] . '">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Add tabbing options
	 * 
	 * Handles to add tabbing
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.2
	 */
	public function woo_vou_add_tabination( $args, $echo = true ){

		$html = '';

		if( !empty( $args['options'] ) ) {

			$i = 1;
			$html .= '<ul class="'. $args['class'] . ' woo-vou-inline-block-section">';

			foreach ( $args['options'] as $option_key => $option_val ) {

				$html .= '<li class="woo-vou-tab-' . $option_key . ' woo-vou-tabination-wrapper';
				if( $i == 1 ) {
					$html .= ' woo-vou-tab-active';
				}
				$html .= '"><a class="woo-vou-tab-' . $option_key . '-link" href="#" data-show-info="' . $option_key . '">' . $option_val . '</a></li>';
				$i++;
			}
			$html .= '</ul>';
		}

		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Add recipient details options
	 * 
	 * Handles to add recipient details
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.2
	 */
	public function woo_vou_add_recipient_details( $args, $echo = true ){

		$prefix = WOO_VOU_META_PREFIX;

		$vou_codes_error_class	= ' woo-vou-display-none ';
		$days_error_msg			= '<span id="woo_vou_days_error" class="woo-vou-days-error ' . $vou_codes_error_class . '">' . esc_html__( ' Please enter valid days.', 'woovoucher' ) . '</span>';
		$delivery_methods		= woo_vou_voucher_delivery_methods();
		$recipient_details		= woo_vou_voucher_recipient_details();
		$_recipient_details		= array();

		if( !empty( $recipient_details ) ) {

			foreach( $recipient_details as $recipient_key => $recipient_val ) {

				if( is_array( $recipient_val ) && array_key_exists( 'label', $recipient_val ) ) {
					$_recipient_details[$recipient_key] = $recipient_val['label'];
				}
			}
		}

		$html = '';

		$new_field = array( 'name' => 'Date Time Field', 'field_type' => 'general', 'label' => esc_html__( 'Recipient Name', 'woovoucher' ), 'class' => '' );
		$field = array_merge( $new_field, $args );

		ob_start();
		?>
		<!-- HTML for creating discount category on product meta settings html starts -->
		<div class="woo-vou-recipient-detail wc-metabox closed <?php echo $field['class']; ?>" data-field-type="<?php echo $field['field_type']; ?>">
		    <!-- HTML for accordian -->
		    <h3>
		        <div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'woovoucher'); ?>"></div>
		        <div class="tips sort" data-tip="<?php esc_attr_e( 'Drag and drop, or click to set admin variation order', 'woocommerce' ); ?>"></div>
		        <strong class="woo-vou-recipient-label"><?php echo esc_html($field['label']); ?></strong>
		        <input type="hidden" class="woo_vou_recipient_detail_order" name="<?php echo $prefix; ?>recipient_detail_order[<?php echo $field['loop']; ?>]" value="<?php echo $field['id']; ?>" />
		    </h3>
		    <!-- HTML for data inside accordian -->
		    <div class="woo-vou-data wc-metabox-content">

		    <?php

		    	if( $field['id'] == $prefix.'enable_recipient_name' ) {

			    	//Recipient Name Detail
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_name', 'field_type' => 'recipient', 'label' => esc_html__('Enable Recipient Name:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient name on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-detail-wrap">';
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_name_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => '' . $days_error_msg, 'placeholder' => esc_html__('Recipient Name', 'woovoucher') ) );
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_name_max_length', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Max Length:', 'woovoucher' ), 'description' => '' . $days_error_msg ) );
						$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_name_is_required', 'label' => esc_html__('Required:', 'woovoucher' ), 'description' => esc_html__( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );						
						$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_name_desc', 'field_type' => 'recipient', 'label' => __( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5' ) );
					echo '</div>';
		    	} else if ( $field['id'] == $prefix.'enable_recipient_email' ) {

		    		//Recipient Email Detail
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_email', 'field_type' => 'recipient', 'label' => esc_html__('Enable Recipient Email:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient email on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-detail-wrap">';
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_email_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '' . $days_error_msg, 'placeholder' => esc_html__('Recipient Email', 'woovoucher') ) );
						$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_email_is_required', 'label' => esc_html__('Required:', 'woovoucher' ), 'description' => esc_html__( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
						$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_email_desc', 'field_type' => 'recipient', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5' ) );
					echo '</div>';
		    	} else if ( $field['id'] == $prefix.'enable_recipient_message' ) {

		    		//Recipient Message Detail
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_message', 'field_type' => 'recipient', 'label' => esc_html__('Enable Recipient Message:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient message on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-detail-wrap">';
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_message_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '', 'placeholder' => esc_html__('Recipient Message', 'woovoucher') ) );
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_message_max_length', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Max Length:', 'woovoucher' ), 'description' => '' ) );
						$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_message_is_required', 'label' => esc_html__(' Required:', 'woovoucher' ), 'description' => esc_html__( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
						$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_message_desc', 'field_type' => 'recipient', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5' ) );
					echo '</div>';
		    	} else if ( $field['id'] == $prefix.'enable_recipient_giftdate' ) {

		    		// Recipient's Date Detail for sending Gift Voucher
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_giftdate', 'field_type' => 'recipient', 'label' => esc_html__('Enable Recipient Gift Date:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient\'s gift date selection on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-detail-wrap">';
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_giftdate_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '', 'placeholder' => esc_html__('Recipient Gift Date', 'woovoucher') ) );
						$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_giftdate_is_required', 'label' => esc_html__(' Required:', 'woovoucher' ), 'description' => esc_html__( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
						$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_giftdate_desc', 'field_type' => 'recipient', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5' ) );
					echo '</div>';
		    	} 
		    	else if ( $field['id'] == $prefix.'enable_recipient_phone' ) { // Meru

		    		// Recipient's Date Detail for Phone
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_phone', 'field_type' => 'recipient', 'label' => esc_html__('Enable Recipient Phone:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient\'s phone selection on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-detail-wrap">';
						$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_phone_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '', 'placeholder' => esc_html__('Recipient Phone', 'woovoucher') ) );
						$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_phone_is_required', 'label' => esc_html__(' Required:', 'woovoucher' ), 'description' => esc_html__( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
						$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_phone_desc', 'field_type' => 'recipient', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5' ) );
					echo '</div>'; 
		    	}
		    	else if ( $field['id'] == $prefix.'enable_recipient_delivery' ) {

		    		// Recipient's Date Detail for sending Gift Voucher
					$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_delivery_method', 'field_type' => 'recipient', 'label' => esc_html__('Enable Delivery Method:', 'woovoucher' ), 'description' => esc_html__( 'To enable the recipient\'s delivery method on the product page.', 'woovoucher' ) ) );
					echo '<div class="recipient-delivery-method-detail-wrap">';
					$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_delivery_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '', 'placeholder' => esc_html__('Delivery Method', 'woovoucher') ) );
					echo '<div class="woo-vou-recipient-errors">' . sprintf( esc_html__( '%sDelivery method%s requires any one recipient fields to be enabled.', 'woovoucher' ), '<strong>','</strong>') . '</div>';
		    		foreach( $delivery_methods as $delivery_method_key => $delivery_method_val ) {

		    			$default = false;
		    			$disabled = array();
		    			$recipient_default 	= array('recipient_name');
		    			$select_recipient_checkbox = '<div class="woo-vou-' . $delivery_method_key . '-delivery-error">' . esc_html__( "Please select any Recipient checkbox above to enable {$delivery_method_val} Delivery Method.", 'woovoucher' ) . '</div>';
		    			if ( $delivery_method_key == 'email' ) {
		    				$default = true;
		    				$disabled = $recipient_default = array( 'recipient_email' );
		    				$select_recipient_checkbox = '<div class="woo-vou-email-delivery-error">' . esc_html__( 'Please tick Recipient Email to enable this delivery method.', 'woovoucher' ) . '</div>';
		    			}

			    		// Recipient's Date Detail for sending Gift Voucher
						$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'recipient_delivery[enable_' . $delivery_method_key . ']', 'field_type' => 'recipient', 'label' => $delivery_method_val, 'description' => '', 'default' => $default, 'option_name' => $prefix . 'recipient_delivery', 'key_name' => 'enable_'.$delivery_method_key ) );
						echo '<div class="recipient-detail-wrap">';
							$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_delivery[label_' . $delivery_method_key . ']', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => esc_html__( 'Label:', 'woovoucher' ), 'description' => '', 'option_name' => $prefix . 'recipient_delivery', 'key_name' => 'label_'.$delivery_method_key, 'placeholder' => $delivery_method_val ) );
							$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'recipient_delivery[desc_' . $delivery_method_key . ']', 'field_type' => 'recipient', 'label' => esc_html__( 'Description:', 'woovoucher' ),'description' => esc_html__( 'Enter the description which you want to show on product page.', 'woovoucher' ), 'label_style' => 'display: block; float: none; width: auto !important;', 'rows' => 2, 'cols' => '5', 'option_name' => $prefix . 'recipient_delivery', 'key_name' => 'desc_'.$delivery_method_key, ) );
							$this->woo_vou_add_multiple_checkbox( array( 'id' => $prefix . 'recipient_delivery[' . $delivery_method_key .']', 'field_type' => 'recipient','options' => $_recipient_details, 'label'=> sprintf( esc_html__( "Select recipient fields that you want to show in %s{$delivery_method_val}%s block.", 'woovoucher' ), '<strong>', '</strong>'), 'description' => '', 'option_name' => $prefix . 'recipient_delivery', 'key_name' => $delivery_method_key ) );
							echo $select_recipient_checkbox;
						echo '</div>';
		    		}

		    		echo '<div class="woo-vou-delivery-method-error">' . esc_html__( 'Please select atleast one delivery method.', 'woovoucher' ) . '</div>';
		    		echo '</div>';
		    	} else {

		    		// add action to custom recipient fields
		    		do_action( 'woo_vou_custom_recipient_field', $field );
		    	}
		    ?>
		    </div>
		</div>
		<!-- HTML for creating discount category on product meta settings html ends -->
		<?php
		$html .= ob_get_clean();

		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
}