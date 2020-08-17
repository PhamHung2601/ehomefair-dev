<?php

/**
 * Auto Charge Reminder Order - Email.
 * 
 * @class SUMO_PP_Deposit_Balance_Payment_Auto_Charge_Reminder_Email
 * @category Class
 */
class SUMO_PP_Deposit_Balance_Payment_Auto_Charge_Reminder_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'deposit_balance_payment_auto_charge_reminder' ;
        $this->name           = 'deposit_balance_payment_auto_charge_reminder' ;
        $this->customer_email = true ;
        $this->title          = __( 'Balance Payment Auto Charge Reminder - Deposit' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Balance Payment Auto Charge Reminder - Deposit will be sent to the customers before charging for the balance payment using the preapproved payment gateway' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-deposit-balance-payment-auto-charge-reminder.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-deposit-balance-payment-auto-charge-reminder.php' ;

        $this->subject = __( '[{site_title}] - Auto Charge Reminder for Balance Payment of {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Auto Charge Reminder for Balance Payment of {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

        $this->subject_paid = $this->subject ;
        $this->heading_paid = $this->heading ;

        // Call parent constructor
        parent::__construct() ;
    }

}

return new SUMO_PP_Deposit_Balance_Payment_Auto_Charge_Reminder_Email() ;
