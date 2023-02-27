=== Segment For Wordpress ===
Contributors: juanin8
Donate link: https://www.juangonzalez.com.au
Tags: segment, tracking, analytics
Requires at least: 5.6
Tested up to: 6.1.1
Requires PHP: 7.3.5
Stable tag: 2.3.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Segment Analytics for WordPress. Event tracking integrated into hundreds of 3rd party tools, see segment.com | Free WooCommerce, Ninja Form & Gravity Form integrations. | Re-written & extended by https://in8.io

== Description ==

Segment Analytics for WordPress. Free WooCommerce, Ninja Form & Gravity Form integrations.
Uses Segment's official PHP libraries for Server Side events, as well as a lot of other features functionality.
I completely rewrote this plugin from the previous version.
Client side and server side tracking.
Client side events fire upon validation (ie, for submissions)
Server side events are scheduled and happen asynchronously, so users don't have to wait for processing of these events, it won't slow them down.

There are annotations throughout the plugin to explain the different features.

* Set a User ID based on user custom field, email, or WP user id
* Built-in support for WooCommerce, Ninja Forms, Gravity Forms and WordPress native events.
* Re-name events, choose what traits to include in Identify calls, etc...
* Supports client side (JS API) and server-side tracking (PHP API)
* Ability to filter out roles, custom post types and the admin area
* Easily include userID and email properties in track calls
* Ability to include custom user traits in identify calls using meta keys

== Hooks ==

You can use the following filters, please return the correct values.

- "segment_for_wp_change_user_id" ($user_id): Return a user id.
- "segment_for_wp_change_user_traits" ($traits, $wp_user_id): Return an associative array of traits.
- "segment_for_wp_change_event_properties" ($properties, $action, $data): Return an associative array of properties.
- "segment_for_wp_change_product_properties" ($properties): Return an associative array of product properties (for WooCommerce).
- "segment_for_wp_change_order_properties" ($properties): Return an associative array of order properties (for WooCommerce).

Happy to extend it, let me know.

== Installation ==

1. Upload the plugin zip file through the Plugins section of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Enter your Segment API keys into the plugin and choose your events/settings.

== Upgrade Notice ==

If you're upgrading between version 1.0 to 2.0:
You will need to reconfigure the plugin again, so test it first and backup before upgrading.
The new version of the plugin is much much better, but you will need to set it up again.

== Screenshots ==

1. Supports client side (JS API) and server-side tracking (PHP API)
2. Ability to filter out roles, custom post types and the admin area
3. Easily include userID and email properties
4. Many other options for Identify calls
5. Support for Ninja Forms and Gravity Forms
6. Supports WooCommerce events, you can re-name them and track them server side, etc...

== Frequently Asked Questions ==

1. What do I need? You will need to signup for Segment.com. You will need a Segment JavaScript source and a PHP Source.
2. How much does it cost? Plugin is free. Segment is free up to 1,000 users.
3. Will it slow my site down? Depends. The more destinations and the more events you use, the slower things can go. The same way as if you installed the scripts directly.

== Changelog ==

= 2.3.3 =
* Fix error with flatten function on Server Side tracking

= 2.3.2 =
* Include more pages in custom post meta functionality
* Recursive check for string length of server side payload

= 2.3.1 =
* Fix File consumer
* Fix php warnings on plugin activation


= 2.3.0 =
* Add server side Page tracking
* Add support for more Gravity Form fields
* Fix Ninja Forms server side tracking
* Fix bugs with role filtering
* Fix bug with some empty page names


= 2.2.1 =
* Fix bug initialising plugin without write permissions for plugin folder
* Handle critical error when Server side payload is greater than 8000 characters
* Fix a bug with client side events for Gravity Forms

= 2.2.0 =
* Let users increase Socket consumer timeout and cron job interval further (help support less powerful servers)

= 2.1.1 =
* Fixes for Logged In event and fallback identify call

= 2.1.0 =
* Adding filter hooks and hook documentation
* Fixed User ID when default is selected.

= 2.0.0 =
* Re-wrote the whole plugin. Much better now.
* Now uses Segment's official PHP Library for Server Side events
* More advanced options and extra functionality. Pull data from user_meta, post_meta, etc... to populate identify calls, page calls and track calls.
* Better implementation of client side tracking
* Better performance and reliability
* Made all of the server side tracking asynchronous

= 1.0.9 =
* More updates to bring woocommerce integration inline with their 'new' functions vs legacy ones I used to begin with

= 1.0.8 =
* Moving to new WooCommerce methods to get order data in order to avoid some error notices

= 1.0.7 =
* Small Fixes

= 1.0.6 =
* Fixed an bug with WC functions


= 1.0.5 =
* Removed some unused functions, fixed a potential bug when reading 'signed up' cookies.

= 1.0.1 =
* Updated README and made the plugin description more helpful

= 1.0 =
* First version