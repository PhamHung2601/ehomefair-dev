<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Manage payment products in checkout.
 * 
 * @class SUMO_PP_Checkout_Manager
 * @category Class
 */
class SUMO_PP_Checkout_Manager {

    /**
     * Check if checkout contains payment products
     */
    protected static $checkout_contains_payments ;

    /**
     * The single instance of the class.
     */
    protected static $instance = null ;

    /**
     * Create instance for SUMO_PP_Checkout_Manager.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self() ;
        }
        return self::$instance ;
    }

    /**
     * Init SUMO_PP_Checkout_Manager.
     */
    public function init() {
        add_action( 'woocommerce_before_checkout_form', array( $this, 'force_guest_signup_on_checkout' ), 999 ) ;
        add_action( 'woocommerce_checkout_process', array( $this, 'force_create_account_for_guest' ), 999 ) ;
    }

    /**
     * Check if checkout contains payment products
     * @return bool 
     */
    public function checkout_contains_payments() {
        if ( is_bool( self::$checkout_contains_payments ) ) {
            return self::$checkout_contains_payments ;
        }

        self::$checkout_contains_payments = false ;

        if ( is_checkout_pay_page() ) {
            global $wp ;

            if ( isset( $_GET[ 'pay_for_order' ] ) && $wp->query_vars[ 'order-pay' ] ) {
                $maybe_payment_order = _sumo_pp_get_order( $wp->query_vars[ 'order-pay' ] ) ;

                if ( $maybe_payment_order && $maybe_payment_order->contains_payment_data() ) {
                    if ( $maybe_payment_order->is_parent() ) {
                        self::$checkout_contains_payments = true ;
                    } else {
                        self::$checkout_contains_payments = $maybe_payment_order->is_payment_order() ;
                    }
                }
            }
        } else {
            if ( _sumo_pp()->cart->cart_contains_payment() || _sumo_pp()->orderpp->is_enabled() ) {
                self::$checkout_contains_payments = true ;
            }
        }

        return self::$checkout_contains_payments ;
    }

    /**
     * Force Display Signup on Checkout for Guest. 
     * Since Guest don't have the permission to buy Deposit Payments.
     */
    public function force_guest_signup_on_checkout( $checkout ) {
        if ( is_user_logged_in() || $checkout->is_registration_required() ) {
            return ;
        }

        if ( ! $checkout->is_registration_enabled() && _sumo_pp()->orderpp->can_user_deposit_payment() ) {
            add_filter( 'woocommerce_checkout_registration_enabled', '__return_true', 99 ) ;
            add_filter( 'woocommerce_checkout_registration_required', '__return_true', 99 ) ;
        } else if ( $this->checkout_contains_payments() ) {
            $checkout->enable_signup         = true ;
            $checkout->enable_guest_checkout = false ;
        }
    }

    /**
     * To Create account for Guest.
     */
    public function force_create_account_for_guest() {
        if ( ! is_user_logged_in() && $this->checkout_contains_payments() ) {
            $_POST[ 'createaccount' ] = 1 ;
        }
    }

}
