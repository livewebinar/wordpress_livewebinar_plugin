<?php
    if (isset($_POST['save_livewebinar_settings'])) :
?>
<div id="message" class="notice notice-success is-dismissible">
                        <p><?php esc_html_e( 'Successfully Updated.', 'livewebinar' ); ?></p>
<button type="button" class="notice-dismiss">
    <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'livewebinar' ); ?></span>
</button>
</div>
<?php endif; ?>

<div class="livewebinar-wrap">
    <ul class="livewebinar-nav nav livewebinar-nav-pills nav-pills">
        <li class="active" data-selector="settings-nav">
            <a class="livewebinar-nav-link active show" href="#api" data-bs-toggle="tab" aria-controls="api"
               aria-selected="true">
                <?php esc_html_e('API credentials', 'livewebinar'); ?>
            </a>
        </li>
        <li data-selector="settings-nav">
            <a class="livewebinar-nav-link" href="#settings" data-bs-toggle="tab" aria-controls="settings"
               aria-selected="false">
                <?php esc_html_e('Additional options', 'livewebinar'); ?>
            </a>
        </li>
        <li class="tikitak" data-selector="settings-nav">
            <a class="livewebinar-nav-link" href="#support" data-bs-toggle="tab" aria-controls="support"
               aria-selected="false">
                <?php esc_html_e('Support', 'livewebinar'); ?>
            </a>
        </li>
        <li data-selector="settings-nav">
            <a class="livewebinar-nav-link" href="#logs" data-bs-toggle="tab" aria-controls="logs"
               aria-selected="false">
                <?php esc_html_e('Logs', 'livewebinar'); ?>
            </a>
        </li>
    </ul>
    <form method="POST" action="admin.php?page=livewebinar-settings" autocomplete="off">
        <?php wp_nonce_field( '_livewebinar_settings_update_nonce_action', '_livewebinar_settings_nonce' ); ?>
        <div class="livewebinar-tab-content tab-content">
            <div id="api" class="livewebinar-tab-pane fade show active" role="tabpanel" aria-labelledby="api-tab">
                <div class="livewebinar-row livewebinar-mb-2">
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <label for="livewebinar_client_id" class="livewebinar-control-label">
                                <span class=""><?php esc_html_e('Client ID', 'livewebinar'); ?></span>
                            </label>
                            <input type="password" name="livewebinar_client_id" id="livewebinar_client_id" class="livewebinar-form-control mb-1" autocomplete="off"
                                   value="<?php echo esc_attr(!empty($livewebinar_client_id) ? esc_html($livewebinar_client_id) : ''); ?>">
                            <small href="javascript:void(0);" class="d-inline-block form-text text-muted cursor-pointer toggle-client-id"><?php echo esc_html($show_text); ?></small>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <label for="livewebinar_client_secret" class="livewebinar-control-label">
                                <span class=""><?php _e('Client Secret', 'livewebinar'); ?></span>
                            </label>
                            <input type="password" name="livewebinar_client_secret" id="livewebinar_client_secret" class="livewebinar-form-control mb-1" autocomplete="off"
                                   value="<?php echo !empty($livewebinar_client_secret) ? esc_html($livewebinar_client_secret) : ''; ?>">
                            <small href="javascript:void(0);" class="d-inline-block form-text text-muted cursor-pointer toggle-client-secret"><?php echo esc_html($show_text); ?></small>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <a href="javascript:void(0);" id="livewebinar_clear_api_credentials" class="clear-api-credentials"><?php esc_html_e( 'Clear API credentials', 'livewebinar' ); ?></a>
                        </div>
                    </div>
                </div>

                <p>
                    <?php esc_html_e("If you don't know where to get Client ID and Client Secret from, go", 'livewebinar'); ?>
                    <a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_JOIN_URL_BASE . '/api-apps'); ?>" target="_blank" class="d-inline-block">
                        <span><?php esc_html_e('here', 'livewebinar'); ?></span>
                    </a>.
                </p>
            </div>

            <div id="settings" class="livewebinar-tab-pane fade" role="tabpanel" aria-labelledby="settings-tab">
                <div class="livewebinar-row">
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <div class="input-group">
                                <div class="togglebutton pull-left">
                                    <label for="livewebinar_enable_error_logs">
                                        <input type="checkbox" name="livewebinar_enable_error_logs" id="livewebinar_enable_error_logs"
                                               value="1" <?php echo esc_attr(!empty($livewebinar_enable_error_logs) ? ' checked' : ''); ?>>
                                        <span class="toggle"></span>
                                        <span class="pull-right" style="text-transform: initial;">
                                            <?php esc_html_e('Enable error logs', 'livewebinar' ); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <div class="input-group">
                                <div class="togglebutton pull-left">
                                    <label for="livewebinar_enable_response_logs">
                                        <input type="checkbox" name="livewebinar_enable_response_logs" id="livewebinar_enable_response_logs"
                                               value="1" <?php echo esc_attr(!empty($livewebinar_enable_response_logs) ? ' checked' : ''); ?>>
                                        <span class="toggle"></span>
                                        <span class="pull-right" style="text-transform: initial;">
                                            <?php esc_html_e('Enable response logs (will grow fast)', 'livewebinar' ); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <div class="livewebinar-form-group is-empty">
                            <div class="input-group">
                                <div class="togglebutton pull-left">
                                    <label for="livewebinar_dont_delete_events">
                                        <input type="checkbox" name="livewebinar_dont_delete_events" id="livewebinar_dont_delete_events"
                                               value="1" <?php echo esc_attr(!empty($livewebinar_dont_delete_events) ? ' checked' : ''); ?>>
                                        <span class="toggle"></span>
                                        <span class="pull-right" style="text-transform: initial;">
                                            <?php esc_html_e('Do not deactivate events in LiveWebinar when removing LiveWebinar post', 'livewebinar' ); ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="support" class="livewebinar-tab-pane fade" role="tabpanel" aria-labelledby="support-tab">
            </div>

            <div id="logs" class="livewebinar-tab-pane fade" role="tabpanel" aria-labelledby="logs-tab">
                <?php if (!$livewebinar_enable_error_logs && !$livewebinar_enable_response_logs) : ?>
                    <p><?php esc_html_e('Logs are disabled, enable them in settings', 'livewebinar'); ?></p>
                <?php else: ?>
                    <div class="livewebinar-form-control">
                        <div class="livewebinar-form-group">
                            <?php if ($livewebinar_enable_response_logs) : ?>
                                <a class="btn btn-primary" href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_LOGS_URL . '/' . LIVEWEBINAR_PLUGIN_RESPONSE_LOG_FILENAME); ?>"><?php
                                    esc_html_e('Download response log', 'livewebinar'); ?></a>
                            <?php endif; ?>
                            <?php if ($livewebinar_enable_response_logs || (file_exists($response_logs_path) && filesize($response_logs_path) > 0)) : ?>
                                <a class="btn btn-danger remove-logs" data-type="response" href="javascript:void(0);"><?php
                                    esc_html_e('Remove response logs', 'livewebinar'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="livewebinar-form-control">
                        <div class="form-group">
                            <?php if($livewebinar_enable_error_logs) : ?>
                                <a class="btn btn-primary" href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_LOGS_URL . '/' . LIVEWEBINAR_PLUGIN_ERROR_LOG_FILENAME); ?>"><?php
                                    esc_html_e('Download error log', 'livewebinar'); ?></a>
                            <?php endif; ?>
                            <?php if ($livewebinar_enable_error_logs || (file_exists($error_logs_path) && filesize($error_logs_path) > 0)) : ?>
                                <a class="btn btn-danger remove-logs" data-type="error" href="javascript:void(0);"><?php
                                    esc_html_e('Remove error logs', 'livewebinar'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="submit" id="submit-settings">
                <input type="submit" name="save_livewebinar_settings" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'livewebinar' ); ?>">
                <a href="javascript:void(0);" class="button button-primary test-api-connection"><?php esc_html_e( 'Test API Connection', 'livewebinar' ); ?></a>
            </div>
            <p>
                <?php esc_html_e("Don’t have account yet?", 'livewebinar'); ?>
                <a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_LIVEWEBINAR_URL_BASE . '/affiliate/wordpress'); ?>" target="_blank" class="d-inline-block">
                    <span class="livewebinar-new-feature ms-1"><?php esc_html_e('Create FREE account', 'livewebinar'); ?></span>
                </a>.
            </p>
            <p>
                <?php esc_html_e('Have questions?', 'livewebinar'); ?>
                <a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_LIVEWEBINAR_URL_BASE . '/about/contact'); ?>" target="_blank">
                    <?php esc_html_e('Contact us!', 'livewebinar'); ?>
                </a>
            </p>
        </div>
    </form>
</div>

<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e('Test Connection', 'livewebinar'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
                <p data-content="testConnectionModal"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php esc_html_e('Close', 'livewebinar'); ?></button>
            </div>
        </div>
    </div>
</div>
