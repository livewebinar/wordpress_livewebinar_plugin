<?php

use Livewebinar\Admin\Livewebinar_Api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;
$meeting_fields = get_post_meta($post->ID, '_livewebinar_event_post_details', true);
$meeting_token = get_post_meta($post->ID, '_livewebinar_event_post_token', true);
$meeting_url = get_post_meta($post->ID, '_livewebinar_event_post_attendee_url', true);
$embed_room = (int) get_post_meta($post->ID, '_livewebinar_event_post_embed_room', true);
$forms_response_string = Livewebinar_Api::instance()->list_forms();
$forms = [];
if (!Livewebinar_Api::instance()->is_error) {
    try {
        $forms = json_decode($forms_response_string, false, 512, JSON_THROW_ON_ERROR)->data;
    } catch (JsonException $e) {
        echo '<div class="error">' . esc_html(__('Error occurred while decoding JSON response from API:', 'livewebinar') . ' ' . $e->getMessage()) . '</div>';
    }
}
$presenters_response_string = Livewebinar_Api::instance()->list_presenters();
$presenters = [];
if (!Livewebinar_Api::instance()->is_error) {
    try {
        $presenters = json_decode($presenters_response_string, false, 512, JSON_THROW_ON_ERROR)->data;
    } catch (JsonException $e) {
        echo '<div class="error">' . esc_html(__('Error occurred while decoding JSON response from API:', 'livewebinar') . ' ' . $e->getMessage()) . '</div>';
    }
}
$errors = get_post_meta($post->ID, '_livewebinar_event_post_errors', true);

$textarea_settings = [
    'tinymce' => true,
    'textarea_name' => 'agenda',
    'media_buttons' => false,
    'textarea_rows' => 6,
    'textarea_cols' => 80,
];

$start_date_default = new DateTime();
$start_date_default->setTimezone(wp_timezone());
?>

<table>
    <tbody>
        <tr id="errorsRow" class="error-row<?php echo esc_attr(empty($errors) ? ' hidden' : ''); ?>">
            <th><?php esc_html_e('Errors', 'livewebinar'); ?></th>
            <td class="error"><?php echo wp_kses_post(nl2br($errors)); ?></td>
        </tr>
        <?php if (!$meeting_token) : ?>
            <tr id="infoRow" class="info-row">
                <th><?php esc_html_e('Info', 'livewebinar'); ?></th>
                <td class="error"><?php esc_html_e('Not bound to Livewebinar event yet', 'livewebinar'); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="livewebinar-row">
    <div class="col-md-12">
        <div class="livewebinar-tab-content tab-content">

            <div class="livewebinar-row">
                <div class="col-md-8">

                    <div class="livewebinar-row livewebinar-mb-2">

                        <div class="col-sm-12 mb-3" id="eventNameRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="event_name" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Event name', 'livewebinar'); ?></span>
                                </label>
                                <input type="text" name="event_name" id="event_name" class="livewebinar-form-control" autocomplete="off"
                                       value="<?php echo esc_attr($meeting_fields['event_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3 <?php echo esc_attr($meeting_token ? '' : 'hidden'); ?>" id="meetingTokenRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="event_token" class="livewebinar-control-label">
                                    <span class=""><?php esc_attr_e('Event token', 'livewebinar'); ?></span>
                                </label>
                                <div id="meetingTokenValueDiv"><?php echo esc_html($meeting_token); ?></div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3 <?php echo esc_attr($meeting_url ? '' : 'hidden'); ?>" id="meetingUrlRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="event_url" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Event url', 'livewebinar'); ?></span>
                                </label>
                                <div id="meetingUrlValueDiv"><a href="<?php echo esc_url($meeting_url); ?>" target="_blank"><?php echo esc_url($meeting_url); ?></a></div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3" id="embedRoomRow">
                            <div class="livewebinar-form-group is-empty">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="embed_room">
                                            <input type="checkbox" name="embed_room" id="embed_room"
                                                   value="1" <?php echo esc_attr(1 === $embed_room ? ' checked="checked"' : ''); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Embed room', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="livewebinar-row livewebinar-form-group is-empty livewebinar-mb-2" id="eventTypeRow">

                        <div class="col-sm-12 mb-3">
                            <label for="event_type" class="livewebinar-control-label">
                                <span><?php esc_html_e('Type', 'livewebinar'); ?></span>
                            </label>
                            <div><?php esc_html_e('Scheduled', 'livewebinar'); ?></div>
                        </div>
                    </div>

                    <div class="livewebinar-row livewebinar-mb-2">

                        <div class="col-sm-12 mb-3" id="startDateRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="start_date" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Start date', 'livewebinar'); ?></span>
                                </label>
                                <input type="text" name="start_date" id="start_date" class="livewebinar-form-control datetimepicker" autocomplete="off"
                                       value="<?php echo esc_attr($meeting_fields['start_date'] ?? $start_date_default->format('Y-m-d H:i:s')); ?>">
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3" id="durationRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="duration" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Duration in minutes', 'livewebinar'); ?></span>
                                </label>
                                <input type="number" max="720" name="duration" id="duration" class="livewebinar-form-control"
                                       value="<?php echo esc_attr($meeting_fields['duration'] ?? 45); ?>">
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3" id="timezoneRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="timezone" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Timezone', 'livewebinar'); ?></span>
                                </label>
                                <select name="timezone" id="timezone" class="livewebinar-form-control">
                                    <?php foreach (\DateTimeZone::listIdentifiers() as $timezone) : ?>
                                        <option value="<?php echo esc_attr($timezone); ?>"<?php
                                        echo esc_attr(((isset($meeting_fields['timezone']) && $timezone === $meeting_fields['timezone'])
                                        || (!isset($meeting_fields['timezone']) && !empty(get_option('timezone_string')) && $timezone === get_option('timezone_string')) ? ' selected="selected"' : '' ));
                                        ?>><?php echo esc_html($timezone); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3" id="formRow">
                            <div class="livewebinar-form-group is-empty">
                                <label for="form" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Form', 'livewebinar'); ?></span>
                                </label>
                                <select name="form" id="form" class="livewebinar-form-control">
                                    <option value=""><?php esc_html_e('---select form---', 'livewebinar'); ?></option>
                                    <?php foreach ($forms as $form) : ?>
                                        <option value="<?php echo esc_attr($form->id); ?>"<?php
                                        echo esc_attr((isset($meeting_fields['form']) && $form->id === $meeting_fields['form']) ? 'selected="selected"' : ''); ?>><?php
                                            echo esc_html($form->name . ('registration' === $form->type ? ' ' . __('registration enabled', 'livewebinar') : ''));
                                            ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="livewebinar-row livewebinar-mb-2">
                        <div class="col-sm-12 livewebinar-mb-2">
                            <div class="livewebinar-form-group is-empty mb-1">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="restrict_access">
                                            <input type="checkbox" name="restrict_access" id="restrict_access"
                                                   value="1" <?php echo esc_attr((!isset($meeting_fields['restrict_access']) || 1 === $meeting_fields['restrict_access'] ? ' checked="checked"' : '')); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;"><?php esc_html_e('Restrict access to room', 'livewebinar'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 livewebinar-mb-2">
                            <div class="livewebinar-form-group is-empty">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="waiting_room">
                                            <input type="checkbox" name="waiting_room" id="waiting_room"
                                                   value="1" <?php echo esc_attr(!isset($meeting_fields['waiting_room']) || 1 === $meeting_fields['waiting_room'] ? ' checked="checked"' : ''); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Waiting room', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 livewebinar-mb-2" id="passwordProtectedRow">
                            <div class="livewebinar-form-group is-empty mb-1">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="password_protected_enabled">
                                            <input type="checkbox" name="password_protected_enabled" id="password_protected_enabled"
                                                   value="1" <?php echo esc_attr(empty($meeting_fields['password_protected_password']) ? '' : 'checked="checked"'); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Password protected', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3" id="passwordRow">
                            <div class="livewebinar-form-group is-empty mb-1">
                                <label for="password_protected_password" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Password', 'livewebinar'); ?></span>
                                </label>
                                <input type="password" name="password_protected_password" id="password_protected_password" class="livewebinar-form-control" autocomplete="off"
                                       value="<?php echo esc_attr($meeting_fields['password_protected_password'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="col-sm-12 livewebinar-mb-2" id="tokensProtectedRow">
                            <div class="livewebinar-form-group is-empty mb-1">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="tokens_enabled">
                                            <input type="checkbox" name="tokens_enabled" id="tokens_enabled"
                                                   value="1" <?php echo esc_attr(isset($meeting_fields['tokens_enabled']) && 1 === $meeting_fields['tokens_enabled'] ? 'checked="checked"' : ''); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Password token protected room', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($meeting_fields['tokens_enabled'])) : ?>
                            <div class="col-sm-12 mb-3" id="tokensAmountRow">
                                <div class="form-group is-empty mb-1">
                                    <label for="tokens_amount" class="livewebinar-control-label">
                                        <span class=""><?php esc_html_e('Password tokens amount', 'livewebinar'); ?></span>
                                    </label>
                                    <input type="number" min="1" max="300" name="tokens_amount" id="tokens_amount" class="livewebinar-form-control"
                                           value="<?php echo esc_attr($meeting_fields['tokens_amount'] ?? 1); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-sm-12 livewebinar-mb-2">
                            <div class="form-group is-empty mb-1">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="thank_you_email">
                                            <input type="checkbox" name="thank_you_email" id="thank_you_email"
                                                   value="1" <?php echo esc_attr(isset($meeting_fields['thank_you_email']) && 1 === $meeting_fields['thank_you_email'] ? ' checked="checked"' : ''); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Send thank you e-mail after event', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3">
                            <div class="form-group is-empty">
                                <div class="input-group">
                                    <div class="togglebutton pull-left">
                                        <label for="disable_notifications">
                                            <input type="checkbox" name="disable_notifications" id="disable_notifications"
                                                   value="1" <?php echo esc_attr(isset($meeting_fields['disable_notifications']) && 1 === $meeting_fields['disable_notifications'] ? ' checked="checked"' : ''); ?>>
                                            <span class="toggle"></span>
                                            <span class="pull-right" style="text-transform: initial;">
                                        <?php esc_html_e('Disable notifications', 'livewebinar'); ?>
                                    </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3">

                            <div class="livewebinar-row form-group is-empty">
                                <div class="col-md-12">
                                    <div class="livewebinar-step-card">

                                        <div class="livewebinar-row mt-2">
                                            <div class="col-sm-2 text-right">
                                                <label class="livewebinar-control-label"><?php esc_html_e('Initial audio mode', 'livewebinar'); ?></label>
                                            </div>
                                            <div class="col-sm-10">
                                                <label data-selector="audio-mode" class="livewebinar-room-content <?php echo esc_attr(isset($meeting_fields['audio_mode']) && 'discussion' === $meeting_fields['audio_mode'] ? 'room-content-active' : ''); ?> mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-comments"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Discussion', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('All participants can talk. For larger sessions, up to 25 participants can talk at the same time. Others listen.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="audio_mode" hidden="hidden" value="discussion"
                                                        <?php echo esc_attr(isset($meeting_fields['audio_mode']) && 'discussion' === $meeting_fields['audio_mode'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="audio-mode" class="livewebinar-room-content <?php echo esc_attr(!isset($meeting_fields['audio_mode']) || 'presentation' === $meeting_fields['audio_mode'] ? 'room-content-active' : ''); ?> mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-presentation"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Presentation', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('Only presenters are allowed to talk. Participants listen.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="audio_mode" hidden="hidden" value="presentation"
                                                        <?php echo esc_attr(!isset($meeting_fields['audio_mode']) || 'presentation' === $meeting_fields['audio_mode'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="audio-mode" class="livewebinar-room-content <?php echo esc_attr(isset($meeting_fields['audio_mode']) && 'qa' === $meeting_fields['audio_mode'] ? 'room-content-active' : ''); ?> mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-question-circle"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Raise hand', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('Only presenters can talk. Participants can ask for permission to speak.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="audio_mode" hidden="hidden" value="qa"<?php
                                                            echo esc_attr(isset($meeting_fields['audio_mode']) && 'qa' === $meeting_fields['audio_mode'] ? ' checked="checked"' : '');
                                                        ?>>
                                                </label>
                                                <label data-selector="audio-mode" class="livewebinar-room-content <?php echo esc_attr(isset($meeting_fields['audio_mode']) && 'classroom' === $meeting_fields['audio_mode'] ? 'room-content-active' : ''); ?> mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-users-class"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Classroom', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('The presenters can see everyone, but the attendees can only see the presenters.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="audio_mode" hidden="hidden" value="classroom"
                                                        <?php echo esc_attr(isset($meeting_fields['audio_mode']) && 'classroom' === $meeting_fields['audio_mode'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="livewebinar-row mt-2">
                                            <div class="col-sm-2 text-right">
                                                <label class="livewebinar-control-label"><?php esc_html_e('Room layout', 'livewebinar'); ?></label>
                                            </div>
                                            <div class="col-sm-10">
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(!isset($meeting_fields['layout']) || 1 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--1"></i>
                                                    <input type="radio" name="layout" value="1" hidden="hidden" <?php echo esc_attr(!isset($meeting_fields['layout']) || 1 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(isset($meeting_fields['layout']) && 2 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--2"></i>
                                                    <input type="radio" name="layout" value="2" hidden="hidden" <?php echo (isset($meeting_fields['layout']) && 2 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(isset($meeting_fields['layout']) && 3 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--3"></i>
                                                    <input type="radio" name="layout" value="3" hidden="hidden" <?php echo (isset($meeting_fields['layout']) && 3 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(isset($meeting_fields['layout']) && 4 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--4"></i>
                                                    <input type="radio" name="layout" value="4" hidden="hidden" <?php echo (isset($meeting_fields['layout']) && 4 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(isset($meeting_fields['layout']) && 5 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--5"></i>
                                                    <input type="radio" name="layout" value="5" hidden="hidden" <?php echo (isset($meeting_fields['layout']) && 5 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-icon" class=" <?php echo esc_attr(isset($meeting_fields['layout']) && 6 === $meeting_fields['layout'] ? 'livewebinar-layout-icon-active' : 'livewebinar-layout-icon-default'); ?> livewebinar-layouts-icon cursor-pointer me-2 livewebinar-mb-2">
                                                    <i class="layouts-icons--6"></i>
                                                    <input type="radio" name="layout" value="6" hidden="hidden" <?php echo esc_attr(isset($meeting_fields['layout']) && 6 === $meeting_fields['layout'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="livewebinar-row mt-2">
                                            <div class="col-sm-2 text-right">
                                                <label class="livewebinar-control-label"><?php esc_html_e('Room template', 'livewebinar'); ?></label>
                                            </div>
                                            <div class="col-sm-10">
                                                <label data-selector="layout-template" class="livewebinar-room-content <?php echo esc_attr((!isset($meeting_fields['room_template']) || 'webinar' === $meeting_fields['room_template']) ? 'room-content-active' : ''); ?>  mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-comments"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Webinar', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('Interface most suitable for webinars and presentations in large groups with all features at hand.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="room_template" hidden="hidden" value="<?php echo esc_attr(\Livewebinar\Admin\Livewebinar_Widget::TEMPLATE_WEBINAR); ?>"
                                                        <?php echo esc_attr((!isset($meeting_fields['room_template']) || 'webinar' === $meeting_fields['room_template']) ? ' checked="checked"' : ''); ?>>
                                                </label>
                                                <label data-selector="layout-template" class="livewebinar-room-content <?php echo esc_attr(isset($meeting_fields['room_template']) && 'meeting' === $meeting_fields['room_template'] ? 'room-content-active' : ''); ?> mb-0">
                                                    <div class="livewebinar-room-content-icon">
                                                        <i class="fas fa-user-friends"></i>
                                                    </div>
                                                    <div class="livewebinar-room-content-text">
                                                        <p class="livewebinar-room-content-text-head font-weight-bold mt-0 mb-0"><?php esc_html_e('Meeting', 'livewebinar'); ?></p>
                                                        <p class="m-0"><?php esc_html_e('An alternative, clean interface layout- like hangouts. Best for meetings in small to medium group.', 'livewebinar'); ?></p>
                                                    </div>
                                                    <input type="radio" name="room_template" hidden="hidden" value="meeting"
                                                        <?php echo esc_attr(isset($meeting_fields['room_template']) && 'meeting' === $meeting_fields['room_template'] ? ' checked="checked"' : ''); ?>>
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="col-sm-12 mb-3">
                            <div class="form-group is-empty">
                                <label for="agenda" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Agenda', 'livewebinar'); ?></span>
                                </label>
                                <?php
                                $content = html_entity_decode($meeting_fields['agenda'] ?? '');
                                $content = stripslashes($content);
                                wp_editor($content, 'agenda', $textarea_settings);
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-3">
                            <div class="form-group is-empty">
                                <label for="presenters" class="livewebinar-control-label">
                                    <span class=""><?php esc_html_e('Presenters', 'livewebinar'); ?></span>
                                </label>
                                <select name="presenters[]" multiple="multiple" class="livewebinar-form-control">
                                    <?php foreach ($presenters as $presenter) : ?>
                                        <option value="<?php echo esc_attr($presenter->id); ?>"<?php
                                        echo esc_attr(isset($meeting_fields['presenters']) && in_array($presenter->id, $meeting_fields['presenters'], false) ? 'selected="selected"' : ''); ?>><?php
                                            echo esc_html($presenter->first_name . ' ' . $presenter->last_name);
                                            ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
