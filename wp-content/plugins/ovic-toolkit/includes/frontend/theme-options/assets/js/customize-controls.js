/**
 * Scripts within the customizer controls window.
 *
 * Contextually shows the color hue control and informs the preview
 * when users open or close the front page sections section.
 */

(function () {
    wp.customize.bind('ready', function () {
        /* Options Reset Customizer Field */
        jQuery.fn.ovic_reset_field = function () {
            return this.each(function () {
                var $this  = jQuery(this),
                    $reset = $this.data('field');

                $this.on('click', function (e) {
                    e.preventDefault();
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'ovic-reset-field',
                            reset: $reset,
                        },
                        success: function (content) {
                            location.reload();
                        }
                    });
                });
            });
        };
        jQuery('.reset-field-customizer').ovic_reset_field();
    });
})(jQuery);