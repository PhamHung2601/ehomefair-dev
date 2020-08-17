<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Load Payment Gateways
 * 
 * @class SUMO_PP_Payment_Gateways
 * @category Class
 */
class SUMO_PP_Payment_Gateways {

    /**
     * Get payment gateways to load in to the WC checkout
     * @var array 
     */
    protected static $load_gateways = array() ;

    /**
     * Get loaded automatic payment gateways
     * @var array 
     */
    protected static $auto_payment_gateways ;

    /**
     * Check if Automatic payment gateways enabled
     * @var bool 
     */
    protected static $auto_payment_gateways_enabled ;

    /**
     * Check if Manual payment gateways enabled
     * @var bool 
     */
    protected static $manual_payment_gateways_enabled ;

    /**
     * Get mode of payment gateway
     * @var mixed 
     */
    protected static $auto_payment_gateway_mode ;

    /**
     * Get the disabled payment gateways in checkout
     * @var array 
     */
    protected static $disabled_payment_gateways ;

    /**
     * The single instance of the class.
     */
    protected static $instance = null ;

    /**
     * Create instance for SUMO_PP_Payment_Gateways.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self() ;
        }
        return self::$instance ;
    }

    /**
     * Init SUMO_PP_Payment_Gateways.
     */
    public function init() {
        add_action( 'plugins_loaded', array( $this, 'load_payment_gateways' ), 20 ) ;
        add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ) ) ;
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'set_payment_gateways' ), 999 ) ;
        add_filter( 'woocommerce_gateway_description', array( $this, 'render_payment_mode_selector' ), 10, 2 ) ;
    }

    /**
     * Check if Automatic payment gateways enabled
     * @return bool 
     */
    public function auto_payment_gateways_enabled() {
        if ( is_bool( self::$auto_payment_gateways_enabled ) ) {
            return self::$auto_payment_gateways_enabled ;
        }

        return self::$auto_payment_gateways_enabled = get_option( SUMO_PP_PLUGIN_PREFIX . 'enable_automatic_payment_gateways', 'yes' ) ;
    }

    /**
     * Check if Manual payment gateways enabled
     * @return bool 
     */
    public function manual_payment_gateways_enabled() {
        if ( is_bool( self::$manual_payment_gateways_enabled ) ) {
            return self::$manual_payment_gateways_enabled ;
        }

        return self::$manual_payment_gateways_enabled = get_option( SUMO_PP_PLUGIN_PREFIX . 'enable_manual_payment_gateways', 'yes' ) ;
    }

    /**
     * Get mode of payment gateway
     * @return string 
     */
    public function get_mode_of_payment_gateway() {
        if ( ! is_null( self::$auto_payment_gateway_mode ) ) {
            return self::$auto_payment_gateway_mode ;
        }

        return self::$auto_payment_gateway_mode = get_option( SUMO_PP_PLUGIN_PREFIX . 'automatic_payment_gateway_mode', 'auto-or-manual' ) ;
    }

    /**
     * Get the disabled payment gateways in checkout
     * @return array 
     */
    public function get_disabled_payment_gateways() {
        if ( is_array( self::$disabled_payment_gateways ) ) {
            return self::$disabled_payment_gateways ;
        }

        return self::$disabled_payment_gateways = get_option( SUMO_PP_PLUGIN_PREFIX . 'disabled_payment_gateways', array() ) ;
    }

    /**
     * Get loaded automatic payment gateways
     * @return array 
     */
    public function get_auto_payment_gateways() {
        if ( is_array( self::$auto_payment_gateways ) ) {
            return self::$auto_payment_gateways ;
        }

        self::$auto_payment_gateway = array() ;

        if ( ! empty( self::$load_gateways ) ) {
            foreach ( self::$load_gateways as $gateway ) {
                if ( $gateway->supports( 'sumo_paymentplans' ) ) {
                    self::$auto_payment_gateways[] = $gateway->id ;
                }
            }
        }
        return self::$auto_payment_gateways ;
    }

    /**
     * Get the Customer chosen payment mode in checkout
     * 
     * @param string $gateway_id
     * @return bool
     */
    public function get_chosen_mode_of_payment( $gateway_id ) {

        if ( $this->auto_payment_gateways_enabled() && _sumo_pp()->checkout->checkout_contains_payments() ) {
            if (
                    'force-auto' === $this->get_mode_of_payment_gateway() ||
                    ('auto-or-manual' === $this->get_mode_of_payment_gateway() && isset( $_POST[ "sumo-pp-{$gateway_id}-auto-payment-enabled" ] ) && 'yes' === $_POST[ "sumo-pp-{$gateway_id}-auto-payment-enabled" ] )
            ) {
                return 'auto' ;
            }
        }
        return 'manual' ;
    }

    /**
     * Get payment gateways to load in to the WC checkout
     */
    public function load_payment_gateways() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) )
            return ;

        self::$load_gateways[] = include_once('gateways/stripe/class-sumo-pp-stripe-gateway.php') ;
    }

    /**
     * Add payment gateways awaiting to load
     * 
     * @param object $gateways
     * @return array
     */
    public function add_payment_gateways( $gateways ) {
        if ( empty( self::$load_gateways ) ) {
            return $gateways ;
        }

        foreach ( self::$load_gateways as $gateway ) {
            $gateways[] = $gateway ;
        }
        return $gateways ;
    }

    /**
     * Check whether specific payment gateway is needed in checkout
     * 
     * @param WC_Payment_Gateway $gateway
     * @return bool
     */
    public function need_payment_gateway( $gateway ) {
        $need = true ;

        if ( _sumo_pp()->checkout->checkout_contains_payments() ) {
            // This is high priority to disable any payment gateways
            if ( in_array( $gateway->id, ( array ) $this->get_disabled_payment_gateways() ) ) {
                $need = false ;
            } else if ( ! $this->auto_payment_gateways_enabled() ) {
                // Do not allow automatic payment gateways
                if ( $gateway->supports( 'sumo_paymentplans' ) ) {
                    $need = false ;
                }
            } else {
                // Allow only automatic payment gateways
                if ( 'force-auto' === $this->get_mode_of_payment_gateway() && ! $gateway->supports( 'sumo_paymentplans' ) ) {
                    $need = false ;
                }
            }
        }

        return apply_filters( 'sumopaymentplans_need_payment_gateway', $need, $gateway ) ;
    }

    /**
     * Handle payment gateways in checkout
     * 
     * @param array $_available_gateways
     * @return array
     */
    public function set_payment_gateways( $_available_gateways ) {
        if ( is_admin() ) {
            return $_available_gateways ;
        }

        foreach ( $_available_gateways as $gateway_name => $gateway ) {
            if ( ! isset( $gateway->id ) ) {
                continue ;
            }

            if ( ! $this->need_payment_gateway( $gateway ) ) {
                unset( $_available_gateways[ $gateway_name ] ) ;
            }
        }
        return $_available_gateways ;
    }

    /**
     * Render checkbox to select the mode of payment in automatic payment gateways by the customer
     * 
     * @param string $description
     * @param string $gateway_id
     * @return string
     */
    public function render_payment_mode_selector( $description, $gateway_id ) {
        if ( ! $this->auto_payment_gateways_enabled() ) {
            return $description ;
        }

        if ( 'auto-or-manual' !== $this->get_mode_of_payment_gateway() ) {
            return $description ;
        }

        if ( isset( $_GET[ 'pay_for_order' ] ) ) {
            if ( ! _sumo_pp_get_balance_payable_order_in_pay_for_order_page() ) {
                return $description ;
            }
        } else if ( ! _sumo_pp()->checkout->checkout_contains_payments() ) {
            return $description ;
        }

        if ( ! apply_filters( "sumopaymentplans_allow_payment_mode_selection_in_{$gateway_id}_payment_gateway", false ) ) {
            return $description ;
        }

        $description .= '<p class="sumo_pp_payment_mode_selection">'
                . '<input type="checkbox" name="sumo-pp-' . $gateway_id . '-auto-payment-enabled" value="yes"/> ' . __( 'Enable Automatic Payments', SUMO_PP_PLUGIN_TEXT_DOMAIN )
                . '</p>' ;
        return $description ;
    }

}
