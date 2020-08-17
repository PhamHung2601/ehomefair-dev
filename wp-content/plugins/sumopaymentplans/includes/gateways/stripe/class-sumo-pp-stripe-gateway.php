<?php

/**
 * Register new Payment Gateway id of Stripe.
 * 
 * @class SUMO_PP_Stripe_Gateway
 * @category Class
 */
class SUMO_PP_Stripe_Gateway extends WC_Payment_Gateway {

    const STRIPE_REQUIRES_AUTH            = 100 ;
    const PAYMENT_RETRY_WITH_DEFAULT_CARD = 200 ;

    /**
     * Check if we need to retry with the Default card
     * @var bool 
     */
    public $retry_failed_payment = false ;

    /**
     * SUMO_PP_Stripe_Gateway constructor.
     */
    public function __construct() {
        $this->id                                   = 'sumo_pp_stripe' ;
        $this->method_title                         = __( 'SUMO Payment Plans - Stripe', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->method_description                   = __( 'Take payments from your customers using Credit/Debit card', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
        $this->has_fields                           = true ;
        $this->init_form_fields() ;
        $this->init_settings() ;
        $this->enabled                              = $this->get_option( 'enabled' ) ;
        $this->title                                = $this->get_option( 'title' ) ;
        $this->description                          = $this->get_option( 'description' ) ;
        $this->cardiconfilter                       = $this->get_option( 'cardiconfilter', array() ) ;
        $this->saved_cards                          = 'yes' === $this->get_option( 'saved_cards', 'no' ) ;
        $this->testmode                             = 'yes' === $this->get_option( 'testmode' ) ;
        $this->testsecretkey                        = $this->get_option( 'testsecretkey' ) ;
        $this->testpublishablekey                   = $this->get_option( 'testpublishablekey' ) ;
        $this->livesecretkey                        = $this->get_option( 'livesecretkey' ) ;
        $this->livepublishablekey                   = $this->get_option( 'livepublishablekey' ) ;
        $this->checkoutmode                         = $this->get_option( 'checkoutmode' ) ;
        $this->pendingAuthEmailReminder             = $this->get_option( 'pendingAuthEmailReminder', '2' ) ;
        $this->pendingAuthPeriod                    = absint( $this->get_option( 'pendingAuthPeriod', '1' ) ) ;
        $this->chargeDefaultCardIfOriginalCardFails = $this->saved_cards && 'yes' === $this->get_option( 'retryDefaultPM', 'no' ) ;
        $this->supports                             = array(
            'products',
            'refunds',
            'tokenization',
            'add_payment_method',
            'sumo_paymentplans'
                ) ;

        include_once('class-sumo-pp-stripe-api-request.php') ;
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) ) ;
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) ) ;
        add_action( 'wc_ajax_sumo_pp_stripe_verify_intent', array( $this, 'verify_intent' ) ) ;
        add_action( 'woocommerce_payment_token_deleted', array( $this, 'wc_payment_token_deleted' ), 10, 2 ) ;
        add_action( 'woocommerce_payment_token_set_default', array( $this, 'wc_payment_token_set_default' ) ) ;
        add_filter( 'woocommerce_get_customer_payment_tokens', array( $this, 'wc_get_customer_payment_tokens' ), 10, 3 ) ;
        add_action( 'sumopaymentplans_new_payment_order', array( $this, 'process_initial_payment_order_success' ), 10, 2 ) ;
        add_action( 'sumopaymentplans_process_stripe_success_response', array( $this, 'process_balance_payment_order_success' ), 10, 2 ) ;
        add_filter( "sumopaymentplans_auto_charge_{$this->id}_balance_payment", array( $this, 'charge_balance_payment' ), 10, 3 ) ;
        add_action( 'sumopaymentplans_stripe_requires_authentication', array( $this, 'prepare_customer_to_authorize_payment' ), 10, 2 ) ;
        add_filter( 'sumopaymentplans_get_next_eligible_payment_failed_status', array( $this, 'prevent_payment_from_cancel' ), 99, 2 ) ;

        add_action( 'sumopaymentplans_payment_awaiting_cancel', array( $this, 'switch_to_manual_payment_mode' ), 9999 ) ;
        add_action( 'sumopaymentplans_payment_is_cancelled', array( $this, 'switch_to_manual_payment_mode' ), 9999 ) ;
        add_action( 'sumopaymentplans_payment_is_failed', array( $this, 'switch_to_manual_payment_mode' ), 9999 ) ;
        add_action( 'sumopaymentplans_payment_is_in_pending_authorization', array( $this, 'switch_to_manual_payment_mode' ), 9999 ) ;
        add_action( 'sumopaymentplans_payment_is_completed', array( $this, 'switch_to_manual_payment_mode' ), 9999 ) ;
    }

    /**
     * Admin Settings
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'                  => array(
                'title'   => __( 'Enable/Disable', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'    => 'checkbox',
                'label'   => __( 'Stripe', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => 'no'
            ),
            'title'                    => array(
                'title'       => __( 'Title:', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user see during checkout.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'     => __( 'SUMO Payment Plans - Stripe', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
            ),
            'description'              => array(
                'title'    => __( 'Description', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'     => 'textarea',
                'default'  => __( 'Pay with Stripe. You can pay with your credit card, debit card and master card   ', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'desc_tip' => true,
            ),
            'cardiconfilter'           => array(
                'type'              => 'multiselect',
                'title'             => __( 'Card Brands to be Displayed', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 450px;',
                'default'           => array(
                    'visa',
                    'mastercard',
                    'amex',
                    'discover',
                    'jcb'
                ),
                'description'       => __( 'Selected card brands will be displayed next to gateway title.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'options'           => array(
                    'visa'       => 'Visa',
                    'mastercard' => 'Mastercard',
                    'amex'       => 'Amex',
                    'discover'   => 'Discover',
                    'jcb'        => 'JCB'
                ),
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'data-placeholder' => __( 'Select Card Brands..', SUMO_PP_PLUGIN_TEXT_DOMAIN )
                )
            ),
            'saved_cards'              => array(
                'title'       => __( 'Saved Cards', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'label'       => __( 'Enable Payment via Saved Cards', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'checkbox',
                'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Stripe servers, not on your store.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'testmode'                 => array(
                'title'       => __( 'Test Mode', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'checkbox',
                'label'       => __( 'Turn on testing', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'description' => __( 'Use the test mode on Stripe dashboard to verify everything works before going live.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'     => 'no',
            ),
            'livepublishablekey'       => array(
                'type'    => 'text',
                'title'   => __( 'Stripe API Live Publishable key', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => '',
            ),
            'livesecretkey'            => array(
                'type'    => 'text',
                'title'   => __( 'Stripe API Live Secret key', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => '',
            ),
            'testpublishablekey'       => array(
                'type'    => 'text',
                'title'   => __( 'Stripe API Test Publishable key', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => '',
            ),
            'testsecretkey'            => array(
                'type'    => 'text',
                'title'   => __( 'Stripe API Test Secret key', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default' => '',
            ),
            'checkoutmode'             => array(
                'title'   => __( 'Checkout Mode', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'    => 'select',
                'default' => 'default',
                'options' => array(
                    'default'        => __( 'Default', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                    'inline_cc_form' => __( 'Inline Credit Card Form', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                ),
            ),
            'autoPaymentFailure'       => array(
                'title' => __( 'Automatic Payment Failure Settings', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'  => 'title',
            ),
            'retryDefaultPM'           => array(
                'title'       => __( 'Authenticate Future Payments using Default Card', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'label'       => __( 'Enable', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'type'        => 'checkbox',
                'description' => __( 'If enabled, payment retries will be happened using the default card in case if the originally authorized card for the respective deposit/payment plan is not able to process the future payment for some reason', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'SCADesc'                  => array(
                'type'        => 'title',
                'description' => __( 'Some banks require customer authentication each time during a balance payment which is not controlled by Stripe. So, even if customer has authorized for future payments of deposit/payment plan, the authorization will be declined by banks. In such case, customer has to manually process their future payments. The following options controls such scenarios.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
            ),
            'pendingAuthPeriod'        => array(
                'type'              => 'number',
                'title'             => __( 'Pending Authorization Period', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'           => '1',
                'description'       => __( 'day', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'desc_tip'          => __( 'This option controls how long the deposit/payment plan needs to be in "Pending Authorization" status until the customer pays for the balance payment or else it was unable to charge for the payment automatically in case of automatic payments. For example, if it is set as 2 then, the deposit/payment plan will be in "Pending Authorization" status for 2 days from the payment due date.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'custom_attributes' => array(
                    'min' => 0
                )
            ),
            'pendingAuthEmailReminder' => array(
                'type'              => 'number',
                'title'             => __( 'Number of Emails to send during Pending Authorization', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'default'           => '2',
                'description'       => __( 'times per day', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'desc_tip'          => __( 'This option controls the number of times the deposit/payment plan emails will be send to the customer in case of a payment failure when the deposit/payment plan in Pending Authorization status.', SUMO_PP_PLUGIN_TEXT_DOMAIN ),
                'custom_attributes' => array(
                    'min' => 0
                )
            ),
                ) ;
    }

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {
        $icon = '' ;

        foreach ( $this->cardiconfilter as $icon_name ) {
            if ( ! $icon_name ) {
                continue ;
            }

            $icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . "/assets/images/icons/credit-cards/{$icon_name}.png" ) . '" alt="' . esc_attr( ucfirst( $icon_name ) ) . '" />' ;
        }

        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id ) ;
    }

    /**
     * Gets the transaction URL linked to Stripe dashboard.
     */
    public function get_transaction_url( $order ) {
        if ( $this->testmode ) {
            $this->view_transaction_url = 'https://dashboard.stripe.com/test/payments/%s' ;
        } else {
            $this->view_transaction_url = 'https://dashboard.stripe.com/payments/%s' ;
        }

        if ( 'setup_intent' === $this->get_intentObj_from_order( $order ) ) {
            $this->view_transaction_url = '' ;
        }

        return parent::get_transaction_url( $order ) ;
    }

    /**
     * Can the order be refunded via Stripe?
     * @param  WC_Order $order
     * @return bool
     */
    public function can_refund_order( $order ) {
        return $order && $order->get_transaction_id() ;
    }

    /**
     * Checks if gateway should be available to use.
     */
    public function is_available() {
        if ( is_account_page() && ! $this->saved_cards ) {
            return false ;
        }

        return parent::is_available() ;
    }

    /**
     * Outputs scripts for Stripe elements.
     */
    public function payment_scripts() {
        if ( 'yes' !== $this->enabled && ! is_checkout() && ! is_add_payment_method_page() && ! is_checkout_pay_page() ) {
            return ;
        }

        if ( $this->saved_cards && $this->supports( 'tokenization' ) && is_checkout_pay_page() ) {
            $this->tokenization_script() ;
        }

        SUMO_PP_Enqueues::enqueue_script( 'sumo-pp-stripe-lib', 'https://js.stripe.com/v3/', array(), array( 'jquery' ), '3.0', true ) ;
        SUMO_PP_Enqueues::enqueue_script( 'sumo-pp-stripe', SUMO_PP_Enqueues::get_asset_url( 'js/frontend/stripe.js' ), array(
            'payment_method' => $this->id,
            'key'            => $this->testmode ? $this->testpublishablekey : $this->livepublishablekey,
            'checkoutmode'   => $this->checkoutmode,
                ), array( 'jquery', 'sumo-pp-stripe-lib' ), SUMO_PP_PLUGIN_VERSION, true ) ;
        SUMO_PP_Enqueues::enqueue_style( 'sumo-pp-stripe-style', SUMO_PP_Enqueues::get_asset_url( 'css/stripe.css' ) ) ;
    }

    /**
     * Render Elements
     */
    public function elements_form() {
        ?>
        <fieldset id="wc-<?php echo esc_attr( $this->id ) ; ?>-cc-form" class="wc-credit-card-form wc-payment-form">
            <?php
            if ( 'inline_cc_form' === $this->checkoutmode ) {
                ?>
                <label for="stripe-card-element">
                    <?php esc_html_e( 'Credit or debit card', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>
                </label>
                <div id="sumo-pp-stripe-card-element" class="sumo-pp-stripe-elements-field">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
                <?php
            } else {
                ?>
                <div class="form-row form-row-wide">
                    <label for="stripe-card-element"><?php esc_html_e( 'Card Number', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?> <span class="required">*</span></label>
                    <div class="sumo-pp-stripe-card-group">
                        <div id="sumo-pp-stripe-card-element" class="sumo-pp-stripe-elements-field">
                            <!-- a Stripe Element will be inserted here. -->
                        </div>

                        <i class="sumo-pp-stripe-credit-card-brand sumo-pp-stripe-card-brand" alt="Credit Card"></i>
                    </div>
                </div>

                <div class="form-row form-row-first">
                    <label for="stripe-exp-element"><?php esc_html_e( 'Expiry Date', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?> <span class="required">*</span></label>

                    <div id="sumo-pp-stripe-exp-element" class="sumo-pp-stripe-elements-field">
                        <!-- a Stripe Element will be inserted here. -->
                    </div>
                </div>

                <div class="form-row form-row-last">
                    <label for="stripe-cvc-element"><?php esc_html_e( 'Card Code (CVC)', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?> <span class="required">*</span></label>
                    <div id="sumo-pp-stripe-cvc-element" class="sumo-pp-stripe-elements-field">
                        <!-- a Stripe Element will be inserted here. -->
                    </div>
                </div>
                <?php
            }
            ?> 
            <!-- Used to display form errors. -->
            <div class="sumo-pp-stripe-card-errors" role="alert"></div>
        </fieldset>
        <?php
    }

    /**
     * Add payment fields.
     */
    public function payment_fields() {
        if ( $this->supports( 'sumo_paymentplans' ) ) {
            add_filter( "sumopaymentplans_allow_payment_mode_selection_in_{$this->id}_payment_gateway", '__return_true' ) ;
        }

        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) ) ;
        }

        if ( $this->saved_cards && $this->supports( 'tokenization' ) && is_checkout() ) {
            $this->tokenization_script() ;
            $this->saved_payment_methods() ;
        }

        $this->elements_form() ;
    }

    /**
     * Adds an error message wrapper to each saved method.
     *
     * @param WC_Payment_Token $token Payment Token.
     * @return string Generated payment method HTML
     */
    public function get_saved_payment_method_option_html( $token ) {
        $html          = parent::get_saved_payment_method_option_html( $token ) ;
        $error_wrapper = '<div class="sumo-pp-stripe-card-errors" role="alert"></div>' ;

        return preg_replace( '~</(\w+)>\s*$~', "$error_wrapper</$1>", $html ) ;
    }

    /**
     * Process a Stripe Payment.
     */
    public function process_payment( $order_id ) {

        try {
            if ( ! $maybe_payment_order = _sumo_pp_get_order( $order_id ) ) {
                throw new Exception( __( 'Payment failed: Invalid order !!', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $pm = SUMO_PP_Stripe_API_Request::request( 'retrieve_pm', array(
                        'id' => $this->get_pm_via_post(),
                    ) ) ;

            if ( is_wp_error( $pm ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $is_payment_order = $maybe_payment_order->contains_payment_data() ;
            $stripe_customer  = false ;

            if ( get_current_user_id() && ($is_payment_order || $this->saved_cards) ) {
                $stripe_customer = $this->maybe_create_customer( $this->prepare_current_userdata() ) ;
            }

            $auto_payments_enabled = $stripe_customer && $is_payment_order && 'auto' === _sumo_pp()->gateways->get_chosen_mode_of_payment( $this->id ) ? true : false ;
            $save_pm               = $stripe_customer && $this->saved_cards && 'stripe' === $this->is_pm_posted_via() ;

            if ( $auto_payments_enabled ) {
                $save_pm = ( ! $this->saved_cards || 'stripe' === $this->is_pm_posted_via() ) ? true : false ;
            }

            $this->save_stripe_pm_to_order( $maybe_payment_order->order, $pm ) ;
            $this->save_payment_mode_to_order( $maybe_payment_order->order, $auto_payments_enabled ? 'auto' : 'manual'  ) ;
            $this->save_customer_to_order( $maybe_payment_order->order, $stripe_customer ) ;

            if ( $maybe_payment_order->order->get_total() <= 0 ) {
                $result = $this->process_order_without_payment( $maybe_payment_order->order, $pm, $stripe_customer, $save_pm, $auto_payments_enabled ) ;
            } else {
                $result = $this->process_order_payment( $maybe_payment_order->order, $pm, $stripe_customer, $save_pm, $auto_payments_enabled ) ;
            }

            if ( $save_pm && isset( $result[ 'result' ], $result[ 'intent' ] ) && 'success' === $result[ 'result' ] ) {
                $this->attach_pm_to_customer( $result[ 'intent' ] ) ;
            }
        } catch ( Exception $e ) {
            if ( isset( $maybe_payment_order->order ) ) {
                $maybe_payment_order->order->add_order_note( esc_html( $e->getMessage() ) ) ;
                $maybe_payment_order->order->save() ;

                $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log(), array(
                    'order' => $maybe_payment_order->get_id(),
                ) ) ;
            } else {
                $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
            }

            wc_add_notice( esc_html( $e->getMessage() ), 'error' ) ;

            return array(
                'result'   => 'failure',
                'redirect' => $this->get_return_url( $maybe_payment_order->order )
                    ) ;
        }
        return $result ;
    }

    /**
     * Process an order that does require payment.
     */
    public function process_order_payment( &$order, $pm, $stripe_customer, $save_pm = false, $auto_payments_enabled = false ) {
        //Check if the pi is already available for this order
        $pi      = SUMO_PP_Stripe_API_Request::request( 'retrieve_pi', array( 'id' => $this->get_intent_from_order( $order ) ) ) ;
        $request = array(
            'payment_method' => $pm->id,
            'amount'         => $order->get_total(),
            'currency'       => $order->get_currency(),
            'metadata'       => $this->prepare_metadata_from_order( $order, $auto_payments_enabled ),
            'shipping'       => wc_shipping_enabled() ? $this->prepare_userdata_from_order( $order, 'shipping' ) : $this->prepare_userdata_from_order( $order ),
            'description'    => $this->prepare_payment_description( $order ),
                ) ;

        if ( $stripe_customer ) {
            $request[ 'customer' ]           = $stripe_customer->id ;
            $request[ 'setup_future_usage' ] = 'off_session' ;
        }

        if ( is_wp_error( $pi ) || ($stripe_customer && $stripe_customer->id !== $pi->customer) ) {
            $pi = SUMO_PP_Stripe_API_Request::request( 'create_pi', $request ) ;
        } else {
            $request[ 'id' ] = $pi->id ;

            $pi = SUMO_PP_Stripe_API_Request::request( 'update_pi', $request ) ;
        }

        if ( is_wp_error( $pi ) ) {
            throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
        }

        $this->save_intent_to_order( $order, $pi ) ;

        if ( 'requires_confirmation' === $pi->status ) {
            // An pi with a payment method is ready to be confirmed.
            $pi->confirm( array(
                'payment_method' => $pm->id,
            ) ) ;
        }

        // If the intent requires a 3DS flow, redirect to it.
        if ( 'requires_action' === $pi->status || 'requires_source_action' === $pi->status ) {
            if ( is_checkout_pay_page() ) {
                $this->prompt_cutomer_to_auth_payment() ;
            }

            return array(
                'result'   => 'success',
                'redirect' => $this->prepare_customer_intent_verify_url( $pi, array(
                    'order'       => $order->get_id(),
                    'save_pm'     => $save_pm,
                    'redirect_to' => $this->get_return_url( $order ),
                ) ),
                    ) ;
        }

        //Process pi response.
        $result = $this->process_response( $pi, $order ) ;

        if ( 'success' !== $result ) {
            throw new Exception( $result ) ;
        }

        return array(
            'result'   => 'success',
            'intent'   => $pi,
            'redirect' => $this->get_return_url( $order )
                ) ;
    }

    /**
     * Process an order that doesn't require payment.
     */
    public function process_order_without_payment( &$order, $pm, $stripe_customer, $save_pm = false, $auto_payments_enabled = false ) {
        // To charge future payments make sure to confirm the si before the order gets completed.
        if ( $stripe_customer && $auto_payments_enabled ) {
            //Check if the si is already available for this order
            $si      = SUMO_PP_Stripe_API_Request::request( 'retrieve_si', array( 'id' => $this->get_intent_from_order( $order ) ) ) ;
            $request = array(
                'payment_method' => $pm->id,
                'customer'       => $stripe_customer->id,
                'metadata'       => $this->prepare_metadata_from_order( $order, $auto_payments_enabled ),
                'description'    => $this->prepare_payment_description( $order ),
                    ) ;

            if ( is_wp_error( $si ) || $stripe_customer->id !== $si->customer ) {
                $request[ 'usage' ] = 'off_session' ;

                $si = SUMO_PP_Stripe_API_Request::request( 'create_si', $request ) ;
            } else {
                $request[ 'id' ] = $si->id ;

                $si = SUMO_PP_Stripe_API_Request::request( 'update_si', $request ) ;
            }

            if ( is_wp_error( $si ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $this->save_intent_to_order( $order, $si ) ;

            if ( 'requires_confirmation' === $si->status ) {
                // An si with a payment method is ready to be confirmed.
                $si->confirm( array(
                    'payment_method' => $pm->id,
                ) ) ;
            }

            // If the intent requires a 3DS flow, redirect to it.
            if ( 'requires_action' === $si->status || 'requires_source_action' === $si->status ) {
                if ( is_checkout_pay_page() ) {
                    $this->prompt_cutomer_to_auth_payment() ;
                }

                return array(
                    'result'   => 'success',
                    'redirect' => $this->prepare_customer_intent_verify_url( $si, array(
                        'order'       => $order->get_id(),
                        'save_pm'     => $save_pm,
                        'redirect_to' => $this->get_return_url( $order ),
                    ) ),
                        ) ;
            }

            //Process si response.
            $result = $this->process_response( $si, $order ) ;

            if ( 'success' !== $result ) {
                throw new Exception( $result ) ;
            }

            return array(
                'result'   => 'success',
                'intent'   => $si,
                'redirect' => $this->get_return_url( $order )
                    ) ;
        } else {
            // Complete free payment 
            $order->payment_complete() ;

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
                    ) ;
        }
    }

    /**
     * Process a refund if supported.
     */
    public function process_refund( $order_id, $amount = null, $reason = null ) {

        try {
            if ( ! $order = wc_get_order( $order_id ) ) {
                throw new Exception( __( 'Refund failed: Invalid order', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            if ( ! $this->can_refund_order( $order ) ) {
                throw new Exception( __( 'Refund failed: No transaction ID', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $pi = SUMO_PP_Stripe_API_Request::request( 'retrieve_pi', array( 'id' => $this->get_intent_from_order( $order ) ) ) ;

            if ( is_wp_error( $pi ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $charge = end( $pi->charges->data ) ;
            $refund = SUMO_PP_Stripe_API_Request::request( 'create_refund', array(
                        'amount' => $amount,
                        'reason' => $reason,
                        'charge' => $charge->id,
                    ) ) ;

            if ( is_wp_error( $refund ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }
        } catch ( Exception $e ) {
            if ( isset( $order ) && is_a( $order, 'WC_Order' ) ) {
                $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log(), array(
                    'order' => $order->get_id(),
                ) ) ;
            } else {
                $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
            }

            return new WP_Error( 'sumo-pp-stripe-error', $e->getMessage() ) ;
        }
        return true ;
    }

    /**
     * Add payment method via account screen
     */
    public function add_payment_method() {
        if ( 'stripe' !== $this->is_pm_posted_via() ) {
            return ;
        }

        try {
            if ( ! is_user_logged_in() ) {
                throw new Exception( __( 'Stripe: User should be logged in and continue.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $pm = SUMO_PP_Stripe_API_Request::request( 'retrieve_pm', array(
                        'id' => $this->get_pm_via_post(),
                    ) ) ;

            if ( is_wp_error( $pm ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $stripe_customer = $this->maybe_create_customer( $this->prepare_current_userdata() ) ;
            $request         = array(
                'payment_method' => $pm->id,
                'customer'       => $stripe_customer->id,
                'usage'          => 'off_session',
                    ) ;

            $si = SUMO_PP_Stripe_API_Request::request( 'create_si', $request ) ;

            if ( is_wp_error( $si ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            if ( 'requires_confirmation' === $si->status ) {
                // An si with a payment method is ready to be confirmed.
                $si->confirm( array(
                    'payment_method' => $pm->id,
                ) ) ;
            }

            // If the intent requires a 3DS flow, redirect to it.
            if ( 'requires_action' === $si->status || 'requires_source_action' === $si->status ) {
                $this->prompt_cutomer_to_auth_payment() ;

                return array(
                    'result'   => 'awaiting_payment_confirmation',
                    'redirect' => $this->prepare_customer_intent_verify_url( $si, array(
                        'endpoint'    => 'add-payment-method',
                        'save_pm'     => true,
                        'redirect_to' => wc_get_endpoint_url( 'payment-methods' ),
                    ) ),
                        ) ;
            }

            //Process si response.
            $result = $this->process_response( $si, false ) ;

            if ( 'success' !== $result ) {
                throw new Exception( $result ) ;
            }

            $this->attach_pm_to_customer( $si ) ;
        } catch ( Exception $e ) {
            wc_add_notice( esc_html( $e->getMessage() ), 'error' ) ;
            $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
            return ;
        }

        return array(
            'result'   => 'success',
            'redirect' => wc_get_endpoint_url( 'payment-methods' ),
                ) ;
    }

    /**
     * Delete pm from Stripe.
     */
    public function wc_payment_token_deleted( $token_id, $token ) {
        if ( $this->id !== $token->get_gateway_id() ) {
            return ;
        }

        try {
            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $pm = SUMO_PP_Stripe_API_Request::request( 'retrieve_pm', array( 'id' => $token->get_token() ) ) ;

            if ( is_wp_error( $pm ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $pm->detach() ;
        } catch ( Exception $e ) {
            wc_add_notice( esc_html( $e->getMessage() ), 'error' ) ;
            $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
        }
    }

    /**
     * Set as default pm in Stripe.
     */
    public function wc_payment_token_set_default( $token_id ) {
        $token = WC_Payment_Tokens::get( $token_id ) ;

        if ( $this->id !== $token->get_gateway_id() ) {
            return ;
        }

        try {
            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $stripe_customer = SUMO_PP_Stripe_API_Request::request( 'update_customer', array(
                        'id'               => $this->get_customer_from_user(),
                        'invoice_settings' => array(
                            'default_payment_method' => $token->get_token(),
                        )
                    ) ) ;

            if ( is_wp_error( $stripe_customer ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            if ( SUMO_PP_Stripe_API_Request::is_customer_deleted( $stripe_customer ) ) {
                throw new Exception( __( 'Stripe: Couldn\'t find valid customer to set default payment method.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }
        } catch ( Exception $e ) {
            wc_add_notice( esc_html( $e->getMessage() ), 'error' ) ;
            $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
        }
    }

    /**
     * Get our payment method from WC_Payment_Token
     *
     * @return int
     */
    public function wc_get_our_token( $token ) {
        return $this->id === $token->get_gateway_id() ? $token->get_token() : '' ;
    }

    /**
     * Get our default payment method from WC_Payment_Token
     *
     * @return int
     */
    public function wc_get_our_default_token( $token ) {
        return $this->id === $token->get_gateway_id() && $token->is_default() ? $token->get_token() : '' ;
    }

    /**
     * Get saved tokens from Stripe
     *
     * @return array
     */
    public function wc_get_customer_payment_tokens( $tokens, $user_id, $gateway_id ) {
        if ( ! is_user_logged_in() || ! class_exists( 'WC_Payment_Token_CC' ) ) {
            return $tokens ;
        }

        // Gateway id is valid only in checkout page, so we are doing this way
        if ( '' !== $gateway_id && $this->id !== $gateway_id ) {
            return $tokens ;
        }

        try {
            $our_tokens = array_filter( array_map( array( $this, 'wc_get_our_token' ), $tokens ) ) ;
            $customer   = $this->get_customer_from_user( $user_id ) ;

            if ( empty( $customer ) ) {
                return $tokens ;
            }

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $customers_pm = SUMO_PP_Stripe_API_Request::request( 'retrieve_all_pm', array(
                        'customer' => $customer,
                    ) ) ;

            if ( is_wp_error( $customers_pm ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            if ( empty( $customers_pm->data ) ) {
                throw new Exception( sprintf( __( 'No such payment methods available for customer %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $customer ) ) ;
            }

            $customer_paymentMethods = array() ;
            foreach ( $customers_pm->data as $pm ) {
                if ( ! isset( $pm->id ) || 'card' !== $pm->type ) {
                    continue ;
                }

                if ( ! in_array( $pm->id, $our_tokens ) ) {
                    $token                      = $this->add_wc_payment_token( $pm, $user_id ) ;
                    $tokens[ $token->get_id() ] = $token ;
                }

                $customer_paymentMethods[] = $pm->id ;
            }

            if ( is_add_payment_method_page() ) {
                if ( $this->chargeDefaultCardIfOriginalCardFails && ! empty( $customer_paymentMethods ) ) {
                    $our_default_token = implode( array_filter( array_map( array( $this, 'wc_get_our_default_token' ), $tokens ) ) ) ;

                    if ( in_array( $our_default_token, $customer_paymentMethods ) ) {
                        wc_print_notice( __( 'In case if the originally authorized card for the respective deposit/payment plan is not able to process the future payment for some reason then the payment retry will happen using the default card selected here', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'notice' ) ;
                    }
                }
            }
        } catch ( Exception $e ) {
            $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
        }
        return $tokens ;
    }

    /**
     *  Process the given response
     */
    public function process_response( $response, $order = false ) {

        switch ( $response->status ) {
            case 'succeeded':
                do_action( 'sumopaymentplans_process_stripe_success_response', $response, $order ) ;

                if ( $order ) {
                    if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
                        $order->payment_complete( $response->id ) ;
                    }

                    if ( 'setup_intent' === $response->object ) {
                        $order->add_order_note( __( 'Stripe: payment complete. Customer has approved for future payments.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                    } else {
                        $order->add_order_note( __( 'Stripe: payment complete', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                    }

                    $order->set_transaction_id( $response->id ) ;
                    $order->save() ;
                }
                return 'success' ;
                break ;
            case 'processing':
                do_action( 'sumopaymentplans_process_stripe_success_response', $response, $order ) ;

                if ( $order ) {
                    if ( ! $order->has_status( 'on-hold' ) ) {
                        $order->update_status( 'on-hold' ) ;
                    }

                    if ( 'setup_intent' === $response->object ) {
                        $order->add_order_note( sprintf( __( 'Stripe: awaiting confirmation by the customer to approve for future payments: %s.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $response->id ) ) ;
                    } else {
                        $order->add_order_note( sprintf( __( 'Stripe: awaiting payment: %s.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $response->id ) ) ;
                    }

                    $order->set_transaction_id( $response->id ) ;
                    $order->save() ;
                }
                return 'success' ;
                break ;
            case 'requires_payment_method':
            case 'requires_source': // BKWD CMPT
            case 'canceled':
                $this->log_err( $response, $order ? array( 'order' => $order->get_id() ) : array()  ) ;

                do_action( 'sumopaymentplans_process_stripe_failed_response', $response, $order ) ;

                if ( isset( $response->last_setup_error ) ) {
                    $message = $response->last_setup_error ? sprintf( __( 'Stripe: SCA authentication failed. Reason: %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $response->last_setup_error->message ) : __( 'Stripe: SCA authentication failed.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
                } else if ( isset( $response->last_payment_error ) ) {
                    $message = $response->last_payment_error ? sprintf( __( 'Stripe: SCA authentication failed. Reason: %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $response->last_payment_error->message ) : __( 'Stripe: SCA authentication failed.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
                } else if ( isset( $response->failure_message ) ) {
                    $message = $response->failure_message ? sprintf( __( 'Stripe: payment failed. Reason: %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $response->failure_message ) : __( 'Stripe: payment failed.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
                } else {
                    $message = __( 'Stripe: payment failed.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ;
                }

                if ( $order ) {
                    $order->add_order_note( $message ) ;
                    $order->save() ;
                }

                return $message ;
                break ;
        }

        $this->log_err( $response, $order ? array( 'order' => $order->get_id() ) : array()  ) ;
        return 'failure' ;
    }

    /**
     * Verify pi/si via Stripe.js
     */
    public function verify_intent() {

        try {
            if ( empty( $_GET[ 'nonce' ] ) || empty( $_GET[ 'endpoint' ] ) || empty( $_GET[ 'intent' ] ) || empty( $_GET[ 'intentObj' ] ) || ! wp_verify_nonce( sanitize_key( $_GET[ 'nonce' ] ), 'sumo_pp_stripe_confirm_intent' ) ) {
                throw new Exception( __( 'Stripe: Intent verification failed.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            }

            if ( in_array( $_GET[ 'endpoint' ], array( 'checkout', 'pay-for-order' ) ) ) {
                $order = wc_get_order( isset( $_GET[ 'order' ] ) ? absint( $_GET[ 'order' ] ) : 0 ) ;

                if ( ! $order ) {
                    throw new Exception( __( 'Stripe: Invalid order while verifying intent confirmation.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                }

                if ( $this->id !== $order->get_payment_method() ) {
                    throw new Exception( __( 'Stripe: Invalid payment method while verifying intent confirmation.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                }
            } else {
                $order = false ;
            }

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            if ( 'setup_intent' === $_GET[ 'intentObj' ] ) {
                $intent = SUMO_PP_Stripe_API_Request::request( 'retrieve_si', array( 'id' => wc_clean( ( string ) $_GET[ 'intent' ] ) ) ) ;
            } else {
                $intent = SUMO_PP_Stripe_API_Request::request( 'retrieve_pi', array( 'id' => wc_clean( ( string ) $_GET[ 'intent' ] ) ) ) ;
            }

            if ( is_wp_error( $intent ) ) {
                throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
            }

            $result = $this->process_response( $intent, $order ) ;

            if ( 'success' !== $result ) {
                throw new Exception( $result ) ;
            }

            if ( $intent->customer && isset( $_GET[ 'save_pm' ] ) && $_GET[ 'save_pm' ] ) {
                $pm = $this->attach_pm_to_customer( $intent ) ;

                if ( 'add-payment-method' === $_GET[ 'endpoint' ] ) {
                    $this->add_wc_payment_token( $pm ) ;
                    wc_add_notice( __( 'Payment method successfully added.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                }
            }

            if ( isset( $_GET[ 'is_ajax' ] ) ) {
                return ;
            }

            $redirect_url = ! empty( $_GET[ 'redirect_to' ] ) ? esc_url_raw( wp_unslash( $_GET[ 'redirect_to' ] ) ) : '' ;

            if ( empty( $redirect_url ) ) {
                if ( $order ) {
                    $redirect_url = $this->get_return_url( $order ) ;
                } else {
                    $redirect_url = WC()->cart->is_empty() ? get_permalink( wc_get_page_id( 'shop' ) ) : wc_get_checkout_url() ;
                }
            }

            wp_safe_redirect( $redirect_url ) ;
            exit ;
        } catch ( Exception $e ) {
            if ( isset( $_GET[ 'is_ajax' ] ) ) {
                $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
                return ;
            }

            wc_add_notice( esc_html( $e->getMessage() ), 'error' ) ;

            $redirect_url = ! empty( $_GET[ 'redirect_to' ] ) ? esc_url_raw( wp_unslash( $_GET[ 'redirect_to' ] ) ) : '' ;

            if ( empty( $redirect_url ) ) {
                if ( isset( $order ) && is_a( $order, 'WC_Order' ) ) {
                    $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log(), array( 'order' => $order->get_id() ) ) ;
                    $redirect_url = ! empty( $_GET[ 'redirect_to' ] ) ? esc_url_raw( wp_unslash( $_GET[ 'redirect_to' ] ) ) : $this->get_return_url( $order ) ;
                } else {
                    $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log() ) ;
                    $redirect_url = WC()->cart->is_empty() ? get_permalink( wc_get_page_id( 'shop' ) ) : wc_get_checkout_url() ;
                }
            }

            wp_safe_redirect( $redirect_url ) ;
            exit ;
        }
    }

    /**
     * Charge the customer automatically to pay their balance payment
     * 
     * @param bool $bool
     * @param SUMO_PP_Payment $payment
     * @param WC_Order $balance_payable_order
     * @return bool
     */
    public function charge_balance_payment( $bool, $payment, $balance_payable_order, $retry = false ) {

        try {

            SUMO_PP_Stripe_API_Request::init( $this ) ;

            $this->retry_failed_payment = $retry ;

            $request = array(
                'customer'       => $this->get_stripe_customer_from_payment( $payment ),
                'payment_method' => $this->get_stripe_pm_from_payment( $payment ),
                    ) ;

            if ( $this->retry_failed_payment ) {
                $payment->add_payment_note( __( 'Stripe: Start retrying payment with the default card chosen by the customer.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), 'pending', __( 'Stripe Charging Default Card', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

                $customer = SUMO_PP_Stripe_API_Request::request( 'retrieve_customer', array(
                            'id' => $request[ 'customer' ]
                        ) ) ;

                if ( is_wp_error( $customer ) ) {
                    throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
                }

                if ( SUMO_PP_Stripe_API_Request::is_customer_deleted( $customer ) ) {
                    throw new Exception( sprintf( __( 'Stripe: Couldn\'t find the customer %s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $customer->id ) ) ;
                }

                if ( isset( $customer->invoice_settings->default_payment_method ) && $customer->invoice_settings->default_payment_method ) {
                    $request[ 'payment_method' ] = $customer->invoice_settings->default_payment_method ;
                } else if ( isset( $customer->default_source ) && $customer->default_source ) { // Applicable if the source set as default in Stripe Dashboard
                    $request[ 'payment_method' ] = $customer->default_source ;
                } else {
                    throw new Exception( __( 'Stripe: Couldn\'t find any default card from the customer.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
                }
            }

            $this->save_stripe_pm_to_order( $balance_payable_order, $request[ 'payment_method' ] ) ;
            $this->save_payment_mode_to_order( $balance_payable_order, 'auto' ) ;
            $this->save_customer_to_order( $balance_payable_order, $request[ 'customer' ] ) ;

            $request[ 'amount' ]      = $balance_payable_order->get_total() ;
            $request[ 'currency' ]    = $balance_payable_order->get_currency() ;
            $request[ 'metadata' ]    = $this->prepare_metadata_from_order( $balance_payable_order, true, $payment ) ;
            $request[ 'shipping' ]    = wc_shipping_enabled() ? $this->prepare_userdata_from_order( $balance_payable_order, 'shipping' ) : $this->prepare_userdata_from_order( $balance_payable_order ) ;
            $request[ 'description' ] = $this->prepare_payment_description( $balance_payable_order ) ;
            $request[ 'off_session' ] = $request[ 'confirm' ]     = true ;

            $response = SUMO_PP_Stripe_API_Request::request( 'create_pi', $request ) ;

            if ( is_wp_error( $response ) ) {
                if ( 'authentication_required' === SUMO_PP_Stripe_API_Request::get_last_declined_code() ) {
                    throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message(), self::STRIPE_REQUIRES_AUTH ) ;
                } else {
                    throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message(), self::PAYMENT_RETRY_WITH_DEFAULT_CARD ) ;
                }
            }

            $this->save_intent_to_order( $balance_payable_order, $response ) ;

            //Process pi response.
            $result = $this->process_response( $response, $balance_payable_order ) ;

            if ( 'success' !== $result ) {
                throw new Exception( $result, self::PAYMENT_RETRY_WITH_DEFAULT_CARD ) ;
            }

            do_action( 'sumopaymentplans_stripe_balance_payment_successful', $payment, $balance_payable_order ) ;
        } catch ( Exception $e ) {
            $payment->add_payment_note( sprintf( __( 'Stripe: <b>%s</b>', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $e->getMessage() ), 'failure', __( 'Stripe Request Failed', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
            $this->log_err( SUMO_PP_Stripe_API_Request::get_last_log(), array(
                'order'   => $balance_payable_order->get_id(),
                'payment' => $payment->get_id(),
            ) ) ;

            if ( ! $this->retry_failed_payment ) {
                switch ( $e->getCode() ) {
                    case self::PAYMENT_RETRY_WITH_DEFAULT_CARD:
                        if ( $this->chargeDefaultCardIfOriginalCardFails ) {
                            return $this->charge_balance_payment( $bool, $payment, $balance_payable_order, true ) ;
                        }
                        break ;
                    case self::STRIPE_REQUIRES_AUTH:
                        if ( $this->chargeDefaultCardIfOriginalCardFails ) {
                            return $this->charge_balance_payment( $bool, $payment, $balance_payable_order, true ) ;
                        }

                        do_action( 'sumopaymentplans_stripe_requires_authentication', $payment, $balance_payable_order ) ;
                        break ;
                }
            }
            return false ;
        }
        return true ;
    }

    /**
     * Save Stripe customer, payment method after initial payment order success
     */
    public function process_initial_payment_order_success( $payment, $order ) {
        if ( $this->id !== $order->get_payment_method() ) {
            return ;
        }

        $payment->update_prop( 'payment_method', $this->id ) ;
        $payment->update_prop( 'payment_method_title', $this->get_title() ) ;
        $payment->set_payment_mode( $this->get_payment_mode_from_order( $order ) ) ;
        $payment->update_prop( 'stripe_customer_id', $this->get_stripe_customer_from_order( $order ) ) ;
        $payment->update_prop( 'stripe_payment_method', $this->get_stripe_pm_from_order( $order ) ) ;
    }

    /**
     * Save Stripe customer, payment method after balance payment order success
     */
    public function process_balance_payment_order_success( $response, $order ) {
        if ( ! $order || 0 === wp_get_post_parent_id( $order->get_id() ) ) {
            return ;
        }

        if ( ! $payment = _sumo_pp_get_payment( get_post_meta( $order->get_id(), '_payment_id', true ) ) ) {
            return ;
        }

        $order->set_payment_method( $this->id ) ;
        $order->set_payment_method_title( $this->get_title() ) ;
        $order->save() ;

        $payment->update_prop( 'payment_method', $this->id ) ;
        $payment->update_prop( 'payment_method_title', $this->get_title() ) ;
        $payment->set_payment_mode( $this->get_payment_mode_from_order( $order ) ) ;

        if ( ! $this->retry_failed_payment ) {
            $payment->update_prop( 'stripe_customer_id', $response->customer ) ;
            $payment->update_prop( 'stripe_payment_method', $response->payment_method ) ;
        }
    }

    /**
     * Prepare the customer to bring it 'OnSession' to complete their balance payment
     */
    public function prepare_customer_to_authorize_payment( $payment, $balance_payable_order ) {
        if ( ! $this->pendingAuthPeriod || ! $payment->has_status( array( 'pending', 'in_progress' ) ) ) {
            return ;
        }

        add_post_meta( $balance_payable_order->get_id(), '_sumo_pp_stripe_authentication_required', 'yes' ) ;

        $payment->update_status( 'pendng_auth' ) ;
        $payment->add_payment_note( sprintf( __( 'Payment automatically changed to Pending Authorization. Since the balance payment of Order #%s is not being paid so far.', SUMO_PP_PLUGIN_TEXT_DOMAIN ), $balance_payable_order->get_id() ), 'pending', __( 'Stripe Authorization is Pending', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;

        $pending_auth_timegap = _sumo_pp_get_timestamp( "+{$this->pendingAuthPeriod} days", _sumo_pp_get_timestamp( $payment->get_prop( 'next_payment_date' ) ) ) ;

        if ( $payment->get_remaining_installments() > 1 ) {
            $next_installment_date = _sumo_pp_get_timestamp( $payment->get_next_payment_date( $payment->get_next_of_next_installment_count() ) ) ;

            if ( $pending_auth_timegap >= $next_installment_date ) {
                $pending_auth_timegap = $next_installment_date ;
            }
        }

        $scheduler = _sumo_pp_get_job_scheduler( $payment ) ;
        $scheduler->unset_jobs() ;
        remove_filter( 'sumopaymentplans_get_next_eligible_payment_failed_status', array( $this, 'prevent_payment_from_cancel' ), 99, 2 ) ;
        $scheduler->schedule_next_eligible_payment_failed_status( $balance_payable_order->get_id(), $pending_auth_timegap ) ;
        add_filter( 'sumopaymentplans_get_next_eligible_payment_failed_status', array( $this, 'prevent_payment_from_cancel' ), 99, 2 ) ;
        $scheduler->schedule_reminder( $balance_payable_order->get_id(), $pending_auth_timegap, 'payment_pending_auth' ) ;

        do_action( 'sumopaymentplans_payment_is_in_pending_authorization', $payment->id, $balance_payable_order->get_id(), 'balance-payment-order' ) ;
    }

    /**
     * Hold the payment untill the payment is approved by the customer and so do not cancel the payment
     */
    public function prevent_payment_from_cancel( $next_eligible_status, $payment ) {
        if ( $payment->has_status( 'pendng_auth' ) ) {
            $next_eligible_status = '' ;
        }

        return $next_eligible_status ;
    }

    /**
     * Save Stripe paymentMethod in Order
     */
    public function save_stripe_pm_to_order( $order, $pm ) {
        update_post_meta( $order->get_id(), '_sumo_pp_stripe_payment_method', isset( $pm->id ) ? $pm->id : $pm  ) ;
    }

    /**
     * Save Stripe customer in Order
     */
    public function save_customer_to_order( $order, $customer ) {
        update_post_meta( $order->get_id(), '_sumo_pp_stripe_customer_id', isset( $customer->id ) ? $customer->id : $customer  ) ;
    }

    /**
     * Save mode of payment in Order
     */
    public function save_payment_mode_to_order( $order, $mode ) {
        $mode = 'auto' === $mode ? 'auto' : 'manual' ;
        update_post_meta( $order->get_id(), '_sumo_pp_payment_mode', $mode ) ;
    }

    /**
     * Save Stripe intent in Order
     */
    public function save_intent_to_order( $order, $intent ) {
        if ( 'payment_intent' === $intent->object ) {
            update_post_meta( $order->get_id(), '_sumo_pp_stripe_pi', $intent->id ) ;
        } else if ( 'setup_intent' === $intent->object ) {
            update_post_meta( $order->get_id(), '_sumo_pp_stripe_si', $intent->id ) ;
        }
        update_post_meta( $order->get_id(), '_sumo_pp_stripe_intentObject', $intent->object ) ;
    }

    /**
     * Prepare pi/si verification url
     */
    public function prepare_customer_intent_verify_url( $intent, $query_args = array() ) {
        $query_args = wp_parse_args( $query_args, array(
            'intent'      => $intent->id,
            'intentObj'   => $intent->object,
            'endpoint'    => '',
            'save_pm'     => false,
            'nonce'       => wp_create_nonce( 'sumo_pp_stripe_confirm_intent' ),
            'redirect_to' => get_site_url(),
                ) ) ;

        $query_args[ 'redirect_to' ] = rawurlencode( $query_args[ 'redirect_to' ] ) ;

        if ( empty( $query_args[ 'endpoint' ] ) ) {
            if ( is_checkout_pay_page() ) {
                $query_args[ 'endpoint' ] = 'pay-for-order' ;
            } else {
                $query_args[ 'endpoint' ] = 'checkout' ;
            }
        }

        // Redirect into the verification URL thereby we need to verify the intent
        $verification_url = rawurlencode( add_query_arg( $query_args, WC_AJAX::get_endpoint( 'sumo_pp_stripe_verify_intent' ) ) ) ;

        return sprintf( '#confirm-sumo-stripe-intent-%s:%s:%s:%s', $intent->client_secret, $intent->object, $query_args[ 'endpoint' ], $verification_url ) ;
    }

    /**
     * Prepare current userdata
     */
    public function prepare_current_userdata() {
        if ( ! $user = get_user_by( 'id', get_current_user_id() ) ) {
            return array() ;
        }

        $billing_first_name = get_user_meta( $user->ID, 'billing_first_name', true ) ;
        $billing_last_name  = get_user_meta( $user->ID, 'billing_last_name', true ) ;

        if ( empty( $billing_first_name ) ) {
            $billing_first_name = get_user_meta( $user->ID, 'first_name', true ) ;
        }

        if ( empty( $billing_last_name ) ) {
            $billing_last_name = get_user_meta( $user->ID, 'last_name', true ) ;
        }

        $userdata = array(
            'address' => array(
                'line1'       => get_user_meta( $user->ID, 'billing_address_1', true ),
                'line2'       => get_user_meta( $user->ID, 'billing_address_2', true ),
                'city'        => get_user_meta( $user->ID, 'billing_city', true ),
                'state'       => get_user_meta( $user->ID, 'billing_state', true ),
                'postal_code' => get_user_meta( $user->ID, 'billing_postcode', true ),
                'country'     => get_user_meta( $user->ID, 'billing_country', true ),
            ),
            'fname'   => $billing_first_name,
            'lname'   => $billing_last_name,
            'phone'   => get_user_meta( $user->ID, 'billing_phone', true ),
            'email'   => $user->user_email,
                ) ;
        return $userdata ;
    }

    /**
     * Prepare userdata from order
     * 
     * @param string $type billing|shipping
     */
    public function prepare_userdata_from_order( $order, $type = 'billing' ) {
        $userdata = array(
            'address' => array(
                'line1'       => get_post_meta( $order->get_id(), "_{$type}_address_1", true ),
                'line2'       => get_post_meta( $order->get_id(), "_{$type}_address_2", true ),
                'city'        => get_post_meta( $order->get_id(), "_{$type}_city", true ),
                'state'       => get_post_meta( $order->get_id(), "_{$type}_state", true ),
                'postal_code' => get_post_meta( $order->get_id(), "_{$type}_postcode", true ),
                'country'     => get_post_meta( $order->get_id(), "_{$type}_country", true ),
            ),
            'fname'   => get_post_meta( $order->get_id(), "_{$type}_first_name", true ),
            'lname'   => get_post_meta( $order->get_id(), "_{$type}_last_name", true ),
            'phone'   => get_post_meta( $order->get_id(), '_billing_phone', true ),
            'email'   => get_post_meta( $order->get_id(), '_billing_email', true ),
                ) ;
        return $userdata ;
    }

    /**
     * Prepare metadata to display in Stripe.
     * May be useful to keep track the payments/orders
     */
    public function prepare_metadata_from_order( $order, $auto_pay = false, $payment = null ) {
        $metadata = array(
            'Order' => '#' . $order->get_id(),
                ) ;

        if ( $payment ) {
            $metadata[ 'Payment' ] = '#' . $payment->get_payment_number() ;
        }

        if ( wp_get_post_parent_id( $order->get_id() ) > 0 ) {
            $metadata[ 'Order Type' ] = 'balance' ;
        } else {
            $metadata[ 'Order Type' ] = 'deposit' ;
        }

        $metadata[ 'Payment Mode' ] = $auto_pay ? 'automatic' : 'manual' ;
        $metadata[ 'Site Url' ]     = esc_url( get_site_url() ) ;
        return apply_filters( 'sumopaymentplans_stripe_metadata', $metadata, $order, $payment ) ;
    }

    /**
     * Prepare the description for each Stripe Payment.
     * 
     * @param WC_Order $order
     * @return string
     */
    public function prepare_payment_description( $order ) {
        $description = sprintf( __( '%1$s - Order %2$s', SUMO_PP_PLUGIN_TEXT_DOMAIN ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_id() ) ;
        return apply_filters( 'sumopaymentplans_stripe_payment_description', $description, $order ) ;
    }

    /**
     * Add token to WooCommerce.
     */
    public function add_wc_payment_token( $pm, $user_id = '' ) {
        if ( ! class_exists( 'WC_Payment_Token_CC' ) ) {
            throw new Exception( __( 'Stripe: Couldn\'t add payment method !!', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        }

        if ( 'payment_method' !== $pm->object || 'card' !== $pm->type ) {
            throw new Exception( __( 'Stripe: Invalid payment method. Please retry !!', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        }

        $wc_token = new WC_Payment_Token_CC() ;
        $wc_token->set_token( $pm->id ) ;
        $wc_token->set_gateway_id( $this->id ) ;
        $wc_token->set_card_type( strtolower( $pm->card->brand ) ) ;
        $wc_token->set_last4( $pm->card->last4 ) ;
        $wc_token->set_expiry_month( $pm->card->exp_month ) ;
        $wc_token->set_expiry_year( $pm->card->exp_year ) ;
        $wc_token->set_user_id( $user_id ? $user_id : get_current_user_id()  ) ;
        $wc_token->save() ;
        return $wc_token ;
    }

    /**
     * Maybe create Stripe Customer
     */
    public function maybe_create_customer( $args = array() ) {

        //Check if the user has already registered as Stripe Customer
        $stripe_customer = SUMO_PP_Stripe_API_Request::request( 'retrieve_customer', array(
                    'id' => $this->get_customer_from_user(),
                ) ) ;

        //If so then create new stripe customer
        if ( ! is_wp_error( $stripe_customer ) && ($saved_stripe_customer_deleted = SUMO_PP_Stripe_API_Request::is_customer_deleted( $stripe_customer )) ) {
            delete_user_meta( get_current_user_id(), '_sumo_pp_stripe_customer_id' ) ;
        }

        if ( is_wp_error( $stripe_customer ) || $saved_stripe_customer_deleted ) {
            $stripe_customer = SUMO_PP_Stripe_API_Request::request( 'create_customer', SUMO_PP_Stripe_API_Request::prepare_customer_details( $args ) ) ;
        }

        if ( is_wp_error( $stripe_customer ) ) {
            throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
        }

        update_user_meta( get_current_user_id(), '_sumo_pp_stripe_customer_id', $stripe_customer->id ) ;
        return $stripe_customer ;
    }

    /**
     * Attach payment method to Customer via Intent
     */
    public function attach_pm_to_customer( $intent ) {
        if ( ! isset( $intent->customer ) || ! $intent->customer ) {
            throw new Exception( __( 'Stripe: Couldn\'t find valid customer to attach payment method.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
        }

        $pm = SUMO_PP_Stripe_API_Request::request( 'retrieve_pm', array( 'id' => $intent->payment_method ) ) ;

        if ( is_wp_error( $pm ) ) {
            throw new Exception( SUMO_PP_Stripe_API_Request::get_last_error_message() ) ;
        }

        $pm->attach( array( 'customer' => $intent->customer ) ) ;
        return $pm ;
    }

    /**
     * Stripe error logger
     */
    public function log_err( $log, $map = array() ) {
        if ( empty( $log ) ) {
            return ;
        }

        SUMO_PP_WC_Logger::log( $log, $map ) ;
    }

    /**
     * Check if the paymentMethod posted via Stripe.js or WC Token
     */
    public function is_pm_posted_via() {
        if ( isset( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $this->id . '-payment-token' ] ) {
            return 'wc-token' ;
        } else if ( ! empty( $_POST[ 'sumo_pp_stripe_pm' ] ) ) {
            return 'stripe' ;
        }
        return null ;
    }

    /**
     * Get the cleaned paymentMethod created via POST.
     */
    public function get_pm_via_post() {
        switch ( $this->is_pm_posted_via() ) {
            case 'wc-token':
                $wc_token = WC_Payment_Tokens::get( wc_clean( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) ) ;

                if ( $wc_token && $wc_token->get_user_id() === get_current_user_id() ) {
                    return $wc_token->get_token() ;
                }
                break ;
            case 'stripe':
                return wc_clean( $_POST[ 'sumo_pp_stripe_pm' ] ) ;
                break ;
        }

        throw new Exception( __( 'Invalid payment method. Please retry with a new card number.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ) ;
    }

    /**
     * Get saved Stripe intent object from Order
     */
    public function get_intentObj_from_order( $order ) {
        return get_post_meta( $order->get_id(), '_sumo_pp_stripe_intentObject', true ) ;
    }

    /**
     * Get saved Stripe intent from Order
     */
    public function get_intent_from_order( $order ) {
        $metakey = 'setup_intent' === $this->get_intentObj_from_order( $order ) ? '_sumo_pp_stripe_si' : '_sumo_pp_stripe_pi' ;

        return get_post_meta( $order->get_id(), "$metakey", true ) ;
    }

    /**
     * Get payment mode from Order
     */
    public function get_payment_mode_from_order( $order ) {
        $payment_type = get_post_meta( $order->get_id(), '_sumo_pp_payment_mode', true ) ;

        return empty( $payment_type ) ? 'manual' : $payment_type ;
    }

    /**
     * Get saved Stripe customer ID from Order
     */
    public function get_stripe_customer_from_order( $order ) {
        return get_post_meta( $order->get_id(), '_sumo_pp_stripe_customer_id', true ) ;
    }

    /**
     * Get saved Stripe paymentMethod ID from Order
     */
    public function get_stripe_pm_from_order( $order ) {
        return get_post_meta( $order->get_id(), '_sumo_pp_stripe_payment_method', true ) ;
    }

    /**
     * Get saved Stripe customer from the user
     * 
     * @return string
     */
    public function get_customer_from_user( $user_id = '' ) {
        $user_id = $user_id ? $user_id : get_current_user_id() ;
        return get_user_meta( $user_id, '_sumo_pp_stripe_customer_id', true ) ;
    }

    /**
     * Get saved Stripe customer ID from payment
     * 
     * @return string
     */
    public function get_stripe_customer_from_payment( $payment ) {
        return get_post_meta( $payment->get_id(), '_stripe_customer_id', true ) ;
    }

    /**
     * Get saved Stripe paymentMethod ID from payment
     * 
     * @return string
     */
    public function get_stripe_pm_from_payment( $payment ) {
        return get_post_meta( $payment->get_id(), '_stripe_payment_method', true ) ;
    }

    /**
     * Prompt the customer to authorize their payment
     */
    public function prompt_cutomer_to_auth_payment() {
        wc_add_notice( esc_html( __( 'Almost there!! the only thing that still needs to be done is for you to authorize the payment with your bank.', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ), 'success' ) ;
    }

    /**
     * Switch to manual payment mode
     */
    public function switch_to_manual_payment_mode( $payment_id ) {
        $payment = _sumo_pp_get_payment( $payment_id ) ;

        if ( doing_action( 'sumopaymentplans_payment_is_completed' ) ) {
            $payment->set_payment_mode( 'manual', false ) ;
        } else {
            $payment->set_payment_mode( 'manual' ) ;
        }

        $payment->delete_prop( 'stripe_customer_id' ) ;
        $payment->delete_prop( 'stripe_payment_method' ) ;
    }

}

return new SUMO_PP_Stripe_Gateway() ;
