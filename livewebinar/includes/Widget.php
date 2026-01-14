<?php

namespace Livewebinar\Includes;

use Livewebinar\Admin\Livewebinar_Api;

class Widget
{
    public const TYPE_SCHEDULED = 'scheduled';

    private static ?Widget $_instance = null;

    private string $embed_url;

    /**
     * @return Widget
     */
    public static function instance(): Widget
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        if ('local' === wp_get_environment_type()) {
            $this->embed_url = 'https://embed.livewebinar.test';
        } else {
            $this->embed_url = 'https://embed.livewebinar.com';
        }
    }

    /**
     * @param array|string $attributes
     * @return false|string
     */
    public function embed_room($attributes)
    {
        if (!empty($attributes['widget_id'])) {
            $widget = Livewebinar_Api::instance()->get_widget((int)$attributes['widget_id']);
            if (!Livewebinar_Api::instance()->is_error) {
                try {
                    $widget_object = json_decode($widget, false, 512, JSON_THROW_ON_ERROR);
                    $current_user = wp_get_current_user();
                    $token = $widget_object->data->token;

                    $embed_code = $this->get_embed_code(
                        $token,
                        current_user_can('administrator') ? $widget_object->data->roles->host : '',
                        $current_user->nickname ?? '',
                        $current_user ? get_avatar_url($current_user->ID, 64) : '',
                        $attributes['widget_id']
                    );
                } catch (\JsonException $e) {
                    $error_message = __('Error occurred, make sure you have selected proper widget.', 'livewebinar');
                }
            } else {
                $error_message = Livewebinar_Api::instance()->error_message;
            }
        } else {
            $error_message = __('No widget selected', 'livewebinar');
        }

        ob_start();
        require(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/blocks/embed-room.php');
        return ob_get_clean();
    }

    /**
     * @param string $token
     * @param string $role_token
     * @param string $nickname
     * @param string $avatar_url
     * @param int|null $widget_id
     * @return array
     */
    public function get_embed_code (string $token, string $role_token = '', string $nickname = '', string $avatar_url = '', ?int $widget_id = null): array
    {
        $id_seed = $widget_id ?? rand(1000000000, 9999999999);
        $extra = [
            '_license_key' => $token,
            '_role_token' => $role_token,
            '_registrant_token' => '',

            '_widget_containerID' => 'livewebinar-embed-widget-' . $id_seed,
            '_widget_width' => '100%',
            '_widget_height' => '100vh',
            '_disable_target_top' => 1,

            '_password_token' => '',
            '_nickname' => $nickname,
            '_avatar_url' => $avatar_url,
        ];

        $code = "<div class=\"livewebinar-embed-widget\" data-selector=\"livewebinar-event-post-embed\" id=\"livewebinar-embed-widget-" . $id_seed ."\"></div>\n\n";
        $code .= "<button class=\"btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top\" data-selector=\"full-screen-button\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" width=\"18\" height=\"18\">
                            <path fill=\"#fff\" d=\"M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z\"/>
                        </svg>
                    </button>
                    <button class=\"btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top\" data-selector=\"exit-full-screen-button\" style=\"display: none;\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" width=\"18\" height=\"18\">
                            <path fill=\"#fff\" d=\"M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z\"/>
                        </svg>
                    </button>\n\n";
        $code .= "<button class=\"btn livewebinar-btn-fullscreen\" data-selector=\"full-screen-button\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" width=\"18\" height=\"18\">
                            <path fill=\"#fff\" d=\"M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z\"/>
                        </svg>
                    </button>
                    <button class=\"btn livewebinar-btn-fullscreen\" data-selector=\"exit-full-screen-button\" style=\"display: none;\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" width=\"18\" height=\"18\">
                            <path fill=\"#fff\" d=\"M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z\"/>
                        </svg>
                    </button>\n\n";

        $result['html'] = $code;
        $result['options'] = $extra;
        $result['url'] = $this->embed_url;

        return $result;
    }

    /**
     * @param array|string $attributes
     * @return false|string
     */
    public function room_info($attributes)
    {
        if (!empty($attributes['widget_id'])) {
            if (!Livewebinar_Api::instance()->is_error) {
                $widget = Livewebinar_Api::instance()->get_widget((int) $attributes['widget_id']);
                try {
                    $widget_object = json_decode($widget, false, 512, JSON_THROW_ON_ERROR);
                    $widget_data = $widget_object->data;
                } catch (\JsonException $e) {
                    $error_message = __('Error occurred, make sure you have selected proper widget.', 'livewebinar');
                }
            } else {
                $error_message = Livewebinar_Api::instance()->error_message;
            }
        } else {
            $error_message = __('No widget selected', 'livewebinar');
        }

        ob_start();
        require(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/blocks/room-info.php');
        return ob_get_clean();
    }
}