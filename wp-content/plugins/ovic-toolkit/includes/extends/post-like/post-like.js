;(function ($) {
    'use strict';
    $(document).on('click', '.sl-button', function (e) {
        var button    = $(this),
            post_id   = button.data('post_id'),
            security  = button.data('nonce'),
            iscomment = button.data('iscomment'),
            allbuttons;
        if ( iscomment === '1' ) { /* Comments can have same id */
            allbuttons = $('.sl-comment-button-' + post_id);
        } else {
            allbuttons = $('.sl-button-' + post_id);
        }
        var loader = allbuttons.next('.sl-loader');
        button.addClass('loading');
        if ( post_id !== '' ) {
            $.ajax({
                type: 'POST',
                url: simpleLikes.ajaxurl,
                data: {
                    action: 'process_simple_like',
                    post_id: post_id,
                    nonce: security,
                    is_comment: iscomment,
                },
                success: function (response) {
                    var icon        = response.icon,
                        count       = response.count,
                        like_text   = simpleLikes.like,
                        unlike_text = simpleLikes.unlike;

                    allbuttons.find('.icon').html(icon);
                    allbuttons.find('.count').html(count);
                    if ( response.status === 'unliked' ) {
                        allbuttons.prop('title', like_text);
                        allbuttons.removeClass('liked');
                        allbuttons.find('.title').html('Like');
                    } else {
                        allbuttons.prop('title', unlike_text);
                        allbuttons.addClass('liked');
                        allbuttons.find('.title').html('Liked');
                    }
                    button.removeClass('loading');
                }
            });
        }
        e.preventDefault();
    });
})(jQuery);