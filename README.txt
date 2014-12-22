=== PrimeTime WordPress + Discourse SSO ===
Contributors: etcio, nphaskins
Tags: discourse, forum, sso
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides single sign-on capabilities for Discourse using WordPress user authentication.

== Description ==

Discourse is a fantastic new forum that can add another layer to your WordPress community. This plugin allows you to create a fluid experience by using your WordPress installation as the authentication server, creating a single-sign-on (SSO) for your users!

Notes:

*   This plugin requires permalinks to be enabled.

Some Features:

*   Seamless integration into almost any WordPress installation.
*   Get setup within minutes through 3 easy steps. Anyone can do it.

Coming Soon:

*   Only allow access with certain capabilities or roles.

== Installation ==

There are only three steps to configuring your WP + Discourse SSO!

= 1. Install this plugin and activate it =

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `plugin-name.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `pt-wp-discourse-sso.zip`
2. Extract the `pt-wp-discourse-sso` directory to your computer
3. Upload the `pt-wp-discourse-sso` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= 2. Configure the plugin =

Under settings, you'll see the SSO configuration page. Add the url to your Discourse community forum. Generate a secret key that will be used to secure the authentication between the installations and paste it here.

= 3. Enabling SSO on Discourse =

In your Discourse admin settings, find the settings labeled, enable_sso, sso_url and sso_secret. Enter the URL of your WP installation, as well as the secret key, and enable sso.

More information about these settings can be found here:
https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045

That's it!

== Frequently Asked Questions ==

= Where can I get a Discourse forum? =

https://www.discoursehosting.com/
https://discourse.org

== Screenshots ==

1. The plugin settings screen. Here you enter your secret key as well as the url to your discourse site.
2. Custom SSO page can be assigned with any theme.
3. Enjoy single sign on between WP + Discourse. Cool!

== Credits ==

* Request processing adapted from Adam Capirola : https://gist.github.com/adamcapriola/11300529
* SSO methods adapted from ArmedGuy : https://github.com/ArmedGuy/discourse_sso_php

== Changelog ==

= 0.2.2 =
* Better compatibility with PHP 5.2

= 0.2.1 =
* More user friendly configuration
* Fields a bit more forgiving

= 0.2 =
* Refactoring away from template based system

= 0.1 =
* Initial release