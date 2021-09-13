<?php

class Segment_For_Wp_By_In8_Io_Async_Request extends WP_Async_Request
{

    /**
     * @var string
     */
    protected $action = 'async_request';


    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    protected function handle(){

        sleep(90);

        $settings = Segment_For_Wp_By_In8_Io::get_settings();
        $direct = $_POST['direct'] ?? false;
        $action = $_POST['action_hook'];
        $action_server = $action . '_server';
        $wp_user_id = $_POST['wp_user_id'] ?? null;
        $ajs_anon_id = $_POST['ajs_anon_id'] ?? null;
        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);

        if ($direct) {

            $event_name = $_POST['event_name']??null;
            $properties = $_POST['properties']??null;
            if ($event_name && $event_name != '') {

                if ($user_id) {

                    if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    }

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));


                }

                elseif ($ajs_anon_id) {
                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));
                }

            }

        }
        else {
            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name($action_server, $_POST);
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties($action, $_POST);

            if ($event_name) {

                if ($user_id) {

                    if ($action === 'user_register') {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    } elseif ($action === 'ninja_forms_after_submission') {
                        if (array_key_exists('identify_ninja_forms', $settings["track_ninja_forms_fieldset"])) {
                            if ($settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits
                                ));
                            }
                        }


                    } elseif ($action === 'gform_after_submission') {
                        if (array_key_exists('identify_gravity_forms', $settings["track_gravity_forms_fieldset"])) {
                            if ($settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits
                                ));
                            }
                        }


                    } elseif (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    }

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));

                }

                elseif ($ajs_anon_id) {
                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));
                }


            }
        }

        Analytics::flush();
    }

}

class Segment_For_Wp_By_In8_Io_Background_Task extends WP_Background_Process
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
     * @var
     */
    protected $settings;

    /**
     * @var string
     */
    protected $action = 'background_task';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task($item){

        $settings = $this->settings;
        $direct = $item['direct'] ?? false;
        $action = $item['action_hook'];
        $action_server = $action . '_server';
        $wp_user_id = $item['wp_user_id'] ?? null;
        $ajs_anon_id = $item['ajs_anon_id'] ?? null;
        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);

        if ($direct) {

            $event_name = $item['event_name']??null;
            $properties = $item['properties']??null;
            if ($event_name && $event_name != '') {

                if ($user_id) {

                    if (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    }

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));


                }

                elseif ($ajs_anon_id) {
                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));
                }

            }

        }
        else {
            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
            $event_name = Segment_For_Wp_By_In8_Io::get_event_name($action_server, $item);
            $properties = Segment_For_Wp_By_In8_Io::get_event_properties($action, $item);

            if ($event_name) {

                if ($user_id) {

                    if ($action === 'user_register') {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    } elseif ($action === 'ninja_forms_after_submission') {
                        if (array_key_exists('identify_ninja_forms', $settings["track_ninja_forms_fieldset"])) {
                            if ($settings["track_ninja_forms_fieldset"]["identify_ninja_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits
                                ));
                            }
                        }


                    } elseif ($action === 'gform_after_submission') {
                        if (array_key_exists('identify_gravity_forms', $settings["track_gravity_forms_fieldset"])) {
                            if ($settings["track_gravity_forms_fieldset"]["identify_gravity_forms"] == 'yes') {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                Analytics::identify(array(
                                    "userId" => $user_id,
                                    "traits" => $traits
                                ));
                            }
                        }


                    } elseif (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits
                        ));
                    }

                    Analytics::track(array(
                        "userId" => $user_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));

                }

                elseif ($ajs_anon_id) {
                    Analytics::track(array(
                        "anonymousId" => $ajs_anon_id,
                        "event" => $event_name,
                        "properties" => $properties
                    ));
                }


            }
        }
        Analytics::flush();
        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        parent::complete();

        // Show notice to user or perform some other arbitrary task...
    }

}

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
     * @var
     */
    protected $settings;
    /**
     * @var Segment_For_Wp_By_In8_Io_Async_Request
     */
    private $async_request;
    /**
     * @var Segment_For_Wp_By_In8_Io_Background_Task
     */
    private $background_task;

    public function __construct($plugin_name, $version, $settings)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = $settings;
        $this->async_request = new Segment_For_Wp_By_In8_Io_Async_Request($plugin_name, $version, $settings);
        $this->background_task = new Segment_For_Wp_By_In8_Io_Background_Task($plugin_name, $version, $settings);

    }

    /**
     * Initialise Segment consumer
     */
    public function init_segment()
    {

        class_alias('Segment', 'Analytics');

        if ($this->settings["segment_php_consumer"] == 'socket') {

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
                }
            ));

        } else {
            Segment::init($this->settings["php_api_key"], array(
                "consumer" => "file",
                "filename" => plugin_dir_path(dirname(__FILE__)) . 'tmp/analytics.log'
            ));
        }

    }

    public function file_consumer()
    {
        $settings = $this->settings;
        $args = array(
            "secret" => $settings["php_api_key"],
            "file" => plugin_dir_path(dirname(__FILE__)) . 'tmp/analytics.log',
            "send_file" => plugin_dir_path(dirname(__FILE__)) . '/includes/segment_php/send.php',
        );
        if (isset($args["secret"]) && isset($args["file"])) {
            include(plugin_dir_path(dirname(__FILE__)) . '/includes/segment_php/send.php');
        }
    }

    public function async_task($args){

        $settings = $this->settings;
        $direct = $args['direct'] ?? false;
        $action = $args['action_hook'] ?? false;
        $action_server = $action . '_server';
        $wp_user_id = $args['wp_user_id'] ?? null;
        $ajs_anon_id = $args['ajs_anon_id'] ?? null;
        $timestamp =  $args['timestamp'];
        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
        if ($direct) {

            $event_name = $args['event_name']??null;
            $properties = $args['properties']??null;
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


                }

                elseif ($ajs_anon_id) {
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


            if ($event_name) {

                if ($user_id) {

                    if ($action === 'user_register') {
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        Analytics::identify(array(
                            "userId" => $user_id,
                            "traits" => $traits,
                            "timestamp" => $timestamp
                        ));
                    } elseif ($action === 'ninja_forms_after_submission') {
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


                    } elseif ($action === 'gform_after_submission') {
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


                    } elseif (Segment_For_Wp_By_In8_Io::check_associated_identify('hook', $action)) {
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

                }

                elseif ($ajs_anon_id) {
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
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


    }

    /**
     * @param ...$args '$form_data'
     */
    public function ninja_forms_after_submission(...$args)
    {
        $args = array(
            'action_hook' => current_action(),
            'args' => json_decode(json_encode(func_get_args()), true)
        );
        $wp_user_id = get_current_user_id() == 0 ? null : get_current_user_id();
        $args['wp_user_id'] = $wp_user_id;
        $args['ajs_anon_id'] = Segment_For_Wp_By_In8_Io::get_ajs_anon_user_id();
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


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
        $args['timestamp']=time();
        $args["direct"] = true;

        if (isset($event_name) && $event_name !== '') {

            as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

        };

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
        $args['timestamp']=time();

        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );



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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );



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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );



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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );


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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );

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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );



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
        $args['timestamp']=time();
        as_enqueue_async_action( 'async_task', array($args), $this->plugin_name );



    }

    public function custom_events(...$args)
    {

    }

}

