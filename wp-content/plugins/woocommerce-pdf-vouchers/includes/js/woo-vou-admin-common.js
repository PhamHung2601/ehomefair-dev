"use strict";

jQuery(document).ready( function($){

	if( $('#woo-voo-order-complete-notice-dismiss').length){
		var setCookie = function ( c_name, value, exdays ) {
                    var exdate = new Date();
                    exdate.setDate( exdate.getDate() + exdays );
                    var c_value = encodeURIComponent( value ) + ((null === exdays) ? "" : "; expires=" + exdate.toUTCString());
                    document.cookie = c_name + "=" + c_value;
                };
        $( document ).on( 'click.woo-voo-order-complete-notice-dismiss',
            '.woo-voo-order-complete-notice-dismiss',
        	function ( e ) {
	            e.preventDefault();
	            var $el = $( this ).closest('#woo-voo-order-complete-notice-dismiss' );
	            $el.fadeTo( 100, 0, function () {
	                $el.slideUp( 100, function () {
	                    $el.remove();
	                } );
	            } );

	            var data = {
                            action  : 'woo_vou_dismiss_order_complete',
                            'process': 'woo_voo_order_complete'
                        };
                // call ajax to set dismiss data
                jQuery.get( WooVouCom.ajaxurl, data, function( response ) {

                });
        } );
	}

	if( $('#woo-voo-order-cancel-notice-dismiss').length) {
		
		var setCookie = function ( c_name, value, exdays ) {
            var exdate = new Date();
            exdate.setDate( exdate.getDate() + exdays );
            var c_value = encodeURIComponent( value ) + ((null === exdays) ? "" : "; expires=" + exdate.toUTCString());
            document.cookie = c_name + "=" + c_value;
        };
        
        $( document ).on( 'click.woo-voo-order-cancel-notice-dismiss',
            '.woo-voo-order-cancel-notice-dismiss',
            function ( e ) {
                e.preventDefault();
                var $el = $( this ).closest('#woo-voo-order-cancel-notice-dismiss' );
                $el.fadeTo( 100, 0, function () {
                    $el.slideUp( 100, function () {
                        $el.remove();
                    } );
                } );
                
                var data = {
                            action  : 'woo_vou_dismiss_order_cancelled',
                            'process': 'woo_voo_order_cancelled'
                        };
                // call ajax to set dismiss data
                jQuery.get( WooVouCom.ajaxurl, data, function( response ) {

                });
        } );
	}
	
	
	
});