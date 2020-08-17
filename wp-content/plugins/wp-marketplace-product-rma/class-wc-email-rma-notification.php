<?php

if (! defined('ABSPATH') ) {
    exit;
}

if (! class_exists('RMA_Notification') ) :

    /**
     * Seller Approve Email.
     */
    class RMA_Notification extends WC_Email
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id               = 'rma_notification';
            $this->title            = __('RMA Notification', 'marketplace-rma');
            $this->heading          = __('RMA Notification', 'marketplace-rma');
            $this->subject          = '[' . get_option('blogname') . ']' . __(' RMA Notification', 'marketplace-rma');
            $this->description      = __('On using RMA this mail is sent to user ', 'marketplace-rma');
            $this->template_html    = 'emails/mprma-mail-handler.php';
			$this->template_plain   = 'emails/plain/mprma-mail-handler.php';
			$this->template_base    = plugin_dir_path(__FILE__) . 'woocommerce/templates/';
			$this->footer           = __('Thanks for choosing Marketplace RMA.', 'marketplace-rma');
			
			add_action( 'woocommerce_mp_rma_mail_notification', array( $this, 'trigger' ) );

            // Call parent constructor
            parent::__construct();

            // Other settings
			$this->recipient = $this->get_option('recipient');
			
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
        }

        /**
         * Trigger.
         *
         */
        public function trigger( $data )
        {
            
			if( empty( $data ) ) {
				return;
			} else {
				$this->email_message = $data['msg'];
				$this->recipient = $data['email'];
            }

            if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
            }

        }

        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html()
        {
            return wc_get_template_html(
                $this->template_html, array(
                    'email_heading'      => $this->get_heading(),
                    'email_message'      => $this->email_message,
                    'customer_email'     => $this->get_recipient(),
                    'blogname'           => $this->get_blogname(),
                    'sent_to_admin'      => false,
                    'plain_text'         => false,
                    'email'              => $this,
				), '', $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain()
        {
            return wc_get_template_html(
                $this->template_plain, array(
                    'email_heading'      => $this->get_heading(),
                    'email_message'      => $this->email_message,
                    'customer_email'     => $this->get_recipient(),
                    'blogname'           => $this->get_blogname(),
                    'sent_to_admin'      => false,
                    'plain_text'         => true,
                    'email'              => $this,
                ), '', $this->template_base
            );
        }

    }

endif;

return new RMA_Notification();