<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Variation Deposit Form Handler.
 * 
 * @class SUMO_PP_Variation_Deposit_Form
 * @category Class
 */
class SUMO_PP_Variation_Deposit_Form {

    /**
     * Get the variation deposit form template.
     * 
     * @var string
     */
    protected static $template ;

    /**
     * Init SUMO_PP_Variation_Deposit_Form.
     */
    public static function init() {
        if ( 'from-plugin' === self::get_template() ) {
            add_filter( 'sumopaymentplans_get_single_variation_data_to_display', __CLASS__ . '::render_variation_payment_type_fields', 9, 2 ) ;
            add_action( 'woocommerce_before_variations_form', __CLASS__ . '::render_variation_payment_type_fields', 10 ) ;
            add_action( 'woocommerce_before_single_variation', __CLASS__ . '::render_variation_payment_type_fields', 10 ) ;
            add_action( 'woocommerce_after_single_variation', __CLASS__ . '::render_variation_payment_type_fields', 10 ) ;
        } else {
            add_filter( 'woocommerce_available_variation', __CLASS__ . '::render_variation_deposit_form', 10, 3 ) ;
        }
    }

    /**
     * Get the variation deposit form template.
     * 
     * @return string
     */
    public static function get_template() {
        if ( ! is_null( self::$template ) ) {
            return self::$template ;
        }

        self::$template = get_option( SUMO_PP_PLUGIN_PREFIX . 'variation_form_template', 'from-woocommerce' ) ;
        return self::$template ;
    }

    /**
     * Deposit/Plan option for Variation product.
     * 
     * @param array $variation_data
     * @param array $variable
     * @param array $variation
     * @return array
     */
    public static function render_variation_deposit_form( $variation_data, $variable, $variation ) {
        if ( _sumo_pp()->product->is_payment_product( $variation ) ) {
            $variation_data[ 'sumo_pp_deposit_form' ] = _sumo_pp()->product->get_deposit_form() ;
        }

        return $variation_data ;
    }

    /**
     * Legacy.
     * 
     * Deposit/Plan option for Variation product.
     * 
     * @global WC_Product $product
     * @param array $data
     * @param mixed $variation
     * @return mixed
     */
    public static function render_variation_payment_type_fields( $data = array(), $variation = null ) {
        global $product ;

        if ( 'sumopaymentplans_get_single_variation_data_to_display' === current_filter() ) {
            if ( $variation && $variation->exists() && _sumo_pp()->product->is_payment_product( $variation ) ) {
                $data[ 'payment_type_fields' ] = _sumo_pp()->product->get_deposit_form() ;
            }
            return $data ;
        } else if ( doing_action( 'woocommerce_before_variations_form' ) ) {
            $children = $product->get_visible_children() ;

            if ( ! empty( $children ) ) {
                $variation_data = array() ;

                foreach ( $children as $child_id ) {
                    $product_variation = new WC_Product_Variation( $child_id ) ;
                    if ( $product_variation->exists() && $product_variation->variation_is_visible() ) {
                        $_variation_data = apply_filters( 'sumopaymentplans_get_single_variation_data_to_display', array(), $product_variation ) ;

                        if ( ! empty( $_variation_data ) ) {
                            $variation_data[ $child_id ] = $_variation_data ;
                        }
                    }
                }

                if ( ! empty( $variation_data ) ) {
                    $variations   = wp_json_encode( array_keys( $variation_data ) ) ;
                    $hidden_field = "<input type='hidden' id='" . SUMO_PP_PLUGIN_PREFIX . "single_variations'" ;
                    $hidden_field .= "data-variations='{$variations}'" ;
                    $hidden_field .= "/>" ;
                    $hidden_field .= "<input type='hidden' id='" . SUMO_PP_PLUGIN_PREFIX . "single_variation_data'" ;
                    foreach ( $variation_data as $variation_id => $data ) {
                        foreach ( $data as $key => $message ) {
                            $message      = htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' ) ;
                            $hidden_field .= "data-{$key}_{$variation_id}='{$message}'" ;
                        }
                    }

                    $hidden_field .= "/>" ;
                    echo $hidden_field ;
                }
            }
        } else if ( doing_action( 'woocommerce_before_single_variation' ) ) {
            echo '<span id="' . SUMO_PP_PLUGIN_PREFIX . 'before_single_variation"></span>' ;
        } else {
            echo '<span id="' . SUMO_PP_PLUGIN_PREFIX . 'after_single_variation"></span>' ;
        }
    }

}

SUMO_PP_Variation_Deposit_Form::init() ;
