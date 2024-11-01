=== Site Checklist ===
Contributors: marijn-bent, van-ons
Tags: check, launch check, checklist plugin, going live, checklist
Requires at least: 4.5
Tested up to: 4.7
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use this plugin to check for various things to make sure everything is setup right. You can use this plugin when going live or doing maintenance.

== Description ==

Site Checklist is born out of frustration we had while manually checking sites for various points. This plugin will do the hard work for you.

**Current checks**

This plugins checks if:

* Database prefix is wp_
* User with the name 'admin' exists
* Upload folder is writeable
* Comments are turned off
* Permalinks are configured
* Inactive themes are found
* Inactive plugins are found
* The standard post and page exists
* The plugin 'What the File' is installed
* The plugin 'Yoast SEO' is installed
* wp-config-sample.php exists
* Unique authentication keys are setup
* Site is turned public
* A custom 404 template exists
* A print.css file exists
* A style.min.css exists
* Administrators use one of the 1000 weakest passwords
* XMLRPC is turned off
* DISALLOW_FILE_EDIT is defined
* The JSON API is turned off

And also:

* Retrieves PageSpeed insight scores from Google
* Generates page with the most used HTML elements for you to check
* Can generate screenshots of your site on different screens and devices using Browserstack.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/site-checklist` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools->Checklist and start the checks
1. Optional: Fill in your Browserstack credentials and generate screenshots

== Screenshots ==

1. Checklist page after running the checks.
2. Generated screenshots using Browserstack
3. Settings page

== Changelog ==

= 1.0.6 =
* Bugfix
= 1.0.5 =
* Bugfix
= 1.0.4 =
* Adds filter to result page
* Bugfixes
= 1.0.3 =
* Checks if XMLRPC is turned off
* Checks if DISALLOW_FILE_EDIT is defined
* Checks if the JSON API is turned off
* Shows result of the checklist after running
= 1.0.2 =
* Bugfixes
* Checks if administrators use one of the 1000 weakest passwords
= 1.0.1 =
* Added plugin assets
= 1.0 =
* Initial plugin release