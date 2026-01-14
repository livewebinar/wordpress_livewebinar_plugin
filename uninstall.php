<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('livewebinar_client_id');
delete_option('livewebinar_client_secret');
delete_option('livewebinar_token');
delete_option('livewebinar_enable_error_logs');
delete_option('livewebinar_enable_response_logs');
