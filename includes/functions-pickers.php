<?php
/*
	image picker functions - the Flags and Icons integrations previously
	either used a plain <select> (Flags, populated via the Flags plugin's
	own azrcrv_f_get_flags() function) or a free-text field the admin had
	to type an icon id into by hand (Icons' "icon-visited" setting).

	Both are replaced here with a visual picker: a scrollable grid of the
	actual flag/icon images, built by scanning the sibling plugin's own
	assets directory directly -
	  - Flags:  {plugin_dir}/azrcrv-flags/assets/flags/*.svg
	  - Icons:  {plugin_dir}/azrcrv-icons/assets/icons/{*.svg,*.png}
	rather than depending on either plugin exposing (or continuing to
	expose) a particular helper function for this. Icons can be PNG or
	SVG; where a custom icon exists as both, the SVG is preferred - this
	matches how the Icons plugin itself already resolves that case for
	the [icon] shortcode.

	Directory scans are cached in transients (see get_available_flags()/
	get_available_icons()) since the Icons plugin alone ships 1,001 icons,
	and this picker can render on every page/post edit screen.
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

const FLAGS_CACHE_KEY = PLUGIN_HYPHEN . '-available-flags';
const ICONS_CACHE_KEY = PLUGIN_HYPHEN . '-available-icons';

/**
 * Path and URL of the Flags plugin's flag images directory.
 */
function get_flags_directory() {
	return array(
		'path' => WP_PLUGIN_DIR . '/azrcrv-flags/assets/flags/',
		'url'  => plugins_url( 'assets/flags/', WP_PLUGIN_DIR . '/azrcrv-flags/azrcrv-flags.php' ),
	);
}

/**
 * Path and URL of the Icons plugin's icon images directory.
 */
function get_icons_directory() {
	return array(
		'path' => WP_PLUGIN_DIR . '/azrcrv-icons/assets/icons/',
		'url'  => plugins_url( 'assets/icons/', WP_PLUGIN_DIR . '/azrcrv-icons/azrcrv-icons.php' ),
	);
}

/**
 * Scan a directory for image files with the given extensions, returning
 * [ id => [ 'name' => label, 'url' => image url ] ], keyed by filename
 * (without extension) and sorted alphabetically.
 *
 * $extensions order matters when a file exists with more than one
 * extension under the same id (e.g. flag.svg and flag.png) - whichever
 * extension is listed first wins, since the first match seen for a given
 * id is kept.
 */
function scan_directory_for_images( $dir_path, $dir_url, array $extensions ) {

	$items = array();

	if ( ! is_dir( $dir_path ) ) {
		return $items;
	}

	foreach ( $extensions as $extension ) {
		$files = glob( $dir_path . '*.' . $extension );
		if ( ! $files ) {
			continue;
		}
		foreach ( $files as $file ) {
			$id = basename( $file, '.' . $extension );
			if ( isset( $items[ $id ] ) ) {
				continue;
			}
			$items[ $id ] = array(
				'name' => ucwords( str_replace( array( '-', '_' ), ' ', $id ) ),
				'url'  => $dir_url . rawurlencode( basename( $file ) ),
			);
		}
	}

	ksort( $items );

	return $items;
}

/**
 * Get the available flags (id => [name, url]), cached for 12 hours.
 */
function get_available_flags() {

	$cached = get_transient( FLAGS_CACHE_KEY );
	if ( false !== $cached ) {
		return $cached;
	}

	$dir   = get_flags_directory();
	$flags = scan_directory_for_images( $dir['path'], $dir['url'], array( 'svg' ) );

	set_transient( FLAGS_CACHE_KEY, $flags, 12 * HOUR_IN_SECONDS );

	return $flags;
}

/**
 * Get the available icons (id => [name, url]), cached for 12 hours. SVG is
 * preferred over PNG for a given id, matching the Icons plugin's own
 * resolution order for custom icons.
 */
function get_available_icons() {

	$cached = get_transient( ICONS_CACHE_KEY );
	if ( false !== $cached ) {
		return $cached;
	}

	$dir   = get_icons_directory();
	$icons = scan_directory_for_images( $dir['path'], $dir['url'], array( 'svg', 'png' ) );

	set_transient( ICONS_CACHE_KEY, $icons, 12 * HOUR_IN_SECONDS );

	return $icons;
}

/**
 * Clear both directory-scan caches - run on this plugin's own activation
 * (in case flags/icons were added while it was inactive) and deactivation
 * (so a stale list is never shown after reactivating).
 */
function clear_picker_caches() {
	delete_transient( FLAGS_CACHE_KEY );
	delete_transient( ICONS_CACHE_KEY );
}

/**
 * Render a visual picker: a hidden input holding the selected id, plus a
 * scrollable grid of clickable image items (with a live filter field when
 * there are more than a handful of items, e.g. the Icons picker). Selecting
 * an item sets the hidden input's value - see assets/js/picker.js.
 */
function render_image_picker( $picker_id, $input_name, $current_value, array $items, $empty_label ) {

	echo '<div class="azrcrv-n-picker" data-target="' . esc_attr( $picker_id ) . '">';
	echo '<input type="hidden" name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $picker_id ) . '" value="' . esc_attr( $current_value ) . '" />';

	if ( count( $items ) > 12 ) {
		echo '<p>';
		echo '<label class="screen-reader-text" for="' . esc_attr( $picker_id ) . '-filter">' . esc_html__( 'Filter', 'azrcrv-n' ) . '</label>';
		echo '<input type="text" class="regular-text azrcrv-n-picker-filter" id="' . esc_attr( $picker_id ) . '-filter" placeholder="' . esc_attr__( 'Type to filter…', 'azrcrv-n' ) . '" />';
		echo '</p>';
	}

	// Pull the currently-selected item (if any) out of its alphabetical
	// position and render it first, so the current selection is visible at
	// a glance without having to scroll or filter to find it.
	$selected_item = null;
	if ( '' !== $current_value && isset( $items[ $current_value ] ) ) {
		$selected_item = $items[ $current_value ];
		unset( $items[ $current_value ] );
	}

	echo '<div class="azrcrv-n-picker-grid" role="radiogroup">';

	if ( null !== $selected_item ) {
		echo '<div class="azrcrv-n-picker-item is-selected" role="radio" aria-checked="true" tabindex="0" data-value="' . esc_attr( $current_value ) . '" data-name="' . esc_attr( $selected_item['name'] ) . '">';
		echo '<img src="' . esc_url( $selected_item['url'] ) . '" alt="" loading="lazy" />';
		echo '<span class="azrcrv-n-picker-label">' . esc_html( $selected_item['name'] ) . '</span>';
		echo '</div>';
	}

	$none_selected = ( '' === $current_value );
	echo '<div class="azrcrv-n-picker-item azrcrv-n-picker-item-none' . ( $none_selected ? ' is-selected' : '' ) . '" role="radio" aria-checked="' . ( $none_selected ? 'true' : 'false' ) . '" tabindex="0" data-value="" data-name="' . esc_attr( $empty_label ) . '">';
	echo '<span class="azrcrv-n-picker-none-glyph" aria-hidden="true">&#8709;</span>';
	echo '<span class="azrcrv-n-picker-label">' . esc_html( $empty_label ) . '</span>';
	echo '</div>';

	foreach ( $items as $item_id => $item ) {
		echo '<div class="azrcrv-n-picker-item" role="radio" aria-checked="false" tabindex="0" data-value="' . esc_attr( $item_id ) . '" data-name="' . esc_attr( $item['name'] ) . '">';
		echo '<img src="' . esc_url( $item['url'] ) . '" alt="" loading="lazy" />';
		echo '<span class="azrcrv-n-picker-label">' . esc_html( $item['name'] ) . '</span>';
		echo '</div>';
	}

	echo '</div>'; // .azrcrv-n-picker-grid
	echo '</div>'; // .azrcrv-n-picker
}

/**
 * Same as render_image_picker(), but returns the markup instead of
 * echoing it - used where the caller is building up a larger string (the
 * Integration tab's per-row $extra_field_html).
 */
function get_image_picker_html( $picker_id, $input_name, $current_value, array $items, $empty_label ) {
	ob_start();
	render_image_picker( $picker_id, $input_name, $current_value, $items, $empty_label );
	return ob_get_clean();
}
