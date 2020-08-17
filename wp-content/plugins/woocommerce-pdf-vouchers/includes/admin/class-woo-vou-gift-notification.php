<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Email Class for Gift Notification
 * 
 * Handles to the email notification template.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
class Woo_Vou_Gift_Notification extends WC_Email {

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

        $this->id = 'woo_vou_gift_notification';
        $this->title = esc_html__('Gift Notification', 'woovoucher');
        $this->description = esc_html__('Gift Notification email will be sent to customer choosen recipient(s) when their order gets access to downloads.', 'woovoucher');

        $this->heading = esc_html__('Gift Notification', 'woovoucher');
        $this->subject = esc_html__('You have received a voucher from', 'woovoucher') . ' {first_name} {last_name}';

        $this->template_html = 'emails/gift-notification.php';
        $this->template_plain = 'emails/plain/gift-notification.php';

        $this->template_base = WOO_VOU_DIR . '/includes/templates/';

        // Triggers for this email via our do_action
        add_action('woo_vou_gift_email_notification', array($this, 'trigger'), 20, 1);

        parent::__construct();
    }

    /**
     * Gift Notification
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function trigger($gift_data) {

        // Declare flag variables to indicate whether the value is already set in $this->find
        $first_name_key = $last_name_key = $recipient_name_key = $product_price_key = false;

        // replace variables in the subject/headings
        foreach ($this->find as $key => $value) {

            if ($value == '{first_name}') {
                $this->replace[$key] = $gift_data['first_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
                $first_name_key = true; // Set appropriate flag to true
            }
            if ($value == '{last_name}') {
                $this->replace[$key] = $gift_data['last_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
                $last_name_key = true; // Set appropriate flag to true
            }
            if ($value == '{recipient_name}') {
                $this->replace[$key] = $gift_data['recipient_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
                $recipient_name_key = true; // Set appropriate flag to true
            }
            if ($value == '{product_price}') {
                $this->replace[$key] = html_entity_decode(strip_tags($gift_data['product_price'])); // If value is set, than replace the value in $this->replace by getting key in $this->find
                $product_price_key = true; // Set appropriate flag to true
            }
        }

        // If flag is not set then create new value in $this->find and $this->replace array
        if ($first_name_key == false) {
            $this->find[] = '{first_name}';
            $this->replace[] = $gift_data['first_name'];
        }
        if ($last_name_key == false) {
            $this->find[] = '{last_name}';
            $this->replace[] = $gift_data['last_name'];
        }
        if ($recipient_name_key == false) {
            $this->find[] = '{recipient_name}';
            $this->replace[] = $gift_data['recipient_name'];
        }

        if ($product_price_key == false) {
            $this->find[] = '{product_price}';
            $this->replace[] = html_entity_decode(strip_tags($gift_data['product_price']));
        }


        //Asign required object for feature use
        $this->object = $gift_data;

        if (isset($gift_data['attachments']) && !empty($gift_data['attachments'])) {//check if attachment not empty
            add_filter('woocommerce_email_attachments', array($this, 'get_email_attachments'), 10, 3);
        }

        if (isset($gift_data['woo_vou_extra_emails']) && !empty($gift_data['woo_vou_extra_emails'])) {//check if extra emails not empty
            add_filter('woocommerce_email_headers', array($this, 'add_bcc_to_wc_admin_gift_notify'), 10, 3);
        }

        if (!$this->is_enabled()) {
            return;
        }

        $this->send($gift_data['recipient_email'], $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * Get email subject.
     *
     * @since  3.1.5
     * @return string
     */
    public function get_default_subject() {
        return esc_html__('You have received a voucher from', 'woovoucher') . ' {first_name} {last_name}';
    }

    /**
     * Get email heading.
     *
     * @since  3.1.5
     * @return string
     */
    public function get_default_heading() {
        return esc_html__('Gift Notification', 'woovoucher');
    }

    /**
     * Get attachments.
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function get_email_attachments($data, $id, $object) {

        return apply_filters('woo_vou_gift_notification_attachment', $this->object['attachments'], $data, $id, $object);
    }

    /**
     * Add Extra Emails.
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.4
     */
    public function add_bcc_to_wc_admin_gift_notify($headers = '', $id = '', $wc_email = array()) {

        if ($id == 'woo_vou_gift_notification' && !empty($this->object['woo_vou_extra_emails'])) {
            $headers .= 'Bcc: ' . $this->object['woo_vou_extra_emails'] . "\r\n";
        }

        return $headers;
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
            'first_name' => $this->object['first_name'],
            'last_name' => $this->object['last_name'],
            'recipient_name' => $this->object['recipient_name'],
            'voucher_link' => $this->object['voucher_link'],
            'recipient_message' => $this->object['recipient_message'],
            'product_price' => $this->object['product_price'],
            'object' => $this->object,
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
            'voucher_link' => $this->object['voucher_link_plain'],
            'recipient_message' => $this->object['recipient_message'],
            'product_price' => $this->object['product_price']
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
            'recipient' => array(
                'title' => esc_html__('Recipient(s)', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(esc_html__('Enter recipients (comma separated) for this email. Defaults to %s Note: It will only send emails to recipient(s) if %s Send Emails to Admin %s settings is enabled.', 'woocommerce'), '<code>' . esc_attr(get_option('admin_email')) . '</code><br>', '<strong>', '</strong>'),
                'placeholder' => '',
                'default' => esc_attr(get_option('admin_email')),
            ),
            'subject' => array(
                'title' => esc_html__('Subject', 'woovoucher'),
                'type' => 'text',
                'description' => '<p class="description">' .
                esc_html__('This is the subject line for the gift notification email. Available template tags for subject fields are :', 'woovoucher') .
                '<br /><code>{first_name}</code> - ' . esc_html__('displays the first name of customer.', 'woovoucher') .
                '<br /><code>{last_name}</code> - ' . esc_html__('displays the last name of customer.', 'woovoucher') .
                '<br /><code>{recipient_name}</code> - ' . esc_html__('displays the recipient name.', 'woovoucher') .
                '<br /><code>{product_price}</code> - ' . esc_html__('displays the product price.', 'woovoucher') . '</p>',
                'placeholder' => $this->get_default_subject(),
                'default' => '',
            ),
            'heading' => array(
                'title' => __('Email Heading', 'woovoucher'),
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

        // Remove recipient email option from settings
        $admin_premission = get_option('vou_allow_bcc_to_admin');
        if ($admin_premission == "no") {
            unset($this->form_fields['recipient']);
        }
    }

}
