jQuery(document).ready(function ($) {
    $.fn.ovic_preview_select = function () {
        var _this = $(this);
        _this.on('ovic_preview_select', function () {
            _this.each(function () {
                var url = jQuery(this).find(':selected').data('preview');
                $(this).closest('.container-select_preview').find('.image-preview img').attr('src', url);
            })
        }).trigger('ovic_preview_select');
        $(document).on('change', function () {
            _this.trigger('ovic_preview_select');
        });
    }
    $('.ovic_select_preview').ovic_preview_select();
});