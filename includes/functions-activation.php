<?php
/*
	activation / deactivation functions - creates/upgrades the two custom
	tables this plugin uses:

	  - {$wpdb->prefix}azrcrv_nearby           one row per post with
	                                            co-ordinates (unchanged shape
	                                            from the pre-4.0.0 plugin).
	  - {$wpdb->prefix}azrcrv_nearby_distances one row per ORDERED pair of
	                                            locations, holding a
	                                            precomputed distance/bearing
	                                            so the [nearby] shortcode
	                                            never has to run trig
	                                            functions at render time.

	Runs on 'plugins_loaded', guarded by a DB_VERSION option check, rather
	than only on the activation hook - Update Manager upgrades don't
	re-trigger register_activation_hook(), so this is the only reliable
	place to pick up schema changes shipped in a new version.
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
 * Plugin activation. Table creation itself happens in maybe_install() on
 * plugins_loaded (see above) so it also runs on upgrade, not just on this
 * hook - kept as a real function so future activation-only setup has
 * somewhere to go.
 */
function activate_plugin() {
	// Nothing to do here - see maybe_install().
}

/**
 * Plugin deactivation. Deliberately does NOT delete any data - deactivating
 * a plugin should not lose the admin's configuration or location data; that
 * only happens on uninstall (see uninstall.php).
 */
function deactivate_plugin() {
	// Nothing to do yet.
}

/**
 * Get the locations table name.
 */
function get_locations_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'azrcrv_nearby';
}

/**
 * Get the distance-cache table name.
 */
function get_distances_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'azrcrv_nearby_distances';
}

/**
 * Create/upgrade the tables if DB_VERSION has changed, migrate legacy
 * postmeta data on a genuinely fresh install, and (re)build the distance
 * cache whenever the schema changes so upgrading never leaves it empty.
 */
function maybe_install() {

	global $wpdb;

	$installed_ver = get_option( PLUGIN_HYPHEN . '-db-version' );

	if ( $installed_ver === DB_VERSION ) {
		return;
	}

	$charset_collate = $wpdb->get_charset_collate();
	$locations_table  = get_locations_table_name();
	$distances_table  = get_distances_table_name();

	$sql = "CREATE TABLE {$locations_table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id BIGINT(20) NOT NULL,
		type VARCHAR(100) NOT NULL,
		country VARCHAR(200) NOT NULL,
		co_ordinates VARCHAR(100) NOT NULL,
		x_co_ordinate DOUBLE NOT NULL,
		y_co_ordinate DOUBLE NOT NULL,
		PRIMARY KEY (id) USING BTREE,
		INDEX post_id (post_id) USING BTREE,
		INDEX type (type (100)) USING BTREE
	) $charset_collate;
	CREATE TABLE {$distances_table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id_a BIGINT(20) NOT NULL,
		post_id_b BIGINT(20) NOT NULL,
		distance_miles DECIMAL(10,2) NOT NULL,
		distance_km DECIMAL(10,2) NOT NULL,
		bearing DECIMAL(6,2) NOT NULL,
		PRIMARY KEY (id) USING BTREE,
		UNIQUE KEY pair (post_id_a, post_id_b) USING BTREE,
		INDEX post_id_a (post_id_a) USING BTREE
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// Fresh install (or upgrade from a version predating the locations
	// table): migrate any legacy postmeta values across, exactly as the
	// pre-4.0.0 plugin's one-off v3.0.0 migration did.
	$num_rows = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$locations_table}" );
	if ( 0 === $num_rows ) {
		$wpdb->query(
			"INSERT INTO {$locations_table} (post_id, type, country, co_ordinates, x_co_ordinate, y_co_ordinate)
			SELECT
				P.ID,
				PM_TYPE.meta_value,
				PM_COUNTRY.meta_value,
				PM_CO_ORDINATES.meta_value,
				TRIM(SUBSTRING_INDEX(PM_CO_ORDINATES.meta_value, ',', 1)),
				TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(PM_CO_ORDINATES.meta_value, ',', 2), ',', -1))
			FROM {$wpdb->prefix}posts AS P
			LEFT JOIN {$wpdb->prefix}postmeta AS PM_TYPE
				ON PM_TYPE.post_id = P.ID AND PM_TYPE.meta_key = '_azrcrv_n_type'
			LEFT JOIN {$wpdb->prefix}postmeta AS PM_COUNTRY
				ON PM_COUNTRY.post_id = P.ID AND PM_COUNTRY.meta_key = '_azrcrv_n_country'
			LEFT JOIN {$wpdb->prefix}postmeta AS PM_CO_ORDINATES
				ON PM_CO_ORDINATES.post_id = P.ID AND PM_CO_ORDINATES.meta_key = '_azrcrv_n_coordinates'
			WHERE P.post_status = 'publish'
			AND (
				PM_TYPE.post_id IS NOT NULL
				OR PM_COUNTRY.post_id IS NOT NULL
				OR PM_CO_ORDINATES.post_id IS NOT NULL
			)"
		);
	}

	// Whenever the schema changes (fresh install or upgrade), rebuild the
	// distance cache from whatever is now in the locations table, so it's
	// never left empty after an upgrade.
	rebuild_all_distances();

	update_option( PLUGIN_HYPHEN . '-db-version', DB_VERSION );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\maybe_install' );
