<div class="<?php echo $class ; ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type_fields' ; ?>" <?php echo $hide_if_variation ? 'style="display:none;"' : '' ; ?>>
    <p>
        <?php if( 'yes' !== $product_props[ 'force_deposit' ] ) { ?>
            <input type="radio" value="pay_in_full" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>" checked="checked"/>
            <?php echo $pay_in_full_label ; ?>
            <input type="radio" value="payment-plans" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>"/>
            <?php echo $pay_with_payment_plans_label ; ?>
        <?php } else { ?>
            <?php if( apply_filters( 'sumopaymentplans_show_option_for_forced_payment_plan_label' , true , $product_props ) ) { ?>
                <input type="radio" value="payment-plans" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>" checked="checked"/>
            <?php } else { ?>
                <input type="hidden" value="payment-plans" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>"/>
            <?php } ?>
            <?php if( apply_filters( 'sumopaymentplans_show_forced_payment_plan_label' , true , $product_props ) ) { ?>
                <?php echo $pay_with_payment_plans_label ; ?>
            <?php } ?>
        <?php } ?>
    </p>                    
    <div id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'plans_to_choose' ; ?>" <?php echo 'yes' === $product_props[ 'force_deposit' ] ? '' : 'style="display: none;"' ; ?>>
        <?php
        ksort( $product_props[ 'selected_plans' ] ) ;
        $plan_columns = array( 'col_1' , 'col_2' ) ;
        $plans        = array() ;

        if( ! empty( $product_props[ 'selected_plans' ][ $plan_columns[ 0 ] ] ) ) {
            $plan_size     = array_map( 'sizeof' , $product_props[ 'selected_plans' ] ) ;
            $max_plan_size = max( $plan_size ) ;

            for( $i = 0 ; $i < $max_plan_size ; $i ++ ) {
                foreach( $plan_columns as $column ) {
                    if( ! empty( $product_props[ 'selected_plans' ][ $column ][ $i ] ) ) {
                        $plans[ $i ][] = $product_props[ 'selected_plans' ][ $column ][ $i ] ;
                    }
                }
            }
        }

        if( empty( $plans ) ) {
            $plans = $product_props[ 'selected_plans' ] ;
        }

        if( ! empty( $plans ) ) {
            _sumo_pp_get_template( 'single-product/payment-plans/plans.php' , array(
                'product_props'                  => $product_props ,
                'plans'                          => $plans ,
                'product'                        => $product ,
                'activate_payments'              => $activate_payments ,
                'display_add_to_cart_plan_link'  => $display_add_to_cart_plan_link ,
                'added_to_cart_plan_redirect_to' => $added_to_cart_plan_redirect_to ,
            ) ) ;
        }
        ?>
    </div>
</div>