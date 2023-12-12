<?php
global $ays_gallery_db_version;
$ays_gallery_db_version = '3.3.6';
/**
 * Fired during plugin activation
 *
 * @link       https://ays-pro.com/
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
 * @author     AYS Pro LLC <info@ays-pro.com>
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
        global $ays_gallery_db_version;
        $installed_ver = get_option( "ays_gallery_db_version" );

        $table = $wpdb->prefix . 'ays_gallery';
        $photo_categories_table  =   $wpdb->prefix . 'ays_gallery_categories';        
        $general_settings_table  =   $wpdb->prefix . 'ays_gallery_settings';        
        $charset_collate = $wpdb->get_charset_collate();
        if($installed_ver != $ays_gallery_db_version) {
            
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
            update_option('ays_gallery_db_version', $ays_gallery_db_version);
        }
	}

    public static function ays_gallery_db_check() {
        global $ays_gallery_db_version;
        if ( get_site_option( 'ays_gallery_db_version' ) != $ays_gallery_db_version ) {
            self::activate();
            self::alter_tables();
        }
            }

    /*private static function alter_tables(){
        global $wpdb;
        $table = $wpdb->prefix . 'ays_gallery';
        
        $ays_gpg_db_version = get_site_option( 'ays_gallery_db_version' );
        $ays_gpg_db_version = explode(".", $ays_gpg_db_version);
        
        if(intval($ays_gpg_db_version[2]) <= 1 && intval($ays_gpg_db_version[1]) <= 3){
            $query = "SELECT * FROM " . $table;
            $ays_gallery_infos = $wpdb->get_results( $query, "ARRAY_A" );
            foreach($ays_gallery_infos as $gal_info){
                $op = json_decode($gal_info['options'], true);
                $lightbox_options = array(                
                    "lightbox_counter"          => !isset($op['lightbox_counter']) ? "true" : $op['lightbox_counter'],
                    "lightbox_autoplay"         => !isset($op['lightbox_autoplay']) ? "true" : $op['lightbox_autoplay'],
                );
                $options = array(
                    'columns_count'             => $op['columns_count'],
                    'view_type'                 => $op['view_type'],
                    'border_radius'             => !isset($op['border_radius']) ? "0" : $op['border_radius'],
                    'admin_pagination'          => !isset($op['admin_pagination']) ? "all" : $op['admin_pagination'],
                    'hover_zoom'                => !isset($op['hover_zoom']) ? "no" : $op['hover_zoom'],
                    'show_gal_title'            => !isset($op['show_gal_title']) ? "on" : $op['show_gal_title'],
                    //'show_gal_desc'             => !isset($op['show_gal_desc']) ? "off" : $op['show_gal_desc'],
                    "images_hover_effect"       => !isset($op['images_hover_effect']) ? "simple" : $op['images_hover_effect'],
                    "hover_dir_aware"           => !isset($op['hover_dir_aware']) ? "slide" : $op['hover_dir_aware'],
                    "images_border"             => !isset($op['images_border']) ? "" : $op['images_border'],
                    "hover_effect"              => $gal_info['hover_effect'],
                    "hover_opacity"             => $gal_info['hover_opacity'],
                    "image_sizes"               => $gal_info['image_sizes'],
                    "lightbox_color"            => $gal_info['lightbox_color'],
                    "images_orderby"            => $gal_info['images_orderby'],
                    "hover_icon"                => $gal_info['hover_icon'],
                    "show_title"                => $gal_info['show_title'],
                    "show_title_on"             => $gal_info['show_title_on'],
                    "title_position"            => $gal_info['title_position'],
                    "show_with_date"            => $gal_info['show_with_date'],
                    "images_distance"           => $gal_info['images_distance'],
                    "images_loading"            => $gal_info['images_loading'],
                );
                $sql = $wpdb->update(
                    $table,
                    array(
                        "title"             => $gal_info['title'],
                        "description"       => $gal_info['description'],
                        "images"            => $gal_info['images'],
                        "images_titles"     => $gal_info['images_titles'],
                        "images_descs"      => $gal_info['images_descs'],
                        "images_alts"       => $gal_info['images_alts'],
                        "images_urls"       => $gal_info['images_urls'],
                        "width"             => $gal_info['width'],
                        "height"            => $gal_info['height'],
                        "options"           => json_encode($options),
                        "lightbox_options"  => json_encode($lightbox_options),
                        "custom_css"        => $gal_info['custom_css'],
                        "images_dates"      => $gal_info['images_dates'],
                        "images_ids"        => $gal_info['images_ids']
                    ),
                    array( "id" => $gal_info['id'] ),
                    array( "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s" ),
                    array( "%d" )
                );
            }
            
            $sql = "ALTER TABLE " . $table . "
                    DROP COLUMN `hover_effect`   ,
                    DROP COLUMN `hover_opacity`  ,
                    DROP COLUMN `image_sizes`    ,
                    DROP COLUMN `lightbox_color` ,
                    DROP COLUMN `images_orderby` ,
                    DROP COLUMN `hover_icon`     ,
                    DROP COLUMN `show_title`     ,
                    DROP COLUMN `show_title_on`  ,
                    DROP COLUMN `title_position` ,
                    DROP COLUMN `show_with_date` ,
                    DROP COLUMN `images_distance`,
                    DROP COLUMN `images_loading` ;";
            $wpdb->query( $sql );
        }
    }*/

}
