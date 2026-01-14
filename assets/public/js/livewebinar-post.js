"use strict";

(function ($) {
    $(document).ready( function() {
        let video = $('[data-selector="livewebinar-event-post-embed"]');
        let fullScreenButton = $('[data-selector="full-screen-button"]');
        let exitFullScreenButton = $('[data-selector="exit-full-screen-button"]');

        fullScreenButton.on('click', function() {
            openFullScreen();
        });
        exitFullScreenButton.on('click', function() {
            closeFullScreen();
        });

        function openFullScreen () {
            video.addClass('livewebinar-full-screen');
            fullScreenButton.hide();
            exitFullScreenButton.show();
        }

        function closeFullScreen () {
            video.removeClass('livewebinar-full-screen');
            exitFullScreenButton.hide();
            fullScreenButton.show();
        }

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                closeFullScreen();
            }
        });
    });
})(jQuery);
