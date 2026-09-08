<?php
/*
	integration tab - lets the admin enable/configure integration with the
	other azurecurve plugins this plugin can work alongside.

	Per-row captions deliberately drop the repeated "from azurecurve"
	clause the pre-4.0.0 plugin used ("Integrate with Flags from
	azurecurve") - redundant on a settings page that's already inside an
	azurecurve plugin. The bolded developer/plugin-name treatment lives in
	the descriptive paragraph on the Default Settings tab instead.
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
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_save_options" />
	<?php wp_nonce_field( PLUGIN_HYPHEN, PLUGIN_HYPHEN . '-nonce' ); ?>

	<table class="form-table azrcrv-settings" role="presentation">
		<tbody>

			<?php if ( is_plugin_active( 'azrcrv-flags/azrcrv-flags.php' ) ) : ?>
				<tr>
					<th scope="row"><?php /* translators: %s: plugin name. */ printf( esc_html__( 'Integrate with %s', 'azrcrv-n' ), 'Flags' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enable-flags" value="1" <?php checked( $settings['enable-flags'], 1 ); ?> />
							<?php /* translators: %s: plugin name. */ printf( esc_html__( 'Enable integration with %s?', 'azrcrv-n' ), 'Flags' ); ?>
						</label>
						<p>
							<label for="flag-width"><?php esc_html_e( 'Flag width (px):', 'azrcrv-n' ); ?></label>
							<input type="number" min="1" name="flag-width" id="flag-width" value="<?php echo esc_attr( $settings['flag-width'] ); ?>" style="width: 80px;" />
						</p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Flags', 'azrcrv-n' ); ?></th>
					<td><p class="description"><?php /* translators: %s: plugin name. */ printf( esc_html__( '%s not installed/activated.', 'azrcrv-n' ), 'Flags' ); ?></p></td>
				</tr>
			<?php endif; ?>

			<?php if ( is_plugin_active( 'azrcrv-icons/azrcrv-icons.php' ) ) : ?>
				<tr>
					<th scope="row"><?php /* translators: %s: plugin name. */ printf( esc_html__( 'Integrate with %s', 'azrcrv-n' ), 'Icons' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="icons-integration" value="1" <?php checked( $settings['icons-integration'], 1 ); ?> />
							<?php /* translators: %s: plugin name. */ printf( esc_html__( 'Enable integration with %s?', 'azrcrv-n' ), 'Icons' ); ?>
						</label>
						<p>
							<?php esc_html_e( 'Icon to use for previously-visited locations:', 'azrcrv-n' ); ?>
							<?php render_image_picker( 'icon-visited', 'icon-visited', $settings['icon-visited'], get_available_icons(), __( 'No icon', 'azrcrv-n' ) ); ?>
						</p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Icons', 'azrcrv-n' ); ?></th>
					<td><p class="description"><?php /* translators: %s: plugin name. */ printf( esc_html__( '%s not installed/activated.', 'azrcrv-n' ), 'Icons' ); ?></p></td>
				</tr>
			<?php endif; ?>

			<?php if ( is_plugin_active( 'azrcrv-timelines/azrcrv-timelines.php' ) ) : ?>
				<tr>
					<th scope="row"><?php /* translators: %s: plugin name. */ printf( esc_html__( 'Integrate with %s', 'azrcrv-n' ), 'Timelines' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="timeline-integration" value="1" <?php checked( $settings['timeline-integration'], 1 ); ?> />
							<?php /* translators: %s: plugin name. */ printf( esc_html__( 'Enable integration with %s?', 'azrcrv-n' ), 'Timelines' ); ?>
						</label>
						<p>
							<label for="timeline-signifier"><?php esc_html_e( 'Visited signifier (used if no Icons integration/icon is set):', 'azrcrv-n' ); ?></label>
							<input type="text" name="timeline-signifier" id="timeline-signifier" value="<?php echo esc_attr( $settings['timeline-signifier'] ); ?>" style="width: 60px;" />
						</p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Timelines', 'azrcrv-n' ); ?></th>
					<td><p class="description"><?php /* translators: %s: plugin name. */ printf( esc_html__( '%s not installed/activated.', 'azrcrv-n' ), 'Timelines' ); ?></p></td>
				</tr>
			<?php endif; ?>

			<?php if ( is_plugin_active( 'azrcrv-toggle-showhide/azrcrv-toggle-showhide.php' ) ) : ?>
				<tr>
					<th scope="row"><?php /* translators: %s: plugin name. */ printf( esc_html__( 'Integrate with %s', 'azrcrv-n' ), 'Toggle Show/Hide' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enable-toggle-showhide" value="1" <?php checked( $settings['enable-toggle-showhide'], 1 ); ?> />
							<?php /* translators: %s: plugin name. */ printf( esc_html__( 'Enable integration with %s?', 'azrcrv-n' ), 'Toggle Show/Hide' ); ?>
						</label>
						<p>
							<label for="toggle-title"><?php esc_html_e( 'Toggle title:', 'azrcrv-n' ); ?></label>
							<input type="text" name="toggle-title" id="toggle-title" value="<?php echo esc_attr( $settings['toggle-title'] ); ?>" />
						</p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Toggle Show/Hide', 'azrcrv-n' ); ?></th>
					<td><p class="description"><?php /* translators: %s: plugin name. */ printf( esc_html__( '%s not installed/activated.', 'azrcrv-n' ), 'Toggle Show/Hide' ); ?></p></td>
				</tr>
			<?php endif; ?>

		</tbody>
	</table>

	<?php submit_button( __( 'Save Changes', 'azrcrv-n' ) ); ?>
</form>
