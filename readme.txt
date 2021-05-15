=== Nearby ===

Description:	Creates table of nearby locations based on GPS co-ordinates.
Version:		2.5.0
Tags:			location,gps
Author:			azurecurve
Author URI:		https://development.azurecurve.co.uk/
Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/nearby/
Download link:	https://github.com/azurecurve/azrcrv-nearby/releases/download/v2.5.0/azrcrv-nearby.zip
Donate link:	https://development.azurecurve.co.uk/support-development/
Requires PHP:	5.6
Requires:		1.0.0
Tested:			4.9.99
Text Domain:	code
Domain Path:	/languages
License: 		GPLv2 or later
License URI: 	http://www.gnu.org/licenses/gpl-2.0.html

Allows a table of nearby locations to be created based on GPS co-ordinates.

== Description ==

# Description

Nearby creates a table of nearby locations (pages) based on GPS co-ordinates.

Nearby integrates with the following [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/) plugins:
 * [Flags from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/flags/) allows a location to be set for a page; this will display the location flag next to the location name in the table of nearby attractions.
 * [Icons from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/icons/) allows an icon to be displayed next to a nearby location which has an entry on a timeline (requires integration with Timelines to be enabled).
 * [Timelines from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/timelines/) allows a character (such as *) to be displayed next to a nearby location which has an entry on a timeline.
 * [Toggle Show/Hide from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/) allows the table of nearby locations to be enclosed with a toggle.

Apply the [nearby] shortcode to a page with co-ordinates and nearby locations (pages with co-ordinates), based on the settings, will be displayed in a table.

The shortcode accepts two parameters:
 * **type** to limit nearby attractions (multiple types can be provided in comma separated string)
 * **title** to override the default toggle title

Example shortcode usage: **[nearby type="Distilleries" title="Nearby Distilleries"]**

Examples of this plugin in action:
* [coppr|Distilleries To Visit](https://coppr.uk/distilleries/ireland/northern/echlinville/)
* [DarkNexus|Tourist Attractions](https://www.darkforge.co.uk/attractions/europe/republic-of-ireland/east/county-meath/newgrange-monument/)

This plugin is multisite compatible; each site will need settings to be configured in the admin dashboard.

== Installation ==

# Installation Instructions

 * Download the plugin from [GitHub](https://github.com/azurecurve/azrcrv-nearby/releases/latest/).
 * Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
 * Activate the plugin.
 * Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

== Frequently Asked Questions ==

# Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot file is in the plugins languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk; if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

== Changelog ==

# Changelog

### [Version 2.5.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.5.0)
 * Add uninstall.
 * Update azurecurve menu and logo.
 
### [Version 2.4.4](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.4.4)
 * Fix bug with version number.
 
### [Version 2.4.3](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.4.3)
 * Fix bug with call to undefined function on settings page.
 * Update azurecurve menu.
 
### [Version 2.4.2](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.4.2)
 * Fix bug with timeline integration not returning correct result.
 * Update azurecurve menu.
 * Replace azurecurve menu icon with svg.
 
### [Version 2.4.1](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.4.1)
 * Fix bug with available icons not listing when [Icons](https://development.azurecurve.co.uk/classicpress-plugins/icons/) integration enabled.
 
### [Version 2.4.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.4.0)
 * Add types; allows attractions to have a type set.
 * Add **type** parameter to shortcode allowing included attractions to be limited.
 * Add **title** parameter to shortcode allowing toggle title to be amended.
 * Add plugin links to Integrate settings tab.
 * Fix bug with display of selected country in page meta box.
 * Fix table display bug when no nearby attractions.

### [Version 2.3.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.3.0)
 * Amend call to get country flag.
 * Add tabs to admin settings page.
 * Integrate with [Icons](https://development.azurecurve.co.uk/classicpress-plugins/icons/) for icon to use as timeline signifier.
 
### [Version 2.2.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.2.0)
 * Fix plugin action link to use admin_url() function.
 * Rewrite option handling so defaults not stored in database on plugin initialisation.
 * Update azurecurve plugin menu.
 * Amend to only load css when shortcode on page.
 
### [Version 2.1.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.1.0)
 * Fix bug in update manager namespace declaration.
 * Add integration with [Timelines](https://development.azurecurve.co.uk/classicpress-plugins/timelines/) from [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/).
 * Add plugin icon and banner.

### [Version 2.0.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v2.0.0)
 * Correct typo in maximum-location option which was maximim-locations; settings will need to be updated and saved once update applied.

### [Version 1.2.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v1.2.0)
 * Add 16 point compass with option to toggle from 32 point compass.

### [Version 1.1.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v1.1.0)
 * Add direction for nearby locations using 32 compass points.
 * Update CSS to provide more control.

### [Version 1.0.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v1.0.0)
 * Initial release.

== Other Notes ==

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://codepotent.com/classicpress/plugins/update-manager/) by [CodePotent](https://codepotent.com/) for fully integrated, no hassle, updates.

Some of the top plugins available from **azurecurve** are:
* [Add Twitter Cards](https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/)
* [Breadcrumbs](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/)
* [Series Index](https://development.azurecurve.co.uk/classicpress-plugins/series-index/)
* [To Twitter](https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/)
* [Theme Switcher](https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/)
* [Toggle Show/Hide](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/)