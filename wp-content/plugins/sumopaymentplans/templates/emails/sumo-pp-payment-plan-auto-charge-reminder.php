<?php
if( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header' , $email_heading , $email ) ; ?>

<?php if( $order->has_status( 'pending' ) ) : ?>

    <p><?php printf( __( 'Hi, <br>This is to remind you that %s for your payment #%s will be automatically charged on <b>%s</b> because you have already preapproved for automatic charging. <br>Kindly make sure you have sufficient funds in your account.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $product_title_with_installment , $payment->get_payment_number() , _sumo_pp_get_date_to_display( $payment->get_prop( 'next_payment_date' ) ) ) ; ?></p>

<?php endif ; ?>

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