(function ($) {
    "use strict";
    
    $.fn.ajax_call = function (callback) {
        var $this    = $(this),
            $form    = $this.data(),
            $wrapper = $this.closest('.wrapper-button'),
            $content = $this.closest('.content-import'),
            $spinner = $wrapper.find('.spinner');
        
        $wrapper.find('.spinner').addClass('is-active');
        
        if ( $('#import-attachments').length > 0 ) {
            if ( $('#import-attachments').is(':checked') ) {
                $form.image = 1;
            } else {
                delete $form[ 'image' ];
            }
        }
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'ovic_import_content',
                form: $form
            },
            complete: function (jqXHR, textStatus) {
                if ( $this.hasClass('full-content') ) {
                    $this.remove();
                    $spinner.remove();
                    $content.find('.import-advanced').slideDown(400);
                } else {
                    $spinner.removeClass('is-active');
                }
                if ( $this.hasClass('reset') ) {
                    location.reload();
                } else {
                    $content.addClass('done-import');
                }
                if ( $this.prev('.spinner').length ) {
                    $this.prev('.spinner').removeClass('is-active');
                }
                if ( callback != false ) {
                    callback.ajax_call(false);
                }
            }
        });
        
        return false;
    };
    
    $(document).on('click', '.content-import .ovic-button-import', function (e) {
        e.preventDefault();
        var $confirm  = true,
            $this     = $(this),
            $importer = $this.closest('.ovic-importer-wrapper'),
            $primary  = $importer.find('.header .content-import'),
            $button   = $primary.find('.ovic-button-import.full-content');
        
        $confirm = confirm('Waring: This Button is run "ONE TIME", "Careful Duplicate Content" Button will be remove after done and you can use option import below. Are You sure!');
        
        if ( $confirm == true ) {
            if ( $this.prev('.spinner').length )
                $this.prev('.spinner').addClass('is-active');
            
            if ( !$this.hasClass('full-content') && !$primary.hasClass('done-import') && $this.data('content') != 1 ) {
                $('#import-attachments').attr('checked', true);
                $button.ajax_call($this);
            } else {
                $this.ajax_call(false);
            }
        }
    });
    $(document).on('click', '.ovic-importer-wrapper .toggle-adv', function (e) {
        e.preventDefault();
        var $this    = $(this),
            $content = $this.closest('.content-import');
        
        $content.find('.import-advanced').slideToggle(400);
    });
    $( document ).on( 'click', '#tabs-container .nav-tab-wrapper .nav-tab', function ( e ) {
        e.preventDefault();
        var $button      = $( this ),
            $newurl      = $button.attr( 'href' ),
            $contain     = $button.closest( '#tabs-container' ),
            $tab_content = $contain.find( $button.data( 'tab' ) );

        $button.addClass( 'nav-tab-active' ).siblings().removeClass( 'nav-tab-active' );
        $tab_content.addClass( 'active' ).siblings().removeClass( 'active' );
        window.history.pushState( { path: $newurl }, '', $newurl );
    } );
    
})(jQuery, window, document);