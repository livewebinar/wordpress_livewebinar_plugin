<?php

/**
 * @copyright  ArchieBot by RTCLab Sp. z o.o.
 * @link       https://www.rtclab.com/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    LiveWebinar plugin
 *
 * Plugin Name: LiveWebinar
 * Plugin URI: https://www.livewebinar.com/
 * Description: Webinar Software. Best Platform for Webinars - LiveWebinar.com
 * Version: 2.2.0
 * Requires at least: 5.8.4
 * Requires PHP: 7.4
 * Author: RTCLab <admin@rtclab.com>
 * Author URI: https://www.rtclab.com/
 */

if (!defined( 'ABSPATH' )) {
    exit;
}

define('LIVEWEBINAR_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
define('LIVEWEBINAR_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));
define('LIVEWEBINAR_PLUGIN_NAME', plugin_basename( __FILE__ ));
const LIVEWEBINAR_PLUGIN_ADMIN_JS_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'assets/admin/js';
const LIVEWEBINAR_PLUGIN_PUBLIC_JS_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'assets/public/js';
const LIVEWEBINAR_PLUGIN_PUBLIC_CSS_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'assets/public/css';
const LIVEWEBINAR_PLUGIN_VENDOR_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'vendor';
const LIVEWEBINAR_PLUGIN_ASSETS_VENDOR_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'assets/vendor';
const LIVEWEBINAR_PLUGIN_INCLUDES_PATH = LIVEWEBINAR_PLUGIN_DIR_PATH . 'includes';
const LIVEWEBINAR_PLUGIN_VIEWS_PATH = LIVEWEBINAR_PLUGIN_DIR_PATH . 'includes/views';
const LIVEWEBINAR_PLUGIN_TEMPLATES_PATH = LIVEWEBINAR_PLUGIN_DIR_PATH . 'templates';
const LIVEWEBINAR_PLUGIN_LOGS_PATH = LIVEWEBINAR_PLUGIN_DIR_PATH . 'logs';
const LIVEWEBINAR_PLUGIN_LOGS_URL = LIVEWEBINAR_PLUGIN_DIR_URL . 'logs';
const LIVEWEBINAR_PLUGIN_RESPONSE_LOG_FILENAME = 'response_log.log';
const LIVEWEBINAR_PLUGIN_ERROR_LOG_FILENAME = 'error_log.log';
define('LIVEWEBINAR_PLUGIN_LANGUAGE_PATH', trailingslashit(basename(LIVEWEBINAR_PLUGIN_DIR_PATH)) . 'i18n/');

const LIVEWEBINAR_PLUGIN_VERSION = '2.2.0';

require_once(LIVEWEBINAR_PLUGIN_INCLUDES_PATH . '/Bootstrap.php');

function check_plugin_version(): void
{
    $current_version = LIVEWEBINAR_PLUGIN_VERSION;
    $latest_version = file_get_contents('https://app.livewebinar.com/wordpress_plugin_version');
    if ($latest_version && version_compare($current_version, $latest_version, '<')) {
        echo '<div class="notice notice-error"><p><strong style="color: red;">LiveWebinar Update Required:</strong> Please update your plugin to version ' . $latest_version . ' for compatibility.</p></div>';
    }
}
add_action('admin_notices', 'check_plugin_version');


add_action( 'plugins_loaded', 'Livewebinar\Includes\Bootstrap::instance', 99 );

if ('local' === wp_get_environment_type()) {
    add_filter('https_ssl_verify', '__return_false');

    define('LIVEWEBINAR_PLUGIN_JOIN_URL_BASE', 'https://app.livewebinar.test');
    define('LIVEWEBINAR_PLUGIN_LIVEWEBINAR_URL_BASE', 'https://www.livewebinar.test');
} else {
    define('LIVEWEBINAR_PLUGIN_JOIN_URL_BASE', 'https://app.livewebinar.com');
    define('LIVEWEBINAR_PLUGIN_LIVEWEBINAR_URL_BASE', 'https://www.livewebinar.com');
}

register_activation_hook(__FILE__, 'Livewebinar\Includes\Bootstrap::activate');
register_deactivation_hook(__FILE__, 'Livewebinar\Includes\Bootstrap::deactivate');
