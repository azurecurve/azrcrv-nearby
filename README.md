# azrcrv-nearby
[Nearby plugin for ClassicPress Plugin](https://development.azurecurve.co.uk/classicpress-plugins/nearby/)

# Description

Nearby creates a table of nearby locations (pages) based on GPS co-ordinates.

Nearby integrates with the following [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/) plugins:
 * [Flags from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/flags/) allows a location to be set for a page; this will display the location flag next to the location name in the table of nearby attractions.
 * [Icons from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/icons/) allows an icon to be displayed next to a nearby location which has an entry on a timeline (requires integration with Timelines to be enabled).
 * [Timelines from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/timelines/) allows a character (such as *) to be displayed next to a nearby location which has an entry on a timeline.
 * [Toggle Show/Hide from azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/) allows the table of nearby locations to be enclosed with a toggle.

Apply the [nearby] shortcode to a page with co-ordinates and nearby locations (pages with co-ordinates), based on the settings, will be displayed in a table.

The shortcode accepts two parameters:
 * `type` to limit nearby attractions (multiple types can be provided in comma separated string).
 * `title` to override the default toggle title.

Example shortcode usage:
```
[nearby type="Distilleries" title="Nearby Distilleries"]
```

Examples of this plugin in action:
* [coppr|Distilleries To Visit](https://coppr.uk/distilleries/ireland/northern/killowen/)
* [DarkNexus|Tourist Attractions](https://www.darkforge.co.uk/attractions/europe/republic-of-ireland/east/county-meath/newgrange-monument/)

This plugin is multisite compatible; each site will need settings to be configured in the admin dashboard.

# Installation Instructions

 * Download the latest release of the plugin from [GitHub](https://github.com/azurecurve/azrcrv-nearby/releases/latest/).
 * Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
 * Activate the plugin.
 * Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://directory.classicpress.net/plugins/update-manager) for fully integrated, no hassle, updates.

Some of the other plugins available from **azurecurve** are:
 * Comment Validator - [details](https://development.azurecurve.co.uk/classicpress-plugins/comment-validator/) / [download](https://github.com/azurecurve/azrcrv-comment-validator/releases/latest/)
 * Estimated Read Time - [details](https://development.azurecurve.co.uk/classicpress-plugins/estimated-read-time/) / [download](https://github.com/azurecurve/azrcrv-estimated-read-time/releases/latest/)
 * Events - [details](https://development.azurecurve.co.uk/classicpress-plugins/events/) / [download](https://github.com/azurecurve/azrcrv-events/releases/latest/)
 * Filtered Categories - [details](https://development.azurecurve.co.uk/classicpress-plugins/filtered-categories/) / [download](https://github.com/azurecurve/azrcrv-filtered-categories/releases/latest/)
 * Flags - [details](https://development.azurecurve.co.uk/classicpress-plugins/flags/) / [download](https://github.com/azurecurve/azrcrv-flags/releases/latest/)
 * Icons - [details](https://development.azurecurve.co.uk/classicpress-plugins/icons/) / [download](https://github.com/azurecurve/azrcrv-icons/releases/latest/)
 * Loop Injection - [details](https://development.azurecurve.co.uk/classicpress-plugins/loop-injection/) / [download](https://github.com/azurecurve/azrcrv-loop-injection/releases/latest/)
 * Page Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/page-index/) / [download](https://github.com/azurecurve/azrcrv-page-index/releases/latest/)
 * Remove Revisions - [details](https://development.azurecurve.co.uk/classicpress-plugins/remove-revisions/) / [download](https://github.com/azurecurve/azrcrv-remove-revisions/releases/latest/)
 * Widget Announcements - [details](https://development.azurecurve.co.uk/classicpress-plugins/widget-announcements/) / [download](https://github.com/azurecurve/azrcrv-widget-announcements/releases/latest/)