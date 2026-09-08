<?php
/*
	instructions tab
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
?>

<h2><?php esc_html_e( 'Instructions', 'azrcrv-n' ); ?></h2>

<p><?php esc_html_e( 'Nearby builds a table of nearby locations (pages) based on GPS co-ordinates, and displays it wherever you place the [nearby] shortcode.', 'azrcrv-n' ); ?></p>

<ol>
	<li><?php esc_html_e( 'Edit a page and open the "Nearby Details" box. Enter co-ordinates as latitude, longitude (for example: 51.477800, -0.001400) - most map services will give you this format if you right-click a location.', 'azrcrv-n' ); ?></li>
	<li><?php esc_html_e( 'If the Flags integration is enabled, choose a flag for the location from the picker; if you have defined Types on the Types tab, choose one there too.', 'azrcrv-n' ); ?></li>
	<li><?php esc_html_e( 'Update/publish the page. Repeat for every page you want considered as a nearby location.', 'azrcrv-n' ); ?></li>
	<li><?php esc_html_e( 'Add the [nearby] shortcode to any page with co-ordinates of its own, and the other nearby pages (within the distance configured on the Default Settings tab) will be listed automatically.', 'azrcrv-n' ); ?></li>
</ol>

<h3><?php esc_html_e( 'Shortcode attributes', 'azrcrv-n' ); ?></h3>
<p><?php esc_html_e( 'The [nearby] shortcode accepts two optional attributes:', 'azrcrv-n' ); ?></p>
<ul style="list-style: disc; margin-left: 20px;">
	<li>
		<?php
		printf(
			/* translators: %s: attribute name, wrapped in <code>. */
			esc_html__( '%s - limit the locations shown to one or more types (comma-separated). If omitted, the Default Shortcode Types configured on the Default Settings tab are used; if none are configured either, every type is shown.', 'azrcrv-n' ),
			'<code>type</code>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: attribute name, wrapped in <code>. */
			esc_html__( '%s - override the default toggle title, when Toggle Show/Hide integration is enabled.', 'azrcrv-n' ),
			'<code>title</code>'
		);
		?>
	</li>
</ul>

<p>
	<?php esc_html_e( 'Example:', 'azrcrv-n' ); ?><br />
	<code>[nearby type="distillery" title="Nearby Distilleries"]</code>
</p>

<h3><?php esc_html_e( 'Distance cache', 'azrcrv-n' ); ?></h3>
<p><?php esc_html_e( 'Nearby distances between locations are precalculated and cached automatically as pages are saved, rather than being calculated on every page view - see the Distance Cache section on the Default Settings tab if you ever need to rebuild the cache from scratch.', 'azrcrv-n' ); ?></p>
