<?php

namespace Livewebinar\Admin;

class Livewebinar_Widget
{
    public const TYPE_SCHEDULED = 'scheduled';

    public const TEMPLATE_WEBINAR = 'webinar';
    public const TEMPLATE_MEETING = 'meeting';

    public static array $template_mapping = [
        self::TEMPLATE_WEBINAR => 0,
        self::TEMPLATE_MEETING => 3,
    ];

    public const LOCK_STATE_LOCKED = 'locked';
    public const LOCK_STATE_UNLOCKED = 'unlocked';

    public static array $lock_states = [
        0 => self::LOCK_STATE_UNLOCKED,
        1 => self::LOCK_STATE_LOCKED,
    ];

    public ?int $widget_id = null;

    public array $fields = [
        'strict_event' => 0,
        'autostart[lock_state]' => 0,
        'disable_all_notifications' => 0,
        'thank_you_email' => 0,
    ];

    public array $mapping = [
        'event_name' => 'name',
        'start_date' => 'start_date',
        'duration' => 'duration',
        'timezone' => 'timezone',
        'restrict_access' => 'strict_event',
        'thank_you_email' => 'thank_you_email',
        'audio_mode' => 'autostart[mode]',
        'password_protected_password' => 'password',
        'disable_notifications' => 'disable_all_notifications',
        'leave_page_url' => 'autostart[thankyou_page_url]',
        'layout' => 'autostart[initial_layout_id]',
        'room_template' => 'autostart[initial_template_id]',
        'form' => 'form[id]',
        'agenda' => 'agenda',
        'status' => 'status',
        'waiting_room' => 'autostart[lock_state]',
        'presenters' => 'presenters',
    ];

    public function __construct(\WP_Post $post, array $fields)
    {
        $widget_id_meta = get_post_meta($post->ID, '_livewebinar_event_post_event_id', true);
        if (!empty($widget_id_meta)) {
            $this->widget_id = $widget_id_meta;
            $this->fields = [];
        }

        $this->fields = array_merge($this->fields, $this->remap_fields($fields));
        $this->remove_empty();
        $this->fields['type'] = self::TYPE_SCHEDULED;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function remap_fields(array $fields): array
    {
        $remapped = [];
        foreach ($fields as $field_name => $field_value) {
            if (array_key_exists($field_name, $this->mapping)) {
                $remapped[$this->mapping[$field_name]] = $field_value;
            }
        }

        if (array_key_exists('autostart[initial_template_id]', $remapped)
                && array_key_exists($remapped['autostart[initial_template_id]'], self::$template_mapping)
                && !in_array($remapped['autostart[initial_template_id]'], self::$template_mapping, true)) {
            $remapped['autostart[initial_template_id]'] = self::$template_mapping[$remapped['autostart[initial_template_id]']];
        }

        if (array_key_exists('autostart[lock_state]', $remapped) && array_key_exists($remapped['autostart[lock_state]'], self::$lock_states)) {
            $remapped['autostart[lock_state]'] = self::$lock_states[$remapped['autostart[lock_state]']];
        }

        $remapped['agenda'] = html_entity_decode($remapped['agenda'] ?? '');

        return $remapped;
    }

    /**
     * @return void
     */
    public function remove_empty(): void
    {
        if (array_key_exists('form[id]', $this->fields) && ('' === $this->fields['form[id]'] || 0 === $this->fields['form[id]'])) {
            $this->fields['form'] = '';
            unset($this->fields['form[id]']);
        }

        if (array_key_exists('start_date', $this->fields) && empty($this->fields['start_date'])) {
            unset($this->fields['start_date']);
        }
    }
}