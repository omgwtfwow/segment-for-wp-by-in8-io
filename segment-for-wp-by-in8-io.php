<?php

/**
 * @link              https://github.com/omgwtfwow/segment-for-wp-by-in8-io
 * @since             1.0.0
 * @package           Segment_For_Wp_By_In8_Io
 *
 * @wordpress-plugin
 * Plugin Name:       Segment for WP by in8.io
 * Plugin URI:        https://github.com/omgwtfwow/segment-for-wp-by-in8-io
 * Description:       Segment Analytics for WordPress
 * Version:           2.3.3
 * Author:            Juan
 * Author URI:        https://juangonzalez.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       segment-for-wp-by-in8-io
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'SEGMENT_FOR_WP_BY_IN8_IO_VERSION', '2.3.3' );

/**
 * The code that runs during plugin activation.
 */
function activate_segment_for_wp_by_in8_io()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-segment-for-wp-by-in8-io-activator.php';
    Segment_For_Wp_By_In8_Io_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_segment_for_wp_by_in8_io()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-segment-for-wp-by-in8-io-deactivator.php';
    Segment_For_Wp_By_In8_Io_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_segment_for_wp_by_in8_io');
register_deactivation_hook(__FILE__, 'deactivate_segment_for_wp_by_in8_io');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-segment-for-wp-by-in8-io.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_segment_for_wp_by_in8_io()
{

	add_action('segment_4_wp_consumer', 'segment_4_wp_consumer');

    $plugin = new Segment_For_Wp_By_In8_Io();
    $plugin->run();

}


run_segment_for_wp_by_in8_io();
