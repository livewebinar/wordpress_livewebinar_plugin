<div class="livewebinar-embed-form-wrapper">
    <?php if (!empty($attributes['title'])) : ?>
        <div class="livewebinar-embed-form-title"><?php echo esc_html($attributes['title']); ?></div>
    <?php endif; ?>

    <?php if (!empty($embed_code) && !isset($error_message)) : ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $embed_code; ?>
    <?php elseif (isset($error_message)) : ?>
        <div class="error"><?php echo esc_html($error_message); ?></div>
    <?php endif; ?>
</div>
