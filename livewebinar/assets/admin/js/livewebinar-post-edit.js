"use strict";

(function ($) {
    let isSaving = false;
    let dom = {};
    let post = {
        initDom: function initDom() {
            dom.passwordProtectedEnabledRow = $('#passwordProtectedRow');
            dom.passwordProtectedPasswordRow = $('#passwordRow');
            dom.tokensEnabledRow = $('#tokensProtectedRow');
            dom.tokensAmountRow = $('#tokensAmountRow');
            dom.errorsRow = $('#errorsRow');
            dom.infoRow = $('#infoRow');
            dom.meetingTokenRow = $('#meetingTokenRow');
            dom.meetingTokenValueDiv = $('#meetingTokenValueDiv');
            dom.meetingUrlRow = $('#meetingUrlRow');
            dom.meetingUrlValueDiv = $('#meetingUrlValueDiv');
            dom.eventNameInput = $('#event_name');
            this.audioMode = $('[data-selector="audio-mode"]');
            this.layoutIcon = $('[data-selector="layout-icon"]');
            this.layoutTemplate = $('[data-selector="layout-template"]');
        },
        onReady: function onReady() {
            this.initDom();
            this.eventListeners();
            this.fireEvents();
        },
        eventListeners: function eventListeners() {
            dom.passwordProtectedEnabledRow.on('change', () => {
                let checked = dom.passwordProtectedEnabledRow.find('input[type="checkbox"]').is(':checked');

                if (checked) {
                    dom.passwordProtectedPasswordRow.show();
                } else {
                    dom.passwordProtectedPasswordRow.hide();
                }
            });

            dom.tokensEnabledRow.on('change', () => {
                let checked = dom.tokensEnabledRow.find('input[type="checkbox"]').is(':checked');

                if (checked) {
                    dom.tokensAmountRow.show();
                } else {
                    dom.tokensAmountRow.hide();
                }
            });

            this.layoutIcon.on('change', (ev) => {
                this.toggleLayoutIcon(ev);
            });
            this.audioMode.on('change', (ev) => {
                this.toggleAudioMode(ev);
            });
            this.layoutTemplate.on('change', (ev) => {
                this.toggleLayoutTemplate(ev);
            });
        },
        fireEvents: function fireEvents() {
            dom.passwordProtectedEnabledRow.change();
            dom.tokensEnabledRow.change();
            $('#agenda-tmce').click();
        },
        updateData: function updateData() {
            $.post(livewebinar_data.ajax_url, {
                action: 'get_post_data',
                security: livewebinar_data.livewebinar_security,
                post_id: $('#post_ID').val(),
            }).done(function (result) {
                if (result.errors.length > 0) {
                    dom.errorsRow.find('td').html(result.errors);
                    dom.errorsRow.show();
                } else {
                    dom.errorsRow.hide();
                    dom.tokensAmountRow.remove()
                }

                if (result.token.length > 0) {
                    dom.meetingTokenValueDiv.text(result.token);
                    dom.meetingTokenRow.show();
                    dom.infoRow.hide();
                } else {
                    dom.meetingTokenRow.hide();
                    dom.infoRow.show();
                }

                if (result.url.length > 0) {
                    dom.meetingUrlValueDiv.html('<a href="' + result.url + '" target="_blank">' + result.url + '</a>');
                    dom.meetingUrlRow.show();
                } else {
                    dom.meetingUrlRow.hide();
                }

                if (result.event_name !== dom.eventNameInput.val()) {
                    dom.eventNameInput.val(result.event_name);
                }
            });
        },

        toggleAudioMode(ev) {
            this.audioMode.removeClass('room-content-active');
            $(ev.currentTarget).find('[name="autostart[mode]"]').prop('checked', true);
            $(ev.currentTarget).addClass('room-content-active');
        },

        toggleLayoutIcon(ev) {
            this.layoutIcon.removeClass('livewebinar-layout-icon-active');
            this.layoutIcon.addClass('livewebinar-layout-icon-default');
            $(ev.currentTarget).find('[name="autostart[initial_layout_id]"]').prop('checked', true);
            $(ev.currentTarget).addClass('livewebinar-layout-icon-active');
            $(ev.currentTarget).removeClass('livewebinar-layout-icon-default');
        },

        toggleLayoutTemplate(ev) {
            this.layoutTemplate.removeClass('room-content-active');
            $(ev.currentTarget).find('[name="autostart[mode]"]').prop('checked', true);
            $(ev.currentTarget).addClass('room-content-active');
        }
    };

    $(function () {
        if ('undefined' !== typeof wp.data) {
            wp.data.subscribe(() => {
                let isSavingMetaboxes = wp.data.select('core/edit-post').isSavingMetaBoxes();
                let isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();

                if (isSavingMetaboxes && !isAutosavingPost) {
                    isSaving = true;
                } else if (isSaving) {
                    isSaving = false;
                    post.updateData();
                }
            });
        }

        $('select').select2({
            theme: 'bootstrap4',
        })

        $(document).ready(() => {
            post.onReady();
        });
    });
})(jQuery);
