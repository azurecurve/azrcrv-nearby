<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name:		Nearby
 * Description:		Creates table of nearby locations based on GPS co-ordinates.
 * Version:			4.0.0
 * Requires CP:		1.0
 * Requires PHP:	8.2
 * Author:			azurecurve
 * Author URI:		https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/nearby/
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		azrcrv-n
 * Domain Path:		/assets/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
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
 * Define constants.
 */
const DEVELOPER_SHORTNAME = 'azurecurve';
const DEVELOPER_NAME      = DEVELOPER_SHORTNAME . ' | Development';
const DEVELOPER_RAW_LINK  = 'https://development.azurecurve.co.uk/classicpress-plugins/';
const DEVELOPER_LINK      = '<a href="' . DEVELOPER_RAW_LINK . '">' . DEVELOPER_NAME . '</a>';

const PLUGIN_NAME       = 'Nearby';
const PLUGIN_SHORT_SLUG = 'nearby';
const PLUGIN_SLUG       = 'azrcrv-' . PLUGIN_SHORT_SLUG;
// Kept identical to the pre-4.0.0 plugin so upgrading keeps existing options
// ('azrcrv-n', 'azrcrv-n-types') and database tables without a rename/migration.
const PLUGIN_HYPHEN     = 'azrcrv-n';
const PLUGIN_UNDERSCORE = 'azrcrv_n';
const PLUGIN_FILE       = __FILE__;

// Option names.
const SETTINGS_OPTION_NAME = PLUGIN_HYPHEN;
const TYPES_OPTION_NAME    = PLUGIN_HYPHEN . '-types';

// Bumped from the pre-4.0.0 plugin's '1.0.0' to trigger creation of the new
// azrcrv_nearby_distances cache table (see includes/functions-activation.php)
// and a one-off rebuild of it from existing data on upgrade.
const DB_VERSION = '2.0.0';

/**
 * Load the remote update client, which integrates with azurecurve's own
 * Update Manager server so this plugin updates the same way as the rest of
 * the azurecurve plugin family.
 *
 * NOTE: per the upgrade PRD, this file is left completely untouched - it
 * already self-registers via UpdateClient::get_instance() under this same
 * namespace at the bottom of the file, exactly as it did before this
 * upgrade.
 */
require_once dirname( PLUGIN_FILE ) . '/libraries/updateclient/UpdateClient.class.php';

/**
 * Load activation/deactivation and table-installation functions.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-activation.php';

/**
 * Load small shared helpers used by more than one of the files below.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-helpers.php';

/**
 * Load the Flags/Icons visual picker (directory scan + rendering), used by
 * both the metabox and the Integration settings tab.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-pickers.php';

/**
 * Load settings (options/types) functions.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-settings.php';

/**
 * Load location storage and distance-cache functions - the data layer used
 * by both the metabox (on save/delete) and the shortcode (on read).
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-locations.php';

/**
 * Load the co-ordinates/type/country metabox shown on the post editor.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-metabox.php';

/**
 * Load the [nearby] shortcode and its compass-direction helpers.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-shortcode.php';

/**
 * Load admin menu functions.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-menu.php';

/**
 * Load custom plugin icon/banner path functions, used by Update Manager.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-plugin-images.php';

/**
 * Load the shared azurecurve cross-plugin menu: populates this plugin's
 * entry into the shared directory of azurecurve plugins, and (if not
 * already added by another azurecurve plugin on this site) registers the
 * shared top-level "azurecurve" admin menu page that lists them all.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/azurecurve-menu-populate.php';
require_once dirname( PLUGIN_FILE ) . '/includes/azurecurve-menu-display.php';

/**
 * Load admin/front-end script and style enqueue functions.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-scripts.php';

/**
 * Load language functions.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/functions-language.php';

/**
 * Load setup of activation/deactivation hooks, actions and filters. This is
 * required last, since it references functions declared in the files above.
 */
require_once dirname( PLUGIN_FILE ) . '/includes/setup.php';
