<?php
/*
	admin and front-end script/style enqueue functions.

	Admin assets are only loaded on this plugin's own admin screen (or the
	shared azurecurve cross-plugin menu page). The front-end stylesheet is
	only enqueued on requests that actually contain the [nearby] shortcode,
	same as the pre-4.0.0 plugin.

	No jQuery dependency remains anywhere in this plugin - the admin tab UI
	uses the same vanilla-JS azrcrv-ui-tabs component (admin-standard.js)
	as Shortcodes in Comments.
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
 * Enqueue the flag/icon image picker's CSS/JS - shared by the settings
 * page (Icons picker on the Integration tab) and the post editor (Flags
 * picker in the co-ordinates metabox).
 */
function enqueue_picker_assets() {
	wp_enqueue_style(
		PLUGIN_HYPHEN . '-picker',
		plugins_url( 'assets/css/picker.css', PLUGIN_FILE ),
		array(),
		'4.1.3'
	);

	wp_enqueue_script(
		PLUGIN_HYPHEN . '-picker',
		plugins_url( 'assets/js/picker.js', PLUGIN_FILE ),
		array(),
		'4.1.3',
		true
	);
}

/**
 * Enqueue admin CSS/JS, only on this plugin's own admin page or the shared
 * azurecurve cross-plugin menu page.
 */
function enqueue_admin_assets( $hook ) {

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page identifier, not a state-changing action.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	if ( PLUGIN_HYPHEN !== $page && 'azrcrv-plugin-menu' !== $page ) {
		return;
	}

	enqueue_picker_assets();

	wp_enqueue_style(
		PLUGIN_HYPHEN . '-admin-standard',
		plugins_url( 'assets/css/admin-standard.css', PLUGIN_FILE ),
		array(),
		'4.1.3'
	);

	wp_enqueue_style(
		PLUGIN_HYPHEN . '-admin-pluginmenu',
		plugins_url( 'assets/css/admin-pluginmenu.css', PLUGIN_FILE ),
		array(),
		'4.1.3'
	);

	wp_enqueue_style(
		PLUGIN_HYPHEN . '-admin',
		plugins_url( 'assets/css/admin.css', PLUGIN_FILE ),
		array( PLUGIN_HYPHEN . '-admin-standard' ),
		'4.1.3'
	);

	wp_enqueue_script(
		PLUGIN_HYPHEN . '-admin-standard',
		plugins_url( 'assets/js/admin-standard.js', PLUGIN_FILE ),
		array(),
		'4.1.3',
		true
	);

	wp_enqueue_script(
		PLUGIN_HYPHEN . '-admin',
		plugins_url( 'assets/js/admin.js', PLUGIN_FILE ),
		array( PLUGIN_HYPHEN . '-admin-standard' ),
		'4.1.3',
		true
	);
}

/**
 * Enqueue the picker's CSS/JS on the page editor screen too, for the Flags
 * picker in the co-ordinates metabox - that metabox is only registered for
 * 'page' (see functions-metabox.php), so this only loads there, not on
 * every post type's editor.
 */
function enqueue_metabox_assets( $hook ) {

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	enqueue_picker_assets();
}

/**
 * Enqueue the front-end stylesheet, but only on requests where at least one
 * queried post actually contains the [nearby] shortcode - avoids loading
 * CSS on every front-end page view regardless of whether it's needed.
 *
 * Registered with add_filter() (in setup.php), not add_action() - 'the_posts'
 * is a filter (its return value replaces the queried posts), so a callback
 * hooked to it must return its argument. The pre-4.0.0 plugin registered
 * this same callback via add_action(), which happened to still work
 * because WordPress/ClassicPress store action and filter callbacks
 * identically internally, but add_filter() is the semantically correct
 * registration for a hook whose return value is used.
 */
function maybe_enqueue_frontend_style( $posts ) {

	if ( empty( $posts ) ) {
		return $posts;
	}

	foreach ( $posts as $post ) {
		if ( has_shortcode( $post->post_content, 'nearby' ) ) {
			wp_enqueue_style(
				PLUGIN_HYPHEN,
				plugins_url( 'assets/css/style.css', PLUGIN_FILE ),
				array(),
				'4.1.3'
			);
			break;
		}
	}

	return $posts;
}
