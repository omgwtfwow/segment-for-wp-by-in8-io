<?php


class Segment_For_Wp_By_In8_Io_Segment_Php_Lib
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The ID of this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of this plugin.
     */
    protected $version;
    /**
     * The plugin settings
     */
    protected $settings;

    /**
     * The Segment consumer types
     */
    protected $consumer;

    public function __construct($plugin_name, $version, $settings, $consumer)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = $settings;
        $this->consumer = $consumer;

    }

    /**
     * Initialise Segment consumer
     */
    public function init_segment()
    {

        class_alias('Segment', 'Analytics');

        if ($this->consumer == 'socket') {

            $timeout = $this->settings["segment_php_consumer_timeout"];

            if (!is_numeric($timeout)) {
                $timeout = 1;
            }

            Segment::init($this->settings["php_api_key"], array(
                "consumer" => "socket",
                "timeout" => $timeout,
                "debug" => false,
                "error_handler" => function ($code, $msg) {
                    error_log($msg);
                    exit(1);
                }
            ));

        }

        // File consumer
        else {
            Segment::init($this->settings["php_api_key"], array(
                "consumer" => "file",
                "filename" => plugin_dir_path(dirname(__FILE__)) . 'tmp/analytics.log'
            ));
        }

    }


    public function file_consumer()
    {
        $settings = $this->settings;
        $timeout = $this->settings["segment_php_consumer_timeout"];

        if (!is_numeric($timeout)) {
            $timeout = 1;
        }

        $args = array(
            "secret" => $settings["php_api_key"],
            "file" => plugin_dir_path(dirname(__FILE__)) . 'tmp/analytics.log',
            "send_file" => plugin_dir_path(dirname(__FILE__)) . '/includes/segment_php/send.php',
            "timeout" => $timeout
        );

        if (isset($args["secret"]) && isset($args["file"]) && isset($args["timeout"])) {
            include(plugin_dir_path(dirname(__FILE__)) . '/includes/segment_php/send.php');
        }

    }

    public function async_task($args)
    {
        $settings = $this->settings;
        $direct = $args['direct'] ?? false;
        $page = $args['page'] ?? false;
        $action = $args['action_hook'] ?? false;
        $action_server = $action . '_server';
        $wp_user_id = $args['wp_user_id'] ?? null;
        $ajs_anon_id = $args['ajs_anon_id'] ?? null;
        $timestamp = $args['timestamp'];
        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);

        if ($page) {

            $page_data = $args['page_data'];

            if ($user_id) {

                Analytics::page(array(
                    "userId" => $user_id,
                    "name" => $page_data['name'],
                    "properties" => $page_data['properties'],
                    "timestamp" => $timestamp,
                    "context" => $page_data['context'],
                ));

            }

            elseif ($ajs_anon_id) {

                Analytics::page(array(
                    "anonymousId" => $ajs_anon_id,
                    "name" => $page_data['name'],
                    "properties" => $page_data['properties'],
                    "timestamp" => $timestamp,
                    "context" => $page_data['context'],
                ));
            }

        }


        elseif ($direct) {

            $event_name = $args['event_name'] ?? null;
            $properties = $args['properties'] ?? null;
            $properties = array_filter($properties);
            $properties = apply_filters('segment_for_wp_change_event_properties', $properties, $action, []);
            if ($event_name && $event_name != '') {

                if ($user_id) {

                    if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits,
                            "timestamp" => $timestamp

                        ));
                    }

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties,
                        "timestamp" => $timestamp

                    ));


                } elseif ($ajs_anon_id) {
                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties,
                        "timestamp" => $timestamp

                    ));
                }

            }

        }


        else {

            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name($action_server, $args);
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties($action, $args);
            $properties = array_filter($properties);

            if($action === 'ninja_forms_after_submission') {
                $event_name = $args["event_name"];
                $properties = array_filter($args["properties"]);
            }

            if ($event_name) {

                if (!$user_id && Segment_For_Wp_By_In8_Io::is_ecommerce_order_hook($action)) {
                    //if settings to try
                    if (array_key_exists('woocommerce_match_logged_out_users', $settings["track_woocommerce_fieldset"])) {
                        if ($settings["track_woocommerce_fieldset"]["woocommerce_match_logged_out_users"] == "yes") {
                            if (array_key_exists('billing_email', $properties)) {
                                $order_email = $properties["billing_email"];
                                if (filter_var($order_email, FILTER_VALIDATE_EMAIL)) {
                                    if (email_exists($order_email)) {
                                        $user = get_user_by('email', $order_email);
                                        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($user->ID);
                                    }
                                }
                            }

                        }
                    }
                }

                if ($action === 'gform_after_submission') {

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

                    $properties = $gf_event_props;

                }

                if ($user_id) {

                    if ($action === 'user_register') {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits,
                            "timestamp" => $timestamp
                        ));
                    }

                    elseif ($action === 'ninja_forms_after_submission') {
                        if (array_key_exists('identify_ninja_forms', $settings["track_ninja_forms_fieldset"])) {
                            if ($settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits,
                                    "timestamp" => $timestamp
                                ));
                            }
                        }


                    }

                    elseif ($action === 'gform_after_submission') {

                        if (array_key_exists('identify_gravity_forms', $settings["track_gravity_forms_fieldset"])) {
                            if ($settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits,
                                    "timestamp" => $timestamp
                                ));
                            }
                        }

                    }

                    elseif (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits,
                            "timestamp" => $timestamp

                        ));
                    }

                    $properties = apply_filters('segment_for_wp_change_event_properties', $properties, $action, []);

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties,
                        "timestamp" => $timestamp

                    ));

                }

                elseif ($ajs_anon_id) {

                    $properties = apply_filters('segment_for_wp_change_event_properties', $properties, $action, []);

                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties,
                        "timestamp" => $timestamp

                    ));
                }

            }

        }

        Analytics::flush();

    }

    // TODO: refactor all of these events into one function

    /**
     * @param ...$args 'wp user id'
     */
    public function user_register(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = $args["args"][0];
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();

        self::schedule_event('async_task', $args, $this->plugin_name);



    }

    public function schedule_event($task, $args, $plugin_name)
    {

        if (mb_strlen(implode($this->flatten($args))) < 8000) {

            as_enqueue_async_action($task, array($args), $plugin_name);

        } else {
            syslog(LOG_WARNING, $plugin_name . ": Payload is too large to schedule. 8000 characters max.");
        }

    }

    private function flatten(array $array)
    {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    /**
     * @param ...$args 'two args, $user_login (username), $user (object)'
     */
    public function wp_login(...$args)
    {
        //user
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = $args["args"][1]["ID"];
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'one arg, the wp user id'
     *
     * @noinspection PhpUnused
     */
    public function wp_logout(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = $args["args"][0];
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

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

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        if ($args["args"][1]["user_id"] != 0) {
            $wp_user_id = $args["args"][1]["user_id"];
        } else {
            $wp_user_id = Segment_For_Wp_By_In8_Io::get_wp_user_id(current_action(), $args);
        }
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);


    }

    /**
     * @param ...$args '$form_data'
     */
    public function ninja_forms_after_submission(...$args)
    {
        $settings = $this->settings;
        $form_data = func_get_args();
        $args = array();
        $args['action_hook'] = current_action();
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();

        //process fields
        $event_properties = array();

        foreach ($form_data[0]["fields"] as $field) {
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


        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args '$entry, $form '
     */
    public function gform_after_submission(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();

        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * When items are added
     *
     * @param ...$args
     * $args[0]=$cart_item_key,
     * $args[1]=$product_id,$
     * args[2]=$quantity,
     * $args[3]=$variation_id,
     * $args[4]=$variation,
     * $args[5]=$cart_item_data
     */
    public function woocommerce_add_to_cart(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['product_id'] = $args["args"][1];
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);


    }

    /**
     * When items are removed
     *
     * @param ...$args
     */
    public function woocommerce_cart_item_removed(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $item_key = $args["args"][0];
        $product_id = $args["args"][1]["removed_cart_contents"][$item_key]["product_id"];
        $quantity = $args["args"][1]["removed_cart_contents"][$item_key]["quantity"];
        $args['args']['product_id'] = $product_id;
        $args['args']['quantity'] = $quantity;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);


    }

    /**
     * When quantity changes, Product Added or Removed logic
     *
     * @param ...$args
     * $args[0]=$cart_item_key,
     * $args[1]=$quantity,
     * $args[2]=$old_quantity,
     * $args[3]=$cart
     */
    public function woocommerce_after_cart_item_quantity_update(...$args)
    {

        if (!array_key_exists('_wp_http_referer', $_REQUEST)) {
            return;
        }
        $cart_path = parse_url(wc_get_cart_url(), PHP_URL_PATH);
        $request_path = parse_url($_REQUEST["_wp_http_referer"], PHP_URL_PATH);
        if ($cart_path !== $request_path) {
            return;
        }

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();

        $new_quantity = $args["args"][1];
        $old_quantity = $args["args"][2];
        $args["quantity"] = abs($new_quantity - $old_quantity);
        $cart = $args["args"][3]["cart_contents"];
        $args["cart"] = $cart;
        $item_key = $args["args"][0];
        $args["item_key"] = $item_key;
        $product_id = $cart[$item_key]["product_id"];
        $args["product_id"] = $product_id;
        $properties = array();
        $event_name = null;

        //PRODUCT ADDED
        if ($new_quantity > $old_quantity && $this->settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_added_server"] == 'yes') {
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_add_to_cart_server');
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties('woocommerce_after_cart_item_quantity_update', $args);
        }

        //PRODUCT REMOVED
        if ($new_quantity < $old_quantity && $this->settings["track_woocommerce_fieldset"]["woocommerce_events"]["woocommerce_events_settings"]["track_woocommerce_events_product_removed_server"] == 'yes') {
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name('woocommerce_cart_item_removed_server');
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties('woocommerce_after_cart_item_quantity_update', $args);
        }

        $args['event_name'] = $event_name;
        $args['properties'] = $properties;
        $args['timestamp'] = time();
        $args["direct"] = true;

        if (isset($event_name) && $event_name !== '') {

            self::schedule_event('async_task', $args, $this->plugin_name);

        }

    }

    /**
     * When 'undo' after removing item from cart
     *
     * @internal
     */
    public function woocommerce_cart_item_restored(...$args)
    {
        // args $removed_cart_item_key, $cart

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $item_key = $args["args"][0];
        $args['cart_contents'] = $args["args"][1]["cart_contents"];
        $args['product_id'] = $args['cart_contents'][$item_key]["product_id"];
        $args["quantity"] = $args['cart_contents'][$item_key]["quantity"];
        $args['timestamp'] = time();

        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_pending(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_failed(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_processing(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_completed(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_payment_complete(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_on_hold(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);
    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_refunded(...$args)
    {

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args 'order id'
     */
    public function woocommerce_order_status_cancelled(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $order_id = $args["args"][0];
        $args['order_id'] = $order_id;
        $args['timestamp'] = time();
        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    /**
     * @param ...$args '?'
     */
    public function page_server_side(...$args)
    {
        $current_post = get_queried_object();

//        if (!$current_post) {
//            return;
//        }

        if (
            ! is_singular() &&
            ! is_page() &&
            ! is_single() &&
            ! is_archive() &&
            ! is_post_type_archive() &&
            ! is_home() &&
            ! is_front_page() &&
            ! is_author() &&
            ! is_category() &&
            ! is_tag()

        ) {
            return false;
        }

        $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);
        if ($trackable_post === false) {
            //not trackable
            return;
        }

        $page_name = Segment_For_Wp_By_In8_Io::get_page_name($current_post);
        $page_props = Segment_For_Wp_By_In8_Io::get_page_props($current_post);

        $url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $referrer = wp_get_referer();

        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $locale = str_replace('_', '-', get_user_locale());

        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp'] = time();
        $args['page'] = true;
        $args['page_data'] = array();
        $args['page_data']['name'] = $page_name;
        $args['page_data']['properties'] = $page_props;
        $args['page_data']['properties']['referrer'] = $referrer;
        $args['page_data']['properties']['url'] = $url;
        $args['page_data']['properties']['path'] = $path;
        $args['page_data']['properties']['search'] = $query ? "?" . $query : '';

        $args['page_data']['context'] = array();
        $args['page_data']['context']['referrer'] = $referrer;
        $args['page_data']['context']['ip'] = $ip;
        $args['page_data']['context']['userAgent'] = $user_agent;
        $args['page_data']['context']['locale'] = $locale;

        self::schedule_event('async_task', $args, $this->plugin_name);

    }

    public function custom_events(...$args)
    {
        //TODO

    }

}

