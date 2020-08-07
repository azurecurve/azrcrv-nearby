<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Nearby
 * Description: Creates table of nearby locations based on GPS co-ordinates.
 * Version: 1.0.0
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/nearby/
 * Text Domain: nearby
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
// add actions
add_action('admin_init', 'azrcrv_n_set_default_options');
add_action('admin_menu', 'azrcrv_n_create_admin_menu');
add_action('admin_post_azrcrv_n_save_options', 'azrcrv_n_save_options');
//add_action('wp_enqueue_scripts', 'azrcrv_n_load_css');
add_action('the_posts', 'azrcrv_n_check_for_shortcode');
add_action('plugins_loaded', 'azrcrv_n_load_languages');
add_action( 'add_meta_boxes', 'azrcrv_n_create_details_metabox' );
add_action( 'save_post', 'azrcrv_n_save_details_metabox', 1, 2 );
add_action( 'save_post', 'azrcrv_n_save_details_revisions' );

// add filters
add_filter('plugin_action_links', 'azrcrv_n_add_plugin_action_link', 10, 2);
add_filter( '_wp_post_revision_fields', 'azrcrv_n_get_details_revisions_fields' );
add_filter( '_wp_post_revision_field_my_meta', 'azrcrv_n_display_details_revisions_fields', 10, 2 );

// add shortcodes
add_shortcode('nearby', 'azrcrv_n_displaynearbylocations');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('nearby', false, $plugin_rel_path);
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
 * Set default options for plugin.
 *
 * @since 1.0.0
 *
 */
function azrcrv_n_set_default_options($networkwide){
	
	$option_name = 'azrcrv-n';
	
	$new_options = array(
						'maximim-locations' => 20,
						'location-distance' => 200,
						'unit-of-distance' => 'miles',
						'enable-flags' => 0,
						'enable-toggle-showhide' => 0,
						'toggle-title' => 'Nearby Locations',
						'updated' => strtotime('2020-08-05'),
			);
	
	// set defaults for multi-site
	if (function_exists('is_multisite') && is_multisite()){
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide){
			global $wpdb;

			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			$original_blog_id = get_current_blog_id();

			foreach ($blog_ids as $blog_id){
				switch_to_blog($blog_id);
				
				azrcrv_n_update_options($option_name, $new_options, false);
			}

			switch_to_blog($original_blog_id);
		}else{
			azrcrv_n_update_options( $option_name, $new_options, false);
		}
		if (get_site_option($option_name) === false){
			azrcrv_n_update_options($option_name, $new_options, true);
		}
	}
	//set defaults for single site
	else{
		azrcrv_n_update_options($option_name, $new_options, false);
	}
}

/**
 * Update options.
 *
 * @since 1.1.3
 *
 */
function azrcrv_n_update_options($option_name, $new_options, $is_network_site){
	if ($is_network_site == true){
		if (get_site_option($option_name) === false){
			add_site_option($option_name, $new_options);
		}else{
			$options = get_site_option($option_name);
			if (!isset($options['updated']) OR $options['updated'] < $new_options['updated'] ){
				$options['updated'] = $new_options['updated'];
				update_site_option($option_name, azrcrv_n_update_default_options($options, $new_options));
			}
		}
	}else{
		if (get_option($option_name) === false){
			add_option($option_name, $new_options);
		}else{
			$options = get_option($option_name);
			if (!isset($options['updated']) OR $options['updated'] < $new_options['updated'] ){
				$options['updated'] = $new_options['updated'];
				update_option($option_name, azrcrv_n_update_default_options($options, $new_options));
			}
		}
	}
}

/**
 * Add default options to existing options.
 *
 * @since 1.1.3
 *
 */
function azrcrv_n_update_default_options( &$default_options, $current_options ) {
    $default_options = (array) $default_options;
    $current_options = (array) $current_options;
    $updated_options = $current_options;
    foreach ($default_options as $key => &$value) {
        if (is_array( $value) && isset( $updated_options[$key])){
            $updated_options[$key] = azrcrv_n_update_default_options($value, $updated_options[$key]);
        } else {
			$updated_options[$key] = $value;
        }
    }
    return $updated_options;
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
		$settings_link = '<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=azrcrv-n"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'nearby').'</a>';
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
 * Create the metabox
 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
 */
function azrcrv_n_create_details_metabox() {

	// Can only be used on a single post type (ie. page or post or a custom post type).
	// Must be repeated for each post type you want the metabox to appear on.
	add_meta_box(
		'azrcrv_n_metabox_details', // Metabox ID
		'GPS Co-ordinates', // Title to display
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
	global $post; // Get the current post data
	$azrcrv_n_coordinates = get_post_meta( $post->ID, '_azrcrv_n_coordinates', true ); // Get the saved values
	$azrcrv_n_country = get_post_meta( $post->ID, '_azrcrv_n_country', true ); // Get the saved values
	?>

		<fieldset>
			<div>
				<table style="width: 100%; border-collapse: collapse;">
					<tr>
						<td style="width: 150px;">
							<label for="azrcrv_n_coordinates">
								<?php
									_e( 'Co-ordinates', 'nearby' );
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
									_e( 'Format of co-ordinates is longitude, latitude (e.g. 51.477800, -0.001400).', 'nearby' );
								?>
						</td>
					</tr>
					<?php
						if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){ ?>
							<tr>
								<td style="width: 150px;">
									<label for="azrcrv_n_country">
										<?php
											_e( 'Country', 'nearby' );
										?>
									</label>
								</td>
								<td style="width: 100%-150px;">
									<select name="azrcrv_n_country">
										<option value="" <?php if($azrcrv_n_country == ""){ echo ' selected="selected"'; } ?>></option>
										<?php
											$dir = plugin_dir_path(__dir__).'/azrcrv-flags/images';
											if (is_dir($dir)){
												if ($directory = opendir($dir)){
													while (($file = readdir($directory)) !== false){
														if ($file != '.' and $file != '..' and $file != 'Thumbs.db'){
															$filewithoutext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file); ?>
															<option value="<?php echo $filewithoutext ?>" <?php if($azrcrv_n_country == $filewithoutext){ echo ' selected="selected"'; } ?>><?php echo $filewithoutext; ?></option><?php
														}
													}
													closedir($directory);
												}
											}
										?>
									</select>
								</td>
							</tr>
						<?php }
					?>
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
	/**
	 * Sanitize the submitted data
	 */
	$azrcrv_n_coordinates = wp_filter_post_kses( $_POST['azrcrv_n_coordinates'] );
	// Save our submissions to the database
	update_post_meta( $post->ID, '_azrcrv_n_coordinates', $azrcrv_n_coordinates );
	/**
	 * Sanitize the submitted data
	 */
	$azrcrv_n_country = wp_filter_post_kses( $_POST['azrcrv_n_country'] );
	// Save our submissions to the database
	update_post_meta( $post->ID, '_azrcrv_n_country', $azrcrv_n_country );

}

/**
 * Save events data to revisions
 * @param  Number $post_id The post ID
 */
function azrcrv_n_save_details_revisions( $post_id ) {

	// Check if it's a revision
	$parent_id = wp_is_post_revision( $post_id );

	// If is revision
	if ( $parent_id ) {

		// Get the saved data
		$parent = get_post( $parent_id );
		$details = get_post_meta( $parent->ID, 'azrcrv_n', true );

		// If data exists and is an array, add to revision
		if ( !empty( $details ) ) {
			add_metadata( 'post', $post_id, 'azrcrv_n', $details );
		}

	}

}

/**
 * Restore events data with post revisions
 * @param  Number $post_id     The post ID
 * @param  Number $revision_id The revision ID
 */
function azrcrv_n_restore_details_revisions( $post_id, $revision_id ) {

	// Variables
	$post = get_post( $post_id ); // The post
	$revision = get_post( $revision_id ); // The revision
	$details = get_metadata( 'post', $revision->ID, 'azrcrv_n', true ); // The historic version

	// Replace our saved data with the old version
	update_post_meta( $post_id, 'azrcrv_n', $details );

}
add_action( 'wp_restore_post_revision', 'azrcrv_n_restore_details_revisions', 10, 2 );

/**
 * Get the data to display on the revisions page
 * @param  Array $fields The fields
 * @return Array The fields
 */
function azrcrv_n_get_details_revisions_fields( $fields ) {
	// Set a title
	$fields['azrcrv_n'] = 'Some Item';
	return $fields;
}

/**
 * Display the data on the revisions page
 * @param  String|Array $value The field value
 * @param  Array        $field The field
 */
function azrcrv_n_display_details_revisions_fields( $value, $field ) {
	global $revision;
	return get_metadata( 'post', $revision->ID, $field, true );
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
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'nearby'));
    }
	
	// Retrieve plugin configuration options from database
	$options = get_option('azrcrv-n');
	?>
	<div id="azrcrv-n-general" class="wrap">
		<fieldset>
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<?php if(isset($_GET['settings-updated'])){ ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e('Settings have been saved.', 'nearby'); ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="azrcrv_n_save_options" />
				<input name="page_options" type="hidden" value="copyright" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field('azrcrv-n', 'azrcrv-n-nonce'); ?>
				<table class="form-table">
				
					<tr><th scope="row"><label for="nearby"><?php esc_html_e('Maximum Locations', 'nearby'); ?></label></th><td>
						<input name="maximim-locations" type="number" step="1" min="1" id="maximim-locations" value="<?php echo stripslashes($options['maximim-locations']); ?>" class="small-text" /> locations</td>
					</td></tr>
					
					<tr><th scope="row"><label for="nearby"><?php esc_html_e('Location Distance', 'nearby'); ?></label></th><td>
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
									
					<tr>
						<th scope="row">
							<label for="enable-flags"><?php esc_html_e('Integrate with Flags from azurecurve', 'nearby'); ?></label></th>
						<td>
							<?php
								if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){ ?>
									<label for="enable-flags"><input name="enable-flags" type="checkbox" id="enable-flags" value="1" <?php checked('1', $options['enable-flags']); ?> /><?php _e('Enable integration with azurecurve Flags?', 'to-twitter'); ?></label>
								<?php }else{
									echo esc_html_e('Flags from azurecurve not installed/activated.', 'nearby');
								}
								?>
						</td>
					</tr>
									
					<tr>
						<th scope="row">
							<label for="enable-toggle-showhide"><?php esc_html_e('Integrate with Toggle Show/Hide from azurecurve', 'nearby'); ?></label></th>
						<td>
							<?php
								if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php')){ ?>
									<label for="enable-toggle-showhide"><input name="enable-toggle-showhide" type="checkbox" id="enable-toggle-showhide" value="1" <?php checked('1', $options['enable-toggle-showhide']); ?> /><?php _e('Enable integration with azurecurve Toggle Show/Hide?', 'to-twitter'); ?></label>
								<?php }else{
									echo esc_html_e('Toggle Show/Hide from azurecurve not installed/activated.', 'nearby');
								}
								?>
						</td>
					</tr>
					
					<?php
						if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php')){ ?>
							<tr><th scope="row"><label for="toggle-title"><?php esc_html_e('Toggle Title', 'nearby'); ?></label></th><td>
								<input name="toggle-title" type="text" step="1" min="1" id="toggle-title" value="<?php echo stripslashes($options['toggle-title']); ?>" class="regular-text" /></td>
							</td></tr>
						<?php }
					?>
					
					<tr><th scope="row" colspan=2>
						<ul class='azrcrv-plugin-index'>
							<li>
								<?php
								if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){
									echo "<a href='admin.php?page=azrcrv-f' class='azrcrv-plugin-index'>Flags</a>";
								}else{
									echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/flags/' class='azrcrv-plugin-index'>Flags</a>";
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
					</th></tr>
				
				</table>
				<input type="submit" value="Save Changes" class="button-primary"/>
			</form>
		</fieldset>
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
		wp_die(esc_html__('You do not have permissions to perform this action', 'nearby'));
	}
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-n', 'azrcrv-n-nonce')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-n');
		
		$option_name = 'maximim-locations';
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
		
		$option_name = 'enable-flags';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
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
		
		// Store updated options array to database
		update_option('azrcrv-n', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-n&settings-updated', admin_url('admin.php')));
		exit;
	}
}

function azrcrv_n_displaynearbylocations($atts, $content = null){
	
	global $wpdb;
	global $post; // Get the current post data
	
	$options = get_option('azrcrv-n');
	
	if ($options['unit-of-distance'] == 'km'){
		$units = '* 1.609344';
	}else{
		$units = '';
	}
	
	$coordinates = get_post_meta( $post->ID, '_azrcrv_n_coordinates', true ); // Get the saved values
	
	// nearby attractions
	if (strlen($coordinates) > 0){
		$sql = 
				"SELECT PMO.post_id,ROUND(((((acos(sin((TRIM(SUBSTRING_INDEX(PM.meta_value, ',', 1)) * pi()/180)) * sin((TRIM(SUBSTRING_INDEX(PMO.meta_value, ',', 1)) * pi()/180))+cos((TRIM(SUBSTRING_INDEX(PM.meta_value, ',', 1))*pi()/180)) * cos((TRIM(SUBSTRING_INDEX(PMO.meta_value, ',', 1)) * pi()/180)) * cos(((TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(PM.meta_value, ',', 2), ',', -1)) - TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(PMO.meta_value, ',', 2), ',', -1))) * pi()/180))))*180/pi())*60*1.1515)) ".$units.",2) AS DISTANCE 
				FROM ".$wpdb->prefix."postmeta AS PM 
				INNER JOIN ".$wpdb->prefix."posts AS P ON P.ID = PM.post_id AND P.post_status = 'publish' AND P.post_type = 'page'
				INNER JOIN ".$wpdb->prefix."postmeta AS PMO ON PMO.meta_key = PM.meta_key AND PMO.post_id <> PM.post_id 
				INNER JOIN ".$wpdb->prefix."posts AS PO ON PO.ID = PMO.post_id  AND PO.post_status = 'publish' AND PO.post_type = 'page'
				WHERE 
					PM.post_id = %d 
				AND 
					PM.meta_key = '_azrcrv_n_coordinates' 
				AND 
					LENGTH(PM.meta_value > 0) 
				AND 
					LENGTH(PMO.meta_value > 0) 
				HAVING 
					DISTANCE <= ".$options['location-distance'];
					
		$sql = $wpdb->prepare($sql, $post->ID);
		
		//echo $sql.'<p />';
		$nearby = array();
		
		$resultset_table = $wpdb->get_results( $sql );
		
		foreach ($resultset_table as $result_table){
			$nearby[$result_table->post_id] = $result_table->DISTANCE;
		}
		
		asort($nearby);
		
		$found = 0;
		
		$attractions = '';
		$attractions = '<table class="azrcrv_n">';
		$attractions .= '<colgroup><col style="width: 100%-100px; "><col style="width: 100px; align: center; "></colgroup>';
		$attractions .= '<tr><th class="azrcrv_n">Location</th><th class="azrcrv_n">Distance</th></tr>';
		
		foreach ($nearby as $key => $value)  {
			$attraction = get_page( $key );
			$link = get_permalink( $key );
			if (azrcrv_n_is_plugin_active('azrcrv-flags/azrcrv-flags.php') AND $options['enable-flags'] == 1){
				$country = do_shortcode('[flag='.get_post_meta( $key, '_azrcrv_n_country', true ).']');
			}else{
				$country = '';
			}
			$attractions .= '<tr><td class="azrcrv_n"><a href="'.$link.'">'.$attraction->post_title.' '.$country.'</a></td><td style="border: 1px solid #38464b; ">'.$value.' '.$options['unit-of-distance'].'</td></tr>';
			$found++;
			if ($found == $options['maximim-locations']){ break; }
		}
		
		if ($found == 0){
			$attractions .= '<tr><td colspan=2 style="border: 1px solid #38464b; ">No nearby attractions were found.</td></tr>';
		}
		
		$attractions .= '</table>';
		
		if (azrcrv_n_is_plugin_active('azrcrv-toggle-showhide/azrcrv-toggle-showhide.php') AND $options['enable-toggle-showhide']){
			$output = do_shortcode('[toggle title="'.$options['toggle-title'].'"]'.$attractions.'[/toggle]');
		}else{
			$output = $attractions;
		}
	}
	
	return $output;
}