<?php

/**
 * Invoice Order - Email.
 * 
 * @class SUMO_PP_Payment_Plan_Success_Email
 * @category Class
 */
class SUMO_PP_Payment_Plan_Success_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'payment_plan_success' ;
        $this->name           = 'payment_plan_success' ;
        $this->customer_email = true ;
        $this->title          = __( 'Payment Success – Payment Plan' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Payment Success – Payment Plan will be sent to the customers when their installment payment for the payment has been received successfully.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-payment-plan-success.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-payment-plan-success.php' ;

        $this->subject = __( '[{site_title}] - Payment Received for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Payment Received for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

        $this->subject_paid = $this->subject ;
        $this->heading_paid = $this->heading ;

        // Call parent constructor
        parent::__construct() ;
    }

    /**
     * Get content args.
     *
     * @return array
     */
    public function get_content_args() {
        $content_args  = parent::get_content_args() ;
        $payment_count = sizeof( $this->payment->get_balance_paid_orders() ) ;

        $content_args[ 'product_title_with_installment' ] = sprintf( __( 'Installment #%s of %s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $payment_count , $content_args[ 'product_title' ] ) ;
        return $content_args ;
    }

}

return new SUMO_PP_Payment_Plan_Success_Email() ;
