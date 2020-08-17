<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Woo_Wou_Settings' ) ) :

/**
 * Setting page Class
 * 
 * Handles Settings page functionality of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
class Woo_Wou_Settings extends WC_Settings_Page {

	/**
	 * Constructor
	 * 
	 * Handles to add hooks for adding settings
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function __construct() {

		global $woo_vou_model, $woo_vou_render, $woo_vou_voucher; // Declare global variables

		$this->id    	= 'woo-vou-settings'; // Get id
		$this->label 	= esc_html__( 'PDF Vouchers', 'woovoucher' ); // Get tab label
		$this->model 	= $woo_vou_model; // Declare variable $this->model
		$this->render 	= $woo_vou_render; // Declare variable $this->render
		$this->voucher	= $woo_vou_voucher; // Declare variable $this->voucher

		// Add filter for adding tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );

		// Add action to show output
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'woo_vou_output' ) );

		// Add action for saving data
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'woo_vou_save' ) );

		// Add action for adding sections
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

		// Add action to add custom field for setting page
		add_action( 'woocommerce_admin_field_vou_filename', array( $this->render, 'woo_vou_render_filename_callback' ) );
		add_action( 'woocommerce_admin_field_vou_upload', array( $this->render, 'woo_vou_render_upload_callback' ) );
		add_action( 'woocommerce_admin_field_vou_preview_upload', array( $this->render, 'woo_vou_render_preview_upload_callback' ) );
		add_action( 'woocommerce_admin_field_vou_textarea', array( $this->render, 'woocommerce_admin_field_vou_textarea' ) );
		add_action( 'woocommerce_admin_field_vou_datetime_picker', array( $this->render, 'woocommerce_admin_field_vou_datetime_picker' ) );
		add_action( 'woocommerce_admin_field_woo_vou_button', array( $this->render, 'woo_vou_button_callback' ) );
	}

	/**
	/**
	 * Handles to add sections for Settings tab
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function get_sections() {
		
		$viewed_extensions = (int)get_option( 'woo_vou_viewed_extensions' );
        $unread_counts = WOO_VOU_AVAILABLE_EXTENSIONS - $viewed_extensions;
		$extention_section_title = '<span>'.esc_html__( 'Add-ons', 'woovoucher' ).'</span>';
		if ( $unread_counts > 0 ) {	
			$extention_section_title .= '<span class="woo-vou-extension-bubble update-plugins count-'.$unread_counts.'"><span class="plugin-count">'.$unread_counts.'</span></span>';
		}
		// Create array
		$sections = array(
			''          			=> esc_html__( 'General Settings', 'woovoucher' ),
			'vou_voucher_settings'  => esc_html__( 'Voucher Settings', 'woovoucher' ),
			'vou_misc_settings' 	=> esc_html__( 'Misc Settings', 'woovoucher' )
		);

		$sections = apply_filters( 'woo_vou_setting_sections', $sections );
		
		$sections['vou_addon'] = $extention_section_title;
		
		return $sections;
	}

	/**
	 * Output of sections
	 * 
	 * Handles output of subtabs of this page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		$li_class = $this->id . '-sub-tab';

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li class="' . $li_class . '"><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . $this->id. ' woo-vou-sub-link ' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';

	}

	/**
	 * Handles to output data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function woo_vou_output() {

		// Get global variable
		global $current_section;

		// Get settings for current section
		$settings = $this->get_settings( $current_section );

		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Handles to save data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function woo_vou_save() {
		global $current_section;
		
		if( isset( $_POST['vou_gift_notification_time'] ) && $_POST['vou_gift_notification_time'] !== '' ) {
			
			// first clear the schedule	
			wp_clear_scheduled_hook( 'woo_vou_send_gift_notification' );
			
			// if not scheduled cron
			if ( ! wp_next_scheduled( 'woo_vou_send_gift_notification' ) ) {
				
				$utc_timestamp = time();
				$local_time = current_time( 'timestamp' ); // to get current local time
				
				// Schedule CRON events starting at user defined hour and periodically thereafter
				$schedule_time 	= mktime( $_POST['vou_gift_notification_time'], 0, 0, date('m', $local_time), date('d', $local_time), date('Y', $local_time) );
				
				// get difference 
				$diff 		= ( $schedule_time - $local_time );
				$utc_timestamp 	= $utc_timestamp + $diff;
				
				wp_schedule_event( $utc_timestamp, 'daily', 'woo_vou_send_gift_notification' );	
			}			
		}

		// If pdf delete time is not empty
		if( !empty( $_POST['vou_pdf_delete_time'] ) ){

			// first clear the schedule	
			wp_clear_scheduled_hook( 'woo_vou_flush_upload_dir_cron' );

			// if not scheduled cron
			if ( ! wp_next_scheduled( 'woo_vou_flush_upload_dir_cron' ) ) {

				// schedule event
				wp_schedule_event( time(), $_POST['vou_pdf_delete_time'], 'woo_vou_flush_upload_dir_cron' );
			}
		}

		// Assign our partial products change to option name in woocommerce so woocommerce saves user changes from partial products
		$_POST['vou_partial_redeem_product_ids'] = !empty($_POST['woo_vou_selected_products']) ? $_POST['woo_vou_selected_products'] : '';

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Handles to get setting
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.8
	 */
	public function get_settings( $current_section = '' ) {

		global $wp_roles;

		$voucher_options	= array(); // Declare variable for voucher options
		$voucher_data		= woo_vou_get_vouchers(); // Get Voucher data

		// Loop on voucher data
		foreach ( $voucher_data as $voucher ) {

			// Check voucher id is not empty
			if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) {
				
				$voucher_options[$voucher['ID']] = $voucher['post_title']; // Get voucher id
				$multiple_voucher_options[$voucher['ID']] = $voucher['post_title']; // Get voucher post title
			}
		}

		// Usability options
		$usability_options = array(
			'0'	=> esc_html__('One time only', 'woovoucher'),
			'1'	=> esc_html__('Unlimited', 'woovoucher')
		);

		// Voucher price options
		$voucher_price_options	= array(
			''	=> esc_html__('Sale Price', 'woovoucher'),
			'1'	=> esc_html__('Regular Price', 'woovoucher'),
			'2'	=> esc_html__('Custom Voucher Price', 'woovoucher')
		);

		// Voucher delivery options
		$voucher_delivery_options	= array(
			'email' 	=> esc_html__('Email', 'woovoucher'),
			'offline'	=> esc_html__('Offline', 'woovoucher')
		);

		// Recipient Form options
		$recipient_form_options = array(
			'1' => esc_html__( 'Above add to cart button ', 'woovoucher' ),
			'2' => esc_html__( 'Below add to cart button', 'woovoucher' )
		);
										
		// Gift notification schedule time options		
		$all_schedule_time_options = array(
			'0'	=> esc_html__( '12 AM', 'woovoucher' ),
			'1'	=> esc_html__( '1 AM', 'woovoucher' ),
			'2'	=> esc_html__( '2 AM', 'woovoucher' ),
			'3'	=> esc_html__( '3 AM', 'woovoucher' ),
			'4'	=> esc_html__( '4 AM', 'woovoucher' ),
			'5'	=> esc_html__( '5 AM', 'woovoucher' ),
			'6'	=> esc_html__( '6 AM', 'woovoucher' ),
			'7'	=> esc_html__( '7 AM', 'woovoucher' ),
			'8'	=> esc_html__( '8 AM', 'woovoucher' ),
			'9'	=> esc_html__( '9 AM', 'woovoucher' ),
			'10'=> esc_html__( '10 AM', 'woovoucher' ),
			'11'=> esc_html__( '11 AM', 'woovoucher' ),
			'12'=> esc_html__( '12 PM', 'woovoucher' ),
			'13'=> esc_html__( '1 PM', 'woovoucher' ),
			'14'=> esc_html__( '2 PM', 'woovoucher' ),
			'15'=> esc_html__( '3 PM', 'woovoucher' ),
			'16'=> esc_html__( '4 PM', 'woovoucher' ),
			'17'=> esc_html__( '5 PM', 'woovoucher' ),
			'18'=> esc_html__( '6 PM', 'woovoucher' ),
			'19'=> esc_html__( '7 PM', 'woovoucher' ),
			'20'=> esc_html__( '8 PM', 'woovoucher' ),
			'21'=> esc_html__( '9 PM', 'woovoucher' ),
			'22'=> esc_html__( '10 PM', 'woovoucher' ),
			'23'=> esc_html__( '11 PM', 'woovoucher' ),
		);

		// Gift notification schedule time options		
		$pdf_delete_cron_schedules = array(
			'hourly' 		=> esc_html__( 'Hourly', 'woovoucher' ),
			'daily'			=> esc_html__( 'Daily', 'woovoucher' ),
			'twicedaily'	=> esc_html__( 'Twice Daily', 'woovoucher' )
		);

		// Declare option for expiry type options
		$expiry_type_options = array(
										'specific_date' 	=> esc_html__('Specific Time', 'woovoucher'),
										'based_on_purchase' => esc_html__('Based on Purchase', 'woovoucher'),
									);

		// Declare variable for based on purchase options
		$based_on_purchase_opt  = array(
										'7' 		=> '7 Days',
										'15' 		=> '15 Days',
										'30' 		=> '1 Month (30 Days)',
										'90' 		=> '3 Months (90 Days)',
										'180' 		=> '6 Months (180 Days)',
										'365' 		=> '1 Year (365 Days)',
										'cust'		=> 'Custom',
									);

		// Filter to change date picker format
		$woo_vou_vou_start_end_date_format = apply_filters( 'woo_vou_vou_start_end_date_format', 'dd-mm-yy' );

		// If voucher settings are selected
		if ( 'vou_voucher_settings' == $current_section ) {

			$settings = array(

				array( 
					'name'	=>	esc_html__( 'Voucher Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_voucher_settings'
				),
				array(
					'id'		=> 'vou_site_logo',
					'name'		=> esc_html__( 'Site Logo', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Here you can upload a logo of your site. This logo will be displayed on the voucher as the Site Logo', 'woovoucher' ).'</p>',
					'type'		=> 'vou_upload',
					'size'		=> 'regular'
				),
				array(
					'id'		=> 'vou_pdf_template',
					'name'		=> esc_html__( 'PDF Template', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Select a PDF template.', 'woovoucher' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $voucher_options
				),
				array(
					'id'		=> 'vou_pdf_usability',
					'name'		=> esc_html__( 'Usability', 'woovoucher' ),
					'desc'		=> '<p class="description">'.sprintf( esc_html__( 'Choose how you wanted to use vouchers codes.%sIf you set usability "%sOne time only%s" then it will automatically set product quantity equal to a number of voucher codes entered and it will automatically decrease quantity by 1 when it gets purchased. If you set usability "%sUnlimited%s" then the plugin will automatically generate unique voucher codes when the product purchased. ', 'woovoucher' ), '<br>', '<b>','</b>','<b>','</b>' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $usability_options
				),
				array(
					'id'		=> 'multiple_pdf',
					'name'		=> esc_html__( 'Multiple Voucher', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable 1 voucher per PDF', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box if you want to generate 1 PDF for 1 voucher code instead of creating 1 combined PDF for all vouchers.', 'woovoucher' ).'</p>'
				),
				array(
					'id'		=> 'revoke_voucher_download_link_access',
					'name'		=> esc_html__( 'Remove Used / Expired Voucher Download Link', 'woovoucher' ),
					'desc'		=> esc_html__( 'Remove Used / Expired Voucher Download Link', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . sprintf(esc_html__( 'Check this box if you want to remove voucher download link, when voucher is %1$sused%2$s or %1$sexpired%2$s.', 'woovoucher' ), '<strong>', '</strong>' ) . '</p>'
				),
				array(
					'id'		=> 'vou_change_expiry_date',
					'name'		=> esc_html__( 'Allow Changing Voucher Expiry Date', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Changing Voucher Expiry Date', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.sprintf( esc_html__( 'Check this box if you want to allow admin/vendors to change voucher expiry date when voucher is %1$sunredeemed%2$s or %1$sexpired%2$s from voucher code page.', 'woovoucher' ), '<b>','</b>').'</p>'
				),
				array(
					'id'		=> 'vou_change_template',
					'name'		=> esc_html__( 'Allow Change Voucher Template', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Change Voucher Template', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.sprintf( esc_html__( 'Check this box if you want to allow admin/vendors to change voucher template when voucher is %1$sunredeemed%2$s, %1$sredeemed%2$s or %1$sexpired%2$s from voucher code details page.', 'woovoucher' ), '<b>','</b>').'</p>'
				),
				array(
					'id'		=> 'vou_attach_processing_mail',
					'name'		=> esc_html__( 'Voucher as Attachment', 'woovoucher' ),
					'desc'		=> sprintf( esc_html__( 'Send voucher PDF as attachment in %1$sprocessing%2$s / %1$scompleted%2$s order email.', 'woovoucher' ),'<b>','</b>'),
					'type'		=> 'checkbox',
					'checkboxgroup'   => 'start',
				),
				array(
					'id'		=> 'vou_attach_gift_mail',
					'name'		=> '',
					'desc'		=> sprintf( esc_html__( 'Send voucher PDF as attachment in %1$sgift notification%2$s email.', 'woovoucher' ), '<b>', '</b>'),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Here you can set where you want to send PDF voucher as an attachment in mail.', 'woovoucher' ).'</p>',
					'checkboxgroup'   => 'end',
				),

				array(
					'id'		=> 'vou_download_processing_mail',
					'name'		=> esc_html__( 'Show / Hide Voucher Download Link', 'woovoucher' ),
					'desc'		=> sprintf( esc_html__( 'Allow Voucher to download from %1$sprocessing%2$s / %1$scompleted%2$s order mail and %1$sorder thank you page%2$s.', 'woovoucher' ), '<b>', '</b>'),
					'type'		=> 'checkbox',
					'checkboxgroup'   => 'start',
				),
				array(
					'id'		=> 'vou_download_gift_mail',
					'name'		=> '',
					'desc'		=> sprintf( esc_html__( 'Allow voucher to download from %1$sgift notification%2$s mail.', 'woovoucher' ), '<b>','</b>'),
					'type'		=> 'checkbox',
					'checkboxgroup'   => '',
				),
				array(
					'id'		=> 'vou_download_dashboard',
					'name'		=> '',
					'desc'		=> sprintf( esc_html__( 'Allow voucher to download from %1$sdashboard -> Downloads%2$s page.', 'woovoucher' ), '<b>','</b>'),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Allow customer can download the voucher from which location.', 'woovoucher' ).'</p>',
					'checkboxgroup'   => 'end',
				),
				array(
					'id'		=> 'vou_allow_redeem_expired_voucher',
					'name'		=> esc_html__( 'Allow Redemption for Expired Vouchers', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Redemption for Expired Vouchers', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box if you want to allow redeem voucher codes after expiration date.', 'woovoucher' ).'</p>'
				),
				array(
					'id'		=> 'vou_allow_unlimited_redeem_vou_code',
					'name'		=> esc_html__( 'Allow Unlimited Redemption of Vouchers', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Unlimited Redemption of Vouchers', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box if you want to allow users to redeem same voucher code unlimited times.', 'woovoucher' ).'</p>'
				),
				array(
					'id'		=> 'vou_allow_unlimited_limit_vou_code',
					'name'		=> esc_html__( 'Voucher Usage Limit:', 'woovoucher' ),
					'desc'		=> esc_html__( 'This sets the number of times the same voucher code can be used. Leave it empty for unlimited redemption.', 'woovoucher' ),
					'type'		=> 'number',
					
					'desc_tip'	=> ''
				),
				array(
					'id'		=> 'vou_enable_logged_user_check_voucher_code',
					'title'		=> esc_html__( 'Access for Logged In Users', 'woovoucher' ),
					'desc'		=> sprintf(esc_html__( 'Check this box to allow %slogged in users%s to access check voucher code page and voucher codes report pages', 'woovoucher' ), '<b>','</b>'),
					'desc_tip'	=> '<p class="description">'.sprintf( esc_html__( 'Logged in users have access to check voucher code page and they can see their %1$sown purchased%2$s, used and expired voucher codes. By default %1$sadmin%2$s and %1$svendors%2$s have access to check voucher code page and voucher codes assigned to them', 'woovoucher' ), '<b>','</b>' ).'</p>',
					'type'		=> 'checkbox',
				),
				array(
					'id'		=> 'vou_enable_logged_user_redeem_vou_code',
					'name'		=> esc_html__( 'Allow Redemption of Own Purchased Vouchers for Logged in Users', 'woovoucher' ),
					'desc'		=> sprintf( esc_html__( 'Allow redemption of own purchased vouchers for %1$slogged in users%2$s', 'woovoucher' ), '<b>','</b>' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.sprintf( esc_html__( 'Check this box to allow logged in users to %1$sredeem own purchased vouchers.%2$s', 'woovoucher' ), '<b>', '</b>').'</p>'
				),
				
				array(
					'id'		=> 'vou_enable_guest_user_check_voucher_code',
					'title'		=>  esc_html__( 'Access for Guest Users', 'woovoucher' ),
					'desc'		=> sprintf( esc_html__( 'Check this box to allow %1$sguest users%2$s to access check voucher code page', 'woovoucher' ), '<b>','</b>'),
					'desc_tip'	=> '<p class="description">'.sprintf( esc_html__( 'By default %1$sadmin%2$s and %1$svendors%2$s have access to check voucher code page.', 'woovoucher' ), '<b>', '</b>').'</p>',
					'type'		=> 'checkbox',
				),
				array(
					'name' =>  esc_html__( 'Allow Redemption of Purchased Vouchers for Guest Users', 'woovoucher' ),
					'desc' => sprintf( esc_html__( 'Allow redemption of purchased vouchers for %1$sguest users%2$s.', 'woovoucher' ), '<b>','</b>' ),
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box to allow guest users to redeem any vouchers.', 'woovoucher' ).'</p>',
					'type' => 'checkbox',
					'id'   => 'woo_vou_guest_user_allow_redeem_voucher'  
				), 
				array(
					'id'		=> 'vou_enable_permission_vou_download_recipient_user',
					'name'		=> esc_html__( 'Allow Direct Access from Downloads Page', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Direct Access from Downloads Page', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box to allow recipient users to download PDF VOuchers from downloads page.', 'woovoucher' ).'</p>'
				),
				// Add "Enable Voucher Code For Regular Price" to Voucher Setting
				array(
					'id'		=> 'vou_voucher_price_options',
					'name'		=> esc_html__( 'Default Voucher Value', 'woovoucher' ),
					'desc'		=> '<p class="description">'.sprintf( esc_html__('This option determines default voucher value.%1$s%2$sSale Price%3$s - Voucher price would be same as product price.%1$s%2$sRegular Price%3$s - Voucher price would be regular price when product sold at either regular price or sale price.%1$s%2$sCustom Voucher Price%3$s - This will add new field of custom voucher price below sale price in product edit page. Voucher price would be same as custom voucher price when product sold at either regular price or sale price.', 'woovoucher' ), '<br>','<b>','</b>').'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $voucher_price_options
				),
				// Add "Enable Voucher Delivery" to Voucher Settings
				array(
					'id'		=> 'vou_voucher_delivery_options',
					'name'		=> esc_html__( 'Voucher Delivery', 'woovoucher' ),
					'desc'		=> '<p class="description">'.sprintf( esc_html__('Choose how your customer receives the "PDF Voucher"%1$s%2$sEmail%3$s - Customer receives "PDF Voucher" through email.%1$s%2$sOffline%3$s - You will have to send voucher through physical mode, via post or on-shop.', 'woovoucher' ), '<br>','<b>','</b>').'</p>',
					'type'		=> 'select',
					'default'	=> 'email',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $voucher_delivery_options
				),
				array(
					'id'		=> 'vou_exp_type',
					'name'		=> esc_html__( 'Expiration Date Type', 'woovoucher' ),
					'desc'		=> '<p class="description">'.sprintf( esc_html__( 'This option determines default voucher expiration type.%1$s%2$sSpecific Time%3$s - Voucher will be valid between Start date and Expiry date.%1$s%2$sBased on Purchase%3$s - Voucher will be valid for x days after purchase.', 'woovoucher' ), '<br>','<b>','</b>').'</p>',
					'type'		=> 'select',
					'default'	=> 'specific_date',
					'class'		=> 'vou_exp_type wc-enhanced-select',
					'options'	=> $expiry_type_options
				),
				array(
					'id'		=> 'vou_start_date',
					'name'		=> esc_html__( 'Voucher Start Date', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter a start date here if you want to make the voucher codes valid for a specific time only.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_datetime_picker',
					'default'	=> '',
					'class'		=> 'vou_start_date',
					'rel'			=> 'yy-mm-dd'
				),
				array(
					'id'		=> 'vou_exp_date',
					'name'		=> __( 'Voucher Expiration Date', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'If you want to make the voucher codes valid for a specific time only, you can enter a expiration date here. Leave it empty if you want to make voucher code never expire.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_datetime_picker',
					'default'	=> '',
					'class'		=> 'vou_start_date',
					'rel'			=> 'yy-mm-dd'
				),
				array(
					'id'		=> 'vou_days_diff',
					'name'		=> esc_html__( 'Expiration Days', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Choose expiration days. This will be counted from day of purchase of voucher code.', 'woovoucher' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $based_on_purchase_opt
				),
				array(
					'title'    => esc_html__( 'Number of Days', 'woovoucher' ),
					'desc'     => esc_html__( 'This sets the number of days after which voucher code will expire. It will be counted from day of purchase', 'woovoucher' ),
					'id'       => 'vou_custom_days',
					'css'      => 'width:95px;',
					'default'  => '',
					'type'     => 'number'
				),
				// End Voucher Option section
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_voucher_settings'
				),
				array( 
					'name'	=>	esc_html__( 'Email Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_email_settings'
				),
				array(
					'id'		=> 'vou_allow_bcc_to_admin',
					'name'		=> esc_html__( 'Send Emails to Admin', 'woovoucher' ),
					'desc'		=> esc_html__( 'Send Emails to Admin', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box if you want to send customer order email and gift notification email to admin.', 'woovoucher' ).'</p>'
				),
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_email_settings'
				),
			);

			
			$settings = apply_filters( 'woo_vou_voucher_settings', $settings );

		} elseif ( 'vou_misc_settings' == $current_section ) { // If misc settings is selected

			$settings = apply_filters( 'woo_vou_misc_settings', array(

				// Start Misc Settings section
				array( 
					'name'	=>	esc_html__( 'Misc Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_misc_settings'
				),
				array(
					'title'         => esc_html__( 'Delete Options', 'woovoucher' ),
					'id'            => 'vou_delete_options',
					'default'       => 'no',
					'desc_tip'      => esc_html__( 'Check this box If you don\'t want to use the Pdf Voucher Plugin on your site anymore. This makes sure, that all the settings and tables are being deleted from the database when you deactivate the plugin.', 'woovoucher' ),
					'type'          => 'checkbox'
				),
				array(
					'id'		=> 'woo_vou_gen_sys_log',
					'name'		=> esc_html__( 'System Report', 'woovoucher' ),
					'type'		=> 'woo_vou_button',
					'class'		=> 'button',
					'btn_title' => esc_html__( 'Generate System Report', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Please generate system report file and provide us in your ticket while contacting support.','woovoucher' ).'</p>'
				),
				// End Voucher Option section
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_misc_settings'
				),

				array( 
					'name'	=>	esc_html__( 'PDF Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_pdf_settings'
				),
				array(
					'id'		=> 'vou_char_support',
					'name'		=> esc_html__( 'Characters not Displaying Correctly?', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable characters support', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">'.esc_html__( 'Check this box to enable the characters support. Only do this if you have characters which is not displaying correctly (e.g. Greek characters).', 'woovoucher' ).'</p>'
				),
				array(
					'id'		=> 'vou_enable_preview_in_browser',
					'name'		=> esc_html__( 'Preview Voucher Template without downloading', 'woovoucher' ),
					'desc'		=> esc_html__( 'Preview Voucher Template without downloading', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow admin to preview voucher template in a browser instead of downloading the pdf from the backend.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_enable_pdf_password_protected',
					'name'		=> esc_html__( 'Enable PDF Password Protection', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable PDF Password Protection', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to enable PDF voucher with password protection.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_pdf_password_pattern',
					'name'		=> esc_html__( 'Password Pattern for PDF Voucher', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the password that you want to use while generating PDF voucher. Available tags are:','woovoucher' ).'<br /><code>{order_id}</code> '.esc_html__('- displays the order id.', 'woovoucher' ).'<br /><code>{order_date}</code> '.esc_html__('- displays the date of order completion, like: ', 'woovoucher' ) . date( 'Y-m-d' ) . '<br /><code>{first_name}</code> '.esc_html__('- displays buyer\'s first name.', 'woovoucher' ) . '<br /><code>{last_name}</code> '.esc_html__('- displays buyer\'s last name.', 'woovoucher' ) . '<br /><code>{buyer_email}</code> '.esc_html__('- displays buyer\'s email.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> ''
				),
				array(
					'id'		=> 'vou_enable_relative_path',
					'name'		=> esc_html__( 'Enable Relative Path', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable Relative Path', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to use a relative path instead of absolute path for loading images in voucher pdf.', 'woovoucher' ) . '</p>'
				),
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_pdf_settings'
				),
				
				array( 
					'name'	=>	esc_html__( 'Vendors Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_vendors_settings'
				),
				array(
					'id'		=> 'vou_enable_vendor_access_all_voucodes',
					'name'		=> esc_html__( 'Enable Vendors to Access/Redeem all Voucher Codes', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable Vendors to Access/Redeem all Voucher Codes', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow vendors to access all voucher codes. By default, they will only be able to access voucher codes assigned to them.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_disable_vendor_access_voucher_template',
					'name'		=> esc_html__( 'Disable Vendors to Access Voucher Template Page', 'woovoucher' ),
					'desc'		=> esc_html__( 'Disable Vendors to Access Voucher Template Page', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to disallow vendors to access voucher template page.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_allow_vendor_access_voucher_settings',
					'name'		=> esc_html__( 'Allow Vendors to Access Voucher Setting Area', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Vendors to Access Voucher Setting Area', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow vendors to access voucher setting area at user profile.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_allow_secondary_vendor_redeem_primary_voucher',
					'name'		=> esc_html__( 'Allow Secondary Vendors to Redeem Voucher Codes', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Secondary Vendors to Redeem Voucher Codes', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . sprintf( esc_html__( 'Check this box if you want to allow secondary vendor user(s) to redeem their %1$sown voucher codes %2$s.', 'woovoucher' ),'<strong>','</strong>' ) . '</p>'
				),
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_vendors_settings'
				),

				array( 
					'name'	=>	esc_html__( 'Other Settings', 'woovoucher' ),
					'type'	=>	'title',
					'desc'	=>	'',
					'id'	=>	'vou_other_settings'
				),
				// Add "Allow only Recipient to get voucher info" to Misc Setting
				array(
					'id'		=> 'vou_allow_recipient_to_get_voucher_info',
					'name'		=> esc_html__( 'Allow Only Recipient To Get Voucher Info', 'woovoucher' ),
					'desc'		=> esc_html__( 'Allow Only Recipient To Get Voucher Info', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . sprintf( esc_html__( 'Check this box to allow only the recipient to get voucher information and disable for customer. Customer who have ordered will not get voucher information. %s(Not Recommnended)%s', 'woovoucher' ), '<b>','</b>' ) . '</p>'
				),
				
				// Add "Disable Variation's Auto Downloadable" to Misc Setting
				array(
					'id'		=> 'vou_disable_variations_auto_downloadable',
					'name'		=> esc_html__( 'Disable Auto Check Downloadable', 'woovoucher' ),
					'desc'		=> esc_html__( 'Disable Auto Check Downloadable', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you don\'t want to tick all variation as downloadable when we "Enable Voucher Codes" at the product level.', 'woovoucher' ) . '</p>'
				),
				// Add "Auto Enable Voucher" to Voucher Setting
				array(
					'id'		=> 'vou_enable_voucher',
					'name'		=> esc_html__( 'Auto Enable Voucher', 'woovoucher' ),
					'desc'		=> esc_html__( 'Auto Enable Voucher', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . sprintf( esc_html__( 'Check this box to automatically "Enable Voucher Codes" when product is set as %1$sDownloadable.%2$s', 'woovoucher' ), '<strong>', '</strong>' ) . '</p>'
				),
				array(
					'id'		=> 'vou_vendor_default_logo',
					'name'		=> esc_html__( 'Vendor Logo For Preview', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Upload a vendor logo for preview pdf, this logo will be displayed on the voucher template preview as vendor logo. Leave it empty to use default logo.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_upload',
					'size'		=> 'regular'
				),
				array(
					'id'		=> 'vou_download_text',
					'name'		=> esc_html__( 'Voucher Download Text', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the Voucher Download link text that you want to use while generating PDF.','woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> ''
				),
				array(
					'id'		=> 'vou_code_postfix',
					'name'		=> esc_html__( 'Voucher Code Postfix', 'woovoucher' ),
					'type'		=> 'number',
					'desc'		=> '<p class="description">'.esc_html__( 'Enter a numeric value which will automatically get succeeded at the end of voucher code when usability is set as unlimited.', 'woovoucher' ).'</p>'
				),
				array(
					'id'		=> 'vou_gift_notification_time',
					'name'		=> esc_html__( 'Select Time for Gift Notification Email', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'It will send gift notification email at selected time.', 'woovoucher' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $all_schedule_time_options
				),
				array(
					'id'		=> 'vou_pdf_delete_time',
					'name'		=> esc_html__( 'Clear Voucher\'s PDF  ', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( "Select time range at which the voucher's PDF file will get deleted. i.e. Hourly, Daily, twice daily", 'woovoucher' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $pdf_delete_cron_schedules,
					'default'	=> 'twicedaily'
				),
				array( 
					'type' 		=> 'sectionend',
					'id' 		=> 'vou_other_settings'
				),
			));

		} 
		elseif($current_section == 'vou_addon'){
			
			$settings = apply_filters( 'woo_vou_addon', array(
			
				require_once WOO_VOU_ADMIN .'/forms/woo-vou-plugin-addon.php',
				
			));
		
		}
		else { // Else go for general settings
			
			
			$settings = apply_filters( 'woocommerce_products_general_settings', array(

				array(
					'title' => esc_html__( 'General Settings', 'woovoucher' ),
					'type' 	=> 'title',
					'id' 	=> 'vou_general_settings'
				),
				// Add Partial Redeem settings
				array(
					'id'		=> 'vou_enable_partial_redeem',
					'name'		=> esc_html__( 'Enable Partial Redemption', 'woovoucher' ),
					'desc'		=> sprintf(esc_html__( 'Enable Partial Redemption %1sChoose individual products%2s ( %3s 0 %4s ) Selected', 'woovoucher' ), '<a class="woo-vou-select-part-redeem-product">', '</a>', '<span class="woo-vou-part-redeem-product-count">', '</span>'),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check "Enable Partial Redemption" if you want to enable partial redemption for all products. However, If you want to enable partial redemption for particular products then click on "Choose individual products" and select the products for which you want to allow partial redemption.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_partial_redeem_product_ids',
					'type'		=> 'text',
				),
				// Add coupon code settings
				array(
					'id'		=> 'vou_enable_coupon_code',
					'name'		=> esc_html__( 'Auto Enable Coupon Code Generation', 'woovoucher' ),
					'desc'		=> esc_html__( 'Auto Enable Coupon Code Generation', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow coupon code generation when a voucher code gets generated. This will allow you to use voucher codes on online store.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_enable_voucher_preview',
					'name'		=> esc_html__( 'Enable Voucher Preview', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable Voucher Preview', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow users to preview the voucher on product detail page before placing the order.', 'woovoucher' ) . '</p>'
				),
				array(
					'id'		=> 'vou_enable_voucher_preview_open_option',
					'name'		=> esc_html__( 'Voucher Preview Type', 'woovoucher' ),
					'type'		=> 'radio',
					'default' => 'popup',
					'options' => array(
				      'popup'        => esc_html__( 'Pop-Up (Open the voucher preview in pop-up.)', 'woovoucher' ),
				      'newtab'       => esc_html__( 'New Tab (Open the voucher preview in new tab.)', 'woovoucher' ),
				    ),
					'desc_tip'	=> ''
				),
				array(
					'id'		=> 'vou_preview_image',
					'name'		=> esc_html__( 'Preview Watermark Image', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Select the image that you would like to apply as watermark to the generated preview PDF on product page.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_preview_upload',
					'size'		=> 'regular'
				),
				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'vou_general_settings'
				),
				array(
						'title' => esc_html__( 'File Download Settings', 'woovoucher' ),
						'type' 	=> 'title',
						'id' 	=> 'vou_file_download_settings'
					),
				array(
					'id'		=> 'vou_pdf_name',
					'name'		=> esc_html__( 'Export PDF File Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file name that you want to use while generating a PDF of unredeemed voucher codes. Available tag is:','woovoucher' ).'<br /><code>{current_date}</code> - '.esc_html__('displays the current date.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> '.pdf'
				),
				array(
					'id'		=> 'vou_pdf_title',
					'name'		=> esc_html__( 'Export PDF Title', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file title that you want to use while generating a PDF of voucher codes.','woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> ''
				),
				array(
					'id'		=> 'vou_pdf_author',
					'name'		=> esc_html__( 'Export PDF Author Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file author that you want to use while generating a PDF of voucher codes.','woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> ''
				),
				array(
					'id'		=> 'vou_pdf_creator',
					'name'		=> esc_html__( 'Export PDF Creator Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file creator that you want to use while generating a PDF of voucher codes.','woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> ''
				),
				array(
					'id'		=> 'vou_csv_name',
					'name'		=> esc_html__( 'Export CSV File Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the CSV file name that you want to use while generating a CSV of unredeemed voucher codes. Available tag is:','woovoucher' ).'<br /><code>{current_date}</code> - '.esc_html__('displays the current date.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> '.csv'
				),
				array(
					'id'		=> 'order_pdf_name',
					'name'		=> esc_html__( 'Download PDF File Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file name that you want to use while users download a PDF of voucher codes on frontend. The available tags are','woovoucher' ).'<br /><code>{current_date}</code> - '.esc_html__('displays the current date.', 'woovoucher' ).'<br /><code>{product_title}</code> - '.esc_html__('displays the product title.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> '.pdf'
				), 
				array(
					'id'		=> 'attach_pdf_name',
					'name'		=> esc_html__( 'Attachment PDF File Name', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'Enter the PDF file name that you want to use while users download a PDF of voucher codes from email attachment. The available tags are','woovoucher' ).'<br /><code>{current_date}</code> - '.esc_html__('displays the current date.', 'woovoucher' ).'<br /><code>{product_title}</code> - '.esc_html__('displays the product title.', 'woovoucher' ).'</p>',
					'type'		=> 'vou_filename',
					'options'	=> '{unique_string}.pdf'
				),
				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'vou_file_download_settings'
				),
				array(
						'title' => esc_html__( 'Display Settings', 'woovoucher' ),
						'type' 	=> 'title',
						'id' 	=> 'vou_display_settings'
					),
				// Add "Enable Voucher Code For Regular Price" to Misc Setting
				array(
					'id'		=> 'vou_recipient_form_position',
					'name'		=> esc_html__( 'Recipient Form Position', 'woovoucher' ),
					'desc'		=> '<p class="description">'.esc_html__( 'It controls the position of the Recipient Form.', 'woovoucher' ).'</p>',
					'type'		=> 'select',
					'class'		=> 'wc-enhanced-select',
					'options'	=> $recipient_form_options
				),
				array(
					'name'   => esc_html__('Custom CSS', 'woovoucher'),
					'class'  => '',
					'css'   => 'width:100%;min-height:100px',
					'desc'   => esc_html__('Here you can enter your custom CSS for the  PDF vouchers. The CSS will be automatically added to the header, when you save it.', 'woovoucher'),
					'id'   => 'vou_custom_css',
					'type'   => 'vou_textarea',
					'default' => ''
				),
				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'vou_display_settings'
				),
			));
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new Woo_Wou_Settings();
