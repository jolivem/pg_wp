<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/public
 * @author     GLP <info@glp-plugin.com>
 */
// TODO le rendu du nomber de colonne ne tient pas compte de la bordure de l'image
// TODO probleme de responsive sur les images
// TODO renommer les fichiers, les variables, les tables, etc..
class Pg_Show_Gallery_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;


    // list of al lpossible countries and their options (file, width, height)
    private $countries = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // $this->settings = new Gallery_Settings_Actions($this->plugin_name);
        add_shortcode( 'pg_show_gallery', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'MarkerCluster', '"https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css', array(), $this->version, 'all' );
        
        wp_enqueue_style( $this->plugin_name."-public.css", plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-map.css", plugin_dir_url( __FILE__ ) . 'css/pg-map.css', array(), $this->version, 'all' );
        //wp_enqueue_style('leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        //wp_enqueue_media();
        wp_enqueue_script( $this->plugin_name.'-glp-public.js', plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        //wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);
        wp_enqueue_script( $this->plugin_name.'-leaflet.js', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet-src.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-markercluster.js', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js', array( 'jquery' ), $this->version, true );

        wp_enqueue_script( $this->plugin_name.'-pg-map.js', plugin_dir_url( __FILE__ ) . 'js/pg-map.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-map.js', 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        //error_log("pg_generate_page aa  IN ".print_r($attr, true));
        error_log("pg_generate_page aa  IN ".print_r($_GET, true));

        // TODO check that the photo belons to the current user
        if (! isset($_GET['gid'])) {
            return "";
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $_GET['gid'] );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }
    
    // attr should have the user id
    public function pg_show_page( $id ){

        error_log("pg_show_page IN id=".$id);
        
        //global $wpdb;

        $gallery = $this->pg_get_gallery_by_id($id);
        if(!$gallery){
            error_log("pg_show_page Gallery not found");
            return "";
        }
        
        $title = $gallery["title"];
        $description = $gallery["description"];
        if(!$gallery){
            error_log("pg_show_page Gallery not found");
            return "";
        }
  
        $medias = $this->pg_get_medias_by_gallery($id);

        if ($medias != null) {
            $html_slider = $this->render_slider($medias);
        }
  
        //$markers_js = $this->define_markers();

        $html_code = "
        <div class='container'>
            <div id='map'></div>
            <div class='slider' id='imageSlider'>
                $html_slider 
            </div>
        </div>";

        $js = $this->script_map($medias);
        $html_code .= $js;

        return $html_code;
    } // end ays_show_galery()

       // render all the images 
    function render_slider($medias){
        //error_log("render_images IN images=".print_r($medias, true));
        $html='';
        // loop for each media
        foreach($medias as $id){
            //error_log("render_images id:".$id);
            //$img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($id, "medium");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }

            $html.="<img src='$img_src' alt='Image 1' class='imgNotSelected'>";
        }
        return $html;
    }
 
    function pg_get_gallery_by_id( $id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }

    private function script_map($medias) {

        // Javascript part
        //$markers_js = "";
        $map_js = "
        <script>

        (function($) {
            'use strict';
            $(window).ready(function(){
               console.log('COUCOU script_map');
               console.log('toto', toto);
               let icon;";

        
        //         var LeafIcon = L.Icon.extend({
        //             options: {
        //                 /*shadowUrl: 'leaf-shadow.png', */
        //                 iconSize:     [60, 60],
        //                 shadowSize:   [50, 64],
        //                 /*iconAnchor:   [22, 94],*/
        //                 shadowAnchor: [4, 62],
        //                 popupAnchor:  [-3, -76],
        //                 className: 'mydivicon'
        //             }
        //         });
        
        //         var markers = L.markerClusterGroup({
        //             zoomToBoundsOnClick: true,
        //             iconCreateFunction: function(cluster) {
        //                 console.log('iconCreateFunction cluster:', cluster);
        
        //                 var children = cluster.getAllChildMarkers()[0];
        
        //                 console.log('icon', children.options.icon);
        //                 var iicoon = new L.Icon(children.options.icon.options);
        //                 var count = cluster.getChildCount();
        //                 if (count < 6) {
        //                     iicoon.options.className = 'mydivmarker6';    
        //                 }
        //                 else if (count < 20) {
        //                     iicoon.options.className = 'mydivmarker9';    
        //                 }
        //                 else {
        //                     iicoon.options.className = 'mydivmarker12';    
        //                 }
        //                 if (imageSelected != null) {
        //                     iicoon.options.iconSize = [100,100];
        //                     iicoon.options.iconUrl = imageSelected;
        //                     imageSelected = null;
        //                     console.log( 'imageSelected = null');
        //                     setTimeout(function(){
        //                         cluster.refreshIconOptions({
        //                             /*shadowUrl: 'leaf-shadow.png',*/
        //                             iconSize:     [60, 60],
        //                         }, true); 
        //                     }, 400);                        
        //                 }
        
        //                 /*iicoon.options.className = 'mydivmarker';*/
        //                 console.log('iicoon', iicoon);
        //                 /*return L.divIcon({ html: '<b>' + cluster.getChildCount() + '</b>' });*/
        //                 return iicoon;
        //             }
        //         }); 
        
        //         var map = L.map('map').setView([45.437859,7.085], 7);
        
        //         L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        //             attribution: '&copy; <a href=\"https://osm.org/copyright\">OpenStreetMap</a> contributors'
        //         }).addTo(map);
        //         let icon;
        //         ";
        
                foreach($medias as $id){
                    //error_log("render_images id:".$id);
                    //$img_src = $item->guid;
                    $url_img = wp_get_attachment_image_src($id, "medium");
                    if ($url_img != false) {
                        $img_src = $url_img[0];
                    
                        $latitude = get_post_meta($id, 'latitude', true);
                        $longitude = get_post_meta($id, 'longitude', true);
                        error_log("latitude=".$latitude."longitude=".$longitude);
                        
                        //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
                        $map_js .= "icon = new LeafIcon({iconUrl: '". $img_src ."'});";
                        $map_js .= "markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}).addTo(map).bindPopup('I am a green leaf.'));";
                    }
                } // end foreach image

                $map_js .= "
                map.addLayer(markers);


            })
        })(jQuery);
        </script>";
        //$map_view .= "";
        //error_log("script_map = [".$map_js."]");
        return $map_js;
    
    }// end ays_add_makers()    

    // private function get_tumbnails_url() {
    //     $thumbnails     = array();
    //     $this_site_path = trim(get_site_url(), "https:");
    //     // TODO get small size for leaflet
    //     $image_sizes = "thumbnail"; // medium_large for gallery
    //     error_log("public image_sizes=".$image_sizes);
    //     foreach($images as $i => $img){
    //         if(strpos(trim($img, "https:"), $this_site_path) !== false){ 
    //             $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `post_type` = 'attachment' AND `guid` = '".$img."'";
    //             $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
    //             if(!empty($result_img)){
    //                 // find the given size
    //                 $url_img = wp_get_attachment_image_src($result_img[0]['ID'], $image_sizes);
    //                 if($url_img === false){
    //                     $thumbnails[] = $img;
    //                 }else{
    //                     $thumbnails[] = $url_img[0];
    //                 }

    //                 // TODO test content of metada
    //                 // $metadata = wp_get_attachment_metadata($result_img[0]['ID']);
    //                 // error_log("image metadata=".print_r($metadata, true));

    //             }else{
    //                 $thumbnails[] = $img;
    //             }                
    //         }else{
    //             $thumbnails[] = $img;
    //         }
    //     }        
    // }

    // $id = gallery id
    // return an array with image IDs
    // TODO, used elsewhere, to be defined in a static method
    public function pg_get_medias_by_gallery( $id ) {
        error_log("pg_get_medias_by_gallery IN gallery_id=".$id);
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";
        $result = $wpdb->get_row( $sql, "ARRAY_A" );
        if ( is_null( $result ) || empty( $result ) ) {
            error_log("pg_get_medias_by_gallery OUT null");
            return null;
        }
        if ( is_null( $result["images_ids"] ) || empty( $result["images_ids"] ) ) {
            error_log("pg_get_medias_by_gallery images_id empty");
            return null;
        }
        $image_ids = explode( "***", $result["images_ids"]);
        return $image_ids;
    }


}
