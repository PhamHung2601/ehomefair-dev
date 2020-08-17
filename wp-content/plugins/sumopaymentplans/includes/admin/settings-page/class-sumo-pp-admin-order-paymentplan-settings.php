<?php

/**
 * Order PaymentPlan Tab.
 * 
 * @class SUMO_PP_Order_PaymentPlan_Settings
 * @category Class
 */
class SUMO_PP_Order_PaymentPlan_Settings extends SUMO_PP_Abstract_Settings {

    /**
     * SUMO_PP_Order_PaymentPlan_Settings constructor.
     */
    public function __construct() {

        $this->id            = 'orderpp' ;
        $this->label         = __( 'Order PaymentPlan', $this->text_domain ) ;
        $this->custom_fields = array(
            'get_pay_balance_type',
            'get_limited_users',
            'get_selected_products',
        ) ;
        $this->settings      = $this->get_settings() ;
        $this->init() ;
    }

    /**
     * Get settings array.
     * @return array
     */
    public function get_settings() {
        global $current_section ;

        return apply_filters( 'sumopaymentplans_get_' . $this->id . '_settings', array(
            array(
                'name' => __( 'Order PaymentPlan Settings', $this->text_domain ),
                'type' => 'title',
                'id'   => $this->prefix . 'orderpp_settings'
            ),
            array(
                'name'     => __( 'Enable Order PaymentPlan', $this->text_domain ),
                'id'       => $this->prefix . 'enable_order_payment_plan',
                'newids'   => $this->prefix . 'enable_order_payment_plan',
                'type'     => 'checkbox',
                'std'      => 'no',
                'default'  => 'no',
                'desc'     => __( 'If enabled, a checkbox will be displayed on their checkout page using which customers can choose to pay for their orders using payment plans. Order PaymentPlan is not applicable if payment plans enabled products are in cart ', $this->text_domain ),
                'desc_tip' => true,
            ),
            array(
                'name'     => __( 'Select Product(s)', $this->text_domain ),
                'id'       => $this->prefix . 'get_order_payment_plan_products_select_type',
                'newids'   => $this->prefix . 'get_order_payment_plan_products_select_type',
                'type'     => 'select',
                'options'  => array(
                    'all_products'      => __( 'All Products', $this->text_domain ),
                    'selected_products' => __( 'Selected Products', $this->text_domain ),
                ),
                'std'      => 'all_products',
                'default'  => 'all_products',
                'desc_tip' => __( 'If "Selected Products" option is chosen, Order PaymentPlan option will be displayed only when the cart contains the products which you have selected', $this->text_domain ),
            ),
            array(
                'type' => $this->get_custom_field_type( 'get_selected_products' ),
            ),
            array(
                'name'    => __( 'Payment Type', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_type',
                'newids'  => $this->prefix . 'order_payment_type',
                'type'    => 'select',
                'options' => array(
                    'pay-in-deposit' => __( 'Pay a Deposit Amount', $this->text_domain ),
                    'payment-plans'  => __( 'Pay with Payment Plans', $this->text_domain ),
                ),
                'std'     => 'pay-in-deposit',
                'default' => 'pay-in-deposit',
            ),
            array(
                'name'    => __( 'Apply Global Level Settings', $this->text_domain ),
                'id'      => $this->prefix . 'apply_global_settings_for_order_payment_plan',
                'newids'  => $this->prefix . 'apply_global_settings_for_order_payment_plan',
                'type'    => 'checkbox',
                'std'     => 'no',
                'default' => 'no',
            ),
            array(
                'name'    => __( 'Force Deposit/Payment Plans', $this->text_domain ),
                'id'      => $this->prefix . 'force_order_payment_plan',
                'newids'  => $this->prefix . 'force_order_payment_plan',
                'type'    => 'checkbox',
                'std'     => 'no',
                'default' => 'no',
            ),
            array(
                'name'    => __( 'Deposit Type', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_plan_deposit_type',
                'newids'  => $this->prefix . 'order_payment_plan_deposit_type',
                'type'    => 'select',
                'options' => array(
                    'pre-defined'  => __( 'Predefined Deposit Amount', $this->text_domain ),
                    'user-defined' => __( 'User Defined Deposit Amount', $this->text_domain ),
                ),
                'std'     => 'pre-defined',
                'default' => 'pre-defined',
            ),
            array(
                'name'    => __( 'Deposit Price Type', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_plan_deposit_price_type',
                'newids'  => $this->prefix . 'order_payment_plan_deposit_price_type',
                'type'    => 'select',
                'options' => array(
                    'fixed-price'              => __( 'Fixed Price', $this->text_domain ),
                    'percent-of-product-price' => __( 'Percentage of Product Price', $this->text_domain ),
                ),
                'std'     => 'percent-of-product-price',
                'default' => 'percent-of-product-price',
            ),
            array(
                'name'              => __( 'Deposit Amount', $this->text_domain ),
                'id'                => $this->prefix . 'fixed_order_payment_plan_deposit_price',
                'newids'            => $this->prefix . 'fixed_order_payment_plan_deposit_price',
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => '0.01',
                    'step' => '0.01',
                ),
            ),
            array(
                'name'              => __( 'Deposit Percentage', $this->text_domain ),
                'id'                => $this->prefix . 'fixed_order_payment_plan_deposit_percent',
                'newids'            => $this->prefix . 'fixed_order_payment_plan_deposit_percent',
                'type'              => 'number',
                'std'               => '50',
                'default'           => '50',
                'desc'              => '',
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'min'  => '0.01',
                    'max'  => '99.99',
                    'step' => '0.01',
                ),
            ),
            array(
                'name'    => __( 'User Defined Deposit Type', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_plan_user_defined_deposit_type',
                'newids'  => $this->prefix . 'order_payment_plan_user_defined_deposit_type',
                'type'    => 'select',
                'options' => array(
                    'percent-of-product-price' => __( 'Percentage of Product Price', $this->text_domain ),
                    'fixed-price'              => __( 'Fixed Price', $this->text_domain ),
                ),
                'std'     => 'percent-of-product-price',
                'default' => 'percent-of-product-price',
            ),
            array(
                'name'              => __( 'Minimum Deposit (%)', $this->text_domain ),
                'id'                => $this->prefix . 'min_order_payment_plan_deposit',
                'newids'            => $this->prefix . 'min_order_payment_plan_deposit',
                'type'              => 'number',
                'std'               => '0.01',
                'default'           => '0.01',
                'desc'              => '',
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'min'  => '0.01',
                    'max'  => '99.99',
                    'step' => '0.01',
                ),
            ),
            array(
                'name'              => __( 'Maximum Deposit (%)', $this->text_domain ),
                'id'                => $this->prefix . 'max_order_payment_plan_deposit',
                'newids'            => $this->prefix . 'max_order_payment_plan_deposit',
                'type'              => 'number',
                'std'               => '99.99',
                'default'           => '99.99',
                'desc'              => '',
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'min'  => '0.01',
                    'max'  => '99.99',
                    'step' => '0.01',
                ),
            ),
            array(
                'name'     => __( 'Minimum Deposit Price', $this->text_domain ),
                'id'       => $this->prefix . 'min_order_payment_plan_user_defined_deposit_price',
                'newids'   => $this->prefix . 'min_order_payment_plan_user_defined_deposit_price',
                'type'     => 'text',
                'std'      => '',
                'default'  => '',
                'desc'     => '',
                'desc_tip' => true,
            ),
            array(
                'name'     => __( 'Maximum Deposit Price', $this->text_domain ),
                'id'       => $this->prefix . 'max_order_payment_plan_user_defined_deposit_price',
                'newids'   => $this->prefix . 'max_order_payment_plan_user_defined_deposit_price',
                'type'     => 'text',
                'std'      => '',
                'default'  => '',
                'desc'     => '',
                'desc_tip' => true,
            ),
            array(
                'type' => $this->get_custom_field_type( 'get_pay_balance_type' ),
            ),
            array(
                'name'    => __( 'Select Plans', $this->text_domain ),
                'id'      => $this->prefix . 'selected_plans_for_order_payment_plan',
                'newids'  => $this->prefix . 'selected_plans_for_order_payment_plan',
                'type'    => 'multiselect',
                'options' => _sumo_pp_get_payment_plan_names(),
                'std'     => array(),
                'default' => array(),
            ),
            array(
                'name'              => __( 'Minimum Order Total to Display Order PaymentPlan', $this->text_domain ),
                'id'                => $this->prefix . 'min_order_total_to_display_order_payment_plan',
                'newids'            => $this->prefix . 'min_order_total_to_display_order_payment_plan',
                'type'              => 'number',
                'std'               => '',
                'default'           => '',
                'custom_attributes' => array(
                    'step' => '0.01',
                ),
            ),
            array(
                'name'              => __( 'Maximum Order Total to Display Order PaymentPlan', $this->text_domain ),
                'id'                => $this->prefix . 'max_order_total_to_display_order_payment_plan',
                'newids'            => $this->prefix . 'max_order_total_to_display_order_payment_plan',
                'type'              => 'number',
                'std'               => '',
                'default'           => '',
                'custom_attributes' => array(
                    'step' => '0.01',
                ),
            ),
            array(
                'name'    => __( 'Order PaymentPlan Option in Checkout Page Label', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_plan_label',
                'newids'  => $this->prefix . 'order_payment_plan_label',
                'type'    => 'text',
                'std'     => __( 'Order PaymentPlan', $this->text_domain ),
                'default' => __( 'Order PaymentPlan', $this->text_domain ),
            ),
            array(
                'name'        => __( 'Order PaymentPlan Product Label', $this->text_domain ),
                'id'          => $this->prefix . 'order_payment_plan_product_label',
                'newids'      => $this->prefix . 'order_payment_plan_product_label',
                'type'        => 'text',
                'placeholder' => 'Order PaymentPlan',
            ),
            array(
                'name'     => __( 'Show Order PaymentPlan Option for', $this->text_domain ),
                'id'       => $this->prefix . 'show_order_payment_plan_for',
                'newids'   => $this->prefix . 'show_order_payment_plan_for',
                'type'     => 'select',
                'std'      => 'all_users',
                'default'  => 'all_users',
                'options'  => array(
                    'all_users'         => __( 'All Users', $this->text_domain ),
                    'include_users'     => __( 'Include User(s)', $this->text_domain ),
                    'exclude_users'     => __( 'Exclude User(s)', $this->text_domain ),
                    'include_user_role' => __( 'Include User Role(s)', $this->text_domain ),
                    'exclude_user_role' => __( 'Exclude User Role(s)', $this->text_domain )
                ),
                'desc'     => '',
                'desc_tip' => true,
            ),
            array(
                'type' => $this->get_custom_field_type( 'get_limited_users' )
            ),
            array(
                'name'     => __( 'Select User Role(s)', $this->text_domain ),
                'id'       => $this->prefix . 'get_limited_userroles_of_order_payment_plan',
                'newids'   => $this->prefix . 'get_limited_userroles_of_order_payment_plan',
                'type'     => 'multiselect',
                'options'  => _sumo_pp_get_user_roles( true ),
                'std'      => array(),
                'default'  => array(),
                'desc'     => '',
                'desc_tip' => true,
            ),
            array(
                'name'    => __( 'Order PaymentPlan Position', $this->text_domain ),
                'id'      => $this->prefix . 'order_payment_plan_form_position',
                'newids'  => $this->prefix . 'order_payment_plan_form_position',
                'type'    => 'select',
                'std'     => 'checkout_order_review',
                'default' => 'checkout_order_review',
                'options' => apply_filters( 'sumopaymentplans_orderpp_form_position', array(
                    'checkout_order_review'           => ucwords( str_replace( '_', ' ', 'woocommerce_checkout_order_review' ) ),
                    'checkout_after_customer_details' => ucwords( str_replace( '_', ' ', 'woocommerce_checkout_after_customer_details' ) ),
                    'before_checkout_form'            => ucwords( str_replace( '_', ' ', 'woocommerce_before_checkout_form' ) ),
                    'checkout_before_order_review'    => ucwords( str_replace( '_', ' ', 'woocommerce_checkout_before_order_review' ) ),
                ) ),
                'desc'    => __( 'Some themes do not support all the positions, if the positions is not supported then it might result in jquery conflict', $this->text_domain ),
            ),
            array( 'type' => 'sectionend', 'id' => $this->prefix . 'orderpp_settings' ),
            array(
                'name' => __( 'Troubleshoot Settings', $this->text_domain ),
                'type' => 'title',
                'id'   => $this->prefix . 'troubleshoot_settings'
            ),
            array(
                'name'     => __( 'Display Order PaymentPlan as', $this->text_domain ),
                'id'       => $this->prefix . 'order_payment_plan_display_mode',
                'newids'   => $this->prefix . 'order_payment_plan_display_mode',
                'type'     => 'select',
                'std'      => 'multiple',
                'default'  => 'multiple',
                'options'  => array(
                    'single'   => __( 'Single Line Item', $this->text_domain ),
                    'multiple' => __( 'Multiple Line Items', $this->text_domain ),
                ),
                'desc'     => __( 'Select Multiple Line Items option if you face any issues in checkout  when using Order PaymentPlan', $this->text_domain ),
                'desc_tip' => true,
            ),
            array( 'type' => 'sectionend', 'id' => $this->prefix . 'troubleshoot_settings' ),
        ) ) ;
    }

    /**
     * Save the custom options once.
     */
    public function custom_types_add_options() {
        // BKWD CMPT
        if ( false !== get_option( $this->prefix . 'version' ) ) {
            add_option( $this->prefix . 'order_payment_plan_display_mode', 'single' ) ;
        }

        add_option( $this->prefix . 'order_payment_plan_pay_balance_type', 'after' ) ;
        add_option( $this->prefix . 'order_payment_plan_pay_balance_after', '' ) ;
        add_option( $this->prefix . 'order_payment_plan_pay_balance_before', '' ) ;
        add_option( $this->prefix . 'get_limited_users_of_order_payment_plan', array() ) ;
        add_option( $this->prefix . 'get_selected_products_of_order_payment_plan', array() ) ;
    }

    /**
     * Delete the custom options.
     */
    public function custom_types_delete_options() {
        delete_option( $this->prefix . 'order_payment_plan_pay_balance_type' ) ;
        delete_option( $this->prefix . 'order_payment_plan_pay_balance_after' ) ;
        delete_option( $this->prefix . 'order_payment_plan_pay_balance_before' ) ;
        delete_option( $this->prefix . 'get_limited_users_of_order_payment_plan' ) ;
        delete_option( $this->prefix . 'get_selected_products_of_order_payment_plan' ) ;
    }

    /**
     * Save custom settings.
     */
    public function custom_types_save() {

        if ( isset( $_POST[ 'pay_balance_type' ] ) ) {
            update_option( $this->prefix . 'order_payment_plan_pay_balance_type', $_POST[ 'pay_balance_type' ] ) ;
        }
        if ( isset( $_POST[ 'pay_balance_after' ] ) ) {
            update_option( $this->prefix . 'order_payment_plan_pay_balance_after', $_POST[ 'pay_balance_after' ] ) ;
        }
        if ( isset( $_POST[ 'pay_balance_before' ] ) ) {
            update_option( $this->prefix . 'order_payment_plan_pay_balance_before', $_POST[ 'pay_balance_before' ] ) ;
        }
        if ( isset( $_POST[ 'get_limited_users' ] ) ) {
            update_option( $this->prefix . 'get_limited_users_of_order_payment_plan',  ! is_array( $_POST[ 'get_limited_users' ] ) ? array_filter( array_map( 'absint', explode( ',', $_POST[ 'get_limited_users' ] ) ) ) : $_POST[ 'get_limited_users' ]  ) ;
        }
        if ( isset( $_POST[ 'get_selected_products' ] ) ) {
            update_option( $this->prefix . 'get_selected_products_of_order_payment_plan',  ! is_array( $_POST[ 'get_selected_products' ] ) ? array_filter( array_map( 'absint', explode( ',', $_POST[ 'get_selected_products' ] ) ) ) : $_POST[ 'get_selected_products' ]  ) ;
        }
    }

    /**
     * Custom type field.
     */
    public function get_selected_products() {

        _sumo_pp_wc_search_field( array(
            'class'       => 'wc-product-search',
            'id'          => $this->prefix . 'get_selected_products',
            'name'        => 'get_selected_products',
            'type'        => 'product',
            'action'      => 'woocommerce_json_search_products_and_variations',
            'title'       => __( 'Select Product(s) ', $this->text_domain ),
            'placeholder' => __( 'Search for a product&hellip;', $this->text_domain ),
            'options'     => get_option( "{$this->prefix}get_selected_products_of_order_payment_plan", array() ),
        ) ) ;
    }

    /**
     * Custom type field.
     */
    public function get_pay_balance_type() {
        ?>
        <tr class="pay-balance-wrapper">
            <th>
                <?php _e( 'Deposit Balance Payment Due Date', $this->text_domain ) ; ?>
            </th>
            <td>
                <select id="<?php echo "{$this->prefix}order_payment_plan_pay_balance_type" ; ?>" name="pay_balance_type" style="width:95px;">
                    <option value="after" <?php selected( 'after' === get_option( $this->prefix . 'order_payment_plan_pay_balance_type', 'after' ), true ) ; ?>><?php _e( 'After', $this->text_domain ) ; ?></option>
                    <option value="before" <?php selected( 'before' === get_option( $this->prefix . 'order_payment_plan_pay_balance_type', 'after' ), true ) ; ?>><?php _e( 'Before', $this->text_domain ) ; ?></option>
                </select>
                <input id="<?php echo "{$this->prefix}order_payment_plan_pay_balance_after" ; ?>" name="pay_balance_after" type="number" value="<?php echo get_option( $this->prefix . 'order_payment_plan_pay_balance_after' ) ; ?>" style="width:150px;"/>
                <input id="<?php echo "{$this->prefix}order_payment_plan_pay_balance_before" ; ?>" name="pay_balance_before" type="text" placeholder="<?php esc_attr_e( 'YYYY-MM-DD', $this->text_domain ) ?>" value="<?php echo get_option( $this->prefix . 'order_payment_plan_pay_balance_before', '' ) ; ?>" style="width:150px;"/>
            </td>
        </tr>
        <?php
    }

    /**
     * Custom type field.
     */
    public function get_limited_users() {

        _sumo_pp_wc_search_field( array(
            'class'       => 'wc-customer-search',
            'id'          => $this->prefix . 'get_limited_users_of_order_payment_plan',
            'name'        => 'get_limited_users',
            'type'        => 'customer',
            'title'       => __( 'Select User(s)', $this->text_domain ),
            'placeholder' => __( 'Search for a user&hellip;', $this->text_domain ),
            'options'     => ( array ) get_option( $this->prefix . 'get_limited_users_of_order_payment_plan', array() )
        ) ) ;
    }

}

return new SUMO_PP_Order_PaymentPlan_Settings() ;
