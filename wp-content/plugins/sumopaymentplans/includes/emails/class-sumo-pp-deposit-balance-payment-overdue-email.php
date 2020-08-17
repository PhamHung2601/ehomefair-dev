<?php

/**
 * Invoice Order - Email.
 * 
 * @class SUMO_PP_Deposit_Balance_Payment_Overdue_Email
 * @category Class
 */
class SUMO_PP_Deposit_Balance_Payment_Overdue_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'deposit_balance_payment_overdue' ;
        $this->name           = 'deposit_balance_payment_overdue' ;
        $this->customer_email = true ;
        $this->title          = __( 'Balance Payment Overdue - Deposit' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Balance Payment Overdue - Deposit will be sent to the customers when the balance payment for the product purchase is currently Overdue.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-deposit-balance-payment-overdue.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-deposit-balance-payment-overdue.php' ;

        $this->subject = __( '[{site_title}] - Payment Overdue for {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Payment Overdue for {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

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
        $content_args = parent::get_content_args() ;

        if( 'auto' === $this->payment->get_payment_mode() ) {
            $next_action_on     = $this->scheduler->get_next_scheduled_job( 'retry_payment_in_overdue' ) ;
            $next_action_status = 'overdue' ;
        } else {
            $next_action_on     = $next_action_status = false ;
        }

        if( ! $next_action_on ) {
            $next_action_on     = $this->scheduler->get_next_scheduled_job( 'notify_awaiting_cancel' ) ;
            $next_action_status = 'await_cancl' ;
        }

        if( ! $next_action_on ) {
            $next_action_on     = $this->scheduler->get_next_scheduled_job( 'notify_cancelled' ) ;
            $next_action_status = 'cancelled' ;
        }

        /** overdue_date - BKWD CMPT * */
        if( $next_action_on ) {
            $content_args[ 'next_action_on' ]     = $content_args[ 'overdue_date' ]       = _sumo_pp_get_date_to_display( $next_action_on ) ;
            $content_args[ 'next_action_status' ] = _sumo_pp_get_payment_status_name( $next_action_status ) ;
        }
        return $content_args ;
    }

}

return new SUMO_PP_Deposit_Balance_Payment_Overdue_Email() ;
