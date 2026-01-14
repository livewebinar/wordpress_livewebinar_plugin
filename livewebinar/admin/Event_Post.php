<?php

namespace Livewebinar\Admin;

use WP_Post;

class Event_Post
{
    private string $post_type = 'livewebinar-post';

    private static ?Event_Post $_instance = null;

    /**
     * @return Event_Post
     */
    public static function instance(): Event_Post
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        add_action('init', [$this, 'register_post_types']);
        add_action('add_meta_boxes', [$this, 'add_metaboxes']);
        add_action('save_post_' . $this->post_type, [$this, 'save_post'], 10, 2);
        add_filter('single_template', [$this, 'render_post'], 20);
        add_action('before_delete_post', [$this, 'delete_post']);
        add_action('wp_ajax_get_post_data', [$this, 'get_post_data']);
    }

    /**
     * @return void
     */
    public function get_post_data(): void
    {
        check_ajax_referer('_nonce_livewebinar_security', 'security');

        $post_id = (int) filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
        $errors = nl2br(get_post_meta($post_id, '_livewebinar_event_post_errors', true));
        $meeting_token = get_post_meta($post_id, '_livewebinar_event_post_token', true);
        $meeting_url = get_post_meta($post_id, '_livewebinar_event_post_attendee_url', true);
        $event_name = get_post_meta($post_id, '_livewebinar_event_post_details', true)['event_name'];

        wp_send_json(['errors' => $errors, 'token' => $meeting_token, 'url' => $meeting_url, 'event_name' => $event_name]);

        wp_die();
    }

    /**
     * @return string
     */
    public function get_post_type(): string
    {
        return $this->post_type;
    }

    /**
     * @return void
     */
    public function register_post_types(): void
    {
        $labels = [
            'name' => __('LiveWebinar event posts', 'livewebinar'),
            'singular_name' => __('LiveWebinar event post', 'livewebinar'),
            'menu_name' => __('LiveWebinar', 'livewebinar'),
            'add_new' => __('Add new', 'livewebinar'),
            'add_new_item' => __('Add new event', 'livewebinar'),
            'new_item' => __('New event', 'livewebinar'),
            'edit_item' => __('Edit event', 'livewebinar'),
            'view_item' => __('View event', 'livewebinar'),
            'all_items' => __('All events', 'livewebinar'),
            'search_items' => __('Search events', 'livewebinar'),
            'not_found' => __('No events found.', 'livewebinar'),
            'not_found_in_trash' => __('No events found in Trash.', 'livewebinar'),
        ];

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'menu_icon' => LIVEWEBINAR_PLUGIN_DIR_URL . 'assets/images/livewebinar-icon-19x19.png',
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'show_in_rest' => true,
            'menu_position' => 5,
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'page-attributes'
            ),
            'rewrite' => ['slug' => 'livewebinar'],
        );

        register_post_type($this->post_type, $args);
        flush_rewrite_rules(true);
    }

    /**
     * @return void
     */
    public function add_metaboxes(): void
    {
        add_meta_box('livewebinar-event-meta', __('LiveWebinar event post details', 'livewebinar'), [$this, 'render_metabox'],
            $this->post_type, 'normal');
    }

    /**
     * @param $post
     * @return void
     */
    public function render_metabox($post): void
    {
        wp_nonce_field('_livewebinar_event_post_save', '_livewebinar_event_post_save_nonce');

        require_once(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/event-post-edit.php');
    }

    /**
     * @param int $post_id
     * @param WP_Post $post
     * @return void
     */
    public function save_post(int $post_id, WP_Post $post): void
    {
        $nonce_action = '_livewebinar_event_post_save';

        // Check if nonce is valid.
        if (!wp_verify_nonce($_POST['_livewebinar_event_post_save_nonce'] ?? '', $nonce_action)) {
            return;
        }

        // Check if user has permissions to save data.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if not an autosave.
        if (wp_is_post_autosave($post_id)) {
            return;
        }

        // Check if not a revision.
        if (wp_is_post_revision($post_id)) {
            return;
        }

        $old_fields = get_post_meta($post_id, '_livewebinar_event_post_details', true);

        $password_protected_enabled = empty(filter_input(INPUT_POST, 'password_protected_enabled')) ? 0 : 1;
        $password_protected_password = $password_protected_enabled ? sanitize_text_field(filter_input(INPUT_POST, 'password_protected_password')) : '';
        $duration = sanitize_text_field(filter_input(INPUT_POST, 'duration'));
        $layout = (int) filter_input(INPUT_POST, 'layout');
        $room_template = filter_input(INPUT_POST, 'room_template');
        $timezone = filter_input(INPUT_POST, 'timezone');
        $restrict_access = empty(filter_input(INPUT_POST, 'restrict_access')) ? 0 : 1;
        $waiting_room = empty(filter_input(INPUT_POST, 'waiting_room')) ? 0 : 1;
        $disable_notifications = empty(filter_input(INPUT_POST, 'disable_notifications')) ? 0 : 1;
        $thank_you_email = empty(filter_input(INPUT_POST, 'thank_you_email')) ? 0 : 1;
        $presenters = filter_input(INPUT_POST, 'presenters', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
        $agenda = sanitize_textarea_field(htmlentities(wpautop(filter_input(INPUT_POST, 'agenda'))));
        $tokens_enabled = empty(filter_input(INPUT_POST, 'tokens_enabled')) ? 0 : 1;
        $event_name = sanitize_text_field(filter_input(INPUT_POST, 'event_name'));
        $event_name = empty($event_name) ? $post->post_title : $event_name;

        $fields = [
            'event_name' => $event_name,
            'start_date' => sanitize_text_field(filter_input(INPUT_POST, 'start_date')),
            'duration' => $duration > 0 && $duration < 720 ? $duration : 45,
            'timezone' => in_array($timezone, \DateTimeZone::listIdentifiers(), true) ? $timezone : 'UTC',
            'form' => (int) filter_input(INPUT_POST, 'form'),
            'restrict_access' => $restrict_access,
            'waiting_room' => $waiting_room,
            'audio_mode' => filter_input(INPUT_POST, 'audio_mode'),
            'password_protected_password' => $password_protected_password,
            'thank_you_email' => $thank_you_email,
            'disable_notifications' => $disable_notifications,
            'layout' => $layout > 0 && $layout < 7 ? $layout : 1,
            'room_template' => in_array($room_template, array_flip(Livewebinar_Widget::$template_mapping), true) ? $room_template : Livewebinar_Widget::TEMPLATE_WEBINAR,
            'agenda' => $agenda,
            'presenters' => $presenters,
            'tokens_enabled' => $tokens_enabled,
        ];

        update_post_meta($post_id, '_livewebinar_event_post_details', $fields);

        if ((!isset($old_fields['tokens_enabled']) || 0 === $old_fields['tokens_enabled']) && 1 === $fields['tokens_enabled']) {
            $tokens_amount = $tokens_enabled ? (int) filter_input(INPUT_POST, 'tokens_amount') : 1;
            $tokens_amount = min(max($tokens_amount, 1), 300);
        }

        $embed_room = empty(filter_input(INPUT_POST, 'embed_room')) ? 0 : 1;
        update_post_meta($post_id, '_livewebinar_event_post_embed_room', $embed_room);

        $livewebinar_event_id = get_post_meta($post_id, '_livewebinar_event_post_event_id', true);
        $livewebinar_event_token = get_post_meta($post_id, '_livewebinar_event_post_token', true);

        if (empty($livewebinar_event_id)) {
            if (empty($fields['form'])) {
                unset($fields['form']);
            }

            $response = Livewebinar_Api::instance()->create_widget($post, $fields);

            if (!Livewebinar_Api::instance()->is_error) {
                delete_post_meta($post_id, '_livewebinar_event_post_errors');
                try {
                    $response_obj = json_decode($response, false, 512, JSON_THROW_ON_ERROR)->data;

                    if (!empty($response_obj)) {
                        $livewebinar_event_id = $response_obj->id;
                        $livewebinar_event_token = $response_obj->token;

                        update_post_meta($post_id, '_livewebinar_event_post_event_id', $livewebinar_event_id);
                        update_post_meta($post_id, '_livewebinar_event_post_token', $livewebinar_event_token);
                        update_post_meta($post_id, '_livewebinar_event_post_attendee_url', LIVEWEBINAR_PLUGIN_JOIN_URL_BASE . '/' . $response_obj->token);
                    }
                } catch (\JsonException $e) {
                    update_post_meta($post_id, '_livewebinar_event_post_errors', $e->getMessage());
                }
            } else {
                update_post_meta($post_id, '_livewebinar_event_post_errors', $response);
            }
        } else {
            $response = Livewebinar_Api::instance()->update_widget($post, $fields, $livewebinar_event_id);
            if (!Livewebinar_Api::instance()->is_error) {
                delete_post_meta($post_id, '_livewebinar_event_post_errors');
            } else {
                update_post_meta($post_id, '_livewebinar_event_post_errors', $response);
            }
        }

        if (isset($tokens_amount) && 1 === $tokens_enabled && !Livewebinar_Api::instance()->is_error) {
            $response = Livewebinar_Api::instance()->create_widget_tokens(
                $fields['event_name'] . ' - ' . $livewebinar_event_token,
                $livewebinar_event_id,
                $tokens_amount
            );
            if (Livewebinar_Api::instance()->is_error) {
                update_post_meta($post_id, '_livewebinar_event_post_errors', $response);
                $fields['tokens_enabled'] = 0;
                update_post_meta($post_id, '_livewebinar_event_post_details', $fields);
            }
        } elseif (isset($tokens_amount)) {
            $fields['tokens_enabled'] = 0;
            update_post_meta($post_id, '_livewebinar_event_post_details', $fields);
        } elseif (0 === $tokens_enabled && isset($old_fields['tokens_enabled']) &&
            1 === $old_fields['tokens_enabled'] && !Livewebinar_Api::instance()->is_error) {
            $response = Livewebinar_Api::instance()->get_widget_token_groups($livewebinar_event_id);
            if (Livewebinar_Api::instance()->is_error) {
                $errors[] = Livewebinar_Api::instance()->error_message;
            } elseif (!empty($response)) {
                try {
                    $response_obj = json_decode($response, false, 512, JSON_THROW_ON_ERROR)->widgets_user_tokens;

                    foreach ($response_obj as $token_group) {
                        Livewebinar_Api::instance()->delete_widget_token_group($token_group->user_widget_token_id);
                        if (Livewebinar_Api::instance()->is_error) {
                            $errors[] = Livewebinar_Api::instance()->error_message;
                        }
                    }
                } catch (\JsonException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!empty($errors)) {
                update_post_meta($post_id, '_livewebinar_event_post_errors', rtrim(implode(PHP_EOL, $errors), PHP_EOL));
                $fields['tokens_enabled'] = 1;
                update_post_meta($post_id, '_livewebinar_event_post_details', $fields);
            }
        }
    }

    /**
     * @param $template
     * @return mixed|string
     */
    public function render_post($template)
    {
        global $post;

        if (!empty($post) && $post->post_type === $this->post_type) {
            unset($GLOBALS['livewebinar']);

            $template = LIVEWEBINAR_PLUGIN_TEMPLATES_PATH . '/event-post.php';
        }

        return $template;
    }

    /**
     * @param int $post_id
     * @return void
     */
    public function delete_post(int $post_id): void
    {
        $dont_delete = get_option('livewebinar_dont_delete_events', false);

        if (!empty($dont_delete)) {
            return;
        }

        if (get_post_type($post_id) === $this->post_type) {
            $livewebinar_event_id = get_post_meta($post_id, '_livewebinar_event_post_event_id', true);

            if (!empty($livewebinar_event_id) && is_numeric($livewebinar_event_id)) {
                $r = Livewebinar_Api::instance()->deactivate_widget(get_post($post_id), $livewebinar_event_id);
            }
        }
    }
}