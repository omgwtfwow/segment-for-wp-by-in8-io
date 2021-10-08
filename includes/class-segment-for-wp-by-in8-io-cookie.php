<?php

class Segment_For_Wp_By_In8_Io_Cookie
{


    /**
     * Set a cookie
     *
     * @param string $name The cookie name.
     * @param string|array $value The cookie value.
     * @param int $expiration A Unix timestamp representing the expiration (use time() plus seconds until expiration). Defaults to 0, which will cause the cookie to expire at the end of the user's browsing session.
     * @param string $cookie_id A unique cookie ID
     */
    public static function set_cookie(string $name, $value, $expiration = 0, $cookie_id = '')
    {
        $length = mb_strlen(json_encode($_COOKIE));
        if ($length > 3093) {
            return;
        }
        $cookie_name = 'segment_4_wp_' . $name . '_' . COOKIEHASH . '_' . $cookie_id;
        $cookie_value = base64_encode(json_encode($value));
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));

        $cookie_path = COOKIEPATH;
        $cookie_domain = COOKIE_DOMAIN;

        setcookie($cookie_name, $cookie_value, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure);

    }

    /**
     * Check if a cookie exists
     *
     * @param string $name
     * @param string $type long or short
     * @param string $cookie_id
     *
     * @return bool Whether or not the cookie exists.
     */
    public static function check_cookie(string $name, string $type = 'short', $cookie_id = ''): bool
    {
        $result = false;
        if (isset($cookie_id) && $cookie_id !== '') {
            $result = isset($_COOKIE['segment_4_wp_' . $name . '_' . COOKIEHASH . '_' . $cookie_id]);
        }
        if ($type == 'short') {
            $result = isset($_COOKIE['segment_4_wp_' . $name . '_' . COOKIEHASH . '_']);
        }

        if ($type == 'long') {
            $result = isset($_COOKIE[$name]);
        }

        return $result;

    }

    /**
     * Get a cookie
     *
     * @param string $name The cookie name.
     * @param string $cookie_id
     *
     * @return mixed Returns the value or the default if the cookie doesn't exist.
     */
    public static function get_cookie(string $name, $cookie_id = '')
    {

        if (isset($cookie_id) && $cookie_id !== '') {
            if (self::check_cookie($name, $cookie_id)) {
                return json_decode(base64_decode($_COOKIE['segment_4_wp_' . $name . '_' . COOKIEHASH . '_' . $cookie_id]), true);
            }
        } else {
            if (self::check_cookie($name)) {
                return json_decode(base64_decode($_COOKIE['segment_4_wp_' . $name . '_' . COOKIEHASH . '_']), true);
            }
        }

    }

    /**
     * Delete a cookie
     *
     * @param string $name The cookie name.
     * @param string $cookie_id
     *
     * @return mixed Returns the value or the default if the cookie doesn't exist.
     */

    public static function delete_cookie(string $full_cookie_name)
    {
        $expiration = time() - HOUR_IN_SECONDS;
        $new_value = '';
        setcookie($full_cookie_name, $new_value, $expiration, COOKIEPATH, COOKIE_DOMAIN);
    }

    /**
     * Delete matching cookies for an event
     *
     * @param string $event_name The event name of the cookie to delete.
     */
    public static function delete_matching_cookies(string $event_name)
    {

        $expiration = time() - HOUR_IN_SECONDS;
        $new_value = '';

        foreach ($_COOKIE as $cookie => $value) {
            if (strpos($cookie, "segment_4_wp_" . $event_name) !== false) {

                setcookie($cookie, $new_value, $expiration, COOKIEPATH, COOKIE_DOMAIN);


            }
        }

    }

    public static function match_cookie($name)
    {

        foreach ($_COOKIE as $cookie => $value) {
            if (strpos($cookie, "segment_4_wp_" . $name) !== false) {
                return true;
            }
        }

        return false;


    }

    public static function get_cookie_name($name, $cookie_id = '')
    {
        if (isset($cookie_id) && $cookie_id !== '') {
            if (self::check_cookie($name, $cookie_id)) {

                return 'segment_4_wp_' . $name . '_' . COOKIEHASH . '_' . $cookie_id;
            }
        } else {
            if (self::check_cookie($name)) {
                return 'segment_4_wp_' . $name . '_' . COOKIEHASH . '_';
            }
        }
    }

    /**
     *
     *
     * @param array of cookies
     *
     * @return array
     */
    public static function get_matching_cookies($name)
    {
        $cookies_array = array();
        foreach ($_COOKIE as $cookie => $value) {
            if (strpos($cookie, "segment_4_wp_" . $name) !== false) {
                $cookies_array[$cookie] = json_decode(base64_decode($value), true);
            }
        }

        return $cookies_array;
    }

    /**
     * Clear cookies
     *
     * @param array of cookies
     */
    public static function clear_cookies()
    {

        if (isset($_COOKIE['segment_4_wp_clear'])) {
            $value = "";
            $expiration = time() - HOUR_IN_SECONDS;
            $path = "/wordpress";

            setcookie("segment_4_wp_clear", $value, $expiration, $path, COOKIE_DOMAIN);
            unset($_COOKIE["segment_4_wp_clear"]);
            $path = "/wordpress/";
            setcookie("segment_4_wp_clear", $value, $expiration, $path, COOKIE_DOMAIN);
            unset($_COOKIE["segment_4_wp_clear"]);
            $path = "/";
            setcookie("segment_4_wp_clear", $value, $expiration, $path, COOKIE_DOMAIN);
            unset($_COOKIE["segment_4_wp_clear"]);
            setcookie("segment_4_wp_clear", $value, $expiration, COOKIEPATH, COOKIE_DOMAIN);
            unset($_COOKIE["segment_4_wp_clear"]);

            foreach ($_COOKIE as $cookie => $value) {
                if (strpos($cookie, "segment_4_wp_") !== false) {
                    setcookie($cookie, $value, $expiration, COOKIEPATH, COOKIE_DOMAIN);
                    unset($_COOKIE[$cookie]);
                }

            }
        }
    }

    /** Clear db */
    public static function clear_db()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'segment_for_wp_by_in8_io';
        $wpdb->query("DELETE  FROM {$table_name} WHERE flag = 'true'");

    }

}