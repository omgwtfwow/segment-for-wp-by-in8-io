(function ($) {

    function get_cookie_names() {
        return document.cookie.split(/=[^;]*(?:;\s*|$)/);
    }

    function remove_cookie(cookie_name) {
        Cookies.set(cookie_name, '');
        Cookies.remove(cookie_name);
        $.ajax({
            cache: false,
            type: "POST",
            url: wp_ajax.ajax_url,
            data: {
                'action': 'public_ajax_cookie',
                'nonce': wp_ajax._nonce,
                'cookie_name': cookie_name,
                'do': 'remove'
            },
            success: function (response) {
                if (response) {
                }
            },
            error: function (xhr, status, error) {
                console.log('Status: ' + xhr.status);
                console.log('Error: ' + xhr.responseText);
            }
        });
    }

    function identify(identify_cookie_name, identify_cookie_data) {

        $.ajax({
            cache: false,
            type: "POST",
            url: wp_ajax.ajax_url,
            data: {
                'action': 'public_ajax_identify',
                'nonce': wp_ajax._nonce,
                'cookie_name': identify_cookie_name,
                'cookie_data': identify_cookie_data,
                'wp_user_id': identify_cookie_data
            },
            success: function (response) {

                if (response) {

                    let user_id = response.data.user_id;
                    let traits = response.data.traits;

                    if (user_id && user_id !== 0 && user_id !== '' && traits) {

                        make_identify_call(user_id, traits, identify_cookie_name);

                    }

                }

            },
            error: function (xhr, status, error) {
                console.log('Status: ' + xhr.status);
                console.log('Error: ' + xhr.responseText);
            }
        });
    }

    function make_identify_call(user_id, traits, identify_cookie_name) {
        analytics.identify(user_id, traits, {}, function () {
            remove_cookie(identify_cookie_name)
        });
    }

    function make_track_call(event_name, event_props, cookie_name) {
        analytics.track(event_name, event_props, {}, function () {
            remove_cookie(cookie_name);
        })

    }

    function process_ninja_forms_cookies_on_load() {

        let cookie_names = get_cookie_names();

        for (let i = 0; i < cookie_names.length; i++) {

            let cookie_name = cookie_names[i];

            if (/^segment_4_wp_ninja_forms_identify/.test(cookie_name)) {
                let identify_value = JSON.parse(JSON.stringify(atob(Cookies.get(cookie_name))));
                if (identify_value != null) {
                    identify(cookie_name, identify_value);
                } else {
                    remove_cookie(cookie_name)
                }
            }

            if (/^segment_4_wp_ninja_forms_after_submission/.test(cookie_name)) {
                let data = JSON.parse(JSON.parse(JSON.stringify(atob(Cookies.get(cookie_name)))))
                let event_name = data.event_name
                let event_props = data.properties;
                if (event_name && event_props) {
                    make_track_call(event_name, event_props, cookie_name)
                } else {
                    remove_cookie(cookie_name)
                }
            }
        }

    }

    //this is so that any cookies are cleared in case of ajax, redirects, etc...
    process_ninja_forms_cookies_on_load();

    analytics.ready(function () {

        analytics.on('track', function (event, properties, options) {
            let cookie_names = get_cookie_names();
            for (let i = 0; i < cookie_names.length; i++) {
                let cookie_name = cookie_names[i];
                if (/^segment_4_wp_ninja_forms_after_submission/.test(cookie_name)) {
                    remove_cookie(cookie_name);
                }
            }
        });

        analytics.on('identify', function (user_id, properties, options) {
            let cookie_names = get_cookie_names();
            for (let i = 0; i < cookie_names.length; i++) {
                let cookie_name = cookie_names[i];
                if (/^segment_4_wp_ninja_forms_identify/.test(cookie_name)) {
                    remove_cookie(cookie_name);
                }
            }
        });

    });

    $(function () {
        // if there is a ninja form on this page
        if (typeof Marionette !== 'undefined') {

            let segment_4_wp_ninja_forms_controller = Marionette.Object.extend({

                initialize: function () {
                    this.listenTo(Backbone.Radio.channel('forms'), 'submit:response', this.actionSubmit);
                },

                actionSubmit: function (response) {
                    let check_cookie_names = get_cookie_names();
                    for (let i = 0; i < check_cookie_names.length; i++) {
                        let cookie_name = check_cookie_names[i];

                        if (/^segment_4_wp_ninja_forms_identify/.test(cookie_name)) {
                            let identify_value = JSON.parse(JSON.stringify(atob(Cookies.get(cookie_name))));
                            if (identify_value != null) {
                                identify(cookie_name, identify_value);
                            } else {
                                remove_cookie(cookie_name)
                            }
                        }

                        if (/^segment_4_wp_ninja_forms_after_submission/.test(cookie_name)) {
                            let event_props = {};
                            let current_value = Cookies.get(cookie_name);
                            if (current_value != null) {
                                let event_data = JSON.parse(atob(Cookies.get(cookie_name)));
                                let event_name = event_data.event_name;
                                let event_props = event_data.properties;
                                if (event_data.event_name && event_data.event_name !== '') {
                                    if (event_name && event_props) {
                                        make_track_call(event_name, event_props, cookie_name);

                                    }
                                }

                            }
                        }

                    }
                },
            });

            // initialise listening controller for ninja form
            new segment_4_wp_ninja_forms_controller();
        }
    });


})(jQuery);

