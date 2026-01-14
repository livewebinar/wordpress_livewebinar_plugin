<?php

namespace Livewebinar\Admin;

use JsonException;

class Livewebinar_Api
{
    public const METHOD_GET = 'get';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_DELETE = 'delete';

    public static ?Livewebinar_Api $_instance = null;

    public const AUTH_ERROR_TRANSIENT_KEY = 'livewebinar_api_auth_error';
    public const AUTH_ERROR_OPTION_KEY = 'livewebinar_api_auth_error_until';
    private const AUTH_ERROR_LOCK_TTL = 600;

    private static bool $auth_lock_runtime = false;

    private string $api_url;

    private string $username;
    private string $password;
    private string $client_id;
    private string $client_secret;
    private ?string $token;
    private ?\stdClass $token_obj = null;
    public string $admin_notice_error_message = '';

    public bool $is_error = false;
    public string $error_message = '';
    public ?array $response;
    public string $response_string = '';
    public ?int $response_code;

    public static function instance(): Livewebinar_Api
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        } else {
            self::$_instance->refreshToken();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        if ('local' === wp_get_environment_type()) {
            $this->api_url = "https://api.archiebot.test/api/";
//            gulios@gulios.pl
//            test
//            XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
//            YYYYYYYYYYYYYYYYYYYYYYYYYYYYYY
        } else {
            $this->api_url = "https://api.archiebot.com/api/";
        }

        $this->client_id = get_option('livewebinar_client_id');
        $this->client_secret = get_option('livewebinar_client_secret');

        try {
            $this->token_obj = get_option('livewebinar_token', null);

            $this->refreshToken();

            $this->token = $this->token_obj->access_token ?? null;
        } catch (\Exception $e) {
            $this->token = null;
        }

        add_action('wp_ajax_test_connection', [$this, 'test_connection']);
        add_action('wp_ajax_clear_api_credentials', [$this, 'clear_api_credentials']);
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $params
     * @return bool
     */
    public function send_request(string $path, string $method, array $params = []): bool
    {
        $this->reset_properties();

        if (!isset($params['headers']) || !is_array($params['headers'])) {
            $params['headers'] = [];
        }
        $params['headers']['X-Livewebinar-Plugin-Version'] = LIVEWEBINAR_PLUGIN_VERSION;

        if ($this->is_auth_locked()) {
            $this->is_error = true;
            $this->error_message = $this->get_auth_lock_message();

            return false;
        }

        switch (strtolower($method)) {
            case 'get':
                $response = wp_remote_get($this->api_url . $path, $params);
                break;
            case 'post':
                $response = wp_remote_post($this->api_url . $path, $params);
                break;
            case 'put':
                $response = wp_remote_request($this->api_url . $path, array_merge(['method' => 'PUT'], $params));
                break;
            case 'delete':
                $response = wp_remote_request($this->api_url . $path, array_merge(['method' => 'DELETE'], $params));
                break;
            default:
                $response = null;
                break;
        }

        $this->response_code = (int) wp_remote_retrieve_response_code($response);
        if (is_wp_error($response)) {
            $this->is_error = true;
            $this->error_message = $response->get_error_message();
        } else {
            $this->response = $response;
            if (200 > $this->response_code || 300 < $this->response_code) {
                $this->is_error = true;
                $this->error_message = $this->retrieve_error_message_from_json(wp_remote_retrieve_body($response), $this->response_code);
                if (401 === $this->response_code) {
                    $this->lock_auth_error();
                }
            } else {
                $this->response_string = wp_remote_retrieve_body($response);
            }
        }

        if (get_option('livewebinar_enable_error_logs') && $this->is_error) {
            $log = fopen(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/error_log.log', 'a');
            fwrite($log, date('Y-m-d H:i:s '));
            fwrite($log, strtoupper($method) . ' ');
            fwrite($log, $path . PHP_EOL);
            fwrite($log, print_r($params, true));
            fwrite($log, $this->error_message . PHP_EOL);
            fclose($log);
        }

        if (get_option('livewebinar_enable_response_logs')) {
            $log = fopen(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/response_log.log', 'a');
            fwrite($log, date('Y-m-d H:i:s '));
            fwrite($log, strtoupper($method) . ' ');
            fwrite($log, $path . PHP_EOL);
            fwrite($log, print_r($params, true));
            fwrite($log, $this->response_string . PHP_EOL);
            fclose($log);
        }

        return !$this->is_error;
    }

    /**
     * @param bool $full_obj
     * @return string
     * @throws JsonException
     */
    public function get_token(bool $full_obj = false): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
        ];
        $body = [
            'identifier' => 'livewebinar',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ];

        $this->send_request('auth/login', self::METHOD_POST, [
            'headers' => $headers,
            'body' => $body,
        ]);

        if (!$this->is_error) {
            $token_data = json_decode($this->response_string, false, 512, JSON_THROW_ON_ERROR);
            $token_data->created_at = time();
            $token_data->access_token = $token_data->token;
            unset($token_data->token);

            update_option('livewebinar_token', $token_data);
            $this->token = $token_data->access_token;

            if ($full_obj) {
                return json_encode($token_data, JSON_THROW_ON_ERROR);
            }

            return $this->token;
        }

        throw new \Exception($this->error_message);
    }

    /**
     * @return void
     */
    public function test_connection(): void
    {
        check_ajax_referer('_nonce_livewebinar_security', 'security');

        if ($this->token && !$this->is_error) {
            wp_send_json(__('API connection established properly!', 'livewebinar'));
        } else {
            wp_send_json(__('Error occured: ', 'livewebinar') . $this->error_message);
        }
        wp_die();
    }

    /**
     * @return void
     */
    public function clear_api_credentials(): void
    {
        check_ajax_referer('_nonce_livewebinar_security', 'security');

        delete_option('livewebinar_client_id');
        delete_option('livewebinar_client_secret');
        delete_option('livewebinar_token');
        $this->clear_auth_lock();
        $this->token = null;
        $this->token_obj = null;

        $options['client_id'] = get_option('livewebinar_client_id', '');
        $options['client_secret'] = get_option('livewebinar_client_secret', '');
        $options['token'] = get_option('livewebinar_token', '');

        $result = true;
        foreach ($options as $option) {
            if (!empty($option)) {
                $result = false;
                break;
            }
        }

        if ($result) {
            wp_send_json(['success' => true, 'message' => __('Credentials have been cleared!', 'livewebinar')]);
        } else {
            $options['client_id'] = get_option('livewebinar_client_id', '');
            $options['client_secret'] = get_option('livewebinar_client_secret', '');

            wp_send_json(['success' => false, 'message' => __('Some credentials were not cleared'), 'fields' => $options]);
        }

        wp_die();
    }

    /**
     * @return string
     */
    public function list_widgets(): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
        ];

        $url = 'widgets?limit=500&scope=active&sort=id&order=desc';

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        return $this->string_result();
    }

    /**
     * @param int $widget_id
     * @return string
     */
    public function get_widget(int $widget_id): string
    {
        $key = 'livewebinar_get_widget_' . $widget_id;
        $cached_data = get_transient($key);

        if (false !== $cached_data) {
            return $cached_data;
        }

        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
        ];

        $url = 'widgets/' . $widget_id;

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        $cached_data = $this->string_result();

        if ($this->is_error) {
            delete_transient($key);
            return $cached_data;
        }

        set_transient($key, $cached_data, 300);

        return $cached_data;
    }

    /**
     * @param \WP_Post $post
     * @param array $data
     * @return string
     */
    public function create_widget(\WP_Post $post, array $data): string
    {
        $widget = new Livewebinar_Widget($post, $data);

        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->send_request('widgets', self::METHOD_POST, [
            'headers' => $headers,
            'body' => $widget->fields,
        ]);

        return $this->string_result();
    }

    /**
     * @param \WP_Post $post
     * @param array $data
     * @param int $livewebinar_event_id
     * @return string
     */
    public function update_widget(\WP_Post $post, array $data, int $livewebinar_event_id): string
    {
        $livewebinar_widget = $this->get_widget($livewebinar_event_id);

        if (!empty($livewebinar_widget)) {
            $widget = new Livewebinar_Widget($post, $data);

            $headers = [
                'Accept' => 'application/vnd.archiebot.v1+json',
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $this->send_request('widgets/' . $livewebinar_event_id, self::METHOD_PUT, [
                'headers' => $headers,
                'body' => $widget->fields,
            ]);
        }

        return $this->string_result();
    }

    /**
     * @param \WP_Post $post
     * @param int $livewebinar_event_id
     * @return string
     */
    public function deactivate_widget(\WP_Post $post, int $livewebinar_event_id): string
    {
        return $this->update_widget($post, ['status' => 'inactive'], $livewebinar_event_id);
    }

    /**
     * @param string $name
     * @param int $livewebinar_widget_id
     * @param int $tokens_amount
     * @return string
     */
    public function create_widget_tokens(string $name, int $livewebinar_widget_id, int $tokens_amount): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->send_request('account/widget_tokens/', self::METHOD_POST, [
            'headers' => $headers,
            'body' => [
                'name' => $name,
                'widgets_ids' => [$livewebinar_widget_id],
                'amount' => $tokens_amount,
            ],
        ]);

        return $this->string_result();
    }

    /**
     * @param int $livewebinar_widget_id
     * @return string
     */
    public function get_widget_token_groups(int $livewebinar_widget_id): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->send_request('account/widget_user_tokens/' . $livewebinar_widget_id, self::METHOD_GET, [
            'headers' => $headers,
        ]);

        return $this->string_result();
    }

    /**
     * @param int $livewebinar_widget_token_group_id
     * @return string
     */
    public function delete_widget_token_group(int $livewebinar_widget_token_group_id): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $this->send_request('account/widget_tokens/' . $livewebinar_widget_token_group_id, self::METHOD_DELETE, [
            'headers' => $headers,
        ]);

        return $this->string_result();
    }

    /**
     * @return string
     */
    public function list_forms(): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $url = 'forms';

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        return $this->string_result();
    }

    /**
     * @return string
     */
    public function list_presenters(): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $url = 'presenters';

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        return $this->string_result();
    }

    /**
     * @param int $presenter_id
     * @return string
     */
    public function get_presenter(int $presenter_id): string
    {
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $url = 'presenters/' . $presenter_id;

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        return $this->string_result();
    }

    /**
     * @return array
     */
    public function list_images(): array
    {
        $filtered = [];
        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $url = 'storage?limit=2000';

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        try {
            $obj = json_decode($this->string_result(), true, 512, JSON_THROW_ON_ERROR);
            if (array_key_exists('data', $obj)) {
                $filtered = array_filter($obj['data'], static function ($element) {
                    if (array_key_exists('file_extension', $element) &&
                        array_key_exists('file_size', $element) &&
                        7 * 1024 * 1024 > $element['file_size'] &&
                        in_array($element['file_extension'], Livewebinar_File::$image_extensions, true)) {
                        return true;
                    }

                    return false;
                });
            }
        } catch (\Exception $e) {
            if (get_option('livewebinar_enable_error_logs')) {
                $log = fopen(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/error_log.log', 'a');
                fwrite($log, date('Y-m-d H:i:s ') . 'list images' . PHP_EOL);
                fwrite($log, $e->getMessage() . PHP_EOL);
                fclose($log);
            }
        }

        return $filtered;
    }

    /**
     * @param int $image_id
     * @return Livewebinar_File
     */
    public function get_image(int $image_id): Livewebinar_File
    {
        if (1 > $image_id) {
            return new Livewebinar_File(new \stdClass());
        }

        $key = 'livewebinar_get_image_' . $image_id;
        $cached_data = get_transient($key);

        if (false !== $cached_data) {
            return $cached_data;
        }

        $headers = [
            'Accept' => 'application/vnd.archiebot.v1+json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $url = 'storage/' . $image_id;

        $this->send_request($url, self::METHOD_GET, ['headers' => $headers]);

        try {
            $result = $this->string_result();

            if ($this->is_error) {
                delete_transient($key);
                return new Livewebinar_File(new \stdClass());
            }

            $obj = json_decode($result, false, 512, JSON_THROW_ON_ERROR)->data;
            $file = new Livewebinar_File($obj);

            $cached_data = $file;

            set_transient($key, $cached_data, 300);

        } catch (\Exception $e) {

            $cached_data = new Livewebinar_File(new \stdClass());
            delete_transient($key);

            if (get_option('livewebinar_enable_error_logs')) {
                $log = fopen(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/error_log.log', 'a');
                fwrite($log, date('Y-m-d H:i:s ') . 'get image' . PHP_EOL);
                fwrite($log, $e->getMessage() . PHP_EOL);
                fclose($log);
            }
        }

        return $cached_data;
    }

    /**
     * @param array $array
     * @param int $code
     * @return string
     */
    private function array_search_error_recursive(array $array, int $code = 200): string
    {
        $result = '';
        foreach ($array as $key => $value) {
            if ('exception' === $key) {
                $result = $value;
                break;
            }

            if (is_array($value) && array_key_exists('error', $value) && array_key_exists('message', $value) && $code !== 422) {
                $result = $value['message'];
                break;
            }

            if ('error' === $key && is_array($value) && array_key_exists('message', $value) && $code !== 422) {
                $result = $value['message'];
                break;
            }

            if ('error' === $key && is_array($value) && array_key_exists('errors', $value)) {
                foreach ($value['errors'] as $field => $error) {
                    $result .= $field . ': ' . $error[0] . PHP_EOL;
                }
                break;
            }

            if (is_array($value)) {
                $result = $this->array_search_error_recursive($value);
                if (!empty($result)) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    private function reset_properties(): void
    {
        $this->is_error = false;
        $this->error_message = '';
        $this->response_code = null;
        $this->response = null;
        $this->response_string = '';
    }

    /**
     * @param string $json_string
     * @param int $code
     * @return string
     */
    private function retrieve_error_message_from_json(string $json_string, int $code = 200): string
    {
        try {
            $data = json_decode($json_string, true, 512, JSON_THROW_ON_ERROR);
            $error_message = $this->array_search_error_recursive($data, $code);

            if (empty($error_message)) {
                $error_message = __('Could not find error message, original response: ', 'livewebinar') . $json_string;
            }
        } catch (JsonException $e) {
            $error_message = __('JSON decode error: ', 'livewebinar') . $e->getMessage() . ' | ' . __('Original response: ', 'livewebinar') . $json_string;
        }

        return $error_message;
    }

    /**
     * @return string
     */
    private function string_result(): string
    {
        if (!$this->is_error) {
            $result = $this->response_string;
        } else {
            $result = $this->error_message;
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function is_auth_locked(): bool
    {
        if (self::$auth_lock_runtime) {
            return true;
        }

        $lock_until = (int) get_option(self::AUTH_ERROR_OPTION_KEY, 0);

        if (false !== get_transient(self::AUTH_ERROR_TRANSIENT_KEY)) {
            if ($lock_until <= time()) {
                $lock_until = time() + self::AUTH_ERROR_LOCK_TTL;
            }

            update_option(self::AUTH_ERROR_OPTION_KEY, $lock_until);
            delete_transient(self::AUTH_ERROR_TRANSIENT_KEY);
        }

        if ($lock_until > time()) {
            self::$auth_lock_runtime = true;
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    private function lock_auth_error(): void
    {
        self::$auth_lock_runtime = true;
        update_option(self::AUTH_ERROR_OPTION_KEY, time() + self::AUTH_ERROR_LOCK_TTL);
        set_transient(self::AUTH_ERROR_TRANSIENT_KEY, 1, self::AUTH_ERROR_LOCK_TTL);
        delete_option('livewebinar_token');
        $this->token = null;
        $this->token_obj = null;
    }

    /**
     * @return void
     */
    private function clear_auth_lock(): void
    {
        self::clear_global_auth_lock();
    }

    /**
     * @return void
     */
    public static function clear_global_auth_lock(): void
    {
        self::$auth_lock_runtime = false;
        delete_option(self::AUTH_ERROR_OPTION_KEY);
        delete_transient(self::AUTH_ERROR_TRANSIENT_KEY);

        self::purge_plugin_transients();
    }

    /**
     * @return void
     */
    public static function purge_plugin_transients(): void
    {
        global $wpdb;

        $table = $wpdb->options;
        $like = $wpdb->esc_like('_transient_livewebinar_') . '%';
        $timeout_like = $wpdb->esc_like('_transient_timeout_livewebinar_') . '%';

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE option_name LIKE %s OR option_name LIKE %s",
                $like,
                $timeout_like
            )
        );
    }

    /**
     * @return string
     */
    private function get_auth_lock_message(): string
    {
        return __('LiveWebinar API authentication is temporarily disabled after repeated errors. Please verify the plugin credentials.', 'livewebinar');
    }

    /**
     * @return void
     */
    private function refreshToken(): void
    {
        if ($this->is_auth_locked()) {
            return;
        }

        if (empty($this->token_obj) || $this->token_obj->created_at + $this->token_obj->expires_in < time() + 600) {
            try {
                $this->token_obj = json_decode($this->get_token(true), false, 512, JSON_THROW_ON_ERROR);
                $this->clear_auth_lock();
            } catch (\Exception $e) {
                $this->lock_auth_error();
                $this->admin_notice_error_message = sprintf(
                    __('LiveWebinar API authentication failed: %s. Further attempts are temporarily paused.', 'livewebinar'),
                    $e->getMessage()
                );
                add_action('admin_notices', [$this, 'error_notice']);
            }
        }
    }

    /**
     * @return void
     */
    public function error_notice(): void
    {
        ?>
        <div class="error notice">
            <p><?php esc_html(__('Livewebinar error: ', 'livewebinar') . $this->admin_notice_error_message); ?></p>
        </div>
        <?php
    }
}
