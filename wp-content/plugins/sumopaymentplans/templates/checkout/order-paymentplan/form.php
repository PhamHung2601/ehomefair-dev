<table class="shop_table <?php echo SUMO_PP_PLUGIN_PREFIX . 'orderpp_fields' ; ?>">
    <tr>
        <td>
            <?php if( 'yes' === $option_props[ 'force_deposit' ] ) { ?>
                <input type="checkbox" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'enable_orderpp' ; ?>" value="1" checked="checked" readonly="readonly" onclick="return false ;"/>
            <?php } else { ?>
                <input type="checkbox" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'enable_orderpp' ; ?>" value="1"/>
            <?php } ?>
            <label><?php echo $option_props[ 'labels' ][ 'enable' ] ; ?></label>
        </td>
    </tr>   
    <tr>
        <?php if( 'pay-in-deposit' === $option_props[ 'payment_type' ] ) { ?>
            <td>                        
                <label><?php echo $option_props[ 'labels' ][ 'deposit_amount' ] ; ?></label>
                <input type="hidden" value="pay-in-deposit" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>"/>
            </td>
            <td id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'amount_to_choose' ; ?>">
                <?php if( 'user-defined' === $option_props[ 'deposit_type' ] ) { ?>
                    <?php
                    if( $max_deposit_price ) {
                        printf( __( 'Enter your Deposit Amount between %s and %s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , wc_price( $min_deposit_price ) , wc_price( $max_deposit_price ) ) ;
                        ?>
                        <input type="number" min="<?php echo floatval( $min_deposit_price ) ; ?>" max="<?php echo floatval( $max_deposit_price ) ; ?>" step="0.01" class="input-text" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'deposited_amount' ; ?>"/>
                        <?php
                    } else {
                        printf( __( 'Enter a deposit amount not less than %s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , wc_price( $min_deposit_price ) ) ;
                        ?>
                        <input type="number" min="<?php echo floatval( $min_deposit_price ) ; ?>" step="0.01" class="input-text" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'deposited_amount' ; ?>"/>
                        <?php
                    }
                } else {
                    ?>
                    <?php echo wc_price( $fixed_deposit_price ) ; ?>
                    <input type="hidden" value="<?php echo $fixed_deposit_price ; ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'deposited_amount' ; ?>"/>
                <?php } ?>
            </td>
        <?php } else { ?>
            <td>                       
                <label><?php echo $option_props[ 'labels' ][ 'payment_plans' ] ; ?></label>
                <input type="hidden" value="payment-plans" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'payment_type' ; ?>"/>
            </td>                    
            <td id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'plans_to_choose' ; ?>">
                <?php
                foreach( $option_props[ 'selected_plans' ] as $col => $plan_id ) {
                    $plan_props = _sumo_pp()->plan->get_props( $plan_id ) ;
                    ?>
                    <p>
                        <input type="radio" value="<?php echo $plan_props[ 'plan_id' ] ; ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'chosen_payment_plan' ; ?>" <?php echo 0 === $col ? 'checked="checked"' : '' ?>/>
                        <strong><?php echo $plan_props[ 'plan_name' ] ; ?></strong><br>
                        <?php
                        if( ! empty( $plan_props[ 'plan_description' ] ) ) {
                            echo $plan_props[ 'plan_description' ] ;
                        }
                        ?>
                    </p>  
                    <?php
                }
                ?>
            </td>
        <?php } ?>
    </tr>
</table>