<?php
/*
	tab output on the admin page - markup/classes match the shared
	azrcrv-ui-tabs component used across azurecurve's plugins (see
	assets/css/admin-standard.css and assets/js/admin-standard.js), rather
	than the old jQuery-UI-tabs markup this plugin previously used.
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

$tab_default_settings_label = esc_html__( 'Default Settings', 'azrcrv-n' );
$tab_integration_label      = esc_html__( 'Integration', 'azrcrv-n' );
$tab_types_label             = esc_html__( 'Types', 'azrcrv-n' );
$tab_instructions_label      = esc_html__( 'Instructions', 'azrcrv-n' );

ob_start();
require_once __DIR__ . '/tab-default-settings.php';
$tab_default_settings = ob_get_clean();

ob_start();
require_once __DIR__ . '/tab-integration.php';
$tab_integration = ob_get_clean();

ob_start();
require_once __DIR__ . '/tab-types.php';
$tab_types = ob_get_clean();

ob_start();
require_once __DIR__ . '/tab-instructions.php';
$tab_instructions = ob_get_clean();

// tab-other-plugins.php sets $tab_plugins_label and $tab_plugins directly
// (matching the shared pattern used across azurecurve's other plugins)
// rather than being captured via ob_start(), since it builds its output as
// a string rather than echoing it.
require_once __DIR__ . '/tab-other-plugins.php';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector, not a state-changing action.
$active_tab = isset( $_GET['types'] ) ? 'types' : 'default-settings';
?>

<div id="tabs" class="azrcrv-ui-tabs">
	<ul class="azrcrv-ui-tabs-nav azrcrv-ui-widget-header" role="tablist">
		<li class="azrcrv-ui-state-default <?php echo ( 'default-settings' === $active_tab ) ? 'azrcrv-ui-state-active' : ''; ?>" aria-controls="tab-panel-default-settings" aria-labelledby="tab-default-settings" aria-selected="<?php echo ( 'default-settings' === $active_tab ) ? 'true' : 'false'; ?>" aria-expanded="<?php echo ( 'default-settings' === $active_tab ) ? 'true' : 'false'; ?>" role="tab">
			<a id="tab-default-settings" class="azrcrv-ui-tabs-anchor" href="#tab-panel-default-settings"><?php echo $tab_default_settings_label; // phpcs:ignore. ?></a>
		</li>
		<li class="azrcrv-ui-state-default" aria-controls="tab-panel-integration" aria-labelledby="tab-integration" aria-selected="false" aria-expanded="false" role="tab">
			<a id="tab-integration" class="azrcrv-ui-tabs-anchor" href="#tab-panel-integration"><?php echo $tab_integration_label; // phpcs:ignore. ?></a>
		</li>
		<li class="azrcrv-ui-state-default <?php echo ( 'types' === $active_tab ) ? 'azrcrv-ui-state-active' : ''; ?>" aria-controls="tab-panel-types" aria-labelledby="tab-types" aria-selected="<?php echo ( 'types' === $active_tab ) ? 'true' : 'false'; ?>" aria-expanded="<?php echo ( 'types' === $active_tab ) ? 'true' : 'false'; ?>" role="tab">
			<a id="tab-types" class="azrcrv-ui-tabs-anchor" href="#tab-panel-types"><?php echo $tab_types_label; // phpcs:ignore. ?></a>
		</li>
		<li class="azrcrv-ui-state-default" aria-controls="tab-panel-instructions" aria-labelledby="tab-instructions" aria-selected="false" aria-expanded="false" role="tab">
			<a id="tab-instructions" class="azrcrv-ui-tabs-anchor" href="#tab-panel-instructions"><?php echo $tab_instructions_label; // phpcs:ignore. ?></a>
		</li>
		<li class="azrcrv-ui-state-default" aria-controls="tab-panel-plugins" aria-labelledby="tab-plugins" aria-selected="false" aria-expanded="false" role="tab">
			<a id="tab-plugins" class="azrcrv-ui-tabs-anchor" href="#tab-panel-plugins"><?php echo $tab_plugins_label; // phpcs:ignore. ?></a>
		</li>
	</ul>
	<div id="tab-panel-default-settings" class="azrcrv-ui-tabs-scroll<?php echo ( 'default-settings' !== $active_tab ) ? ' azrcrv-ui-tabs-hidden' : ''; ?>" role="tabpanel" aria-hidden="<?php echo ( 'default-settings' !== $active_tab ) ? 'true' : 'false'; ?>">
		<fieldset>
			<legend class="screen-reader-text"><?php echo $tab_default_settings_label; // phpcs:ignore. ?></legend>
			<?php echo $tab_default_settings; // phpcs:ignore. ?>
		</fieldset>
	</div>
	<div id="tab-panel-integration" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
		<fieldset>
			<legend class="screen-reader-text"><?php echo $tab_integration_label; // phpcs:ignore. ?></legend>
			<?php echo $tab_integration; // phpcs:ignore. ?>
		</fieldset>
	</div>
	<div id="tab-panel-types" class="azrcrv-ui-tabs-scroll<?php echo ( 'types' !== $active_tab ) ? ' azrcrv-ui-tabs-hidden' : ''; ?>" role="tabpanel" aria-hidden="<?php echo ( 'types' !== $active_tab ) ? 'true' : 'false'; ?>">
		<fieldset>
			<legend class="screen-reader-text"><?php echo $tab_types_label; // phpcs:ignore. ?></legend>
			<?php echo $tab_types; // phpcs:ignore. ?>
		</fieldset>
	</div>
	<div id="tab-panel-instructions" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
		<fieldset>
			<legend class="screen-reader-text"><?php echo $tab_instructions_label; // phpcs:ignore. ?></legend>
			<?php echo $tab_instructions; // phpcs:ignore. ?>
		</fieldset>
	</div>
	<div id="tab-panel-plugins" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
		<fieldset>
			<legend class="screen-reader-text"><?php echo $tab_plugins_label; // phpcs:ignore. ?></legend>
			<?php echo $tab_plugins; // phpcs:ignore. ?>
		</fieldset>
	</div>
</div>
<?php
/*
	donate button
*/
?>
<div class="azrcrv-donate">
	<?php esc_html_e( 'Support', 'azrcrv-n' ); ?>
	azurecurve | Development
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="MCJQN9SJZYLWJ">
		<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
	</form>
	<span>
		<?php esc_html_e( 'You can help support the development of our free plugins by donating a small amount of money.', 'azrcrv-n' ); ?>
	</span>
</div>
