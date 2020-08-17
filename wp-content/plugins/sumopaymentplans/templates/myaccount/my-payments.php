<?php
if( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}

global $wp ;
?>
<p style="display:inline-table">
    <?php _e( 'Search:' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?>
    <input id="filter" type="text" style="width: 40%"/>&nbsp;
    <?php _e( 'Page Size:' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?>
    <input id="change-page-size" type="number" min="5" step="5" value="5" style="width: 25%"/>
</p>
<table class="shop_table shop_table_responsive my_account_orders <?php echo SUMO_PP_PLUGIN_PREFIX . 'my-payments' ; ?> <?php echo SUMO_PP_PLUGIN_PREFIX . 'footable' ; ?>" data-filter="#filter" data-page-size="5" data-page-previous-text="prev" data-filter-text-only="true" data-page-next-text="next" style="width:100%">
    <thead>
        <tr>
            <th class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-number' ; ?>"><span class="nobr"><?php _e( 'Payment Number' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></span></th>
            <th class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-product-title' ; ?>"><span class="nobr"><?php _e( 'Product Title' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></span></th>
            <th class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-plan' ; ?>"><span class="nobr"><?php _e( 'Payment Plan' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></span></th>
            <th class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-status' ; ?>"><span class="nobr"><?php _e( 'Payment Status' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></span></th>
            <th data-sort-ignore="true">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach( $payments as $payment_id ) :
            $payment                                  = _sumo_pp_get_payment( $payment_id ) ;
            $wp->query_vars[ 'sumo-pp-view-payment' ] = $payment->id ;
            ?>
            <tr class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-data' ; ?>">
                <td class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-number' ; ?>" data-title="<?php _e( 'Payment Number' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>">
                    <?php
                    echo '<a href="' . $payment->get_view_endpoint_url() . '">#' . $payment->get_payment_number() . '</a>' ;
                    ?>
                </td>
                <td class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-product-title' ; ?>" data-title="<?php _e( 'Product Title' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>">
                    <?php
                    echo $payment->get_formatted_product_name() ;
                    ?>
                </td>
                <td class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-plan' ; ?>" data-title="<?php _e( 'Payment Plan' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>">
                    <?php
                    if( 'payment-plans' === $payment->get_payment_type() ) {
                        echo $payment->get_plan()->post_title ;
                    } else {
                        echo 'N/A' ;
                    }
                    ?>
                </td>
                <td class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-payment-status' ; ?>" data-title="<?php _e( 'Payment Status' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>">
                    <?php
                    if( $payment->has_status( 'await_cancl' ) ) {
                        $payment_statuses = _sumo_pp_get_payment_statuses() ;
                        printf( '<mark class="%s"/>%s</mark>' , SUMO_PP_PLUGIN_PREFIX . 'overdue' , esc_attr( $payment_statuses[ SUMO_PP_PLUGIN_PREFIX . 'overdue' ] ) ) ;
                    } else {
                        printf( '<mark class="%s"/>%s</mark>' , $payment->get_status( true ) , esc_attr( $payment->get_status_label() ) ) ;
                    }
                    ?>
                </td>
                <td class="<?php echo SUMO_PP_PLUGIN_PREFIX . '-view-payment' ; ?>">
                    <a href="<?php echo $payment->get_view_endpoint_url() ; ?>" class="button view" data-action="view"><?php _e( 'View' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></a>
                </td>
            </tr>
        <?php endforeach ; ?>
    </tbody>
</table>
<div class="pagination pagination-centered"></div>
