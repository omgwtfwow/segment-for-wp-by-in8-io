<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @property  random
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/admin
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io_Admin
{

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    protected $version;
    protected $random;
    protected $settings;
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version, $settings)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->random = self::get_random_number();
        $this->settings = $settings;

    }

    public static function get_random_number()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "segment_4_wp";
        $result = $wpdb->get_results("SELECT random FROM {$table_name} WHERE id = 1");

        return $result[0]->random;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Segment_For_Wp_By_In8_Io_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Segment_For_Wp_By_In8_Io_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/segment-for-wp-by-in8-io-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        $settings = $this->settings;
        $custom_js_events = array();

        if (array_key_exists('track_wp_admin', $settings)) {
            if ($settings["track_wp_admin"] == "yes") {
                if (array_key_exists('track_custom_event_group', $settings)) {
                    if (count($settings["track_custom_event_group"]) > 0) {
                        foreach ($settings["track_custom_event_group"] as $event) {
                            array_push($custom_js_events, $event["track_custom_event_name"]);
                        }
                    }
                }

                wp_enqueue_script($this->plugin_name . '-js.cookie.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-js.cookie.min.js', array(), 'v3.0.0-rc.4', false);
                wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-admin.js', array('jquery'), $this->version, false);

                if (Segment_For_Wp_By_In8_Io::woocommerce_active() && array_key_exists("track_woocommerce_fieldset", $settings)) {
                    if ($settings["track_woocommerce_fieldset"]["track_woocommerce"] == 'yes') {
                        $is_wc_cart = is_cart();
                        wp_enqueue_script($this->plugin_name . '-woocommerce.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-woocommerce.js', array('jquery'), $this->version, true);
                    }
                    wp_localize_script($this->plugin_name, 'wp_ajax', array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        '_nonce' => wp_create_nonce($settings["nonce_string"]),
                        'custom_js_events' => $custom_js_events,
                        'wc_settings' => array(
                            'is_wc_cart' => isset($is_wc_cart) ? $is_wc_cart : false,
                            'add' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added"],
                            'remove' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed"],
                            'coupon' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_coupon_applied"]
                        )
                    ));
                }
            }
        }


    }

    /**
     * Creates the options menu
     */
    public function create_menu()
    {

        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }

        $parent = 'plugins.php';
        $settings_link = ('plugins.php?page=plugin-name');

        $config_submenu = [

            'type' => 'menu',
            // Required, menu or metabox
            'id' => $this->plugin_name,
            // Required, meta box id, unique per page, to save: get_option( id )
            'menu' => $parent,
            // Required, sub page to your options page
            'submenu' => true,
            // Required for submenu
            'settings-link' => $settings_link,
            'title' => esc_html__('Segment for WP', 'plugin-name'),
            //The name of this page
            'capability' => 'manage_options',
            // The capability needed to view the page
            'plugin_basename' => plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php'),
            'tabbed' => true,
            'multilang' => false

        ];

        $random = $this->random;

        //KEYS

        // IF FS WRITEABLE
        if (is_writable(plugin_dir_path(dirname(__FILE__)))) {
            $fields[] = array(
                'name' => 'API_keys',
                'title' => 'API Keys',
                'icon' => 'dashicons-admin-network',
                'description' => 'Go to Segment, copy your API keys and paste them here',
                'fields' => array(
                    array(
                        'id' => 'js_api_key',
                        'type' => 'text',
                        'title' => 'JavaScript API Write Key',
                        'description' => 'Go to Segment,copy your JavaScript source API write key and paste it here',
//					'help'        => 'Paste your Segment JS API key here',
                        'attributes' => array(
                            'placeholder' => 'paste your Segment API write key here',
                        ),
                    ),
                    array(
                        'id' => 'php_api_key',
                        'type' => 'text',
                        'title' => 'PHP API Write Key',
                        'description' => 'Go to Segment, copy your PHP source API and paste it here',
//					'help'        => 'Paste your Segment PHP API key here',
                        'attributes' => array(
                            'placeholder' => 'paste your Segment PHP source API write key here',
                        ),

                    ),
                    array(
                        'type' => 'content',
                        'class' => 'class-name',
                        'content' => '<h3>Advanced Options</h3><p>Server side events are asynchronous. They are timestamped with the correct time when the event occurs, and then they are sent later when they are processed according to your settings.</p>',
                        'wrap_class' => 'no-border-bottom',
                    ),
                    array(
                        'id' => 'segment_php_consumer',
                        'type' => 'radio',
                        'title' => 'PHP Consumer',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#socket-consumer" target="_blank">More info about these</a>',
                        'options' => array(
                            'socket' => 'Socket: Default. If you\'re dealing with less than 100s of requests per second.',
                            'file' => 'File: Useful if you\'re dealing with 100s of requests per second. NOTE: The server/user running the cron job needs to have Read and Write permissions for the plugin folder for this to work.',
                        ),
                        'default' => 'socket',
                        'dependency' => array('php_api_key', '!=', ''),
                        'wrap_class' => 'no-border-bottom',
                    ),
                    array(
                        'id' => 'segment_php_consumer_file_cron_interval',
                        'type' => 'number',
                        'title' => 'File Consumer Cron Interval.',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#file-consumer" target="_blank">Docs.</a> How often to upload events.',
                        'dependency' => array('php_api_key', '!=', ''),
                        'default' => '1',
                        'after' => ' <i class="text-muted">minutes</i>',
                        'min' => '1',
                        'max' => '10',
                        'step' => '1'

                    ),
                    array(
                        'id' => 'segment_php_consumer_timeout',
                        'type' => 'number',
                        'title' => 'Socket Consumer Timeout',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#socket-consumer" target="_blank">Docs.</a> The number of seconds to wait for the socket request to time out, defaults to 1. Try higher value for slower servers.</br></br> This setting affects both consumers.',
                        'dependency' => array('php_api_key', '!=', ''),
                        'default' => '1',
                        'after' => ' <i class="text-muted">seconds</i>',
                        'min' => '0.5',
                        'max' => '60',
                        'step' => '0.5'
                    ),
                    array(
                        'id' => 'nonce_string',
                        'type' => 'hidden',
                        'attributes' => array(
                            'placeholder' => $random,
                            'default' => $random
                        ),
                    ),

                ),
            );
        }

        //If FS is not writeable
        else {
            $fields[] = array(
                'name' => 'API_keys',
                'title' => 'API Keys',
                'icon' => 'dashicons-admin-network',
                'description' => 'Go to Segment, copy your API keys and paste them here',
                'fields' => array(
                    array(
                        'id' => 'js_api_key',
                        'type' => 'text',
                        'title' => 'JavaScript API Write Key',
                        'description' => 'Go to Segment,copy your JavaScript source API write key and paste it here',
//					'help'        => 'Paste your Segment JS API key here',
                        'attributes' => array(
                            'placeholder' => 'paste your Segment API write key here',
                        ),
                    ),
                    array(
                        'id' => 'php_api_key',
                        'type' => 'text',
                        'title' => 'PHP API Write Key',
                        'description' => 'Go to Segment, copy your PHP source API and paste it here',
//					'help'        => 'Paste your Segment PHP API key here',
                        'attributes' => array(
                            'placeholder' => 'paste your Segment PHP source API write key here',
                        ),

                    ),
                    array(
                        'type' => 'content',
                        'class' => 'class-name',
                        'content' => '<h3>Advanced Options</h3><p>Server side events are asynchronous. They are timestamped with the correct time when the event occurs, and then they are sent later when they are processed according to your settings.</p>',
                        'wrap_class' => 'no-border-bottom',
                    ),
                    array(
                        'id' => 'segment_php_consumer',
                        'type' => 'radio',
                        'title' => 'PHP Consumer',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#socket-consumer" target="_blank">More info about these</a>',
                        'options' => array(
                            'socket' => 'Socket: Default. If you\'re dealing with less than 100s of requests per second.',
                            'file' => 'File: You don\'t seem to have Write permission for the plugin folder. This option probably won\'t work for you. Please change the permission settings and try again.',
                        ),
                        'default' => 'socket',
                        'dependency' => array('php_api_key', '!=', ''),
                        'wrap_class' => 'no-border-bottom',
                    ),
                    array(
                        'id' => 'segment_php_consumer_file_cron_interval',
                        'type' => 'number',
                        'title' => 'File Consumer Cron Interval.',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#file-consumer" target="_blank">Docs.</a> How often to upload events.',
                        'dependency' => array('php_api_key', '!=', ''),
                        'default' => '1',
                        'after' => ' <i class="text-muted">minutes</i>',
                        'min' => '1',
                        'max' => '10',
                        'step' => '1'

                    ),
                    array(
                        'id' => 'segment_php_consumer_timeout',
                        'type' => 'number',
                        'title' => 'Socket Consumer Timeout',
                        'description' => '<a href="https://segment.com/docs/connections/sources/catalog/libraries/server/php/#socket-consumer" target="_blank">Docs.</a> The number of seconds to wait for the socket request to time out, defaults to 1. </br></br> This setting affects both consumers.',
                        'dependency' => array('php_api_key', '!=', ''),
                        'default' => '1',
                        'after' => ' <i class="text-muted">seconds</i>',
                        'min' => '0.5',
                        'max' => '60',
                        'step' => '0.5'
                    ),
                    array(
                        'id' => 'nonce_string',
                        'type' => 'hidden',
                        'attributes' => array(
                            'placeholder' => $random,
                            'default' => $random
                        ),
                    ),

                ),
            );
        }

        //FILTER
        global $wp_roles;
        $roles = $wp_roles->get_names();
        $trait_options = array(
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email',
            'user_nicename' => 'Username',
            'display_name' => 'Display Name',
            'user_registered' => 'Signup Date',
            'user_url' => 'URL',
            'description' => 'Description',
            'ID' => 'ID'
        );


        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $post_types = get_post_types($args, 'names', 'and');
        $fields[] = array(
            'name' => 'Filtering',
            'title' => 'Filtering',
            'icon' => 'dashicons-filter',
            'description' => 'Filtering your tracking',
            'fields' => array(
                array(
                    'id' => 'ignored_users',
                    'type' => 'tap_list',
                    'title' => 'Roles to ignore',
                    'description' => 'These users won\'t be tracked',
                    'options' => $roles,
                ),
                array(
                    'id' => 'ignored_post_types',
                    'type' => 'tap_list',
                    'title' => 'Custom post types to ignore',
                    'description' => 'Custom post types to ignore',
                    'options' => $post_types,
                ),
                array(
                    'id' => 'track_wp_admin',
                    'type' => 'switcher',
                    'title' => 'Track wp_admin area?',
                    'description' => 'To fire events that happen in /wp-admin/. Some times needed for a few events to work properly',
                    'default' => 'yes',
                ),
            ),
        );

        $identify_events = array(
            'wp_login' => 'Logged in',
            'wp_logout' => 'Logged out',
            'wp_insert_comment' => 'Comment posted'
        );

        //IDENTIFY
        $fields[] = array(
            'name' => 'Identify',
            'title' => 'Identify',
            'icon' => 'dashicons-groups',
            'attributes' => array(
                'cols' => 2,
            ),
            'description' => 'Identify Calls',
            'fields' => array(

                //User ID
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => '<h3>User ID</h3><p><a href="https://segment.com/docs/connections/spec/identify/#user-id" target="_blank">More info</a>. Default is the WordPress user ID.</p><p>If you want to use the default, I recommend you install the <a href="https://wordpress.org/plugins/random-user-ids/" target="_blank">Random User IDs plugin</a> for security.
									The default WordPress user IDs are sequential (1, 2, 3, 4 ...) and easy to guess.</p>',
                    'wrap_class' => 'no-border-bottom',
//					'description' => 'I recommend you install the Random User IDs plugin for security.',

                ),

                array(
                    'id' => 'userid_is_custom',
                    'type' => 'switcher',
                    'title' => 'Use a custom user_meta value as the User ID.',
                    'description' => 'Recommended. Get the user id from a value in user_meta.',
                    'default' => 'no',
                    'wrap_class' => 'no-border-bottom',
                    'options' => array(
                        'cols' => 2,
                    ),
                ),

                array(
                    'id' => 'userid_custom_key',
                    'type' => 'text',
                    'wrap_class' => 'no-border-bottom',
                    'prepend' => 'Custom User ID',
                    'attributes' => array(
                        'data-title' => 'title',
                        'placeholder' => esc_html__('user_meta key', 'plugin-name'),
                    ),
                    'dependency' => array('userid_is_custom|userid_is_email', '==|==', 'true|false'),

                ),

                array(
                    'id' => 'userid_is_email',
                    'type' => 'switcher',
                    'title' => 'Use email as the User ID.',
                    'description' => 'Not best practice, but preferred by many,',
                    'default' => 'no',
                    'wrap_class' => 'no-border-bottom',
                ),

                array(
                    'id' => 'use_alias',
//					'type'        => 'switcher',
                    'type' => 'hidden',
                    'title' => 'Use Alias calls, for Mixpanel for example',
                    'description' => '',
                ),

                //TRAITS
                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>User Traits</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                array(
                    'id' => 'included_user_traits',
                    'type' => 'tap_list',
                    'title' => 'Select user traits',
                    'description' => 'Select the user traits you want to add to your identify calls.',
                    'options' => $trait_options,
                ),

                array(
                    'type' => 'group',
                    'id' => 'custom_user_traits',
                    'title' => esc_html__('Custom user traits', 'plugin-name'),
                    'description' => 'Use meta keys to pull custom traits from user meta data.',

                    'options' => array(
                        'repeater' => true,
                        'accordion' => true,
                        'button_title' => esc_html__('Add new', 'plugin-name'),
                        'group_title' => esc_html__('Accordion Title', 'plugin-name'),
                        'limit' => 50,
                        'sortable' => false,
                        'mode' => 'compact',

                    ),
                    'fields' => array(

                        array(
                            'id' => 'custom_user_traits_label',
                            'type' => 'text',
                            'prepend' => 'Trait label',
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => esc_html__('ie. "Plans', 'plugin-name'),
                            ),
                            'class' => 'chosen',
                        ),

                        array(
                            'id' => 'custom_user_traits_key',
                            'type' => 'text',
                            'prepend' => 'Meta key',
                            'class' => 'chosen',
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => esc_html__('meta key', 'plugin-name'),
                            ),
                        ),
                    ),
                ),

                //ASSOCIATED EVENTS
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => '<h3>Associated Events</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                array(
                    'id' => 'identify_associated_events',
                    'type' => 'tap_list',
                    'title' => 'Fire identify calls when these track events are fired.',
                    'description' => '<strong>Signups always on by default.</strong><br>Make sure you\'ve switched on tracking for each of these in the Events menu.</br> Custom events you add in the Events section will also show up here. Currently supports custom client side events for logged in users.</br>',
                    'options' => $identify_events,
                    'default' => array('Signed Up'),
                    'class' => 'chosen'
                ),

            ),
        );

        //PAGE
        $fields[] = array(
            'name' => 'Pages',
            'title' => 'Page',
            'icon' => 'dashicons-welcome-widgets-menus',
            'attributes' => array(
                'cols' => 2,
            ),
            'description' => 'Page Calls',
            'fields' => array(

                //Names
                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>Page Names</h3><p>Settings for Page calls. <a href="https://segment.com/docs/connections/sources/catalog/libraries/website/javascript/ajs-classic/#page">Read more.</a></p>',
                    'wrap_class' => 'no-border-bottom',
                ),

                //Home
                array(
                    'id' => 'home_page_name',
                    'type' => 'text',
                    'wrap_class' => 'no-border-bottom',
                    'prepend' => 'Home Page Name',
                    'attributes' => array(
                        'data-title' => 'title',
                        'placeholder' => esc_html__('Home', 'plugin-name'),
                    ),
                ),

                //Login
                array(
                    'id' => 'login_page_name',
                    'type' => 'text',
                    'wrap_class' => 'no-border-bottom',
                    'prepend' => 'WP Login/Signup Page Name',
                    'attributes' => array(
                        'data-title' => 'title',
                        'placeholder' => esc_html__('Login', 'plugin-name'),
                    ),
                ),

                //404
                array(
                    'id' => '404_page_name',
                    'type' => 'text',
                    'wrap_class' => 'no-border-bottom',
                    'prepend' => '404 Page Name',
                    'attributes' => array(
                        'data-title' => 'title',
                        'placeholder' => esc_html__('404', 'plugin-name'),
                    ),
                ),

                array(
                    'id' => 'page_is_custom',
                    'type' => 'switcher',
                    'title' => 'Use a the value of a field in post_meta as the page name.',
                    'description' => '',
                    'default' => 'no',
                    'wrap_class' => 'no-border-bottom',
                    'options' => array(
                        'cols' => 2,
                    ),
                ),

                array(
                    'id' => 'page_custom_key',
                    'type' => 'text',
                    'wrap_class' => 'no-border-bottom',
                    'prepend' => 'Custom Page Name',
                    'attributes' => array(
                        'data-title' => 'title',
                        'placeholder' => esc_html__('post_meta key', 'plugin-name'),
                    ),
                    'dependency' => array('page_is_custom', '==', 'true'),

                ),

                //Props
                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>Page Props</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                //ADD CUSTOM PAGE META
                array(
                    'type' => 'group',
                    'id' => 'include_custom_page_props',
                    'title' => esc_html__('Include custom page props', 'plugin-name'),
                    'description' => esc_html__('Include these page props in the page calls. Pulled from post meta.', 'plugin-name'),
                    'options' => array(
                        'repeater' => true,
                        'accordion' => true,
                        'button_title' => esc_html__('Add new', 'plugin-name'),
                        'group_title' => esc_html__('Accordion Title', 'plugin-name'),
                        'limit' => 50,
                        'sortable' => false,
                        'mode' => 'compact',

                    ),
                    'fields' => array(
                        array(
                            'id' => 'custom_page_props_label',
                            'type' => 'text',
                            'prepend' => 'Prop label',
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => esc_html__('Prop label', 'plugin-name'),
                            ),
                            'class' => 'chosen',
                        ),
                        array(
                            'id' => 'custom_page_props_key',
                            'type' => 'text',
                            'prepend' => 'Prop value',
                            'class' => 'chosen',
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => esc_html__('post_meta key', 'plugin-name'),

                            ),
                        ),
                    ),
                ),


                //Server Side
                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>Advanced</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                array(
                    'id' => 'track_pages_server_side',
                    'type' => 'switcher',
                    'title' => 'Track Pages Server Side?',
                    'description' => 'Make sure you have the server resources to use this.',
                    'default' => 'no',
                    'wrap_class' => 'no-border-bottom',
                    'options' => array(
                        'cols' => 2,
                    ),
                ),

            ),
        );

        //TRACK
        $fields[] = array(
            'name' => 'Events',
            'title' => 'Events',
            'icon' => 'dashicons-yes',
            'options' => array(
                'cols' => 2,
            ),
            'fields' => array(

                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>WordPress Events</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                //TRACK SIGNUPS
                array(
                    'type' => 'fieldset',
                    'id' => 'track_signups_fieldset',
                    'title' => 'Track Sign Ups',
                    'description' => 'Trigger an event when people sign up',
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields' => array(
                        array(
                            'id' => 'track_signups',
                            'type' => 'switcher',
                            'prepend' => 'Track sign ups?',
                        ),

                        array(
                            'id' => 'track_signups_client_event_name',
                            'type' => 'text',
                            'prepend' => 'Client Side Event Name',
                            'dependency' => array('track_signups', '==', 'true'),
                            'attributes' => array(
                                'placeholder' => 'Signed up',
                            )
                        ),

                        array(
                            'id' => 'track_signups_server',
                            'type' => 'checkbox',
                            'label' => 'Track server side',
                            'dependency' => array('track_signups|php_api_key', '==|!=', 'true|""')
                        ),

                        array(
                            'id' => 'track_signups_server_event_name',
                            'type' => 'text',
                            'prepend' => 'Server Side Event Name',
                            'dependency' => array(
                                'track_signups|track_signups_server|php_api_key',
                                '==|==|!=',
                                'true|true|""'
                            ),
                            'attributes' => array('placeholder' => 'Signed up')
                        ),


                    ),

                ),

                //TRACK LOGINS
                array(
                    'type' => 'fieldset',
                    'id' => 'track_logins_fieldset',
                    'title' => 'Track Log Ins',
                    'description' => 'Trigger an event when people log in',
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields' => array(
                        array(
                            'id' => 'track_logins',
                            'type' => 'switcher',
                            'prepend' => 'Track log ins?',
                        ),

                        array(
                            'id' => 'track_logins_client_event_name',
                            'type' => 'text',
                            'prepend' => 'Client Side Event Name',
                            'dependency' => array('track_logins', '==', 'true'),
                            'attributes' => array(
                                'placeholder' => 'Logged in',
                            )
                        ),

                        array(
                            'id' => 'track_logins_server',
                            'type' => 'checkbox',
                            'label' => 'Track server side',
                            'dependency' => array('track_logins|php_api_key', '==|!=', 'true|""')
                        ),

                        array(
                            'id' => 'track_logins_server_event_name',
                            'type' => 'text',
                            'prepend' => 'Server Side Event Name',
                            'dependency' => array(
                                'track_logins|track_logins_server|php_api_key',
                                '==|==|!=',
                                'true|true|""'
                            ),
                            'attributes' => array(
                                'placeholder' => 'Logged in',
                            )
                        ),

                    ),

                ),

                //TRACK LOGOUTS
                array(
                    'type' => 'fieldset',
                    'id' => 'track_logouts_fieldset',
                    'title' => 'Track Log Outs',
                    'description' => 'Trigger an event when people log out',
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields' => array(
                        array(
                            'id' => 'track_logouts',
                            'type' => 'switcher',
                            'prepend' => 'Track log outs?',
                        ),


                        array(
                            'id' => 'track_logouts_client_event_name',
                            'type' => 'text',
                            'prepend' => 'Client Side Event Name',
                            'dependency' => array('track_logouts', '==', 'true'),
                            'attributes' => array(
                                'placeholder' => 'Logged out',
                            )
                        ),

                        array(
                            'id' => 'track_logouts_server',
                            'type' => 'checkbox',
                            'label' => 'Track server side',
                            'dependency' => array('track_logouts|php_api_key', '==|!=', 'true|""')
                        ),

                        array(
                            'id' => 'track_logouts_server_event_name',
                            'type' => 'text',
                            'prepend' => 'Server Side Event Name',
                            'dependency' => array(
                                'track_logouts|track_logouts_server|php_api_key',
                                '==|==|!=',
                                'true|true|""'
                            ),
                            'attributes' => array(
                                'placeholder' => 'Logged out',
                            )
                        ),

                    ),

                ),

                //TRACK COMMENTS
                array(
                    'type' => 'fieldset',
                    'id' => 'track_comments_fieldset',
                    'title' => 'Track Comments',
                    'description' => 'Trigger an event when people comment',
                    'options' => array(
                        'cols' => 1,
                    ),
                    'fields' => array(
                        array(
                            'id' => 'track_comments',
                            'type' => 'switcher',
                            'prepend' => 'Track comments?',
                        ),

                        array(
                            'id' => 'track_comments_client_event_name',
                            'type' => 'text',
                            'prepend' => 'Client Side Event Name',
                            'dependency' => array('track_comments', '==', 'true'),
                            'attributes' => array(
                                'placeholder' => 'Commented',
                            )
                        ),

                        array(
                            'id' => 'track_comments_server',
                            'type' => 'checkbox',
                            'label' => 'Track server side',
                            'dependency' => array('track_comments|php_api_key', '==|!=', 'true|""')
                        ),

                        array(
                            'id' => 'track_comments_server_event_name',
                            'type' => 'text',
                            'prepend' => 'Server Side Event Name',
                            'dependency' => array(
                                'track_comments|track_comments_server|php_api_key',
                                '==|==|!=',
                                'true|true|""'
                            ),
                            'attributes' => array(
                                'placeholder' => 'Commented',
                            )
                        ),

                    ),

                ),

                array(
                    'type' => 'content',
                    'class' => 'class-name', // for all fieds
                    'content' => '<h3>Advanced</h3>',
                    'wrap_class' => 'no-border-bottom',
                ),

                //ADD USER ID
                array(
                    'id' => 'include_user_ids',
                    'type' => 'switcher',
                    'title' => 'Add userId and email as properties to each event.',
                    'description' => 'Some email tools need this in order to track things properly.',
                    'options' => array(
                        'cols' => 1,
                    ),
                ),

                // TRACK CUSTOM
                array(
                    'type' => 'group',
                    'id' => 'track_custom_event_group',
                    'title' => esc_html__('Custom events', 'plugin-name'),
                    'description' => 'If you\'re firing your own JavaScript events, you can enter the event names here to be able to process them with this plugin.</br></br>For example, if you want to fire a server side event for one of your JS events.</br></br>Note: Associated identify calls for these custom events are supported for logged in users.',
                    'options' => array(
                        'repeater' => true,
                        'accordion' => false,
                        'button_title' => esc_html__('Add new', 'plugin-name'),
                        'group_title' => esc_html__('Javascript Event Name', 'plugin-name'),
                        'limit' => 50,
                        'sortable' => true,
                        'mode' => 'compact',
                    ),
                    'fields' => array(
                        array(
                            'id' => 'track_custom_event_name',
                            'type' => 'text',
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => esc_html__('', 'plugin-name'),
                            ),
                        ),
                        array(
                            'id' => 'track_custom_event_server_side',
                            'type' => 'checkbox',
                            'label' => 'Track server side',
                            'dependency' => array('php_api_key', '!=', ''),
                        ),
                    ),
                ),

//				//ADD CUSTOM USER META
//				array(
//					'type'       =>'group',
//					'id'         =>'include_user_traits',
//					'title'      =>esc_html__( 'Include custom traits', 'plugin-name' ),
//					'description'=>esc_html__( 'Include these user traits in track calls. Pulled from user meta.', 'plugin-name' ),
//					'options'    =>array(
//						'repeater'    =>true,
//						'accordion'   =>true,
//						'button_title'=>esc_html__( 'Add new', 'plugin-name' ),
//						'group_title' =>esc_html__( 'Accordion Title', 'plugin-name' ),
//						'limit'       =>50,
//						'sortable'    =>false,
//						'mode'        =>'compact',
//
//					),
//					'fields'     =>array(
//						array(
//							'id'        =>'included_user_traits_label',
//							'type'      =>'text',
//							'prepend'   =>'Trait label',
//							'attributes'=>array(
//								'data-title' =>'title',
//								'placeholder'=>esc_html__( 'ie. "total orders"', 'plugin-name' ),
//							),
//							'class'     =>'chosen',
//						),
//						array(
//							'id'        =>'included_user_traits_key',
//							'type'      =>'text',
//							'prepend'   =>'Meta key',
//							'class'     =>'chosen',
//							'attributes'=>array(
//								'data-title' =>'title',
//								'placeholder'=>esc_html__( 'user_meta key', 'plugin-name' ),
//
//							),
//						),
//					),
//				),

            ),
        );

        //FORMS
        $ninja_forms_active = Segment_For_Wp_By_In8_Io::ninja_forms_active();
        $gravity_forms_active = Segment_For_Wp_By_In8_Io::gravity_forms_active();
        if ($ninja_forms_active || $gravity_forms_active) {
            $form_plugin_active = 'true';
        } else {
            $form_plugin_active = 'false';
        }
        if ($form_plugin_active == 'true') {

            $fields[] = array(
                'name' => 'Forms',
                'title' => 'Forms',
                'icon' => 'dashicons-list-view',
                'fields' =>
                    array(

                        array(
                            'id' => 'ninja_forms_active',
                            'type' => 'hidden',
                            'default' => $ninja_forms_active,
                        ),

                        array(
                            'id' => 'gravity_forms_active',
                            'type' => 'hidden',
                            'default' => $gravity_forms_active,
                        ),

                        array(
                            'id' => 'form_plugin_active',
                            'type' => 'hidden',
                            'default' => $form_plugin_active,
                        ),

                        //NINJA FORM
                        array(
                            'type' => 'fieldset',
                            'id' => 'track_ninja_forms_fieldset',
                            'title' => 'Track Ninja Forms',
                            'description' => 'Trigger events when people complete Ninja Forms.</br></br><a href=http://dev.local/wp-admin/admin.php?page=nf-settings#ninja_forms[builder_dev_mode]">Enable the Ninja Form Builder "Dev Mode" setting</a> to see form field Admin Labels.</br>',
                            'options' => array(
                                'cols' => 1,
                            ),
                            'fields' => array(
                                array(
                                    'id' => 'track_ninja_forms',
                                    'type' => 'switcher',
                                    'wrap_class' => 'no-border-bottom',

                                ),

                                array(
                                    'id' => 'track_ninja_forms_server',
                                    'type' => 'checkbox',
                                    'label' => 'Track server side',
                                    'dependency' => array('track_ninja_forms', '==', 'true'),
                                ),

                                array(
                                    'id' => 'identify_ninja_forms',
                                    'title' => '</br>Identify',
                                    'label' => 'Fire identify calls for Ninja Forms.',
                                    'description' => 'At the moment only supports Identify calls if the user is logged in. You can provide the WP user ID in a field or it will detect it automatically.',
                                    'type' => 'switcher',
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_ninja_forms', '==', 'true'),

                                ),

                                array(
                                    'id' => 'ninja_forms_wp_user_id_field',
                                    'type' => 'text',
                                    'title' => 'WP User ID Field',
                                    'description' => 'The admin label of the fields that will contain a wordpress user id. If no field is specified, it will use the wp id for logged in users.',
                                    'attributes' => array(
                                        'placeholder' => esc_html__('Field \'Admin Label\'', 'plugin-name'),

                                    ),
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array(
                                        'track_ninja_forms|identify_ninja_forms',
                                        '==|==',
                                        'true|true'
                                    ),
                                ),

                                array(
                                    'type' => 'content',
                                    'class' => 'class-name', // for all fieds
                                    'content' => '<hr>',
                                    'description' => 'Track Ninja Forms?',
                                    'wrap_class' => 'no-border-bottom',
//							'dependency'  => array( 'track_ninja_forms|ninja_forms_active', '==|==', 'true|true'  ),
                                    'dependency' => array('track_ninja_forms', '==', 'true'),

                                ),

                                array(
                                    'id' => 'ninja_forms_event_name_field',
                                    'type' => 'text',
                                    'title' => 'Event Names',
                                    'description' => 'You can provide an \'Admin Label\' for a field you want to use as the event name. For example, add a hidden form field with the event name as the default value, set an Admin Label for that field and then enter it here.</br>',
                                    'attributes' => array(
                                        'placeholder' => esc_html__('Field \'Admin Label\'', 'plugin-name'),
                                    ),
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_ninja_forms', '==', 'true'),
                                ),

                                array(
                                    'id' => 'ninja_forms_properties_info',
                                    'type' => 'content',
                                    'title' => '</br>Event Properties',
                                    'description' => 'Event properties can be created from form fields. Provide a name for the property, and an \'Admin Label\' for the form field you want to use as the value of that property.',
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_ninja_forms', '==', 'true'),
                                ),

                                array(
                                    'type' => 'group',
                                    'id' => 'ninja_form_event_properties',
//									'title'     =>'</br>',
                                    'options' => array(
                                        'repeater' => true,
                                        'accordion' => true,
                                        'button_title' => esc_html__('Add new', 'plugin-name'),
                                        'group_title' => esc_html__('Accordion Title', 'plugin-name'),
                                        'limit' => 50,
                                        'sortable' => false,
                                        'mode' => 'compact',

                                    ),
                                    'fields' => array(

                                        array(
                                            'id' => 'ninja_form_event_property_label',
                                            'type' => 'text',
                                            'prepend' => 'Event Property Label',
                                            'attributes' => array(
                                                'data-title' => 'title',
                                                'placeholder' => esc_html__('Example', 'plugin-name'),
                                            ),
                                            'class' => 'chosen',
                                        ),
                                        array(
                                            'id' => 'ninja_form_event_property_field_id',
                                            'type' => 'text',
                                            'prepend' => 'Event Property Value',
                                            'class' => 'chosen',
                                            'attributes' => array(
                                                'data-title' => 'title',
                                                'placeholder' => esc_html__('Field \'Admin Label\'', 'plugin-name'),

                                            ),
                                        ),
                                    ),
//							'dependency' => array('track_ninja_forms|ninja_forms_active', '==|==', 'true|true' ),
                                    'dependency' => array('track_ninja_forms', '==', 'true'),
                                    'wrap_class' => 'no-border-bottom',

                                ),

                            ),
                        ),

                        //GRAVITY FORM
                        array(
                            'type' => 'fieldset',
                            'id' => 'track_gravity_forms_fieldset',
                            'title' => 'Track Gravity Forms',
                            'description' => 'Trigger events when people complete Gravity Forms.</br></br>You can find "Admin Field Labels" in the "Advanced" section of the field when creating forms.',
                            'options' => array(
                                'cols' => 1,
                            ),
                            'fields' => array(
                                array(
                                    'id' => 'track_gravity_forms',
                                    'type' => 'switcher',
                                    'wrap_class' => 'no-border-bottom',

                                ),

                                array(
                                    'id' => 'track_gravity_forms_server',
                                    'type' => 'checkbox',
                                    'label' => 'Track server side',
                                    'dependency' => array('track_gravity_forms', '==', 'true'),
                                ),

                                array(
                                    'id' => 'identify_gravity_forms',
                                    'title' => '</br>Identify',
                                    'label' => 'Fire identify calls for Gravity Forms.',
                                    'description' => 'At the moment only supports Identify calls if you provide the WP user ID or if the user is logged in.',
                                    'type' => 'switcher',
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_gravity_forms', '==', 'true'),

                                ),


                                array(
                                    'id' => 'gravity_forms_wp_user_id_field',
                                    'type' => 'text',
                                    'title' => 'WP User ID Field',
                                    'description' => 'The "Admin Field Label" of the fields that will contain a wordpress user id. If no field is specified, it will use the wp id for logged in users.',
                                    'attributes' => array(
                                        'placeholder' => esc_html__('Field \'Admin Label\'', 'plugin-name'),

                                    ),
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array(
                                        'track_gravity_forms|identify_gravity_forms',
                                        '==|==',
                                        'true|true'
                                    ),
                                ),

                                array(
                                    'type' => 'content',
                                    'class' => 'class-name', // for all fieds
                                    'content' => '<hr>',
                                    'description' => 'Track Gravity Forms?',
                                    'wrap_class' => 'no-border-bottom',
//							'dependency'  => array( 'track_ninja_forms|ninja_forms_active', '==|==', 'true|true'  ),
                                    'dependency' => array('track_gravity_forms', '==', 'true'),

                                ),

                                array(
                                    'id' => 'gravity_forms_event_name_field',
                                    'type' => 'text',
                                    'title' => 'Event Names',
                                    'description' => 'Provide the "Admin Field Label" for the field you want to use as the event name. For example, create a hidden field with an event name as the default value, then enter the  "Admin Field Label" for that field here.</br>',
                                    'attributes' => array(
                                        'placeholder' => esc_html__('Field \'Admin Label\'', 'plugin-name'),
                                    ),
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_gravity_forms', '==', 'true'),
                                ),

                                array(
                                    'id' => 'gravity_forms_properties_info',
                                    'type' => 'content',
                                    'title' => '</br>Event Properties',
                                    'description' => 'Event properties can be created from form fields. Provide a name for the property, and an \'Admin Field Label\' for the form field you want to use as the value of that property.',
                                    'wrap_class' => 'no-border-bottom',
                                    'dependency' => array('track_gravity_forms', '==', 'true'),
                                ),

                                array(
                                    'type' => 'group',
                                    'id' => 'gravity_form_event_properties',
//									'title'     =>'</br>',
                                    'options' => array(
                                        'repeater' => true,
                                        'accordion' => true,
                                        'button_title' => esc_html__('Add new', 'plugin-name'),
                                        'group_title' => esc_html__('Accordion Title', 'plugin-name'),
                                        'limit' => 50,
                                        'sortable' => false,
                                        'mode' => 'compact',

                                    ),
                                    'fields' => array(

                                        array(
                                            'id' => 'gravity_form_event_property_label',
                                            'type' => 'text',
                                            'prepend' => 'Event Property Label',
                                            'attributes' => array(
                                                'data-title' => 'title',
                                                'placeholder' => esc_html__('Example', 'plugin-name'),
                                            ),
                                            'class' => 'chosen',
                                        ),
                                        array(
                                            'id' => 'gravity_form_event_property_field_id',
                                            'type' => 'text',
                                            'prepend' => 'Event Property Value',
                                            'class' => 'chosen',
                                            'attributes' => array(
                                                'data-title' => 'title',
                                                'placeholder' => esc_html__('\'Admin Field Label\'', 'plugin-name'),

                                            ),
                                        ),
                                    ),
                                    'dependency' => array('track_gravity_forms', '==', 'true'),
                                    'wrap_class' => 'no-border-bottom',

                                ),

                            ),
                        ),
                    ),
            );

        } else {

            $fields[] = array(
                'name' => 'forms_inactive',
                'title' => 'Forms',
                'icon' => 'dashicons-list-view',
                'fields' => array(

                    array(
                        'type' => 'notice',
                        'class' => 'primary',
                        'content' => 'Sorry, Ninja Forms or Gravity Forms must be installed.',
                    ),


                ),
            );

        }

        //WOO
        $woocommerce_active = Segment_For_Wp_By_In8_Io::woocommerce_active();
        if ($woocommerce_active) {
            $fields[] = array(
                'name' => 'WooCommerce',
                'title' => 'WooCommerce',
                'icon' => 'dashicons-cart',
                'fields' => array(

                    array(
                        'id' => 'woocommerce_active',
                        'type' => 'hidden',
                        'default' => true,
                    ),

                    array(
                        'type' => 'fieldset',
                        'id' => 'track_woocommerce_fieldset',
                        'title' => 'Track WooCommerce',
                        'description' => 'Trigger events when people complete Woocommerce actions',
                        'options' => array(
                            'cols' => 1,
                        ),
                        'fields' => array(
                            array(
                                'id' => 'track_woocommerce',
                                'type' => 'switcher',
                                'wrap_class' => 'no-border-bottom',
                                'class' => 's4wp-woo-field',
                            ),
                            array(
                                'type' => 'fieldset',
                                'id' => 'woocommerce_events',
                                'dependency' => array('track_woocommerce', '==', 'true'),
                                'options' => array(
                                    'cols' => 1,
                                ),
                                'fields' => array(
                                    array(
                                        'id' => 'woocommerce_events_settings',
                                        'type' => 'accordion',
                                        'options' => array(
                                            'allow_all_open' => true,

                                        ),
                                        'class' => 's4wp-woo-field',
                                        'sections' => array(

                                            //Cart and product
                                            array(
                                                'options' => array(
                                                    'title' => esc_html__('Product and Cart Events', 'plugin-name'),
                                                    'closed' => true,
                                                    'cols' => 1
                                                ),
                                                'fields' => array(

                                                    //PRODUCT ADDED
                                                    array(
                                                        'id' => 'track_woocommerce_events_product_added',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track "Add to cart"?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_events_product_added',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
//                                                        'description' => 'Event name',
                                                        'prepend' => esc_html__('Product Added', 'plugin-name'),
//													'attributes'    => array(
//														'style'        => 'margin-left:50%!important;',
//													),
                                                        'class' => 's4wp-woo-field',
                                                        'attributes' => array(
                                                            'placeholder' => 'Product Added',
                                                            'default' => 'Product Added',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_added',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_events_product_added_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_added',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_events_product_added_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Product Added', 'plugin-name'),
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_added_server|track_woocommerce_events_product_added',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Product Added',
                                                            'placeholder' => 'Product Added',
                                                        ),


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'spacer',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),

                                                    //PRODUCT REMOVED
                                                    array(
                                                        'id' => 'track_woocommerce_events_product_removed',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track removals from Cart?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_events_product_removed',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Product Removed', 'plugin-name'),
//													'attributes'    => array(
//														'style'        => 'margin-left:50%!important;',
//													),
                                                        'attributes' => array(
                                                            'placeholder' => 'Product Removed',
                                                            'default' => 'Product Removed',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_removed',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_events_product_removed_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_removed',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_events_product_removed_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Product Removed',
                                                        'dependency' => array(
                                                            'track_woocommerce_events_product_removed_server|track_woocommerce_events_product_removed',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Product Removed',
                                                            'placeholder' => 'Product Removed',
                                                        ),
                                                        'class' => 's4wp-woo-field',

                                                    ),

//												//CART VIEWED
//												array(
//													'id'        =>'track_woocommerce_events_cart_viewed',
//													'type'      =>'switcher',
//													'wrap_class'=>'no-border-bottom',
//													'title'     =>'Track cart views?',
//													'class'     =>'s4wp-woo-field',
//
//												),
//												array(
//													'id'        =>'woocommerce_events_cart_viewed',
//													'type'      =>'text',
//													'title'     =>'</br>',
//													'prepend'   =>esc_html__( 'Cart Viewed', 'plugin-name' ),
//													'attributes'=>array(
//														'placeholder'=>'Cart Viewed',
//														'default'    =>'Cart Viewed',
//													),
//													'wrap_class'=>'no-border-bottom',
//													'class'     =>'s4wp-woo-field',
//													'dependency'=>array(
//														'track_woocommerce_events_cart_viewed',
//														'==',
//														'true'
//													),
//
//
//												),
//												array(
//													'id'        =>'woocommerce_events_cart_viewed_server',
//													'type'      =>'checkbox',
//													'label'     =>'Track server side',
//													'wrap_class'=>'no-border-bottom',
//													'class'     =>'s4wp-woo-field',
//													'dependency'=>array(
//														'track_woocommerce_events_cart_viewed',
//														'==',
//														'true'
//													),
//
//
//												),
//												array(
//													'id'        =>'woocommerce_events_cart_viewed_server_event_name',
//													'type'      =>'text',
//													'prepend'   =>'Product Added',
//													'append'    =>'Server',
//													'dependency'=>array(
//														'woocommerce_events_cart_viewed_server|track_woocommerce_events_cart_viewed',
//														'==|==',
//														'true|true'
//													),
//													'attributes'=>array(
//														'placeholder'=>'Cart Viewed',
//														'default'    =>'Cart Viewed',
//													),
//													'class'     =>'s4wp-woo-field',
//
//
//												),


                                                ),
                                            ),

                                            //Orders
                                            array(
                                                'options' => array(
                                                    'icon' => 'fa fa-star',
                                                    'title' => 'Order Events',
                                                    'closed' => true,
                                                ),

                                                'fields' => array(


                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-orders',
                                                        'content' => '<small><strong>NOTE: </strong><span style="color:red">Admins must log out to test all these order events.</span> Client-side order events won\'t fire for logged in admins.</small>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),
                                                    array(
                                                        'id' => 'content-wc-hr',
                                                        'type' => 'content',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),

                                                    //ORDER PENDING
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_pending',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Pending event?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_pending',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Pending', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Pending',
                                                            'default' => 'Order Pending',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_pending',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_pending_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_pending',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_pending_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Processing',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_pending_server|track_woocommerce_event_order_pending',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Pending',
                                                            'default' => 'Order Pending',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'id' => 'content-wc-hr',
                                                        'type' => 'content',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER PROCESSING
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_processing',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Processing event?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_processing',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Processing', 'plugin-name'),
//													'attributes'    => array(
//														'style'        => 'margin-left:50%!important;',
//													),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Processing',
                                                            'default' => 'Order Processing',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_processing',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_processing_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_processing',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_processing_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Processing',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_processing_server|track_woocommerce_event_order_processing',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Processing',
                                                            'default' => 'Order Processing',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'id' => 'content-wc-hr',
                                                        'type' => 'content',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER FAILED
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_failed',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Failed event?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_failed',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Failed', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Failed',
                                                            'default' => 'Order Failed',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_failed',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_failed_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_failed',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_failed_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Failed',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_failed_server|track_woocommerce_event_order_failed',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Failed',
                                                            'default' => 'Order Failed',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER COMPLETED
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_completed',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Completed event?',
                                                        'class' => 's4wp-woo-field',
                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_completed',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Completed', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Completed',
                                                            'default' => 'Order Completed',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_completed',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_completed_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_completed',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_completed_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Paid',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_completed_server|track_woocommerce_event_order_completed',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Completed',
                                                            'default' => 'Order Completed',
                                                        ),
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER PAID
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_paid',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Paid event?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_paid',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Paid', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Paid',
                                                            'default' => 'Order Paid',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_paid',
                                                            '==',
                                                            'true'
                                                        ),
                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_paid_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_paid',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_paid_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Paid',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_paid_server|track_woocommerce_event_order_paid',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Paid',
                                                            'default' => 'Order Paid',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER ON HOLD
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_on_hold',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order On Hold event?',
                                                        'class' => 's4wp-woo-field',

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_on_hold',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order On Hold', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order On Hold',
                                                            'default' => 'Order On Hold',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_on_hold',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_on_hold_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_on_hold',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_on_hold_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order On Hold',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_on_hold_server|track_woocommerce_event_order_on_hold',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order On Hold',
                                                            'default' => 'Order On Hold',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //ORDER REFUNDED
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_refunded',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Refunded event?',
                                                        'class' => 's4wp-woo-field',
                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_refunded',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Refunded', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Refunded',
                                                            'default' => 'Order Refunded',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_refunded',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_refunded_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_refunded',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_refunded_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Refunded',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_refunded_server|track_woocommerce_event_order_refunded',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Refunded',
                                                            'default' => 'Order Refunded',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),

                                                    //ORDER CANCELLED
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_cancelled',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Order Cancelled event?',
                                                        'class' => 's4wp-woo-field',
                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_cancelled',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Order Cancelled', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Cancelled',
                                                            'default' => 'Order Cancelled',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_cancelled',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_cancelled_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_cancelled',
                                                            '==',
                                                            'true'
                                                        ),


                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_cancelled_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Order Cancelled',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_cancelled_server|track_woocommerce_event_order_cancelled',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Order Cancelled',
                                                            'default' => 'Order Cancelled',
                                                        ),
                                                        'class' => 's4wp-woo-field',


                                                    ),
                                                    array(
                                                        'type' => 'content',
                                                        'id' => 'content-wc-hr',
                                                        'content' => '<hr>',
                                                        'wrap_class' => 'no-border-bottom',
                                                    ),


                                                    //COUPON APPLIED
                                                    array(
                                                        'id' => 'track_woocommerce_event_coupon_applied',
                                                        'type' => 'switcher',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'title' => 'Track Coupon Applied event?',
                                                        'class' => 's4wp-woo-field',
                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_coupon_applied',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => esc_html__('Coupon Applied', 'plugin-name'),
                                                        'attributes' => array(
                                                            'placeholder' => 'Coupon Applied',
                                                            'default' => 'Coupon Applied',
                                                        ),
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_coupon_applied',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'track_woocommerce_event_order_coupon_applied_server',
                                                        'type' => 'checkbox',
                                                        'label' => 'Track server side',
                                                        'wrap_class' => 'no-border-bottom',
                                                        'class' => 's4wp-woo-field',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_coupon_applied',
                                                            '==',
                                                            'true'
                                                        ),

                                                    ),
                                                    array(
                                                        'id' => 'woocommerce_event_order_coupon_applied_server_event_name',
                                                        'type' => 'text',
                                                        'title' => '<small>Event name</small>',
                                                        'prepend' => 'Coupon Applied',
                                                        'dependency' => array(
                                                            'track_woocommerce_event_order_coupon_applied_server|track_woocommerce_event_coupon_applied',
                                                            '==|==',
                                                            'true|true'
                                                        ),
                                                        'attributes' => array(
                                                            'placeholder' => 'Coupon Applied',
                                                            'default' => 'Coupon Applied',
                                                        ),
                                                        'class' => 's4wp-woo-field',

                                                    ),

                                                ),
                                            ),
                                        ),
                                    ),
                                ),

                            ),

                            // Match logged out users
                            array(
                                'id' => 'woocommerce_match_logged_out_users',
                                'type' => 'switcher',
                                'wrap_class' => 'no-border-bottom',
                                'title' => 'Attempt to get user id for "logged out" orders?',
                                'description' => 'If a logged out customer places an order, the plugin can attempt to find their User ID based on the order email. This can help match these events to users in some tools downstream.',
                                'class' => 's4wp-woo-field',
                                'default' => 'no'
                            ),

//                            array(
//								'type'        => 'fieldset',
//								'id'          => 'track_woocommerce_meta_fieldset',
//								'title'       => 'Add WooCommerce user meta to identify calls',
//								'description' => 'Add WooCommerce user meta to identify calls',
//								'dependency'  => array( 'track_woocommerce', '==', 'true' ),
//								'options'     => array(
//									'cols' => 3,
//								),
//								'fields'      => array(
//									array(
//										'id'   => 'track_woocommerce_meta',
//										'type' => 'switcher',
//									),
//								),
//
//							),

                        ),
                    ),
                ),
            );
        } else {
            $fields[] = array(
                'name' => 'woo_inactive',
                'title' => 'WooCommerce',
                'icon' => 'dashicons-list-view',
                'fields' => array(

                    array(
                        'type' => 'notice',
                        'class' => 'primary',
                        'content' => 'Sorry, WooCommerce must be installed.',
                    ),


                ),
            );
        }


        /**
         * instantiate your admin page
         */
        $options_panel = new Exopite_Simple_Options_Framework($config_submenu, $fields);
    }

    /**
     * updates fields when options saved
     *
     * @param $fields
     *
     * @return array
     */
    public function save_menu($fields)
    {

        $settings = get_exopite_sof_option('segment-for-wp-by-in8-io');
        $ninja_forms_active = Segment_For_Wp_By_In8_Io::ninja_forms_active();
        $gravity_forms_active = Segment_For_Wp_By_In8_Io::gravity_forms_active();

        if ($ninja_forms_active || $gravity_forms_active) {
            $form_plugin_active = true;
        } else {
            $form_plugin_active = false;
        }

        $settings["nonce_string"] = $this->random;
        $settings["ninja_forms_active"] = $ninja_forms_active;
        $settings["gravity_forms_active"] = $gravity_forms_active;
        $settings["form_plugin_active"] = $form_plugin_active;

        if (array_key_exists('track_logins_fieldset', $settings)) {
            if ($settings["track_logins_fieldset"]["track_logins"] == 'yes') {
                if ($settings["track_logins_fieldset"]["track_logins_client_event_name"] != '') {
                    $fields[2]["fields"][9]["options"]['wp_login'] = $settings["track_signups_fieldset"]["track_signups_client_event_name"];
                }
            }
        }
        if (array_key_exists('track_logouts_fieldset', $settings)) {
            if ($settings["track_logouts_fieldset"]["track_logouts"] == 'yes' && $settings["track_logouts_fieldset"]["track_logouts_client_event_name"] != '') {

                $fields[2]["fields"][9]["options"]['wp_logout'] = $settings["track_logouts_fieldset"]["track_logouts_client_event_name"];

            }
        }
        if (array_key_exists('track_comments_fieldset', $settings)) {

            if ($settings["track_comments_fieldset"]["track_comments"] == 'yes' && $settings["track_comments_fieldset"]["track_comments_client_event_name"] != '') {

                $fields[2]["fields"][9]["options"]['wp_insert_comment'] = $settings["track_comments_fieldset"]["track_comments_client_event_name"];

            }
        }

        if (array_key_exists('track_custom_event_group', $settings)) {

            if (count($settings["track_custom_event_group"]) > 0) {
                foreach ($settings["track_custom_event_group"] as $event) {
                    $event_name = $event["track_custom_event_name"];
                    if ($event_name != '') {
                        $fields[2]["fields"][9]["options"][$event_name] = $event_name;
                    }
                }
            }
        }

        $temp_dir = plugin_dir_path(dirname(__FILE__)) . 'tmp';
        $log_file = $temp_dir . '/analytics.log';

        if (array_key_exists('segment_php_consumer', $settings)) {

            if ($settings["segment_php_consumer"] == 'file') {

                $timestamp = time();
                $recurrence = 's4wp_file_consumer';
                $args = array();
                if (!wp_next_scheduled('segment_4_wp_consumer')) {
                    wp_schedule_event($timestamp, $recurrence, 'segment_4_wp_consumer', $args);
                }

            } elseif ($settings["segment_php_consumer"] == 'socket') {
                wp_clear_scheduled_hook('segment_4_wp_consumer');
                if (is_writable(plugin_dir_path(dirname(__FILE__)))) {
                    array_map('unlink', glob("$temp_dir/*.*"));
	                if (file_exists($temp_dir)) {
		                rmdir($temp_dir);
	                }

                    unset($fields[0]["fields"][4]);
                }
            }
        }
        if (!file_exists($temp_dir)) {
            if (is_writable(plugin_dir_path(dirname(__FILE__)))) {
                mkdir($temp_dir);
            }

        }
        if (!file_exists($log_file)) {
            if (is_writable(plugin_dir_path(dirname(__FILE__)))) {

                fopen($log_file, 'w');
            }
        }

        return $fields;
    }

    public function cron_schedules($schedules)
    {
        $settings = $this->settings;
        $interval = 1;
        if (!isset($schedules["s4wp_file_consumer"])) {

            if (array_key_exists('segment_php_consumer_file_cron_interval', $settings)) {
                $interval = $settings["segment_php_consumer_file_cron_interval"];

                if (!is_numeric($interval)) {
                    $interval = 1;
                }
            }


            $schedules["s4wp_file_consumer"] = array(
                'interval' => 60 * $interval,
                'display' => __('Every ' . $interval . ' minutes.')
            );
        }


        return $schedules;
    }

}

