<?php
/*
	settings functions - two options are used:

	  azrcrv-n        plugin settings (see get_builtin_settings() below).
	  azrcrv-n-types  admin-defined "type" taxonomy (e.g. "Distillery",
	                  "Attraction"), stored as [ type_id => type_name ].

	Both option names are unchanged from the pre-4.0.0 plugin, so upgrading
	keeps existing configuration with no migration step.
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
 * Render the admin page (Default Settings/Integration/Types, in tabs).
 */
function display_admin_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-n' ) );
	}

	echo '<div class="wrap ' . esc_attr( PLUGIN_HYPHEN ) . '-wrap">';
	echo '<h1>';
		echo '<a href="' . esc_url_raw( DEVELOPER_RAW_LINK ) . '"><img src="' . esc_url_raw( plugins_url( '../assets/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="' . esc_attr( DEVELOPER_NAME ) . '" /></a>';
		echo esc_html( get_admin_page_title() );
	echo '</h1>';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag, not a state-changing action.
	if ( isset( $_GET[ PLUGIN_HYPHEN . '-message' ] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		render_admin_notice( sanitize_key( wp_unslash( $_GET[ PLUGIN_HYPHEN . '-message' ] ) ) );
	}

	require_once __DIR__ . '/tabs-output.php';

	echo '</div>';
}

/**
 * Show a dismissible admin notice for a given message key, set via a
 * redirect query arg after a save/add/delete/rebuild action.
 */
function render_admin_notice( $message_key ) {

	$messages = array(
		'settings-saved'     => array( 'success', __( 'Settings have been saved.', 'azrcrv-n' ) ),
		'invalid-nonce'      => array( 'error', __( 'Security check failed - please try again.', 'azrcrv-n' ) ),
		'type-added'         => array( 'success', __( 'New type has been successfully added.', 'azrcrv-n' ) ),
		'type-exists'        => array( 'error', __( 'Type already exists.', 'azrcrv-n' ) ),
		'type-deleted'       => array( 'success', __( 'Type has been successfully deleted.', 'azrcrv-n' ) ),
		'distances-rebuilt'  => array( 'success', __( 'Distance cache has been rebuilt.', 'azrcrv-n' ) ),
	);

	if ( ! isset( $messages[ $message_key ] ) ) {
		return;
	}

	list( $type, $text ) = $messages[ $message_key ];
	$css_class            = 'success' === $type ? 'notice-success' : 'notice-error';

	echo '<div class="notice ' . esc_attr( $css_class ) . ' is-dismissible"><p><strong>' . esc_html( $text ) . '</strong></p></div>';
}

/**
 * Build a redirect URL back to the admin page with a status message,
 * optionally preserving which tab was active.
 */
function redirect_with_message( $message_key, $extra_args = array() ) {
	$args = array_merge(
		array(
			'page'                      => PLUGIN_HYPHEN,
			PLUGIN_HYPHEN . '-message' => $message_key,
		),
		$extra_args
	);
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
}

/**
 * The plugin's built-in default settings - unchanged from the pre-4.0.0
 * plugin, so upgrading keeps the same defaults.
 */
function get_builtin_settings() {
	return array(
		'maximum-locations'       => 20,
		'location-distance'       => 200,
		'compass-type'            => 16,
		'unit-of-distance'        => 'miles',
		'enable-flags'            => 0,
		'flag-width'              => 16,
		'enable-toggle-showhide'  => 0,
		'toggle-title'            => 'Nearby Locations',
		'timeline-integration'    => 0,
		'timeline-signifier'      => '*',
		'icons-integration'       => 0,
		'icon-visited'            => '',
		'default-type'            => '',
		'default-shortcode-types' => array(),
	);
}

/**
 * Get the saved settings, merged over the built-in defaults so every key is
 * always present even for a fresh install - fixes a bug in the pre-4.0.0
 * plugin (PRD B4) where the save handler read get_option('azrcrv-n')
 * directly (no default array); on a fresh install that returns false, and
 * $options[$key] = ... on false is a PHP 8+ fatal-adjacent warning.
 */
function get_settings() {
	$stored = get_option( SETTINGS_OPTION_NAME, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return wp_parse_args( $stored, get_builtin_settings() );
}

/**
 * Persist the settings.
 */
function save_settings( $settings ) {
	update_option( SETTINGS_OPTION_NAME, $settings );
}

/**
 * Get the saved types ([ type_id => type_name ]), sorted by key.
 */
function get_types() {
	$types = get_option( TYPES_OPTION_NAME, array() );

	if ( ! is_array( $types ) ) {
		$types = array();
	}

	ksort( $types );

	return $types;
}

/**
 * Persist the types.
 */
function save_types( $types ) {
	update_option( TYPES_OPTION_NAME, $types );
}

/**
 * Build a sanitized settings array from a submitted settings form ($_POST),
 * merged over the current settings so an unrelated tab's fields (only one
 * tab's <form> is submitted at a time) are never lost.
 */
function sanitize_settings_from_post( $post_data ) {

	$settings = get_settings();

	if ( isset( $post_data['maximum-locations'] ) ) {
		$settings['maximum-locations'] = max( 1, (int) $post_data['maximum-locations'] );
	}
	if ( isset( $post_data['location-distance'] ) ) {
		$settings['location-distance'] = max( 1, (int) $post_data['location-distance'] );
	}
	if ( isset( $post_data['unit-of-distance'] ) && in_array( $post_data['unit-of-distance'], array( 'km', 'miles' ), true ) ) {
		$settings['unit-of-distance'] = $post_data['unit-of-distance'];
	}
	if ( isset( $post_data['compass-type'] ) && in_array( (string) $post_data['compass-type'], array( '16', '32' ), true ) ) {
		$settings['compass-type'] = $post_data['compass-type'];
	}
	$settings['enable-flags'] = isset( $post_data['enable-flags'] ) ? 1 : 0;
	if ( isset( $post_data['flag-width'] ) ) {
		$settings['flag-width'] = max( 1, (int) $post_data['flag-width'] );
	}
	$settings['enable-toggle-showhide'] = isset( $post_data['enable-toggle-showhide'] ) ? 1 : 0;
	if ( isset( $post_data['toggle-title'] ) ) {
		$settings['toggle-title'] = sanitize_text_field( $post_data['toggle-title'] );
	}
	$settings['timeline-integration'] = isset( $post_data['timeline-integration'] ) ? 1 : 0;
	if ( isset( $post_data['timeline-signifier'] ) ) {
		$settings['timeline-signifier'] = sanitize_text_field( $post_data['timeline-signifier'] );
	}
	$settings['icons-integration'] = isset( $post_data['icons-integration'] ) ? 1 : 0;
	if ( isset( $post_data['icon-visited'] ) ) {
		$settings['icon-visited'] = sanitize_text_field( $post_data['icon-visited'] );
	}
	if ( isset( $post_data['default-type'] ) ) {
		$settings['default-type'] = sanitize_text_field( $post_data['default-type'] );
	}
	if ( isset( $post_data['default-shortcode-types'] ) && is_array( $post_data['default-shortcode-types'] ) ) {
		$settings['default-shortcode-types'] = array_map( 'sanitize_text_field', $post_data['default-shortcode-types'] );
	}

	return $settings;
}

/**
 * Handle the "Save Changes" form on the Default Settings/Integration tabs.
 */
function handle_save_settings() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action.', 'azrcrv-n' ) );
	}

	if ( ! isset( $_POST[ PLUGIN_HYPHEN . '-nonce' ] ) || ! check_admin_referer( PLUGIN_HYPHEN, PLUGIN_HYPHEN . '-nonce' ) ) {
		redirect_with_message( 'invalid-nonce' );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already verified above.
	$settings = sanitize_settings_from_post( wp_unslash( $_POST ) );

	save_settings( $settings );

	redirect_with_message( 'settings-saved' );
}
add_action( 'admin_post_' . PLUGIN_UNDERSCORE . '_save_options', __NAMESPACE__ . '\\handle_save_settings' );

/**
 * Handle the "Add new type" form on the Types tab.
 */
function handle_add_type() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action.', 'azrcrv-n' ) );
	}

	if ( ! isset( $_POST[ PLUGIN_HYPHEN . '-nonce-add-type' ] ) || ! check_admin_referer( PLUGIN_HYPHEN . '-add-type', PLUGIN_HYPHEN . '-nonce-add-type' ) ) {
		redirect_with_message( 'invalid-nonce' );
	}

	$types = get_types();

	$new_type_name = isset( $_POST['new-type'] ) ? sanitize_text_field( wp_unslash( $_POST['new-type'] ) ) : '';
	$new_type_id   = strtolower( $new_type_name );

	if ( '' === $new_type_id ) {
		redirect_with_message( 'type-exists', array( 'types' => '1' ) );
	}

	if ( isset( $types[ $new_type_id ] ) ) {
		redirect_with_message( 'type-exists', array( 'types' => '1' ) );
	}

	$types[ $new_type_id ] = $new_type_name;
	save_types( $types );

	redirect_with_message( 'type-added', array( 'types' => '1' ) );
}
add_action( 'admin_post_' . PLUGIN_UNDERSCORE . '_add_type', __NAMESPACE__ . '\\handle_add_type' );

/**
 * Handle a "Delete" form on the Types tab.
 */
function handle_delete_type() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action.', 'azrcrv-n' ) );
	}

	if ( ! isset( $_POST[ PLUGIN_HYPHEN . '-nonce-delete-type' ] ) || ! check_admin_referer( PLUGIN_HYPHEN . '-delete-type', PLUGIN_HYPHEN . '-nonce-delete-type' ) ) {
		redirect_with_message( 'invalid-nonce' );
	}

	$types = get_types();

	if ( isset( $_POST['delete-type'] ) ) {
		$type_id = strtolower( sanitize_text_field( wp_unslash( $_POST['delete-type'] ) ) );
		unset( $types[ $type_id ] );
		save_types( $types );
	}

	redirect_with_message( 'type-deleted', array( 'types' => '1' ) );
}
add_action( 'admin_post_' . PLUGIN_UNDERSCORE . '_delete_type', __NAMESPACE__ . '\\handle_delete_type' );

/**
 * Handle the "Rebuild Distance Cache" button on the Default Settings tab.
 * Synchronous by design (see PRD s4) - runs to completion within this
 * request.
 */
function handle_rebuild_distances() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action.', 'azrcrv-n' ) );
	}

	if ( ! isset( $_POST[ PLUGIN_HYPHEN . '-nonce-rebuild-distances' ] ) || ! check_admin_referer( PLUGIN_HYPHEN . '-rebuild-distances', PLUGIN_HYPHEN . '-nonce-rebuild-distances' ) ) {
		redirect_with_message( 'invalid-nonce' );
	}

	rebuild_all_distances();

	redirect_with_message( 'distances-rebuilt' );
}
add_action( 'admin_post_' . PLUGIN_UNDERSCORE . '_rebuild_distances', __NAMESPACE__ . '\\handle_rebuild_distances' );

/**
 * How many pages/rows currently exist in how many "in use" counts for a
 * type - used by the Types tab to work out whether a type can be deleted.
 * Fixes PRD B3: the pre-4.0.0 plugin's equivalent query referenced
 * `$wpdb->azrcrv_nearby`, which isn't a real wpdb property (the table is
 * `$wpdb->prefix . 'azrcrv_nearby'`), so the query always failed and this
 * check silently did nothing - types in use could be deleted from the
 * admin UI without warning.
 */
function get_type_usage_count( $type_id ) {

	global $wpdb;

	$table = get_locations_table_name();

	return (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE type = %s", $type_id ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $table is a fixed, internally-built name, not user input.
	);
}
