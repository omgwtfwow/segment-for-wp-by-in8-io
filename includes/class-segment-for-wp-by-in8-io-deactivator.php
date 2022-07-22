<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://juangonzalez.com.au
 * @since      1.0.0
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io_Deactivator
{

    /**
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        self::remove_tables();
        self::remove_fs();

    }

    static function remove_tables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'segment_4_wp';
        $plugin_name_db_version = get_option('plugin-name_db_version', '1.0');

        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        delete_option($plugin_name_db_version);
        wp_clear_scheduled_hook('segment_4_wp_consumer');
    }

    static function remove_fs()
    {
        $temp_dir = plugin_dir_path(dirname(__FILE__)) . 'tmp';
        array_map('unlink', glob("$temp_dir/*.*"));
    }

}
