<?php
/*
	setup - registration of activation/deactivation hooks, actions and
	filters. Hooks registered directly in the file that owns the
	functions they call (metabox save/render, shortcode, admin_post
	handlers, before_delete_post) are NOT repeated here - only the
	cross-cutting ones are.
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

// Activation / deactivation.
register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\activate_plugin' );
register_deactivation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\deactivate_plugin' );
register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\clear_picker_caches' );
register_deactivation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\clear_picker_caches' );

// Admin menu.
add_action( 'admin_menu', __NAMESPACE__ . '\\create_admin_menu' );
add_filter( 'plugin_action_links_' . plugin_basename( PLUGIN_FILE ), __NAMESPACE__ . '\\add_plugin_action_link', 10, 2 );

// Update Manager: tell it where to find this plugin's own icon/banner
// images (assets/images) rather than falling back to a generic default.
$plugin_slug_for_um = plugin_basename( trim( PLUGIN_FILE ) );
add_filter( 'codepotent_update_manager_' . $plugin_slug_for_um . '_image_path', __NAMESPACE__ . '\\custom_image_path' );
add_filter( 'codepotent_update_manager_' . $plugin_slug_for_um . '_image_url', __NAMESPACE__ . '\\custom_image_url' );

// Admin assets.
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_metabox_assets' );

// Front-end asset, only on requests containing the [nearby] shortcode.
add_filter( 'the_posts', __NAMESPACE__ . '\\maybe_enqueue_frontend_style' );

// Co-ordinates/type/country metabox.
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\create_details_metabox' );
add_action( 'save_post', __NAMESPACE__ . '\\save_details_metabox', 10, 2 );

// Language.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_languages' );
