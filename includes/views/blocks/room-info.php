<div class="livewebinar-room-info-wrapper">
    <?php if (!empty($widget_data) && !isset($error_message)) : ?>
        <?php if (!empty($attributes['title'])) : ?>
            <div class="livewebinar-room-info-title"><?php echo esc_html($attributes['title']); ?></div>
        <?php endif; ?>
        <table class="livewebinar-room-info-table table">
            <thead></thead>
            <tbody>
            <?php if (isset($attributes) && (!array_key_exists('show_link_only', $attributes) || !$attributes['show_link_only'])): ?>
                <tr>
                    <th><?php esc_html_e('Room name:', 'livewebinar'); ?></th>
                    <td class="bold"><?php esc_html_e($widget_data->name ?? ''); ?></td>
                </tr>
                <?php if (\Livewebinar\Includes\Widget::TYPE_SCHEDULED === $widget_data->type): ?>
                    <?php
                        $start_date = new DateTime();
                        $start_date->setTimestamp($widget_data->start_date);
                    ?>
                    <tr>
                        <th><?php esc_html_e('Starts at:', 'livewebinar'); ?></th>
                        <td><?php echo esc_html($start_date->format('Y-m-d H:i:s') ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Duration:', 'livewebinar'); ?></th>
                        <td><?php echo esc_html(($widget_data->duration ?? '0') . ' ' . __('minutes', 'livewebinar')); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Timezone:', 'livewebinar'); ?></th>
                        <td><?php echo esc_html($widget_data->timezone ?? ''); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($widget_data->agenda)): ?>
                  <tr>
                      <th><?php esc_html_e('Agenda:', 'livewebinar'); ?></th>
                      <td><?php echo wp_kses_post($widget_data->agenda); ?></td>
                  </tr>
                <?php endif; ?>
            <?php endif; ?>
            <tr>
                <th><?php esc_html_e('Join now', 'livewebinar'); ?></th>
                <td><a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_JOIN_URL_BASE . '/' . $widget_data->token); ?>" target="_blank"><?php
                       esc_html_e('Enter the room', 'livewebinar'); ?></a></td>
            </tr>
            </tbody>
        </table>
    <?php elseif (isset($error_message)) : ?>
        <div class="error"><?php echo esc_html($error_message); ?></>
    <?php endif; ?>
</div>
