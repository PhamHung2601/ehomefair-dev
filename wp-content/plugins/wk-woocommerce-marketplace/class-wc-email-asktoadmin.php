<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_ATAEMAIL')) :

    /**
     * Ask to admin Email.
     */
    class MP_ATAEMAIL extends WC_Email
    {
        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'new_query';
            $this->title = __('Ask To Admin', 'marketplace');
            $this->description = __('Query emails are sent to chosen recipient(s) ', 'marketplace');
            $this->heading = __('Ask to admin', 'marketplace');
            $this->subject = '['.get_option('blogname').']'.__(' New Query', 'marketplace');
            $this->template_html = 'emails/asktoadmin.php';
            $this->template_plain = 'emails/plain/asktoadmin.php';
            $this->footer = __('Thanks for choosing marketplace.', 'marketplace');
            $this->template_base = plugin_dir_path(__FILE__).'woocommerce/templates/';

            add_action('asktoadmin_mail_notification', array($this, 'trigger'), 10, 3);

            // Call parent constructor.
            parent::__construct();

            // Other settings.
            $this->recipient = get_option('admin_email');
        }

        /**
         * Trigger.
         *
         * @param int $order_id order id
         */
        public function trigger($email, $subject, $ask)
        {
            if (!empty($email) && !empty($subject) && !empty($ask)) {
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);

                $subject = filter_var($subject, FILTER_SANITIZE_STRING);

                $ask = filter_var($ask, FILTER_SANITIZE_STRING);

                $this->data = array(
                    'email' => $email,
                    'subject' => $subject,
                    'ask' => $ask,
                );

                $this->subject = $subject;

                $headers = 'MIME-Version: 1.0'."\n";
                $headers .= 'From: '.$email."\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1'."\n";
                $headers .= "X-Priority: 1 (Highest)\n";
                $headers .= "X-MSMail-Priority: High\n";
                $headers .= "Importance: High\n";

                $this->headers = $headers;
            }

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
            return wc_get_template_html($this->template_html, array(
                'email_heading' => $this->get_heading(),
                'admin_email' => $this->get_recipient(),
                'data' => $this->data,
                'email_heading' => $this->get_heading(),
                'blogname' => $this->get_blogname(),
                'sent_to_admin' => false,
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
                'email_heading' => $this->get_heading(),
                'admin_email' => $this->get_recipient(),
                'data' => $this->data,
                'email_heading' => $this->get_heading(),
                'blogname' => $this->get_blogname(),
                'sent_to_admin' => false,
                'plain_text' => true,
                'email' => $this,
            ), '', $this->template_base);
        }
    }

endif;

return new MP_ATAEMAIL();
