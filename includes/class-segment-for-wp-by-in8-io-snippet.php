<?php
//
//  Render tracking snippet, goes in <head>
class Segment_For_Wp_By_In8_Io_Snippet
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

    public function __construct($plugin_name, $version, $settings)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = $settings;
    }

    function render_segment_snippet()
    {
        $settings = $this->settings;
        if (isset($settings['js_api_key']) && $settings['js_api_key'] !== '' && $settings['js_api_key'] !== null) {
            $current_user = wp_get_current_user();
            $current_post = get_post();
            $trackable_user = Segment_For_Wp_By_In8_Io::check_trackable_user($current_user);
            $trackable_post = Segment_For_Wp_By_In8_Io::check_trackable_post($current_post);
            if ($trackable_user === false || $trackable_post === false) {
                //not trackable
                return;
            } else {
                ?>
                <script type="text/javascript">
                    !function () {
                        var analytics = window.analytics = window.analytics || [];
                        if (!analytics.initialize) if (analytics.invoked) window.console && console.error && console.error("Segment snippet included twice."); else {
                            analytics.invoked = !0;
                            analytics.methods = ["trackSubmit", "trackClick", "trackLink", "trackForm", "pageview", "identify", "reset", "group", "track", "ready", "alias", "debug", "page", "once", "off", "on"];
                            analytics.factory = function (t) {
                                return function () {
                                    var e = Array.prototype.slice.call(arguments);
                                    e.unshift(t);
                                    analytics.push(e);
                                    return analytics
                                }
                            };
                            for (var t = 0; t < analytics.methods.length; t++) {
                                var e = analytics.methods[t];
                                analytics[e] = analytics.factory(e)
                            }
                            analytics.load = function (t, e) {
                                var n = document.createElement("script");
                                n.type = "text/javascript";
                                n.async = !0;
                                n.src = "https://cdn.segment.com/analytics.js/v1/" + t + "/analytics.min.js";
                                var a = document.getElementsByTagName("script")[0];
                                a.parentNode.insertBefore(n, a);
                                analytics._loadOptions = e
                            };
                            analytics.SNIPPET_VERSION = "4.1.0";
                            analytics.load("<?php echo sanitize_text_field($settings['js_api_key']); ?>");
                        }
                    }();
                </script>
                <?php
            }
        }
    }
}