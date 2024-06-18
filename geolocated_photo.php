<?php
ob_start();
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://glp-plugin.com/
 * @since             1.0.0
 * @package           GLI_Photo_Gallery
 *
 * @wordpress-plugin
 * Plugin Name:       Planet Gallery
 * Plugin URI:        https://glp-plugin.com/wordpress/photo-gallery
 * Description:       Plugin for Planet-Gallery.org.
 * Version:           1.1.10
 * Author:            Planet Gallery Team
 * Author URI:        https://glp-plugin.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       geolocated-photo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( ! defined( 'GLP_BASE_URL' ) ) {
    define( 'GLP_BASE_URL', plugin_dir_url(__FILE__ ) );
}

if( ! defined( 'GLP_DIR' ) )
    define( 'GLP_DIR', plugin_dir_path( __FILE__ ) );

if( ! defined( 'GLP_ADMIN_URL' ) ) {
    define( 'GLP_ADMIN_URL', plugin_dir_url(__FILE__ ) . 'admin/' );
}


if( ! defined( 'GLP_PUBLIC_URL' ) ) {
    define( 'GLP_PUBLIC_URL', plugin_dir_url(__FILE__ ) . 'public/' );
}

if( ! defined( 'GLP_BASENAME' ) )
    define( 'GLP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GLP_GALLERY_VERSION', '1.1.10' );
define( 'GLP_GALLERY_NAME_VERSION', 'V1.1.10' );
define( 'GLP_GALLERY_NAME', 'geolocated-photo' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/geolocated-photo-activator.php
 */
function activate_gallery_photo_gallery() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/geolocated-photo-activator.php';
	Geolocated_Photo_Activator::ays_gallery_db_check();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/glp-categories-list-table-deactivator.php
 */
function deactivate_gallery_photo_gallery() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/geolocated-photo-deactivator.php';
	Geolocated_Photo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gallery_photo_gallery' );
register_deactivation_hook( __FILE__, 'deactivate_gallery_photo_gallery' );

add_action( 'plugins_loaded', 'activate_gallery_photo_gallery' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/geolocated-photo-core.php';


require plugin_dir_path( __FILE__ ) . 'gallery/glp-block.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gallery_photo_gallery() {
    // add_action( 'activated_plugin', 'glp_gallery_activation_redirect_method' );
    //add_action( 'admin_notices', 'general_gpg_admin_notice' );
	$plugin = new Geolocated_Photo();
	$plugin->run();

}

function gpg_get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function glp_gallery_activation_redirect_method( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'admin.php?page=' . GLP_GALLERY_NAME ) ) );
    }
}

// Hook into profile_update event
add_action('profile_update', 'pg_profile_update', 10, 2);

// Hook into user_register event
add_action('user_register', 'pg_user_register', 10, 1);

/**
 * Function to handle profile update
 *
 * @param int $user_id The ID of the user being updated.
 * @param WP_User $old_user_data The old user object.
 */
function pg_profile_update($user_id, $old_user_data) {
    // Your custom code here
    // For example, logging the update
    //error_log("User with ID $user_id has updated their profile.");
    //error_log("old_user_data ".print_r($old_user_data->data->user_url, true));

    $new_user_data = get_userdata($user_id);
    //error_log("new_user_data ".print_r($new_user_data->data->user_url, true));
    if ($new_user_data->data->user_url != $old_user_data->data->user_url)
    {
        update_user_meta( $user_id, 'user_url', 'to_be_checked');
    }

}

/**
 * Function to handle user registration
 *
 * @param int $user_id The ID of the newly registered user.
 */
function pg_user_register($user_id) {
    // Your custom code here
    // For example, sending a welcome email
    $user_info = get_userdata($user_id);

    error_log("pg_user_register data ".print_r($user_info->data, true));
    if ($user_info->data->user_url != ''){
        update_user_meta( $user_id, 'user_url', 'to_be_checked');
    }
}

function my_custom_404() {
    // Set the status to 404
    error_log("my_custom_404 IN");


    // Set the HTTP status header to 404
    status_header(404);

    // Construct the file path to the custom template in the plugin directory
    $template = plugin_dir_path(__FILE__) . 'public/pg-404.php';

    // Check if the custom template file exists
    if (file_exists($template)) {
        include($template);
    } else {
        // Fallback to the default 404 template
        $default_404_template = get_404_template();
        if ($default_404_template) {
            include($default_404_template);
        }
    }

    // Exit to ensure no further processing
    exit;


}


run_gallery_photo_gallery();
