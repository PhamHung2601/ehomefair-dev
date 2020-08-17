<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Email_Shop_Follower_Notification')) :

    /**
     * Reply to seller regarding query Email.
     */
    class WC_Email_Shop_Follower_Notification extends WC_Email
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'shop_follower';
            $this->title = __('Shop Follower Notification', 'marketplace');
            $this->heading = __('Notification to Shop Followers', 'marketplace');
            $this->subject = '['.get_option('blogname').'] '.__('Shop Follower Notification', 'marketplace');
            $this->description = __('Query emails are sent to chosen recipient(s) ', 'marketplace');
            $this->template_html = 'emails/shop-follower-notification.php';
            $this->template_plain = 'emails/plain/shop-follower-notification.php';
            $this->template_base = plugin_dir_path(__FILE__).'woocommerce/templates/';
            $this->footer = __('Thanks for choosing marketplace.', 'marketplace');

            add_action('woocommerce_shop_follower_notification', array($this, 'trigger'), 10, 3);

            // Call parent constructor.
            parent::__construct();

            // Other settings.
            $this->recipient = $this->get_option('recipient');

            if (!$this->recipient) {
                $this->recipient = get_option('admin_email');
            }
        }

        /**
         * Trigger.
         */
        public function trigger($customer_email, $subject, $feedback)
        {

            $this->recipient = $customer_email;

            if( !empty( $subject ) ) {
                $this->subject = $subject;
            }

            $this->data = $feedback;

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

return new WC_Email_Shop_Follower_Notification();
