<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Email Class for Vendor Sale Notification
 * 
 * Handles to the email notification template.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
class Woo_Vou_Vendor_Sale extends WC_Email {

    public $model;

    /**
     * Constructor
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function __construct() {

        global $woo_vou_model;

        $this->model = $woo_vou_model;

        $this->id = 'woo_vou_vendor_sale_notification';
        $this->title = esc_html__('Vendor Sale', 'woovoucher');
        $this->description = esc_html__('Vendor sale emails are sent to product vendor(s) when their product gets sold.', 'woovoucher');

        $this->heading = esc_html__('Vendor Sale Notification', 'woovoucher');
        $this->subject = esc_html__('New Sale!', 'woovoucher');

        $this->template_html = 'emails/vendor-sales.php';
        $this->template_plain = 'emails/plain/vendor-sales.php';

        $this->template_base = WOO_VOU_DIR . '/includes/templates/';

        // Triggers for this email via our do_action
        add_action('woo_vou_vendor_sale_email_notification', array($this, 'trigger'), 20, 1);

        parent::__construct();
    }

    /**
     * Vendor Sale Notification
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function trigger($vendor_data) {

        $attachments = array( );

        // replace variables in the subject/headings
        $this->find[] = '{site_name}';
        $this->replace[] = $vendor_data['site_name'];
        $this->find[] = '{product_title}';
        $this->replace[] = $vendor_data['product_title'];
        $this->find[] = '{product_price}';
        $this->replace[] = $vendor_data['product_price'];
        $this->find[] = '{voucher_code}';
        $this->replace[] = $vendor_data['voucher_code'];
        $this->find[] = '{order_id}';
        $this->replace[] = $vendor_data['order_id'];

        //Asign required object for feature use
        $this->object = $vendor_data;

        $this->send($vendor_data['vendor_email'], $this->get_subject(), $this->get_content(), $this->get_headers(), $attachments);
    }

    /**
	 * Get email subject.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_subject() {
		return esc_html__('New Sale!', 'woovoucher');
	}

	/**
	 * Get email heading.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__('Vendor Sale Notification', 'woovoucher');
	}

    /**
     * Gets the email HTML content
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function get_content_html() {
        ob_start();
        wc_get_template(
                $this->template_html, array(
                    'email_heading' => $this->get_heading(),
                    'site_name' => $this->object['site_name'],
                    'product_title' => $this->object['product_title'],
                    'voucher_code' => $this->object['voucher_code'],
                    'product_price' => $this->object['product_price'],
                    'product_quantity' => $this->object['product_quantity'],
                    'order_id' => $this->object['order_id'],
                    'voucher_link' => $this->object['voucher_link'],
                    'customer_name' => $this->object['customer_name'],
                    'shipping_address' => $this->object['shipping_address'],
                    'shipping_postcode' => $this->object['shipping_postcode'],
                    'shipping_city' => $this->object['shipping_city'],
                    'recipient_name' => $this->object['recipient_name'],
                    'recipient_email' => $this->object['recipient_email'],
                    'order_date' => $this->object['order_date'],
                    'vou_exp_date' => $this->object['vou_exp_date'],
                    'vendor_first_name' => $this->object['vendor_first_name'],
                    'vendor_last_name' => $this->object['vendor_last_name'],
                ), '', $this->template_base
        );
        return ob_get_clean();
    }

    /**
     * Gets the email plain content
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template(
                $this->template_plain, array(
                    'email_heading' => $this->get_heading(),
                    'site_name' => $this->object['site_name'],
                    'product_title' => $this->object['product_title'],
                    'voucher_code' => $this->object['voucher_code'],
                    'product_price' => $this->object['product_price'],
                    'product_quantity' => $this->object['product_quantity'],
                    'order_id' => $this->object['order_id'],
                    'customer_name' => $this->object['customer_name'],
                    'shipping_address' => $this->object['shipping_address'],
                    'shipping_postcode' => $this->object['shipping_postcode'],
                    'shipping_city' => $this->object['shipping_city'],
                    'recipient_name' => $this->object['recipient_name'],
                    'recipient_email' => $this->object['recipient_email'],
                    'order_date' => $this->object['order_date'],
                    'vou_exp_date' => $this->object['vou_exp_date'],
                    'vendor_first_name' => $this->object['vendor_first_name'],
                    'vendor_last_name' => $this->object['vendor_last_name'],
                ), '', $this->template_base
        );
        return ob_get_clean();
    }

    /**
     * Initialize Settings Form Fields
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_html__('Enable/Disable', 'woovoucher'),
                'type' => 'checkbox',
                'label' => esc_html__('Enable this email notification', 'woovoucher'),
                'default' => 'yes',
            ),
            'enabled_sec_vendor_notification' => array(
                'title' => esc_html__('Enable/Disable Secondary Vendor Email Notification', 'woovoucher'),
                'type' => 'checkbox',
                'label' => esc_html__('Enable email notification for secondary vendors', 'woovoucher'),
                'default' => 'no',
            ),
            'subject' => array(
                'title' => esc_html__('Subject', 'woovoucher'),
                'type' => 'text',
                'description' => '<p class="description">' .
                esc_html__('This is the subject line for the vendor sale notification email. Available template tags for subject fields are :', 'woovoucher') .
                '<br /><code>{site_name}</code> - ' . esc_html__('displays the site name', 'woovoucher') .
                '<br /><code>{product_title}</code> - ' . esc_html__('displays the product title.', 'woovoucher') .
                '<br /><code>{product_price}</code> - ' . esc_html__('displays the product price.', 'woovoucher') .
                '<br /><code>{voucher_code}</code> - ' . esc_html__('displays the voucher code.', 'woovoucher') . '</p>',
                'placeholder' => $this->get_default_subject(),
                'default' => '',
            ),
            'heading' => array(
                'title' => esc_html__('Email Heading', 'woovoucher'),
                'type' => 'text',
                'description' => esc_html__('This controls the main heading contained within the email notification. Leave blank to use the default heading:', 'woovoucher') . '<code> ' . $this->heading . '</code>.',
                'placeholder' => $this->get_default_heading(),
                'default' => '',
            ),
            'email_type' => array(
                'title' => esc_html__('Email type', 'woovoucher'),
                'type' => 'select',
                'description' => esc_html__('Choose which format of email to send.', 'woovoucher'),
                'default' => 'html',
                'class' => 'email_type',
                'options' => array(
                    'plain' => esc_html__('Plain text', 'woovoucher'),
                    'html' => esc_html__('HTML', 'woovoucher'),
                ),
            ),
        );
    }

}
