<?php

/**
 * Stripe API Request.
 * 
 * @class SUMO_PP_Stripe_API_Request
 * @category Class
 */
class SUMO_PP_Stripe_API_Request {

    /** @protected bool Is Sandbox mode enabled. */
    protected static $sandbox = false ;

    /** @protected string Stripe Secret key */
    protected static $secret_key ;

    /** protected string Stripe Error messge. */
    protected static $error_message = '' ;

    /** protected string Stripe Declined Code. */
    protected static $declined_code = '' ;

    /** protected string Stripe logger. */
    protected static $logger = '' ;

    /**
     * Init the SUMO_PP_Stripe_API_Request
     */
    public static function init( SUMO_PP_Stripe_Gateway $stripe_gateway ) {
        self::$sandbox    = ( bool ) $stripe_gateway->testmode ;
        self::$secret_key = self::$sandbox ? $stripe_gateway->testsecretkey : $stripe_gateway->livesecretkey ;

        //make sure the classes are load once
        if( ! class_exists( '\Stripe\Stripe' ) ) {
            require_once('inc/Stripe.php') ;
        }

        if( ! class_exists( '\Stripe\Stripe' ) ) {
            throw new Exception( __( 'Cannot connect to Stripe.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        }

        //Set Stripe API key
        if( is_wp_error( self::request( 'set_apiKey' ) ) ) {
            throw new Exception( self::get_last_error_message() ) ;
        }
    }

    /**
     * Validate Zero decimal currencies. Since it must be effective to charge the Amount in or from the Stripe.
     * 
     * @param mixed $total
     * @param string $currency
     * @return int
     */
    protected static function prepare_amount( $total , $currency = '' ) {
        if( ! $currency ) {
            $currency = get_woocommerce_currency() ;
        }

        switch( strtoupper( $currency ) ) {
            // Zero decimal currencies
            case 'BIF' :
            case 'CLP' :
            case 'DJF' :
            case 'GNF' :
            case 'JPY' :
            case 'KMF' :
            case 'KRW' :
            case 'MGA' :
            case 'PYG' :
            case 'RWF' :
            case 'VND' :
            case 'VUV' :
            case 'XAF' :
            case 'XOF' :
            case 'XPF' :
                $total = absint( $total ) ;
                break ;
            default :
                // In cents. 100 cents make 1 USD
                $total = round( $total , 2 ) * apply_filters( 'sumopaymentplans_multiplication_factor' , 100 , $total , $currency ) ;
                break ;
        }
        return $total ;
    }

    /**
     * Prepare customer details
     */
    public static function prepare_customer_details( $params , $exclude_params = array() ) {
        $default_params = array(
            'address'     => array(
                'line1'       => '' ,
                'line2'       => '' ,
                'city'        => '' ,
                'state'       => '' ,
                'postal_code' => '' ,
                'country'     => '' ,
            ) ,
            'name'        => '' ,
            'phone'       => '' ,
            'email'       => '' ,
            'description' => '' ,
                ) ;

        $params = wp_parse_args( $params , $default_params ) ;

        if( ! empty( $params[ 'fname' ] ) ) {
            $params[ 'name' ] = $params[ 'fname' ] ;

            if( ! empty( $params[ 'lname' ] ) ) {
                $params[ 'name' ] .= ' ' ;
                $params[ 'name' ] .= $params[ 'lname' ] ;
            }
        }

        if( empty( $params[ 'description' ] ) ) {
            $params[ 'description' ] = sprintf( __( '%1$s - Name: %2$s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , wp_specialchars_decode( get_bloginfo( 'name' ) , ENT_QUOTES ) , $params[ 'name' ] ) ;
        }

        $allowed_params = array_keys( $default_params ) ;
        foreach( $params as $param => $val ) {
            if( ! in_array( $param , $allowed_params ) ) {
                unset( $params[ $param ] ) ;
            }
        }

        if( ! empty( $exclude_params ) ) {
            foreach( $exclude_params as $exclude_param ) {
                if( in_array( $exclude_param , $allowed_params ) ) {
                    unset( $params[ $exclude_param ] ) ;
                }
            }
        }
        return $params ;
    }

    /**
     * Stripe Logger
     */
    protected static function log( $e ) {
        self::$logger = $e ;
    }

    /**
     * Check if saved customer is deleted in Stripe server
     */
    public static function is_customer_deleted( $customer ) {
        if( isset( $customer->id , $customer->deleted ) && $customer->deleted ) {
            return true ;
        }
        return false ;
    }

    /**
     * Request Stripe
     */
    public static function request( $api , $args = array() ) {
        // make sure to clear the cache before request
        self::clear_cache() ;

        try {
            switch( $api ) {
                case 'set_apiKey':
                    \Stripe\Stripe::setApiKey( self::$secret_key ) ;
                    return true ;
                case 'create_customer':
                    return \Stripe\Customer::create( $args ) ;
                case 'retrieve_customer':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    return \Stripe\Customer::retrieve( $request[ 'id' ] ) ;
                case 'retrieve_all_pm':
                    $request = wp_parse_args( $args , array(
                        'customer' => '' ,
                        'type'     => 'card'
                            ) ) ;

                    return \Stripe\PaymentMethod::all( $request ) ;
                case 'update_customer':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    $id      = $request[ 'id' ] ;
                    unset( $request[ 'id' ] ) ;
                    return \Stripe\Customer::update( $id , $request ) ;
                case 'retrieve_pm':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    return \Stripe\PaymentMethod::retrieve( $request[ 'id' ] ) ;
                case 'create_si':
                    $request = wp_parse_args( $args , array(
                        'payment_method_types' => array( 'card' ) ,
                            ) ) ;

                    return \Stripe\SetupIntent::create( $request ) ;
                case 'retrieve_si':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    return \Stripe\SetupIntent::retrieve( $request[ 'id' ] ) ;
                case 'update_si':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    $id                    = $request[ 'id' ] ;
                    unset( $request[ 'id' ] ) ;
                    return \Stripe\SetupIntent::update( $id , $request ) ;
                case 'create_pi':
                    $request               = wp_parse_args( $args , array(
                        'amount'               => 0 ,
                        'currency'             => '' ,
                        'capture_method'       => 'automatic' ,
                        'confirmation_method'  => 'automatic' ,
                        'payment_method_types' => array( 'card' ) ,
                            ) ) ;
                    $request[ 'amount' ]   = self::prepare_amount( $request[ 'amount' ] , $request[ 'currency' ] ) ;
                    $request[ 'currency' ] = strtolower( $request[ 'currency' ] ) ;

                    if( ! empty( $request[ 'shipping' ] ) ) {
                        $request[ 'shipping' ] = self::prepare_customer_details( $request[ 'shipping' ] , array( 'email' , 'description' ) ) ;
                    }

                    return \Stripe\PaymentIntent::create( $request ) ;
                case 'retrieve_pi':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    return \Stripe\PaymentIntent::retrieve( $request[ 'id' ] ) ;
                case 'update_pi':
                    $request = wp_parse_args( $args , array(
                        'id' => ''
                            ) ) ;

                    if( isset( $request[ 'amount' ] ) ) {
                        $request[ 'amount' ] = self::prepare_amount( $request[ 'amount' ] , $request[ 'currency' ] ) ;
                    }

                    if( ! empty( $request[ 'currency' ] ) ) {
                        $request[ 'currency' ] = strtolower( $request[ 'currency' ] ) ;
                    }

                    if( ! empty( $request[ 'shipping' ] ) ) {
                        $request[ 'shipping' ] = self::prepare_customer_details( $request[ 'shipping' ] , array( 'email' , 'description' ) ) ;
                    }

                    $id                    = $request[ 'id' ] ;
                    unset( $request[ 'id' ] ) ;
                    return \Stripe\PaymentIntent::update( $id , $request ) ;
                case 'charge_customer':
                    $request               = wp_parse_args( $args , array(
                        'amount'   => 0 ,
                        'currency' => '' ,
                            ) ) ;
                    $request[ 'amount' ]   = self::prepare_amount( $request[ 'amount' ] , $request[ 'currency' ] ) ;
                    $request[ 'currency' ] = strtolower( $request[ 'currency' ] ) ;

                    if( ! empty( $request[ 'shipping' ] ) ) {
                        $request[ 'shipping' ] = self::prepare_customer_details( $request[ 'shipping' ] , array( 'email' , 'description' ) ) ;
                    }

                    return \Stripe\Charge::create( $request ) ;
                case 'create_refund':
                    $request = wp_parse_args( $args , array(
                        'charge'   => '' ,
                        'amount'   => 0 ,
                        'currency' => '' ,
                            ) ) ;

                    $request[ 'amount' ] = self::prepare_amount( $request[ 'amount' ] , $request[ 'currency' ] ) ;

                    if( empty( $request[ 'reason' ] ) ) {
                        unset( $request[ 'reason' ] ) ;
                    }
                    unset( $request[ 'currency' ] ) ;

                    return \Stripe\Refund::create( $request ) ;
            }
            // Use Stripe's library to make requests..
        } catch( \Stripe\Error\RateLimit $e ) {// Too many requests made to the API too quickly
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        } catch( \Stripe\Error\InvalidRequest $e ) {// Invalid parameters were supplied to Stripe's API
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        } catch( \Stripe\Error\Authentication $e ) {// Authentication with Stripe's API failed or Too many requests made to the API too quickly
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        } catch( \Stripe\Error\ApiConnection $e ) {// Network communication with Stripe failed
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        } catch( \Stripe\Error\Base $e ) {// Display a very generic error to the user
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        } catch( Exception $e ) {// Something else happened, completely unrelated to Stripe
            self::log( $e->getJsonBody() ) ;
            return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
        }
        // If we reached this point then there were errors
        return new WP_Error( 'sumo-pp-stripe-error' , self::get_last_error_message() ) ;
    }

    /**
     * Get last error message while requesting Stripe
     */
    public static function get_last_error_message() {
        if( is_callable( array( self::$logger , 'getMessage' ) ) ) {
            self::$error_message = self::$logger->getMessage() ;
        } else if( is_array( self::$logger ) && isset( self::$logger[ 'error' ][ 'message' ] ) ) {
            self::$error_message = self::$logger[ 'error' ][ 'message' ] ;
        }

        if( empty( self::$error_message ) ) {
            self::$error_message = __( 'Something went wrong !!' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        }
        return self::$error_message ;
    }

    /**
     * Get last declined code while requesting Stripe
     */
    public static function get_last_declined_code() {
        if( is_array( self::$logger ) && isset( self::$logger[ 'error' ][ 'decline_code' ] ) ) {
            self::$declined_code = self::$logger[ 'error' ][ 'decline_code' ] ;
        }

        return self::$declined_code ;
    }

    /**
     * Get last log while requesting Stripe
     */
    public static function get_last_log() {
        return self::$logger ;
    }

    /**
     * Clear the cache 
     */
    public static function clear_cache() {
        self::$logger        = self::$error_message = self::$declined_code = '' ;
    }

}
