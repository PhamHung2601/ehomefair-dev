<?php

if( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Check the currently installed WC version
 * @param string $comparison_opr The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
  This parameter is case-sensitive, values should be lowercase
 * @param string $version
 * @return boolean
 */
function _sumo_pp_is_wc_version( $comparison_opr , $version ) {
    if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION , $version , $comparison_opr ) ) {
        return true ;
    }
    return false ;
}

function _sumo_pp_is_my_payments_page() {
    return wc_post_content_has_shortcode( 'sumo_pp_my_payments' ) ;
}

function _sumo_pp_cart_has_payment_items() {
    return _sumo_pp()->cart->cart_contains_payment() ;
}

function _sumo_pp_is_payment_order( $order ) {
    $order = _sumo_pp_get_order( $order ) ;
    return $order && $order->is_payment_order() ;
}

function _sumo_pp_is_initial_payment_order( $order ) {
    $order = _sumo_pp_get_order( $order ) ;
    return $order && $order->is_payment_order() && $order->is_parent() ;
}

function _sumo_pp_is_balance_payment_order( $order ) {
    $order = _sumo_pp_get_order( $order ) ;
    return $order && $order->is_payment_order() && $order->is_child() ;
}

function _sumo_pp_is_final_payment_order( $order ) {
    $order = _sumo_pp_get_order( $order ) ;

    if( _sumo_pp_is_balance_payment_order( $order ) ) {
        $payment = _sumo_pp_get_payment( get_post_meta( $order->order_id , '_payment_id' , true ) ) ;

        if( $payment ) {
            if( $payment->has_status( 'completed' ) ) {
                return true ;
            }
            return 1 === $payment->get_remaining_installments() ;
        }
    }
    return false ;
}

function _sumo_pp_cancel_payment_immediately() {
    return 'auto' === get_option( SUMO_PP_PLUGIN_PREFIX . 'cancel_payments_after_balance_payment_due_date' , 'after_admin_approval' ) ? true : false ;
}

function _sumo_pp_user_can_purchase_payment( $user = null , $args = array() ) {
    include_once( ABSPATH . 'wp-includes/pluggable.php' ) ;

    $args = wp_parse_args( $args , array(
        'limit_by'            => get_option( SUMO_PP_PLUGIN_PREFIX . 'show_deposit_r_payment_plans_for' , 'all_users' ) ,
        'filtered_users'      => ( array ) get_option( SUMO_PP_PLUGIN_PREFIX . 'get_limited_users_of_payment_product' ) ,
        'filtered_user_roles' => ( array ) get_option( SUMO_PP_PLUGIN_PREFIX . 'get_limited_userroles_of_payment_product' ) ,
            ) ) ;

    if( is_numeric( $user ) && $user ) {
        $user_id = $user ;
    } else if( isset( $user->ID ) ) {
        $user_id = $user->ID ;
    } else {
        $user_id = get_current_user_id() ;
    }
    $user = get_user_by( 'id' , $user_id ) ;

    switch( $args[ 'limit_by' ] ) {
        case 'all_users':
            return true ;
        case 'include_users':
            if( ! $user ) {
                return false ;
            }

            $filtered_user_mails = array() ;
            foreach( $args[ 'filtered_users' ] as $_id ) {
                if( ! $_user = get_user_by( 'id' , $_id ) ) {
                    continue ;
                }

                $filtered_user_mails[] = $_user->data->user_email ;
            }
            if( in_array( $user->data->user_email , $filtered_user_mails ) ) {
                return true ;
            }
            break ;
        case 'exclude_users':
            if( ! $user ) {
                return false ;
            }

            $filtered_user_mails = array() ;
            foreach( $args[ 'filtered_users' ] as $_id ) {
                if( ! $_user = get_user_by( 'id' , $_id ) ) {
                    continue ;
                }

                $filtered_user_mails[] = $_user->data->user_email ;
            }
            if( ! in_array( $user->data->user_email , $filtered_user_mails ) ) {
                return true ;
            }
            break ;
        case 'include_user_role':
            if( $user ) {
                if( isset( $user->roles[ 0 ] ) && in_array( $user->roles[ 0 ] , $args[ 'filtered_user_roles' ] ) ) {
                    return true ;
                }
            } elseif( in_array( 'guest' , $args[ 'filtered_user_roles' ] ) ) {
                return true ;
            }
            break ;
        case 'exclude_user_role':
            if( $user ) {
                if( isset( $user->roles[ 0 ] ) && ! in_array( $user->roles[ 0 ] , $args[ 'filtered_user_roles' ] ) ) {
                    return true ;
                }
            } elseif( ! in_array( 'guest' , $args[ 'filtered_user_roles' ] ) ) {
                return true ;
            }
            break ;
    }
    return false ;
}

function _sumo_pp_is_valid_order_to_pay( $order ) {
    $maybe_payment_order = _sumo_pp_get_order( $order ) ;

    if ( ! $maybe_payment_order ) {
        return false ;
    }

    if ( $maybe_payment_order->has_status( array( 'completed', 'processing', 'refunded' ) ) ) {
        return false ;
    }

    if ( $maybe_payment_order->is_child() ) {
        $parent_order = wc_get_order( $maybe_payment_order->get_parent_id() ) ;

        if ( $parent_order && $parent_order->has_status( 'refunded' ) ) {
            return false ;
        }
    }

    return true ;
}
