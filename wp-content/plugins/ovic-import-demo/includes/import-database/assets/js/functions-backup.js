(function ($) {
    'use strict';

    $(document).on('click', '.install-sample', function (e) {
        e.preventDefault();

        var sample_package = $(this).data('package'),
            data           = {
                package: sample_package,
                security: import_sample_data_ajax_admin.security,
                step: 1,
                action: 'import_sample_data_install_sample_data',
            };

        $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {
            $.magnificPopup.open({
                items: {
                    src: '<div class="install-sample-popup"><div class="head">' + import_sample_data_ajax_admin.install_popup_title + '</div><div class="popup-content"> ' + response[ 'data' ] + '</div></div>'
                },
                type: 'inline',
                closeOnContentClick: true,
                closeOnBgClick: false,
                modal: true
            });

        });
    });


    $(document).on('click', '.button-cancel', function (e) {
        $.magnificPopup.close();
        e.preventDefault();
    })
    $(document).on('change', '#confirm-sample-data-installation', function () {
        if ( $(this).is(':checked') ) {
            $('#config-action').removeAttr('disabled');
        } else {
            $('#config-action').attr('disabled', 'disabled');
        }
    })
    $(document).on('change', 'input[name="extend_settings"]', function () {
        if ( $(this).is(":checked") ) {
            $('.box-wrap.select-page').show();
        } else {
            $('.box-wrap.select-page').hide();
        }
    })
    $(document).on('click', '.select-page .box', function () {
        var wrapper    = $(this),
            checkBoxes = wrapper.find("input[name=sample_page]");

        $(this.closest('#sample-data-installation-options')).find('.box').removeClass('selected');

        checkBoxes.prop("checked", !checkBoxes.prop("checked"));

        $(this).addClass('selected');

    })
    $(document).on('click', '.installation-actions .button-install', function () {
        var sample_package = $(this).data('package'),
            data           = {
                package: sample_package,
                security: import_sample_data_ajax_admin.security,
                step: 2,
                action: 'import_sample_data_install_sample_data',
            };

        $('body').addClass('install-sample-data-process');
        $('#sample-data-installation-step-2').removeClass('hidden');
        $('#sample-data-installation-step-1').addClass('hidden');

        $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {
            if ( response[ 'success' ] == true ) {

                $('#install-sample-data-download-package').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-yes');
                $('#install-sample-data-import-data').removeClass('hidden');

                var sample_page = $("#sample-data-installation-options input[name='sample_page']:checked").val(),
                    data        = {
                        package: sample_package,
                        security: import_sample_data_ajax_admin.security,
                        step: 3,
                        sample_page: sample_page,
                        action: 'import_sample_data_install_sample_data',
                    };

                $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {
                    if ( response[ 'success' ] == true ) {
                        $('#install-sample-data-import-data').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-yes');

                        if ( import_sample_data_ajax_admin.required_plugins.length ) {

                            $('#install-sample-data-required-plugins').removeClass('hidden');

                            var error        = false,
                                count_plugin = import_sample_data_ajax_admin.required_plugins.length;

                            var install_plugin = function (index) {
                                index = index || 0;

                                $("#install-sample-data-required-plugins .install-status").text((index + 1) + "/" + count_plugin + ": " + import_sample_data_ajax_admin.required_plugins[ index ].name);
                                $("#install-sample-data-required-plugins .progress-bar").css("width", Math.round((index / count_plugin) * 100) + "%");
                                $("#install-sample-data-required-plugins .percentage").text(Math.round((index / count_plugin) * 100));

                                var data = {
                                    plugin: import_sample_data_ajax_admin.required_plugins[ index ],
                                    security: import_sample_data_ajax_admin.security,
                                    action: 'import_sample_data_install_plugin',
                                }
                                $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {

                                    if ( response[ 'success' ] == false ) {

                                        $('#install-sample-data-required-plugins').append('<div class="alert alert-warning">' + response[ 'data' ] + '</div>').addClass('error');
                                    }

                                    if ( index + 1 == import_sample_data_ajax_admin.required_plugins.length ) {
                                        $("#install-sample-data-required-plugins .install-status").addClass('hidden');
                                        $("#install-sample-data-required-plugins .progress").addClass('hidden');

                                        if ( $('#install-sample-data-required-plugins').hasClass('error') ) {
                                            $('#install-sample-data-required-plugins ').find('.spinner').removeClass('spinner').addClass('dashicons  dashicons-no-alt');
                                            $('#install-sample-data-failure-message').removeClass('hidden');
                                        } else {
                                            $('#install-sample-data-required-plugins ').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-yes');

                                            $('#install-sample-data-success-message').removeClass('hidden');
                                            $('#sample-data-installation-step-2').addClass('hidden');

                                        }
                                        return;
                                    }

                                    install_plugin(index + 1);
                                })
                            }
                            install_plugin();
                        } else {
                            $('#install-sample-data-success-message').removeClass('hidden');
                        }

                    } else {
                        $('#install-sample-data-import-data').append('<div class="alert alert-warning">' + response[ 'data' ] + '</div>');
                        $('#install-sample-data-import-data').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-no-alt');
                    }
                })
            } else {
                $('#install-sample-data-download-package').append('<div class="alert alert-warning">' + response[ 'data' ] + '</div>');
                $('#install-sample-data-download-package').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-no-alt');
            }
        });
        return false;
    });

    $(document).on('click', '.uninstall-sample', function (e) {
        e.preventDefault();

        var sample_package = $(this).data('package'),
            data           = {
                package: sample_package,
                security: import_sample_data_ajax_admin.security,
                step: 1,
                action: 'import_sample_data_uninstall_sample_data',
            };

        $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {
            $.magnificPopup.open({
                items: {
                    src: '<div class="install-sample-popup"><div class="head">' + import_sample_data_ajax_admin.uninstall_popup_title + '</div><div class="popup-content"> ' + response[ 'data' ] + '</div></div>'
                },
                type: 'inline',
                closeOnContentClick: true,
                closeOnBgClick: false,
                modal: true
            });
        });
    });
    $(document).on('click', '.uninstallation-actions .button-uninstall', function (e) {
        e.preventDefault();

        var sample_package = $(this).data('package'),
            data           = {
                package: sample_package,
                security: import_sample_data_ajax_admin.security,
                step: 2,
                action: 'import_sample_data_uninstall_sample_data',
            };

        $('#sample-data-uninstallation-step-2').removeClass('hidden');
        $('#sample-data-uninstallation-step-1').addClass('hidden');
        $('body').addClass('uninstall-sample-data-process');

        $.post(import_sample_data_ajax_admin.ajaxurl, data, function (response) {
            if ( response[ 'success' ] == true ) {
                $.magnificPopup.close();
                $('#sample-data-' + sample_package).find('.uninstall-sample').addClass('hidden');
                $('#sample-data-' + sample_package).find('.install-sample').removeClass('hidden');
                $('body').removeClass('uninstall-sample-data-process');
            } else {
                $('#uninstall-sample-data').append('<div class="alert alert-warning">' + response[ 'data' ] + '</div>');
                $('#uninstall-sample-data').find('.spinner').removeClass('spinner').addClass('dashicons dashicons-no-alt');
            }
        })
    });

})(window.jQuery);