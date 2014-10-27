=== WordPress PopUp ===
Contributors: WPMUDEV
Tags: Popup, Pop-up, Pop Over, popover, Responsive Popup, Advertise, Promotion, Marketing, Lightbox, Mailing list pop-up
Requires at least: 3.1
Tested up to: 4.0
Stable tag: trunk

WordPress PopUp is the smart, responsive, customizable and beautifully coded pop-up and pop-over plugin for WordPress and Multisite.

== Description ==

WordPress PopUp brings a proven solution for effective advertising to your site or network. Introduce mailing lists, exclusive offers and other advertisements to your clients, users or visitors with a polished pop-up ad.

[youtube  https://www.youtube.com/watch?v=lxyomzQkQKc]

Because the WordPress PopUp interface provides a simple, flexible design template in a familiar workspace, you can spend more time creating and less time building. Plus, with this fully-responsive plugin, your pop-ups will look fantastic on every device.

★★★★★<br />
“The plugin works great on Multisite. It is amazingly fast, easy to install and very flexible.” - <a href="http://profiles.wordpress.org/spkane">spkane</a>

<strong>Need More?</strong>

<blockquote>
While WordPress PopUp is feature-rich and flexible, you may need more - so we created <a href="http://premium.wpmudev.org/project/the-pop-over-plugin/">PopUp Pro</a>. PopUp Pro takes everything you love about the free version and adds more templates, greater design control and an unlimited number of pop-ups with extended powerful behaviors for setting specific times, locations and who the pop-ups are displayed to.<br /><br />
</blockquote>

<strong>See What WordPress PopUp and PopUp Pro Can Do For You:</strong>

<ul>
<li>Design pop-ups from a familiar intuitive interface</li>
<li>Unlimited pop-ups that display across an entire network, on individual sites or on specific URLs - limited to 1 active pop-up in free version</li>
<li>Both responsive and fixed design options mean your pop-ups look great on every device</li>
<li>3 built-in modern templates for displaying clean, simple or sophisticated pop-ups - 1 easy-to-use layout in the free version</li>
<li>Choose to hide pop-ups from mobile devices - pro version only</li>
<li>Control who sees a pop-up including logged out users, visitors who have never commented and search engine visitors - limited with free version</li>
<li>Set when a pop-up appears based on time, location, CSS markers and clicks - limited on free version</li>
<li>Allow visitors to hide a pop-up from ever displaying again</li>
<li>Display your pop-ups to visitors from specific geographic locations - pro version only</li>
<li>Access to our brilliant fast 24/7 support team</li>
</ul>

If you are looking to build your mailing list, increase sales or even promote an event, use WordPress PopUp or <a href="http://premium.wpmudev.org/project/the-pop-over-plugin/">PopUp Pro</a> - it just works.

== Installation ==

WordPress Installation Instructions:

----------------------------------------------------------------------

1) Place the popover directory in the plugins directory
2) Activate the plugin


WPMU Installation Instructions:

----------------------------------------------------------------------

1) Place the popover directory in the plugins directory
2) Network Activate the plugin

For blog by blog, leave as is.

For network wide control - add the line define('PO_GLOBAL', true); to your wp-config.php file.

* You can find <a href='http://premium.wpmudev.org/manuals/installing-regular-plugins-on-wpmu/'>in-depth setup and usage instructions with screenshots here &raquo;</a>

== Screenshots ==

1. The PopUp in action
2. PopUp produces double the number of pages and twice the length of stay compared to visitors that come via Adwords
3. Some of the settings options

== Changelog ==

= 4.6.1.3 =
* New: Allow page to be scrolled while PopUp is open.
* Fix: Prevent PopUps from staying open after submitting a form to external URL.
* Fix: PopUps without content can be displayed now.

= 4.6.1.2 =
* New: Two new WordPress filters allow custom positioning and styling of PopUps.
* Fix: Correctly display Meta-boxes of other plugins in the popup-editor.
* Fix: Plugins that use custom URL rewriting are working now (e.g. NextGen Gallery)
* Fix: PopUps can be edited even on servers with memcache/similar caching extensions.
* Fix: Resolve "Strict Standards" notes in PHP 5.4
* Fix: Rule "Not internal link" now works correctly when opening page directly.
* Fix: Rule "Specific Referrer" handles empty referrers correctly.
* Better: Forms inside PopUps will only refresh the PopUp and not reload the page.
* Better: Detection of theme compatibility for loading method "Page Footer" improved.

= 4.6.1.1 =
* New: Added Contextual Help to the PopUp editor to show supported shortcodes.
* Fix: Logic of rule "[Not] On specific URL" corrected.
* Fix: Close forever now works also via click on background layer.
* Better: Improved info on supported shortcodes.

= 4.6.1 =
* Fix: For some users the plugin was not loading after update to 4.6
* Fix: Old Popups will now replace shortcodes correctly.

= 4.6 =

* Completely re-build the UI from ground up!
* Migrated PopUps to a much more flexible data structure.
* Merged sections "Add-Ons" and "Settings" to a single page.
* Removed old legacy code; plugin is cleaner and faster.
* New feature: Preview PopUp inside the Editor!
* Three new, modern PopUp styles added.
* Featured Image support for new PopUp styles.

= 4.4.5.4 =

* Performance improvements
* Fixed issue with dynamic JavaScript loading
* Added PO_PLUGIN_DIR in config for changing plugin directory name

= 4.4.5.2 =

* Added missing translatable strings
* Updated language file

= 4.4.5.1 =

* added collation to tables creation code
* updated require calls to include directory path
* moved custom loading out of experimental status
* set default loading method to custom loading

= 4.4.5 =

* Added different custom loading method that should be cache resistant and remove issues with other ajax loading method.
* Made On URL rule more specific so that it doesn't match child pages when the main page is specified

= 4.4.4 =

* Added option to switch from JS loading to standard loading of pop ups.
* Added ability to use regular expressions in the referrers and on url conditions.
* Prepared code to make it easy to upgrade interface for future releases.

= 4.4.3 =

* Updated for WP 3.5
* Added initial attempt to distinguish referrers from Google search and referrers from Google custom site search.

= 4.4.2 =

* Removed unneeded css and js files
* Updated language file

= 4.4.1 =

* Moved popover loading js to be created by a php file due to needing extra processing.
* Fixed issue with directory based sites loading popover script from main site.
* Fixed issue of popover loading on login and register pages.

= 4.4 =

* Updated Popover to load via ajax call rather than page creation for cache plugin compatibility

= 4.3.2 =

* Major rewrite
* Multiple PopUps can be created
* Fixed issue of network activation not creating tables until admin area visited
* Updated code to remove all notifications, warnings and depreciated function calls ready for WP 3.4

= 3.1.4 =

* WP3.3 style updating

= 3.0 =

* Initial release
