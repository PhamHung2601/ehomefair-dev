<?php

/**
 * Invoice Order - Email.
 * 
 * @class SUMO_PP_Deposit_Balance_Payment_Invoice_Email
 * @category Class
 */
class SUMO_PP_Deposit_Balance_Payment_Invoice_Email extends SUMO_PP_Abstract_Email {

    /**
     * Constructor.
     * 
     * @access public
     */
    function __construct() {
        $this->id             = SUMO_PP_PLUGIN_PREFIX . 'deposit_balance_payment_invoice' ;
        $this->name           = 'deposit_balance_payment_invoice' ;
        $this->customer_email = true ;
        $this->title          = __( 'Balance Payment Invoice - Deposit' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->description    = addslashes( __( 'Balance Payment Invoice - Deposit will be sent to the customers when the balance payment for the purchase has to be charged' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $this->template_html  = 'emails/sumo-pp-deposit-balance-payment-invoice.php' ;
        $this->template_plain = 'emails/plain/sumo-pp-deposit-balance-payment-invoice.php' ;

        $this->subject = __( '[{site_title}] - Invoice for Balance Payment of {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->heading = __( 'Invoice for Balance Payment of {product_name}' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;

        $this->subject_paid = $this->subject ;
        $this->heading_paid = $this->heading ;

        // Call parent constructor
        parent::__construct() ;
    }

}

return new SUMO_PP_Deposit_Balance_Payment_Invoice_Email() ;
