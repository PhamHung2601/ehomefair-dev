<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Downloads handler
 * 
 * Handle digital payments downloads.
 * 
 * @class SUMO_PP_Downloads
 * @category Class
 */
class SUMO_PP_Downloads {

    /**
     * Init SUMO_PP_Downloads.
     */
    public static function init() {
        add_action( 'woocommerce_checkout_update_order_meta', __CLASS__ . '::maybe_set_download_permission', 20 ) ;
        add_filter( 'woocommerce_downloadable_file_permission', __CLASS__ . '::maybe_grant_downloadable_file_permission', 999, 3 ) ;
        add_filter( 'woocommerce_order_is_download_permitted', __CLASS__ . '::is_download_permitted', 999, 2 ) ;
    }

    /**
     * Maybe set the download permission in order which will be used later.
     * 
     * @param int $order_id
     */
    public static function maybe_set_download_permission( $order_id ) {
        $maybe_payment_order = _sumo_pp_get_order( $order_id ) ;

        if ( ! $maybe_payment_order->contains_payment_data() ) {
            return ;
        }

        $items = $maybe_payment_order->order->get_items() ;
        $value = 'initial-payment' ;

        foreach ( $items as $item ) {
            $product = wc_get_product( $item [ 'product_id' ] ) ;

            if ( $product && $product->is_downloadable() ) {
                $value = get_option( SUMO_PP_PLUGIN_PREFIX . 'grant_permission_to_download_after', 'initial-payment' ) ;
                break ;
            }
        }

        update_post_meta( $order_id, SUMO_PP_PLUGIN_PREFIX . 'grant_permission_to_download_after', $value ) ;
    }

    /**
     * Grant downloadable product access to the file either in the initial order or the final order.
     * 
     * @param WC_Customer_Download $download
     * @param WC_Product $product
     * @param WC_Order $order
     * @return WC_Customer_Download
     */
    public static function maybe_grant_downloadable_file_permission( $download, $product, $order ) {
        $maybe_payment_order = _sumo_pp_get_order( $order->get_id() ) ;

        if ( $maybe_payment_order->is_parent() ) {
            if ( ! $maybe_payment_order->contains_payment_data() ) {
                return $download ;
            }

            if ( 'final-payment' !== self::grant_permission_after( $maybe_payment_order ) ) {
                return $download ;
            }

            $items = $maybe_payment_order->order->get_items() ;
            foreach ( $items as $item ) {
                $product_id = absint( $item[ 'variation_id' ] > 0 ? $item[ 'variation_id' ] : $item[ 'product_id' ]  ) ;

                if ( $product_id !== $product->get_id() ) {
                    continue ;
                }

                if ( $maybe_payment_order->item_contains_payment_data( $item ) ) {
                    $download = new WC_Customer_Download() ;
                    break ;
                }
            }
        } else {
            if ( $maybe_payment_order->is_payment_order() ) {
                if ( 'final-payment' !== self::grant_permission_after( $maybe_payment_order ) || 0 !== absint( get_post_meta( $order->get_id(), SUMO_PP_PLUGIN_PREFIX . 'remaining_installments', true ) ) ) {
                    $download = new WC_Customer_Download() ;
                }
            }
        }

        return $download ;
    }

    /**
     * Check if permission granted after initial payment or final payment?
     * 
     * @param WC_Order|SUMO_PP_Order $order
     * @return string
     */
    public static function grant_permission_after( $order ) {
        $order_id = $order->is_parent() ? $order->get_id() : $order->get_parent_id() ;
        return get_post_meta( $order_id, SUMO_PP_PLUGIN_PREFIX . 'grant_permission_to_download_after', true ) ;
    }

    /**
     * Checks if product download is permitted.
     *
     * @return bool
     */
    public static function is_download_permitted( $bool, $order ) {
        if ( ! $bool ) {
            return $bool ;
        }

        $maybe_payment_order = _sumo_pp_get_order( $order->get_id() ) ;

        if ( $maybe_payment_order->is_parent() ) {
            if ( ! $maybe_payment_order->contains_payment_data() ) {
                return $bool ;
            }

            if ( 'final-payment' === self::grant_permission_after( $maybe_payment_order ) ) {
                $bool = false ;
            }
        } else {
            if ( $maybe_payment_order->is_payment_order() ) {
                if ( 'final-payment' !== self::grant_permission_after( $maybe_payment_order ) || 0 !== absint( get_post_meta( $order->get_id(), SUMO_PP_PLUGIN_PREFIX . 'remaining_installments', true ) ) ) {
                    $bool = false ;
                }
            }
        }

        return $bool ;
    }

}

SUMO_PP_Downloads::init() ;
