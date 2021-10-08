<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://juangonzalez.com.au
 * @since      1.0.0
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io_i18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'segment-for-wp-by-in8-io',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }


}
