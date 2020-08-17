<td>
    <input type="radio" value="<?php echo $plan_props[ 'plan_id' ] ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' ; ?>" <?php echo $is_default ? 'checked="checked"' : '' ?>/>
    <?php if( 'yes' === $display_add_to_cart_plan_link ) { ?>
        <a href="<?php echo esc_url_raw( add_query_arg( array( SUMO_PP_PLUGIN_PREFIX . 'payment_type' => 'payment-plans' , SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' => $plan_props[ 'plan_id' ] ) , $product->add_to_cart_url() ) ) ; ?>"><?php echo $plan_props[ 'plan_name' ] ; ?></a>
        <?php
        if( 'product' !== $added_to_cart_plan_redirect_to && ! empty( $_GET[ SUMO_PP_PLUGIN_PREFIX . 'payment_type' ] ) && 'payment-plans' === $_GET[ SUMO_PP_PLUGIN_PREFIX . 'payment_type' ] ) {
            wp_safe_redirect( wc_get_page_permalink( $added_to_cart_plan_redirect_to ) ) ;
            exit ;
        }
        ?>
    <?php } else { ?>
        <strong><?php echo $plan_props[ 'plan_name' ] ; ?></strong>
    <?php } ?>
    <p class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'initial_payable' ; ?>">
        <?php
        if( 'fixed-price' === $plan_props[ 'plan_price_type' ] ) {
            $initial_payable = floatval( $plan_props[ 'initial_payment' ] ) ;
        } else {
            $initial_payable = ($product_props[ 'product_price' ] * floatval( $plan_props[ 'initial_payment' ] )) / 100 ;
        }
        ?>
        <?php printf( __( '<strong>Initial Payable:</strong> %s<br>' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , wc_price( $initial_payable ) ) ; ?>
    </p>    
    <p class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'total_payable' ; ?>">
        <?php printf( __( '<strong>Total Payable:</strong> %s<br>' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , wc_price( $total_payable ) ) ; ?>
    </p>
    <?php
    do_action( 'sumopaymentplans_after_total_payable_html' , $product_props , $plan_props ) ;
    
    if( ! empty( $plan_props[ 'plan_description' ] ) ) {
        ?>
        <p class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'plan_description' ; ?>">
            <?php echo $plan_props[ 'plan_description' ] ; ?>
        </p>
        <?php
    }

    if( 'after' === $plan_props[ 'pay_balance_type' ] && 'after_admin_approval' === $activate_payments ) {
        //Do not display plan information since scheduled date is not available for this plan.
    } else if( ! empty( $plan_props[ 'payment_schedules' ] ) ) {
        ?>
        <div class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'plan_view_more' ; ?>">
            <p>
                <a href="#"><?php _e( 'View more' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></a>
            </p>
            <?php
            _sumo_pp_get_template( 'single-product/payment-plans/view-more.php' , array(
                'product_props' => $product_props ,
                'plan_props'    => $plan_props ,
            ) ) ;
            ?>
        </div>
        <?php
    }
    ?>
</td>