"use strict";

(function ($) {
    let dom = {};
    let apiJs = {
        onReady: function onReady() {
            this.initDom();
            this.eventListeners();
            this.initializeDatetimepickers();
        },
        initDom: function() {
            dom.clientId = $('#livewebinar_client_id');
            dom.clientSecret = $('#livewebinar_client_secret');
            dom.togglePassword = $('.toggle-password');
            dom.toggleClientId = $('.toggle-client-id');
            dom.toggleClientSecret = $('.toggle-client-secret');
            dom.submitSettings = $('#submit-settings');
        },
        testConnection: function testConnection(e) {
            e.preventDefault();
            $.post(livewebinar_admin_js.ajax_url, {
                action: 'test_connection',
                security: livewebinar_admin_js.livewebinar_security
            }).done(function (result) {
                $('[data-content="testConnectionModal"]').html(result);
                $('#testConnectionModal').modal('show');
            });
        },
        clearApiCredentials: function clearApiCredentials(e) {
            e.preventDefault();
            $.post(livewebinar_admin_js.ajax_url, {
                action: 'clear_api_credentials',
                security: livewebinar_admin_js.livewebinar_security
            }).done(function (result) {
                if (result.success) {
                    dom.clientId.val('');
                    dom.clientSecret.val('');
                } else {
                    dom.clientId.val(result.fields.client_id);
                    dom.clientSecret.val(result.fields.client_secret);
                }

                $('[data-content="testConnectionModal"]').html(result.message);
                $('#testConnectionModal').modal('show');
            });
        },
        removeLogs: function removeLogs(e) {
            e.preventDefault();
            let type = $(e.target).data('type');
            let filename = type + '_log.log';

            $.post(livewebinar_admin_js.ajax_url, {
                action: 'remove_log_file',
                security: livewebinar_admin_js.livewebinar_security,
                filename: filename
            }).done(function (result) {
                alert(result);
            });
        },
        eventListeners: function() {
            $('.test-api-connection').on('click', this.testConnection.bind(this));
            $('#livewebinar_clear_api_credentials').on('click', this.clearApiCredentials.bind(this));
            $('.remove-logs').on('click', this.removeLogs.bind(this));
            dom.togglePassword.on('click', this.togglePassword.bind(this));
            dom.toggleClientId.on('click', this.toggleClientId.bind(this));
            dom.toggleClientSecret.on('click', this.toggleClientSecret.bind(this));
            $('[data-selector="settings-nav"]').on('click', this.toggleSubmitSettingsBlock.bind(this));
        },
        initializeDatetimepickers: function() {
            $('.datetimepicker').datetimepicker({
                // Formats
                // follow MomentJS docs: https://momentjs.com/docs/#/displaying/format/
                format: 'YYYY-MM-DD HH:mm:ss',

                // Your Icons
                // as Bootstrap 4 is not using Glyphicons anymore
                icons: {
                    time: 'fa fa-clock',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-check',
                    clear: 'fa fa-trash',
                    close: 'fa fa-times'
                }
            });
        },
        togglePassword: function togglePassword() {
            let input = dom.togglePassword.siblings('input');

            if ('password' === input.attr('type')) {
                dom.togglePassword.text(livewebinar_admin_js.hide_text);
                input.attr('type', 'text');
            } else {
                dom.togglePassword.text(livewebinar_admin_js.show_text);
                input.attr('type', 'password');
            }
        },
        toggleClientId: function toggleClientId() {
            let input = dom.toggleClientId.siblings('input');

            if ('password' === input.attr('type')) {
                dom.toggleClientId.text(livewebinar_admin_js.hide_text);
                input.attr('type', 'text');
            } else {
                dom.toggleClientId.text(livewebinar_admin_js.show_text);
                input.attr('type', 'password');
            }
        },
        toggleClientSecret: function toggleClientSecret() {
            let input = dom.toggleClientSecret.siblings('input');

            if ('password' === input.attr('type')) {
                dom.toggleClientSecret.text(livewebinar_admin_js.hide_text);
                input.attr('type', 'text');
            } else {
                dom.toggleClientSecret.text(livewebinar_admin_js.show_text);
                input.attr('type', 'password');
            }
        },
        toggleSubmitSettingsBlock: function toggleSubmitSettingsBlock(e) {
            let targetHref = $(e.target).attr('href');
            if ('#api' === targetHref || '#settings' === targetHref) {
                dom.submitSettings.show();
            } else {
                dom.submitSettings.hide();
            }
        }
    };

    $(function () {
        apiJs.onReady();
        $(document).ready(() => {
            $('.livewebinar-select2').select2({
                theme: 'bootstrap4',
            });
        });
    });
})(jQuery);
