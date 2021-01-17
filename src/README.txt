=== Plugins Page Cleanup ===
Contributors: BrianHenryIE
Donate link: https://github.com/brianhenryie/bh-wp-plugins-page
Tags: plugins, tidy, clean
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Removes formatting and up-sells, and moves Settings links to the beginning and Deactivate links to the end of plugins.php action links. Disables plugin deactivation surveys.

== Description ==

* Removes formatting from links on plugins.php
* Moves external links from the action links (first) column into the description column
* Moves deactivate to the end of the list of action links, and settings to the beginning (where it exists)
* Replaces GitHub links with icons
* Removes click handlers on Deactivate links (to remove deactivation surveys)

== Installation ==

Install from WordPress plugins repo, or download from the releases on [GitHub](https://github.com/BrianHenryIE/bh-wp-plugins-page/releases).

No configuration necessary.

== Frequently Asked Questions ==

= How can I help? =

Open an issue on [GitHub](https://github.com/BrianHenryIE/bh-wp-plugins-page/issues) with a link to a plugin that is still offensive.

= GitHub =

Yes: [BrianHenryIE/bh-wp-plugins-page](https://github.com/BrianHenryIE/bh-wp-plugins-page)

= I like this plugin, what else will I like? =

**[Admin Menu Editor](https://adminmenueditor.com/)**

This allows reordering the wp-admin menu items, i.e. categorise them and hide unwanted menu items.

[Admin Menu Editor on WordPress.org](https://wordpress.org/plugins/admin-menu-editor/) | [Admin Menu Editor Pro](https://adminmenueditor.com/)

**[Plugin Notes Plus](https://wordpress.org/plugins/plugin-notes-plus/)**

This adds another column on the plugins page where you can write "what bad thing will happen if I disable this plugin".

[Plugin Notes Plus on WordPress.org](https://wordpress.org/plugins/plugin-notes-plus/) | [Plugin Notes Plus on GitHub.com](https://github.com/jamiebergen/plugin-notes-plus)

= What other plugins do you have? =

[Autologin URLs](https://wordpress.org/plugins/bh-wp-autologin-urls/) adds short-lived login codes to links in emails sent from WordPress.

[Set Gateway By URL](https://wordpress.org/plugins/bh-wc-set-gateway-by-url/) allows setting WooCommerce's selected payment gateway in links shared with customers.

A plethora of working but unpolished plugins on GitHub: [Brian](https://github.com/BrianHenryIE?tab=repositories&q=&type=source&language=php)

== Screenshots ==

1. Before

2. After

3. No more deactivation dialogs

== Changelog ==

= 1.0.1 =
* Improvement: GitHub icons are now only used when the link is to a repo (i.e. not to repo/issues etc.)
* Bugfix: Was hiding links when "premium" was in the URL (when trying to hide upsells).
* Bugfix: Plugins with empty action links were crashing things.

= 1.0 =
* Removes formatting from links on plugins.php
* Moves external links from the action links (first) column into the description column
* Moves deactivate to the end of the list of action links, and settings to the beginning (where it exists)
* Replaces GitHub links with icons
* Removes click handlers on Deactivate links (to remove deactivation surveys)
