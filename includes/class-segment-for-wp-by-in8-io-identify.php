<?php

class Segment_For_Wp_By_In8_Io_Identify
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

    function render_segment_identify()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) { //Only render these for actual browsers
            return;
        }

        if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('identify') || Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_logout')) {
            $settings = $this->settings;
            if (isset($settings['js_api_key']) && $settings['js_api_key'] !== '' && $settings['js_api_key'] !== null) {
                $current_user = wp_get_current_user();
                $current_post = get_post();
                $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
                $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);
                if ($trackable_user && $trackable_post) {
                    if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('identify')) {
                        $cookie_data = Segment_For_Wp_By_In8_Io_Cookie::get_cookie('identify');
                        $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('identify');
                        $user_info = Segment_For_Wp_By_In8_Io::get_user_info_from_cookie($cookie_data, $cookie_name);
                        $wp_user_id = $user_info["wp_user_id"];
                        $user_id = $user_info['user_id'];
                        if (!isset($wp_user_id) || !isset($user_id) || $wp_user_id == null || $wp_user_id == 0) {
                            ?>
                            <script type="text/javascript">
                                Cookies.remove("<?php echo sanitize_text_field($cookie_name)?>");
                            </script>
                            <?php
                            Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies($cookie_name);
                            return;
                        }

                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        $traits = array_filter($traits);
                        $traits = apply_filters('segment_for_wp_change_user_traits', $traits, $user_info);
                        $user_id = apply_filters('segment_for_wp_change_user_id', $user_id);

                        ?>
                        <script type="text/javascript">
                            console.log(<?php echo sanitize_text_field(json_encode($traits))?>)
                            analytics.identify("<?php echo sanitize_text_field($user_id) ?>",
                                <?php echo json_encode($traits) ?>,
                                {},
                                function () {
                                    Cookies.remove("<?php echo sanitize_text_field($cookie_name)?>");
                                }
                            );
                        </script>
                        <?php
                        Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies($cookie_name);

                    } else {
                        $wp_user_id = $current_user->ID;
                        if ($wp_user_id && $wp_user_id != 0) {
                            $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
                            if ($user_id && $user_id !== 0) {
                                $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                                $traits = array_filter($traits);
                                $traits = apply_filters('segment_for_wp_change_user_traits', $traits, $current_user);
                                $user_id = apply_filters('segment_for_wp_change_user_id', $user_id);
                                ?>
                                <script type="text/javascript">
                                    analytics.identify("<?php echo sanitize_text_field($user_id) ?>",
                                        <?php echo sanitize_text_field(json_encode($traits))?>,
                                    );
                                </script>
                                <?php
                            }
                        }
                    }
                }
                if (Segment_For_Wp_By_In8_Io_Cookie::check_cookie('wp_logout')) {
                    $cookie_name = Segment_For_Wp_By_In8_Io_Cookie::get_cookie_name('wp_logout');
                    Segment_For_Wp_By_In8_Io_Cookie::delete_matching_cookies($cookie_name);
                }
            }
        } else {
            if (is_user_logged_in() && !isset($_COOKIE["ajs_user_id"])) {

                if (isset($settings['js_api_key']) && $settings['js_api_key'] !== '' && $settings['js_api_key'] !== null) {
                    $current_user = wp_get_current_user();
                    $current_post = get_post();
                    $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
                    $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);
                    if ($trackable_user && $trackable_post) {
                        $wp_user_id = get_current_user_id();
                        $user_id = Segment_For_Wp_By_In8_Io::get_user_id($wp_user_id);
                        $traits = Segment_For_Wp_By_In8_Io::get_user_traits($wp_user_id);
                        $traits = array_filter($traits);
                        $traits = apply_filters('segment_for_wp_change_user_traits', $traits, $current_user);
                        $user_id = apply_filters('segment_for_wp_change_user_id', $user_id);

                        ?>
                        <script type="text/javascript">
                            console.log(<?php echo sanitize_text_field(json_encode($traits))?>)
                            analytics.identify("<?php echo sanitize_text_field($user_id) ?>",
                                <?php echo json_encode($traits) ?>,
                                {},
                                function () {
                                }
                            );
                        </script>
                        <?php

                    }
                }

            }

        }

    }

}
