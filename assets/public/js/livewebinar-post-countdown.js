"use strict";

(function ($) {
    $(document).ready(function() {
        simplyCountdown('#simply-countdown', {
            year: livewebinar_post_countdown_data.year, // required
            month: livewebinar_post_countdown_data.month, // required
            day: livewebinar_post_countdown_data.day, // required
            hours: livewebinar_post_countdown_data.hour, // Default is 0 [0-23] integer
            minutes: livewebinar_post_countdown_data.minute, // Default is 0 [0-59] integer
            seconds: livewebinar_post_countdown_data.second, // Default is 0 [0-59] integer
            words: { //words displayed into the countdown
                days: {singular: 'day', plural: 'days'},
                hours: {singular: 'hour', plural: 'hours'},
                minutes: {singular: 'minute', plural: 'minutes'},
                seconds: {singular: 'second', plural: 'seconds'}
            },
            plural: true, //use plurals
            inline: false, //set to true to get an inline basic countdown like : 24 days, 4 hours, 2 minutes, 5 seconds
            inlineClass: 'simply-countdown-inline', //inline css span class in case of inline = true
            // in case of inline set to false
            enableUtc: false,
            onEnd: function () {
                // your code
                return;
            },
            refresh: 1000, //default refresh every 1s
            sectionClass: 'simply-section', //section css class
            amountClass: 'simply-amount', // amount css class
            wordClass: 'simply-word', // word css class
            zeroPad: false,
            countUp: false // enable count up if set to true
        });
    });
})(jQuery);