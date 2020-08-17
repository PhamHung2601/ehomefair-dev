/* global sumo_pp_stripe */

jQuery( function( $ ) {
    'use strict' ;

    // sumo_pp_stripe is required to continue, ensure the object exists
    if ( typeof sumo_pp_stripe === 'undefined' ) {
        return false ;
    }

    var sumo_stripe = {
        stripeClient : null,
        stripeElements : null,
        stripeCard : null,
        stripeExp : null,
        stripeCVC : null,
        styles : {
            base : {
                color : '#32325d',
                lineHeight : '18px',
                fontFamily : '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing : 'antialiased',
                fontSize : '16px',
                '::placeholder' : {
                    color : '#aab7c4'
                }
            },
            invalid : {
                color : '#fa755a',
                iconColor : '#fa755a'
            }
        },
        init : function() {

            if ( $( 'form#order_review' ).length ) {
                this.form = $( 'form#order_review' ) ;
            } else if ( $( 'form#add_payment_method' ).length ) {
                this.form = $( 'form#add_payment_method' ) ;
            } else {
                this.form = $( 'form.checkout' ) ;
            }

            if ( 0 === this.form.length ) {
                return false ;
            }

            this.initElements() ;

            if ( $( 'form#order_review' ).length || $( 'form#add_payment_method' ).length ) {
                $( document.body ).on( 'payment_method_selected', this.maybeToggleSavedPm ) ;
                this.form.on( 'submit', this.createPaymentMethod ) ;
                this.mayBeMountElements() ;
                this.onVerifyIntentHash() ;
            } else {
                this.form.on( 'checkout_place_order', this.createPaymentMethod ) ;
                $( document.body ).on( 'updated_checkout', this.mayBeMountElements ) ;
                window.addEventListener( 'hashchange', this.onVerifyIntentHash ) ;
            }

            $( document.body ).on( 'checkout_error', this.onCheckoutErr ) ;
        },
        isStripeChosen : function() {
            if ( sumo_pp_stripe.payment_method === $( '.payment_methods input[name="payment_method"]:checked' ).val() ) {
                return true ;
            }
            return false ;
        },
        initElements : function() {
            if ( 'inline_cc_form' === sumo_pp_stripe.checkoutmode ) {
                sumo_stripe.stripeCard = sumo_stripe.stripeElements.create( 'card', { style : sumo_stripe.styles, hidePostalCode : true } ) ;

                sumo_stripe.stripeCard.addEventListener( 'change', sumo_stripe.onChangeElements ) ;
            } else {
                sumo_stripe.stripeCard = sumo_stripe.stripeElements.create( 'cardNumber', { style : sumo_stripe.styles } ) ;
                sumo_stripe.stripeExp = sumo_stripe.stripeElements.create( 'cardExpiry', { style : sumo_stripe.styles } ) ;
                sumo_stripe.stripeCVC = sumo_stripe.stripeElements.create( 'cardCvc', { style : sumo_stripe.styles } ) ;

                sumo_stripe.stripeCard.addEventListener( 'change', sumo_stripe.onChangeElements ) ;
                sumo_stripe.stripeExp.addEventListener( 'change', sumo_stripe.onChangeElements ) ;
                sumo_stripe.stripeCVC.addEventListener( 'change', sumo_stripe.onChangeElements ) ;
            }
        },
        onChangeElements : function( event ) {
            sumo_stripe.reset() ;

            if ( event.brand ) {
                sumo_stripe.updateCardBrand( event.brand ) ;
            }

            if ( event.error ) {
                sumo_stripe.throwErr( event.error ) ;
            }
        },
        mayBeMountElements : function() {
            if ( sumo_stripe.stripeCard ) {
                sumo_stripe.unmountElements() ;

                if ( $( '#wc-sumo_pp_stripe-cc-form' ).length ) {
                    sumo_stripe.mountElements() ;
                }
            }
        },
        maybeToggleSavedPm : function() {
            // Loop over gateways with saved payment methods
            var $saved_payment_methods = $( 'ul.woocommerce-SavedPaymentMethods' ) ;

            $saved_payment_methods.each( function() {
                $( this ).wc_tokenization_form() ;
            } ) ;
        },
        updateCardBrand : function( brand ) {
            var brandClass = {
                'visa' : 'sumo-pp-stripe-visa-brand',
                'mastercard' : 'sumo-pp-stripe-mastercard-brand',
                'amex' : 'sumo-pp-stripe-amex-brand',
                'discover' : 'sumo-pp-stripe-discover-brand',
                'diners' : 'sumo-pp-stripe-diners-brand',
                'jcb' : 'sumo-pp-stripe-jcb-brand',
                'unknown' : 'sumo-pp-stripe-credit-card-brand'
            } ;

            var imageElement = $( '.sumo-pp-stripe-card-brand' ),
                    imageClass = 'sumo-pp-stripe-credit-card-brand' ;

            if ( brand in brandClass ) {
                imageClass = brandClass[ brand ] ;
            }

            $.each( brandClass, function( i, el ) {
                imageElement.removeClass( el ) ;
            } ) ;

            imageElement.addClass( imageClass ) ;
        },
        mountElements : function() {
            if ( 'inline_cc_form' === sumo_pp_stripe.checkoutmode ) {
                sumo_stripe.stripeCard.mount( '#sumo-pp-stripe-card-element' ) ;
            } else {
                sumo_stripe.stripeCard.mount( '#sumo-pp-stripe-card-element' ) ;
                sumo_stripe.stripeExp.mount( '#sumo-pp-stripe-exp-element' ) ;
                sumo_stripe.stripeCVC.mount( '#sumo-pp-stripe-cvc-element' ) ;
            }
        },
        unmountElements : function() {
            if ( 'inline_cc_form' === sumo_pp_stripe.checkoutmode ) {
                sumo_stripe.stripeCard.unmount( '#sumo-pp-stripe-card-element' ) ;
            } else {
                sumo_stripe.stripeCard.unmount( '#sumo-pp-stripe-card-element' ) ;
                sumo_stripe.stripeExp.unmount( '#sumo-pp-stripe-exp-element' ) ;
                sumo_stripe.stripeCVC.unmount( '#sumo-pp-stripe-cvc-element' ) ;
            }
        },
        hasPaymentMethod : function() {
            return sumo_stripe.form.find( 'input[name="sumo_pp_stripe_pm"]' ).length > 0 ? true : false ;
        },
        savedPaymentMethodChosen : function() {
            return $( '#payment_method_sumo_pp_stripe' ).is( ':checked' )
                    && $( 'input[name="wc-sumo_pp_stripe-payment-token"]' ).is( ':checked' )
                    && 'new' !== $( 'input[name="wc-sumo_pp_stripe-payment-token"]:checked' ).val() ;
        },
        createPaymentMethod : function() {

            if ( ! sumo_stripe.isStripeChosen() ) {
                sumo_stripe.reset() ;
                return true ;
            }

            if ( sumo_stripe.savedPaymentMethodChosen() ) {
                sumo_stripe.reset() ;
                return true ;
            }

            sumo_stripe.reset( 'no' ) ;

            if ( sumo_stripe.hasPaymentMethod() ) {
                return true ;
            }

            sumo_stripe.reset() ;
            sumo_stripe.blockFormOnSubmit() ;
            sumo_stripe.stripeClient.createPaymentMethod( 'card', sumo_stripe.stripeCard ).then( sumo_stripe.handlePaymentMethodResponse ) ;
            return false ;
        },
        handlePaymentMethodResponse : function( response ) {
            if ( response.error ) {
                sumo_stripe.throwErr( response.error ) ;
            } else {
                sumo_stripe.form.append( '<input type="hidden" class="sumo-pp-stripe-paymentMethod" name="sumo_pp_stripe_pm" value="' + response.paymentMethod.id + '"/>' ) ;
                sumo_stripe.form.submit() ;
            }
        },
        onVerifyIntentHash : function() {
            var hash = window.location.hash.match( /^#?confirm-sumo-stripe-intent-([^:]+):(.+):(.+):(.+)$/ ) ;

            if ( ! hash || 5 > hash.length ) {
                return ;
            }

            var intentClientSecret = hash[1],
                    intentObj = hash[2],
                    endpoint = hash[3],
                    redirectURL = decodeURIComponent( hash[4] ) ;

            //Allow only when the endpoint contains either 'checkout' or 'pay-for-order' or 'add-payment-method'
            if ( 'checkout' !== endpoint && 'pay-for-order' !== endpoint && 'add-payment-method' !== endpoint ) {
                return ;
            }

            sumo_stripe.blockFormOnSubmit() ;
            window.location.hash = '' ;

            if ( 'setup_intent' === intentObj ) {
                sumo_stripe.onConfirmSi( intentClientSecret, redirectURL, endpoint ) ;
            } else if ( 'payment_intent' === intentObj ) {
                sumo_stripe.onConfirmPi( intentClientSecret, redirectURL, endpoint ) ;
            }
            return ;
        },
        onConfirmSi : function( intentClientSecret, redirectURL, endpoint ) {

            sumo_stripe.stripeClient.handleCardSetup( intentClientSecret )
                    .then( function( response ) {
                        if ( response.error ) {
                            throw response.error ;
                        }

                        //Allow only when the Intent succeeded 
                        if ( ! response.setupIntent || 'succeeded' !== response.setupIntent.status ) {
                            return ;
                        }

                        window.location = redirectURL ;
                    } )
                    .catch( function( error ) {
                        sumo_stripe.reset() ;

                        if ( 'pay-for-order' === endpoint || 'add-payment-method' === endpoint ) {
                            return window.location = redirectURL ;
                        }

                        sumo_stripe.throwErr( error ) ;

                        // Report back to the server.
                        $.get( redirectURL + '&is_ajax' ) ;
                    } ) ;
        },
        onConfirmPi : function( intentClientSecret, redirectURL, endpoint ) {

            sumo_stripe.stripeClient.handleCardPayment( intentClientSecret )
                    .then( function( response ) {
                        if ( response.error ) {
                            throw response.error ;
                        }

                        //Allow only when the Intent succeeded 
                        if ( ! response.paymentIntent || 'succeeded' !== response.paymentIntent.status ) {
                            return ;
                        }

                        window.location = redirectURL ;
                    } )
                    .catch( function( error ) {
                        sumo_stripe.reset() ;

                        if ( 'pay-for-order' === endpoint || 'add-payment-method' === endpoint ) {
                            return window.location = redirectURL ;
                        }

                        sumo_stripe.throwErr( error ) ;

                        // Report back to the server.
                        $.get( redirectURL + '&is_ajax' ) ;
                    } ) ;
        },
        onCheckoutErr : function() {
            sumo_stripe.reset( 'yes', 'no' ) ;
        },
        blockFormOnSubmit : function() {
            if ( ! sumo_stripe.form ) {
                return ;
            }

            sumo_stripe.form.block( {
                message : null,
                overlayCSS : {
                    background : '#fff',
                    opacity : 0.6
                }
            } ) ;
        },
        throwErr : function( error ) {
            sumo_stripe.reset() ;

            if ( error.message ) {
                if ( $( '.woocommerce-SavedPaymentMethods' ).length ) {
                    var $selected_saved_pm = $( 'input[name="wc-sumo_pp_stripe-payment-token"]' ).filter( ':checked' ).closest( '.woocommerce-SavedPaymentMethods-token' ) ;

                    if ( $selected_saved_pm.length && $selected_saved_pm.find( '.sumo-pp-stripe-card-errors' ).length ) {
                        $selected_saved_pm.find( '.sumo-pp-stripe-card-errors' ).html( '<ul class="woocommerce_error woocommerce-error"><li /></ul>' ) ;
                        $selected_saved_pm.find( '.sumo-pp-stripe-card-errors' ).find( 'li' ).text( error.message ) ;
                    } else {
                        $( '#wc-sumo_pp_stripe-cc-form' ).find( '.sumo-pp-stripe-card-errors' ).html( '<ul class="woocommerce_error woocommerce-error"><li /></ul>' ) ;
                        $( '#wc-sumo_pp_stripe-cc-form' ).find( '.sumo-pp-stripe-card-errors' ).find( 'li' ).text( error.message ) ;
                    }
                } else {
                    $( '.sumo-pp-stripe-card-errors' ).html( '<ul class="woocommerce_error woocommerce-error"><li /></ul>' ) ;
                    $( '.sumo-pp-stripe-card-errors' ).find( 'li' ).text( error.message ) ;
                }
            }

            if ( $( '.sumo-pp-stripe-card-errors' ).length ) {
                $( 'html, body' ).animate( {
                    scrollTop : ( $( '.sumo-pp-stripe-card-errors' ).offset().top - 200 )
                }, 200 ) ;
            }

            if ( sumo_stripe.form ) {
                sumo_stripe.form.removeClass( 'processing' ) ;
                sumo_stripe.form.unblock() ;
            }
        },
        reset : function( remove_pm, remove_notices ) {
            remove_pm = remove_pm || 'yes' ;
            remove_notices = remove_notices || 'yes' ;

            $( '.sumo-pp-stripe-card-errors' ).text( '' ) ;

            if ( 'yes' === remove_pm && 'no' === remove_notices ) {
                $( 'input.sumo-pp-stripe-paymentMethod' ).remove() ;
            } else if ( 'no' === remove_pm && 'yes' === remove_notices ) {
                $( 'div.woocommerce-notices-wrapper, div.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove() ;
            } else {
                $( 'input.sumo-pp-stripe-paymentMethod, div.woocommerce-notices-wrapper, div.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove() ;
            }
        },
    } ;

    try {
        // Create a Stripe client.
        sumo_stripe.stripeClient = Stripe( sumo_pp_stripe.key ) ;
        // Create an instance of Elements.
        sumo_stripe.stripeElements = sumo_stripe.stripeClient.elements() ;
        // Init
        sumo_stripe.init() ;
    } catch ( error ) {
        console.log( error ) ;
        return false ;
    }
} ) ;
