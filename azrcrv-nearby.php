<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Nearby
 * Description: Creates table of nearby locations based on GPS co-ordinates.
 * Version: 3.0.2
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/nearby/
 * Text Domain: azrcrv-n
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_n');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */

// constants.
const DB_VERSION = '1.0.0';		

// add actions
add_action('admin_menu', 'azrcrv_n_create_admin_menu');
add_action('admin_enqueue_scripts', 'azrcrv_n_load_admin_style');
add_action('admin_post_azrcrv_n_save_options', 'azrcrv_n_save_options');
add_action('admin_post_azrcrv_n_add_type', 'azrcrv_n_add_type');
add_action('admin_post_azrcrv_n_delete_type', 'azrcrv_n_delete_type');
add_action('the_posts', 'azrcrv_n_check_for_shortcode');
add_action('plugins_loaded', 'azrcrv_n_load_languages');
add_action( 'add_meta_boxes', 'azrcrv_n_create_details_metabox' );
add_action( 'save_post', 'azrcrv_n_save_details_metabox', 1, 2 );
add_action( 'plugins_loaded', 'azrcrv_n_install' );

// add filters
add_filter('plugin_action_links', 'azrcrv_n_add_plugin_action_link', 10, 2);
add_filter('codepotent_update_manager_image_path', 'azrcrv_n_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_n_custom_image_url');

// add shortcodes
add_shortcode('nearby', 'azrcrv_n_displaynearbylocations');

/**
 * Create table on install of plugin.
 *
 * @since 3.0.0
 */
function azrcrv_n_install() {

	global $wpdb;

	$installed_ver = get_option( 'azrcrv-n-db-version' );

	if ( $installed_ver != DB_VERSION ) {

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}azrcrv_nearby (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) NOT NULL,
			type VARCHAR(100) NOT NULL,
			country VARCHAR(200) NOT NULL,
			co_ordinates VARCHAR(100) NOT NULL,
			x_co_ordinate DOUBLE NOT NULL,
			y_co_ordinate DOUBLE NOT NULL,
			PRIMARY KEY (id) USING BTREE,
			INDEX post_id (post_id) USING BTREE,
			INDEX type (type (100)) USING BTREE
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'azrcrv-n-db-version', DB_VERSION );
	}
	
	// check if table populated and if not migrate postmeta
	$num_rows = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}azrcrv_nearby" );
	if ( $num_rows == 0 ) {
		$wpdb->query( "INSERT INTO {$wpdb->prefix}azrcrv_nearby (post_id,type,country,co_ordinates,x_co_ordinate,y_co_ordinate) SELECT
	P.ID
	,PM_TYPE.meta_value AS NearbyType
	,PM_COUNTRY.meta_value AS NearbyCountry
	,PM_CO_ORDINATES.meta_value AS NearbyCoOrdinates
	,TRIM(SUBSTRING_INDEX(PM_CO_ORDINATES.meta_value, ',', 1)) AS NearbyXCoOrdinate
	,TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(PM_CO_ORDINATES.meta_value, ',', 2), ',', -1)) AS NearbyYCoOrdinate
FROM
	{$wpdb->prefix}posts AS P
LEFT JOIN
	{$wpdb->prefix}postmeta AS PM_TYPE
		ON
			PM_TYPE.post_id = P.ID
		AND
			PM_TYPE.meta_key = '_azrcrv_n_type'
LEFT JOIN
	{$wpdb->prefix}postmeta AS PM_COUNTRY
		ON
			PM_COUNTRY.post_id = P.ID
		AND
			PM_COUNTRY.meta_key = '_azrcrv_n_country'
LEFT JOIN
	{$wpdb->prefix}postmeta AS PM_CO_ORDINATES
		ON
			PM_CO_ORDINATES.post_id = P.ID
		AND
			PM_CO_ORDINATES.meta_key = '_azrcrv_n_coordinates'
WHERE
	P.post_status = 'publish'
AND
	(
		PM_TYPE.post_id IS NOT NULL
	OR
		PM_COUNTRY.post_id IS NOT NULL
	OR
		PM_CO_ORDINATES.post_id IS NOT NULL
	)" );
	}
}

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('azrcrv-n', false, $plugin_rel_path);
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_check_for_shortcode($posts){
    if (empty($posts)){
        return $posts;
	}
	
	
	// array of shortcodes to search for
	$shortcodes = array(
						'nearby'
						);
	
    // loop through posts
    $found = false;
    foreach ($posts as $post){
		// loop through shortcodes
		foreach ($shortcodes as $shortcode){
			// check the post content for the shortcode
			if (has_shortcode($post->post_content, $shortcode)){
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}
 
    if ($found){
		// as shortcode found call functions to load css and jquery
        azrcrv_n_load_css();
    }
    return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_load_css(){
	wp_enqueue_style('azrcrv-n', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Custom plugin image path.
 *
 * @since 2.1.0
 *
 */
function azrcrv_n_custom_image_path($path){
    if (strpos($path, 'azrcrv-nearby') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 2.1.0
 *
 */
function azrcrv_n_custom_image_url($url){
    if (strpos($url, 'azrcrv-nearby') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Get options including defaults.
 *
 * @since 1.2.0
 *
 */
function azrcrv_n_get_option($option_name){
 
	$defaults = array(
						'maximum-locations' => 20,
						'location-distance' => 200,
						'compass-type' => 16,
						'unit-of-distance' => 'miles',
						'enable-flags' => 0,
						'flag-width' => 16,
						'enable-toggle-showhide' => 0,
						'toggle-title' => 'Nearby Locations',
						'timeline-integration' => 0,
						'timeline-signifier' => '*',
						'icons-integration' => 0,
						'icon-visited' => '',
						'default-type' => '',
						'default-shortcode-types' => array(),
					);

	$options = get_option($option_name, $defaults);

	$options = wp_parse_args($options, $defaults);

	return $options;

}

/**
 * Add nearby action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-n').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'azrcrv-n').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Nearby Settings", "nearby")
						,esc_html__("Nearby", "nearby")
						,'manage_options'
						,'azrcrv-n'
						,'azrcrv_n_display_options');
}

/**
 * Load css and jquery for flags.
 *
 * @since 2.3.0
 *
 */
function azrcrv_n_load_admin_style(){
	if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'azrcrv-n' ) ) {
		wp_register_style('nearby-css', plugins_url('assets/css/admin.css', __FILE__), false, '1.0.0');
		wp_enqueue_style( 'nearby-css' );
		
		wp_enqueue_script("nearby-admin-js", plugins_url('assets/jquery/jquery.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'));
	}
}

/**
 * Create the metabox
 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
 */
function azrcrv_n_create_details_metabox() {

	// Can only be used on a single post type (ie. page or post or a custom post type).
	// Must be repeated for each post type you want the metabox to appear on.
	add_meta_box(
		'azrcrv_n_metabox_details', // Metabox ID
		sprintf(__('%s Details', 'azrcrv-n'), 'Nearby'), // Title to display
		'azrcrv_n_render_details_metabox', // Function to call that contains the metabox content
		'page', // Post type to display metabox on
		'normal', // Where to put it (normal = main colum, side = sidebar, etc.)
		'default' // Priority relative to other metaboxes
	);
}

/**
 * Render the metabox markup
 * This is the function called in `azrcrv_n_create_metabox()`
 */
function azrcrv_n_render_details_metabox() {
	// Variables
	global $wpdb; // Get global database object.
	global $post; // Get the current post data.
	
	$options = azrcrv_n_get_option('azrcrv-n');
	
	$post_id = sanitize_key( wp_unslash(  $post->ID ) );
	
	$nearby_recordset = $wpdb->get_row( $wpdb->prepare( "SELECT type,country,co_ordinates FROM {$wpdb->prefix}azrcrv_nearby WHERE post_id = %d", $post_id ) );
	if ( $nearby_recordset ) {
		$azrcrv_n_coordinates = $nearby_recordset->co_ordinates;
		$azrcrv_n_country = $nearby_recordset->country;
		$azrcrv_n_type = $nearby_recordset->type;
	}else{
		$azrcrv_n_coordinates = '';
		$azrcrv_n_country = '';
		$azrcrv_n_type = '';
	}
	?>

		<fieldset>
			<div>
				<table style="width: 100%; border-collapse: collapse;">
					<tr>
						<td style="width: 150px;">
							<label for="azrcrv_n_coordinates">
								<?php
									esc_html_e( 'Co-ordinates', 'azrcrv-n');
								?>
							</label>
						</td>
						<td style="width: 100%-150px;">
							<input
								type="text"
								name="azrcrv_n_coordinates"
								id="azrcrv_n_coordinates"
								class="regular-text"
								value="<?php echo esc_attr( $azrcrv_n_coordinates ); ?>"
							><br />
								<?php
									esc_html_e( 'Format of co-ordinates is latitude, longitude (e.g. 51.477800, -0.001400).', 'azrcrv-n');
								?>
						</td>
					</tr>
					
					<?php if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){ ?>
						<tr>
							<td style="width: 150px;">
								<label for="azrcrv_n_country">
									<?php
										esc_html_e( 'Location', 'azrcrv-n');
									?>
								</label>
							</td>
							<td style="width: 100%-150px;">
								<select name="azrcrv_n_country">
									<option value="" <?php if($azrcrv_n_country == ""){ echo ' selected="selected"'; } ?>></option>
									<?php
										$flags = azrcrv_f_get_flags();
										
										foreach ($flags as $flag_id => $flag){
											if ($azrcrv_n_country == $flag_id){
												$selected = 'selected';
											}else{
												$selected = '';
											}
											echo '<option value="'.esc_html($flag_id).'" '.$selected.'>'.esc_html($flag['name']).'</option>';
										}
									?>
								</select>
							</td>
						</tr>
					<?php }	?>
					
					<?php
					$types = get_option('azrcrv-n-types');
					if (is_array($types) AND count($types) > 0){ ?>
						<tr>
							<td style="width: 150px;">
								<label for="type">
									<?php
										esc_html_e( 'Type', 'azrcrv-n' );
									?>
								</label>
							</td>
							<td style="width: 100%-150px;">
								<select name="azrcrv_n_type">
									<option value="" <?php if($options['default-type'] == ""){ echo ' selected="selected"'; } ?>></option>
									 
									<?php
										ksort($types);
										
										foreach ($types as $type_id => $type_name){
											if ($type_id == $azrcrv_n_type){
												$selected = 'selected';
											}else{
												$selected = '';
											}
											echo '<option value="'.esc_attr($type_id).'" '.$selected.'>'.esc_attr(stripslashes($type_name)).'</option>';
										}
									?>
								</select>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</fieldset>

	<?php
	// Security field
	// This validates that submission came from the
	// actual dashboard and not the front end or
	// a remote server.
	wp_nonce_field( 'azrcrv_n_form_details_metabox_nonce', 'azrcrv_n_form_details_metabox_process' );
}

/**
 * Save the metabox
 * @param  Number $post_id The post ID
 * @param  Array  $post    The post data
 */
function azrcrv_n_save_details_metabox( $post_id, $post ) {
	
	global $wpdb;

	// Verify that our security field exists. If not, bail.
	if ( !isset( $_POST['azrcrv_n_form_details_metabox_process'] ) ) return;

	// Verify data came from edit/dashboard screen
	if ( !wp_verify_nonce( $_POST['azrcrv_n_form_details_metabox_process'], 'azrcrv_n_form_details_metabox_nonce' ) ) {
		return $post->ID;
	}

	// Verify user has permission to edit post
	if ( !current_user_can( 'edit_post', $post->ID )) {
		return $post->ID;
	}
	
	$type = '';
	if ( isset( $_POST['azrcrv_n_type'] ) ) {
		$type = sanitize_text_field( wp_unslash( $_POST['azrcrv_n_type'] ) );
	}
	$country = '';
	if ( isset( $_POST['azrcrv_n_country'] ) ) {
		$country = sanitize_text_field( wp_unslash( $_POST['azrcrv_n_country'] ) );
	}
	$coordinates = '';
	if ( isset( $_POST['azrcrv_n_coordinates'] ) ) {
		$coordinates = sanitize_text_field( wp_unslash( $_POST['azrcrv_n_coordinates'] ) );
	}

	$nearby_exists = azrcrv_n_get_nearby( $post->ID );

	if ( $nearby_exists ) {
		
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}azrcrv_nearby SET type = %s, country = %s, co_ordinates = %s, x_co_ordinate = TRIM(SUBSTRING_INDEX( %s, ',', 1)), y_co_ordinate = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX( %s, ',', 2), ',', -1)) WHERE post_id = %d", $type, $country, $coordinates, $coordinates, $coordinates, $post->ID ) );

	} else {

		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}azrcrv_nearby ( post_id, type, country, co_ordinates, x_co_ordinate, y_co_ordinate ) VALUES ( %d, %s, %s, %s, TRIM(SUBSTRING_INDEX( %s, ',', 1)), TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX( %s, ',', 2), ',', -1)) )", $post->ID ,$type, $country, $coordinates, $coordinates, $coordinates ) );

	}

}

/**
 * Get nearby.
 *
 * @since 3.0.0
 */
function azrcrv_n_get_nearby( $post_id ) {

	global $wpdb;

	$nearby = $wpdb->get_row( $wpdb->prepare( "SELECT type, country, co_ordinates, x_co_ordinate, y_co_ordinate FROM {$wpdb->prefix}azrcrv_nearby WHERE post_id = %d", $post_id ) );

	return $nearby;
}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_is_plugin_active($plugin){
    return in_array($plugin, (array) get_option('active_plugins', array()));
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'azrcrv-n'));
    }
	
	global $wpdb;
	
	// Retrieve plugin configuration options from database
	$options = azrcrv_n_get_option('azrcrv-n');
	
	$types = get_option('azrcrv-n-types');
	if (is_array($types)){ ksort($types); }
	
	?>
	<div id="azrcrv-n-general" class="wrap">
		<fieldset>
			<h1>
				<?php
					echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
					echo esc_html(get_admin_page_title());
				?>
			</h1>
			<?php if(isset($_GET['settings-updated'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Settings have been saved.', 'azrcrv-n'); ?></strong></p>
				</div>
			<?php }elseif(isset($_GET['type-added'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('New type has been successfully added.', 'azrcrv-n'); ?></strong></p>
				</div>
			<?php }elseif(isset($_GET['type-exists'])){ ?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e('Type already exists.', 'azrcrv-n'); ?></strong></p>
				</div>
			<?php }elseif(isset($_GET['type-deleted'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Type has been successfully deleted.', 'azrcrv-n'); ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_n_save_options" />
				<input name="page_options" type="hidden" value="copyright" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-n', 'azrcrv-n-nonce'); ?>
				
				<?php
					if(isset($_GET['types'])){
						$tab1active = '';
						$tab3active = 'nav-tab-active';
						$tab1visibility = 'invisible';
						$tab3visibility = '';
					}else{
						$tab1active = 'nav-tab-active';
						$tab3active = '';
						$tab1visibility = '';
						$tab3visibility = 'invisible';
					}
				?>
			
				<h2 class="nav-tab-wrapper nav-tab-wrapper-azrcrv-n">
					<a class="nav-tab <?php echo $tab1active; ?>" data-item=".tabs-1" href="#tabs-1"><?php esc_html_e('Default Settings', 'azrcrv-n') ?></a>
					<a class="nav-tab" data-item=".tabs-2" href="#tabs-2"><?php esc_html_e('Integration', 'azrcrv-n') ?></a>
					<a class="nav-tab <?php echo $tab3active; ?>" data-item=".tabs-3" href="#tabs-3"><?php esc_html_e('Types', 'azrcrv-n') ?></a>
				</h2>
				
				<div>
					<div class="azrcrv_n_tabs <?php echo $tab1visibility; ?> tabs-1">
						
						<p>
							<?php printf(esc_html__('Nearby creates a table of nearby locations (pages) based on GPS co-ordinates and integrates with the following %s plugins:', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
							<ul class='azrcrv-n'>
								<li><?php printf(esc_html__('%s allows a location to be set for a page; this will display the location flag next to the location name in the table of nearby attractions.', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>'); ?></li>
								<li><?php printf(esc_html__('%s allows an icon to be displayed next to a nearby location which has an entry on a timeline (requires integration with %s to be enabled).', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>', '<em>Timelines</em>'); ?></li>
								<li><?php printf(esc_html__('%s allows a character (such as *) to be displayed next to a nearby location which has an entry on a timeline.', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/">Timelines</a>'); ?></li>
								<li><?php printf(esc_html__('%s allows the table of nearby locations to be enclosed with a toggle.', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/">Toggle Show/Hide</a>'); ?></li>
							</ul>
						</p>

						<p><?php printf(esc_html__('Apply the %s shortcode to a page with co-ordinates and nearby locations (pages with co-ordinates), based on the settings, will be displayed in a table.', 'azrcrv-n'), '<strong>[nearby]</strong>'); ?></p>

						<p><?php esc_html_e('The shortcode accepts two parameters', 'azrcrv-n'); ?>:
							<ul>
								<li><?php printf(esc_html__('%s to limit nearby attractions  (multiple types can be provided in comma separated string)', 'azrcrv-n'), '<strong>type</strong>'); ?></li>
								<li><?php printf(esc_html__('%s to override the default toggle title', 'azrcrv-n'), '<strong>title</strong>'); ?></li>
							</ul>
						</p>

						<p><?php printf(esc_html__('Example shortcode usage: %s', 'azrcrv-n'), '<strong>[nearby type="Distilleries" title="Nearby Distilleries"]</strong>'); ?></p>

						<p><?php esc_html_e('Examples of this plugin in action:', 'azrcrv-n'); ?>
							<ul>
								<li><a href='https://coppr.uk/distilleries/ireland/northern/echlinville/'>coppr|Distilleries To Visit</a></li>
								<li><a href='https://www.darkforge.co.uk/attractions/europe/republic-of-ireland/east/county-meath/newgrange-monument/'>DarkNexus|Tourist Attractions</a></li>
							</ul>
						</p>
						
						<table class="form-table">
						
							<tr><th scope="row"><label for="nearby"><?php esc_html_e('Maximum Locations', 'azrcrv-n'); ?></label></th><td>
								<input name="maximum-locations" type="number" step="1" min="1" id="maximum-locations" value="<?php echo stripslashes($options['maximum-locations']); ?>" class="small-text" /> locations</td>
							</td></tr>
							
							<tr><th scope="row"><label for="nearby"><?php esc_html_e('Location Distance', 'azrcrv-n'); ?></label></th><td>
								<input name="location-distance" type="number" step="1" min="1" id="location-distance" value="<?php echo stripslashes($options['location-distance']); ?>" class="small-text" /> <select name="unit-of-distance">
									<?php
										if ($options['unit-of-distance'] == 'km'){
											echo '<option value="km" selected>km</option>';
											echo '<option value="miles" >miles</option>';
										}else{ // miles
											echo '<option value="km" >km</option>';
											echo '<option value="miles" selected >miles</option>';
										}
									?>
								</select></td>
							</td></tr>
							
							<tr><th scope="row"><label for="compass-type"><?php esc_html_e('Compass Type', 'azrcrv-n'); ?></label></th><td>
								<select name="compass-type">
									<?php
										if ($options['compass-type'] == '16'){
											echo '<option value="16" selected>16 point compass</option>';
											echo '<option value="32" >32 point compass</option>';
										}else{ // 32-wind compass
											echo '<option value="16" >16 point compass</option>';
											echo '<option value="32" selected >32 point compass</option>';
										}
									?>
								</select></td>
							</td></tr>
							
						</table>
						
						<table class="form-table">
							
							<tr>
								<th scope="row">
									<label for="default-type">
										<?php esc_html_e('Default type', 'azrcrv-n'); ?>
									</label>
								</th>
								<td>
										<?php if (is_array($types) AND count($types) > 0){ ?>
										<select name="default-type">
											 
											<option value="" <?php if($options['default-type'] == ""){ echo ' selected="selected"'; } ?>></option>
											<?php
												
												foreach ($types as $type_id => $type_name){
													if ($type_id == $options['default-type']){
														$selected = 'selected';
													}else{
														$selected = '';
													}
													echo '<option value="'.esc_attr($type_id).'" '.$selected.'>'.esc_attr(stripslashes($type_name)).'</option>';
												}
											?>
										</select>
										<p class="description"><?php esc_html_e('Default type for new nearby locations.', 'azrcrv-n'); ?></p>
									<?php }else{
										_e('Add some types to select a default.', 'azrcrv-n');
									} ?>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="default-shortcode-types">
										<?php esc_html_e('Default shortcode types', 'azrcrv-n'); ?>
									</label>
								</th>
								<td>
									<?php if (is_array($types) AND count($types) > 0){ ?>
										<select name="default-shortcode-types[]" multiple>
											 
											<option value="" <?php if(in_array("",$options['default-shortcode-types'])){ echo ' selected="selected"'; } ?>></option>
											<?php
												foreach ($types as $type_id => $type_name){
													if (in_array($type_id, $options['default-shortcode-types'])){
														$selected = 'selected';
													}else{
														$selected = '';
													}
													echo '<option value="'.esc_attr($type_id).'" '.$selected.'>'.esc_attr(stripslashes($type_name)).'</option>';
												}
											?>
										</select>
										<p class="description"><?php printf(esc_html__('Hold down %s to select multiple types.', 'azrcrv-n'), 'Control/Command'); ?></p>
									<?php }else{
										_e('Add some types to select default shortcode types.', 'azrcrv-n');
									} ?>
								</td>
							</tr>
							
						</table>
				<input type="submit" value="Save Changes" class="button-primary"/>
					</div>
				
					<div class="azrcrv_n_tabs invisible tabs-2">
						
						<table class="form-table">
						
							<tr>
								<th scope="row">
									<label for="enable-flags">
										<?php printf(esc_html__('Integrate with %s from %s', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
									</label>
								</th>
								<td>
									<?php
										if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){ ?>
											<label for="enable-flags"><input name="enable-flags" type="checkbox" id="enable-flags" value="1" <?php checked('1', $options['enable-flags']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'azrcrv-n'), 'Flags', 'azurecurve'); ?></label>
										<?php }else{
											printf(esc_html__('%s from %s not installed/activated.', 'azrcrv-n'), 'Flags', 'azurecurve');
										}
										?>
								</td>
							</tr>
							
							<tr><th scope="row"><?php esc_html_e('Flag width', 'azrcrv-n'); ?></th><td>
								<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Flag width', 'azrcrv-n'); ?></span></legend>
									<label for="flag-width"><input type="number" name="flag-width" class="small-text" value="<?php echo $options['flag-width']; ?>" />px</label>
								</fieldset>
							</td></tr>
							
						</table>
					
						<table class="form-table">
							
							<tr>
								<th scope="row">
									<label for="icons-integration">
										<?php printf(esc_html__('Integrate with %s from %s', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
									</label>
								</th>
								<td>
									<?php
										if (azrcrv_n_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){ ?>
											<label for="icons-integration"><input name="icons-integration" type="checkbox" id="icons-integration" value="1" <?php checked('1', $options['icons-integration']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'azrcrv-n'), 'Icons', 'azurecurve'); ?></label>
										<?php }else{
											printf(esc_html__('%s from %s not installed/activated.', 'azrcrv-n'), 'Icons', 'azurecurve');
										}
										?>
								</td>
							</tr>
						
						</table>
						
						<table class="form-table">
						
							<tr>
								<th scope="row">
									<label for="timeline-integration">
										<?php printf(esc_html__('Integrate with %s from %s', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/">Timelines</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
									</label>
								</th>
								<td>
									<?php
										if (azrcrv_n_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){ ?>
											<label for="timeline-integration"><input name="timeline-integration" type="checkbox" id="timeline-integration" value="1" <?php checked('1', $options['timeline-integration']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'azrcrv-n'), 'Timelines', 'azurecurve'); ?></label>
										<?php }else{
											printf(esc_html__('%s from %s not installed/activated.', 'azrcrv-n'), 'Timelines', 'azurecurve');
										}
										?>
								</td>
							</tr>
							
							<?php
								if (azrcrv_n_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){ ?>
									<tr>
										<th scope="row">
											<label for="timeline-signifier">
												<?php esc_html_e('Timeline Signifier', 'azrcrv-n'); ?>
											</label>
										</th>
										<td>
											<input name="timeline-signifier" type="text" id="timeline-signifier" value="<?php echo stripslashes($options['timeline-signifier']); ?>" class="small-text" />
											<?php										
											if (azrcrv_n_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){
											_e('or', 'azrcrv-n'); ?> <select name="icon-visited">
													<option value="" <?php if($options['icon-visited'] == ''){ echo ' selected="selected"'; } ?>>&nbsp;</option>
													<?php						
													$icons = azrcrv_i_get_icons();
													
													foreach ($icons as $icon_id => $icon){
														echo '<option value="'.esc_html($icon_id).'" ';
														if($options['icon-visited'] == esc_html($icon_id)){ echo ' selected="selected"'; }
														echo '>'.esc_html($icon_id).'</option>';
													}
												echo '</select>';
											}
											?>
											<p class="description"><?php esc_html_e('Symbol displayed next to nearby entries which have a timeline entry.', 'azrcrv-n'); ?></p>
										</td>
									</tr>
								<?php }
							?>
							
						</table>
						
						<table class="form-table">
							
							<tr>
								<th scope="row">
									<label for="enable-toggle-showhide">
										<?php printf(esc_html__('Integrate with %s from %s', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/">Toggle Show/Hide</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
									</label>
								</th>
								<td>
									<?php
										if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php')){ ?>
											<label for="enable-toggle-showhide"><input name="enable-toggle-showhide" type="checkbox" id="enable-toggle-showhide" value="1" <?php checked('1', $options['enable-toggle-showhide']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'azrcrv-n'), 'Toggle Show/Hide', 'azurecurve'); ?></label></label>
										<?php }else{
											echo esc_html_e('Toggle Show/Hide from azurecurve not installed/activated.', 'azrcrv-n');
										}
										?>
								</td>
							</tr>
							
							<?php
								if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php')){ ?>
									<tr>
										<th scope="row">
											<label for="toggle-title">
												<?php esc_html_e('Toggle Title', 'azrcrv-n'); ?>
											</label>
										</th>
										<td>
											<input name="toggle-title" type="text" id="toggle-title" value="<?php echo stripslashes($options['toggle-title']); ?>" class="regular-text" />
										</td>
									</tr>
								<?php }
							?>
							
						</table>
						<input type="submit" value="Save Changes" class="button-primary"/>
					</div>
			</form>
					
				<div class="azrcrv_n_tabs <?php echo $tab3visibility; ?> tabs-3">
					
					<form method="post" action="admin-post.php" enctype="multipart/form-data">
						<input type="hidden" name="action" value="azrcrv_n_add_type" />
					
						<table class="form-table">
							
							<tr>
								<th scope="row">
									<label for="new-type">
										<?php esc_html_e('Add new type', 'azrcrv-n'); ?>
									</label>
								</th>
								<td>
									<input name="new-type" type="text" id="new-type" value="" class="regular-text" /><input type="image" src="<?php echo plugin_dir_url(__FILE__); ?>assets/images/add.png" name="add" title="Add" alt="Add" value="Add" class="azrcrv-n" />
								</td>
							</tr>
							
						</table>
					
						<?php wp_nonce_field('azrcrv-n-add-type', 'azrcrv-n-nonce-add-type'); ?>
						<input type="hidden" name="azrcrv_n_add_type" value="yes" />
					</form>
					
					<table class="form-table">
						
						<tr>
							<th scope="row">
								<label for="delete-type">
									<?php esc_html_e('Existing types', 'azrcrv-n'); ?>
								</label>
							</th>
							<td>
								<?php
								if (is_array($types) AND count($types) > 0){
									foreach ($types as $type_id => $type_name){
										echo '<form method="post" action="admin-post.php" enctype="multipart/form-data">';
										echo  '<input name="delete-type-desc" type="text" id="delete-type-desc" value="'.esc_attr(stripslashes($type_name)).'" class="regular-text" disabled />&nbsp;';
										
										$page_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->azrcrv_nearby WHERE type = '%s';", $type_id)); 
										
										$locked_message = '';
										if ($type_id == $options['default-type']){
											$locked_message = 'Default';
										}elseif (in_array($type_id, $options['default-shortcode-types'])){
											$locked_message = 'Default for shortcodes';
										}elseif ($page_count > 0){
											$locked_message = $page_count.' assignment';
											if ($page_count > 1){
												$locked_message .= 's';
											}
										}
										
										if ($locked_message == ''){
											echo '<input type="image" src="'.plugin_dir_url(__FILE__).'assets/images/delete.png" name="delete" title="Delete" alt="Delete" value="Delete" class="azrcrv-n" />';
										}else{
											echo '&nbsp;<img src="'.plugin_dir_url(__FILE__).'assets/images/lock.png" name="lock" title="Locked: '.$locked_message.'" alt="Locked: '.$page_count.'" class="azrcrv-n" />';
										}
										
										echo '<input type="hidden" name="action" value="azrcrv_n_delete_type" />';
										echo '<input type="hidden" name="delete-type" value="'.esc_attr(stripslashes($type_id)).'" class="short-text" />';
										wp_nonce_field('azrcrv-n-delete-type', 'azrcrv-n-nonce-delete-type');
										echo '<input type="hidden" name="azrcrv_n_delete_type" value="yes" />';
										echo '</form>';
									}
									echo '<p class="description">'.esc_html__('Types assigned to pages or set as defaults cannot be deleted.', 'azrcrv-n').'</p>';
								}else{
									echo esc_html__('No types have been added.', 'azrcrv-n');
								}
								?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</fieldset>
	</div>
	
	<div>
		<p>
			<label for="additional-plugins">
				<?php printf(esc_html__('This plugin integrates with the following plugins from %s:', 'azrcrv-n'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
			</label>
			<ul class='azrcrv-plugin-index'>
				<li>
					<?php
					if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){
						echo '<a href="admin.php?page=azrcrv-f" class="azrcrv-plugin-index">Flags</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/" class="azrcrv-plugin-index">Flags</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_n_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){
						echo '<a href="admin.php?page=azrcrv-i" class="azrcrv-plugin-index">Icons</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons" class="azrcrv-plugin-index">Icons</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_n_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){
						echo '<a href="admin.php?page=azrcrv-t" class="azrcrv-plugin-index">Timelines</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/" class="azrcrv-plugin-index">Timelines</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php')){
						echo "<a href='admin.php?page=azrcrv-tsh' class='azrcrv-plugin-index'>Toggle Show/Hide</a>";
					}else{
						echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/' class='azrcrv-plugin-index'>Toggle Show/Hide</a>";
					}
					?>
				</li>
			</ul>
		</p>
	</div>
	<?php
}

/**
 * Save settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_save_options(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'azrcrv-n'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-n', 'azrcrv-n-nonce')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-n');
		
		$option_name = 'maximum-locations';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'location-distance';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'unit-of-distance';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'compass-type';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'enable-flags';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'flag-width';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'enable-toggle-showhide';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'toggle-title';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'timeline-integration';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'timeline-signifier';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'icons-integration';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'icon-visited';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'default-type';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'default-shortcode-types';
		if (is_array($_POST[$option_name])){
			$options[$option_name] = array_map( 'strip_tags', $_POST[$option_name] );
			
		}
		
		// Store updated options array to database
		update_option('azrcrv-n', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-n&settings-updated', admin_url('admin.php')));
		exit;
	}
}

/**
 * Add type.
 *
 * @since 2.4.0
 *
 */
function azrcrv_n_add_type(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'azrcrv-n'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-n-add-type', 'azrcrv-n-nonce-add-type')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-n-types');
		
		$option_name = 'new-type';
		if (isset($options[strtolower(sanitize_text_field($_POST[$option_name]))])){
			$action = 'type-exists';
		}elseif (isset($_POST[$option_name])){
			$options[strtolower(sanitize_text_field($_POST[$option_name]))] = sanitize_text_field($_POST[$option_name]);
		
			// Store updated options array to database
			update_option('azrcrv-n-types', $options);
			$action = 'type-added';
		}
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-n&'.$action.'&types#tabs-3', admin_url('admin.php')));
		exit;
	}
}

/**
 * Delete type.
 *
 * @since 2.4.0
 *
 */
function azrcrv_n_delete_type(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'azrcrv-n'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-n-delete-type', 'azrcrv-n-nonce-delete-type')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-n-types');
		
		$option_name = 'delete-type';
		if (isset($_POST[$option_name])){
			unset($options[strtolower(sanitize_text_field($_POST[$option_name]))]);
		}
		
		// Store updated options array to database
		update_option('azrcrv-n-types', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-n&type-deleted&types#tabs-3', admin_url('admin.php')));
		exit;
	}
}


/**
 * Display shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_displaynearbylocations($atts, $content = null){
	
	global $wpdb;
	global $post; // Get the current post data
	
	$options = azrcrv_n_get_option('azrcrv-n');
	
	// get shortcode attributes
	$args = shortcode_atts(array(
		'type' => implode(',', $options['default-shortcode-types']),
		'title' => $options['toggle-title'],
	), $atts);
	$type = explode(',', strtolower(stripslashes($args['type'])));
	$toggle_title = $args['title'];
	
	$types = get_option('azrcrv-n-types');
	if (is_array($types)){
		$type_count = count($types);
	}else{
		$type_count = 0;
	}
	
	if ($options['unit-of-distance'] == 'km'){
		$units = '* 1.609344';
	}else{
		$units = '';
	}
	
	$post_id = sanitize_key( wp_unslash( $post->ID ) );
	$coordinates = $wpdb->get_var( $wpdb->prepare( "SELECT co_ordinates FROM {$wpdb->prefix}azrcrv_nearby WHERE post_id = %d", $post_id ) );
	
	// nearby attractions
	if (strlen($coordinates) > 0){
		$sql = 
				"SELECT PMO.post_id,ROUND(((((acos(sin((PM.x_co_ordinate * pi()/180)) * sin((PMO.x_co_ordinate * pi()/180))+cos((PM.x_co_ordinate*pi()/180)) * cos((PMO.x_co_ordinate * pi()/180)) * cos(((PM.y_co_ordinate - PMO.y_co_ordinate) * pi()/180))))*180/pi())*60*1.1515)) ".$units.",2) AS DISTANCE
				,(360.0 + 
				  DEGREES(ATAN2(
				   SIN(RADIANS(PMO.y_co_ordinate-PM.y_co_ordinate))*COS(RADIANS(PMO.x_co_ordinate)),
				   COS(RADIANS(PM.x_co_ordinate))*SIN(RADIANS(PMO.x_co_ordinate))-SIN(RADIANS(PM.x_co_ordinate))*COS(RADIANS(PMO.x_co_ordinate))*
						COS(RADIANS(PMO.y_co_ordinate-PM.y_co_ordinate))
				  ))
				 ) % 360.0 AS BEARING
				 , PMO.type
				 , PMO.country
				FROM {$wpdb->prefix}azrcrv_nearby AS PM 
				INNER JOIN $wpdb->posts AS P ON P.ID = PM.post_id AND P.post_status = 'publish' AND P.post_type = 'page'
				INNER JOIN {$wpdb->prefix}azrcrv_nearby AS PMO ON PMO.post_id <> PM.post_id 
				INNER JOIN $wpdb->posts AS PO ON PO.ID = PMO.post_id  AND PO.post_status = 'publish' AND PO.post_type = 'page'
				WHERE 
					PM.post_id = %d 
				AND 
					LENGTH(PM.co_ordinates > 0) 
				AND 
					LENGTH(PMO.co_ordinates > 0) 
				HAVING 
					DISTANCE <= %d";
		
		$sql = $wpdb->prepare($sql, $post_id, $options['location-distance']);
		
		$nearby = array();
		
		$resultset_table = $wpdb->get_results( $sql );
		
		foreach ($resultset_table as $result_table){
						
			$include = false;
			if ($type_count == 0){
				$include = true;
			}elseif (in_array(strtolower($result_table->type), $type)){
				$include = true;
			}
			
			if ($include == true){
				$nearby[$result_table->post_id] = array('distance' => $result_table->DISTANCE, 'bearing' => $result_table->BEARING, 'country' => $result_table->country);
			}
		}
		
		asort($nearby);
		
		$found = 0;
		
		$attractions = '';
		if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php') AND $options['enable-toggle-showhide']){
			$attractions = '<table class="azrcrv_n_toggle">';
		}else{
			$attractions = '<table class="azrcrv_n">';
		}
		$attractions .= '<colgroup><col style="width: 100%-100px; "><col style="width: 100px; align: center; "></colgroup>';
		$attractions .= '<tr><th class="azrcrv_n">Location</th><th class="azrcrv_n">Distance</th><th class="azrcrv_n">Direction</th></tr>';
		
		$max_locations = 50;
		if (isset($options['maximum-locations'])){
			$max_locations = $options['maximum-locations'];
		}
		
		foreach ($nearby as $key => $value)  {
			$attraction = get_page( $key );
			$link = get_permalink( $key );
			$country = '';
			if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php') AND $options['enable-flags'] == 1){
				$country_code = $value['country'];
				if ($country_code != ''){
					$country = azrcrv_f_flag(array( 'id' => $country_code, 'width' => $options['flag-width'].'px',));
				}
			}
			if (isset($options['compass-type']) AND $options['compass-type'] == 16){
				$direction = azrcrv_n_getcompassdirectionsixteen($value['bearing']);
			}else{
				$direction = azrcrv_n_getcompassdirectionthirtytwo($value['bearing']);
			}
			
			$timeline_signifier = '';
			$timeline_signifier_to_use = '';
			if (azrcrv_n_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php') AND $options['timeline-integration'] == 1){
				
				if (azrcrv_n_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){
					if ($options['icon-visited'] != ''){
						$timeline_signifier_to_use = azrcrv_i_icon(array($options['icon-visited']));
					}
				}
				if (strlen($timeline_signifier_to_use) == 0){
					$timeline_signifier_to_use = $options['timeline-signifier'];
				}
				
				if ($options['timeline-integration'] == 1){
					$sql = "SELECT COUNT(pm.meta_value) FROM ".$wpdb->prefix."posts as p INNER JOIN ".$wpdb->prefix."postmeta AS pm ON pm.post_id = p.ID WHERE p.post_status = 'publish' AND p.post_type = 'timeline-entry' AND pm.meta_key = 'timelines_metafields' AND pm.meta_value LIKE '%s'";
					//echo $sql.'<br />';
					
					$timeline_exists = $wpdb->get_var(
											$wpdb->prepare(
												$sql,
												'%'.$link.'%'
											)
										);
					
					//$timeline_exists = $wpdb->get_results( $sql);
					if ($timeline_exists >= 1){
						$timeline_signifier = '&nbsp;'.$timeline_signifier_to_use;
					}else{
						$timeline_signifier = '';
					}
				}
			}
			
			$attractions .= '<tr><td class="azrcrv_n"><a href="'.$link.'">'.$attraction->post_title.' '.$country.'</a>'.$timeline_signifier.'</td><td class="azrcrv_n">'.$value['distance'].' '.$options['unit-of-distance'].'</td><td class="azrcrv_n">'.$direction.'</td></tr>';
			
			$found++;
			if ($found == $max_locations){ break; }
		}
		
		if ($found == 0){
			$attractions .= '<tr><td colspan=3 style="border: 1px solid #38464b; ">No nearby attractions were found.</td></tr>';
		}
		
		$attractions .= '</table>';
		
		if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php') AND $options['enable-toggle-showhide']){
			$output = do_shortcode('[toggle title="'.$toggle_title.'"]'.$attractions.'[/toggle]');
		}else{
			$output = $attractions;
		}
	}
	
	return $output;
}

/**
 * Get 16 point compass direction for location bearing.
 *
 * @since 1.2.0
 *
 */
function azrcrv_n_getcompassdirectionsixteen($bearing) {
	if ($bearing > 348.75 OR $bearing <= 11.25){
		$direction = "N";
	}elseif ($bearing > 11.25 AND $bearing <= 37.5){
		$direction = "NNE";
	}elseif ($bearing > 37.5 AND $bearing <= 56.25){
		$direction = "NE";
	}elseif ($bearing > 56.25 AND $bearing <= 78.75){
		$direction = "ENE";
	}elseif ($bearing > 78.75 AND $bearing <= 101.25){
		$direction = "E";
	}elseif ($bearing > 101.25 AND $bearing <= 123.75){
		$direction = "ESE";
	}elseif ($bearing > 123.75 AND $bearing <= 146.25){
		$direction = "SE";
	}elseif ($bearing > 146.25 AND $bearing <= 168.75){
		$direction = "SSE";
	}elseif ($bearing > 168.75 AND $bearing <= 191.25){
		$direction = "S";
	}elseif ($bearing > 191.25 AND $bearing <= 213.75){
		$direction = "SSW";
	}elseif ($bearing > 213.75 AND $bearing <= 236.25){
		$direction = "SW";
	}elseif ($bearing > 236.25 AND $bearing <= 258.75){
		$direction = "WSW";
	}elseif ($bearing > 258.75 AND $bearing <= 281.25){
		$direction = "W";
	}elseif ($bearing > 281.25 AND $bearing <= 303.75){
		$direction = "WNW";
	}elseif ($bearing > 303.75 AND $bearing <= 326.25){
		$direction = "NW";
	}elseif ($bearing > 326.25 AND $bearing <= 348.75){
		$direction = "NNW";
	}else{
		$direction = $bearing;
	}
	return $direction;
}

/**
 * Get 32 point compass direction for location bearing.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_getcompassdirectionthirtytwo($bearing) {
	if ($bearing > 354.37 OR $bearing <= 5.62){
		$direction = "N";
	}elseif ($bearing > 5.62 AND $bearing <= 16.87){
		$direction = "NbE";
	}elseif ($bearing > 16.87 AND $bearing <= 28.12){
		$direction = "NNE";
	}elseif ($bearing > 28.12 AND $bearing <= 39.37){
		$direction = "NEbN";
	}elseif ($bearing > 39.37 AND $bearing <= 50.62){
		$direction = "NE";
	}elseif ($bearing > 50.62 AND $bearing <= 61.87){
		$direction = "NEbE";
	}elseif ($bearing > 61.87 AND $bearing <= 73.12){
		$direction = "ENE";
	}elseif ($bearing > 73.12 AND $bearing <= 84.37){
		$direction = "EbN";
	}elseif ($bearing > 84.37 AND $bearing <= 95.62){
		$direction = "E";
	}elseif ($bearing > 95.62 AND $bearing <= 106.87){
		$direction = "EbS";
	}elseif ($bearing > 106.87 AND $bearing <= 118.12){
		$direction = "ESE";
	}elseif ($bearing > 118.12 AND $bearing <= 129.37){
		$direction = "SEbE";
	}elseif ($bearing > 129.37 AND $bearing <= 140.62){
		$direction = "SE";
	}elseif ($bearing > 140.62 AND $bearing <= 151.87){
		$direction = "SEbS";
	}elseif ($bearing > 151.87 AND $bearing <= 163.12){
		$direction = "SSE";
	}elseif ($bearing > 163.12 AND $bearing <= 174.37){
		$direction = "SbE";
	}elseif ($bearing > 174.37 AND $bearing <= 185.62){
		$direction = "S";
	}elseif ($bearing > 185.62 AND $bearing <= 198.87){
		$direction = "SbW";
	}elseif ($bearing > 198.87 AND $bearing <= 208.12){
		$direction = "SSW";
	}elseif ($bearing > 208.12 AND $bearing <= 219.37){
		$direction = "SWbS";
	}elseif ($bearing > 219.37 AND $bearing <= 219.37){
		$direction = "SW";
	}elseif ($bearing > 219.37 AND $bearing <= 241.87){
		$direction = "SWbW";
	}elseif ($bearing > 241.87 AND $bearing <= 253.12){
		$direction = "WSW";
	}elseif ($bearing > 253.12 AND $bearing <= 264.37){
		$direction = "WbS";
	}elseif ($bearing > 264.37 AND $bearing <= 275.62){
		$direction = "W";
	}elseif ($bearing > 275.62 AND $bearing <= 286.87){
		$direction = "WbN";
	}elseif ($bearing > 286.87 AND $bearing <= 298.12){
		$direction = "WNW";
	}elseif ($bearing > 298.12 AND $bearing <= 309.37){
		$direction = "NWbW";
	}elseif ($bearing > 309.37 AND $bearing <= 320.62){
		$direction = "NW";
	}elseif ($bearing > 320.62 AND $bearing <= 331.87){
		$direction = "NWbN";
	}elseif ($bearing > 331.87 AND $bearing <= 343.12){
		$direction = "NNW";
	}elseif ($bearing > 343.12 AND $bearing <= 354.37){
		$direction = "NbW";
	}else{
		$direction = $bearing;
	}
	return $direction;
}