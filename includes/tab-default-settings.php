<?php
/*
	default settings tab - general behaviour settings, plus the distance
	cache rebuild control.
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

$settings = get_settings();
$types    = get_types();
?>

<p>
	<?php
	printf(
		/* translators: %1$s: plugin name, wrapped in <strong>; %2$s: developer name, wrapped in <strong>. */
		esc_html__( '%1$s creates a table of nearby locations based on GPS co-ordinates and integrates with the following %2$s plugins:', 'azrcrv-n' ),
		'<strong>' . esc_html( PLUGIN_NAME ) . '</strong>',
		'<strong>' . esc_html( DEVELOPER_SHORTNAME ) . '</strong>'
	);
	?>
</p>
<ul style="list-style: disc; margin-left: 20px;">
	<li>
		<?php
		printf(
			/* translators: %s: plugin name, wrapped in <strong>. */
			esc_html__( '%s allows a flag to be shown for the location.', 'azrcrv-n' ),
			'<strong>Flags</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: plugin name, wrapped in <strong>. */
			esc_html__( '%s allows an icon to be shown for a location previously visited on a timeline.', 'azrcrv-n' ),
			'<strong>Icons</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: plugin name, wrapped in <strong>. */
			esc_html__( '%s allows the location to be checked against timeline entries to identify previously-visited locations.', 'azrcrv-n' ),
			'<strong>Timelines</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: plugin name, wrapped in <strong>. */
			esc_html__( '%s allows the list of nearby locations to be shown collapsed, expanding when clicked.', 'azrcrv-n' ),
			'<strong>Toggle Show/Hide</strong>'
		);
		?>
	</li>
</ul>
<p><?php esc_html_e( 'These integrations are configured on the Integration tab.', 'azrcrv-n' ); ?></p>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_save_options" />
	<?php wp_nonce_field( PLUGIN_HYPHEN, PLUGIN_HYPHEN . '-nonce' ); ?>

	<table class="form-table azrcrv-settings" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="maximum-locations"><?php esc_html_e( 'Maximum Locations', 'azrcrv-n' ); ?></label></th>
				<td>
					<input type="number" min="1" name="maximum-locations" id="maximum-locations" value="<?php echo esc_attr( $settings['maximum-locations'] ); ?>" />
					<p class="description"><?php esc_html_e( 'The maximum number of nearby locations to display.', 'azrcrv-n' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="location-distance"><?php esc_html_e( 'Location Distance', 'azrcrv-n' ); ?></label></th>
				<td>
					<input type="number" min="1" name="location-distance" id="location-distance" value="<?php echo esc_attr( $settings['location-distance'] ); ?>" />
					<p class="description"><?php esc_html_e( 'The maximum distance, in the unit selected below, for a location to be considered nearby.', 'azrcrv-n' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="unit-of-distance"><?php esc_html_e( 'Unit of Distance', 'azrcrv-n' ); ?></label></th>
				<td>
					<select name="unit-of-distance" id="unit-of-distance">
						<option value="miles" <?php selected( $settings['unit-of-distance'], 'miles' ); ?>><?php esc_html_e( 'Miles', 'azrcrv-n' ); ?></option>
						<option value="km" <?php selected( $settings['unit-of-distance'], 'km' ); ?>><?php esc_html_e( 'Kilometres', 'azrcrv-n' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="compass-type"><?php esc_html_e( 'Compass Directions', 'azrcrv-n' ); ?></label></th>
				<td>
					<select name="compass-type" id="compass-type">
						<option value="16" <?php selected( (string) $settings['compass-type'], '16' ); ?>><?php esc_html_e( '16 point', 'azrcrv-n' ); ?></option>
						<option value="32" <?php selected( (string) $settings['compass-type'], '32' ); ?>><?php esc_html_e( '32 point', 'azrcrv-n' ); ?></option>
					</select>
				</td>
			</tr>
			<?php if ( ! empty( $types ) ) : ?>
				<tr>
					<th scope="row"><label for="default-type"><?php esc_html_e( 'Default Type', 'azrcrv-n' ); ?></label></th>
					<td>
						<select name="default-type" id="default-type">
							<option value="" <?php selected( $settings['default-type'], '' ); ?>></option>
							<?php foreach ( $types as $type_id => $type_name ) : ?>
								<option value="<?php echo esc_attr( $type_id ); ?>" <?php selected( $settings['default-type'], $type_id ); ?>><?php echo esc_html( stripslashes( $type_name ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Type selected by default when adding co-ordinates to a page.', 'azrcrv-n' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default Shortcode Types', 'azrcrv-n' ); ?></th>
					<td>
						<?php foreach ( $types as $type_id => $type_name ) : ?>
							<label style="display: block;">
								<input type="checkbox" name="default-shortcode-types[]" value="<?php echo esc_attr( $type_id ); ?>" <?php checked( in_array( $type_id, (array) $settings['default-shortcode-types'], true ) ); ?> />
								<?php echo esc_html( stripslashes( $type_name ) ); ?>
							</label>
						<?php endforeach; ?>
						<p class="description"><?php esc_html_e( 'Types shown by a plain [nearby] shortcode with no type attribute. Leave all unchecked to show every type.', 'azrcrv-n' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php submit_button( __( 'Save Changes', 'azrcrv-n' ) ); ?>
</form>

<hr />

<h2><?php esc_html_e( 'Distance Cache', 'azrcrv-n' ); ?></h2>
<p><?php esc_html_e( 'Nearby locations and distances are precalculated and cached, rather than being calculated on every page view. Adding, editing, or removing a location\'s co-ordinates automatically refreshes its own cached distances. Use the button below to rebuild the entire cache from scratch - for example, after directly editing the database, or to recover from any drift.', 'azrcrv-n' ); ?></p>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_rebuild_distances" />
	<?php wp_nonce_field( PLUGIN_HYPHEN . '-rebuild-distances', PLUGIN_HYPHEN . '-nonce-rebuild-distances' ); ?>
	<?php submit_button( __( 'Rebuild Distance Cache', 'azrcrv-n' ), 'secondary' ); ?>
</form>
