<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = array();

$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. ', 'marketplace' ) . '<code>10.00 * [qty]</code>.<br/><br/>' . __( 'Use ', 'marketplace' ) . '<code>[qty]</code>' . __( ' for the number of items, ', 'marketplace' ) . '<br/><code>[cost]</code>' . __( ' for the total cost of items, and ', 'marketplace' ) . '<code>[fee percent="10" min_fee="20" max_fee=""]</code>' . __( ' for percentage based fees.', 'marketplace' );

if ( is_admin() ) {
	$settings = array_merge( $settings, array(
		'title' => array(
	    'title' 		=> __( 'Marketplace Flat Rate Shipping', 'marketplace' ),
	    'type' 			=> 'text',
	    'description' 	=> __( 'This controls the title which the user sees during checkout.', 'marketplace' ),
	    'default'		=> __( 'Marketplace Flat Rate Shipping', 'marketplace' ),

	  ),
	) );
}

$settings = array_merge( $settings, array(
  'enabled' => array(
    'title' 		=> __( 'Enable/Disable', 'marketplace' ),
    'type' 			=> 'checkbox',
    'label' 		=> __( 'Enable WooCommerce Marketplace Flat Rate Shipping', 'marketplace' ),
    'default' 		=> 'yes'
  ),
  'tax_status' => array(
    'title' 		=> __( 'Tax status', 'marketplace' ),
    'type' 			=> 'select',
    'class'         => 'wc-enhanced-select',
    'default' 		=> 'taxable',
    'options'		=> array(
      'taxable' 	=> __( 'Taxable', 'marketplace' ),
      'none' 		=> _x( 'None', 'Tax status', 'marketplace' ),
    ),
  ),
  'cost' => array(
    'title' 		=> __( 'Cost', 'marketplace' ),
    'type' 			=> 'text',
    'placeholder'	=> '',
    'description'	=> $cost_desc,
    'default'		=> '0',
    'desc_tip'		=> true,
  ),
) );

return $settings;
