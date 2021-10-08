(function ($) {

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


})(jQuery);
