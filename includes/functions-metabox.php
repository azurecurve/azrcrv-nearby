<?php
/*
	co-ordinates/type/country metabox shown on the page editor. Saving it
	upserts the location row (functions-locations.php) and refreshes that
	location's cached distances in the same request.
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
 * Register the metabox. Kept on 'page' only, matching the pre-4.0.0
 * plugin - opening it up to other post types is a separate decision from
 * removing the post_type restriction on the distance calculation itself
 * (see PRD s4), and is out of scope here.
 */
function create_details_metabox() {
	add_meta_box(
		'azrcrv_n_metabox_details',
		sprintf( esc_html__( '%s Details', 'azrcrv-n' ), PLUGIN_NAME ),
		__NAMESPACE__ . '\\render_details_metabox',
		'page',
		'normal',
		'default'
	);
}

/**
 * Render the metabox markup.
 */
function render_details_metabox() {

	global $post;

	$options   = get_settings();
	$location  = get_location_for_post( $post->ID );
	$types     = get_types();

	$coordinates = $location->co_ordinates ?? '';
	$country     = $location->country ?? '';
	// Falls back to the configured Default Type only when this post has no
	// location row at all yet (a new page) - $location->type ?? ... does
	// NOT fall back if a row exists with type explicitly saved as '', so an
	// admin who deliberately clears the type on an existing page has that
	// respected rather than being pulled back to the default on every load.
	$type = $location->type ?? $options['default-type'];
	?>

	<fieldset>
		<div>
			<table style="width: 100%; border-collapse: collapse;">
				<tr>
					<td style="width: 150px;">
						<label for="azrcrv_n_coordinates"><?php esc_html_e( 'Co-ordinates', 'azrcrv-n' ); ?></label>
					</td>
					<td style="width: 100%-150px;">
						<input
							type="text"
							name="azrcrv_n_coordinates"
							id="azrcrv_n_coordinates"
							class="regular-text"
							value="<?php echo esc_attr( $coordinates ); ?>"
						><br />
						<?php esc_html_e( 'Format of co-ordinates is latitude, longitude (e.g. 51.477800, -0.001400).', 'azrcrv-n' ); ?>
					</td>
				</tr>

				<?php if ( is_plugin_active( 'azrcrv-flags/azrcrv-flags.php' ) ) : ?>
					<tr>
						<td style="width: 150px; vertical-align: top;">
							<label><?php esc_html_e( 'Location', 'azrcrv-n' ); ?></label>
						</td>
						<td style="width: 100%-150px;">
							<?php
							render_image_picker(
								'azrcrv_n_country',
								'azrcrv_n_country',
								$country,
								get_available_flags(),
								__( 'No flag', 'azrcrv-n' )
							);
							?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( ! empty( $types ) ) : ?>
					<tr>
						<td style="width: 150px;">
							<label for="azrcrv_n_type"><?php esc_html_e( 'Type', 'azrcrv-n' ); ?></label>
						</td>
						<td style="width: 100%-150px;">
							<select name="azrcrv_n_type" id="azrcrv_n_type">
								<option value="" <?php selected( '', $type ); ?>></option>
								<?php
								foreach ( $types as $type_id => $type_name ) {
									echo '<option value="' . esc_attr( $type_id ) . '" ' . selected( $type, $type_id, false ) . '>' . esc_html( stripslashes( $type_name ) ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</fieldset>

	<?php
	wp_nonce_field( 'azrcrv_n_form_details_metabox_nonce', 'azrcrv_n_form_details_metabox_process' );
}

/**
 * Save the metabox: upsert the location row, then refresh its distances -
 * both always happen together, in the same request.
 */
function save_details_metabox( $post_id, $post ) {

	if ( ! isset( $_POST['azrcrv_n_form_details_metabox_process'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['azrcrv_n_form_details_metabox_process'] ) ), 'azrcrv_n_form_details_metabox_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}

	// Skip autosaves/revisions - they'd otherwise upsert a location row
	// against the autosave's own post ID.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$type        = isset( $_POST['azrcrv_n_type'] ) ? sanitize_text_field( wp_unslash( $_POST['azrcrv_n_type'] ) ) : '';
	$country     = isset( $_POST['azrcrv_n_country'] ) ? sanitize_text_field( wp_unslash( $_POST['azrcrv_n_country'] ) ) : '';
	$coordinates = isset( $_POST['azrcrv_n_coordinates'] ) ? sanitize_text_field( wp_unslash( $_POST['azrcrv_n_coordinates'] ) ) : '';

	save_location_for_post( $post->ID, $type, $country, $coordinates );
}
