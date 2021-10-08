<?php

//
//  Render tracking snippet in <footer>
class Segment_For_Wp_By_In8_Io_Track
{
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
     * @var
     */
    private $settings;

    public function __construct($plugin_name, $version, $settings)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = $settings;
    }

    public function render_track_call()
    {

        $settings = $this->settings;


        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) { //Only render these for actual browsers
            return;
        }

        if (isset($settings['js_api_key']) && $settings['js_api_key'] !== '' && $settings['js_api_key'] !== null) {

            $action = current_action();
            $current_user = wp_get_current_user();
            $current_post = get_post();
            $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
            $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);

            if ($trackable_user && $trackable_post) {

                $tracks = Segment_For_Wp_By_In8_Io::get_current_tracks();

                foreach ($tracks as $track) {
                    $cookie = $track["cookie"];
                    $hook = $track["hook"];
                    $event_name = $track["event"];
                    $event_opts = array();
                    $event_props = $track["properties"];
                    if (count($event_props) == 0) {
                        $event_props = array();
                    }
                    if ($event_name != "") {
                        if (!is_user_logged_in()) {
                            if (Segment_For_Wp_By_In8_Io::is_ecommerce_order_hook($hook)) {
                                if (array_key_exists("woocommerce_match_logged_out_users", $settings["track_woocommerce_fieldset"])) {
                                    if ($settings["track_woocommerce_fieldset"]["woocommerce_match_logged_out_users"] === "yes") {
                                        if (array_key_exists('billing_email', $event_props)) {
                                            $order_email = $event_props["billing_email"];
                                            if (filter_var($order_email, FILTER_VALIDATE_EMAIL)) {
                                                if (email_exists($order_email)) {
                                                    $user = get_user_by('email', $order_email);
                                                    $user_id = Segment_For_Wp_By_In8_Io::get_user_id($user->ID);
                                                    $event_opts['userId'] = $user_id;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                        }
                        ?>
                        <script type="text/javascript">
                            analytics.track("<?php
                                    //name
                                    echo sanitize_text_field($event_name)
                                    ?>",
                                <?php
                                //props
                                echo sanitize_text_field(json_encode($event_props))
                                ?>,
                                <?php
                                //opts
                                echo sanitize_text_field(json_encode($event_opts));
                                ?>,
                                function () {
                                    Cookies.remove("<?php echo sanitize_text_field($cookie)?>");
                                });
                        </script>
                        <?php
                    }
                }
            }
        }
    }
}
