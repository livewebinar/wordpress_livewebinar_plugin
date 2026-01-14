<?php

namespace Livewebinar\Includes;

use Livewebinar\Admin\Event_Post;
use Livewebinar\Admin\Livewebinar_Api;
use Livewebinar\Admin\Settings;

class Bootstrap
{
    private static ?Bootstrap $_instance = null;

    /**
     * @return Bootstrap
     */
    public static function instance(): Bootstrap
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        require_once(LIVEWEBINAR_PLUGIN_DIR_PATH . '/vendor/autoload.php');

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('plugin_action_links', array($this, 'action_links'), 10, 2);
        add_action('wp_print_styles', [$this, 'add_styles']);
        //method 3: https://getridbug.com/wordpress/wp_editor-visual-tab-in-meta-box-doesnt-show-content/
        add_filter(
            'wp_default_editor',
            function () {
                return 'html';
            }
        );

        new Settings();
        Livewebinar_Api::instance();
        Shortcodes::instance();
        Blocks::instance();
        Event_Post::instance();

        $this->load_plugin_textdomain();
    }

    /**
     * @param string $hook
     * @return void
     */
    public function enqueue_admin_scripts(string $hook): void
    {
        wp_register_script('livewebinar-admin-js', LIVEWEBINAR_PLUGIN_ADMIN_JS_URL . '/livewebinar-admin.js', [
            'jquery',
        ], LIVEWEBINAR_PLUGIN_VERSION, true);

        // necessary for object registration
        wp_localize_script('livewebinar-admin-js', 'livewebinar_admin_js', [
            'ajax_url'      => admin_url('admin-ajax.php'),
            'livewebinar_security' => wp_create_nonce( "_nonce_livewebinar_security"),
            'show_text' => __('Show', 'livewebinar'),
            'hide_text' => __('Hide', 'livewebinar'),
        ]);

        wp_enqueue_script('livewebinar-admin-js');

        wp_enqueue_style('livewebinar-fontawesome-css', LIVEWEBINAR_PLUGIN_PUBLIC_CSS_URL . '/fontawesome-pro-6.1.1-web/css/all.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_style('livewebinar-layouts-icons-css', LIVEWEBINAR_PLUGIN_PUBLIC_CSS_URL . '/layouts-icons.css', [], LIVEWEBINAR_PLUGIN_VERSION);

        wp_register_script('livewebinar-bootstrap-js', LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL . '/bootstrap/bootstrap.min.js', [], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_script('livewebinar-bootstrap-js');

        wp_register_script('livewebinar-bootstrap-datetimepicker-js',
            LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL . '/bootstrap/bootstrap-datetimepicker.min.js', ['moment'], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_script('livewebinar-bootstrap-datetimepicker-js');

        wp_enqueue_style('livewebinar-bootstrap', LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL . '/bootstrap/bootstrap.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_style('livewebinar-bootstrap-datetimepicker',
            LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL . '/bootstrap/bootstrap-datetimepicker.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);

        wp_register_script('livewebinar-select2-js', LIVEWEBINAR_PLUGIN_VENDOR_URL . '/select2/select2/dist/js/select2.full.js', [], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_script('livewebinar-select2-js');

        wp_enqueue_style('livewebinar-select2-css', LIVEWEBINAR_PLUGIN_VENDOR_URL . '/select2/select2/dist/css/select2.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);

        wp_enqueue_style('livewebinar-select2-bootstrap-theme-css',
            LIVEWEBINAR_PLUGIN_VENDOR_URL . '/ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);

        wp_enqueue_style('livewebinar-main-style', LIVEWEBINAR_PLUGIN_PUBLIC_CSS_URL . '/livewebinar.css', [], LIVEWEBINAR_PLUGIN_VERSION);
        global $post_type;

        if (Event_Post::instance()->get_post_type() === $post_type) {
            wp_register_script('livewebinar-post-edit-js', LIVEWEBINAR_PLUGIN_ADMIN_JS_URL . '/livewebinar-post-edit.js', ['wp-editor'], LIVEWEBINAR_PLUGIN_VERSION);

            wp_localize_script('livewebinar-post-edit-js', 'livewebinar_data', [
                'ajax_url'      => admin_url('admin-ajax.php'),
                'livewebinar_security' => wp_create_nonce( "_nonce_livewebinar_security"),
            ]);

            wp_enqueue_script('livewebinar-post-edit-js');
            
        }
    }

    /**
     * @return void
     */
    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain('livewebinar', false, LIVEWEBINAR_PLUGIN_LANGUAGE_PATH);
    }

    /**
     * @return void
     */
    public function enqueue_scripts(): void
    {
        global $post_type, $post;

        wp_register_script('livewebinar-post-js', LIVEWEBINAR_PLUGIN_PUBLIC_JS_URL . '/livewebinar-post.js', ['jquery'], LIVEWEBINAR_PLUGIN_VERSION);
        wp_register_script('livewebinar-simply-countdown-min-js', LIVEWEBINAR_PLUGIN_PUBLIC_JS_URL . '/simplyCountdown.min.js', ['jquery'], LIVEWEBINAR_PLUGIN_VERSION);
        wp_register_script('livewebinar-post-countdown-js',LIVEWEBINAR_PLUGIN_PUBLIC_JS_URL . '/livewebinar-post-countdown.js', ['jquery', 'livewebinar-simply-countdown-min-js'], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_script('livewebinar-post-js');

        if (Event_Post::instance()->get_post_type() === $post_type) {
            $start_date_time = new \DateTime();

            if ($post) {
                $post_data = get_post_meta($post->ID, '_livewebinar_event_post_details', true);

                try {
                    if (is_array($post_data) && array_key_exists('start_date', $post_data)) {
                        $start_date = strtotime($post_data['start_date']);
                        $start_date_time->setTimestamp($start_date);
                    }
                } catch (\Exception $e) {
                }
            }

            wp_localize_script('livewebinar-post-js', 'livewebinar_post_countdown_data', [
                'year' => $start_date_time->format('Y'),
                'month' => $start_date_time->format('m'),
                'day' => $start_date_time->format('d'),
                'hour' => $start_date_time->format('H'),
                'minute' => $start_date_time->format('i'),
                'second' => $start_date_time->format('s'),
            ]);

            wp_enqueue_script('livewebinar-simply-countdown-min-js');
            wp_enqueue_script('livewebinar-post-countdown-js');
        }
    }

    /**
     * @return void
     */
    public function add_styles(): void
    {
        wp_enqueue_style('livewebinar-bootstrap', LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL . '/bootstrap/bootstrap.min.css', [], LIVEWEBINAR_PLUGIN_VERSION);
        wp_enqueue_style('livewebinar-main-style', LIVEWEBINAR_PLUGIN_PUBLIC_CSS_URL . '/livewebinar.css', [], LIVEWEBINAR_PLUGIN_VERSION);
    }

    /**
     * Add Action links to plugins page.
     *
     * @param $actions
     * @param $pluginFile
     *
     * @return array
     */
    public function action_links( $actions, $pluginFile ) {
        static $plugin;

        if ( ! isset( $plugin ) ) {
            $plugin = LIVEWEBINAR_PLUGIN_NAME;
        }

        if ( $plugin === $pluginFile ) {
            $settings = array( 'settings' => '<a href="' . admin_url('edit.php?post_type=livewebinar-post&page=livewebinar-settings') . '">' . __('Settings', 'livewebinar') . '</a>' );

            $actions = array_merge( $settings, $actions );
        }

        return $actions;
    }

    /**
     * Fired on plugin activation.
     *
     * @return void
     */
    public static function activate(): void
    {
        require_once(LIVEWEBINAR_PLUGIN_DIR_PATH . '/admin/Event_Post.php');
        \Livewebinar\Admin\Event_Post::instance()->register_post_types();
        delete_option('livewebinar_token');
        add_option('livewebinar_enable_error_logs', false);
        add_option('livewebinar_enable_response_logs', false);
        file_put_contents(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/' . LIVEWEBINAR_PLUGIN_RESPONSE_LOG_FILENAME, '');
        file_put_contents(LIVEWEBINAR_PLUGIN_LOGS_PATH . '/' . LIVEWEBINAR_PLUGIN_ERROR_LOG_FILENAME, '');

        flush_rewrite_rules(true);
    }

    /**
     * Fired on plugin deactivation.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        delete_option('livewebinar_client_id');
        delete_option('livewebinar_client_secret');
        delete_option('livewebinar_token');
        flush_rewrite_rules(true);
    }
}
