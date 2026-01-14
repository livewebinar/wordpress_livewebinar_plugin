<div class="livewebinar-embed-room-wrapper">
    <div>
<?php if (!empty($widget) && !isset($error_message)): ?>
    <?php if (!empty($attributes['title'])): ?>
        <div class="livewebinar-embed-room-title"><?php echo esc_html($attributes['title']); ?></div>
    <?php endif; ?>
    <?php if (!empty($embed_code)): ?>
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
                    (function() {
                        let _options = <?php echo json_encode($embed_code['options']); ?>;
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
    <?php endif; ?>
    <?php if (!empty($attributes['show_link']) && !empty($token)): ?>
        <div class="livewebinar-embed-room-link"><a href="<?php echo esc_url(LIVEWEBINAR_PLUGIN_JOIN_URL_BASE . '/' . $token); ?>" target="_blank"><?php
                esc_html_e('Open in new tab', 'livewebinar'); ?></a></div>
    <?php endif; ?>
<?php elseif (isset($error_message)): ?>
    <div class="error"><?php echo esc_html($error_message); ?></div>
<?php endif; ?>
    </div>
</div>
