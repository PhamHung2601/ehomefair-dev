(function ($) {
    "use strict"; // Start of use strict

    document.addEventListener('DOMContentLoaded', function () {

        var reload_check         = false;
        var publish_button_click = false;

        setInterval(function () {

            if ( $('.editor-post-publish-button').length && !publish_button_click ) {
                publish_button_click = true;
                $(document).on('click', '.editor-post-publish-button', function () {
                    var reloader = setInterval(function () {
                        if ( reload_check ) {
                            return;
                        } else {
                            reload_check = true;
                        }
                        var postsaving = wp.data.select('core/editor').isSavingPost(),
                            autosaving = wp.data.select('core/editor').isAutosavingPost(),
                            success    = wp.data.select('core/editor').didPostSaveRequestSucceed();

                        console.log('Saving: ' + postsaving + ' - Autosaving: ' + autosaving + ' - Success: ' + success);
                        if ( !success ) {
                            reload_check = false;
                            return;
                        }
                        clearInterval(reloader);
                        window.location.reload(true);
                    }, 1000);
                });
            }
        }, 500);
    });

})(jQuery); // End of use strict