(function ($) {
    'use strict';


    function get_cookie_names() {
        return document.cookie.split(/=[^;]*(?:;\s*|$)/);
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
                            if (response) {
                                if ('user_id' in response.data) {
                                    if (response.data.event === 'identify') {
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


