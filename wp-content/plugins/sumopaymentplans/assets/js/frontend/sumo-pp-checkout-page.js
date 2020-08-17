/* global sumo_pp_checkout_page */

jQuery( function( $ ) {
    // sumo_pp_checkout_page is required to continue, ensure the object exists
    if( typeof sumo_pp_checkout_page === 'undefined' ) {
        return false ;
    }

    var $form = $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).closest( 'form' ) ;

    var is_blocked = function( $node ) {
        return $node.is( '.processing' ) || $node.parents( '.processing' ).length ;
    } ;

    /**
     * Block a node visually for processing.
     *
     * @param {JQuery Object} $node
     */
    var block = function( $node ) {
        if( ! is_blocked( $node ) ) {
            $node.addClass( 'processing' ).block( {
                message : null ,
                overlayCSS : {
                    background : '#fff' ,
                    opacity : 0.6
                }
            } ) ;
        }
    } ;

    /**
     * Unblock a node after processing is complete.
     *
     * @param {JQuery Object} $node
     */
    var unblock = function( $node ) {
        $node.removeClass( 'processing' ).unblock() ;
    } ;

    var checkout = {
        /**
         * Manage Checkout page events.
         */
        sync : false ,
        isOrderPPEnabled : false ,
        paymentType : false ,
        depositedAmount : false ,
        paymentPlan : false ,
        init : function() {

            this.onPageLoad() ;

            $( document.body ).on( 'input:checkbox' , '#_sumo_pp_enable_orderpp' , this.preventdefault ) ;
            $( document ).on( 'change' , '#_sumo_pp_enable_orderpp' , this.toggleOrderPPCheckoutStatus ) ;
            $( document ).on( 'change' , '#_sumo_pp_deposited_amount' , this.updateOrderPPDepositedAmount ) ;
            $( document ).on( 'change' , '#_sumo_pp_chosen_payment_plan' , this.updateOrderPPPaymentPlan ) ;
        } ,
        onPageLoad : function() {
            checkout.sync = true ;
            this.getOrderPPCheckoutStatus( $( '#_sumo_pp_enable_orderpp' ).is( ':checked' ) ) ;
        } ,
        preventdefault : function( evt ) {
            evt.preventDefault() ;
        } ,
        toggleOrderPPCheckoutStatus : function( evt ) {
            evt.preventDefault() ;
            checkout.sync = false ;
            checkout.getOrderPPCheckoutStatus( evt.currentTarget.checked ) ;
        } ,
        updateOrderPPDepositedAmount : function( evt ) {
            evt.preventDefault() ;
            checkout.updateCheckout() ;
        } ,
        updateOrderPPPaymentPlan : function( evt ) {
            evt.preventDefault() ;
            checkout.updateCheckout() ;
        } ,
        getOrderPPCheckoutStatus : function( is_checked ) {
            is_checked = is_checked || false ;

            $( 'table._sumo_pp_orderpp_fields tr:eq(1)' ).slideUp() ;

            if( is_checked ) {
                $( 'table._sumo_pp_orderpp_fields tr:eq(1)' ).slideDown() ;
            }
            checkout.updateCheckout() ;
        } ,
        populate : function() {
            checkout.isOrderPPEnabled = $( '#_sumo_pp_enable_orderpp' ).is( ':checked' ) ;
            checkout.paymentType = $( '#_sumo_pp_payment_type' ).val() ;
            checkout.depositedAmount = $( '#_sumo_pp_deposited_amount' ).val() ;
            checkout.paymentPlan = $( '#_sumo_pp_chosen_payment_plan:checked' ).val() ;
        } ,
        updateCheckout : function() {
            checkout.populate() ;

            $.blockUI.defaults.overlayCSS.cursor = 'wait' ;
            block( $form ) ;

            $.ajax( {
                type : 'POST' ,
                url : sumo_pp_checkout_page.wp_ajax_url ,
                dataType : 'text' ,
                async : checkout.sync ? false : true ,
                data : {
                    action : '_sumo_pp_checkout_orderpp' ,
                    security : sumo_pp_checkout_page.orderpp_nonce ,
                    enabled : checkout.isOrderPPEnabled ? 'yes' : 'no' ,
                    payment_type : checkout.paymentType ,
                    deposited_amount : checkout.depositedAmount ,
                    chosen_payment_plan : checkout.paymentPlan ,
                } ,
                success : function() {
                    checkout.forceRenderSignupFormIfGuest() ;
                    $( document.body ).trigger( 'update_checkout' ) ;
                } ,
                complete : function() {
                    unblock( $form ) ;
                    checkout.clearCache() ;
                }
            } ) ;
        } ,
        forceRenderSignupFormIfGuest : function() {

            if( sumo_pp_checkout_page.is_user_logged_in ) {
                return false ;
            }

            if( $( 'p.create-account' ).length ) {
                $( 'p.create-account' ).show() ;
                $( 'div.create-account' ).slideUp() ;
            }

            if( sumo_pp_checkout_page.maybe_prevent_from_hiding_guest_signup_form && sumo_pp_checkout_page.can_user_deposit_payment ) {
                $( 'div.create-account' ).slideUp() ;
            }

            if( checkout.isOrderPPEnabled ) {
                if( $( 'p.create-account' ).length ) {
                    $( 'p.create-account' ).hide() ;
                }
                if( $( 'div.create-account' ).length ) {
                    $( 'div.create-account' ).slideDown() ;
                }
            }
        } ,
        clearCache : function() {
            checkout.sync = false ;
            checkout.isOrderPPEnabled = false ;
            checkout.paymentType = false ;
            checkout.depositedAmount = false ;
            checkout.paymentPlan = false ;
        }
    } ;

    if( sumo_pp_checkout_page.can_user_deposit_payment && $( '._sumo_pp_orderpp_fields' ).length ) {
        checkout.init() ;
    }
} ) ;