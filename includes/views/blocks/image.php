<div class="livewebinar-image-storage-wrapper">
    <?php if (!empty($image) && !isset($error_message)) : ?>
        <?php if (!empty($attributes['title'])) : ?>
            <div class="livewebinar-image-storage-title"><?php echo esc_html($attributes['title']); ?></div>
        <?php endif; ?>
        <div class="livewebinar-image-storage-image"><img src="<?php echo esc_url($image->url); ?>" alt="<?php echo esc_attr($image->get_filename()); ?>"<?php
            echo !empty($attributes['width']) ? ' width="' . esc_attr($attributes['width']) . '"' : '';
            echo !empty($attributes['height']) ? ' height="' . esc_attr($attributes['height']) . '"' : '';
        ?> /></div>
        <?php if (!empty($attributes['caption'])) : ?>
            <div class="livewebinar-image-storage-caption"><?php echo esc_html($attributes['caption']); ?></div>
        <?php endif; ?>
    <?php elseif (isset($error_message)) : ?>
        <div class="error"><?php echo esc_html($error_message); ?></>
    <?php endif; ?>
</div>
