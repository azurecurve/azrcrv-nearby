<?php
/*
	menu functions - admin menu registration. Page rendering itself
	(display_admin_page()) lives in functions-settings.php, alongside the
	settings functions it displays.

	Reachable via the shared azurecurve cross-plugin menu (registered in
	azurecurve-menu-display.php), matching the pre-4.0.0 plugin's
	navigation and the pattern used by Shortcodes in Comments.
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
 * Add settings link on the Plugins list page. Registered against the
 * plugin-specific 'plugin_action_links_{basename}' filter (in setup.php),
 * not the generic 'plugin_action_links' filter that fires for every
 * plugin's row - fixes PRD B5.
 */
function add_plugin_action_link( $links, $file ) {

	$this_plugin = PLUGIN_SLUG . '/' . PLUGIN_SLUG . '.php';

	if ( $file === $this_plugin ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=' . PLUGIN_HYPHEN ) ) . '"><img src="' . esc_url( plugins_url( '../assets/images/logo.svg', __FILE__ ) ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-n' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add this plugin's entry under the shared azurecurve cross-plugin menu.
 * Text domain fixed to 'azrcrv-n' (was 'nearby' in the pre-4.0.0 plugin,
 * so these two strings could never be translated by the plugin's own
 * language files - fixes PRD B6).
 */
function create_admin_menu() {

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'Nearby Settings', 'azrcrv-n' ),
		esc_html__( 'Nearby', 'azrcrv-n' ),
		'manage_options',
		PLUGIN_HYPHEN,
		__NAMESPACE__ . '\\display_admin_page'
	);
}
