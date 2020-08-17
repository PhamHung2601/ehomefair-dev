<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email Class for Voucher Redeem Notification
 * 
 * Handles to the email notification template.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.1
 */
class Woo_Vou_Redeem_Notification extends WC_Email {

	public $model;

	/**
	 * Constructor
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function __construct() {

		global $woo_vou_model;

		$this->model	= $woo_vou_model;

		$this->id          = 'woo_vou_redeem_notification';
		$this->title       = esc_html__( 'Voucher Redeem Notification', 'woovoucher' );
		$this->description = esc_html__( 'Voucher Redeem Notification Email are sent to chosen recipient(s) when a voucher code is redeemed.', 'woovoucher' );

		$this->heading     = esc_html__( 'Voucher Redeem Notification', 'woovoucher' );
		$this->subject     = esc_html__( 'Voucher code has been redeemed!', 'woovoucher' );

		$this->template_html  = 'emails/vou-redeem-notification.php';
		$this->template_plain = 'emails/plain/vou-redeem-notification.php';

		$this->template_base  = WOO_VOU_DIR . '/includes/templates/';

		// Triggers for this email via our do_action
		add_action( 'woo_vou_redeem_email_notification', array( $this, 'trigger' ), 20, 1 );

		// Other settings
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		parent::__construct();
	}

	/**
	 * Gift Notification
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function trigger( $vou_redeem_info ) {

	    $this->find[] 		= '{voucode}';
	    $this->replace[] 	= $vou_redeem_info['voucode'];
	    $this->find[] 		= '{first_name}';
	    $this->replace[] 	= $vou_redeem_info['first_name'];
	    $this->find[] 		= '{last_name}';
	    $this->replace[] 	= $vou_redeem_info['last_name'];

	    //Assign required object for feature use
	    $this->object		= $vou_redeem_info;

        if ( ! $this->is_enabled() ) {
			return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get valid recipients.
	 * @return string
	 */
	public function get_recipient() {

		global $woo_vou_vendor_role;

		// get prefix
    	$prefix = WOO_VOU_META_PREFIX;

    	// Get recipient email list from the input box we provided
		$recipient  = apply_filters( 'woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object );
		$recipients = array_map( 'trim', explode( ',', $recipient ) );
		$recipients = array_filter( $recipients, 'is_email' );

		// Check whether "Enable/Disable Customer Email Notification" is ticked
		// If it is than add customer email to recipient email array
		$send_email_to_customer = $this->get_option( 'enabled_customer_email_notification' );
		if( !empty( $send_email_to_customer ) && $send_email_to_customer == 'yes' && !empty( $this->object['order_id'] ) ) {

			// Get order and billing email
			$_order = wc_get_order( $this->object['order_id'] );
			if( !empty( $_order ) ) {
				$recipients[] = $_order->get_billing_email();
			}
		}

		// Check whether "Enable/Disable Vendor Email Notification" is ticked
		// If it is than add vendor email to recipient email array
		$send_email_to_vendor = $this->get_option( 'enabled_vendor_email_notification' );
		if( !empty( $send_email_to_vendor ) && $send_email_to_vendor == 'yes' && !empty( $this->object['voucodeid'] ) ) {

			// Get email for primary vendor, if set
			$primary_vendor_id 		= get_post_field( 'post_author', $this->object['voucodeid'] );
			$primary_vendor 		= get_user_by('id', $primary_vendor_id);
			//Get User roles
			$user_roles	= isset( $primary_vendor->roles ) ? $primary_vendor->roles : array();
			$user_role	= array_shift( $user_roles );
	
			if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role

				$primary_vendor_email 	= $primary_vendor->user_email; // Get vendor email id

				// If recipients are not empty and primary vendor email is in array of recipient email
				if( !empty( $recipients ) && !in_array( $primary_vendor_email, $recipients ) ) {
	
					$recipients[] = $primary_vendor_email; // Add vendor email in recipient email list
				}
			}

			// Get secondary vendor user from post meta
			$sec_vendor_users = get_post_meta( $this->object['voucodeid'], $prefix.'sec_vendor_users', true );
			if( !empty( $sec_vendor_users ) ) {

				// Get array of secondary vendor user
				$sec_vendor_user_arr = explode(',', $sec_vendor_users);
				if( !empty( $sec_vendor_user_arr ) && is_array( $sec_vendor_user_arr ) ) {

					foreach( $sec_vendor_user_arr as $sec_vendor_id ) {

						// Get secondary vendor user email
						$sec_vendor_user 	= get_user_by('id', $sec_vendor_id);
						if( $sec_vendor_user ) {
							$sec_vendor_email 	= $sec_vendor_user->user_email;
							if( !empty( $recipients ) && !in_array( $sec_vendor_email, $recipients ) ) {
				
								$recipients[] = $sec_vendor_email;
							}
						}
					}
				}
			}
		}

		// Return all recipient email after comma seperation
		return implode( ', ', $recipients );
	}

	/**
	 * Get email subject.
	 *
	 * @since  3.3.1
	 * @return string
	 */
	public function get_default_subject() {
		return esc_html__( 'Voucher code has been redeemed!', 'woovoucher' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  3.3.1
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__( 'Voucher Code Redeemed', 'woovoucher' );
	}

	/**
	 * Gets the email HTML content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'email_heading' 		=> $this->get_heading(),
				'voucode'				=> $this->object['voucode'],
				'first_name'			=> $this->object['first_name'],
				'last_name'				=> $this->object['last_name'],
				'redeem_date'			=> $this->object['redeem_date'],
				'redeem_method' 		=> $this->object['redeem_method'],
				'redeem_amount' 		=> $this->object['redeem_amount'],
				'vou_redeem_method' 	=> $this->object['vou_redeem_method']
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Gets the email plain content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'email_heading' 		=> $this->get_heading(),
				'voucode'				=> $this->object['voucode'],
				'first_name'			=> $this->object['first_name'],
				'last_name'				=> $this->object['last_name'],
				'redeem_date'			=> $this->object['redeem_date'],
				'redeem_method' 		=> $this->object['redeem_method'],
				'redeem_amount' 		=> $this->object['redeem_amount'],
				'vou_redeem_method' 	=> $this->object['vou_redeem_method']
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initialize Settings Form Fields
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.3.1
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'   => esc_html__( 'Enable/Disable', 'woovoucher' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable this email notification', 'woovoucher' ),
				'default' => 'no',
			),
			'recipient' => array(
				'title'         => esc_html__( 'Recipient(s)', 'woocommerce' ),
				'type'          => 'text',
				'description'   => sprintf( esc_html__( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder'   => '',
				'default'       => esc_attr( get_option( 'admin_email' ) ),
			),
			'enabled_customer_email_notification' => array(
				'title'   		=> esc_html__( 'Enable/Disable Customer Email Notification', 'woovoucher' ),
				'type'    		=> 'checkbox',
				'label'   		=> esc_html__( 'Enable/Disable Customer Email Notification', 'woovoucher' ),
				'default' 		=> 'no',
				'description'	=> esc_html__( 'Check this box if you want to send Voucher Redeem Notification Email to Customer.' )
			),
			'enabled_vendor_email_notification' => array(
				'title'   		=> esc_html__( 'Enable/Disable Vendor Email Notification', 'woovoucher' ),
				'type'    		=> 'checkbox',
				'label'   		=> esc_html__( 'Enable/Disable Vendor Email Notification', 'woovoucher' ),
				'default' 		=> 'no',
				'description'	=> esc_html__( 'Check this box if you want to send Voucher Redeem Notification Email to Vendor.' )
			),
			'subject' => array(
				'title'       => esc_html__( 'Subject', 'woovoucher' ),
				'type'        => 'text',
				'description' => '<p class="description">'.
									esc_html__( 'This is the subject line for the voucher redeem notification email. Available template tags for subject fields are :', 'woovoucher' ).
									'<br /><code>{voucode}</code> - '.esc_html__( 'displays the voucher code.', 'woovoucher' ).
									'<br /><code>{first_name}</code> - '.esc_html__( 'displays the first name of vendor.', 'woovoucher' ).
									'<br /><code>{last_name}</code> - '.esc_html__( 'displays the last name of vendor.', 'woovoucher' ).'</p>',
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading' => array(
				'title'       => esc_html__( 'Email Heading', 'woovoucher' ),
				'type'        => 'text',
				'description' => esc_html__( 'This is the subject line for the voucher redeem notification email. Leave blank to use the default heading:', 'woovoucher' ) . '<code> '. $this->heading . '</code>.',
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => esc_html__( 'Email type', 'woovoucher' ),
				'type'        => 'select',
				'description' => esc_html__( 'Choose which format of email to send.', 'woovoucher' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options' => array(
					'plain'     => esc_html__( 'Plain text', 'woovoucher' ),
					'html'      => esc_html__( 'HTML', 'woovoucher' ),
				),
			),
		);
	}
}