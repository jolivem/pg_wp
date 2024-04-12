<?php

class Pg_Geoposts_Table {
    private $plugin_name;
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
    `public` BOOL NULL DEFAULT NULL, 
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

    public static function insert_post($post_id, $latitude, $longitude, $is_exif, $date)
    {
        global $wpdb;
        $table = $wpdb->prefix . "glp_geo_posts";
        error_log("insert_post table=".$table);
        $bexif = 0;
        if ($is_exif == 'true') {
            $bexif = 1;
        }
        //$point = "ST_GeomFromText('POINT($latitude $longitude)')";
        // $query = "INSERT INTO wp_glp_geo_posts (post_id, location) VALUES (%d, ST_PointFromText('%s'), 30, 0, %s, %d)";
        // error_log("insert_post query=".$query);
        // $sql = $wpdb->prepare($query, $post_id, $point, $date, $bexif);
        // $wpdb->query($query);

        //$point = "POINT($latitude $longitude)";
        $point = "POINT(1 2)";
        error_log("insert_post point=".$point);
        //$query = $wpdb->prepare("INSERT INTO $dbTable (post_id, location) VALUES (%d, ST_PointFromText('POINT(%d %d)'))",13, 1, 2);
        $query = $wpdb->prepare("INSERT INTO $table (`post_id`, `location`) VALUES (%d, ST_PointFromText('POINT(%f %f)'))",20,1.2,2.3);
        error_log("insert_post query=".$query);
        $wpdb->query($query);

        // sprintf("POINT(%s,%s)",$latitude, $longitude),
        // error_log("insert_post point=".$point);
        // $result = $wpdb->insert(
        //     $table,
        //     array(
        //         "post_id"           => $post_id,
        //         "location"          => $point,
        //         "zoom_level"        => 30,
        //         "public"            => 0,
        //         "date"              => $date,
        //         "is_exif"           => $bexif
        //     ),
        //     array( "%d", "%s", "%d", "%d", "%s", "%d" ));

        //$wpdb->show_error();
        //$wpdb->print_error();
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_post( $id ) {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}glp_geo_posts",
            array( "id" => $id ),
            array( "%d" )
        );
    }
}