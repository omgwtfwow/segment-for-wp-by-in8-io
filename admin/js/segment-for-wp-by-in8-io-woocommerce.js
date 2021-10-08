(function ($) {
    'use strict';

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

        $(document.body).append('<div id=\"segment-4-wp-wc-add-to-cart\" class=\"segment-4-wp-wc-add-to-cart-event-placeholder\"></div>');

        analytics.ready(function () {

            if (wp_ajax.wc_settings.is_wc_cart) {

                $.ajax({
                    cache: false,
                    type: "POST",
                    url: wp_ajax.ajax_url,
                    data: {
                        'action': 'public_wc_cart_events',
                        'nonce': wp_ajax._nonce,
                        'event': 'setup'
                    },
                    success: function (response) {

                        if (response) {
                            if ('setup' in response.data) {
                                console.log('init cart state');
                            }
                        }

                    },
                    error: function (xhr, status, error) {
                        console.log('Status: ' + xhr.status);
                        console.log('Error: ' + xhr.responseText);
                    }
                });

                if (wp_ajax.wc_settings.add === 'yes' || wp_ajax.wc_settings.remove === 'yes') {

                    $(document.body).on('updated_cart_totals wc_cart_emptied', function () {

                        $.ajax({
                            cache: false,
                            type: "POST",
                            url: wp_ajax.ajax_url,
                            data: {
                                'action': 'public_wc_cart_events',
                                'nonce': wp_ajax._nonce,
                                'event': 'update'
                            },
                            success: function (response) {
                                if (response) {
                                    if ('update' in response.data && 'changed' in response.data) {
                                        if (response.data.changed === true) {
                                            let event_data = JSON.parse(response.data.tracks);
                                            for (let key in event_data) {
                                                if (event_data != null) {
                                                    let event_name = event_data[key].event_name;
                                                    let event_props = event_data[key].properties;
                                                    if (event_name && event_name !== '') {
                                                        analytics.track(
                                                            event_name,
                                                            event_props,
                                                            {},
                                                            function () {
                                                                // console.log('');
                                                            })
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }

                            },
                            error: function (xhr, status, error) {
                                console.log('Status: ' + xhr.status);
                                console.log('Error: ' + xhr.responseText);
                            }
                        });

                    });

                }
                if (wp_ajax.wc_settings.coupon === 'yes') {

                    $(document.body).on('applied_coupon', function (event, coupon_code) {
                        console.log(coupon_code)
                        $.ajax({
                            cache: false,
                            type: "POST",
                            url: wp_ajax.ajax_url,
                            data: {
                                'action': 'public_wc_cart_events',
                                'nonce': wp_ajax._nonce,
                                'event': 'coupon',
                                'coupon_code': coupon_code
                            },
                            success: function (response) {
                                if (response) {
                                    if ('update' in response.data && 'changed' in response.data) {
                                        if (response.data.changed === true) {
                                            let event_data = JSON.parse(response.data.tracks);
                                            if (event_data != null) {
                                                let event_name = event_data.event_name;
                                                let event_props = event_data.properties;
                                                if (event_name && event_name !== '') {
                                                    analytics.track(
                                                        event_name,
                                                        event_props,
                                                        {},
                                                        function () {
                                                            // console.log('');
                                                        })
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Status: ' + xhr.status);
                                console.log('Error: ' + xhr.responseText);
                            }
                        });

                    });

                }


            }


            analytics.on('track', function (event, properties, options) {
                if ($("#segment-4-wp-wc-add-to-cart").length) {
                    $('#segment-4-wp-wc-add-to-cart').html('');
                    $('#segment-4-wp-wc-add-to-cart').empty();
                    // $( document.body ).trigger( 'wc_fragment_refresh' );
                }
            });

        });

    });

})(jQuery);
