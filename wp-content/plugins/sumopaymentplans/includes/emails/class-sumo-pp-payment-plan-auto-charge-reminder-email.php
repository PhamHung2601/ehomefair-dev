<?php

/**
 * Auto Charge Reminder Order - Email.
 * 
 * @class SUMO_PP_Payment_Plan_Auto_Charge_Reminder_Email
 * @category Class
 */
class SUMO_PP_Payment_Plan_Auto_Charge_Reminder_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'payment_plan_auto_charge_reminder' ;
        $this->name           = 'payment_plan_auto_charge_reminder' ;
        $this->customer_email = true ;
        $this->title          = __( 'Payment Auto Charge Reminder - Payment Plan' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Payment Auto Charge Reminder - Payment Plan will be sent to the customers before charging for the installment payment using the preapproved payment gateway.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-payment-plan-auto-charge-reminder.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-payment-plan-auto-charge-reminder.php' ;

        $this->subject = __( '[{site_title}] - Auto Charge Reminder for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Auto Charge Reminder for {product_with_installment_no}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

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
        $payment_count = sizeof( $this->payment->get_balance_paid_orders() ) + 1 ;

        $content_args[ 'product_title_with_installment' ] = sprintf( __( 'Installment #%s of %s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $payment_count , $content_args[ 'product_title' ] ) ;
        return $content_args ;
    }

}

return new SUMO_PP_Payment_Plan_Auto_Charge_Reminder_Email() ;
