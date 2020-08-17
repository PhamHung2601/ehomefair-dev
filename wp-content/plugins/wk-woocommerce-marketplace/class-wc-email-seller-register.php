<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NEW_SELLER')) :

    /**
     * New Seller Email.
     */
    class NEW_SELLER extends WC_Email
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'new_seller';
            $this->title = __('Seller Register', 'marketplace');
            $this->heading = __('New seller register', 'marketplace');
            $this->subject = '[ '.get_option('blogname').' ] '.__('New seller register', 'marketplace');
            $this->description = __('New seller emails are sent to chosen recipient(s) ', 'marketplace');
            $this->template_html = 'emails/seller-new-account.php';
            $this->template_plain = 'emails/plain/seller-new-account.php';
            $this->template_base = plugin_dir_path(__FILE__).'woocommerce/templates/';
            $this->footer = __('Thanks for choosing marketplace.', 'marketplace');

            // Call parent constructor.
            parent::__construct();

            add_action('new_seller_registration_notification', array($this, 'trigger'), 10, 1);

            // Other settings.
            $this->recipient = $this->get_option('recipient');

            if (!$this->recipient) {
                $this->recipient = get_option('admin_email');
            }
        }

        /**
         * Trigger.
         *
         */
        public function trigger($info)
        {
            $this->data = $info;
            $this->recipient = $info['user_email'];

            if ($this->is_enabled() && $this->get_recipient()) {
                $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
            }

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

return new NEW_SELLER();
