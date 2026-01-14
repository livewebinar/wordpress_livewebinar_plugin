<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$no_post = false;
$fields = get_post_meta($post->ID, '_livewebinar_event_post_details', true);
$embed_room = get_post_meta($post->ID, '_livewebinar_event_post_embed_room', true);
$livewebinar_event_id = get_post_meta($post->ID, '_livewebinar_event_post_event_id', true);
$livewebinar_event_token = get_post_meta($post->ID, '_livewebinar_event_post_token', true);
if (!empty($livewebinar_event_id) && is_numeric($livewebinar_event_id) && $livewebinar_event_id > 0) {
    $livewebinar_widget = \Livewebinar\Admin\Livewebinar_Api::instance()->get_widget($livewebinar_event_id);
    $start_date = $fields['start_date'];
    if (!\Livewebinar\Admin\Livewebinar_Api::instance()->is_error) {
        try {
            $widget_object = json_decode($livewebinar_widget, false, 512, JSON_THROW_ON_ERROR)->data;
            $current_user = wp_get_current_user();
        } catch (\JsonException $e) {
            echo '<div class="error">' . esc_html(__('Error occurred while decoding JSON response from API:', 'livewebinar') . ' ' . $e->getMessage()) . '</div>';
        }
    } else {
        echo '<div class="error">' . esc_html(\Livewebinar\Admin\Livewebinar_Api::instance()->error_message) . '</div>';
    }
} else {
    $no_post = true;
}

$embed_code = ['html' => '', 'options' => '', 'url' => ''];
if ($embed_room) {
    $embed_code = \Livewebinar\Includes\Widget::instance()->get_embed_code(
        $livewebinar_event_token,
        current_user_can('administrator') ? ($widget_object->roles->host ?? '') : '',
        $current_user->nickname ?? '',
        $current_user ? get_avatar_url($current_user->ID, 64) : '',
        false !== $livewebinar_event_id ? $livewebinar_event_id : null
    );
}

$presenters = [];

if (!empty($fields['presenters'])) {
    foreach ($fields['presenters'] as $presenter_id) {
        $presenter = \Livewebinar\Admin\Livewebinar_Api::instance()->get_presenter($presenter_id);
        if (!\Livewebinar\Admin\Livewebinar_Api::instance()->is_error) {
            try {
                $presenter_object = json_decode($presenter, false, 512, JSON_THROW_ON_ERROR)->data;
                $presenters[$presenter_id] = $presenter_object->first_name . ' ' . $presenter_object->last_name;
            } catch (\JsonException $e) {
                echo '<div class="error">' . esc_html(__('Error occurred while decoding JSON response from API:', 'livewebinar') . ' ' . $e->getMessage()) . '</div>';
            }
        }
    }
}

if (!function_exists('wp_is_block_theme') || !wp_is_block_theme()) {
    get_header();
} else {
    ?>
    <!doctype html>
    <html <?php language_attributes() ?>><head>
    <?php
    wp_head();
    ?>
    </head><body <?php body_class() ?>>
    <?php
}

while (have_posts()) {
    the_post();

    if (!$no_post):
    ?>

        <div class="livewebinar-event-post-wrapper container mt-4 ">
            <div class="livewebinar-row">
                <div class="col-lg-12">
                    <div class="livewebinar-event-post-header mb-3">
                        <h1><?php echo $post->post_title; ?></h1>
                    </div>
                </div>
            </div>
            <div class="livewebinar-row">
                <div class="col-lg-12">
                    <div class="livewebinar-row">

                        <?php if ($embed_room): ?>
                        <div class="col-lg-7">
                            <div class="livewebinar-event-post-embed" data-selector="livewebinar-event-post-embed">
                                <button class="btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top" data-selector="full-screen-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                        <path fill="#fff" d="M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z"/>
                                    </svg>
                                </button>
                                <button class="btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top" data-selector="exit-full-screen-button" style="display: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                        <path fill="#fff" d="M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z"/>
                                    </svg>
                                </button>
                                <?php echo wp_kses_post($embed_code['html']); ?>
                                <script type='text/javascript'>
                                    let _options = <?php echo json_encode($embed_code['options']); ?>;
                                    (function() {
                                        !function(i) {
                                            i.Widget=function(c) {
                                                'function'==typeof c&&i.Widget.__cbs.push(c),i.Widget.initialized&&(i.Widget.__cbs.forEach(function(i){i()}),i.Widget.__cbs=[])
                                            },i.Widget__cbs=[]
                                        }(window);
                                        let ab = document.createElement('script');
                                        ab.type = 'text/javascript';
                                        ab.async = true;
                                        ab.src = '<?php echo esc_url($embed_code['url']);?>'+'/em?t='+_options['_license_key']+'&'+
                                            Object.keys(_options).reduce(function(a,k){
                                                a.push(k+'='+encodeURIComponent(_options[k]));
                                                return a;
                                            },[]).join('&');
                                        let s = document.getElementsByTagName('script')[0];
                                        s.parentNode.insertBefore(ab, s);
                                    })();
                                </script>
                                <button class="btn livewebinar-btn-fullscreen" data-selector="full-screen-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                        <path fill="#fff" d="M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z"/>
                                    </svg>
                                </button>
                                <button class="btn livewebinar-btn-fullscreen" data-selector="exit-full-screen-button" style="display: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                        <path fill="#fff" d="M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php elseif (has_post_thumbnail()) : ?>
                            <div class="col-lg-7">
                                <div class="livewebinar-event-post-embed" data-selector="livewebinar-event-post-embed">
                                    <button class="btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top" data-selector="full-screen-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                            <path fill="#fff" d="M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z"/>
                                        </svg>
                                    </button>
                                    <button class="btn livewebinar-btn-fullscreen livewebinar-btn-fullscreen-top" data-selector="exit-full-screen-button" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                            <path fill="#fff" d="M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z"/>
                                        </svg>
                                    </button>
                                    <?php the_post_thumbnail(); ?>
                                    <button class="btn livewebinar-btn-fullscreen" data-selector="full-screen-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                            <path fill="#fff" d="M136 32h-112C10.75 32 0 42.75 0 56v112C0 181.3 10.75 192 24 192C37.26 192 48 181.3 48 168V80h88C149.3 80 160 69.25 160 56S149.3 32 136 32zM424 32h-112C298.7 32 288 42.75 288 56c0 13.26 10.75 24 24 24h88v88C400 181.3 410.7 192 424 192S448 181.3 448 168v-112C448 42.75 437.3 32 424 32zM136 432H48v-88C48 330.7 37.25 320 24 320S0 330.7 0 344v112C0 469.3 10.75 480 24 480h112C149.3 480 160 469.3 160 456C160 442.7 149.3 432 136 432zM424 320c-13.26 0-24 10.75-24 24v88h-88c-13.26 0-24 10.75-24 24S298.7 480 312 480h112c13.25 0 24-10.75 24-24v-112C448 330.7 437.3 320 424 320z"/>
                                        </svg>
                                    </button>
                                    <button class="btn livewebinar-btn-fullscreen" data-selector="exit-full-screen-button" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18">
                                            <path fill="#fff" d="M136 320h-112C10.75 320 0 330.7 0 344c0 13.25 10.75 24 24 24H112v88C112 469.3 122.7 480 136 480S160 469.3 160 456v-112C160 330.7 149.3 320 136 320zM312 192h112C437.3 192 448 181.3 448 168c0-13.26-10.75-24-24-24H336V56C336 42.74 325.3 32 312 32S288 42.74 288 56v112C288 181.3 298.7 192 312 192zM136 32C122.7 32 112 42.74 112 56V144H24C10.75 144 0 154.7 0 168C0 181.3 10.75 192 24 192h112C149.3 192 160 181.3 160 168v-112C160 42.74 149.3 32 136 32zM424 320h-112C298.7 320 288 330.7 288 344v112c0 13.25 10.75 24 24 24s24-10.75 24-24V368h88c13.25 0 24-10.75 24-24C448 330.7 437.3 320 424 320z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="livewebinar-event-post-details <?php echo esc_attr($embed_room || has_post_thumbnail() ? 'col-lg-5' : 'col-lg-12'); ?>">

                            <p class="simply-countdown" id="simply-countdown"></p>

                            <div class="livewebinar-title">
                                <?php esc_html_e('Details', 'livewebinar'); ?>
                            </div>

                            <div class="livewebinar-event-post-details-content livewebinar-tab-content tab-content">
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Widget name', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php echo $fields['event_name']; ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Widget token', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php echo esc_html($livewebinar_event_token); ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Event type', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php esc_html_e('Scheduled', 'livewewbinar'); ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Start date', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php echo esc_html($fields['start_date']); ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Timezone', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php echo esc_html($fields['timezone']); ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Duration', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><strong><?php echo esc_html($fields['duration'] . ' ' . __('minutes', 'livewebinar')); ?></strong></span>
                                </div>
                                <div class="livewebinar-event-post-details-content-row row mb-3">
                                    <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Agenda', 'livewebinar'); ?>:</span>
                                    <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><?php
                                        $agenda_html = html_entity_decode($fields['agenda']);
                                        echo wp_kses_post($agenda_html); ?></span>
                                </div>
                                <?php if (!empty($presenters)) : ?>
                                    <div class="livewebinar-event-post-details-content-row row mb-3">
                                        <span class="livewebinar-event-post-details-label <?php echo $embed_room || has_post_thumbnail() ? 'col-5 p-0' : 'col-5 col-sm-3 px-0 px-sm-3' ?> text-right"><?php esc_html_e('Presenters', 'livewebinar'); ?>:</span>
                                        <span class="livewebinar-event-post-details-content <?php echo $embed_room || has_post_thumbnail() ? 'col-7' : 'col-7 col-sm-3' ?>"><?php
                                            echo esc_html(implode(', ', $presenters)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-4 text-center">
                                    <a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_JOIN_URL_BASE . '/' . $livewebinar_event_token); ?>" class="btn btn-primary livewebinar-btn"
                                       target="_blank"><?php esc_html_e('Open in new window', 'livewebinar'); ?></a>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <?php
        else:
    ?>
        <div class="livewebinar-event-post-wrapper container">
            <div class="livewebinar-row">
                <div class="livewebinar-event-post-header col-xl-12">
                    <h1><?php esc_html_e('No widget bound to the post', 'livewebinar') ?></h1>
                </div>
            </div>
        </div>
    <?php
        endif;
}

if (!function_exists('wp_is_block_theme') || !wp_is_block_theme()) {
    get_footer();
} else {
    wp_footer();
    echo '</body></html>';
}
