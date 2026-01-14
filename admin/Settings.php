<?php

namespace Livewebinar\Admin;

class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'plugin_livewebinar_admin_menus'], 9);
        add_action('wp_ajax_remove_log_file', [$this, 'remove_log_file']);
    }

    /**
     * @return void
     */
    public function remove_log_file(): void
    {
        check_ajax_referer( '_nonce_livewebinar_security', 'security' );
        $filename = sanitize_text_field(filter_input(INPUT_POST, 'filename'));

        if (in_array($filename, [LIVEWEBINAR_PLUGIN_RESPONSE_LOG_FILENAME, LIVEWEBINAR_PLUGIN_ERROR_LOG_FILENAME], true)
            && false !== file_put_contents(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/' . $filename, '')) {
            wp_send_json(__('Log file contents removed', 'livewebinar'));
        }
        wp_die();
    }

    /**
     * Creates plugin menu in backend
     *
     * @return void
     */
    public function plugin_livewebinar_admin_menus(): void
    {
        add_submenu_page( 'edit.php?post_type=livewebinar-post',
            __('Settings', 'livewebinar'),
            __('Settings', 'livewebinar'),
            'manage_options',
            'livewebinar-settings',
            [
                $this,
                'livewebinar_admin_settings',
            ]
        );
    }

    /**
     * Setting page
     *
     * @return void
     */
    public function livewebinar_admin_settings(): void
    {
        $response_logs_path = LIVEWEBINAR_PLUGIN_LOGS_PATH . '/' . LIVEWEBINAR_PLUGIN_RESPONSE_LOG_FILENAME;
        $error_logs_path = LIVEWEBINAR_PLUGIN_LOGS_PATH . '/' . LIVEWEBINAR_PLUGIN_ERROR_LOG_FILENAME;
        if (isset($_POST['save_livewebinar_settings'])) {
            check_admin_referer('_livewebinar_settings_update_nonce_action', '_livewebinar_settings_nonce');

            $livewebinar_client_id_old = get_option('livewebinar_client_id', '');
            $livewebinar_client_secret_old = get_option('livewebinar_client_secret', '');

            $livewebinar_client_id = sanitize_text_field(filter_input(INPUT_POST, 'livewebinar_client_id'));
            $livewebinar_client_secret = sanitize_text_field(filter_input(INPUT_POST, 'livewebinar_client_secret'));
            $livewebinar_enable_error_logs = !is_null(filter_input(INPUT_POST, 'livewebinar_enable_error_logs'));
            $livewebinar_enable_response_logs = !is_null(filter_input(INPUT_POST, 'livewebinar_enable_response_logs'));
            $livewebinar_dont_delete_events = !is_null(filter_input(INPUT_POST, 'livewebinar_dont_delete_events'));

            update_option('livewebinar_client_id', $livewebinar_client_id);
            update_option('livewebinar_client_secret', $livewebinar_client_secret);
            update_option('livewebinar_enable_error_logs', $livewebinar_enable_error_logs);
            update_option('livewebinar_enable_response_logs', $livewebinar_enable_response_logs);
            update_option('livewebinar_dont_delete_events', $livewebinar_dont_delete_events);

            \Livewebinar\Admin\Livewebinar_Api::clear_global_auth_lock();

            if ($livewebinar_client_id !== $livewebinar_client_id_old || $livewebinar_client_secret !== $livewebinar_client_secret_old) {
                delete_option('livewebinar_token');
            }

        } else {

            $livewebinar_client_id = get_option('livewebinar_client_id', '');
            $livewebinar_client_secret = get_option('livewebinar_client_secret', '');
            $livewebinar_enable_error_logs = get_option('livewebinar_enable_error_logs', false);
            $livewebinar_enable_response_logs = get_option('livewebinar_enable_response_logs', false);
            $livewebinar_dont_delete_events = get_option('livewebinar_dont_delete_events', false);
        }

        if ('local' === wp_get_environment_type()) {
            $livewebinar_client_id = empty($livewebinar_client_id) ? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' : $livewebinar_client_id;
            $livewebinar_client_secret = empty($livewebinar_client_secret) ? 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYY' : $livewebinar_client_secret;
        }

        $show_text = __('Show', 'livewebinar');

        require_once(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/settings.php');
    }
}
