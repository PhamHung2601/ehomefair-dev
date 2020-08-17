jQuery( function( $ ) {
    $( '#woocommerce_sumo_pp_stripe_testmode' ).change( function() {
        $( '#woocommerce_sumo_pp_stripe_testsecretkey' ).closest( 'tr' ).hide() ;
        $( '#woocommerce_sumo_pp_stripe_testpublishablekey' ).closest( 'tr' ).hide() ;
        $( '#woocommerce_sumo_pp_stripe_livesecretkey' ).closest( 'tr' ).show() ;
        $( '#woocommerce_sumo_pp_stripe_livepublishablekey' ).closest( 'tr' ).show() ;

        if( this.checked ) {
            $( '#woocommerce_sumo_pp_stripe_testsecretkey' ).closest( 'tr' ).show() ;
            $( '#woocommerce_sumo_pp_stripe_testpublishablekey' ).closest( 'tr' ).show() ;
            $( '#woocommerce_sumo_pp_stripe_livesecretkey' ).closest( 'tr' ).hide() ;
            $( '#woocommerce_sumo_pp_stripe_livepublishablekey' ).closest( 'tr' ).hide() ;
        }
    } ).change() ;

    $( '#woocommerce_sumo_pp_stripe_saved_cards' ).change( function() {
        $( '#woocommerce_sumo_pp_stripe_retryDefaultPM' ).closest( 'tr' ).hide() ;

        if( this.checked ) {
            $( '#woocommerce_sumo_pp_stripe_retryDefaultPM' ).closest( 'tr' ).show() ;
        }
    } ).change() ;
} ) ;