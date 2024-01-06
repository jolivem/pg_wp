<?php
global $glp_gallery_db_version;
$glp_gallery_db_version = '3.3.6';
/**
 * Fired during plugin activation
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Gallery_Photo_Gallery
 * @subpackage Gallery_Photo_Gallery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gallery_Photo_Gallery
 * @subpackage Gallery_Photo_Gallery/includes
 * @author     AYS Pro LLC <info@glp-plugin.com>
 */

class Gallery_Photo_Gallery_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        global $glp_gallery_db_version;
        $installed_ver = get_option( "glp_gallery_db_version" );

        $table = $wpdb->prefix . 'glp_gallery';
        $photo_categories_table  =   $wpdb->prefix . 'glp_gallery_categories';        
        $general_settings_table  =   $wpdb->prefix . 'glp_gallery_settings';        
        $charset_collate = $wpdb->get_charset_collate();
        if($installed_ver != $glp_gallery_db_version) {
            
            $sql = "CREATE TABLE `" . $table . "` (
                  `id` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `title` VARCHAR(256) NOT NULL,
                  `description` TEXT NOT NULL,
                  `images` TEXT NOT NULL,
                  `images_titles` TEXT NOT NULL, 
                  `images_descs` TEXT NOT NULL,
                  `images_alts` TEXT NOT NULL,
                  `images_urls` TEXT NOT NULL,
                  `categories_id` TEXT NOT NULL,
                  `width` INT(16) NOT NULL,
                  `height` INT NOT NULL,
                  `options` TEXT NOT NULL,
                  `lightbox_options` TEXT NOT NULL,
                  `custom_css` TEXT NOT NULL,
                  `images_dates` TEXT NOT NULL,
                  `images_ids` TEXT NOT NULL,
                  PRIMARY KEY (`id`)
                )$charset_collate;";
            dbDelta( $sql );

            $sql = "CREATE TABLE `" . $general_settings_table . "` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `meta_key` TEXT NULL DEFAULT NULL,
                      `meta_value` TEXT NULL DEFAULT NULL,
                      `note` TEXT NULL DEFAULT NULL,
                      `options` TEXT NULL DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )".$charset_collate.";";

            $sql_schema = "SELECT * FROM INFORMATION_SCHEMA.TABLES
                           WHERE table_schema = '".DB_NAME."' AND 
                           table_name = '".$general_settings_table."' ";
            $res = $wpdb->get_results($sql_schema);

            if(empty($res)){
                $wpdb->query( $sql );
            }else{
                dbDelta( $sql );
            }
            
            $sql = "CREATE TABLE `".$photo_categories_table."` (
                `id` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(256) NOT NULL,
                `description` TEXT NOT NULL,
                PRIMARY KEY (`id`)
            )$charset_collate;";

            $sql_schema = "SELECT * 
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema = '".DB_NAME."' 
                        AND table_name = '".$photo_categories_table."' ";
            $res = $wpdb->get_results($sql_schema);

            if(empty($res)){
                $wpdb->query( $sql );
            }else{
                dbDelta( $sql );
            }
            update_option('glp_gallery_db_version', $glp_gallery_db_version);
        }
	}

    public static function ays_gallery_db_check() {
        global $glp_gallery_db_version;
        if ( get_site_option( 'glp_gallery_db_version' ) != $glp_gallery_db_version ) {
            self::activate();
        }
    }
}
