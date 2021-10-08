<?php

/**
 * Fired during plugin activation
 *
 * @link       https://juangonzalez.com.au
 * @since      1.0.0
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io_Activator
{

    /**
     *
     * @since    1.0.0
     */
    public static function activate()
    {

        self::create_db();
        self::insert_row_to_db();
        self::setup_fs();
    }


    public static function create_db()
    {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . "segment_4_wp";
        $plugin_name_db_version = get_option('plugin-name_db_version', '1.0');

        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name ||
            version_compare(SEGMENT_FOR_WP_BY_IN8_IO_VERSION, '1.0') < 0) {

            $sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  random tinytext NOT NULL,
			  PRIMARY KEY  (id)
			) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }

    }

    public static function insert_row_to_db()
    {
        global $wpdb;
        $random = self::random_string(20);
        $table = $wpdb->prefix . 'segment_4_wp';

        $wpdb->insert(
            $table,
            array(
                'random' => $random
            )
        );

        return $wpdb->insert_id;

    }

    static function random_string($length)
    {
        $result = null;
        $replace = array('/', '+', '=');
        while (!isset($result[$length - 1])) {
            $result .= str_replace($replace, NULL, base64_encode(random_bytes($length)));
        }
        return substr($result, 0, $length);
    }

    static function setup_fs()
    {

        if (is_writable(plugin_dir_path(dirname(__FILE__)) . 'tmp')) {

            $temp_dir = plugin_dir_path(dirname(__FILE__)) . 'tmp';
            $log_file = $temp_dir . '/analytics.log';
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir);

            }
            if (!file_exists($log_file)) {
                fopen($log_file, 'w');
            }

        }

    }

}


