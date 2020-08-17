<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

/**
 * Handle payment plans enqueues.
 * 
 * @class SUMO_PP_Enqueues
 * @category Class
 */
class SUMO_PP_Enqueues {

    /**
     * Init SUMO_PP_Enqueues.
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_script' ) ;
        add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_style' ) ;
        add_action( 'wp_enqueue_scripts', __CLASS__ . '::frontend_script' ) ;
        add_action( 'wp_enqueue_scripts', __CLASS__ . '::frontend_style' ) ;
        add_filter( 'woocommerce_screen_ids', __CLASS__ . '::load_woocommerce_enqueues', 1 ) ;
    }

    /**
     * Register and enqueue a script for use.
     *
     * @uses   wp_enqueue_script()
     * @access public
     * @param  string   $handle
     * @param  string   $path
     * @param  array   $localize_data
     * @param  string[] $deps
     * @param  string   $version
     * @param  boolean  $in_footer
     */
    public static function enqueue_script( $handle, $path = '', $localize_data = array(), $deps = array( 'jquery' ), $version = SUMO_PP_PLUGIN_VERSION, $in_footer = false ) {
        wp_register_script( $handle, $path, $deps, $version, $in_footer ) ;

        $name = str_replace( '-', '_', $handle ) ;
        wp_localize_script( $handle, $name, $localize_data ) ;
        wp_enqueue_script( $handle ) ;
    }

    /**
     * Register and enqueue a styles for use.
     *
     * @uses   wp_enqueue_style()
     * @access public
     * @param  string   $handle
     * @param  string   $path
     * @param  string[] $deps
     * @param  string   $version
     * @param  string   $media
     * @param  boolean  $has_rtl
     */
    public static function enqueue_style( $handle, $path = '', $deps = array(), $version = SUMO_PP_PLUGIN_VERSION, $media = 'all', $has_rtl = false ) {
        wp_register_style( $handle, $path, $deps, $version, $media, $has_rtl ) ;
        wp_enqueue_style( $handle ) ;
    }

    /**
     * Return asset URL.
     *
     * @param string $path
     * @return string
     */
    public static function get_asset_url( $path ) {
        return SUMO_PP_PLUGIN_URL . "/assets/{$path}" ;
    }

    /**
     * Enqueue jQuery UI events
     */
    public static function enqueue_jQuery_ui() {
        self::enqueue_script( 'sumo-pp-jquery-ui', self::get_asset_url( 'js/jquery-ui/jquery-ui.js' ) ) ;
        self::enqueue_style( 'sumo-pp-jquery-ui', self::get_asset_url( 'css/jquery-ui.css' ) ) ;
    }

    /**
     * Enqueue Footable.
     */
    public static function enqueue_footable_scripts() {

        self::enqueue_script( 'sumo-pp-footable', self::get_asset_url( 'js/footable/footable.js' ) ) ;
        self::enqueue_script( 'sumo-pp-footable-sort', self::get_asset_url( 'js/footable/footable.sort.js' ) ) ;
        self::enqueue_script( 'sumo-pp-footable-paginate', self::get_asset_url( 'js/footable/footable.paginate.js' ) ) ;
        self::enqueue_script( 'sumo-pp-footable-filter', self::get_asset_url( 'js/footable/footable.filter.js' ) ) ;
        self::enqueue_script( 'sumo-pp-footable-action', self::get_asset_url( 'js/footable/sumo-pp-footable.js' ) ) ;

        self::enqueue_style( 'sumo-pp-footable-core', self::get_asset_url( 'css/footable/footable.core.css' ) ) ;
        self::enqueue_style( 'sumo-pp-footable-standalone', self::get_asset_url( 'css/footable/footable.standalone.css' ) ) ;
        self::enqueue_style( 'sumo-pp-footable-bootstrap', self::get_asset_url( 'css/footable/bootstrap.css' ) ) ;
        self::enqueue_style( 'sumo-pp-footable-chosen', self::get_asset_url( 'css/footable/chosen.css' ) ) ;
    }

    /**
     * Enqueue WC Multiselect field
     */
    public static function enqueue_wc_multiselect() {
        wp_enqueue_script( 'wc-enhanced-select' ) ;
    }

    /**
     * Enqueue date, time, or datetime picker
     */
    public static function enqueue_datetimepicker() {
        self::enqueue_script( 'sumo-pp-datetimepicker', self::get_asset_url( 'js/datetimepicker/jquery-ui-timepicker-addon.js' ) ) ;
        self::enqueue_style( 'sumo-pp-datetimepicker', self::get_asset_url( 'js/datetimepicker/jquery-ui-timepicker-addon.css' ) ) ;
    }

    /**
     * Enqueue Jquery tipTip
     */
    public static function enqueue_jquery_tiptip() {
        self::enqueue_script( 'sumo-pp-jquery-tiptip-lib', self::get_asset_url( 'js/jquery-tiptip/jquery.tipTip.js' ) ) ;
        self::enqueue_script( 'sumo-pp-jquery-tiptip', self::get_asset_url( 'js/jquery-tiptip/sumo-pp-my-tipTip.js' ) ) ;
        self::enqueue_style( 'sumo-pp-jquery-tiptip', self::get_asset_url( 'css/sumo-pp-jquery.tipTip.css' ) ) ;
    }

    /**
     * Perform script localization in backend.
     */
    public static function admin_script() {

        //Welcome page
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'sumopaymentplans-welcome-page' ) {
            self::enqueue_script( 'sumo-pp-admin-welcome-page', self::get_asset_url( 'js/admin/sumo-pp-admin-welcome-page.js' ) ) ;
        }

        //Admin Page.
        switch ( get_post_type() ? get_post_type() : (isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : false) ) {
            case 'sumo_payment_plans':
            case 'sumo_pp_payments':
            case SUMO_PP_Payments_Exporter::$exporter_page:
                self::enqueue_script( 'sumo-pp-admin-dashboard', self::get_asset_url( 'js/admin/sumo-pp-admin-dashboard.js' ), array(
                    'wp_ajax_url'                => admin_url( 'admin-ajax.php' ),
                    'duration_options'           => _sumo_pp_get_duration_options(),
                    'get_post_type'              => get_post_type(),
                    'price_dp'                   => wc_get_price_decimals(),
                    'add_note_nonce'             => wp_create_nonce( 'sumo-pp-add-payment-note' ),
                    'delete_note_nonce'          => wp_create_nonce( 'sumo-pp-delete-payment-note' ),
                    'exporter_nonce'             => wp_create_nonce( 'sumo-pp-payments-exporter' ),
                    'warning_dates_not_in_order' => __( 'You cannot save when the installment dates are not in order. Please make the changes and try again.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                ) ) ;

                self::enqueue_jQuery_ui() ;
                self::enqueue_footable_scripts() ;
                self::enqueue_jquery_tiptip() ;
                self::enqueue_datetimepicker() ;
                // Disable WP Auto Save on Edit Page.
                wp_dequeue_script( 'autosave' ) ;
                break ;
            case 'sumo_pp_masterlog':
                self::enqueue_jquery_tiptip() ;
                break ;
            case 'product':
                self::enqueue_script( 'sumo-pp-admin-product', self::get_asset_url( 'js/admin/sumo-pp-admin-product.js' ), array(
                    'decimal_sep'       => get_option( 'woocommerce_price_decimal_sep', '.' ),
                    'plan_search_nonce' => wp_create_nonce( 'sumo-pp-get-payment-plan-search-field' ),
                ) ) ;
                self::enqueue_wc_multiselect() ;
                self::enqueue_footable_scripts() ;
                break ;
            case 'sumo_pp_settings':
                switch ( isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '' ) {
                    case 'orderpp':
                        self::enqueue_script( 'sumo-pp-admin-orderpp-settings', self::get_asset_url( 'js/admin/sumo-pp-admin-order-paymentplan-settings.js' ) ) ;
                        break ;
                    case 'bulk_action':
                        self::enqueue_script( 'sumo-pp-admin-bulk-action-settings', self::get_asset_url( 'js/admin/sumo-pp-admin-bulk-action-settings.js' ), array(
                            'wp_ajax_url'       => admin_url( 'admin-ajax.php' ),
                            'products_nonce'    => wp_create_nonce( 'produts-bulk-update' ),
                            'plan_search_nonce' => wp_create_nonce( 'sumo-pp-get-payment-plan-search-field' ),
                        ) ) ;
                        break ;
                    default :
                        self::enqueue_script( 'sumo-pp-admin-general-settings', self::get_asset_url( 'js/admin/sumo-pp-admin-general-settings.js' ), array(
                            'plan_search_nonce' => wp_create_nonce( 'sumo-pp-get-payment-plan-search-field' ),
                        ) ) ;
                        break ;
                }

                self::enqueue_jQuery_ui() ;
                self::enqueue_wc_multiselect() ;
                break ;
            case 'wc-settings':
                if ( isset( $_GET[ 'tab' ] ) && isset( $_GET[ 'section' ] ) ) {
                    switch ( $_GET[ 'section' ] ) {
                        case 'sumo_pp_stripe':
                            self::enqueue_script( 'sumo-pp-stripe-settings', self::get_asset_url( 'js/admin/sumo-pp-admin-wc-settings-stripe.js' ) ) ;
                            break ;
                    }
                }
                break ;
        }
    }

    /**
     * Load style in backend.
     */
    public static function admin_style() {
        //Welcome page
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'sumopaymentplans-welcome-page' ) {
            self::enqueue_style( 'sumo-pp-admin-welcome-page', self::get_asset_url( 'css/sumo-pp-admin-welcome-page.css' ) ) ;
        }

        if ( in_array( get_post_type(), array( 'sumo_payment_plans', 'sumo_pp_payments' ) ) ) {
            self::enqueue_style( 'sumo-pp-admin-dashboard', self::get_asset_url( 'css/sumo-pp-admin-dashboard.css' ) ) ;
        }
    }

    /**
     * Perform script localization in frontend.
     * @global object $post
     */
    public static function frontend_script() {
        global $post ;

        self::enqueue_script( 'sumo-pp-single-product-page', self::get_asset_url( 'js/frontend/sumo-pp-single-product-page.js' ), array(
            'wp_ajax_url'                         => admin_url( 'admin-ajax.php' ),
            'product'                             => isset( $post->ID ) ? $post->ID : false,
            'get_wc_booking_deposit_fields_nonce' => wp_create_nonce( 'sumo-pp-get-payment-type-fields' ),
            'hide_product_price'                  => get_option( SUMO_PP_PLUGIN_PREFIX . 'hide_product_price_for_payment_plans', 'no' ),
            'variation_deposit_form_template'     => SUMO_PP_Variation_Deposit_Form::get_template(),
        ) ) ;

        self::enqueue_script( 'sumo-pp-checkout-page', self::get_asset_url( 'js/frontend/sumo-pp-checkout-page.js' ), array(
            'wp_ajax_url'                                 => admin_url( 'admin-ajax.php' ),
            'is_user_logged_in'                           => is_user_logged_in(),
            'orderpp_nonce'                               => wp_create_nonce( 'sumo-pp-checkout-orderpp' ),
            'can_user_deposit_payment'                    => _sumo_pp()->orderpp->can_user_deposit_payment(),
            'maybe_prevent_from_hiding_guest_signup_form' => 'yes' === get_option( 'woocommerce_enable_guest_checkout' ) && 'yes' !== get_option( 'woocommerce_enable_signup_and_login_from_checkout' ),
        ) ) ;

        self::enqueue_script( 'sumo-pp-my-account-page', self::get_asset_url( 'js/frontend/sumo-pp-my-account-page.js' ), array(
            'wp_ajax_url'           => admin_url( 'admin-ajax.php' ),
            'show_more_notes_label' => __( 'Show More', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
            'show_less_notes_label' => __( 'Show Less', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
            'myaccount_nonce'       => wp_create_nonce( 'sumo-pp-myaccount' ),
        ) ) ;

        if ( is_account_page() || _sumo_pp_is_my_payments_page() ) {
            self::enqueue_footable_scripts() ;
            self::enqueue_jquery_tiptip() ;
        }
    }

    /**
     * Load style in frontend.
     */
    public static function frontend_style() {
        self::enqueue_style( 'sumo-pp-single-product-page', self::get_asset_url( 'css/sumo-pp-single-product-page.css' ) ) ;
    }

    /**
     * Load WooCommerce enqueues.
     * @global object $typenow
     * @param array $screen_ids
     * @return array
     */
    public static function load_woocommerce_enqueues( $screen_ids ) {
        global $typenow ;

        $new_screen = get_current_screen() ;

        if ( in_array( $typenow, array( 'sumo_pp_payments', 'sumo_payment_plans' ) ) || (isset( $_GET[ 'page' ] ) && in_array( $_GET[ 'page' ], array( 'sumo_pp_settings', SUMO_PP_Payments_Exporter::$exporter_page ) ) ) ) {
            $screen_ids[] = $new_screen->id ;
        }
        return $screen_ids ;
    }

}

SUMO_PP_Enqueues::init() ;
