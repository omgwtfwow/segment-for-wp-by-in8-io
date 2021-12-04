<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://juangonzalez.com.au
 * @since      1.0.0
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Segment_For_Wp_By_In8_Io
 * @subpackage Segment_For_Wp_By_In8_Io/public
 * @author     Juan <hello@juangonzalez.com.au>
 */
class Segment_For_Wp_By_In8_Io_Public
{

    /**
     * @var
     */
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
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @param $settings
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version, $settings)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = $settings;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/segment-for-wp-by-in8-io-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        $settings = $this->settings;
        $custom_js_events = array();

        $current_user = wp_get_current_user();
        $current_post = get_post();
        $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
        $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);

        if ($trackable_post === false || $trackable_user === false) {
            //not trackable
            return;
        }

        if (array_key_exists('track_custom_event_group', $settings)) {
            if (count($settings["track_custom_event_group"]) > 0) {
                foreach ($settings["track_custom_event_group"] as $event) {
                    array_push($custom_js_events, $event["track_custom_event_name"]);
                }
            }
        }
        wp_enqueue_script($this->plugin_name . '-js.cookie.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-js.cookie.min.js', array(), 'v3.0.0-rc.4', false);
        if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_logout')) {
            $cookie = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('wp_logout');
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-logout.js', array('jquery'), $this->version, true);
            wp_localize_script($this->plugin_name, 'wp_logout', array('cookie_name' => $cookie));
        }


        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-public.js', array('jquery'), $this->version, true);

        if (Segment_For_Wp_By_In8_Io::ninja_forms_active() && array_key_exists("track_ninja_forms_fieldset", $settings)) {
            if ($settings["track_ninja_forms_fieldset"]["track_ninja_forms"] == 'yes' && Segment_For_Wp_By_In8_Io::ninja_forms_active()) {
                wp_enqueue_script($this->plugin_name . '-nf.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-ninja-forms.js', array('jquery'), $this->version, true);
            }
        }
        if (Segment_For_Wp_By_In8_Io::gravity_forms_active() && array_key_exists("track_gravity_forms_fieldset", $settings)) {
            if ($settings["track_gravity_forms_fieldset"]["track_gravity_forms"] == 'yes' && Segment_For_Wp_By_In8_Io::gravity_forms_active()) {
                wp_enqueue_script($this->plugin_name . '-gf.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-gravity-forms.js', array('jquery'), $this->version, true);
            }
        }
        if (Segment_For_Wp_By_In8_Io::woocommerce_active() && array_key_exists("track_woocommerce_fieldset", $settings)) {
            if ($settings["track_woocommerce_fieldset"]["track_woocommerce"] == 'yes' && Segment_For_Wp_By_In8_Io::woocommerce_active()) {
                $is_wc_cart = is_cart();
                wp_enqueue_script($this->plugin_name . '-woocommerce.js', plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-woocommerce.js', array('jquery'), $this->version, true);
            }
        }


        wp_localize_script($this->plugin_name, 'wp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            '_nonce' => wp_create_nonce($settings["nonce_string"]),
            'custom_js_events' => $custom_js_events,
            'nf_settings' => array(
                'identify' => $settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] ?? [],
            ),
            'wc_settings' => array(
                'is_wc_cart' => $is_wc_cart ?? false,
                'add' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added"] ?? '',
                'remove' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed"] ?? '',
                'coupon' => $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_coupon_applied"] ?? ''
            ),
            'other_settings' => array(
                'cookie_domain' => COOKIE_DOMAIN,
                'cookie_path' => COOKIEPATH)
        ));
    }

    /**
     * @param ...$args 'one arg, the wp user id'
     */
    public function user_register(...$args)
    {
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = $args["args"][0];
        $args['wp_user_id'] = $wp_user_id;
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie($action, $args);
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie('identify', $args);
    }

    /**
     * @param ...$args 'two args, $user_login (username), $user (object)'
     */
    public function wp_login(...$args)
    { //user
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $args['wp_user_id'] = $args["args"][1]["ID"];
        $args['args'] = null;
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie($action, $args);
        if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('identify', $args);
        }
    }

    /**
     * @param mixed ...$args
     *
     * @noinspection PhpUnused
     */
    public function wp_logout(...$args)
    {
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = $args["args"][0];
        $args['wp_user_id'] = $wp_user_id;
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie('wp_logout', $args);
        if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('identify', $args);
        }
    }

    /**
     * @param ...$args 'int $id, WP_Comment $comment'
     */
    public function wp_insert_comment(...$args)
    {
        if (isset($args[1]->comment_author) && $args[1]->comment_author == 'WooCommerce') {
            //because Woo inserts a comment with order details
            return;
        }
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        if ($args["args"][1]["user_id"] != 0) {
            $wp_user_id = $args["args"][1]["user_id"];
        } else {
            $wp_user_id = Segment_For_Wp_By_In8_Io::get_wp_user_id($action, $args);
        }
        $args['wp_user_id'] = $wp_user_id;
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie('wp_insert_comment', $args);
        if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('identify', $args);
        }
    }

    /** TODO
     * @param ...$args '$form_data'
     */
    public function ninja_forms_after_submission(...$args)
    {
        $settings = $this->settings;
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $event_properties = array();

        //process fields
        foreach ($args["args"][0]["fields"] as $field) {
            if ($field["value"] != "") {

                // EVENT NAME
                if ($field["admin_label"] == $settings["track_ninja_forms_fieldset"]["ninja_forms_event_name_field"]) {
                    $args['event_name'] = sanitize_text_field($field["value"]);
                }

                // WP USER ID
                if ($field["admin_label"] == $settings["track_ninja_forms_fieldset"]["ninja_forms_wp_user_id_field"]) {
                    $wp_user_id = sanitize_text_field($field["value"]);
                    $args['wp_user_id'] = $wp_user_id;
                    if ($settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes' && $settings["track_ninja_forms_fieldset"]["ninja_forms_wp_user_id_field"] != '') {
                        $args['nf_wp_user_id'] = $wp_user_id;
                    }
                }

                // EVENT PROPS
                if (array_key_exists('ninja_form_event_properties', $settings["track_ninja_forms_fieldset"])) {
                    if (count($settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"]) > 0) {
                        $ninja_form_event_properties = $settings["track_ninja_forms_fieldset"]["ninja_form_event_properties"];

                        foreach ($ninja_form_event_properties as $event_property) {

                            if ($field["admin_label"] == $event_property["ninja_form_event_property_field_id"]) {

                                $event_properties[$event_property["ninja_form_event_property_label"]] = $field["value"];


                            }
                        }

                    }
                }

                $args['properties'] = $event_properties;
            }

        }
        unset($args["args"]);
        if (!isset($args["wp_user_id"])) {
            if (is_user_logged_in()) {
                $args["wp_user_id"] = get_current_user_id();
            }
        }
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie($action, $args);

        if ($settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes') {
            if ($args['wp_user_id'] &&
                $args['wp_user_id'] !== "" &&
                $args['wp_user_id'] != 0) {
                $nf_wp_user = get_user_by('ID', $args["wp_user_id"]);
                if ($nf_wp_user) {
                    $args["wp_user_id"] = $nf_wp_user->ID;
                }
            } elseif (is_user_logged_in()) {
                $args["wp_user_id"] = get_current_user_id();
            }
            if (isset($args["wp_user_id"]) && $args['wp_user_id'] !== "" && $args['wp_user_id'] != 0) {
                Segment_For_Wp_By_In8_Io_Cookie::set_cookie('ninja_forms_identify', $args["wp_user_id"]);
            }

        }

    }

    /**
     * @param ...$args '$entry, $form '
     */
    public function gform_after_submission(...$args)
    {
        $settings = $this->settings;
        $action = current_action();
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $entry = $args["args"][0];
        $form = $args["args"][1];
        $gf_event_name_field = sanitize_text_field($settings["track_gravity_forms_fieldset"]["gravity_forms_event_name_field"]);
        $gf_wp_user_id_field = sanitize_text_field($settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"]);
        $gf_event_props = array();

        foreach ($form['fields'] as $field) {
            if ($gf_event_name_field != '') {
                if ($field["adminLabel"] == $gf_event_name_field) {
                    $gf_event_name = rgar($entry, $field["id"]);
                    if ($gf_event_name != '') {
                        $args['event_name'] = sanitize_text_field($gf_event_name);
                    }
                }
                if ($settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"] != '') {
                    if ($field["adminLabel"] == $gf_wp_user_id_field) {
                        $gf_wp_user_id = rgar($entry, $field["id"]);
                        $args['gf_wp_user_id'] = sanitize_text_field($gf_wp_user_id);
                    }
                }
                if (array_key_exists('gravity_form_event_properties', $settings["track_gravity_forms_fieldset"]) && count($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"]) > 0) {
                    foreach ($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"] as $property) {
                        if ($property["gravity_form_event_property_field_id"] != '') {
                            $gf_field_label_key = $property["gravity_form_event_property_field_id"];
                            $gf_label_text = $property["gravity_form_event_property_label"];
                            if ($field["adminLabel"] == $gf_field_label_key) {

                                $gf_field_id = $field["id"];

                                if ($field["type"] == "checkbox") {

                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ', ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "name") {
                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ' ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "address") {
                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ' ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "list") {

                                    $string = '';
                                    $list = maybe_unserialize($entry[$gf_field_id]);

                                    foreach ($list as $item) {

                                        if (strlen($string) == 0) {
                                            $string = sanitize_text_field($item);
                                        } else {
                                            $string = $string . ', ' . sanitize_text_field($item);
                                        }

                                    }

                                    $value = $string;


                                }

                                else {
                                    $value = $entry[$gf_field_id];

                                }

                                if ($field["type"] == "multiselect") {

                                    $selections = json_decode($value);
                                    $string = '';
                                    foreach ($selections as $selection) {
                                        if (strlen($string) == 0) {
                                            $string = $selection;
                                        } else {
                                            $string = $string . ', ' . $selection;
                                        }
                                    }
                                    $value = $string;


                                }

                                if ($value && $value != '') {

                                    if ($field["type"] == "number") {

                                        $value = ($value == (int)$value) ? (int)$value : (float)$value;

                                        $gf_event_props[sanitize_text_field($gf_label_text)] = $value;

                                    } else {
                                        $gf_event_props[sanitize_text_field($gf_label_text)] = sanitize_text_field($value);
                                    }

                                }


                            }
                        }


                    }


                }

            }

        }

        unset($args["args"]);
        $args['properties'] = $gf_event_props;

        if (!isset($args["wp_user_id"])) {
            if (is_user_logged_in()) {
                $args["wp_user_id"] = sanitize_text_field(get_current_user_id());
            }
        }
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie($action, $args);

        if ($settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes') {
            if (array_key_exists('wp_user_id', $args) && $args['wp_user_id'] != 0) {
                $wp_user = get_user_by('ID', $args["wp_user_id"]);
                if ($wp_user) {
                    $args["wp_user_id"] = $wp_user->ID;
                }
            } elseif (is_user_logged_in()) {
                $args["wp_user_id"] = get_current_user_id();
            }
            if (isset($args["wp_user_id"]) && $args['wp_user_id'] !== "" && $args['wp_user_id'] !== 0) {
                Segment_For_Wp_By_In8_Io_Cookie::set_cookie('gravity_forms_identify', $args["wp_user_id"]);
            }

        }
    }

    /**
     * @throws Exception
     */
    public function gform_confirmation($confirmation, $form, $entry, $ajax)
    {
        global $wp_scripts;
        if (isset($wp_scripts->registered['jquery']->ver)) {
            $ver = $wp_scripts->registered['jquery']->ver;
            $jquery_ver = str_replace("-wp", "", $ver);
        } else {
            $jquery_ver = '1.12.4';
        }

        $settings = $this->settings;
        $js_file = plugin_dir_url(__FILE__) . 'js/segment-for-wp-by-in8-io-gravity-forms.js';
        $ajax_url = esc_url_raw(admin_url('admin-ajax.php'));
        $ajax_nonce = wp_create_nonce($settings["nonce_string"]);
        $gf_event_name_field = sanitize_text_field($settings["track_gravity_forms_fieldset"]["gravity_forms_event_name_field"]);
        $gf_wp_user_id_field = sanitize_text_field($settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"]);
        $gf_identify = $settings["track_gravity_forms_fieldset"]["identify_gravity_forms"];

        if ($gf_identify === 'yes') {
            $gf_identify = true;

        } else {
            $gf_identify = false;
        }
        $gf_event_props = array();
        foreach ($form['fields'] as $field) {
            if ($gf_event_name_field != '') {
                if ($field["adminLabel"] == $gf_event_name_field) {
                    $gf_event_name = rgar($entry, $field["id"]);
                    if ($gf_event_name != '') {
                        $args['event_name'] = sanitize_text_field($gf_event_name);
                    }
                }
                if ($settings["track_gravity_forms_fieldset"]["gravity_forms_wp_user_id_field"] != '') {
                    if ($field["adminLabel"] == $gf_wp_user_id_field) {
                        $gf_wp_user_id = rgar($entry, $field["id"]);
                        $args['gf_wp_user_id'] = sanitize_text_field($gf_wp_user_id);
                    }
                }

                if (array_key_exists('gravity_form_event_properties', $settings["track_gravity_forms_fieldset"]) && count($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"]) > 0) {
                    foreach ($settings["track_gravity_forms_fieldset"]["gravity_form_event_properties"] as $property) {
                        if ($property["gravity_form_event_property_field_id"] != '') {
                            $gf_field_label_key = $property["gravity_form_event_property_field_id"];
                            $gf_label_text = $property["gravity_form_event_property_label"];
                            if ($field["adminLabel"] == $gf_field_label_key) {


                                $gf_field_id = $field["id"];

                                if ($field["type"] == "checkbox") {

                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ', ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "name") {
                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ' ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "address") {
                                    $string = '';

                                    foreach ($field["inputs"] as $input) {

                                        if (strlen($entry[$input["id"]]) > 0) {
                                            if (strlen($string) == 0) {
                                                $string = $entry[$input["id"]];
                                            } else {
                                                $string = $string . ' ' . $entry[$input["id"]];
                                            }
                                        }

                                    }

                                    $value = $string;


                                }

                                elseif ($field["type"] == "list") {

                                    $string = '';
                                    $list = maybe_unserialize($entry[$gf_field_id]);

                                    foreach ($list as $item) {

                                        if (strlen($string) == 0) {
                                            $string = sanitize_text_field($item);
                                        } else {
                                            $string = $string . ', ' . sanitize_text_field($item);
                                        }

                                    }

                                    $value = $string;


                                }

                                else {
                                    $value = $entry[$gf_field_id];

                                }

                                if ($field["type"] == "multiselect") {

                                    $selections = json_decode($value);
                                    $string = '';
                                    foreach ($selections as $selection) {
                                        if (strlen($string) == 0) {
                                            $string = $selection;
                                        } else {
                                            $string = $string . ', ' . $selection;
                                        }
                                    }
                                    $value = $string;


                                }

                                if ($value && $value != '') {

                                    if ($field["type"] == "number") {

                                        $value = ($value == (int)$value) ? (int)$value : (float)$value;

                                        $gf_event_props[sanitize_text_field($gf_label_text)] = $value;

                                    } else {
                                        $gf_event_props[sanitize_text_field($gf_label_text)] = sanitize_text_field($value);
                                    }

                                }

                            }
                        }

                    }

                }
            } else {
                $gf_event_name = 'Form Submitted';
            }

        }
        if (!isset($gf_wp_user_id)) {
            if (is_user_logged_in()) {
                $gf_wp_user_id = sanitize_text_field(get_current_user_id());
            }
        }

        if ($gf_identify && isset($gf_wp_user_id) && is_user_logged_in()) {
            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($gf_wp_user_id);
            $user_traits = Segment_For_Wp_By_In8_Io::get_user_traits($gf_wp_user_id);
            $identify = true;
        } else {
            $user_id = null;
            $identify = false;
        }

        $form = json_encode($form);
        $entry = json_encode($entry);
        $gf_event_props = json_encode($gf_event_props);
        $user_traits = isset($user_traits) ? json_encode($user_traits) : json_encode(array());

        if (isset($gf_event_name) && $gf_event_name != '') {
            if (isset($confirmation['redirect'])) {

                $url = esc_url_raw($confirmation['redirect']);
                GFCommon::log_debug(__METHOD__ . '(): Redirect to URL: ' . $url);

                return "<script type='text/javascript' src='$js_file'></script>" .
                    "<script>s4wp_run_gf_tracking(
                            '$form',
                            '$entry',
                            '$url',
                            '$ajax_url',
						    '$ajax_nonce',
						    '$gf_event_name',
						    '$gf_event_props',
						    '$identify',
						    '$user_id',
						    '$user_traits',
						    '$jquery_ver'
					)</script>";

            } else {

                $string = "<script type='text/javascript' src='$js_file'></script>" .
                    "<script>s4wp_run_gf_tracking(
                            '$form',
                            '$entry',
                             null,
                            '$ajax_url',
                            '$ajax_nonce',
                            '$gf_event_name',
                            '$gf_event_props',
                            '$identify',
                            '$user_id',
                            '$user_traits',
                            '$jquery_ver'
					)</script>";
                return $confirmation . $string;

            }

        }

    }

    /**
     * Add to cart
     *
     * @param mixed ...$args
     * $args[0]=$cart_item_key,
     * $args[1]=$product_id,$
     * args[2]=$quantity,
     * $args[3]=$variation_id,
     * $args[4]=$variation,
     * $args[5]=$cart_item_data
     */
    public function woocommerce_add_to_cart(...$args)
    {
        // don't track add to cart from AJAX here
        if (is_ajax()) {
            return;
        }
        $action = current_action();
        $args = func_get_args();
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode($args), true)
        );
        $name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_add_to_cart');
        $properties = array();
        foreach ($args["args"] as $index => $value) {
            if ($index == 0) {
                $args['cart_item_key'] = $value;
            } elseif ($index == 1) {
                $args['product_id'] = $value;
            } elseif ($index == 2) {
                $args['quantity'] = $value;
            } elseif ($index == 3) {
                $args['variation_id'] = $value;
            } elseif ($index == 4) {
                $args['variation'] = $value;
            } elseif ($index == 5) {
                $args['cart_item_data'] = $value;
            }

        }
        unset($args["args"]);
        if ($args['product_id']) {
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties('woocommerce_add_to_cart', $args);
        }
        if (is_user_logged_in()) {
            $args['wp_user_id'] = get_current_user_id();
        }
        array_filter(array_merge($properties, $args));
        Segment_For_Wp_By_In8_Io::inject_wc_event($name, json_encode($properties));
    }

    /**
     * ajax add to cart via fragments
     *
     * @param $fragments
     *
     * @return mixed
     */
    public function woocommerce_add_to_cart_fragments($fragments)
    {
        if (array_key_exists('product_id', $_POST) && array_key_exists('wc-ajax', $_REQUEST) && $_REQUEST["wc-ajax"] == 'add_to_cart') {
            $script = Segment_For_Wp_By_In8_Io::get_woocommerce_cart_fragment_event_script('woocommerce_add_to_cart_fragments', $_POST);
            $fragments['div.segment-4-wp-wc-add-to-cart-event-placeholder'] = '<div id="segment-4-wp-wc-add-to-cart" class="segment-4-wp-wc-add-to-cart-event-placeholder">' . $script . '</div>';
        }

        return $fragments;
    }

    /**
     * When redirecting to the cart page after adding
     *
     * @internal
     */
    public function woocommerce_add_to_cart_redirect(...$args)
    {
        $redirect = $args[0];
//		$last_product_id = WC()->session->get( 'segment_4_wp_last_product_added_to_cart', 0 );
//
//		if ( $last_product_id > 0 ) {
//			WC()->session->set( 'segment_4_wp_last_product_added_to_cart', 0 );
//		}

        return $redirect;
    }

    /**
     * When add to cart via ajax
     *
     * @internal
     */
    public function woocommerce_ajax_added_to_cart($product_id = null)
    {
        if (!$product_id) {
            return;
        }
        $product = wc_get_product($product_id);
        if ($product instanceof WC_Product) {
            WC()->session->set('segment_4_wp_last_product_added_to_cart', $product->get_id());
        }

    }

    public function woocommerce_after_cart()
    {
        if (!is_cart()) {
            return;
        }
        $last_product_id = WC()->session->get('segment_4_wp_last_product_added_to_cart', 0);
        if ($last_product_id == 0) {
            return;
        }
        $properties = array();
        if ($last_product_id > 0) {
            WC()->session->set('segment_4_wp_last_product_added_to_cart', 0);
        }
        if (is_user_logged_in()) {
            $properties['wp_user_id'] = get_current_user_id();
        }
        $properties["product_id"] = $last_product_id;
        $name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_after_cart');
        $properties = Segment_For_Wp_By_In8_Io::get_event_properties('woocommerce_after_cart', $properties);
        array_filter($properties);
        Segment_For_Wp_By_In8_Io::inject_wc_event($name, json_encode($properties));
    }

    /**
     * When 'undo' after removing item from cart
     */
    public function woocommerce_cart_item_restored(...$args)
    {
        // args $removed_cart_item_key, $cart
        $action_hook = current_action();
        $args = func_get_args();
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode($args), true)
        );
        $name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_cart_item_restored');
        $args['cart_contents'] = $args["args"][1]["cart_contents"];
        unset($args["args"]);
        if (is_user_logged_in()) {
            $args['wp_user_id'] = get_current_user_id();
        }
        Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_cart_item_restored', $args);
    }

    /**
     * @param ...$args
     */
    public function woocommerce_remove_cart_item(...$args)
    {
        // args $removed_cart_item_key, $cart
        $action = current_action();
//		$action                = 'woocommerce_remove_cart_item';
        $args = array(
            'action_hook' => $action,
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_remove_cart_item');
        $args['cart_contents'] = $args["args"][1]["cart_contents"];
        unset($args["args"]);
        if (is_user_logged_in()) {
            $args['wp_user_id'] = get_current_user_id();
        }
        Segment_For_Wp_By_In8_Io::inject_wc_event($name, json_encode($args));
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_pending(...$args)
    {

        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_pending', $args);
        }
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_failed(...$args)
    {

        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_failed', $args);
        }
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_processing(...$args)
    {
        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }

            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_processing', $args);
        }
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_completed(...$args)
    {

        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_completed', $args);
        }
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_payment_complete(...$args)
    {

        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_payment_complete', $args);
        }
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_on_hold(...$args)
    {
        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_on_hold', $args);
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_refunded(...$args)
    {
        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_refunded', $args);
        }

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_cancelled(...$args)
    {
        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_order_status_cancelled', $args);
        }

    }

    /**
     * @param ...$args
     */
    public function woocommerce_applied_coupon(...$args)
    {
        if (!current_user_can('manage_woocommerce')) {
            $action = current_action();
            $args = array(
                'action_hook' => $action,
                'args' => json_decode(json_encode(func_get_args()), true)
            );
            $args['order_id'] = $args["args"][0];
            unset($args["args"]);
            if (is_user_logged_in()) {
                $args['wp_user_id'] = get_current_user_id();
            }
            Segment_For_Wp_By_In8_Io_Cookie::set_cookie('woocommerce_applied_coupon', $args);
        }

    }

    /**
     * Ajax calls to get user id and traits
     */
    public function ajax_identify()
    {
        $settings = $this->settings;
        $nonce_string = $settings["nonce_string"];
        if (!wp_verify_nonce($_POST['nonce'], $nonce_string)) {
            die();
        }

        if (!check_ajax_referer($nonce_string, 'nonce', false)) {
            die();

        }

        if (!isset($_POST['cookie_name']) || $_POST['cookie_name'] == '' || $_POST['cookie_name'] == null) {
            die();

        }

        if (!isset($_POST['wp_user_id']) || $_POST['wp_user_id'] == '' || $_POST['wp_user_id'] == null) {
            die();
        }

        // Eg.: get POST value
        $wp_user_id = sanitize_text_field($_POST['wp_user_id']);
        $wp_user = get_user_by('ID', $wp_user_id);
        if ($wp_user) {
            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
            $user_traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
            $user_identify = array();
            $user_identify['event'] = 'identify';
            $user_identify['user_id'] = $user_id;
            $user_identify['traits'] = $user_traits;
            $user_identify['cookie_name'] = $_POST['cookie_name'];
            $user_identify['cookie_data'] = $_POST['cookie_data'];
            wp_send_json_success($user_identify);
            exit();
        }
        die();

    }

    /**
     * Ajax calls to process track events
     * ie fire associated itentify calls
     */
    public function ajax_track()
    {
        $settings = $this->settings;
        $nonce_string = $settings["nonce_string"];
        if (!wp_verify_nonce($_POST['nonce'], $nonce_string)) {
            die();
        }
        if (!check_ajax_referer($nonce_string, 'nonce', false)) {
            die();
        }
        if (isset($_POST['event'])) {
            $event = sanitize_text_field($_POST['event']);
            $properties = $_POST['properties'];
            if (array_key_exists('track_custom_event_group', $settings)) {
                if (count($settings["track_custom_event_group"]) > 0) {
                    foreach ($settings["track_custom_event_group"] as $custom_event) {
                        if ($event == $custom_event['track_custom_event_name'] && $custom_event["track_custom_event_server_side"] == "yes") {
                            if (isset($_COOKIE["ajs_user_id"])) {
                                Analytics::track(array(
                                    "userId" => $_COOKIE["ajs_user_id"],
                                    "event" => $event,
                                    "properties" => $properties
                                ));
                                if (is_user_logged_in()) {
                                    $event = sanitize_text_field($_POST['event']);
                                    if (array_key_exists("identify_associated_events", $settings) && count($settings["identify_associated_events"]) > 0) {
                                        if (in_array($event, $settings["identify_associated_events"])) {
                                            $user_info = array();
                                            $wp_user_id = get_current_user_id();
                                            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
                                            $user_traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                            $user_info["event"] = 'identify';
                                            $user_info["user_id"] = $user_id;
                                            $user_info["user_traits"] = $user_traits;
                                            Analytics::identify(array(
                                                "userId" => $user_id,
                                                "traits" => $user_info["user_traits"]
                                            ));
                                        }
                                    }
                                }
                            } elseif (isset($_COOKIE["ajs_anonymous_id"])) {
                                Analytics::track(array(
                                    "anonymousId" => $_COOKIE["ajs_anonymous_id"],
                                    "event" => $event,
                                    "properties" => $properties
                                ));
                            }
                            Analytics::flush();
                        }
                    }
                }
            }
            if (is_user_logged_in()) {
                $event = sanitize_text_field($_POST['event']);
                if (array_key_exists("identify_associated_events", $settings) && count($settings["identify_associated_events"]) > 0) {
                    if (in_array($event, $settings["identify_associated_events"])) {
                        $user_info = array();
                        $wp_user_id = get_current_user_id();
                        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
                        $user_traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        $user_info["event"] = 'identify';
                        $user_info["user_id"] = $user_id;
                        $user_info["user_traits"] = $user_traits;
                        wp_send_json_success($user_info, 200);
                        exit();
                    }

                }
            }
        }
        Analytics::flush();
        die();
    }

    /**
     * Ajax calls to process track events
     * ie fire associated itentify calls
     */
    public function ajax_cookie()
    {
        $settings = $this->settings;
        $nonce_string = $settings["nonce_string"];
        if (!wp_verify_nonce($_POST['nonce'], $nonce_string)) {
            die();
        }
        if (!check_ajax_referer($nonce_string, 'nonce', false)) {
            die();
        }
        if (isset($_POST['cookie_name']) && isset($_POST['do'])) {
            if ($_POST['do'] == 'remove') {
                if ($_POST['cookie_name_type'] == 'long') {
                    Segment_For_Wp_By_In8_Io_Cookie::delete_cookie($_POST["cookie_name"]);
                    wp_send_json_success($_POST["cookie_name"], 200);
                }
                if ($_POST['cookie_name_type'] == 'short') {
                    Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies($_POST["cookie_name"]);
                    wp_send_json_success($_POST["cookie_name"], 200);
                }
            }
            if ($_POST['do'] == 'check' && isset($_POST['cookie_name_type'])) {
                $cookie_name = $_POST["cookie_name"];
                $cookie_name_type = $_POST["cookie_name_type"];
                $check = Segment_For_Wp_By_In8_Io_Cookie::check_cookie($cookie_name, $cookie_name_type);
                wp_send_json_success($check, 200);
            }
        }

        die();
    }

    /**
     * Logic for cart page ajax events where quantity changes
     */
    public function wc_cart_events()
    {
        $settings = $this->settings;
        $nonce_string = $settings["nonce_string"];
        if (!wp_verify_nonce($_POST['nonce'], $nonce_string)) {
            die();
        }
        if (!check_ajax_referer($nonce_string, 'nonce', false)) {
            die();
        }
        if (Segment_For_Wp_By_In8_Io::woocommerce_active() && $settings["track_woocommerce_fieldset"]["track_woocommerce"] == 'yes') {
            if ($settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added"] == "yes" ||
                $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed"] == "yes" ||
                $settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_event_coupon_applied"] == "yes") {
                if (sanitize_text_field($_POST['event']) === "setup") {
                    WC()->session->set('segment_4_wp_cart_initial_state', WC()->cart->get_cart());
                    WC()->session->set('segment_4_wp_cart_new_state', WC()->cart->get_cart());
                    wp_send_json_success(array('setup' => true), 200);
                    die();
                }
                if (sanitize_text_field($_POST['event']) === "update") {
                    $changed = false;
                    $refresh = false;
                    WC()->session->set('segment_4_wp_cart_new_state', WC()->cart->get_cart());
                    $initial_items = WC()->session->get('segment_4_wp_cart_initial_state', 0);
                    $new_items = WC()->session->get('segment_4_wp_cart_new_state', 0);
                    if ($initial_items === $new_items) {
                        wp_send_json_success(array(
                            'update' => true,
                            'changed' => $changed
                        ), 200);
                    } else {
                        $changed = true;
                        $events = array();
                        $i = 0;

                        //Changes
                        foreach ($initial_items as $initial_item) {

                            $initial_item_key = $initial_item["key"];
                            $new_item = $new_items[$initial_item_key];

                            if (!array_key_exists($initial_item_key, $new_items)) {
                                // product has been removed
                                $event_name = Segment_For_Wp_By_In8_Io::get_event_name('segment_4_wp_wc_cart_ajax_item_removed');
                                $events[$i]["event_name"] = $event_name;
                                $events[$i]["properties"] = Segment_For_Wp_By_In8_Io::get_event_properties('segment_4_wp_wc_cart_ajax_item_removed', $initial_item);
                                $events[$i]["properties"]["quantity"] = $initial_item['quantity'];
                                $events[$i]["properties"] = apply_filters('segment_for_wp_change_event_properties', array_filter($events[$i]["properties"]), 'segment_4_wp_wc_cart_ajax_item_removed', []);
                                $i++;
                            } else {
                                $new_item = $new_items[$initial_item_key];
                                //Reductions - if initial quantity lower than new quantity
                                if ($new_item['quantity'] < $initial_item['quantity']) {
                                    $refresh = true;
                                    $event_name = Segment_For_Wp_By_In8_Io::get_event_name('segment_4_wp_wc_cart_ajax_item_removed');
                                    $events[$i]["event_name"] = $event_name;
                                    $events[$i]["properties"] = Segment_For_Wp_By_In8_Io::get_event_properties('segment_4_wp_wc_cart_ajax_item_removed', $initial_item);
                                    $events[$i]["properties"]["quantity"] = $initial_item['quantity'] - $new_item["quantity"];
                                    $events[$i]["properties"] = apply_filters('segment_for_wp_change_event_properties', array_filter($events[$i]["properties"]), 'segment_4_wp_wc_cart_ajax_item_removed', []);
                                    $i++;
                                }
                                //Additions - if new quantity is higher than previously
                                if ($new_item['quantity'] > $initial_item['quantity']) {
                                    $event_name = Segment_For_Wp_By_In8_Io::get_event_name('segment_4_wp_wc_cart_ajax_item_added');
                                    $events[$i]["event_name"] = $event_name;
                                    $events[$i]["properties"] = Segment_For_Wp_By_In8_Io::get_event_properties('segment_4_wp_wc_cart_ajax_item_added', $initial_item);
                                    $events[$i]["properties"]["quantity"] = $new_item["quantity"] - $initial_item['quantity'];
                                    $events[$i]["properties"] = apply_filters('segment_for_wp_change_event_properties', array_filter($events[$i]["properties"]), 'segment_4_wp_wc_cart_ajax_item_added', []);
                                    $i++;
                                }


                            }

                        }

                        //Additions - if new key appears (when 'undo' is clicked after add to cart)
                        if (count($initial_items) < count($new_items)) {
                            foreach ($new_items as $new_item) {
                                $new_item_key = $new_item["key"];
                                $new_item = $new_items[$new_item_key];
                                $event_name = Segment_For_Wp_By_In8_Io::get_event_name('segment_4_wp_wc_cart_ajax_item_added');
                                $events[$i]["event_name"] = $event_name;
                                if (!array_key_exists($new_item_key, $initial_items)) {
                                    $events[$i]["properties"] = Segment_For_Wp_By_In8_Io::get_event_properties('segment_4_wp_wc_cart_ajax_item_added', $new_item);
                                    $events[$i]["properties"] = apply_filters('segment_for_wp_change_event_properties', array_filter($events[$i]["properties"]), 'segment_4_wp_wc_cart_ajax_item_added', $new_item);

                                }
                            }
                        }

                        WC()->session->set('segment_4_wp_cart_initial_state', $new_items);
                        wp_send_json_success(array(
                            'update' => true,
                            'changed' => $changed,
                            'refresh' => $refresh,
                            'tracks' => wp_json_encode($events)
                        ), 200);
                        die();

                    }
                    die();
                }
                if (sanitize_text_field($_POST['event']) === "coupon" && sanitize_text_field($_POST['coupon_code']) !== "") {
                    $coupon_code = $_POST['coupon_code'];
                    $event = array();
                    $event_name = Segment_For_Wp_By_In8_Io::get_event_name('segment_4_wp_wc_cart_ajax_coupon_applied');
                    $event["event_name"] = $event_name;
                    $event["properties"] = Segment_For_Wp_By_In8_Io::get_event_properties('segment_4_wp_wc_cart_ajax_coupon_applied', $coupon_code);
                    $event["properties"] = apply_filters('segment_for_wp_change_event_properties', array_filter($event["properties"]), 'segment_4_wp_wc_cart_ajax_coupon_applied', $coupon_code);
                    wp_send_json_success(array(
                        'update' => true,
                        'changed' => true,
                        'tracks' => wp_json_encode($event)
                    ), 200);
                    die();
                }
            }
        }

        die();
    }

}
