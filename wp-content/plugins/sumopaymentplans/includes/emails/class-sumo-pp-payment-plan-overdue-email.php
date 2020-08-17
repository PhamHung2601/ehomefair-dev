<?php

/**
 * Invoice Order - Email.
 * 
 * @class SUMO_PP_Payment_Plan_Overdue_Email
 * @category Class
 */
class SUMO_PP_Payment_Plan_Overdue_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'payment_plan_overdue' ;
        $this->name           = 'payment_plan_overdue' ;
        $this->customer_email = true ;
        $this->title          = __( 'Payment Overdue – Payment Plan' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Payment Overdue – Payment Plan will be sent to the customers when their installment for their Payment Plan is currently Overdue.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-payment-plan-overdue.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-payment-plan-overdue.php' ;

        $this->subject = __( '[{site_title}] - Payment Overdue for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Payment Overdue for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

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

        $payment_count                                    = sizeof( $this->payment->get_balance_paid_orders() ) + 1 ;
        $content_args[ 'product_title_with_installment' ] = sprintf( __( 'Installment #%s of %s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $payment_count , $content_args[ 'product_title' ] ) ;
        return $content_args ;
    }

}

return new SUMO_PP_Payment_Plan_Overdue_Email() ;
