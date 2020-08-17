<?php
if( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header' , $email_heading , $email ) ; ?>

<p>
    <?php
    printf( __( 'Your payment #%s is in Pending Authorization status because we couldn\'t charge your account for future payment as your bank have declined the authorization which you have previously given. Please pay using another card or else using any other payment gateway <a href="%s">pay</a>. If payment is not made for the future payment by <strong>%s</strong>, the Payment will be <strong>%s</strong>.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $payment->get_payment_number() , $order->get_pay_url() , $next_action_on , $next_action_status ) ;
    ?>
</p>

<?php do_action( 'woocommerce_email_before_order_table' , $order->order , $sent_to_admin , $plain_text , $email ) ; ?>

<h2><?php printf( __( 'Payment #%s' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $payment->get_payment_number() ) ; ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <thead>
        <tr>
            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Product' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></th>
            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Quantity' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></th>
            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php echo $order->get_email_order_items_table() ; ?>
    </tbody>
    <tfoot>
        <?php echo $order->get_email_order_item_totals() ; ?>
    </tfoot>
</table>

<?php do_action( 'woocommerce_email_footer' , $email ) ; ?>