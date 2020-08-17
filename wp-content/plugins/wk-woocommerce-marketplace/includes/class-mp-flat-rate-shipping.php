<?php

class MP_FLAT_RATE_SHIPPING
{

	function __construct() {
	 	add_filter( 'woocommerce_shipping_methods',array($this,'add_mp_flat_rate_shipping_method'));
	 	add_action( 'woocommerce_shipping_init',array($this,'mp_flat_rate_shipping_method_init') );

	}


	function add_mp_flat_rate_shipping_method( $methods ) {

	   $methods['mp_flat_rate'] = 'MP_FLAT_RATE_SHIPPING_METHOD';

	   return $methods;

	}

	function mp_flat_rate_shipping_method_init() {
	   require_once WK_MARKETPLACE_DIR . 'includes/shipping/mp-flat-rate/class-mp-flat-rate-shipping-method.php';
	 }

}

new MP_FLAT_RATE_SHIPPING();
