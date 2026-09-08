<?php
/*
	uninstall - removes this plugin's options and drops both of its custom
	tables.

	Fixes two gaps in the pre-4.0.0 plugin's uninstall.php: it only ever
	deleted the 'azrcrv-n' option (the settings), never 'azrcrv-n-types'
	(the admin-defined type taxonomy) or the 'azrcrv-n-db-version' marker,
	and never dropped the azrcrv_nearby table itself, let alone the new
	distance-cache table added in 4.0.0.
*/

// Check that code was called from ClassicPress with uninstallation constant declared.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Options to remove.
$options = array(
	'azrcrv-n',
	'azrcrv-n-types',
	'azrcrv-n-db-version',
);

/**
 * Drop this plugin's two custom tables on the current site.
 */
function azrcrv_n_uninstall_drop_tables() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}azrcrv_nearby_distances" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}azrcrv_nearby" );
}

// Remove from single site.
if ( ! is_multisite() ) {

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	azrcrv_n_uninstall_drop_tables();

} else {
	// Remove from multisite.
	global $wpdb;

	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		azrcrv_n_uninstall_drop_tables();
	}

	switch_to_blog( $original_blog_id );

	foreach ( $options as $option ) {
		delete_site_option( $option );
	}
}
