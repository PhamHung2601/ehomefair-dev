"use strict";

jQuery( document ).ready( function( $ ) { 
    
	// hide/show redeem amount on change of redeem method
    $( document ).on( 'change', '#vou_redeem_method',  function() {
    	
    	// get selected redeem method value
    	var redeem_method = $( this ).val();
    	if( redeem_method == 'partial' ) {
    		$('.woo-vou-partial-redeem-amount').fadeIn();
    	} else {
    		$('.woo-vou-partial-redeem-amount').fadeOut	();
    	}    	
    });

    
 });