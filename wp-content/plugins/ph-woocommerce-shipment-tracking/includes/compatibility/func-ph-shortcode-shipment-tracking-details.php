<?php

//compatibility with WP HTML Mail - WooCommerce plugin
if( ! function_exists('ph_add_tracking_info_to_email_through_shortcode') ) {
    function ph_add_tracking_info_to_email_through_shortcode( $atts, $content = null, $tag = '' ) {
        $order_id = $atts['order_id'];
        $order = wc_get_order( $order_id );

        $shipment_result_array 	= get_post_meta( $order->get_id() , WF_Tracking_Admin::SHIPMENT_RESULT_KEY, true );
        if( !empty( $shipment_result_array ) ) {
            
            if ( ! class_exists( 'WF_Tracking_Admin' ) ) {
                include_once ( plugin_dir_url( __FILE__ ) . '/includes/class-wf-tracking-admin.php' );
            }
            $tracking_admin_obj = new WF_Tracking_Admin();
            
            $shipping_title = apply_filters('wf_shipment_tracking_email_shipping_title', __( 'Shipping Detail', 'woocommerce-shipment-tracking' ) ,$order->get_id());
            $display = '<h3>'.$shipping_title.'</h3>';


            $shipment_source_data 	= $tracking_admin_obj->get_shipment_source_data( $order->get_id() );
            $order_notice 	= Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
            $display .= '<p>'.$order_notice.'</p></br>';

            $order_shipment_description = Ph_Shipment_Tracking_Util::get_shipment_description_as_message( $shipment_source_data );
            if( ! empty($order_shipment_description) ) {
                $display .= "<br>".$order_shipment_description."<br>";
            }
            return $display;
        }
    }
}

// To get shipment tracking details outside in shortcode
add_shortcode( 'PLUGINHIVE_TRACKING_DETAILS' , 'ph_add_tracking_info_to_email_through_shortcode' );