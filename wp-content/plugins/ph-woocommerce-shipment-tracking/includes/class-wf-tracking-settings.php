<?php
class WF_Tracking_Settings {

	public function __construct() {
		$this->init();
	}

    public function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_'.Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY, array( $this, 'settings_tab') );
        add_action( 'woocommerce_update_options_'.Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY, array( $this, 'update_settings') );

        add_action( 'woocommerce_admin_field_connect_access_token', array( $this, 'ph_connect_access_token_key' ) );
    }

    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs[ Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY ] = __( 'Tracking', 'woocommerce-shipment-tracking' );
        return $settings_tabs;
    }

    public function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    public function update_settings() {
		$options = self::get_settings();
		foreach ( $options as $value ) {
			if ( ! isset( $value['id'] ) || ! isset( $value['type'] ) ) {
				continue;
			}

			if( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_data_txt' == $value['id'] ) {
				// Do nothing.
			}

			if( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_reset_data' == $value['id'] ) {
				// Reset tracking data is checked.
				if( isset( $_POST[ $value['id'] ] ) ) {
					unset ( $_POST[ $value['id'] ] );
					
					$tracking_data			= Ph_Shipment_Tracking_Util::load_tracking_data( false, true );
					$tracking_data_txt 		= Ph_Shipment_Tracking_Util::convert_tracking_data_to_piped_text( $tracking_data );
					
					$_POST[  Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_data_txt' ] = $tracking_data_txt;
					$result = delete_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.Ph_Shipment_Tracking_Util::TRACKING_DATA_KEY );
				}
				else {
					$tracking_data_txt 		= $_POST[  Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_data_txt' ];
					$default_tracking_data	= Ph_Shipment_Tracking_Util::load_tracking_data();
					$tracking_data 			= Ph_Shipment_Tracking_Util::convert_piped_text_to_tracking_data( $tracking_data_txt , $default_tracking_data);

					update_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.Ph_Shipment_Tracking_Util::TRACKING_DATA_KEY, $tracking_data );
				}
			}
		}

        woocommerce_update_options( $options );
    }

    public function ph_connect_access_token_key() {

		$store_id = get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );

		if( $store_id )
		{
			$button_text = 'Re-Connect';
		}else{
			$button_text = 'Connect';
		}

		?>
		<tr valign="top" id="ph_connect_access_token_key" class="ph-shipment-tracking-access-key">
			<th scope="row" class="titledesc"></th>
			<td class="forminp">
				<?php 
				if( !$store_id )
				{
					?>
					<p style="margin-top: -10px;">Enter Access Token provided with the plugin to enable Live Shipment Tracking. Contact our <a href="https://www.pluginhive.com/support/" target="_blank">Support Team</a> to get Access Token if it is not available.</p><br/>
					<?php
				}
				?>

				<p style="margin-top: -10px;"><b>Access Token is only required for: FedEx, UPS, USPS, Canada Post, DHL Express, Delhivery, Australia Post, Blue Dart</b></p><br/>

				<button type="button" id="ph_shipment_tracking_get_store_id" class="button-primary"><?php echo $button_text; ?> </button>
			</td>
		</tr>
		<?php
	}

    public static function get_settings() {
		$tracking_data			= Ph_Shipment_Tracking_Util::load_tracking_data();
		$tracking_data_txt		= Ph_Shipment_Tracking_Util::convert_tracking_data_to_piped_text( $tracking_data );
		$message				= Ph_Shipment_Tracking_Util::get_default_shipment_message_placeholder();
		
        $settings = array(
            'section_title'			=> array(
                'name'				=> __( 'Shipment Tracking Settings', 'woocommerce-shipment-tracking' ),
                'type'				=> 'title',
                'desc'				=> '',
                'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_section_title'
            ),
    	   'access_token_id'		=> array(
				'title'				=> __( 'Live Tracking Access Token', 'woocommerce-shipment-tracking' ),
				'type'				=> 'text',
				'desc'				=> __( 'Provide Access Token for the live shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'			=> true,
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id',
			),
			'ph_connect_access_token_key'	=>	array(
				'type'				=> 'connect_access_token',
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_connect_access_token_key'
			),
			'custom_tracking_page_url'	=> array(
				'title'				=> __( 'Tracking Page URL', 'woocommerce-shipment-tracking' ),
				'type'				=> 'text',
				'desc'				=> __( "<br/><br/><p style='font-style: normal;'>Enter the Tracking Page URL. Create a Tracking Page and add the shortcode <b>[ph-shipment-tracking-page]</b> to that page. Live Tracking Details will be shown on that page.</p>", 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_custom_page_url',
			),
			'view_on_carriers_page'	=> array(
				'title'				=> __( "View on Shipping Carrier's Page", 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Enable', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( "Enabling this will display a link to the shipping carrier's website to display tracking status.", 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_view_on_carriers_page',
				'default'			=> 'no'
			),
			'custom_message'		=> array(
				'title'				=> __( 'Custom Shipment Message', 'woocommerce-shipment-tracking' ),
				'type'				=> 'textarea',
				'desc'				=> __( 'Define your own shipment message. Use the place holder tags [ID], [SERVICE] and [DATE] for Shipment Id, Shipment Service and Shipment Date respectively.<br>', 'woocommerce-shipment-tracking' ),
				'desc_tip'			=>	true,
				'css'				=> 'width:900px',
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.Ph_Shipment_Tracking_Util::TRACKING_MESSAGE_KEY,
				'placeholder'		=> $message
			),
			'turn_off_api'			=> array(
				'title'				=> __( 'Turn off API Status', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Turn off Real time API Status', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Turn off real time API tracking status on customer order page. Basic Tracking info on top will still be  displayed.', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.Ph_Shipment_Tracking_Util::TRACKING_TURN_OFF_API_KEY,
				'default'			=> 'no'
			),
			
			'api_live_status_refresh'	=> array(
				'title'				=> __( 'Automatic Refresh', 'woocommerce-shipment-tracking' ),
				'type'				=> 'select',
				'options'			=> array(
					'enable'	=> __( 'Enable', 'woocommerce-shipment-tracking' ),
					'disable'	=> __( 'Disable', 'woocommerce-shipment-tracking' ),
				),
				'default'			=> 'disable',
				'desc'				=> '<strong>'.__( 'Enable - ', 'woocommerce-shipment-tracking' ).'</strong><br/>'.__( 'It will turn on live status automatic refresh on Myaccount->Order page loading.', 'woocommerce-shipment-tracking' ).'<br/><strong>'.__( 'Disable - ', 'woocommerce-shipment-tracking' ).'</strong>'.__( 'It will turn off the automatic refresh on Myaccount->Order page and Refresh button will be shown', 'woocommerce-shipment-tracking'),
				'desc_tip'			=> true,
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_automatic_tracking_live_status_refresh',
			),
            'data_txt' => array(
                'name'				=> __( 'Tracking Data', 'woocommerce-shipment-tracking' ),
                'type'				=> 'textarea',
				'wrap'				=> 'off',
				'css'				=> 'width:900px; height:500px; overflow:auto',
                'desc'				=> __( 'You can add or remove any shipment tracking services by adding or removing respective lines. <br/>To add new service, create a new line by adding shipper name and tracking url (optional) separated using pipe symbol \'|\' as given below. <br/>Format: <strong>[ shipping service name ] | [ shipment tracking url (optional) ]</strong><br/>Example: <strong>Shipping Service Name | http://tracking_url?tracking_id=</strong><br/><br/>Complex tracking urls can be represented using place holder tags [ID] and [PIN] for Shipment Id and Postcode respectively.<br/>Example: <strong>PostNL | https://jouw.postnl.nl/[ID]/track-en-trace/111111111/NL/[PIN]</strong><br/><br/>', 'woocommerce-shipment-tracking' ),
				'default'			=> $tracking_data_txt,
                'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_data_txt',
				'placeholder'		=> __( '[ shipping service name ]|[ shipment tracking url ]', 'woocommerce-shipment-tracking' )
            ),
            'ph_ups_integration'	=> array(
				'title'				=> __( "Third Party Tracking Integration", 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Enable', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( "Enable this to integrate with PluginHive UPS Plugin and sync UPS Tracking Details to the orders.<br/><small><i>Make sure to Disable Shipment Tracking for Customer within UPS plugin to avoid displaying tracking details twice.</i></small>", 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_ups_integration',
				'default'			=> 'no'
			),
			'third_party'			=> array(
				'label'				=> __( 'Enable', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Enable this to integrate with Shipping Easy<br/><small><i>This will sync Tracking Details from Shipping Easy to your WooCommerce Orders.</i></small>', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY."_third_party",
				'default'			=> 'no',
			),
			'ph_go_shippo'			=> array(
				'label'				=> __( 'Enable', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Enable this to integrate with Go Shippo<br/><small><i>This will sync Tracking Details from Go Shippo to your WooCommerce Orders.</i></small>', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY."_ph_go_shippo",
				'default'			=> 'no',
			),
			'shipment_tracking_customer' => array(
				'title'				=> __( 'Send Tracking Details To Customer', 'woocommerce-shipment-tracking' ),
				'label'		   		=> __( 'Add Tracking Details to Customer Order Notes in My Accounts and in Order Completion Email.', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Add Tracking Details to Customer Order Notes in My Accounts and in Order Completion Email.', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY."_shipment_tracking_customer",
				'default'			=> 'yes',
			),
			'display_tracking_in_email' => array(
				'title'				=> __( 'Add Tracking Details to email', 'woocommerce-shipment-tracking' ),
				'label'		   		=> __( 'Add Tracking Details in the Order Completion Email to Customer', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Add Tracking Details in the Order Completion Email to Customer.', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY."_shipment_tracking_email_customer",
				'default'			=> 'yes',
			),
			'reset_data'			=> array(
				'title'				=> __( 'Reset Tracking Data', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Reset Tracking Data', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Reset tracking data to the default values. All custom added values in the above tracking data will be cleaned up.', 'woocommerce-shipment-tracking' ),
				'id'				=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_reset_data',
				'default'			=> 'no'
			),

			// Credentials
			// 'account_credentials'   => array(
			// 	'title'			=> __( 'Shipping Carrier Credentials', 'woocommerce-shipment-tracking' ),
			// 	'type'			=> 'title',
			// 	'class'			=> 'wf_settings_heading_tab'
			// ),

			'ups_user_id'			=> array(
				'title'					=> __( 'UPS User Id', 'woocommerce-shipment-tracking' ),
				'type'					=> 'text',
				'desc'					=> __( 'Provide UPS user Id for the live UPS shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ups[user_id]',
			),
			'ups_password'			=> array(
				'title'					=> __( 'UPS Password', 'woocommerce-shipment-tracking' ),
				'type'					=> 'password',
				'desc'					=> __( 'Provide UPS password.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ups[password]',
			),
			'ups_access_key'			=> array(
				'title'					=> __( 'UPS Access Key', 'woocommerce-shipment-tracking' ),
				'type'					=> 'password',
				'desc'					=> __( 'Provide UPS access key.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ups[access_key]',
			),
			'fedex_account_number'		=> array(
				'title'					=> __( 'FedEx Account Number', 'woocommerce-shipment-tracking' ),
				'type'					=> 'text',
				'placeholder'			=> '',
				'desc'					=> __( 'Provide FedEx account number for the live FedEx shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_fedex[account_number]',
			),
			'fedex_meter_number'		=> array(
				'title'					=> __( 'FedEx Meter Number', 'woocommerce-shipment-tracking' ),
				'type'					=> 'text',
				'placeholder'			=> '',
				'desc'					=> __( 'Provide FedEx meter number for the live FedEx shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_fedex[meter_number]',
			),
			'fedex_web_services_key'	=> array(
				'title'					=> __( 'FedEx Web Services', 'woocommerce-shipment-tracking' ),
				'type'					=> 'text',
				'placeholder'			=> '',
				'desc'					=> __( 'Provide FedEx Web Services for the live FedEx shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_fedex[web_services_key]',
			),
			'fedex_password'			=> array(
				'title'					=> __( 'FedEx Password', 'woocommerce-shipment-tracking' ),
				'type'					=> 'text',
				'placeholder'			=> '',
				'desc'					=> __( 'Provide FedEx Password for the live FedEx shipment tracking.', 'woocommerce-shipment-tracking' ),
				'desc_tip'				=> true,
				'id'					=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_fedex[password]',
			),

            'section_end' => array(
                 'type'			=> 'sectionend',
                 'id'			=> Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_section_end'
            ),
        );

        return apply_filters( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_settings', $settings );
    }
}

new WF_Tracking_Settings();
