<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://github.com/omgwtfwow/segment-for-wp-by-in8-io
 * @since      2.0.0
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/includes
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Segment_For_Wp_By_In8_Io_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('SEGMENT_FOR_WP_BY_IN8_IO_VERSION')) {
            $this->version = SEGMENT_FOR_WP_BY_IN8_IO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'segment-for-wp-by-in8-io';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class for cookies
         */
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-cookie.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-segment-for-wp-by-in8-io-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-segment-for-wp-by-in8-io-public.php';

        /**
         * The class for the Segment js snippet
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-snippet.php';

        /**
         * The class for the Segment identify snippet
         */
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-identify.php';

        /**
         * The class for the Segment page calls
         */
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-page.php';

        /**
         * The class for the Segment track calls
         */
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-track.php';

        /**
         * Exopite Simple Options Framework
         *
         * @link https://github.com/JoeSz/Exopite-Simple-Options-Framework
         * @author Joe Szalai
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/exopite-simple-options/exopite-simple-options-framework-class.php';


        /**
         * The class for the Segment PHP library
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/segment_php/lib/Segment.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/action-scheduler/action-scheduler.php';
        require plugin_dir_path(dirname(__FILE__)) . 'includes/class-segment-for-wp-by-in8-io-track-server.php';


        $this->loader = new Segment_For_Wp_By_In8_Io_Loader();


    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Segment_For_Wp_By_In8_Io_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Segment_For_Wp_By_In8_Io_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $settings = self::get_settings();
        $plugin_admin = new Segment_For_Wp_By_In8_Io_Admin($this->get_plugin_name(), $this->get_version(), $settings);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        // Save/Update our plugin options
        $this->loader->add_action('init', $plugin_admin, 'create_menu', 1);
        $this->loader->add_action('cron_schedules', $plugin_admin, 'cron_schedules', 1);
        $this->loader->add_action('exopite_sof_options_' . $this->get_plugin_name(), $plugin_admin, 'save_menu', 1, 1);


    }

    /**
     * @return mixed|void
     */
    public static function get_settings()
    {
        $settings = get_exopite_sof_option('segment-for-wp-by-in8-io');
        if ($settings["nonce_string"] == '') {
            $settings["nonce_string"] = Segment_For_Wp_By_In8_Io_Admin::get_random_number();
        }

        return $settings;
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $settings = self::get_settings();
        $plugin_public = new Segment_For_Wp_By_In8_Io_Public($this->get_plugin_name(), $this->get_version(), $settings);

        //JS
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('login_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        //CORE AJAX
        $this->loader->add_action('wp_ajax_public_ajax_identify', $plugin_public, 'ajax_identify');
        $this->loader->add_action('wp_ajax_nopriv_public_ajax_identify', $plugin_public, 'ajax_identify');
        $this->loader->add_action('wp_ajax_public_ajax_track', $plugin_public, 'ajax_track');
        $this->loader->add_action('wp_ajax_nopriv_public_ajax_track', $plugin_public, 'ajax_track');
        $this->loader->add_action('wp_ajax_public_ajax_cookie', $plugin_public, 'ajax_cookie');
        $this->loader->add_action('wp_ajax_nopriv_public_ajax_cookie', $plugin_public, 'ajax_cookie');


        //SNIPPET
        $snippet = new Segment_For_Wp_By_In8_Io_Snippet($this->get_plugin_name(), $this->get_version(), $settings);
        $this->loader->add_action('wp_head', $snippet, 'render_segment_snippet');
        $this->loader->add_action('admin_head', $snippet, 'render_segment_snippet');
        $this->loader->add_action('login_head', $snippet, 'render_segment_snippet');

        //IDENTIFY
        $identify = new Segment_For_Wp_By_In8_Io_Identify($this->get_plugin_name(), $this->get_version(), $settings);
        $this->loader->add_action('wp_head', $identify, 'render_segment_identify');
        $this->loader->add_action('admin_head', $identify, 'render_segment_identify');
        $this->loader->add_action('login_head', $identify, 'render_segment_identify');

        //PAGE
        $page = new Segment_For_Wp_By_In8_Io_Page($this->get_plugin_name(), $this->get_version(), $settings);
        $this->loader->add_action('wp_head', $page, 'render_segment_page');
        $this->loader->add_action('admin_head', $page, 'render_segment_page');
        $this->loader->add_action('login_head', $page, 'render_segment_page');

        //TRACK
        $track = new Segment_For_Wp_By_In8_Io_Track($this->get_plugin_name(), $this->get_version(), $settings);
        $this->loader->add_action('wp_footer', $track, 'render_track_call', 20);
        $this->loader->add_action('admin_footer', $track, 'render_track_call', 20);
        $this->loader->add_action('login_footer', $track, 'render_track_call', 20);

        //SIGNUPS
        if (array_key_exists('track_signups_fieldset', $settings)) {
            if ($settings["track_signups_fieldset"]["track_signups"] == "yes") {
                $this->loader->add_action('user_register', $plugin_public, 'user_register', 1, 1);
            }
        }

        //LOGINS
        if (array_key_exists('track_logins_fieldset', $settings)) {
            if ($settings["track_logins_fieldset"]["track_logins"] == "yes") {
                $this->loader->add_action('wp_login', $plugin_public, 'wp_login', 1, 2);
            }
        }


        //LOGOUT
        //Since I use this to do analytics.reset(), I check the options for the logged out event settings/name elsewhere
        $this->loader->add_action('wp_logout', $plugin_public, 'wp_logout', 9, 1);

        //COMMENTS
        if (array_key_exists('track_comments_fieldset', $settings)) {
            if ($settings['track_comments_fieldset']['track_comments'] == "yes") {
                $this->loader->add_action('wp_insert_comment', $plugin_public, 'wp_insert_comment', 9, 2);
            }
        }

        //NINJA FORMS
        if (array_key_exists('track_ninja_forms_fieldset', $settings) && self::ninja_forms_active()) {
            if ($settings["track_ninja_forms_fieldset"]["track_ninja_forms"] === 'yes') {
                $this->loader->add_action('ninja_forms_after_submission', $plugin_public, 'ninja_forms_after_submission', 9, 1);
            }
        }

        //GRAVITY FORMS
        if (array_key_exists('track_gravity_forms_fieldset', $settings) && self::gravity_forms_active()) {
            if ($settings["track_gravity_forms_fieldset"]["track_gravity_forms"] === 'yes') {
                $this->loader->add_action('gform_after_submission', $plugin_public, 'gform_after_submission', 9, 2);
                $this->loader->add_action('gform_confirmation', $plugin_public, 'gform_confirmation', 9, 4);
            }
        }

        //WOOCOMMERCE
        if (array_key_exists('track_woocommerce_fieldset', $settings) && self::woocommerce_active()) {
            if (array_key_exists('track_woocommerce', $settings["track_woocommerce_fieldset"])) {
                if ($settings["track_woocommerce_fieldset"]["track_woocommerce"] == 'yes') {

                    if (array_key_exists('woocommerce_events', $settings["track_woocommerce_fieldset"])) {

                        if (array_key_exists('woocommerce_events_settings', $settings["track_woocommerce_fieldset"]["woocommerce_events"])) {

                            // ADD TO CART
                            if (array_key_exists("track_woocommerce_events_product_added", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added"] == 'yes') {
                                    $this->loader->add_action('woocommerce_add_to_cart', $plugin_public, 'woocommerce_add_to_cart', 9, 6);
                                    $this->loader->add_action('woocommerce_add_to_cart_fragments', $plugin_public, 'woocommerce_add_to_cart_fragments', 9, 1);
                                    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                                        $this->loader->add_action('woocommerce_add_to_cart_redirect', $plugin_public, 'woocommerce_add_to_cart_redirect', 9, 2);
                                        $this->loader->add_action('woocommerce_ajax_added_to_cart', $plugin_public, 'woocommerce_ajax_added_to_cart');
                                        $this->loader->add_action('woocommerce_after_cart', $plugin_public, 'woocommerce_after_cart', 9, 2);
                                    }
                                    $this->loader->add_action('woocommerce_cart_item_restored', $plugin_public, 'woocommerce_cart_item_restored', 5, 2);
                                }
                            }

                            // REMOVE FROM CART
                            if (array_key_exists("track_woocommerce_events_product_removed", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed"] == 'yes') {
                                    $this->loader->add_action('woocommerce_remove_cart_item', $plugin_public, 'woocommerce_remove_cart_item', 9, 2);
                                    $this->loader->add_action('wp_ajax_public_wc_cart_events', $plugin_public, 'wc_cart_events');
                                    $this->loader->add_action('wp_ajax_nopriv_public_wc_cart_events', $plugin_public, 'wc_cart_events');
                                }
                            }

                            // ORDER PENDING
                            if (array_key_exists("track_woocommerce_event_order_pending", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_pending"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_pending', $plugin_public, 'woocommerce_order_status_pending', 5, 1);
                                }
                            }
                            // ORDER FAILED
                            if (array_key_exists("track_woocommerce_event_order_failed", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_failed"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_failed', $plugin_public, 'woocommerce_order_status_failed', 5, 1);
                                }
                            }
                            // ORDER PROCESSING
                            if (array_key_exists("track_woocommerce_event_order_processing", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_processing"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_processing', $plugin_public, 'woocommerce_order_status_processing', 5, 1);
                                }
                            }
                            // ORDER COMPLETED
                            if (array_key_exists("track_woocommerce_event_order_complete", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_complete"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_completed', $plugin_public, 'woocommerce_order_status_completed', 5, 1);
                                }
                            }
                            // ORDER PAID
                            if (array_key_exists("track_woocommerce_event_order_paid", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_paid"] == 'yes') {
                                    $this->loader->add_action('woocommerce_payment_complete', $plugin_public, 'woocommerce_payment_complete', 5, 1);
                                }
                            }
                            // ORDER COMPLETED
                            if (array_key_exists("track_woocommerce_event_order_complete", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_complete"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_completed', $plugin_public, 'woocommerce_order_status_completed', 5, 1);
                                }
                            }
                            // ORDER ON HOLD
                            if (array_key_exists("track_woocommerce_event_order_on_hold", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_on_hold"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_on-hold', $plugin_public, 'woocommerce_order_status_on_hold', 5, 1);
                                }
                            }
                            // ORDER REFUNDED
                            if (array_key_exists("track_woocommerce_event_order_refunded", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_refunded"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_refunded', $plugin_public, 'woocommerce_order_status_refunded', 5, 1);
                                }
                            }
                            // ORDER CANCELLED
                            if (array_key_exists("track_woocommerce_event_order_cancelled", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {

                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_cancelled"] == 'yes') {
                                    $this->loader->add_action('woocommerce_order_status_cancelled', $plugin_public, 'woocommerce_order_status_cancelled', 5, 1);
                                }
                            }
                            // COUPON APPLIED
                            if (array_key_exists("track_woocommerce_event_coupon_applied", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_coupon_applied"] == 'yes') {
                                    $this->loader->add_action('woocommerce_applied_coupon', $plugin_public, 'woocommerce_applied_coupon', 9, 1);
                                }
                            }


                        }

                    }

                }
            }

        }

        //SERVER SIDE
        if (self::server_side_ready()) {

            $segment_php = new Segment_For_Wp_By_In8_Io_Segment_Php_Lib($this->get_plugin_name(), $this->get_version(), $settings, $settings["segment_php_consumer"]);
            $this->loader->add_action('init', $segment_php, 'init_segment', 1);
//            $this->loader->add_action('init', $segment_php, 'init_scheduler', 1);

            $this->loader->add_action('segment_4_wp_consumer', $segment_php, 'file_consumer', 1);
//	        $this->loader->add_action('segment_4_wp_consumer', $segment_php, 'default_consumer', 1);

	        $this->loader->add_action('async_task', $segment_php, 'async_task', 1, 1);

            //PAGES SERVER SIDE
            if (array_key_exists('track_pages_server_side', $settings)) {
                if ($settings["track_pages_server_side"] == 'yes') {
                    $this->loader->add_action('wp_head', $segment_php, 'page_server_side', 1, 1);
                    $this->loader->add_action('admin_head', $segment_php, 'page_server_side', 1, 1);
                    $this->loader->add_action('login_head', $segment_php, 'page_server_side', 1, 1);
                }
            }

            // SIGNUPS SERVER SIDE
            if (array_key_exists('track_signups_fieldset', $settings)) {
                if ($settings["track_signups_fieldset"]["track_signups_server"] == 'yes' && $settings["track_signups_fieldset"]["track_signups"] == "yes") {
                    $this->loader->add_action('user_register', $segment_php, 'user_register', 1, 1);
                }
            }

            //LOGINS SERVER
            if (array_key_exists('track_logins_fieldset', $settings)) {
                if ($settings["track_logins_fieldset"]["track_logins_server"] == "yes" && $settings["track_logins_fieldset"]["track_logins"] == "yes") {
                    $this->loader->add_action('wp_login', $segment_php, 'wp_login', 1, 2);
                }
            }

            //LOGOUTS SERVER
            if (array_key_exists('track_logouts_fieldset', $settings)) {
                if ($settings["track_logouts_fieldset"]["track_logouts_server"] == "yes") {
                    $this->loader->add_action('wp_logout', $segment_php, 'wp_logout', 9, 1);
                }
            }

            //COMMENTS SERVER SIDE
            if (array_key_exists('track_comments_fieldset', $settings)) {
                if ($settings['track_comments_fieldset']['track_comments_server'] == "yes" && $settings['track_comments_fieldset']['track_comments'] == "yes") {
                    $this->loader->add_action('wp_insert_comment', $segment_php, 'wp_insert_comment', 9, 2);
                }
            }

            //NINJA FORMS SERVER
            if (array_key_exists('track_ninja_forms_fieldset', $settings) && self::ninja_forms_active()) {
                if ($settings["track_ninja_forms_fieldset"]["track_ninja_forms_server"] === 'yes' && $settings["track_ninja_forms_fieldset"]["track_ninja_forms"] == 'yes') {
                    $this->loader->add_action('ninja_forms_after_submission', $segment_php, 'ninja_forms_after_submission', 9, 1);
                }
            }

            //GRAVITY FORMS SERVER SIDE
            if (array_key_exists('track_gravity_forms_fieldset', $settings) && self::gravity_forms_active()) {
                if ($settings["track_gravity_forms_fieldset"]["track_gravity_forms_server"] === 'yes' && $settings["track_gravity_forms_fieldset"]["track_gravity_forms"] == 'yes') {
                    $this->loader->add_action('gform_after_submission', $segment_php, 'gform_after_submission', 9, 2);
                }
            }

            //WOOCOMMERCE SERVER SIDE
            if (array_key_exists('track_woocommerce_fieldset', $settings) && self::woocommerce_active()) {
                if (array_key_exists('track_woocommerce', $settings["track_woocommerce_fieldset"])) {
                    if ($settings["track_woocommerce_fieldset"]["track_woocommerce"] == 'yes') {
                        if (array_key_exists('woocommerce_events', $settings["track_woocommerce_fieldset"])) {
                            if (array_key_exists('woocommerce_events_settings', $settings["track_woocommerce_fieldset"]["woocommerce_events"])) {

                                //ADD TO CART SERVER
                                if (array_key_exists('track_woocommerce_events_product_added_server', $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_add_to_cart', $segment_php, 'woocommerce_add_to_cart', 9, 6);
                                        $this->loader->add_action('woocommerce_after_cart_item_quantity_update', $segment_php, 'woocommerce_after_cart_item_quantity_update', 9, 4);
                                        $this->loader->add_action('woocommerce_cart_item_restored', $segment_php, 'woocommerce_cart_item_restored', 9, 4);
                                    }
                                }

                                // REMOVE FROM CART SERVER
                                if (array_key_exists('track_woocommerce_events_product_removed_server', $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_cart_item_removed', $segment_php, 'woocommerce_cart_item_removed', 9, 2);
                                    }
                                }

                                // ORDER PENDING
                                if (array_key_exists("track_woocommerce_event_order_pending_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_pending_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_pending', $segment_php, 'woocommerce_order_status_pending', 5, 1);
                                    }
                                }

                                // ORDER FAILED
                                if (array_key_exists("track_woocommerce_event_order_failed_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_failed_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_failed', $segment_php, 'woocommerce_order_status_failed', 5, 1);
                                    }
                                }


                                // ORDER PROCESSING
                                if (array_key_exists("track_woocommerce_event_order_processing_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_processing_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_processing', $segment_php, 'woocommerce_order_status_processing', 5, 1);
                                    }
                                }


                                // ORDER COMPLETED
                                if (array_key_exists("track_woocommerce_event_order_completed_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_completed_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_completed', $segment_php, 'woocommerce_order_status_completed', 5, 1);
                                    }
                                }

                                // ORDER PAID
                                if (array_key_exists("track_woocommerce_event_order_paid_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_paid_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_payment_complete', $segment_php, 'woocommerce_payment_complete', 5, 1);
                                    }
                                }


                                // ORDER ON HOLD
                                if (array_key_exists("track_woocommerce_event_order_on_hold_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_on_hold_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_on-hold', $segment_php, 'woocommerce_order_status_on_hold', 5, 1);
                                    }
                                }

                                // ORDER REFUNDED
                                if (array_key_exists("track_woocommerce_event_order_refunded_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_refunded_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_refunded', $segment_php, 'woocommerce_order_status_refunded', 5, 1);
                                    }
                                }

                                // ORDER CANCELLED
                                if (array_key_exists("track_woocommerce_event_order_cancelled_server", $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_cancelled_server"] == 'yes') {
                                        $this->loader->add_action('woocommerce_order_status_cancelled', $segment_php, 'woocommerce_order_status_cancelled', 5, 1);
                                    }
                                }

                            }

                        }

                    }
                }
            }


        }

    }

    /**
     * @return bool
     */
    public static function ninja_forms_active()
    {

        if (is_plugin_active("ninja-forms/ninja-forms.php") && class_exists('Ninja_Forms')) {
            return true;
        } else {

            return false;
        }
    }

    /**
     * @return bool
     */
    public static function gravity_forms_active()
    {
        if (is_plugin_active('gravityforms/gravityforms.php') && class_exists('GFForms')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public static function woocommerce_active()
    {
        $plugins = get_plugins();
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function server_side_ready()
    {
        $settings = self::get_settings();

        if (array_key_exists('php_api_key', $settings) && $settings["php_api_key"] !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $current_user
     *
     * @return bool
     */
    public static function check_trackable_user($current_user)
    {
        $settings = self::get_settings();

        if (is_user_logged_in()) {
            $user_roles = ( array )$current_user->roles;
            $current_role = $user_roles[0];
            if (array_key_exists('ignored_users', $settings)) {
                $excluded_roles = $settings['ignored_users'];
                if (!$excluded_roles) {
                    return true;
                }
                if (!in_array($current_role, $excluded_roles)) {
                    return true;
                } else { //not trackable
                    return false;
                }
            } else {
                //no exclusions
                return true;
            }

        } else { //logged out
            return true;
        }
    }

    /**
     * @param $current_post
     *
     * @return bool
     */
    public static function check_trackable_post($current_post)
    {

        $settings = self::get_settings();

        if ($settings["track_wp_admin"] === "no" && is_admin() === true) {
            return false;
        } else {
            $post_type = get_post_type($current_post);
            if (array_key_exists('ignored_post_types', $settings)) {
                $excluded_post_types = $settings['ignored_post_types'];
                if (!$excluded_post_types) {
                    return true;
                }
                if (in_array($post_type, $excluded_post_types)) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }

        }

    }

    /**
     * get anonymous id from ajs cookie
     *
     * @return string|null
     */
    public static function get_ajs_anon_user_id()
    {
        if (array_key_exists('ajs_anonymous_id', $_COOKIE)) {
            $ajs_anon_id = str_replace('"', "", stripslashes($_COOKIE["ajs_anonymous_id"]));

            return sanitize_text_field($ajs_anon_id);
        }

        return null;
    }

    /**
     * get anonymous id from ajs cookie
     *
     * @return string|null
     */
    public static function get_ajs_user_id()
    {
        if (array_key_exists('ajs_user_id', $_COOKIE)) {
            $ajs_user_id = str_replace('"', "", stripslashes($_COOKIE["ajs_user_id"]));
            return sanitize_text_field($ajs_user_id);
        }

        return null;
    }

    /**
     * @param $cookie_data
     * @param $cookie_name
     *
     * @return array
     */
    public static function get_user_info_from_cookie($cookie_data, $cookie_name)
    {
        $user_info = array();
        if (!$cookie_data || !$cookie_name) {
            return null;
        }
        if (isset($cookie_data['wp_user_id']) && $cookie_data['wp_user_id'] !== '' && $cookie_data['wp_user_id'] !== 0) {

            $user_info['wp_user_id'] = $cookie_data['wp_user_id'];
            $user_info['user_id'] = self::get_user_id($cookie_data['wp_user_id']);

            return $user_info;
        }
        if ($cookie_data['action_hook'] == 'ninja_forms_after_submission') {
            if (isset($cookie_data['nf_wp_user_id']) && $cookie_data['nf_wp_user_id'] !== '' && $cookie_data['nf_wp_user_id'] !== 0) {
                $nf_wp_user = get_userdata($cookie_data['nf_wp_user_id']);
                if ($nf_wp_user && $nf_wp_user->ID != 0) {
                    $user_info['wp_user_id'] = $nf_wp_user->ID;
                    $user_info['user_id'] = self::get_user_id($cookie_data['wp_user_id']);

                    return $user_info;
                }
            }
        }

        return $user_info;
    }

    /**
     * @param $wp_user_id
     *
     * @return mixed|void user id
     */
    public static function get_user_id($wp_user_id)
    {
        if ($wp_user_id == 0 || $wp_user_id == null) {
            return null;
        }
        $user_id = $wp_user_id;
        $settings = self::get_settings();
        if ($settings["userid_is_custom"] == "yes" && $settings["userid_custom_key"] != "") {
            $key = $settings["userid_custom_key"];
            $user_id = get_user_meta($wp_user_id, $key, true);
        } else if ($settings["userid_is_email"] == "yes") {
            $user_data = get_userdata($wp_user_id);
            $user_id = $user_data->user_email;
        } else {
            $user_id = $wp_user_id;
        }

        //you can use the hook 'change user id' to change the user id
        return apply_filters('segment_for_wp_change_user_id', $user_id);

    }

    /**
     * @param $wp_user_id
     *
     * @return Array
     * @throws Exception
     */
    public static function get_user_traits($wp_user_id)
    {
        $settings = get_exopite_sof_option('segment-for-wp-by-in8-io');
        $user = get_userdata($wp_user_id);
        $user_data = $user->data;
        $user_info = get_user_meta($wp_user_id);

        $traits = array();
        $trait_options = array(
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email',
            'user_nicename' => 'Username',
            'display_name' => 'Display Name',
            'user_registered' => 'Signup Date',
            'user_url' => 'URL',
            'description' => 'Description'
        );

        if (array_key_exists("included_user_traits", $settings)) {
            if (count($settings["included_user_traits"]) > 0) {
                if (isset($wp_user_id) && $wp_user_id !== 0) {
                    $user_data = get_userdata($wp_user_id);
                    $user_data_keys = array(
                        'user_login' => 'username',
                        'user_nicename' => 'nicename',
                        'user_email' => 'email',
                        'user_url' => 'website',
                        'user_registered' => 'created_at',
                        'display_name' => 'display_name',
                    );
                    $included_traits = $settings['included_user_traits'];
                    foreach ($included_traits as $trait_key) {
                        if ($trait_key != '') {

                            if (array_key_exists($trait_key, $user_data_keys)) {
                                $key = $user_data_keys[$trait_key];
                                if ($key === 'created_at') {
                                    $datetime = new DateTime($user_data->$trait_key);
                                    $traits[$key] = $datetime->format('c');
                                } else {
                                    $traits[$key] = $user_data->$trait_key;

                                }
                            } else {
                                $key = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $trait_key)), '_');
                                $trait_value = get_user_meta($wp_user_id, $trait_key, true);
                                if ($key === 'created_at') {
                                    $datetime = new DateTime($trait_value);
                                    $traits[$key] = $datetime->format('c');
                                } else {
                                    $traits[$key] = $trait_value;
                                }
                            }

                        }

                    }
                }
            }
        }
        if (array_key_exists("custom_user_traits", $settings)) {
            if (count($settings["custom_user_traits"]) > 0) {
                if (isset($wp_user_id) && $wp_user_id !== 0) {
                    $custom_traits = $settings["custom_user_traits"];
                    foreach ($custom_traits as $custom_trait) {
                        $trait_key = $custom_trait['custom_user_traits_key'];
                        if ($trait_key != '') {
                            $trait_value = get_user_meta($wp_user_id, $trait_key, true);
                            $traits[$trait_key] = $trait_value;
                        }
                    }
                }
            }
        }

        // Clean out empty traits and apply filter before sending it back.
        $traits = array_filter($traits);

        //Use the hook 'segment_for_wp_change_user_traits' to modify user traits
        return apply_filters('segment_for_wp_change_user_traits', $traits, $wp_user_id);

    }

    /**
     * @param $current_post
     *
     * @return mixed|string
     */
    public static function get_page_name($current_post)
    {

        $settings = self::get_settings();

        if(!function_exists('is_wplogin')) {
            function is_wplogin()
            {
                $ABSPATH_MY = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);

                return ((in_array($ABSPATH_MY . 'wp-login.php', get_included_files()) || in_array($ABSPATH_MY . 'wp-register.php', get_included_files())) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF'] == '/wp-login.php');
            }
        }

        if (is_wplogin()) {
            if ($settings['login_page_name'] != "") {
                return $settings['login_page_name'];
            }

            return "Login";
        }
        if (is_404()) {
            if ($settings['404_page_name'] != "") {
                return $settings['404_page_name'];
            }

            return "404";
        }
        if (is_front_page()) {
            if ($settings['home_page_name'] != "") {
                return $settings['home_page_name'];
            }

            return "Home";
        }
        if (is_single($current_post) || is_page($current_post)) {
            if ($settings['page_is_custom'] === 'yes' && $settings["page_custom_key"] != "") {
                $key = $settings["page_custom_key"];

                return get_post_meta($current_post->ID, $key, true);
            } else {
                return $current_post->post_title;
            }
        }

        
        return $current_post->post_title??"";
    }

    /**
     * @param $current_post
     *
     * @return array
     */
    public static function get_page_props($current_post)
    {
        $settings = get_exopite_sof_option('segment-for-wp-by-in8-io');
        $properties = [];
        if (is_single($current_post) || is_page($current_post) || is_home() || is_wplogin() || is_front_page() || is_404()) {
            if (array_key_exists('include_custom_page_props', $settings)) {
                if (count($settings['include_custom_page_props']) > 0) {
                    $custom_page_props = $settings['include_custom_page_props'];
                    foreach ($custom_page_props as $custom_prop) {
                        $prop_label = $custom_prop["custom_page_props_label"];
                        $prop_key = $custom_prop["custom_page_props_key"];
                        //get value based on custom key
                        if ($prop_label != "" && get_post_meta($current_post->ID, $prop_key, true)) {

                            $prop_value = get_post_meta($current_post->ID, $prop_key, true);

                            if(is_array($prop_value)){
                                $properties[$prop_label] = array($prop_value);
                            }

                            else if(is_string($prop_value)){
                                $properties[$prop_label] = $prop_value;
                            }
                        }
                    }
                }
            }
        }

        return $properties;

    }

    /**
     * Creates the array of track calls on every page
     *
     * @returns array
     *
     */
    public static function get_current_tracks()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) { //Only render these for actual browsers
            return "stop";
        }
        $tracks = array();
        $settings = self::get_settings();
        $i = 0; // an index to form an array of track calls

        // SIGNUPS
        if ($settings["track_signups_fieldset"]["track_signups"] == "yes") {
            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('user_register', 'short')) {
                $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('user_register');
                $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('user_register');
                $event_name = self::get_event_name('user_register');
                $properties = self::get_event_properties('user_register', $data);
                $tracks[$i] = array(
                    'hook' => 'user_register',
                    'cookie' => $cookie_name,
                    'event' => $event_name,
                    'properties' => $properties,
                );
                if (self::check_associated_identify('event', $event_name)) {
                    $wp_user_id = self::get_wp_user_id('user_register', wp_get_current_user());
                    $user_id = self::get_user_id($wp_user_id);
                    if ($user_id) {
                        $tracks[$i]['identify'] = $user_id;
                    }
                }
                $i++;
            }
        }

        // LOGINS
        if ($settings["track_logins_fieldset"]["track_logins"] == "yes") {
            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_login', 'short')) {
                $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('wp_login');
                $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('wp_login');
                $event_name = self::get_event_name('wp_login');
                $properties = self::get_event_properties('wp_login', $data);
                $tracks[$i] = array(
                    'hook' => 'wp_login',
                    'cookie' => $cookie_name,
                    'event' => $event_name,
                    'properties' => $properties,
                );
                $i++;
            }
        }

        // LOGOUTS
        if ($settings["track_logouts_fieldset"]["track_logouts"] == "yes") {
            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_logout', 'short')) {
                $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('wp_logout');
                $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('wp_logout');
                $event_name = self::get_event_name('wp_logout');
                $properties = self::get_event_properties('wp_logout', $data);
                $tracks[$i] = array(
                    'hook' => 'wp_logout',
                    'cookie' => $cookie_name,
                    'event' => $event_name,
                    'properties' => $properties,
                );
                $i++;
            }
        }

        // COMMENTS
        if ($settings['track_comments_fieldset']['track_comments'] == "yes") {
            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_insert_comment', 'short')) {
                $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('wp_insert_comment');
                $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('wp_insert_comment');
                $event_name = self::get_event_name('wp_insert_comment');
                $properties = self::get_event_properties('wp_insert_comment', $data);
                $tracks[$i] = array(
                    'hook' => 'wp_insert_comment',
                    'cookie' => $cookie_name,
                    'event' => $event_name,
                    'properties' => $properties,
                );
                $i++;
            }
        }

        //NINJA FORMS
        if (array_key_exists('track_ninja_forms_fieldset', $settings) && self::ninja_forms_active()) {
            if ($settings["track_ninja_forms_fieldset"]["track_ninja_forms"] == "yes") {
                if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('ninja_forms_after_submission', 'short')) {
                    $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('ninja_forms_after_submission');
                    $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('ninja_forms_after_submission');
//                  $event_name = self::get_event_name('ninja_forms_after_submission', $data);
//					$wp_user_id ='';
                    $properties = array();
                    if (isset($data['event_name'])) {
                        $event_name = sanitize_text_field($data['event_name']);
                    }
                    if (isset($data['wp_user_id'])) {
                        $wp_user_id = sanitize_text_field($data['wp_user_id']);
                    }
                    if (isset($data['properties'])) {
                        $properties = $data['properties'];
                    }
                    if ($event_name != '') {
                        $tracks[$i] = array(
                            'hook' => 'ninja_forms_after_submission',
                            'cookie' => sanitize_text_field($cookie_name),
                            'event' => sanitize_text_field($event_name),
                            'properties' => $properties,
                        );
                        $i++;
                    }
                    Segment_For_Wp_By_In8_Io_Cookie::delete_cookie($cookie_name);

                }
            }
        }

        //GRAVITY FORMS
//		if ( array_key_exists( 'track_gravity_forms_fieldset', $settings ) && self::gravity_forms_active() ) {
//			if ( $settings["track_gravity_forms_fieldset"]["track_gravity_forms"] == "yes" ) {
//				if ( Segment_For_Wp_By_In8_Io_Cookie::check_cookie( 'gform_after_submission', 'short') ) {
//					$data        = Segment_For_Wp_By_In8_Io_Cookie::get_cookie( 'gform_after_submission' );
//					$cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name( 'gform_after_submission' );
//
//					$event_name  = '';
//					$properties = array();
//					if ( isset( $data['event_name'] ) ) {
//						$event_name = sanitize_text_field( $data['event_name'] );
//					}
//
//					if ( isset( $data['properties'] ) ) {
//						$properties = $data['properties'];
//					}
//					if ( $event_name != '' ) {
//						$tracks[ $i ] = array(
//							'hook'       => 'gform_after_submission',
//							'cookie'     => sanitize_text_field( $cookie_name ),
//							'event'      => sanitize_text_field( $event_name ),
//							'properties' => $properties,
//						);
//						$i ++;
//					}
//
//					Segment_For_Wp_By_In8_Io_Cookie::delete_cookie($cookie_name);
//					Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies('gform_after_submission');
//
//					if ($settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes' ) {
//						Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies('gravity_forms_identify');
//
//					}
//
//				}
//			}
//		}

        //WOOCOMMERCE
        if (array_key_exists('track_woocommerce_fieldset', $settings) && self::woocommerce_active()) {
            if ($settings["track_woocommerce_fieldset"]["track_woocommerce"] == "yes") {

                // PRODUCT ADDED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_add_to_cart')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_add_to_cart');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_add_to_cart');
                        $event_name = self::get_event_name('woocommerce_add_to_cart');
                        $properties = self::get_event_properties('woocommerce_add_to_cart', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_add_to_cart',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;
                    }
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_cart_item_restored')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_cart_item_restored');
                        $event_name = self::get_event_name('woocommerce_cart_item_restored');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_cart_item_restored');
                        foreach ($data['cart_contents'] as $item) {
                            $args["product_id"] = $item["product_id"];
                            $args["variation_id"] = $item["variation_id"];
                            $args["quantity"] = $item["quantity"];
                            $properties = Segment_For_Wp_By_In8_Io::get_event_properties('woocommerce_cart_item_restored', $args);
                            $properties = array_filter(array_merge($args, $properties));
                            $tracks[$i] = array(
                                'hook' => 'woocommerce_cart_item_restored',
                                'cookie' => $cookie_name,
                                'event' => $event_name,
                                'properties' => $properties,
                            );
                            $i++;
                        }
                    }
                }

                // PRODUCT REMOVED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_remove_cart_item', 'short')) {

                        $event_name = self::get_event_name('woocommerce_remove_cart_item');

                        $add_to_cart_cookies = Segment_For_Wp_By_In8_Io_Cookie::get_matching_cookies('woocommerce_remove_cart_item');

                        foreach ($add_to_cart_cookies as $cookie_name => $data) {

                            $properties = self::get_event_properties('woocommerce_remove_cart_item', $data);

                            $tracks[$i] = array(
                                'hook' => 'woocommerce_remove_cart_item',
                                'cookie' => $cookie_name,
                                'event' => $event_name,
                                'properties' => $properties,
                            );

                            $i++;

                        }

                    }
                }

                // ORDER PENDING
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_pending"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_order_status_pending', 'short')) {

                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_order_status_pending');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_order_status_pending');
                        $event_name = self::get_event_name('woocommerce_order_status_pending');
                        $properties = self::get_event_properties('woocommerce_order_status_pending', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_pending',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER FAILED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_failed"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_order_status_failed', 'short')) {

                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_order_status_failed');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_order_status_failed');
                        $event_name = self::get_event_name('woocommerce_order_status_failed');
                        $properties = self::get_event_properties('woocommerce_order_status_failed', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_failed',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER PROCESSING
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_processing"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_order_status_processing', 'short')) {

                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_order_status_processing');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_order_status_processing');
                        $event_name = self::get_event_name('woocommerce_order_status_processing');
                        $properties = self::get_event_properties('woocommerce_order_status_processing', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_processing',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER COMPLETED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_completed"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_order_status_completed', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_order_status_completed');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_order_status_completed');
                        $event_name = self::get_event_name('woocommerce_order_status_completed');
                        $properties = self::get_event_properties('woocommerce_order_status_completed', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_completed',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER PAID
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_paid"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_payment_complete', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_payment_complete');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_payment_complete');
                        $event_name = self::get_event_name('woocommerce_payment_complete');
                        $properties = self::get_event_properties('woocommerce_payment_complete', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_payment_complete',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER ON HOLD
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_on_hold"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_event_order_on_hold', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_event_order_on_hold');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_event_order_on_hold');
                        $event_name = self::get_event_name('woocommerce_order_status_on_hold');
                        $properties = self::get_event_properties('woocommerce_order_status_on_hold', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_on_hold',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER REFUNDED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_refunded"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_event_order_refunded', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_event_order_refunded');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_event_order_refunded');
                        $event_name = self::get_event_name('woocommerce_event_order_refunded');
                        $properties = self::get_event_properties('woocommerce_event_order_refunded', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_event_order_refunded',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // ORDER CANCELLED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_order_cancelled"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_order_status_cancelled', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_order_status_cancelled');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_order_status_cancelled');
                        $event_name = self::get_event_name('woocommerce_order_status_cancelled');
                        $properties = self::get_event_properties('woocommerce_order_status_cancelled', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_order_status_cancelled',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

                // COUPON APPLIED
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_coupon_applied"] == 'yes') {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('woocommerce_applied_coupon', 'short')) {
                        $data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('woocommerce_applied_coupon');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('woocommerce_applied_coupon');
                        $event_name = self::get_event_name('woocommerce_applied_coupon');
                        $properties = self::get_event_properties('woocommerce_applied_coupon', $data);
                        $tracks[$i] = array(
                            'hook' => 'woocommerce_applied_coupon',
                            'cookie' => $cookie_name,
                            'event' => $event_name,
                            'properties' => $properties,
                        );
                        $i++;

                    }
                }

            }
        }

        if (!isset($tracks)) { // We don't have any track calls
            $tracks = false;
        }

        return $tracks; // Returns an array of track calls
    }

    /**
     * Returns the event name for action hooks
     *
     * @param $action
     * @param null $data
     * @return string
     */
    public static function get_event_name($action, $data = null)
    {

        $settings = self::get_settings();

        if ($action == 'user_register') {
            if ($settings["track_signups_fieldset"]["track_signups_client_event_name"] != "") {
                return $settings["track_signups_fieldset"]["track_signups_client_event_name"];
            } else {
                return 'Signed up';
            }
        } elseif ($action == 'user_register_server') {
            if ($settings["track_signups_fieldset"]["track_signups_server_event_name"] != "") {
                return $settings["track_signups_fieldset"]["track_signups_server_event_name"];
            } else {
                return 'Signed up';
            }
        } elseif ($action == 'register_new_user') {
            if ($settings["track_signups_fieldset"]["track_signups_server_event_name"] != "") {
                return $settings["track_signups_fieldset"]["track_signups_server_event_name"];
            } else {
                return 'Signed up';
            }
        } elseif ($action == 'wp_login') {
            if ($settings["track_logins_fieldset"]["track_logins_client_event_name"] != "") {
                return $settings["track_logins_fieldset"]["track_logins_client_event_name"];
            } else {
                return 'Logged in';
            }
        } elseif ($action == 'wp_login_server') {
            if ($settings["track_logins_fieldset"]["track_logins_server_event_name"] != "") {
                return $settings["track_logins_fieldset"]["track_logins_server_event_name"];
            } else {
                return 'Logged in';
            }
        } elseif ($action == 'wp_logout') {
            if ($settings["track_logouts_fieldset"]["track_logouts_client_event_name"] != "") {
                return $settings["track_logouts_fieldset"]["track_logouts_client_event_name"];
            } else {
                return 'Logged out';
            }
        } elseif ($action == 'wp_logout_server') {
            if ($settings["track_logouts_fieldset"]["track_logouts_server_event_name"] != "") {
                return $settings["track_logouts_fieldset"]["track_logouts_server_event_name"];
            } else {
                return 'Logged out';
            }
        } elseif ($action == 'wp_insert_comment') {
            if ($settings["track_comments_fieldset"]["track_comments_client_event_name"] != "") {
                return $settings["track_comments_fieldset"]["track_comments_client_event_name"];
            } else {
                return 'Comment posted';
            }
        } elseif ($action == 'wp_insert_comment_server') {
            if ($settings["track_comments_fieldset"]["track_comments_server_event_name"] != "") {
                return $settings["track_comments_fieldset"]["track_comments_server_event_name"];
            } else {
                return 'Comment posted';
            }
        } elseif ($action == 'ninja_forms_after_submission' || $action == 'ninja_forms_after_submission_server') {
            foreach ($data["args"][0]["fields"] as $field) {
                if ($field["value"] != "") {
                    if ($field["admin_label"] == $settings["track_ninja_forms_fieldset"]["ninja_forms_event_name_field"]) {
                        return sanitize_text_field($field["value"]);
                    }
                }
            }
            return 'Completed Form';
        } elseif ($action == 'gform_after_submission' || $action == 'gform_after_submission_server') {
            $entry = $data["args"][0];
            $form = $data["args"][1];
            $gf_event_name_field = sanitize_text_field($settings["track_gravity_forms_fieldset"]["gravity_forms_event_name_field"]);

            foreach ($form['fields'] as $field) {
                if ($gf_event_name_field != '') {
                    if ($field["adminLabel"] == $gf_event_name_field) {
                        $gf_event_name = rgar($entry, $field["id"]);
                        if ($gf_event_name != '') {
                            return sanitize_text_field($gf_event_name);
                        }
                    }
                }
            }

            return 'Completed Form';
        } elseif (self::woocommerce_active()) {

            if ($action == 'woocommerce_add_to_cart' ||
                $action == 'woocommerce_add_to_cart_fragments' ||
                $action == 'woocommerce_add_to_cart_redirect' ||
                $action == 'woocommerce_ajax_add_to_cart' ||
                $action == 'woocommerce_after_cart' ||
                $action == 'woocommerce_cart_item_restored' ||
                $action == 'segment_4_wp_wc_cart_ajax_item_added') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_added"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_added"];
                } else {
                    return 'Product Added';
                }
            } elseif ($action == 'woocommerce_add_to_cart_server' || $action == 'woocommerce_cart_item_restored_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_added_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_added_server_event_name"];
                } else {
                    return 'Product Added';
                }

            } elseif ($action == 'woocommerce_remove_cart_item' || $action == 'segment_4_wp_wc_cart_ajax_item_removed') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_removed"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_removed"];
                } else {
                    return 'Product Removed';
                }

            } elseif ($action == 'woocommerce_cart_item_removed_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_removed_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_events_product_removed_server_event_name"];
                } else {
                    return 'Product Removed';
                }

            } elseif ($action == 'woocommerce_order_status_pending') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_pending"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_pending"];
                } else {
                    return 'Order Pending';
                }
            } elseif ($action == 'woocommerce_order_status_pending_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_pending_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_pending_server_event_name"];
                } else {
                    return 'Order Pending';
                }
            } elseif ($action == 'woocommerce_order_status_failed') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_failed"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_failed"];
                } else {
                    return 'Order Failed';
                }
            } elseif ($action == 'woocommerce_order_status_failed_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_failed_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_failed_server_event_name"];
                } else {
                    return 'Order Failed';
                }
            } elseif ($action == 'woocommerce_order_status_processing') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_processing"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_processing"];
                } else {
                    return 'Order Processing';
                }
            } elseif ($action == 'woocommerce_order_status_processing_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_processing_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_processing_server_event_name"];
                } else {
                    return 'Order Processing';
                }
            } elseif ($action == 'woocommerce_order_status_completed') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_completed"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_completed"];
                } else {
                    return 'Order Completed';
                }
            } elseif ($action == 'woocommerce_order_status_completed_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_completed_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_completed_server_event_name"];
                } else {
                    return 'Order Completed';
                }
            } elseif ($action == 'woocommerce_payment_complete') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_paid"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_paid"];
                } else {
                    return 'Order Paid';
                }
            } elseif ($action == 'woocommerce_payment_complete_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_paid_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_paid_server_event_name"];
                } else {
                    return 'Order Paid';
                }
            } elseif ($action == 'woocommerce_order_status_on_hold') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_on_hold"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_on_hold"];
                } else {
                    return 'Order On Hold';
                }
            } elseif ($action == 'woocommerce_order_status_on_hold_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_on_hold_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_on_hold_server_event_name"];
                } else {
                    return 'Order On Hold';
                }
            } elseif ($action == 'woocommerce_event_order_refunded') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_refunded"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_refunded"];
                } else {
                    return 'Order Refunded';
                }
            } elseif ($action == 'woocommerce_event_order_refunded_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_refunded"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_refunded"];
                } else {
                    return 'Order Refunded';
                }
            } elseif ($action == 'woocommerce_order_status_cancelled') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_cancelled"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_cancelled"];
                } else {
                    return 'Order Cancelled';
                }
            } elseif ($action == 'woocommerce_order_status_cancelled_server') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_cancelled_server_event_name"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_cancelled_server_event_name"];
                } else {
                    return 'Order Cancelled';
                }
            } elseif ($action == 'segment_4_wp_wc_cart_ajax_coupon_applied') {
                if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_coupon_applied"] != "") {
                    return $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["woocommerce_event_order_coupon_applied"];
                } else {
                    return 'Coupon Applied';
                }
            }
        }

    }

    /**
     * Returns the event props for actions
     *
     * @param $action
     *
     * @param $data
     *
     * @return array
     */
    public static function get_event_properties($action, $data)
    {
        $settings = get_exopite_sof_option('segment-for-wp-by-in8-io');
        $properties = [];
        if ($action == 'user_register') {
            $wp_user_id = $data["wp_user_id"];
        } elseif ($action == 'edit_user_created_user') {
            $wp_user_id = $data["wp_user_id"];
        } elseif ($action == 'register_new_user') {
            $wp_user_id = $data["wp_user_id"];
        } elseif ($action == 'wp_login') {
            $wp_user_id = $data["wp_user_id"];
        } elseif ($action == 'wp_logout') {
            $wp_user_id = $data["wp_user_id"];
        } elseif ($action == 'wp_insert_comment') {
            if ($data["args"][1]["user_id"] != 0) {
                $wp_user_id = $data["args"][1]["user_id"];
            }
            $properties["comment_id"] = $data["args"][0];
            $properties["comment_post_id"] = $data["args"][1]["comment_ID"];
            $properties["comment_author"] = $data["args"][1]["comment_author"];
            $properties["comment_author_email"] = $data["args"][1]["comment_author_email"];
            $properties["comment_author_url"] = $data["args"][1]["comment_author_url"];
        } elseif ($action == 'ninja_forms_after_submission') {
            if (array_key_exists('ninja_form_event_properties', $settings["track_ninja_forms_fieldset"])) {
                if (count($settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"]) > 0) {
                    $ninja_form_event_properties = $settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"];
                    foreach ($data["args"][0]["fields"] as $field) {
                        if ($field["value"] != "") {
                            foreach ($ninja_form_event_properties as $event_property) {
                                if ($field["admin_label"] == $event_property["ninja_form_event_property_field_id"]) {
                                    $properties[$event_property["ninja_form_event_property_label"]] = $field["value"];
                                }
                            }

                        }
                    }

                }

            }
        } elseif ($action == 'gform_after_submission') {
            if (array_key_exists('gravity_form_event_properties', $settings["track_gravity_forms_fieldset"])) {
                $entry = $data["args"][0];
                $form = $data["args"][1];
                $gf_event_props = array();
                foreach ($form['fields'] as $field) {

                    if (array_key_exists('gravity_form_event_properties', $settings["track_gravity_forms_fieldset"]) && count($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"]) > 0) {
                        foreach ($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"] as $property) {
                            if ($property["gravity_form_event_property_field_id"] != '') {
                                $gf_field_label_key = $property["gravity_form_event_property_field_id"];
                                $gf_label_text = $property["gravity_form_event_property_label"];
                                if ($field["adminLabel"] == $gf_field_label_key) {
                                    $gf_field_id = $field["id"];

                                    $value = $entry[$gf_field_id];

                                    if ($value && $value != '') {
                                        $gf_event_props[sanitize_text_field($gf_label_text)] = sanitize_text_field($value);
                                    }

                                }
                            }


                        }

                    }

                }
                return array_filter($gf_event_props);

            }
        } elseif (self::woocommerce_active()) {
            if ($action == 'woocommerce_add_to_cart') {
                if (isset($data['wp_user_id'])) {
                    $wp_user_id = $data["wp_user_id"];
                }
                if (array_key_exists('product_id', $data) && is_numeric($data["product_id"])) {
                    $properties = self::get_product_props_from_product_id($data["product_id"]);
                    $properties['variant'] = $data["variation"] ?? null;
                    $properties['variant_id'] = $data["variation_id"] ?? null;
                }

            } elseif ($action == 'woocommerce_after_cart_item_quantity_update') {
                if (isset($data['wp_user_id'])) {
                    $wp_user_id = $data["wp_user_id"];
                }
                if (array_key_exists('product_id', $data) && is_numeric($data["product_id"])) {
                    $properties = self::get_product_props_from_product_id($data["product_id"]);
                    $properties['quantity'] = $data["quantity"];
                }
            } elseif ($action == 'woocommerce_add_to_cart_redirect') {
                if (isset($data['wp_user_id'])) {
                    $wp_user_id = $data["wp_user_id"];
                }
                $properties = self::get_product_props_from_product_id($data["product_id"]);
                $properties['variant'] = $data["variation"];
                $properties['variant_id'] = $data["variation_id"];

            } elseif ($action == 'woocommerce_after_cart') {
                if (isset($data['wp_user_id'])) {
                    $wp_user_id = $data["wp_user_id"];
                }
                $properties = self::get_product_props_from_product_id($data["product_id"]);
                $properties['variant'] = $data["variation"] ?? null;
                $properties['variant_id'] = $data["variation_id"] ?? null;

            } elseif ($action == 'woocommerce_add_to_cart_fragments') {
                $properties = self::get_product_props_from_product_id($data["args"]["product_id"]);
                $properties['variant'] = $data["args"]["variation"] ?? null;
                $properties['variant_id'] = $data["args"]["variation_id"] ?? null;
            } elseif ($action == 'woocommerce_after_cart_item_quantity_update') {
                $properties = self::get_product_props_from_product_id($data["product_id"]);
            } elseif ($action == 'woocommerce_cart_item_restored') {
                $properties = self::get_product_props_from_product_id($data["product_id"]);
            } elseif ($action == 'woocommerce_remove_cart_item') {
                $properties = self::get_product_props_from_product_id($data["args"]["product_id"]);
                $properties['variant'] = $data["args"]["variation"] ?? null;
                $properties['variant_id'] = $data["args"]["variation_id"] ?? null;
            } elseif ($action == 'woocommerce_cart_item_removed') {
                $properties = self::get_product_props_from_product_id($data["args"]["product_id"]);
                $properties['quantity'] = $data["args"]["quantity"] ?? null;
                $properties['variant'] = $data["args"]["variation"] ?? null;
                $properties['variant_id'] = $data["args"]["variation_id"] ?? null;
            } elseif ($action == 'segment_4_wp_wc_cart_ajax_item_removed') {
                $properties = self::get_product_props_from_product_id($data["product_id"]);
                $properties['variant'] = $data["variation"] ?? null;
                $properties['variant_id'] = $data["variation_id"] ?? null;
            } elseif ($action == 'segment_4_wp_wc_cart_ajax_item_added') {
                $properties = self::get_product_props_from_product_id($data["product_id"]);
                $properties['variant'] = $data["variation"] ?? null;
                $properties['variant_id'] = $data["variation_id"] ?? null;
            } //Orders
            elseif ($action == 'woocommerce_order_status_pending') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }

            } elseif ($action == 'woocommerce_order_status_failed') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_order_status_processing') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_order_status_completed') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_payment_complete') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_order_status_on_hold') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_order_status_refunded') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'woocommerce_order_status_cancelled') {
                if (is_numeric($data["order_id"])) {
                    $properties["order_id"] = $data["order_id"];
                    $properties = self::get_order_props_from_order_id($properties["order_id"]);
                }
            } elseif ($action == 'segment_4_wp_wc_cart_ajax_coupon_applied') {
                $coupon_code = $data;
                $coupon_id = wc_get_coupon_id_by_code($coupon_code);
                $coupon_type = wc_get_coupon_type($coupon_code);
                $properties["coupon_code"] = $coupon_code;
                $properties["coupon_id"] = $coupon_id;
                $properties["coupon_type"] = $coupon_type;
            }

            if (!is_user_logged_in()) {
                if (strpos($action, 'woocommerce_order') !== false || $action === 'woocommerce_payment_complete') {
                    if (array_key_exists("woocommerce_match_logged_out_users", $settings["track_woocommerce_fieldset"])) {
                        if ($settings["track_woocommerce_fieldset"]["woocommerce_match_logged_out_users"] === "yes") {
                            if (array_key_exists('billing_email', $properties)) {
                                $order_email = $properties["billing_email"];
                                if (filter_var($order_email, FILTER_VALIDATE_EMAIL)) {
                                    if (email_exists($order_email)) {
                                        $user = get_user_by('email', $order_email);
                                        $wp_user_id = self::get_user_id($user->ID);
                                    }
                                }
                            }

                        }
                    }

                }
            }
        }

        if ($settings["include_user_ids"] == "yes") {
            if (isset($wp_user_id) && $wp_user_id !== 0) {
                $user_id = self::get_user_id($wp_user_id);
                if ($user_id) {
                    $properties["user_id"] = $user_id;
                    $user = get_userdata($wp_user_id);
                    $properties["user_email"] = $user->user_email;
                }

            } else {
                if (is_user_logged_in()) {
                    $wp_user_id = get_current_user_id();
                    $user_id = self::get_user_id($wp_user_id);
                    $properties["user_id"] = $user_id;
                    $user = get_userdata($wp_user_id);
                    $properties["user_email"] = $user->user_email;
                }
            }
        }

        $properties = array_filter($properties);

        return apply_filters('segment_for_wp_change_event_properties', $properties, $action, $data);

    }

    /**
     * Get a product's details in an array
     *
     * @param $product_id
     *
     * @return array
     */
    public static function get_product_props_from_product_id($product_id)
    {
        // Make a $product object from product ID
        $properties = array();
        $product = wc_get_product($product_id);
        if ($product) {
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail');
            $product_meta = $product->get_meta_data() ?? null;
            $properties['product_id'] = $product_id ?? null;
            $properties['sku'] = $product->get_sku() ?? null;
            $product_categories = $product->get_category_ids() ?? null;
            if (is_array($product_categories)) {
                foreach ($product_categories as $key => $value) {
                    if ($key == 0) {
                        $properties['product_category'] = $value ?? null;
                    } else {
                        $properties['product_category_' . ($key + 1)] = $value ?? null;
                    }
                }
            }
            $properties['name'] = $product->get_name() ?? null;
            $properties['price'] = $product->get_price() ?? null;
            $properties['regular_price'] = $product->get_regular_price() ?? null;
            $properties['sale_price'] = $product->get_sale_price() ?? null;
            $properties['on_sale_from'] = $product->get_date_on_sale_from() ?? null;
            $properties['on_sale_to'] = $product->get_date_on_sale_to() ?? null;
            //$properties['total_sales']        = $product->get_total_sales(); //switching this on prob not a good idea
            $properties['url'] = get_permalink($product_id) ?? null;
            $properties['image_url'] = $image_url[0] ?? null;
            $properties['slug'] = $product->get_slug() ?? null;
//            $properties['date_created'] = $product->get_date_created() ?? null;
//            $properties['date_modified'] = $product->get_date_modified() ?? null;
            $properties['status'] = $product->get_status() ?? null;
            $properties['featured'] = $product->get_featured() ?? null;
            $properties['catalog_visibility'] = $product->get_catalog_visibility() ?? null;
            //  $properties['description']        = $product->get_description(); can be quite long
            //  $properties['short_description']  = $product->get_short_description(); can be quite long
            $properties['position'] = $product->get_menu_order() ?? null;
            $properties['tax_status'] = $product->get_tax_status() ?? null;
            $properties['tax_class'] = $product->get_tax_class() ?? null;
            $properties['manage_stock'] = $product->get_manage_stock() ?? null;
            $properties['stock_quantity'] = $product->get_stock_quantity() ?? null;
            $properties['stock_status'] = $product->get_stock_status() ?? null;
            $properties['backorders'] = $product->get_backorders() ?? null;
            $properties['sold_individually'] = $product->get_sold_individually() ?? null;
            //  $properties['purchase_note']      = $product->get_purchase_note();     ??null;
            $properties['shipping_class'] = $product->get_shipping_class_id() ?? null;
            $properties['weight'] = $product->get_weight() ?? null;
            $properties['length'] = $product->get_length() ?? null;
            $properties['width'] = $product->get_width() ?? null;
            $properties['height'] = $product->get_height() ?? null;
            //  $properties['dimensions']     = $product->get_dimensions();
            //  $properties['upsell_ids']        = json_encode($product->get_upsell_ids());
            //  $properties['cross_sell_ids']    = json_encode($product->get_cross_sell_ids());
            $properties['parent_id'] = $product->get_parent_id() ?? null;
            $properties['variations'] = $product->get_attributes() ?? null;
            $properties['default_variation'] = $product->get_default_attributes() ?? null;
            //  $properties['categories']         = $product->get_categories(); HTML
            //  $properties['category_ids']    = json_encode($product->get_category_ids()); //Array
            //  $properties['tag_ids']         = json_encode($product->get_tag_ids());
            $properties['downloads'] = $product->get_downloads() ?? null;
            $properties['download_expiry'] = $product->get_download_expiry() ?? null;
            $properties['downloadable'] = $product->get_downloadable() ?? null;
            $properties['download_limit'] = $product->get_download_limit() ?? null;
            $properties['image_id'] = $product->get_image_id() ?? null;
            //  $properties['image']              = $product->get_image(); HTML
            //	$properties['gallery_image_ids'] = json_encode($product->get_gallery_image_ids());
            $properties['reviews_allowed'] = $product->get_reviews_allowed() ?? null;
            $properties['rating_count'] = $product->get_rating_counts() ?? null;
            $properties['average_rating'] = $product->get_average_rating() ?? null;
            $properties['review_count'] = $product->get_review_count() ?? null;

            if ($properties["sku"] == '') {
                $properties["sku"] = $properties['product_id'] ?? null;
            }
        }

        //clean and return
        $properties = array_filter($properties);

        return apply_filters('segment_for_wp_change_product_properties', $properties);

    }

    /**
     * Returns event props from an order id
     *
     * @param $order_id
     *
     * @return array
     */
    public static function get_order_props_from_order_id($order_id)
    {

        $order = wc_get_order($order_id);
        if ($order) {
            $total = (double)$order->get_total() ?? null;
            $tax = (double)$order->get_total_tax() ?? null;
            $shipping = (double)$order->get_shipping_total() ?? null;
            //TODO explain this to users
            $revenue = $total - $shipping - $tax;
            $order_properties = array(
                'order_id' => $order_id,
                //affiliation
                'total' => $total,
                'revenue' => $revenue,
                'shipping' => (double)$order->get_shipping_total(),
                'tax' => $tax,
                'discount' => $order->get_discount_total(),
                'cart_tax' => $order->get_cart_tax(),
                'currency' => $order->get_currency(),
                'discount_tax' => $order->get_discount_tax(),
                'fees' => $order->get_fees(),
                'shipping_tax' => $order->get_shipping_tax(),
                'subtotal' => $order->get_subtotal(),
                'tax_totals' => $order->get_tax_totals(),
                'taxes' => $order->get_taxes(),
                'total_refunded' => $order->get_total_refunded(),
                'total_tax_refunded' => $order->get_total_tax_refunded(),
                'total_shipping_refunded' => $order->get_total_shipping_refunded(),
                'item_count_refunded' => $order->get_item_count_refunded(),
                'total_quantity_refunded' => $order->get_total_qty_refunded(),
                'remaining_refund_amount' => $order->get_remaining_refund_amount(),
                'shipping_method' => $order->get_shipping_method(),
                'shipping_methods' => $order->get_shipping_methods(),
                'date_created' => $order->get_date_created(),
                'date_modified' => $order->get_date_modified(),
                'date_completed' => $order->get_date_completed(),
                'date_paid' => $order->get_date_paid(),
                'customer_id' => $order->get_customer_id(),
                'userId' => $order->get_user_id(),
                'ip_address' => $order->get_customer_ip_address(),
                'customer_user_agent' => $order->get_customer_user_agent(),
                'created_via' => $order->get_created_via(),
                //'customer_note'               => $order->get_customer_note(),
                'billing_first_name' => $order->get_billing_first_name(),
                'billing_last_name' => $order->get_billing_last_name(),
                'billing_company' => $order->get_billing_company(),
                'billing_address_1' => $order->get_billing_address_1(),
                'billing_address_2' => $order->get_billing_address_2(),
                'billing_city' => $order->get_billing_city(),
                'billing_state' => $order->get_billing_state(),
                'billing_postcode' => $order->get_billing_postcode(),
                'billing_country' => $order->get_billing_country(),
                'billing_email' => $order->get_billing_email(),
                'billing_phone' => $order->get_billing_phone(),
                'shipping_first_name' => $order->get_shipping_first_name(),
                'shipping_last_name' => $order->get_shipping_last_name(),
                'shipping_company' => $order->get_shipping_company(),
                'shipping_address_1' => $order->get_shipping_address_1(),
                'shipping_address_2' => $order->get_shipping_address_2(),
                'shipping_city' => $order->get_shipping_city(),
                'shipping_state' => $order->get_shipping_state(),
                'shipping_postcode' => $order->get_shipping_postcode(),
                'shipping_country' => $order->get_shipping_country(),
                'shipping_address' => $order->get_address(),
                'shipping_address_map_url' => $order->get_shipping_address_map_url(),
                'payment_method' => $order->get_payment_method(),
                'payment_method_title' => $order->get_payment_method_title(),
                'transaction_id' => $order->get_transaction_id(),
                'checkout_payment_url' => $order->get_checkout_payment_url(),
                'checkout_order_received_url' => $order->get_checkout_order_received_url(),
                'cancel_order_url' => $order->get_cancel_order_url(),
                'cancel_order_url_raw' => $order->get_cancel_order_url_raw(),
                //'cancel_order_endpoint'       => $order->get_cancel_endpoint(),
                'view_order_url' => $order->get_view_order_url(),
                'edit_order_url' => $order->get_edit_order_url(),
            );
            $order_properties['products'] = self::get_product_array_from_order_array($order) ?? null;
        } else {
            $order_properties = array();
        }
        //clean and return
        $order_properties = array_filter($order_properties);

        return apply_filters('segment_for_wp_change_order_properties', $order_properties);
    }

    /**
     * Get full product details from order id
     *
     * @param $order
     *
     * @return array
     */
    public static function get_product_array_from_order_array($order)
    {
        $properties = array();
        foreach ($order->get_items() as $item_id => $item) {
            // Get an instance of corresponding the WC_Product object
            $product = $item->get_product() ?? null;
            $quantity = $item->get_quantity() ?? null;
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id() ?? null), 'single-post-thumbnail');
            $items_data[$item_id] = array(
                'quantity' => $quantity,
                'product_id' => $product->get_id() ?? null,
                'sku' => $product->get_name() ?? null,
                'category' => $product->get_category_ids() ?? null,
                'name' => $product->get_name() ?? null,
                'price' => $product->get_price() ?? null,
                'regular_price' => $product->get_regular_price() ?? null,
                'sale_price' => $product->get_sale_price() ?? null,
                'on_sale_from' => $product->get_date_on_sale_from() ?? null,
                'on_sale_to' => $product->get_date_on_sale_to() ?? null,
                //'total_sales'        => $product->get_total_sales(),
                'url' => get_permalink($product->get_id()) ?? null,
                'image_url' => $image_url,
                'slug' => $product->get_slug() ?? null,
                'date_created' => $product->get_date_created() ?? null,
                'date_modified' => $product->get_date_modified() ?? null,
                'status' => $product->get_status() ?? null,
                'featured' => $product->get_featured() ?? null,
                'catalog_visibility' => $product->get_catalog_visibility() ?? null,
                //'description'        => $product->get_description(),
                //'short_description'  => $product->get_short_description(),
                'position' => $product->get_menu_order() ?? null,
                'tax_status' => $product->get_tax_status() ?? null,
                'tax_class' => $product->get_tax_class() ?? null,
                'manage_stock' => $product->get_manage_stock() ?? null,
                'stock_quantity' => $product->get_stock_quantity() ?? null,
                'stock_status' => $product->get_stock_status() ?? null,
                'backorders' => $product->get_backorders() ?? null,
                'sold_individually' => $product->get_sold_individually() ?? null,
                //'purchase_note'      => $product->get_purchase_note(),
                'shipping_class' => $product->get_shipping_class_id() ?? null,
                'weight' => $product->get_weight() ?? null,
                'length' => $product->get_length() ?? null,
                'width' => $product->get_width() ?? null,
                'height' => $product->get_height() ?? null,
//                'dimensions' => $product->get_dimensions()??null,
                'upsell_ids' => $product->get_upsell_ids() ?? null,
                'cross_sell_ids' => $product->get_cross_sell_ids() ?? null,
                'parent_id' => $product->get_parent_id() ?? null,
                'variations' => $product->get_attributes() ?? null,
                'default_variation' => $product->get_default_attributes() ?? null,
//                'categories' => $product->get_categories()??null,
                'category_ids' => $product->get_category_ids() ?? null,
                'tag_ids' => $product->get_tag_ids() ?? null,
                'downloads' => $product->get_downloads() ?? null,
                'download_expiry' => $product->get_download_expiry() ?? null,
                'downloadable' => $product->get_download_expiry() ?? null,
                'download_limit' => $product->get_download_limit() ?? null,
                'image_id' => $product->get_image_id() ?? null,
                'image' => $product->get_image() ?? null,
                'gallery_image_ids' => $product->get_gallery_image_ids() ?? null,
                'reviews_allowed' => $product->get_reviews_allowed() ?? null,
                'rating_count' => $product->get_rating_counts() ?? null,
                'average_rating' => $product->get_average_rating() ?? null,
                'review_count' => $product->get_review_count() ?? null
            );
            $items_data[$item_id] = array_filter($items_data[$item_id]);
        }

        $items_data = array_values($items_data);
        $properties = array_merge($items_data);
        $properties = array_filter($properties);

        return $properties;
    }

    /**
     * @param $type 'hook or event'
     * @param $name 'name of the hook or of the event'
     *
     * @return bool
     */
    public static function check_associated_identify($type, $name)
    {
        $settings = self::get_settings();
        $identify_associated_events = $settings["identify_associated_events"];
        if (!isset($identify_associated_events) || count($identify_associated_events) == 0) {
            return false;
        }
        if ($type == 'event') {
            return in_array($name, $identify_associated_events);
        }
        if ($type == 'hook') {
            return in_array($name, $identify_associated_events);
        }
    }

    /**
     * @param $action
     * @param $data
     *
     * @return string|null
     */
    public static function get_wp_user_id($action, $data)
    {

        if ($action == 'wp_head') {

            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('user_register', 'short')) {
                $cookie_data = json_decode(stripslashes(Segment_For_Wp_By_In8_Io_Cookie::get_cookie('user_register')));

                return $cookie_data->wp_user_id;
            }

        }
        if ($action == 'user_register_server') {

            if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('user_register', 'short')) {
                $cookie_data = json_decode(stripslashes(Segment_For_Wp_By_In8_Io_Cookie::get_cookie('user_register')));

                return $cookie_data->wp_user_id;
            }

        }
        if ($action == 'register_new_user') {
            return $data["args"][0];
        }

        //COMMENTS
        if ($action == "wp_insert_comment") {
            $comment = $data["args"][1];
            //args[0]=$comment_id, args[1]=$comment
            if (isset ($comment["user_id"]) && $comment["user_id"] != 0) {
                return $comment["user_id"];
            } elseif (isset ($comment["comment_author_email"])) {
                $user_email = $comment["comment_author_email"];
                if (email_exists($user_email)) {
                    $user = get_user_by('email', $user_email);

                    return $user->ID;
                }

            } else {
                return null;
            }

        }

        if ($action == "wp_insert_comment_server") {
            $comment = $data["args"][1];
            $comment_wp_user_id = $comment["user_id"];
            //args[0]=$comment_id, args[1]=$comment
            if ($comment_wp_user_id && $comment_wp_user_id !== 0) {
                return $comment_wp_user_id;
            } elseif (isset ($comment["comment_author_email"])) {
                $user_email = $comment["comment_author_email"];
                if (email_exists($user_email)) {
                    $user = get_user_by('email', $user_email);

                    return $user->ID;
                }
            } else {
                return null;
            }

        }

        if (is_user_logged_in()) {

            return get_current_user_id();
        }

        return null;

    }

    /**
     * Check if it's an ecommerce event hooks
     *
     * @param $action_hook
     *
     * @return bool
     */
    public static function is_ecommerce_hook($action_hook)
    {

        $ecommerce_hooks = array(
            'woocommerce_before_single_product',
            'woocommerce_add_to_cart',
            'woocommerce_ajax_added_to_cart',
            'woocommerce_remove_cart_item',
            'woocommerce_cart_item_restored',
            'woocommerce_before_cart',
            'woocommerce_before_checkout_form',
            'woocommerce_order_status_pending',
            'woocommerce_order_status_processing',
            'woocommerce_order_status_completed',
            'woocommerce_payment_complete',
            'woocommerce_order_status_cancelled',
            'woocommerce_applied_coupon',
            'is_checkout',
            'is_cart',
            'woocommerce_checkout_process'
        );

        return in_array($action_hook, $ecommerce_hooks);

    }

    /**
     * Check if it's an ecommerce event hooks
     *
     * @param $action_hook
     *
     * @return bool
     */
    public static function is_ecommerce_order_hook($action_hook)
    {

        $ecommerce_order_hooks = array(
            'woocommerce_order_status_pending',
            'woocommerce_order_status_processing',
            'woocommerce_order_status_completed',
            'woocommerce_payment_complete',
            'woocommerce_order_status_cancelled',
        );

        return in_array($action_hook, $ecommerce_order_hooks);

    }

    /**
     * Gets the JavaScript code to track ajax add to cart event wrapped in <script>.
     *
     * @return string
     */
    public static function get_woocommerce_cart_fragment_event_script($action, $data)
    {

        if ($action == 'woocommerce_add_to_cart_fragments') {
            $settings = self::get_settings();
            $product_id = $data["product_id"];
            ob_start();
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_add_to_cart_fragments');
            $properties = Segment_For_Wp_By_In8_Io::get_product_props_from_product_id($product_id);
            $properties["quantity"] = $data["quantity"];

            if ($settings["include_user_ids"] == "yes") {
                if (is_user_logged_in()) {
                    $wp_user_id = get_current_user_id();
                    $user_id = self::get_user_id($wp_user_id);
                    $properties["user_id"] = $user_id;
                    $user = get_userdata($wp_user_id);
                    $properties["user_email"] = $user->user_email;
                }
            }
            if (count($settings["include_user_traits"]) > 0) {
                if (isset($wp_user_id) && $wp_user_id !== 0 && $wp_user_id !== null) {
                    $include_traits = $settings['include_user_traits'];
                    foreach ($include_traits as $included_trait) {
                        $trait_label = $included_trait["included_user_traits_label"];
                        $trait_key = $included_trait["included_user_traits_key"];
                        if ($trait_key != '' && $trait_label != '') {
                            $trait_value = get_user_meta($wp_user_id, $trait_key, true);
                            $properties[$trait_label] = $trait_value;
                        }

                    }
                }
            }
            $properties = array_filter($properties);
            $properties = apply_filters('segment_for_wp_change_event_properties', $properties, $action, $data);
            ?>
            <script>
                analytics.track('<?php echo sanitize_text_field($event_name); ?>',
                    <?php echo sanitize_text_field(json_encode($properties)); ?>
                    , {}, function () {
                        jQuery(document.body).trigger('wc_fragment_refresh');
                        if (jQuery("#segment-4-wp-wc-add-to-cart").length) {
                            jQuery('#segment-4-wp-wc-add-to-cart').html('');
                            jQuery('#segment-4-wp-wc-add-to-cart').empty();
                        }
                    });
            </script>
            <?php

            return ob_get_clean();

        } else {
            return null;
        }

    }

    public static function inject_wc_event($name, $properties)
    {
        if (Segment_For_Wp_By_In8_Io::woocommerce_active()) {
//            $properties = apply_filters('segment_for_wp_change_event_properties', $properties);
            wc_enqueue_js(Segment_For_Wp_By_In8_Io::get_event_js_script($name, $properties, 'console.log("")'));

        }
    }

    /**
     * Gets the Js code to output into dom, no script tags
     *
     *
     * @param string $name
     * @param $properties
     * @param string $callback_js
     *
     * @return string
     * @since 2.0
     */
    public static function get_event_js_script($name, $properties, $callback_js = '')
    {
        if (!$properties || $properties == 'null') {
            $properties = array();
        }
        ob_start();

        ?>
        analytics.track('<?php echo sanitize_text_field($name); ?>',
        <?php echo trim(stripslashes_deep(json_encode($properties)), '"'); ?>
        , {}, function () {
        <?php echo trim(stripslashes_deep(json_encode($callback_js)), '"'); ?>
        });
        <?php

        return ob_get_clean();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Segment_For_Wp_By_In8_Io_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

}
