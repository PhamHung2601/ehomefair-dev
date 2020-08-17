(function ($) {
    "use strict";
    
    function str2bytes(str) {
        var bytes = new Uint8Array(str.length);
        for ( var i = 0; i < str.length; i++ ) {
            bytes[ i ] = str.charCodeAt(i);
        }
        return bytes;
    }
    
    $(document).on('click', '.update-guid-attachment', function (e) {
        e.preventDefault();
        
        var $this    = $(this),
            $content = $this.closest('.export-demo'),
            $alert   = $content.find('.alert-export'),
            $spinner = $content.find('.spinner');
        
        $spinner.addClass('is-active');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'ovic_guid_attachment',
            },
            success: function (response, status, request) {
                if ( status == 'success' && response.success == 'yes' ) {
                    if ( response.message != '' ) {
                        $alert.html('<div class="notice notice-success is-dismissible" style="margin: 5px 0 10px;"><p>' + response.message + '</p></div>');
                    }
                }
                $spinner.removeClass('is-active');
                setTimeout(function () {
                    $alert.html('');
                }, 1000);
            },
            ajaxError: function (response, status) {
                $alert.html('<div class="notice notice-error is-dismissible" style="margin: 5px 0 10px;"><p>Error</p></div>');
                $spinner.removeClass('is-active');
            }
        });
    });
    
    $(document).on('click', '.create-export-data', function (e) {
        e.preventDefault();
        
        var $this     = $(this),
            $content  = $this.closest('.export-demo'),
            $alert    = $content.find('.alert-export'),
            $spinner  = $content.find('.spinner'),
            $key      = $content.find('.theme-option').val(),
            $download = $content.find('.download-export');
        
        if ( $download.is(':checked') ) {
            $download = 'yes';
        } else {
            $download = 'no';
        }
        $spinner.addClass('is-active');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'ovic_export_data',
                key: $key,
                plugins: ovic_export_params.plugins,
                download: $download,
            },
            success: function (response, status, request) {
                if ( status == 'success' && response.success == 'yes' ) {
                    if ( response.message != '' ) {
                        $alert.html('<div class="notice notice-success is-dismissible" style="margin: 5px 0 10px;"><p>' + response.message + '</p></div>');
                    }
                    if ( $download === 'yes' ) {
                        window.location.href = response.redirect;
                    } else {
                        location.reload();
                    }
                }
                $spinner.removeClass('is-active');
            },
            ajaxError: function (response, status) {
                $alert.html('<div class="notice notice-error is-dismissible" style="margin: 5px 0 10px;"><p>Error</p></div>');
                $spinner.removeClass('is-active');
            }
        });
    });
    
})(jQuery, window, document);