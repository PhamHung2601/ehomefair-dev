<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Manage payment products in cart
 * 
 * @class SUMO_PP_Cart_Manager
 * @category Class
 */
class SUMO_PP_Cart_Manager {

    protected static $allow_only_single_payment_product ;
    protected static $allow_multiple_payment_products ;
    protected static $should_allow_same_plans ;
    protected static $charge_shipping_during ;
    protected static $add_to_cart_transient ;

    const CANNOT_ADD_NORMAL_PRODUCTS_WHILE_PAYMENTS_IN_CART = 501 ;
    const CANNOT_ADD_PAYMENTS_WHILE_NORMAL_PRODUCTS_IN_CART = 502 ;
    const CANNOT_ADD_MULTIPLE_PAYMENTS_IN_CART              = 503 ;
    const INVALID_PAYMENTS_REMOVED_FROM_CART                = 504 ;
    const INVALID_DEPOSIT_AMOUNT_IS_ENTERED                 = 505 ;
    const CANNOT_ADD_DIFFERENT_PLANS_IN_CART                = 506 ;
    const INVALID_PLANS_REMOVED_FROM_CART                   = 507 ;

    /**
     * The single instance of the class.
     */
    protected static $instance = null ;

    /**
     * Create instance for SUMO_PP_Cart_Manager.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self() ;
        }
        return self::$instance ;
    }

    /**
     * Init SUMO_PP_Cart_Manager.
     */
    public function init() {
        add_filter( 'woocommerce_cart_item_name', array( $this, 'render_payment_plan_name' ), 10, 3 ) ;
        add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'render_payment_plan_name' ), 10, 3 ) ;
        add_filter( 'woocommerce_cart_item_price', array( $this, 'render_payment_info' ), 10, 3 ) ;
        add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'render_payment_info' ), 10, 3 ) ;
        add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'render_balance_payable' ), 10, 3 ) ;
        add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'render_cart_balance_payable' ), 999 ) ;
        add_action( 'woocommerce_review_order_after_order_total', array( $this, 'render_cart_balance_payable' ), 999 ) ;
        add_filter( 'woocommerce_cart_totals_order_total_html', array( $this, 'render_payable_now' ), 10 ) ;

        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 999, 6 ) ;
        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'validate_cart_session' ), 999 ) ;
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_payment_item_data' ), 99, 4 ) ;
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'refresh_cart' ), 998 ) ;
        add_action( 'woocommerce_after_calculate_totals', array( $this, 'refresh_cart' ), 998 ) ;
        add_filter( 'woocommerce_product_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;
        add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;
        add_filter( 'woocommerce_calculated_total', array( $this, 'prevent_shipping_charges_in_initial_order' ), 99, 2 ) ;
    }

    public function allow_only_single_payment_product() {
        if ( ! is_bool( self::$allow_only_single_payment_product ) ) {
            self::$allow_only_single_payment_product = 'single-payment' === get_option( SUMO_PP_PLUGIN_PREFIX . 'products_that_can_be_placed_in_an_order', 'any' ) ? true : false ;
        }
        return self::$allow_only_single_payment_product ;
    }

    public function allow_multiple_payment_products() {
        if ( ! is_bool( self::$allow_multiple_payment_products ) ) {
            self::$allow_multiple_payment_products = 'multiple-payments' === get_option( SUMO_PP_PLUGIN_PREFIX . 'products_that_can_be_placed_in_an_order', 'any' ) ? true : false ;
        }
        return self::$allow_multiple_payment_products ;
    }

    public function should_allow_same_plans() {
        if ( ! is_bool( self::$should_allow_same_plans ) ) {
            self::$should_allow_same_plans = 'same-plan' === get_option( SUMO_PP_PLUGIN_PREFIX . 'allow_payment_plans_in_cart_as', 'same-r-different-plan' ) ? true : false ;
        }
        return self::$should_allow_same_plans ;
    }

    public function is_payment_item( $item ) {

        if ( is_array( $item ) ) {
            if ( ! empty( $item[ 'sumopaymentplans' ][ 'payment_product_props' ] ) ) {
                return $item[ 'sumopaymentplans' ] ;
            }
        } else if ( is_string( $item ) ) {
            if ( ! empty( WC()->cart->cart_contents[ $item ][ 'sumopaymentplans' ][ 'payment_product_props' ] ) ) {
                return WC()->cart->cart_contents[ $item ][ 'sumopaymentplans' ] ;
            }
        } else {
            $product_id = false ;
            if ( is_callable( array( $item, 'get_id' ) ) ) {
                $product_id = $item->get_id() ;
            } else if ( is_numeric( $item ) ) {
                $product_id = $item ;
            }

            if ( $product_id && ! empty( WC()->cart->cart_contents ) ) {
                foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                    if (
                            ! empty( $cart_item[ 'sumopaymentplans' ][ 'payment_product_props' ] ) &&
                            $product_id == $cart_item[ 'sumopaymentplans' ][ 'product_id' ]
                    ) {
                        return $cart_item[ 'sumopaymentplans' ] ;
                    }
                }
            }
        }
        return false ;
    }

    public function cart_contains_payment() {
        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $this->is_payment_item( $cart_item ) ) {
                    return true ;
                }
            }
        }
        return false ;
    }

    public function cart_contains_payment_of( $context, $value ) {
        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $payment = $this->is_payment_item( $cart_item ) ) {
                    if ( isset( $payment[ 'payment_product_props' ][ $context ] ) && in_array( $payment[ 'payment_product_props' ][ $context ], ( array ) $value ) ) {
                        return $item_key ;
                    }
                }
            }
        }
        return false ;
    }

    public function maybe_get_duplicate_products_in_cart( $product_id ) {
        $duplicate_products = array() ;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( ! empty( $cart_item[ 'product_id' ] ) && $product_id == ($cart_item[ 'variation_id' ] ? $cart_item[ 'variation_id' ] : $cart_item[ 'product_id' ]) ) {
                    $duplicate_products[] = $item_key ;
                }
            }
        }
        return $duplicate_products ;
    }

    public function charge_shipping_during() {
        $valid_cart = WC()->cart->needs_shipping() && 1 === sizeof( WC()->cart->cart_contents ) && (WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax()) > 0 ;

        if ( ! $valid_cart ) {
            return 'initial-payment' ;
        }

        if ( ! $this->allow_only_single_payment_product() ) {
            return 'initial-payment' ;
        }

        if ( ! self::$charge_shipping_during ) {
            self::$charge_shipping_during = get_option( SUMO_PP_PLUGIN_PREFIX . 'charge_shipping_during', 'initial-payment' ) ;
        }

        return self::$charge_shipping_during ;
    }

    public function get_payments_from_cart( $context = null, $value = null ) {
        $payments = array() ;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $payment = $this->is_payment_item( $cart_item ) ) {
                    if ( ! is_null( $context ) ) {
                        if ( isset( $payment[ 'payment_product_props' ][ $context ] ) && in_array( $payment[ 'payment_product_props' ][ $context ], ( array ) $value ) ) {
                            $payments[ $item_key ] = $payment ;
                        }
                    } else {
                        $payments[ $item_key ] = $payment ;
                    }
                }
            }
        }
        return $payments ;
    }

    public function get_plan_id_from_cart() {
        $plans = array() ;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $payment = $this->is_payment_item( $cart_item ) ) {
                    $plans[ $item_key ] = $payment[ 'payment_plan_props' ][ 'plan_id' ] ;
                }
            }
        }
        return $plans ;
    }

    public function get_cart_balance_payable_amount() {
        $remaining_payable_amount = 0 ;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $payment = $this->is_payment_item( $cart_item ) ) {
                    $remaining_payable_amount += $payment[ 'remaining_payable_amount' ] ;
                }
            }
        }
        return $remaining_payable_amount ;
    }

    public function get_cart_total_payable_amount() {
        $total_payable_amount = 0 ;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
                if ( $payment = $this->is_payment_item( $cart_item ) ) {
                    if ( isset( $payment[ 'discount_amount' ] ) && is_numeric( $payment[ 'discount_amount' ] ) ) {
                        $total_payable_amount += ( $payment[ 'total_payable_amount' ] - $payment[ 'discount_amount' ] ) ;
                    } else {
                        $total_payable_amount += $payment[ 'total_payable_amount' ] ;
                    }
                }
            }
        }
        return $total_payable_amount ;
    }

    public function get_distinct_plans_in_cart() {
        $plans           = $this->get_plan_id_from_cart() ;
        $diff_plan_items = array() ;

        if ( ! empty( $plans ) && count( array_unique( $plans ) ) > 1 ) {
            // Comman plans in cart.
            $common_plans      = array_diff_assoc( $plans, array_unique( $plans ) ) ;
            // First comman plan in cart.
            $first_comman_plan = ! is_null( $common_plans ) ? ( array ) array_shift( $common_plans ) : array() ;
            //Different plan products item key.
            $diff_plan_items   = array_keys( array_diff( $plans, $first_comman_plan ) ) ;
        }

        return $diff_plan_items ;
    }

    public function get_payment_info_to_display( $cart_item, $context = 'default' ) {
        if ( ! empty( $cart_item[ 'sumopaymentplans' ][ 'payment_product_props' ][ 'payment_type' ] ) ) {
            $payment_data = $cart_item[ 'sumopaymentplans' ] ;
        }

        if ( empty( $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) ) {
            return '' ;
        }

        $shortcodes = _sumo_pp_get_shortcodes_from_cart_r_checkout( $payment_data ) ;

        $info = '' ;
        switch ( $context ) {
            case 'plan_name':
                if ( 'payment-plans' !== $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) {
                    break ;
                }

                $label = get_option( SUMO_PP_PLUGIN_PREFIX . 'payment_plan_label' ) ;

                if ( $label && false === strpos( $label, '[' ) && false === strpos( $label, ']' ) ) {
                    $info = sprintf( __( '<p><strong>%s</strong> <br>%s</p>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $label, $shortcodes[ 'content' ][ '[sumo_pp_payment_plan_name]' ] ) ;
                } else {
                    $info = str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], $label ) ;
                }
                break ;
            case 'balance_payable':
                $info  = str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], get_option( SUMO_PP_PLUGIN_PREFIX . 'balance_payable_label' ) ) ;
                break ;
            default :
                $label = get_option( SUMO_PP_PLUGIN_PREFIX . 'next_payment_date_label' ) ;
                $info  = '<p>' ;
                if ( 'payment-plans' === $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) {
                    if ( $shortcodes[ 'content' ][ '[sumo_pp_payment_plan_desc]' ] ) {
                        $info .= str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], get_option( SUMO_PP_PLUGIN_PREFIX . 'payment_plan_desc_label' ) ) ;
                    }

                    if ( 'enabled' === $payment_data[ 'payment_plan_props' ][ 'sync' ] && $payment_data[ 'down_payment' ] <= 0 ) {
                        $label = get_option( SUMO_PP_PLUGIN_PREFIX . 'first_payment_on_label' ) ;
                    }
                } else {
                    if ( 'before' === $payment_data[ 'payment_product_props' ][ 'pay_balance_type' ] ) {
                        $label = get_option( SUMO_PP_PLUGIN_PREFIX . 'balance_payment_due_date_label' ) ;
                    }
                }

                $info .= str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], get_option( SUMO_PP_PLUGIN_PREFIX . 'total_payable_label' ) ) ;

                if ( 'payment-plans' === $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) {
                    $info .= str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], get_option( SUMO_PP_PLUGIN_PREFIX . 'next_installment_amount_label' ) ) ;
                }

                if ( $shortcodes[ 'content' ][ '[sumo_pp_next_payment_date]' ] ) {
                    if ( $label && false === strpos( $label, '[' ) && false === strpos( $label, ']' ) ) {
                        $info .= sprintf( __( '<br><small style="color:#777;">%s <strong>%s</strong></small>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $label, $shortcodes[ 'content' ][ '[sumo_pp_next_payment_date]' ] ) ;
                    } else {
                        $info .= str_replace( $shortcodes[ 'find' ], $shortcodes[ 'replace' ], $label ) ;
                    }
                }
                $info .= '</p>' ;
        }
        return $info ;
    }

    public function add_cart_notice( $code ) {
        switch ( $code ) {
            case 501:
                return wc_add_notice( __( 'You can\'t add this product to Cart as a Product with Payment Plan is in Cart.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 502:
                return wc_add_notice( __( 'You can\'t add this product to Cart as normal products is in Cart.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 503:
                return wc_add_notice( __( 'You can\'t add this product to Cart as Product(s) with Payment Plan is in Cart.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 504:
                return wc_add_notice( __( 'Some of the Product(s) are removed from the Cart as Product with Payment Plan can\'t be bought together with other product(s).', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 505:
                return wc_add_notice( __( 'Enter the deposit amount and try again!!', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 506:
                return wc_add_notice( __( 'Same Payment Plan product only can add to cart.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
            case 507:
                return wc_add_notice( __( 'Invalid Payment plan product removed from cart.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'error' ) ;
        }
    }

    public function render_payment_plan_name( $return, $cart_item, $item_key ) {
        if ( (is_checkout() && 'woocommerce_cart_item_name' === current_filter()) || ! $this->is_payment_item( $cart_item ) ) {
            return $return ;
        }

        $return .= $this->get_payment_info_to_display( $cart_item, 'plan_name' ) ;
        return apply_filters( 'sumopaymentplans_payment_plan_name_html', $return, $cart_item ) ;
    }

    public function render_payment_info( $price, $cart_item, $item_key ) {
        if ( ! $payment = $this->is_payment_item( $cart_item ) ) {
            return $price ;
        }

        $return = '' ;
        if (
                'pay-in-deposit' === $payment[ 'payment_product_props' ][ 'payment_type' ] ||
                ('payment-plans' === $payment[ 'payment_product_props' ][ 'payment_type' ] && 'no' === get_option( SUMO_PP_PLUGIN_PREFIX . 'hide_product_price_for_payment_plans', 'no' ))
        ) {
            if ( is_cart() ) {
                $return .= wc_price( floatval( $payment[ 'payment_product_props' ][ 'product_price' ] ) ) ;
            } else if ( is_checkout() ) {
                $return .= $price ;
            }
        }
        $return .= $this->get_payment_info_to_display( $cart_item ) ;
        return $return ;
    }

    public function render_balance_payable( $product_subtotal, $cart_item, $item_key ) {
        if ( $this->is_payment_item( $cart_item ) ) {
            $product_subtotal .= $this->get_payment_info_to_display( $cart_item, 'balance_payable' ) ;
        }
        return $product_subtotal ;
    }

    public function render_cart_balance_payable() {
        $remaining_payable_amount = $this->get_cart_balance_payable_amount() ;
        $total_payable_amount     = $this->get_cart_total_payable_amount() ;

        if ( $remaining_payable_amount > 0 ) {
            echo '<tr class="' . SUMO_PP_PLUGIN_PREFIX . 'balance_payable_amount">'
            . '<th>' . get_option( SUMO_PP_PLUGIN_PREFIX . 'balance_payable_amount_label' ) . '</th>'
            . '<td data-title="' . get_option( SUMO_PP_PLUGIN_PREFIX . 'balance_payable_amount_label' ) . '">' . wc_price( $remaining_payable_amount ) . '</td>'
            . '</tr>' ;
        }

        if ( $total_payable_amount > 0 ) {
            echo '<tr class="' . SUMO_PP_PLUGIN_PREFIX . 'total_payable_amount">'
            . '<th>' . get_option( SUMO_PP_PLUGIN_PREFIX . 'total_payable_amount_label' ) . '</th>'
            . '<td data-title="' . get_option( SUMO_PP_PLUGIN_PREFIX . 'total_payable_amount_label' ) . '">' . wc_price( $total_payable_amount ) . '</td>'
            . '</tr>' ;
        }
    }

    public function render_payable_now( $total ) {
        if ( $this->cart_contains_payment() ) {
            $total .= sprintf( __( '<p class="%spayable_now"><small style="color:#777;">Payable now</small></p>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), SUMO_PP_PLUGIN_PREFIX ) ;

            if ( 'final-payment' === $this->charge_shipping_during() ) {
                $total .= '<div>' ;
                $total .= '<small style="color:#777;font-size:smaller;">' ;
                $total .= sprintf( __( '(Shipping amount <strong>%s%s</strong> will be calculated during final payment)', SUMO_PP_PLUGIN_TEXT_DOMAIN ), get_woocommerce_currency_symbol(), WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax() ) ;
                $total .= '</small>' ;
                $total .= '</div>' ;
            }
        }
        return $total ;
    }

    public function get_request() {
        $payment_type        = null ;
        $deposited_amount    = null ;
        $calc_deposit        = false ;
        $chosen_payment_plan = null ;

        if ( isset( $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'payment_type' ] ) ) {
            $payment_type = wc_clean( $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'payment_type' ] ) ;

            switch ( $payment_type ) {
                case 'pay-in-deposit':
                    if ( isset( $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'deposited_amount' ] ) ) {
                        $deposited_amount = $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'deposited_amount' ] ;
                    } else if ( isset( $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'calc_deposit' ] ) ) {
                        $calc_deposit = ( bool ) $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'calc_deposit' ] ;
                    }
                    break ;
                case 'payment-plans':
                    if ( isset( $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' ] ) ) {
                        $chosen_payment_plan = $_REQUEST[ SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' ] ;
                    }
                    break ;
            }
        }

        return array(
            'payment_type'        => $payment_type,
            'deposited_amount'    => $deposited_amount,
            'calc_deposit'        => $calc_deposit,
            'chosen_payment_plan' => $chosen_payment_plan,
                ) ;
    }

    public function validate_add_to_cart( $bool, $product_id, $quantity, $variation_id = null, $variations = null, $cart_item_data = null ) {
        $add_to_cart_product = $variation_id ? $variation_id : $product_id ;
        $requested           = $this->get_request() ;

        if ( ! in_array( $requested[ 'payment_type' ], array( 'pay-in-deposit', 'payment-plans' ) ) ) {
            if ( $this->cart_contains_payment() ) {
                if ( $this->allow_only_single_payment_product() || $this->allow_multiple_payment_products() ) {
                    $this->add_cart_notice( self::CANNOT_ADD_NORMAL_PRODUCTS_WHILE_PAYMENTS_IN_CART ) ;
                    return false ;
                }

                if ( $duplicate_products = $this->maybe_get_duplicate_products_in_cart( $add_to_cart_product ) ) {
                    array_map( array( WC()->cart, 'remove_cart_item' ), $duplicate_products ) ;
                }
            }
            return $bool ;
        }

        if ( 'pay-in-deposit' === $requested[ 'payment_type' ] && ! $requested[ 'calc_deposit' ] && ! is_numeric( $requested[ 'deposited_amount' ] ) ) {
            $this->add_cart_notice( self::INVALID_DEPOSIT_AMOUNT_IS_ENTERED ) ;
            return false ;
        }

        self::$add_to_cart_transient = _sumo_pp()->product->get_props( $add_to_cart_product ) ;

        if ( $duplicate_products = $this->maybe_get_duplicate_products_in_cart( $add_to_cart_product ) ) {
            array_map( array( WC()->cart, 'remove_cart_item' ), $duplicate_products ) ;
        }

        if ( empty( WC()->cart->cart_contents ) ) {
            return $bool ;
        }

        remove_action( 'woocommerce_cart_loaded_from_session', array( $this, 'validate_cart_session' ), 999 ) ;

        if ( _sumo_pp()->product->is_payment_product( self::$add_to_cart_transient ) ) {
            // Single Payment Plan OR Deposit Product.
            if ( $this->allow_only_single_payment_product() ) {
                if ( $this->cart_contains_payment() ) {
                    $this->add_cart_notice( self::CANNOT_ADD_MULTIPLE_PAYMENTS_IN_CART ) ;
                    return false ;
                } else {
                    $this->add_cart_notice( self::CANNOT_ADD_PAYMENTS_WHILE_NORMAL_PRODUCTS_IN_CART ) ;
                    return false ;
                }
            } else if ( $this->allow_multiple_payment_products() ) {
                if ( $this->cart_contains_payment() ) {
                    // Multiple Payment Plan OR Depsosit product.
                    if ( $this->should_allow_same_plans() && ! in_array( absint( $requested[ 'chosen_payment_plan' ] ), $this->get_plan_id_from_cart() ) ) {
                        //While different payment plan add to cart.
                        $this->add_cart_notice( self::CANNOT_ADD_DIFFERENT_PLANS_IN_CART ) ;
                        return false ;
                    }
                } else {
                    // While normal products already in cart.
                    $this->add_cart_notice( self::CANNOT_ADD_PAYMENTS_WHILE_NORMAL_PRODUCTS_IN_CART ) ;
                    return false ;
                }
            } else {
                // Any Payemnt plan OR Deposit product.
                if ( $this->cart_contains_payment() && $this->should_allow_same_plans() && ! in_array( absint( $requested[ 'chosen_payment_plan' ] ), $this->get_plan_id_from_cart() ) ) {
                    //While different payment plan add to cart.
                    $this->add_cart_notice( self::CANNOT_ADD_DIFFERENT_PLANS_IN_CART ) ;
                    return false ;
                }
            }
        } else {
            if ( $this->allow_only_single_payment_product() || $this->allow_multiple_payment_products() ) {
                if ( $this->cart_contains_payment() ) {
                    $this->add_cart_notice( self::CANNOT_ADD_NORMAL_PRODUCTS_WHILE_PAYMENTS_IN_CART ) ;
                    return false ;
                }
            }
        }

        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'validate_cart_session' ), 999 ) ;
        return $bool ;
    }

    public function validate_cart_session( $cart ) {
        if ( empty( WC()->cart->cart_contents ) ) {
            return ;
        }

        // Single Payment Plan OR Deposit Product
        if ( $this->allow_only_single_payment_product() ) {
            $payments = array_keys( $this->get_payments_from_cart() ) ;

            if ( ! empty( $payments ) && sizeof( WC()->cart->cart_contents ) > 1 ) {
                if ( sizeof( WC()->cart->cart_contents ) > sizeof( $payments ) || sizeof( WC()->cart->cart_contents ) === sizeof( $payments ) ) {
                    $this->add_cart_notice( self::INVALID_PAYMENTS_REMOVED_FROM_CART ) ;
                    array_map( array( WC()->cart, 'remove_cart_item' ), $payments ) ;
                }
            }
        } else if ( $this->allow_multiple_payment_products() ) {
            $payments = array_keys( $this->get_payments_from_cart() ) ;
            // Multiple Payment Plan OR Deposit product.
            if ( ! empty( $payments ) ) {
                if ( $this->should_allow_same_plans() && count( $this->get_distinct_plans_in_cart() ) > 0 ) {
                    // Remove different payment plan product in cart.
                    $this->add_cart_notice( self::INVALID_PLANS_REMOVED_FROM_CART ) ;
                    array_map( array( WC()->cart, 'remove_cart_item' ), $this->get_distinct_plans_in_cart() ) ;
                }

                if ( sizeof( WC()->cart->cart_contents ) > sizeof( $payments ) ) {
                    // Remove invalid payment plan or deposit products.
                    $this->add_cart_notice( self::INVALID_PAYMENTS_REMOVED_FROM_CART ) ;
                    array_map( array( WC()->cart, 'remove_cart_item' ), $payments ) ;
                }
            }
        } else {
            $payments = array_keys( $this->get_payments_from_cart() ) ;
            // Any Payment plan OR Deposit products.
            if ( ! empty( $payments ) ) {
                if ( $this->should_allow_same_plans() && count( $this->get_distinct_plans_in_cart() ) > 0 ) {
                    // Remove different payment plan products in cart.
                    $this->add_cart_notice( self::INVALID_PLANS_REMOVED_FROM_CART ) ;
                    array_map( array( WC()->cart, 'remove_cart_item' ), $this->get_distinct_plans_in_cart() ) ;
                }
            }
        }
    }

    public function add_payment_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
        $add_to_cart_product = $variation_id ? $variation_id : $product_id ;

        if (
                self::$add_to_cart_transient &&
                _sumo_pp()->product->is_payment_product( self::$add_to_cart_transient ) &&
                $add_to_cart_product === _sumo_pp()->product->get_prop( 'product_id', array( 'props' => self::$add_to_cart_transient ) )
        ) {
            $requested = $this->get_request() ;

            if ( is_null( $requested[ 'payment_type' ] ) ) {
                return $cart_item_data ;
            }

            $cart_item_data[ 'sumopaymentplans' ] = apply_filters( 'sumopaymentplans_add_cart_item_data', SUMO_PP_Data_Manager::get_payment_data( array(
                        'product_props'          => self::$add_to_cart_transient,
                        'plan_props'             => $requested[ 'chosen_payment_plan' ],
                        'deposited_amount'       => $requested[ 'deposited_amount' ],
                        'calc_deposit'           => $requested[ 'calc_deposit' ],
                        'qty'                    => absint( $quantity ),
                        'activate_payment'       => get_option( SUMO_PP_PLUGIN_PREFIX . 'activate_payments', 'auto' ),
                        'charge_shipping_during' => $this->charge_shipping_during(),
                        'item_meta'              => $cart_item_data,
                    ) ), $cart_item_data, $product_id, $variation_id, $quantity ) ;
        }

        return $cart_item_data ;
    }

    public function refresh_cart() {

        foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
            if ( ! empty( $cart_item[ 'sumopaymentplans' ] ) ) {
                if ( ! isset( WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ][ 'down_payment' ] ) ) {
                    WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ] = array() ;
                    continue ;
                }

                remove_filter( 'woocommerce_product_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;
                remove_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;

                $payment_data = SUMO_PP_Data_Manager::get_payment_data( array(
                            'product_props'          => $cart_item[ 'variation_id' ] > 0 ? $cart_item[ 'variation_id' ] : $cart_item[ 'product_id' ],
                            'plan_props'             => WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ][ 'payment_plan_props' ][ 'plan_id' ],
                            'deposited_amount'       => WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ][ 'down_payment' ],
                            'base_price'             => isset( WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ][ 'base_price' ] ) ? WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ][ 'base_price' ] : null,
                            'qty'                    => $cart_item[ 'quantity' ],
                            'activate_payment'       => get_option( SUMO_PP_PLUGIN_PREFIX . 'activate_payments', 'auto' ),
                            'charge_shipping_during' => $this->charge_shipping_during(),
                            'discount_amount'        => ( isset( $cart_item[ 'line_subtotal' ], $cart_item[ 'line_total' ] ) && $cart_item[ 'line_subtotal' ] !== $cart_item[ 'line_total' ] ) ? ( $cart_item[ 'line_subtotal' ] - $cart_item[ 'line_total' ] ) : null,
                            'item_meta'              => $cart_item,
                        ) ) ;

                add_filter( 'woocommerce_product_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;
                add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_initial_amount' ), 999, 2 ) ;

                if ( empty( $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) ) {
                    WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ] = array() ;
                    continue ;
                }

                switch ( $payment_data[ 'payment_product_props' ][ 'payment_type' ] ) {
                    case 'payment-plans':
                        if (
                                empty( $payment_data[ 'payment_plan_props' ][ 'payment_schedules' ] ) ||
                                empty( $payment_data[ 'payment_product_props' ][ 'selected_plans' ] )
                        ) {
                            WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ] = array() ;
                            continue 2 ;
                        }

                        $plans_col_1 = ! empty( $payment_data[ 'payment_product_props' ][ 'selected_plans' ][ 'col_1' ] ) ? $payment_data[ 'payment_product_props' ][ 'selected_plans' ][ 'col_1' ] : array() ;
                        $plans_col_2 = ! empty( $payment_data[ 'payment_product_props' ][ 'selected_plans' ][ 'col_2' ] ) ? $payment_data[ 'payment_product_props' ][ 'selected_plans' ][ 'col_2' ] : array() ;

                        if ( ! in_array( $payment_data[ 'payment_plan_props' ][ 'plan_id' ], $plans_col_1 ) && ! in_array( $payment_data[ 'payment_plan_props' ][ 'plan_id' ], $plans_col_2 ) ) {
                            WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ] = array() ;
                            continue 2 ;
                        }
                        break ;
                    case 'pay-in-deposit':
                        break ;
                }
                WC()->cart->cart_contents[ $item_key ][ 'sumopaymentplans' ] = $payment_data ;
            }
        }
    }

    public function get_initial_amount( $price, $product ) {
        if ( ! is_front_page() && ( is_shop() || is_product() ) ) {
            return $price ;
        }

        if ( $payment = $this->is_payment_item( $product ) ) {
            if ( _sumo_pp()->product->is_payment_product( $payment[ 'payment_product_props' ] ) ) {
                $price = $payment[ 'down_payment' ] ;
            }
        }
        return $price ;
    }

    public function prevent_shipping_charges_in_initial_order( $total, $cart = '' ) {
        if ( 'final-payment' === $this->charge_shipping_during() && $this->cart_contains_payment() ) {
            $shipping_total = WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax() ;
            $total          = max( $total, $shipping_total ) - min( $total, $shipping_total ) ;
        }
        return $total ;
    }

}
