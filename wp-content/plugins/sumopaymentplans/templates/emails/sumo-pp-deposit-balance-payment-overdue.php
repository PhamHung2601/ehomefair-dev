<?php
if( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header' , $email_heading , $email ) ; ?>

<p><?php printf( __( 'Hi, <br>Your Balance Payment for %s from payment #%s is currently Overdue. <br>Please make the payment using the payment link %s before <strong>%s</strong>. If Payment is not received within <strong>%s</strong>, the order will be <strong>%s</strong>.' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) , $product_title , $payment->get_payment_number() , '<a href="' . $order->get_pay_url() . '">' . __( 'pay' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) . '</a>' , $next_action_on , $next_action_on , $next_action_status ) ; ?></p>

<p><?php _e( 'Thanks' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></p>

<?php do_action( 'woocommerce_email_footer' , $email ) ; ?>