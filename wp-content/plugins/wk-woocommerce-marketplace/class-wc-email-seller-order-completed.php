<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_EMAIL_Seller_Order_Completed')) :

    /**
     * Seller New Order Email.
     */
    class MP_EMAIL_Seller_Order_Completed extends WC_Email
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'seller_order_completed';
            $this->title = __('Seller Order Completed', 'marketplace');
            $this->heading = __('Seller Order Completed', 'marketplace');
            $this->subject = '['.get_option('blogname').']' . ' ' . __('Seller Order Completed', 'marketplace');
            $this->description = __('Order complete emails are sent to sellers when their orders are marked completed and usually indicate that their orders have been shipped.', 'marketplace');
            $this->template_html = 'emails/seller-order-completed.php';
            $this->template_plain = 'emails/plain/seller-order-completed.php';
            $this->template_base = plugin_dir_path(__FILE__).'woocommerce/templates/';
            $this->footer = __('Thanks for choosing marketplace.', 'marketplace');

            add_action('woocommerce_seller_order_completed_notification', array($this, 'trigger'), 10, 2);

            // Call parent constructor
            parent::__construct();

            // Other settings.
            $this->recipient = $this->get_option('recipient');

            if (!$this->recipient) {
                $this->recipient = get_option('admin_email');
            }
        }

        /**
         * Trigger.
         *
         * @param int $order_id
         */
        public function trigger($items, $key)
        {
            $this->data = $items;
            $this->recipient = $key;

            if (!$this->is_enabled() || !$this->get_recipient()) {
                return;
            }

            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }

        /**
         * Get content html.
         *
         * @return string
         */
        public function get_content_html()
        {
            return wc_get_template_html(
                $this->template_html, array(
                    'email_heading' => $this->get_heading(),
                    'customer_email' => $this->get_recipient(),
                    'sent_to_admin' => false,
                    'plain_text' => false,
                    'email' => $this,
                    'data' => $this->data,
                ), '', $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @return string
         */
        public function get_content_plain()
        {
            return wc_get_template_html(
                $this->template_plain, array(
                    'email_heading' => $this->get_heading(),
                    'customer_email' => $this->get_recipient(),
                    'sent_to_admin' => false,
                    'plain_text' => true,
                    'data' => $this->data,
                    'email' => $this,
                ), '', $this->template_base
            );
        }
    }

endif;

return new MP_EMAIL_Seller_Order_Completed();
