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
    add_action( 'admin_notices', 'general_gpg_admin_notice' );
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
function general_gpg_admin_notice(){
    if ( isset($_GET['page']) && strpos($_GET['page'], AYS_GALLERY_NAME) !== false ) {
        ?>
            <div class="ays-notice-banner">
                <div class="navigation-bar">
                    <div id="navigation-container">                        
                        <div class="ays-gpg-logo-container-upgrade">
                            <div class="ays-gpg-logo-container">
                                <a href="https://ays-pro.com/wordpress/photo-gallery" target="_blank" style="box-shadow: none;">
                                    <img  class="gpg-logo" src="<?php echo esc_attr(AYS_GPG_ADMIN_URL) . '/images/gallery.png'; ?>" alt="<?php echo __( "Gallery - Photo Gallery", AYS_GALLERY_NAME ); ?>" title="<?php echo __( "Gallery - Photo Gallery", AYS_GALLERY_NAME ); ?>"/>
                                </a>
                            </div>
                            <div class="ays-gpg-upgrade-container">
                                <a href="https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=top-menu-gallery" target="_blank">
                                    <img src="<?php echo esc_attr(AYS_GPG_ADMIN_URL) . '/images/icons/lightning.svg'; ?>">
                                    <img src="<?php echo esc_attr(AYS_GPG_ADMIN_URL) . '/images/icons/lightning-hover.svg'; ?>" class= "ays-gpg-svg-light-hover">
                                    <span><?php echo __( "Upgrade", AYS_GALLERY_NAME ); ?></span>
                                </a>
                                <span class="ays-gpg-logo-container-one-time-text"><?php echo __( "One-time payment", 'photo-gallery' ); ?></span>
                            </div>
                        </div>
                        <ul id="menu">                            
                            <li class="modile-ddmenu-lg"><a class="ays-btn" href="https://ays-demo.com/wordpress-photo-gallery-plugin-free-demo/" target="_blank">Demo</a></li>
                            <li class="modile-ddmenu-lg"><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Free Support</a></li>
                            <li class="modile-ddmenu-xs make_a_suggestion"><a class="ays-btn" href="https://ays-demo.com/gallery-plugin-survey/" target="_blank">Make a Suggestion</a></li>
                            <li class="modile-ddmenu-lg"><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Contact us</a></li>
                            <li class="modile-ddmenu-md">
                                <a class="toggle_ddmenu" href="javascript:void(0);"><i class="fa ays_fa_ellipsis_h"></i></a>
                                <ul class="ddmenu" data-expanded="false">                                
                                    <li><a class="ays-btn" href="https://ays-demo.com/wordpress-photo-gallery-plugin-free-demo/" target="_blank">Demo</a></li>
                                    <li><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Free Support</a></li>
                                    <li><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Contact us</a></li>
                                </ul>
                            </li>
                            <li class="modile-ddmenu-sm">
                                <a class="toggle_ddmenu" href="javascript:void(0);"><i class="fa ays_fa_ellipsis_h"></i></a>
                                <ul class="ddmenu" data-expanded="false">
                                    <li><a class="ays-btn" href="https://ays-demo.com/wordpress-photo-gallery-plugin-free-demo/" target="_blank">Demo</a></li>
                                    <li><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Free Support</a></li>
                                    <li class="make_a_suggestion"><a class="ays-btn" href="https://ays-demo.com/gallery-plugin-survey/" target="_blank">Make a Suggestion</a></li>
                                    <li><a class="ays-btn" href="https://wordpress.org/support/plugin/gallery-photo-gallery/" target="_blank">Contact us</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- <div class="ays_ask_question_content">
                <div class="ays_ask_question_content_inner">
                    <a href="https://wordpress.org/support/plugin/gallery-photo-gallery/" class="ays_gpg_question_link" target="_blank">Ask a question</a>
                    <img src="<?php echo AYS_GPG_ADMIN_URL . '/images/ask-question.png'?>">
                </div>
            </div> -->
        <?php
    }
}

function get_vignette_options() {
    return array(
        'option1' => 'Option 1',
        'option2' => 'Option 2',
        'option3' => 'Option 3',
    );
}

// Add custom field to media edit screen
function add_custom_fields_to_media_edit_screen($form_fields, $post) {
    $latitude_value = get_post_meta($post->ID, '_latitude', true);
    $longitude_value = get_post_meta($post->ID, '_longitude', true);
    $vignette_value = get_post_meta($post->ID, '_vignette', true);


    $form_fields['latitude'] = array(
        'label' => 'Latitude',
        'input' => 'text',
        'value' => $latitude_value,
        'show_in_edit' => true,
    );

    $form_fields['longitude'] = array(
        'label' => 'Longitude',
        'input' => 'text',
        'value' => $longitude_value,
        'show_in_edit' => true,
    );

    $vignette_options = get_vignette_options();

    $vignette_dropdown = '<select name="attachments[' . $post->ID . '][vignette]">';
    foreach ($vignette_options as $key => $label) {
        $vignette_dropdown .= '<option value="' . esc_attr($key) . '" ' . selected($vignette_value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $vignette_dropdown .= '</select>';


    $form_fields['vignette'] = array(
        'label' => 'Vignette',
        'input' => 'text',
        'input' => 'html',
        'html' => $vignette_dropdown,
        'show_in_edit' => true,
    );

    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'add_custom_fields_to_media_edit_screen', 10, 2);

// Save custom field value
function save_custom_fields_value($post, $attachment) {
    if (isset($attachment['latitude'])) {
        update_post_meta($post['ID'], '_latitude', $attachment['latitude']);
    }
    if (isset($attachment['longitude'])) {
        update_post_meta($post['ID'], '_longitude', $attachment['longitude']);
    }

    if (isset($attachment['vignette'])) {
        update_post_meta($post['ID'], '_vignette', $attachment['vignette']);
    }

    return $post;
}
add_filter('attachment_fields_to_save', 'save_custom_fields_value', 10, 2);

run_gallery_photo_gallery();
