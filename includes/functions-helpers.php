<?php
/*
	small shared helpers used by more than one of the other includes files.
*/

/**
 * Declare the Namespace.
 */
namespace azurecurve\Nearby;

/**
 * Prevent direct access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Check if a plugin is active by reading the 'active_plugins' option
 * directly, rather than calling core's is_plugin_active() - that function
 * lives in wp-admin/includes/plugin.php, which isn't loaded on front-end
 * requests, so calling it directly from the [nearby] shortcode (a
 * front-end code path) would fatal. Kept as its own function (rather than
 * conditionally require-ing the admin file) to avoid loading unrelated
 * admin code on every front-end page view.
 */
function is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}
