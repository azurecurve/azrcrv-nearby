<?php
/*
	the [nearby] shortcode. Reads from the precomputed distance cache (see
	functions-locations.php) instead of calculating distance/bearing live -
	this is now a single indexed SELECT, not a self-joined trig calculation.
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

add_shortcode( 'nearby', __NAMESPACE__ . '\\render_shortcode' );

/**
 * Render the [nearby] shortcode.
 */
function render_shortcode( $atts ) {

	global $post;

	$options = get_settings();

	$args = shortcode_atts(
		array(
			'type'  => '',
			'title' => $options['toggle-title'],
		),
		$atts,
		'nearby'
	);

	$toggle_title = $args['title'];

	// Build the type allow-list. An explicit shortcode 'type' attribute
	// takes priority; failing that, the site-wide default-shortcode-types
	// option; failing that, no filter at all. Fixes a bug in the pre-4.0.0
	// plugin where a plain [nearby] (no explicit type, and no configured
	// default-shortcode-types) on a site that had *any* types defined
	// silently matched nothing, because the empty default was treated as
	// "match an empty string" rather than "no filter" - see PRD B2.
	if ( '' !== trim( (string) $args['type'] ) ) {
		$type_filter = array_filter( array_map( 'strtolower', array_map( 'trim', explode( ',', stripslashes( $args['type'] ) ) ) ) );
	} elseif ( ! empty( $options['default-shortcode-types'] ) ) {
		$type_filter = array_map( 'strtolower', $options['default-shortcode-types'] );
	} else {
		$type_filter = array();
	}

	$location = get_location_for_post( $post->ID );

	if ( empty( $location ) || '' === trim( (string) $location->co_ordinates ) ) {
		return '';
	}

	$unit         = ( 'km' === $options['unit-of-distance'] ) ? 'km' : 'miles';
	$max_distance = (int) $options['location-distance'];

	$nearby = get_nearby_for_post( $post->ID, $max_distance, $unit, $type_filter );

	$max_locations = isset( $options['maximum-locations'] ) ? (int) $options['maximum-locations'] : 50;

	$toggle_active = is_plugin_active( 'azrcrv-toggle-showhide/azrcrv-toggle-showhide.php' ) && $options['enable-toggle-showhide'];

	$table_class = $toggle_active ? 'azrcrv_n_toggle' : 'azrcrv_n';

	$attractions  = '<table class="' . esc_attr( $table_class ) . '">';
	$attractions .= '<colgroup><col style="width: 100%-100px;"><col style="width: 100px;"></colgroup>';
	$attractions .= '<tr><th class="azrcrv_n">' . esc_html__( 'Location', 'azrcrv-n' ) . '</th><th class="azrcrv_n">' . esc_html__( 'Distance', 'azrcrv-n' ) . '</th><th class="azrcrv_n">' . esc_html__( 'Direction', 'azrcrv-n' ) . '</th></tr>';

	$found = 0;

	foreach ( $nearby as $row ) {

		if ( $found >= $max_locations ) {
			break;
		}

		$attraction = get_post( $row->post_id );
		if ( ! $attraction ) {
			continue;
		}
		$link = get_permalink( $row->post_id );

		$country = '';
		if ( is_plugin_active( 'azrcrv-flags/azrcrv-flags.php' ) && ! empty( $options['enable-flags'] ) ) {
			if ( '' !== $row->country ) {
				$country = \azrcrv_f_flag(
					array(
						'id'    => $row->country,
						'width' => $options['flag-width'] . 'px',
					)
				);
			}
		}

		$direction = ( isset( $options['compass-type'] ) && '16' === (string) $options['compass-type'] )
			? get_compass_direction_sixteen( (float) $row->bearing )
			: get_compass_direction_thirtytwo( (float) $row->bearing );

		$timeline_signifier = get_timeline_signifier( $options, $link );

		$attractions .= '<tr>';
		$attractions .= '<td class="azrcrv_n"><a href="' . esc_url( $link ) . '">' . esc_html( $attraction->post_title ) . '</a> ' . $country . $timeline_signifier . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $country/$timeline_signifier are markup from trusted, plugin-controlled helper functions (Flags/Icons), not user input.
		$attractions .= '<td class="azrcrv_n">' . esc_html( $row->distance ) . ' ' . esc_html( $options['unit-of-distance'] ) . '</td>';
		$attractions .= '<td class="azrcrv_n">' . esc_html( $direction ) . '</td>';
		$attractions .= '</tr>';

		++$found;
	}

	if ( 0 === $found ) {
		$attractions .= '<tr><td colspan="3">' . esc_html__( 'No nearby attractions were found.', 'azrcrv-n' ) . '</td></tr>';
	}

	$attractions .= '</table>';

	if ( $toggle_active ) {
		return do_shortcode( '[toggle title="' . esc_attr( $toggle_title ) . '"]' . $attractions . '[/toggle]' );
	}

	return $attractions;
}

/**
 * Work out the timeline signifier markup for one nearby row, if Timelines
 * integration is enabled.
 */
function get_timeline_signifier( $options, $link ) {

	if ( ! is_plugin_active( 'azrcrv-timelines/azrcrv-timelines.php' ) || empty( $options['timeline-integration'] ) ) {
		return '';
	}

	global $wpdb;

	$signifier = '';
	if ( is_plugin_active( 'azrcrv-icons/azrcrv-icons.php' ) && ! empty( $options['icons-integration'] ) && '' !== $options['icon-visited'] ) {
		$signifier = \azrcrv_i_icon( array( $options['icon-visited'] ) );
	}
	if ( '' === $signifier ) {
		$signifier = $options['timeline-signifier'];
	}

	$timeline_exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(pm.meta_value) FROM {$wpdb->prefix}posts AS p
			INNER JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = p.ID
			WHERE p.post_status = 'publish' AND p.post_type = 'timeline-entry'
			AND pm.meta_key = 'timelines_metafields' AND pm.meta_value LIKE %s",
			'%' . $wpdb->esc_like( $link ) . '%'
		)
	);

	return ( $timeline_exists >= 1 ) ? '&nbsp;' . $signifier : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $signifier is markup from Icons' own helper, or a short admin-configured character, not user input.
}

/**
 * Get 16-point compass direction for a bearing.
 */
function get_compass_direction_sixteen( $bearing ) {

	$directions = array( 'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW' );

	$index = (int) round( $bearing / 22.5 ) % 16;

	return $directions[ $index ];
}

/**
 * Get 32-point compass direction for a bearing.
 */
function get_compass_direction_thirtytwo( $bearing ) {

	$directions = array(
		'N', 'NbE', 'NNE', 'NEbN', 'NE', 'NEbE', 'ENE', 'EbN',
		'E', 'EbS', 'ESE', 'SEbE', 'SE', 'SEbS', 'SSE', 'SbE',
		'S', 'SbW', 'SSW', 'SWbS', 'SW', 'SWbW', 'WSW', 'WbS',
		'W', 'WbN', 'WNW', 'NWbW', 'NW', 'NWbN', 'NNW', 'NbW',
	);

	$index = (int) round( $bearing / 11.25 ) % 32;

	return $directions[ $index ];
}
