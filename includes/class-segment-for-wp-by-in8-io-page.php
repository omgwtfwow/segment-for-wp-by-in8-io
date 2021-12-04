<?php
//
//  Render page call, goes in <head>
class Segment_For_Wp_By_In8_Io_Page
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

    function render_segment_page()
    {

        $settings = $this->settings;

        if (isset($settings['js_api_key']) && $settings['js_api_key'] !== '' && $settings['js_api_key'] !== null) {
            $current_user = wp_get_current_user();
            $current_post = get_post();
            $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
            $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);
            if ($trackable_post === false || $trackable_user === false) {
                //not trackable
                return;
            } else {
                $page_name = Segment_For_Wp_By_In8_Io::get_page_name($current_post);
                $page_props = Segment_For_Wp_By_In8_Io::get_page_props($current_post);
                if ($page_name != "" && count($page_props) > 0) {
                    ?>
                    <script type="text/javascript">
                        analytics.page("<?php echo sanitize_text_field($page_name) ?>",<?php echo sanitize_text_field(wp_json_encode($page_props))?>);
                    </script>
                    <?php
                }
                if ($page_name != "" && count($page_props) == 0) {
                    ?>
                    <script type="text/javascript">
                        analytics.page("<?php echo sanitize_text_field($page_name) ?>");
                    </script>
                    <?php
                }
                if ($page_name == "") {
                    ?>
                    <script type="text/javascript">
                        analytics.page();
                    </script>
                    <?php
                }

            }
        }

    }


}