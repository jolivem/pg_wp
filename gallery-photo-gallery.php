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
 * @link              https://ays-pro.com/
 * @since             1.0.0
 * @package           GLI_Photo_Gallery
 *
 * @wordpress-plugin
 * Plugin Name:       Gallery - Photo Gallery
 * Plugin URI:        https://ays-pro.com/wordpress/photo-gallery
 * Description:       If you want to be different and represent your photos in a cool way, then our Photo Gallery plugin is perfect for you.
 * Version:           1.0.1
 * Author:            Photo Gallery Team
 * Author URI:        https://ays-pro.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gallery-photo-gallery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( ! defined( 'AYS_GPG_BASE_URL' ) ) {
    define( 'AYS_GPG_BASE_URL', plugin_dir_url(__FILE__ ) );
}

if( ! defined( 'AYS_GPG_DIR' ) )
    define( 'AYS_GPG_DIR', plugin_dir_path( __FILE__ ) );

if( ! defined( 'AYS_GPG_ADMIN_URL' ) ) {
    define( 'AYS_GPG_ADMIN_URL', plugin_dir_url(__FILE__ ) . 'admin/' );
}


if( ! defined( 'AYS_GPG_PUBLIC_URL' ) ) {
    define( 'AYS_GPG_PUBLIC_URL', plugin_dir_url(__FILE__ ) . 'public/' );
}

if( ! defined( 'AYS_GPG_BASENAME' ) )
    define( 'AYS_GPG_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AYS_GALLERY_VERSION', '1.0.1' );
define( 'AYS_GALLERY_NAME_VERSION', '5.3.7' );
define( 'AYS_GALLERY_NAME', 'gallery-photo-gallery' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gallery-photo-gallery-activator.php
 */
function activate_gallery_photo_gallery() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gallery-photo-gallery-activator.php';
	Gallery_Photo_Gallery_Activator::ays_gallery_db_check();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gallery-photo-gallery-deactivator.php
 */
function deactivate_gallery_photo_gallery() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gallery-photo-gallery-deactivator.php';
	Gallery_Photo_Gallery_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gallery_photo_gallery' );
register_deactivation_hook( __FILE__, 'deactivate_gallery_photo_gallery' );

add_action( 'plugins_loaded', 'activate_gallery_photo_gallery' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gallery-photo-gallery.php';


require plugin_dir_path( __FILE__ ) . 'gallery/gallery-photo-gallery-block.php';
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
    // add_action( 'activated_plugin', 'gallery_p_gallery_activation_redirect_method' );
    //add_action( 'admin_notices', 'general_gpg_admin_notice' );
	$plugin = new Gallery_Photo_Gallery();
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

function gallery_p_gallery_activation_redirect_method( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'admin.php?page=' . AYS_GALLERY_NAME ) ) );
    }
}

run_gallery_photo_gallery();
