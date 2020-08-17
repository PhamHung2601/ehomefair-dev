jQuery( function( $ ) {

    $( '#woocommerce_sumo_pp_stripe_testsecretkey' ).closest( 'tr' ).hide() ;
    $( '#woocommerce_sumo_pp_stripe_testpublishablekey' ).closest( 'tr' ).hide() ;
    $( '#woocommerce_sumo_pp_stripe_livesecretkey' ).closest( 'tr' ).show() ;
    $( '#woocommerce_sumo_pp_stripe_livepublishablekey' ).closest( 'tr' ).show() ;

    if( $( '#woocommerce_sumo_pp_stripe_testmode' ).is( ':checked' ) ) {
        $( '#woocommerce_sumo_pp_stripe_testsecretkey' ).closest( 'tr' ).show() ;
        $( '#woocommerce_sumo_pp_stripe_testpublishablekey' ).closest( 'tr' ).show() ;
        $( '#woocommerce_sumo_pp_stripe_livesecretkey' ).closest( 'tr' ).hide() ;
        $( '#woocommerce_sumo_pp_stripe_livepublishablekey' ).closest( 'tr' ).hide() ;
    }

    $( document ).on( 'change' , '#woocommerce_sumo_pp_stripe_testmode' , function() {
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
    } ) ;
} ) ;