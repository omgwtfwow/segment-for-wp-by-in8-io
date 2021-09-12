(function ($) {
    'use strict';

    $(function () {
        analytics.ready(function () {
            analytics.reset();
            Cookies.remove(wp_logout.cookie_name);
        });
    });
})(jQuery);
