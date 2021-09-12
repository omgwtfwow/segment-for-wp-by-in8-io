function s4wp_run_gf_tracking(form, entry, redirect = false, ajax_url, ajax_nonce, gf_event_name, gf_event_props = {}, identify = false, user_id, user_traits = {}) {

    (function ($) {

        function remove_server_cookie(event_name) {

            $.ajax({
                cache: false,
                type: "POST",
                url: JSON.parse(ajax_url),
                data: {
                    'action': 'public_ajax_cookie',
                    'nonce': ajax_nonce,
                    'cookie_name': event_name,
                    'cookie_name_type': 'short',
                    'do': 'remove'
                },
                success: function (response) {
                    if (response) {
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Status: ' + xhr.status);
                }
            });
        }


        analytics.track(gf_event_name, gf_event_props, {}, function () {
            remove_server_cookie('gform_after_submission');
        })

        if (identify && user_id && user_traits) {
            analytics.identify(user_id, JSON.parse(user_traits), {}, function () {
                remove_server_cookie('gravity_forms_identify');
            })

        }

        if (redirect) {
            $(document).ready(function () {
                // Handler for .ready() called.
                window.setTimeout(function () {
                    location.href = redirect;
                }, 500);
            });
        }

    })(jQuery);

}

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

(function ($) {
    'use strict';
    if (typeof wp_ajax !== 'undefined') {

        function remove_server_cookie(cookie_name) {

            $.ajax({
                cache: false,
                type: "POST",
                url: wp_ajax.ajax_url,
                data: {
                    'action': 'public_ajax_cookie',
                    'nonce': wp_ajax._nonce,
                    'cookie_name': cookie_name,
                    'cookie_name_type': 'long',
                    'do': 'remove'
                },
                success: function (response) {
                    if (response) {
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Status: ' + xhr.status);
                }
            });
        }

        $(document).ready(function () {
            setTimeout(function () {
                let cookies = Cookies.get();
                for (const [key, value] of Object.entries(cookies)) {
                    if (key.includes('segment_4_wp_gravity_forms')) {
                        remove_server_cookie(key)
                    }
                    if (key.includes('wp_gform_after_submission')) {
                        remove_server_cookie(key)

                    }
                }

            }, 500);
        })

    }

})(jQuery);
