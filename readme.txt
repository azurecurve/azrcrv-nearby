=== Nearby ===

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

== Installation ==

# Installation Instructions

 * Download the latest release of the plugin from [GitHub](https://github.com/azurecurve/azrcrv-nearby/releases/latest/).
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

### [Version 3.0.4](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v3.0.4)
 * Update plugin header for compatibility with ClasssicPress v2.
 
### [Version 3.0.3](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v3.0.3)
 * Update plugin header and readme for compatibility with ClassicPress Directory v2.
 * Update Update Manager to version 2.5.0.
 
### [Version 3.0.2](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v3.0.2)
 * Update readme file for compatibility with ClassicPress Directory.
 
### [Version 3.0.1](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v3.0.1)
 * Update readme files.
 * Update language template.
 * Fix bug with azurecurve menu.
 
### [Version 3.0.0](https://github.com/azurecurve/azrcrv-nearby/releases/tag/v3.0.0)
 * Migrate type, country and co-ordinates data from cp_postmeta to custom table.
 * Change text domain to ensure it is unique.
 * Update to only load admin css when on plugin settings page.
 * Update readme.txt and readme.md.
 * Update azurecurve menu.

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

**azurecurve** was one of the first plugin developers to start developing for ClassicPress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://directory.classicpress.net/plugins/update-manager)  for fully integrated, no hassle, updates.

The other plugins available from **azurecurve** are:
 * Add Open Graph Tags - [details](https://development.azurecurve.co.uk/classicpress-plugins/add-open-graph-tags/) / [download](https://github.com/azurecurve/azrcrv-add-open-graph-tags/releases/latest/)
 * Add Twitter Cards - [details](https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/) / [download](https://github.com/azurecurve/azrcrv-add-twitter-cards/releases/latest/)
 * Avatars - [details](https://development.azurecurve.co.uk/classicpress-plugins/avatars/) / [download](https://github.com/azurecurve/azrcrv-avatars/releases/latest/)
 * BBCode - [details](https://development.azurecurve.co.uk/classicpress-plugins/bbcode/) / [download](https://github.com/azurecurve/azrcrv-bbcode/releases/latest/)
 * Breadcrumbs - [details](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/) / [download](https://github.com/azurecurve/azrcrv-breadcrumbs/releases/latest/)
 * Call-out Boxes - [details](https://development.azurecurve.co.uk/classicpress-plugins/call-out-boxes/) / [download](https://github.com/azurecurve/azrcrv-call-out-boxes/releases/latest/)
 * Check Plugin Status - [details](https://development.azurecurve.co.uk/classicpress-plugins/check-plugin-status/) / [download](https://github.com/azurecurve/azrcrv-check-plugin-status/releases/latest/)
 * Code - [details](https://development.azurecurve.co.uk/classicpress-plugins/code/) / [download](https://github.com/azurecurve/azrcrv-code/releases/latest/)
 * Comment Validator - [details](https://development.azurecurve.co.uk/classicpress-plugins/comment-validator/) / [download](https://github.com/azurecurve/azrcrv-comment-validator/releases/latest/)
 * Conditional Links - [details](https://development.azurecurve.co.uk/classicpress-plugins/conditional-links/) / [download](https://github.com/azurecurve/azrcrv-conditional-links/releases/latest/)
 * Contact Forms - [details](https://development.azurecurve.co.uk/classicpress-plugins/contact-forms/) / [download](https://github.com/azurecurve/azrcrv-contact-forms/releases/latest/)
 * Disable FLoC - [details](https://development.azurecurve.co.uk/classicpress-plugins/disable-floc/) / [download](https://github.com/azurecurve/azrcrv-disable-floc/releases/latest/)
 * Display After Post Content - [details](https://development.azurecurve.co.uk/classicpress-plugins/display-after-post-content/) / [download](https://github.com/azurecurve/azrcrv-display-after-post-content/releases/latest/)
 * Estimated Read Time - [details](https://development.azurecurve.co.uk/classicpress-plugins/estimated-read-time/) / [download](https://github.com/azurecurve/azrcrv-estimated-read-time/releases/latest/)
 * Events - [details](https://development.azurecurve.co.uk/classicpress-plugins/events/) / [download](https://github.com/azurecurve/azrcrv-events/releases/latest/)
 * Filtered Categories - [details](https://development.azurecurve.co.uk/classicpress-plugins/filtered-categories/) / [download](https://github.com/azurecurve/azrcrv-filtered-categories/releases/latest/)
 * Flags - [details](https://development.azurecurve.co.uk/classicpress-plugins/flags/) / [download](https://github.com/azurecurve/azrcrv-flags/releases/latest/)
 * Floating Featured Image - [details](https://development.azurecurve.co.uk/classicpress-plugins/floating-featured-image/) / [download](https://github.com/azurecurve/azrcrv-floating-featured-image/releases/latest/)
 * Gallery From Folder - [details](https://development.azurecurve.co.uk/classicpress-plugins/gallery-from-folder/) / [download](https://github.com/azurecurve/azrcrv-gallery-from-folder/releases/latest/)
 * Get GitHub File - [details](https://development.azurecurve.co.uk/classicpress-plugins/get-github-file/) / [download](https://github.com/azurecurve/azrcrv-get-github-file/releases/latest/)
 * Icons - [details](https://development.azurecurve.co.uk/classicpress-plugins/icons/) / [download](https://github.com/azurecurve/azrcrv-icons/releases/latest/)
 * Images - [details](https://development.azurecurve.co.uk/classicpress-plugins/images/) / [download](https://github.com/azurecurve/azrcrv-images/releases/latest/)
 * Insult Generator - [details](https://development.azurecurve.co.uk/classicpress-plugins/insult-generator/) / [download](https://github.com/azurecurve/azrcrv-insult-generator/releases/latest/)
 * Load Admin CSS - [details](https://development.azurecurve.co.uk/classicpress-plugins/load-admin-css/) / [download](https://github.com/azurecurve/azrcrv-load-admin-css/releases/latest/)
 * Loop Injection - [details](https://development.azurecurve.co.uk/classicpress-plugins/loop-injection/) / [download](https://github.com/azurecurve/azrcrv-loop-injection/releases/latest/)
 * Maintenance Mode - [details](https://development.azurecurve.co.uk/classicpress-plugins/maintenance-mode/) / [download](https://github.com/azurecurve/azrcrv-maintenance-mode/releases/latest/)
 * Markdown - [details](https://development.azurecurve.co.uk/classicpress-plugins/markdown/) / [download](https://github.com/azurecurve/azrcrv-markdown/releases/latest/)
 * Mobile Detection - [details](https://development.azurecurve.co.uk/classicpress-plugins/mobile-detection/) / [download](https://github.com/azurecurve/azrcrv-mobile-detection/releases/latest/)
 * Multisite Favicon - [details](https://development.azurecurve.co.uk/classicpress-plugins/multisite-favicon/) / [download](https://github.com/azurecurve/azrcrv-multisite-favicon/releases/latest/)
 * Page Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/page-index/) / [download](https://github.com/azurecurve/azrcrv-page-index/releases/latest/)
 * Post Archive - [details](https://development.azurecurve.co.uk/classicpress-plugins/post-archive/) / [download](https://github.com/azurecurve/azrcrv-post-archive/releases/latest/)
 * Redirect - [details](https://development.azurecurve.co.uk/classicpress-plugins/redirect/) / [download](https://github.com/azurecurve/azrcrv-redirect/releases/latest/)
 * Remove Revisions - [details](https://development.azurecurve.co.uk/classicpress-plugins/remove-revisions/) / [download](https://github.com/azurecurve/azrcrv-remove-revisions/releases/latest/)
 * RSS Feed - [details](https://development.azurecurve.co.uk/classicpress-plugins/rss-feed/) / [download](https://github.com/azurecurve/azrcrv-rss-feed/releases/latest/)
 * RSS Suffix - [details](https://development.azurecurve.co.uk/classicpress-plugins/rss-suffix/) / [download](https://github.com/azurecurve/azrcrv-rss-suffix/releases/latest/)
 * Series Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/series-index/) / [download](https://github.com/azurecurve/azrcrv-series-index/releases/latest/)
 * Shortcodes in Comments - [details](https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/) / [download](https://github.com/azurecurve/azrcrv-shortcodes-in-comments/releases/latest/)
 * Shortcodes in Widgets - [details](https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/) / [download](https://github.com/azurecurve/azrcrv-shortcodes-in-widgets/releases/latest/)
 * Sidebar Login - [details](https://development.azurecurve.co.uk/classicpress-plugins/sidebar-login/) / [download](https://github.com/azurecurve/azrcrv-sidebar-login/releases/latest/)
 * SMTP - [details](https://development.azurecurve.co.uk/classicpress-plugins/smtp/) / [download](https://github.com/azurecurve/azrcrv-smtp/releases/latest/)
 * Snippets - [details](https://development.azurecurve.co.uk/classicpress-plugins/snippets/) / [download](https://github.com/azurecurve/azrcrv-snippets/releases/latest/)
 * Strong Password Generator - [details](https://development.azurecurve.co.uk/classicpress-plugins/strong-password-generator/) / [download](https://github.com/azurecurve/azrcrv-strong-password-generator/releases/latest/)
 * Tag Cloud - [details](https://development.azurecurve.co.uk/classicpress-plugins/tag-cloud/) / [download](https://github.com/azurecurve/azrcrv-tag-cloud/releases/latest/)
 * Taxonomy Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-index/) / [download](https://github.com/azurecurve/azrcrv-taxonomy-index/releases/latest/)
 * Taxonomy Order - [details](https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-order/) / [download](https://github.com/azurecurve/azrcrv-taxonomy-order/releases/latest/)
 * Theme Switcher - [details](https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/) / [download](https://github.com/azurecurve/azrcrv-theme-switcher/releases/latest/)
 * Timelines - [details](https://development.azurecurve.co.uk/classicpress-plugins/timelines/) / [download](https://github.com/azurecurve/azrcrv-timelines/releases/latest/)
 * Toggle Show/Hide - [details](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/) / [download](https://github.com/azurecurve/azrcrv-toggle-showhide/releases/latest/)
 * Update Admin Menu - [details](https://development.azurecurve.co.uk/classicpress-plugins/update-admin-menu/) / [download](https://github.com/azurecurve/azrcrv-update-admin-menu/releases/latest/)
 * URL Shortener - [details](https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/) / [download](https://github.com/azurecurve/azrcrv-url-shortener/releases/latest/)
 * Username Protection - [details](https://development.azurecurve.co.uk/classicpress-plugins/username-protection/) / [download](https://github.com/azurecurve/azrcrv-username-protection/releases/latest/)
 * Widget Announcements - [details](https://development.azurecurve.co.uk/classicpress-plugins/widget-announcements/) / [download](https://github.com/azurecurve/azrcrv-widget-announcements/releases/latest/)
 