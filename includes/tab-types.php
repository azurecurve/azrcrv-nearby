<?php
/*
	types tab - lets the admin manage the "type" taxonomy used to filter
	the [nearby] shortcode (e.g. "Distillery", "Attraction").

	Add/delete use plain <button>/submit_button() elements rather than the
	pre-4.0.0 plugin's <input type="image"> (add.png/delete.png/lock.png)
	submit buttons.
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

$types = get_types();
?>

<h2><?php esc_html_e( 'Add New Type', 'azrcrv-n' ); ?></h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_add_type" />
	<?php wp_nonce_field( PLUGIN_HYPHEN . '-add-type', PLUGIN_HYPHEN . '-nonce-add-type' ); ?>
	<p>
		<label for="new-type" class="screen-reader-text"><?php esc_html_e( 'New type name', 'azrcrv-n' ); ?></label>
		<input type="text" name="new-type" id="new-type" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Distillery', 'azrcrv-n' ); ?>" required />
		<?php submit_button( __( 'Add Type', 'azrcrv-n' ), 'secondary', 'submit', false ); ?>
	</p>
</form>

<h2><?php esc_html_e( 'Existing Types', 'azrcrv-n' ); ?></h2>

<?php if ( empty( $types ) ) : ?>
	<p><?php esc_html_e( 'No types have been added yet.', 'azrcrv-n' ); ?></p>
<?php else : ?>
	<table class="widefat striped" style="max-width: 600px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Type', 'azrcrv-n' ); ?></th>
				<th><?php esc_html_e( 'In Use', 'azrcrv-n' ); ?></th>
				<th><?php esc_html_e( 'Action', 'azrcrv-n' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $types as $type_id => $type_name ) : ?>
				<?php $usage_count = get_type_usage_count( $type_id ); ?>
				<tr>
					<td><?php echo esc_html( stripslashes( $type_name ) ); ?></td>
					<td>
						<?php
						printf(
							/* translators: %d: number of locations using this type. */
							esc_html( _n( '%d location', '%d locations', $usage_count, 'azrcrv-n' ) ),
							(int) $usage_count
						);
						?>
					</td>
					<td>
						<?php if ( $usage_count > 0 ) : ?>
							<span class="description"><?php esc_html_e( 'In use - cannot be deleted', 'azrcrv-n' ); ?></span>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this type?', 'azrcrv-n' ) ); ?>');">
								<input type="hidden" name="action" value="<?php echo esc_attr( PLUGIN_UNDERSCORE ); ?>_delete_type" />
								<input type="hidden" name="delete-type" value="<?php echo esc_attr( $type_id ); ?>" />
								<?php wp_nonce_field( PLUGIN_HYPHEN . '-delete-type', PLUGIN_HYPHEN . '-nonce-delete-type' ); ?>
								<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Delete', 'azrcrv-n' ); ?></button>
							</form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
