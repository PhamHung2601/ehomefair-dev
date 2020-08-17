<?php

/**
 * Abstract Payment Email
 * 
 * @abstract SUMO_PP_Abstract_Email
 */
abstract class SUMO_PP_Abstract_Email extends WC_Email {

    /**
     * @var array Supports
     */
    public $supports = array( 'mail_to_admin' ) ;

    /**
     * @var SUMO_PP_Payment
     */
    public $payment = false ;

    /**
     * @var SUMO_PP_Job_Scheduler
     */
    public $scheduler = false ;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->template_base = SUMO_PP_PLUGIN_TEMPLATE_PATH ;
        $this->mail_to_admin = 'yes' === $this->get_option( 'mail_to_admin' ) ;

        // Call WC_Email constuctor
        parent::__construct() ;
    }

    /**
     * Populate the Email
     * 
     * @param int $order_id
     * @param SUMO_PP_Payment $payment
     * @param string $to
     */
    protected function populate( $order_id, $payment ) {
        $this->payment   = $payment ;
        $this->order_id  = absint( $order_id ) ;
        $this->object    = wc_get_order( $this->order_id ) ;
        $this->scheduler = _sumo_pp_get_job_scheduler( $this->payment ) ;

        if ( $this->supports( 'recipient' ) ) {
            return ;
        }

        if ( $this->payment ) {
            $this->recipient = $this->payment->get_customer_email() ;
        }

        if ( empty( $this->recipient ) ) {
            $this->recipient = $this->object->get_billing_email() ;
        }

        if ( $this->supports( 'mail_to_admin' ) && $this->mail_to_admin ) {
            $this->recipient = $this->recipient . ',' . get_option( 'admin_email' ) ;
        }
    }

    /**
     * Check this Email supported feature.
     * @param string $type
     * @return boolean
     * 
     */
    public function supports( $type = '' ) {
        return in_array( $type, $this->supports ) ;
    }

    /**
     * Trigger.
     * 
     * @return bool on Success
     */
    public function trigger( $order_id, $payment ) {
        $this->populate( $order_id, $payment ) ;

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return false ;
        }

        $payment_count = sizeof( $this->payment->get_balance_paid_orders() ) ;

        if ( in_array( $this->name, array(
                    'payment_plan_invoice',
                    'payment_plan_auto_charge_reminder',
                    'payment_plan_overdue',
                ) )
        ) {
            $payment_count += 1 ;
        }

        if ( $this->object ) {
            $this->placeholders[ '{order_date}' ]   = wc_format_datetime( $this->object->get_date_created() ) ;
            $this->placeholders[ '{order_number}' ] = $this->object->get_order_number() ;
        }

        $this->find[ 'payment-no' ]                  = '{payment_no}' ;
        $this->find[ 'product-name' ]                = '{product_name}' ;
        $this->find[ 'product-with-installment-no' ] = '{product_with_installment_no}' ;

        $this->replace[ 'payment-no' ]                  = $this->payment->get_payment_number() ;
        $this->replace[ 'product-name' ]                = $this->payment->get_formatted_product_name( array(
            'tips'           => false,
            'maybe_variable' => false,
            'qty'            => false,
                ) ) ;
        $this->replace[ 'product-with-installment-no' ] = sprintf( __( 'Installment #%s of %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $payment_count, $this->payment->get_formatted_product_name( array(
                    'tips'           => false,
                    'maybe_variable' => false,
                    'qty'            => false,
                ) ) ) ;

        do_action( 'sumopaymentplans_before_email_send', $this ) ;

        $sent = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() ) ;

        if ( $sent ) {
            do_action( 'sumopaymentplans_email_sent', $this ) ;
        } else {
            do_action( 'sumopaymentplans_email_failed_to_sent', $this ) ;
        }

        return $sent ;
    }

    /**
     * get_type function.
     *
     * @return string
     */
    public function get_email_type() {
        return class_exists( 'DOMDocument' ) ? 'html' : '' ;
    }

    /**
     * Format date to display.
     * @param int|string $date
     * @return string
     */
    public function format_date( $date = '' ) {
        return _sumo_pp_get_date_to_display( $date ) ;
    }

    /**
     * Get content args.
     *
     * @return array
     */
    public function get_content_args() {
        $product_title = $this->payment->get_formatted_product_name( array(
            'tips' => false,
            'qty'  => false,
                ) ) ;

        return array(
            'order'                          => _sumo_pp_get_order( $this->object ),
            'payment_id'                     => $this->payment->id,
            'payment'                        => $this->payment,
            'email_heading'                  => $this->get_heading(),
            'sent_to_admin'                  => true,
            'plain_text'                     => false,
            'email'                          => $this,
            'product_title'                  => $product_title,
            'product_title_with_installment' => $product_title,
            'next_action_on'                 => '',
            'next_action_status'             => '',
            'overdue_date'                   => '', //BKWD CMPT
                ) ;
    }

    /**
     * Get content HTMl.
     *
     * @return string
     */
    public function get_content_html() {
        ob_start() ;
        _sumo_pp_get_template( $this->template_html, $this->get_content_args() ) ;
        return ob_get_clean() ;
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return '' ;
    }

    /**
     * Display form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => 'yes'
            ) ) ;

        if ( $this->supports( 'recipient' ) ) {
            $this->form_fields = array_merge( $this->form_fields, array(
                'recipient' => array(
                    'title'       => __( 'Recipient(s)', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                    'placeholder' => '',
                    'default'     => '',
                    'desc_tip'    => true,
                ) ) ) ;
        }

        $this->form_fields = array_merge( $this->form_fields, array(
            'subject' => array(
                'title'       => __( 'Email Subject', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'text',
                'description' => sprintf( __( 'Defaults to <code>%s</code>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading' => array(
                'title'       => __( 'Email Heading', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'text',
                'description' => sprintf( __( 'Defaults to <code>%s</code>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ) ) ) ;

        if ( $this->supports( 'paid_order' ) ) {
            $this->form_fields = array_merge( $this->form_fields, array(
                'subject_paid' => array(
                    'title'       => __( 'Email Subject (paid)', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'Defaults to <code>%s</code>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $this->subject_paid ),
                    'placeholder' => '',
                    'default'     => ''
                ),
                'heading_paid' => array(
                    'title'       => __( 'Email Heading (paid)', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'type'        => 'text',
                    'description' => sprintf( __( 'Defaults to <code>%s</code>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $this->heading_paid ),
                    'placeholder' => '',
                    'default'     => ''
                ) ) ) ;
        }

        if ( $this->supports( 'pay_link' ) ) {
            $this->form_fields = array_merge( $this->form_fields, array(
                'enable_pay_link' => array(
                    'title'   => __( 'Enable Payment Link in Mail', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'type'    => 'checkbox',
                    'default' => 'yes'
                ) ) ) ;
        }

        if ( $this->supports( 'mail_to_admin' ) ) {
            $this->form_fields = array_merge( $this->form_fields, array(
                'mail_to_admin' => array(
                    'title'   => __( 'Send Email to Admin', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'type'    => 'checkbox',
                    'default' => 'no'
                ) ) ) ;
        }
    }

}