(function ($) {
    "use strict"; // Start of use strict

	/* ---------------------------------------------
	 Scripts ready
	 --------------------------------------------- */
    $(document).ready(function () {
        $(document).on('click','ul.cmb2-image-select-list .cmb2-image-select label',function (e) {
            e.stopPropagation(); // stop the click from bubbling
            $(this).closest('ul').find('.cmb2-image-select-selected').removeClass('cmb2-image-select-selected');
            $(this).parent().closest('li').addClass('cmb2-image-select-selected');
        })

    });

})(jQuery); // End of use strict
