(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    function get_cookie_names() {
        return document.cookie.split(/=[^;]*(?:;\s*|$)/);
    }

    function delete_segment_4_wp_cookies() {
        let pattern = new RegExp(/^segment_4_wp_/);
        let cookie_names = get_cookie_names();
        for (let i = 0; i < cookie_names.length; i++) {
            let cookie_name = cookie_names[i];
            if (pattern.test(cookie_name)) {
                Cookies.set(cookie_name, '');
                Cookies.remove(cookie_name);

            }
        }

    }

    function delete_cookie(event) {


        let reg_1 = new RegExp(/^segment_4_wp_/);
        let reg_2 = event;
        let reg_3 = '_';

        let pattern = new RegExp(reg_1.source + reg_2 + reg_3);

        let cookie_names = get_cookie_names();
        for (let i = 0; i < cookie_names.length; i++) {
            if (pattern.test(cookie_names[i])) {
                let cookie_name = cookie_names[i];
                Cookies.set(cookie_name, '');
                Cookies.remove(cookie_name);
            }
        }
    }

    $(function () {

        analytics.ready(function () {

            analytics.on('track', function (event, properties, options) {

                delete_cookie(event);

                if (wp_ajax.custom_js_events.includes(event)) {

                    $.ajax({
                        cache: false,
                        type: "POST",
                        url: wp_ajax.ajax_url,
                        data: {
                            'action': 'public_ajax_track',
                            'nonce': wp_ajax._nonce,
                            'event': event,
                            'properties': properties
                        },
                        success: function (response) {
                            if(response) {
                                if ('user_id' in response.data) {
                                    if (response.data.event==='identify'){
                                        let user_id = response.data.user_id;
                                        let traits = response.data.traits;
                                        analytics.identify(user_id, (typeof traits === 'undefined') ? {} : traits);
                                    }
                                }
                            }

                        },
                        error: function (xhr, status, error) {
                            console.log('Status: ' + xhr.status);
                            console.log('Error: ' + xhr.responseText);
                        }
                    });

                }


            });


        });

    });


})(jQuery);


