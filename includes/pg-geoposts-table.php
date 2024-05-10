<?php

class Pg_Geoposts_Table {
    private $plugin_name;

    const PUBLIC_HIDDEN = 0;
    const PUBLIC_VISIBLE = 1;

    private $title_length;

    /** Class constructor */
    public function __construct($plugin_name) 
    {
        $this->plugin_name = $plugin_name;
    }

/*
    `id` INT(16) NOT NULL AUTO_INCREMENT,
    `post_id` BIGINT(20) NOT NULL,
    `location` POINT NOT NULL,
    `zoom_level` INT NULL DEFAULT NULL, do not display if ZOOM < value
    `visible` BOOL NULL DEFAULT NULL, 
    `rating` INT NULL DEFAULT NULL,
    `date` DATE NULL DEFAULT NULL,
    `is_exif` BOOL NOT NULL,
    SPATIAL INDEX(location),
    PRIMARY KEY (`id`)
*/

    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_by_post_id( $id) 
    {

        global $wpdb;
        $table = $wpdb->prefix . "glp_geo_posts";
        $id = absint( sanitize_text_field( $id ));
        $sql = "SELECT * FROM ".$table." WHERE post_id = %d";

        $result = $wpdb->get_row(
                    $wpdb->prepare( $sql, $id)
                  , "ARRAY_A");

        return $result;
    }

    public static function insert_post(int $post_id, float $latitude, float $longitude, string $is_exif, bool $date)
    {
        global $wpdb;

        error_log("insert_post post_id=$post_id, latitude=$latitude, longitude=$longitude, is_exif=$is_exif, date=$date");

        $table = $wpdb->prefix . "glp_geo_posts";
        error_log("insert_post table=".$table);
        $bexif = 0;
        if ($is_exif == 'true') {
            $bexif = 1;
        }

        $query = $wpdb->prepare("INSERT INTO $table (`post_id`, `location`, `visible`, `date`, `is_exif`) 
            VALUES (%d, ST_PointFromText('POINT(%f %f)'), %d, %s, %d)",
            $post_id, $latitude, $longitude, self::PUBLIC_HIDDEN, $date, $bexif);
        
            error_log("insert_post query=".$query);
        $wpdb->query($query);

    }

    public static function update_visible(int $post_id, int $visible)
    {
        global $wpdb;

        //$visible = 0;
        if ($visible != self::PUBLIC_VISIBLE && $visible != self::PUBLIC_HIDDEN) {
            return;
        }
        error_log("update_visible post_id=$post_id, visible=$visible");


        $table = $wpdb->prefix . "glp_geo_posts";
        //error_log("update_visible table=".$table);

        $gallery_result = $wpdb->update(
            $table,
            array( "visible" => $visible),
            array("post_id" => $post_id),
            array( "%d" ),
            array( "%d" )
        );
    }

    /**
     * Delete an image.
     *
     * @param int $id post ID
     */
    public static function delete_post( int $id ) {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}glp_geo_posts",
            array( "post_id" => $id ),
            array( "%d" )
        );
    }

    public static function get_all_public_images() 
    {

        global $wpdb;
        $table = $wpdb->prefix . "glp_geo_posts";
        $sql = "SELECT post_id FROM ".$table." WHERE visible = 1";

        $result = $wpdb->get_results( $sql, "ARRAY_A");
        error_log("get_all_public_images: ".print_r($result, true));
        return $result;
    }

    public static function get_boundingbox_images(float $ne_lat, float $ne_lng, float $sw_lat, float $sw_lng, int $zoom)
    {

        global $wpdb;
        $table = $wpdb->prefix . "glp_geo_posts";
        $query = $wpdb->prepare("SELECT post_id FROM ".$table." WHERE (visible = 1 and ST_within(location, 
            ST_GeomFromText('POLYGON((%f %f, %f %f, %f %f, %f %f, %f %f))')))",
            $sw_lat, $sw_lng,
            $ne_lat, $sw_lng,
            $ne_lat, $ne_lng,
            $sw_lat, $ne_lng,
            $sw_lat, $sw_lng);

        $result = $wpdb->get_results( $query, "ARRAY_A");
        //error_log("get_boundingbox_images: ".print_r($result, true));
        return $result;
    }

}