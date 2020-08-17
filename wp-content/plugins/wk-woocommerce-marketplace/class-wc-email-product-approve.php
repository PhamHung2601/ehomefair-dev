<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_EMAIL_Product_Approval')) :

    /**
     * Product publish Email.
     */
    class MP_EMAIL_Product_Approval extends WC_Email
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'product_approve';
            $this->title = __('Product Publish', 'marketplace');
            $this->description = __('Product Publish emails are sent to chosen recipient(s) ', 'marketplace');
            $this->heading = __('Product approval request', 'marketplace');
            $this->subject = '['.get_option('blogname').']'.__(' Product Approval Request', 'marketplace');
            $this->template_html = 'emails/product-approval.php';
            $this->template_plain = 'emails/plain/product-approval.php';
            $this->footer = __('Thanks for choosing marketplace.', 'marketplace');
            $this->template_base = plugin_dir_path(__FILE__).'woocommerce/templates/';

            add_action('woocommerce_product_notifier_admin_notification', array($this, 'trigger'), 10, 2);

            // Call parent constructor
            parent::__construct();

            // Other settings
            $this->recipient = get_option('admin_email');
        }

        /**
         * Trigger.
         *
         * @param int $order_id
         */
        public function trigger($user_id, $product_id)
        {
            if (!empty($user_id) && !empty($product_id)) {
                $this->user_id = $user_id;
                $this->product_id = $product_id;
            }

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
            return wc_get_template_html($this->template_html, array(
                'user' => $this->user_id,
                'product' => $this->product_id,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this,
            ), '', $this->template_base);
        }

        /**
         * Get content plain.
         *
         * @return string
         */
        public function get_content_plain()
        {
            return wc_get_template_html($this->template_plain, array(
                'user' => $this->user_id,
                'product' => $this->product_id,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text' => true,
                'email' => $this,
            ), '', $this->template_base);
        }
    }

endif;

return new MP_EMAIL_Product_Approval();
