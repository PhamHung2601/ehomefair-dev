<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Manage payments in My account page
 * 
 * @class SUMO_PP_My_Account_Manager
 * @category Class
 */
class SUMO_PP_My_Account_Manager {

    public static $template_base = SUMO_PP_PLUGIN_TEMPLATE_PATH ;

    /**
     * The single instance of the class.
     */
    protected static $instance = null ;

    /**
     * Create instance for SUMO_PP_My_Account_Manager.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self() ;
        }
        return self::$instance ;
    }

    /**
     * Init SUMO_PP_My_Account_Manager.
     */
    public function init() {
        //Compatible with Woocommerce v2.6.x and above
        add_filter( 'woocommerce_account_menu_items', array( $this, 'set_my_account_menu_items' ) ) ;
        add_action( 'woocommerce_account_sumo-pp-my-payments_endpoint', array( $this, 'my_payments' ) ) ;
        add_action( 'woocommerce_account_sumo-pp-view-payment_endpoint', array( $this, 'view_payment' ) ) ;
        add_action( 'sumopaymentplans_my_payments_sumo-pp-view-payment_endpoint', array( $this, 'view_payment' ) ) ;
        add_shortcode( 'sumo_pp_my_payments', array( $this, 'my_payments' ), 10, 3 ) ;

        //Compatible up to Woocommerce v2.5.x
        add_action( 'woocommerce_before_my_account', array( $this, 'bkd_cmptble_my_payments' ) ) ;
        add_filter( 'wc_get_template', array( $this, 'bkd_cmptble_view_payment' ), 10, 5 ) ;

        add_filter( 'user_has_cap', array( $this, 'customer_has_capability' ), 10, 3 ) ;

        //May be do some restrictions in Pay for Order page
        if ( isset( $_GET[ 'pay_for_order' ] ) ) {
            add_filter( 'woocommerce_product_is_in_stock', array( $this, 'prevent_from_outofstock_product' ), 20, 2 ) ;
        }

        //Prevent cancel action in myorders page.
        add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'prevent_cancel_action' ), 99, 2 ) ;
    }

    /**
     * Checks if a user has a certain capability.
     *
     * @param array $allcaps All capabilities.
     * @param array $caps    Capabilities.
     * @param array $args    Arguments.
     *
     * @return array The filtered array of all capabilities.
     */
    public function customer_has_capability( $allcaps, $caps, $args ) {
        if ( isset( $caps[ 0 ] ) ) {
            switch ( $caps[ 0 ] ) {
                case 'sumo-pp-view-payment':
                    $user_id = absint( $args[ 1 ] ) ;
                    $payment = _sumo_pp_get_payment( absint( $args[ 2 ] ) ) ;

                    if ( $payment && $user_id === $payment->get_customer_id() ) {
                        $allcaps[ 'sumo-pp-view-payment' ] = true ;
                    }
                    break ;
            }
        }
        return $allcaps ;
    }

    /**
     * Get my payments.
     */
    public function get_payments() {

        try {
            $payments = _sumo_pp()->query->get( array(
                'type'       => 'sumo_pp_payments',
                'status'     => array_keys( _sumo_pp_get_payment_statuses() ),
                'meta_key'   => '_customer_id',
                'meta_value' => get_current_user_id(),
                    ) ) ;

            if ( empty( $payments ) ) {
                throw new Exception( __( "You don't have any payment.", SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            _sumo_pp_get_template( 'myaccount/my-payments.php', array(
                'payments' => $payments,
            ) ) ;
        } catch ( Exception $e ) {
            _sumo_pp_get_template( 'myaccount/no-payments-found.php', array(
                'message' => $e->getMessage(),
            ) ) ;
        }
    }

    /**
     * Set our menus under My account menu items
     * @param array $items
     * @return array
     */
    public function set_my_account_menu_items( $items ) {
        $menu     = array(
            'sumo-pp-my-payments' => apply_filters( 'sumopaymentplans_my_payments_title', __( 'My Payments', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ),
                ) ;
        $position = 2 ;

        $items = array_slice( $items, 0, $position ) + $menu + array_slice( $items, $position, count( $items ) - 1 ) ;

        return $items ;
    }

    /**
     * Output my payments table.
     */
    public function my_payments( $atts = '', $content = '', $tag = '' ) {
        global $wp ;

        if ( 'sumo_pp_my_payments' === $tag ) {
            if ( ! empty( $wp->query_vars ) ) {
                foreach ( $wp->query_vars as $key => $value ) {
                    // Ignore pagename param.
                    if ( 'pagename' === $key ) {
                        continue ;
                    }

                    if ( has_action( 'sumopaymentplans_my_payments_' . $key . '_endpoint' ) ) {
                        do_action( 'sumopaymentplans_my_payments_' . $key . '_endpoint', $value ) ;
                        return ;
                    }
                }
            }
        }

        echo $this->get_payments() ;
    }

    /**
     * Output Payment content.
     * @param int $payment_id
     */
    public function view_payment( $payment_id ) {
        $payment = _sumo_pp_get_payment( $payment_id ) ;

        if ( ! $payment || ! current_user_can( 'sumo-pp-view-payment', $payment_id ) ) {
            echo '<div class="woocommerce-error">' . esc_html__( 'Invalid payment.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) . ' <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="wc-forward">' . esc_html__( 'My account', SUMO_PP_PLUGIN_TEXT_DOMAIN ) . '</a></div>' ;
            return ;
        }

        _sumo_pp_get_template( 'myaccount/view-payment.php', array(
            'payment_id'            => $payment_id,
            'payment'               => $payment,
            'initial_payment_order' => wc_get_order( $payment->get_initial_payment_order_id() ),
        ) ) ;
    }

    /**
     * Output my payments table up to Woocommerce v2.5.x
     */
    public function bkd_cmptble_my_payments() {

        if ( _sumo_pp_is_wc_version( '<', '2.6' ) ) {
            echo '<h2>' . apply_filters( 'sumopaymentplans_my_payments_title', __( 'My Payments', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) . '</h2>' ;
            echo $this->get_payments() ;
        }
    }

    /**
     * Output payment content up to Woocommerce v2.5.x
     * @global object $wp
     * @param string $located
     * @param string $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     * @return string
     */
    public function bkd_cmptble_view_payment( $located, $template_name, $args, $template_path, $default_path ) {
        global $wp ;

        if ( _sumo_pp_is_wc_version( '<', '2.6' ) && isset( $_GET[ 'payment-id' ] ) && _sumo_pp_get_payment( $_GET[ 'payment-id' ] ) ) {

            $wp->query_vars[ 'sumo-pp-view-payment' ] = absint( $_GET[ 'payment-id' ] ) ;

            return self::$template_base . 'myaccount/view-payment.php' ;
        }
        return $located ;
    }

    public function prevent_from_outofstock_product( $is_in_stock, $product ) {
        if ( ! $is_in_stock ) {
            if ( $balance_payable_order = _sumo_pp_get_balance_payable_order_in_pay_for_order_page() ) {
                return true ;
            }
        }
        return $is_in_stock ;
    }

    /**
     * Prevent cancel option in my orders page for payment order.
     * 
     * @param array $actions
     * @param SUMO_PP_Order $order
     * @return array
     */
    public function prevent_cancel_action( $actions, $order ) {
        $maybe_payment_order = _sumo_pp_get_order( $order ) ;

        if ( $maybe_payment_order && $maybe_payment_order->is_payment_order() ) {
            unset( $actions[ 'cancel' ] ) ;
        }

        return $actions ;
    }

}
