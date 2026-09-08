<?php
/*
	location storage and distance-cache functions.

	Distance/bearing between every pair of locations used to be calculated
	live, in MySQL, on every single front-end render of the [nearby]
	shortcode (a self-join of the locations table against itself, running a
	haversine distance formula and an ATAN2 bearing formula for every other
	row, every page view). With more than a handful of locations this got
	measurably slower, and the same result was being recalculated for every
	visitor until the underlying data changed.

	This file precomputes and stores that result instead, in
	{$wpdb->prefix}azrcrv_nearby_distances (one row per ORDERED pair of
	locations - bearing from A to B is not the same as bearing from B to A,
	so both directions are stored, avoiding any CASE/GREATEST juggling at
	read time). The shortcode (see functions-shortcode.php) then does a
	single indexed SELECT against that table - no trig at request time.

	The cache is kept in sync automatically:
	  - rebuild_all_distances()      run once on install/upgrade, and
	                                  available as an admin "Rebuild
	                                  Distance Cache" button for recovery.
	  - update_distances_for_post()  run after every save of the
	                                  co-ordinates metabox, so a location's
	                                  own row and its distances are always
	                                  updated together.
	  - delete_location_and_distances() run on permanent post deletion, so
	                                  the cache never serves stale data for
	                                  a removed location.
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
 * Post types excluded from the nearby calculation - ClassicPress' own
 * non-content types, not a content-type restriction. Any actual content
 * post type (page, post, or a custom post type) is eligible, since linking
 * only ever needs the post ID (get_permalink() resolves the right URL for
 * any post type on its own - no post_type column is needed in either
 * table).
 */
const EXCLUDED_POST_TYPES = array( 'attachment', 'revision', 'nav_menu_item' );

/**
 * Get the stored location row for a post, or null if it doesn't have one.
 */
function get_location_for_post( $post_id ) {
	global $wpdb;

	$table = get_locations_table_name();

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT type, country, co_ordinates, x_co_ordinate, y_co_ordinate FROM {$table} WHERE post_id = %d",
			$post_id
		)
	);
}

/**
 * Save (insert or update) a post's location, then refresh its distances.
 *
 * Co-ordinates being blank does NOT skip saving type/country - an admin
 * choosing a flag or type before they've filled in co-ordinates (a very
 * normal order to fill the metabox in) must not have that selection
 * silently discarded. The row is only removed entirely once type, country
 * AND co-ordinates are all blank - i.e. there is genuinely nothing left to
 * keep. A row with blank co-ordinates simply never appears in any
 * distance-cache rows, since build_distance_calc_sql() already requires
 * LENGTH(co_ordinates) > 0 on both sides of a pair - so calling
 * update_distances_for_post() unconditionally below is self-correcting,
 * with no special-casing needed here.
 */
function save_location_for_post( $post_id, $type, $country, $coordinates ) {

	global $wpdb;

	$coordinates = trim( $coordinates );

	if ( '' === $type && '' === $country && '' === $coordinates ) {
		delete_location_and_distances( $post_id );
		return;
	}

	$table  = get_locations_table_name();
	$exists = get_location_for_post( $post_id );

	if ( $exists ) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table}
				SET type = %s, country = %s, co_ordinates = %s,
					x_co_ordinate = TRIM(SUBSTRING_INDEX( %s, ',', 1)),
					y_co_ordinate = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX( %s, ',', 2), ',', -1))
				WHERE post_id = %d",
				$type,
				$country,
				$coordinates,
				$coordinates,
				$coordinates,
				$post_id
			)
		);
	} else {
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} ( post_id, type, country, co_ordinates, x_co_ordinate, y_co_ordinate )
				VALUES ( %d, %s, %s, %s,
					TRIM(SUBSTRING_INDEX( %s, ',', 1)),
					TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX( %s, ',', 2), ',', -1)) )",
				$post_id,
				$type,
				$country,
				$coordinates,
				$coordinates,
				$coordinates
			)
		);
	}

	update_distances_for_post( $post_id );
}

/**
 * Remove a post's location row and every cached distance referencing it
 * (in either direction). Called when a location's co-ordinates are cleared,
 * and on permanent post deletion.
 */
function delete_location_and_distances( $post_id ) {

	global $wpdb;

	$locations_table = get_locations_table_name();
	$distances_table  = get_distances_table_name();

	$wpdb->query( $wpdb->prepare( "DELETE FROM {$locations_table} WHERE post_id = %d", $post_id ) );
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$distances_table} WHERE post_id_a = %d OR post_id_b = %d",
			$post_id,
			$post_id
		)
	);
}
add_action( 'before_delete_post', __NAMESPACE__ . '\\delete_location_and_distances' );

/**
 * Build the excluded-post-types SQL fragment once, for reuse in both the
 * rebuild and per-post update queries.
 */
function get_excluded_post_types_sql() {
	global $wpdb;

	$placeholders = implode( ',', array_fill( 0, count( EXCLUDED_POST_TYPES ), '%s' ) );

	return $wpdb->prepare( "post_type NOT IN ({$placeholders})", EXCLUDED_POST_TYPES ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built via prepare() above, safe to inline.
}

/**
 * The shared SELECT used by both rebuild_all_distances() and
 * update_distances_for_post() to (re)compute ordered-pair distance/bearing
 * rows from the locations table. $extra_where, if given, is ANDed onto the
 * WHERE clause (used to restrict the rebuild to pairs touching one post).
 *
 * The acos() argument is clamped to [-1, 1] (LEAST/GREATEST) because
 * floating-point rounding can otherwise push it fractionally outside that
 * domain for near-identical co-ordinates, which would make MySQL's acos()
 * return NULL and silently drop that pair rather than reporting distance 0.
 */
function build_distance_calc_sql( $extra_where = '' ) {

	global $wpdb;

	$locations_table = get_locations_table_name();
	$excluded_types   = get_excluded_post_types_sql();

	$where = "LENGTH(A.co_ordinates) > 0 AND LENGTH(B.co_ordinates) > 0";
	if ( '' !== $extra_where ) {
		$where .= ' AND ' . $extra_where;
	}

	return "SELECT post_id_a, post_id_b, distance_miles, distance_miles * 1.609344 AS distance_km, bearing
		FROM (
			SELECT
				A.post_id AS post_id_a,
				B.post_id AS post_id_b,
				ROUND(
					(
						ACOS(
							LEAST( 1, GREATEST( -1,
								SIN(RADIANS(A.x_co_ordinate)) * SIN(RADIANS(B.x_co_ordinate))
								+ COS(RADIANS(A.x_co_ordinate)) * COS(RADIANS(B.x_co_ordinate))
									* COS(RADIANS(A.y_co_ordinate - B.y_co_ordinate))
							) )
						) * 180 / PI()
					) * 60 * 1.1515, 2
				) AS distance_miles,
				MOD(
					360 + DEGREES(
						ATAN2(
							SIN(RADIANS(B.y_co_ordinate - A.y_co_ordinate)) * COS(RADIANS(B.x_co_ordinate)),
							COS(RADIANS(A.x_co_ordinate)) * SIN(RADIANS(B.x_co_ordinate))
								- SIN(RADIANS(A.x_co_ordinate)) * COS(RADIANS(B.x_co_ordinate))
									* COS(RADIANS(B.y_co_ordinate - A.y_co_ordinate))
						)
					), 360
				) AS bearing
			FROM {$locations_table} AS A
			INNER JOIN {$wpdb->posts} AS P ON P.ID = A.post_id AND P.post_status = 'publish' AND P.{$excluded_types}
			INNER JOIN {$locations_table} AS B ON B.post_id <> A.post_id
			INNER JOIN {$wpdb->posts} AS PO ON PO.ID = B.post_id AND PO.post_status = 'publish' AND PO.{$excluded_types}
			WHERE {$where}
		) AS calc"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $excluded_types is pre-prepared, $extra_where is built internally from %d-prepared fragments only.
}

/**
 * Rebuild the entire distance cache from scratch. Synchronous by design -
 * intended to be run rarely (on upgrade, or via the admin "Rebuild Distance
 * Cache" button), not on every request.
 */
function rebuild_all_distances() {

	global $wpdb;

	$distances_table = get_distances_table_name();

	$wpdb->query( "TRUNCATE TABLE {$distances_table}" );

	$select_sql = build_distance_calc_sql();

	$wpdb->query(
		"INSERT INTO {$distances_table} (post_id_a, post_id_b, distance_miles, distance_km, bearing) {$select_sql}" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- built via build_distance_calc_sql(), which prepares its own dynamic fragment.
	);
}

/**
 * Recompute cached distances for just one post (both directions), rather
 * than the whole table - used after every metabox save, so adding or
 * moving a single location doesn't require a full rebuild.
 */
function update_distances_for_post( $post_id ) {

	global $wpdb;

	$distances_table = get_distances_table_name();

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$distances_table} WHERE post_id_a = %d OR post_id_b = %d",
			$post_id,
			$post_id
		)
	);

	$extra_where = $wpdb->prepare( '(A.post_id = %d OR B.post_id = %d)', $post_id, $post_id );
	$select_sql  = build_distance_calc_sql( $extra_where );

	$wpdb->query(
		"INSERT INTO {$distances_table} (post_id_a, post_id_b, distance_miles, distance_km, bearing) {$select_sql}" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $extra_where is %d-prepared above; rest as build_distance_calc_sql().
	);
}

/**
 * Read the cached nearby locations for a post, filtered by maximum distance
 * (in the given unit) and, optionally, by type. Returns an array of rows
 * (post_id, distance, bearing, type, country) sorted nearest-first.
 *
 * Fixes a bug in the pre-4.0.0 plugin where a plain [nearby] shortcode (no
 * explicit type attribute) on a site that had defined types, but not
 * configured a site-wide default-shortcode-types, silently matched nothing
 * - see PRD B2. An empty $type_filter here always means "no type filter",
 * never "match nothing".
 */
function get_nearby_for_post( $post_id, $max_distance, $unit, array $type_filter = array() ) {

	global $wpdb;

	$distances_table = get_distances_table_name();
	$locations_table  = get_locations_table_name();

	$distance_column = ( 'km' === $unit ) ? 'distance_km' : 'distance_miles';

	$sql = "SELECT D.post_id_b AS post_id, D.{$distance_column} AS distance, D.bearing, L.type, L.country
		FROM {$distances_table} AS D
		INNER JOIN {$locations_table} AS L ON L.post_id = D.post_id_b
		WHERE D.post_id_a = %d
		AND D.{$distance_column} <= %d
		ORDER BY D.{$distance_column} ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $distance_column is from the fixed two-value whitelist above, not user input.

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $post_id, $max_distance ) );

	if ( empty( $type_filter ) ) {
		return $rows;
	}

	return array_values(
		array_filter(
			$rows,
			static function ( $row ) use ( $type_filter ) {
				return in_array( strtolower( $row->type ), $type_filter, true );
			}
		)
	);
}
