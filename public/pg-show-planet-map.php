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
class Pg_Show_Planet_Map_Public {

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
        add_shortcode( 'pg_show_planet_map', array($this, 'pg_generate_page') );
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
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');

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
        error_log("Pg_Show_Planet_Map_Public::pg_generate_page IN ".print_r($attr, true));
 
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page();

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }
    
    // attr should have the user id
    public function pg_show_page(){

        error_log("pg_show_page IN");
        
        //global $wpdb;
  
        //$medias = $this->pg_get_medias_by_gallery($id);
        $medias = Pg_Geoposts_Table::get_all_public_images();
        //error_log("pg_show_page ".print_r($medias, true));
        $html_slider = $this->render_slider($medias);
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('show_planet');

  
        //$markers_js = $this->define_markers();

        $html_code = "
        <input type='hidden' id='page_nonce' value='$nonce'/>
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <div class='container'>
            <form id='searchForm' class='search-place'>
                <input type='text' class='form-control' id='searchInput' placeholder='Entrez un lieu ou une adresse'>
                <button type='button' id='searchButton' class='btn btn-primary'>Rechercher</button>
            </form>
            <div id='map' style='height:300px;'></div>
            <div class='flex-container'>
                <div class='slider-options-left' style='background-color: lightgreen'>
                    <div>
                        <div class='show-gallery-option fas fa-step-backward' style='padding-bottom:38px;' aria-hidden='true'></div>
                        <div class='show-gallery-option fas fa-angle-double-left' aria-hidden='true'></div>
                    </div>
                </div>
                <div class='planet-slider' id='imageSlider'>
                    $html_slider 
                </div>
                <div class='slider-options-right' style='background-color: lightgreen'>
                    <div>
                        <div class='show-gallery-option fas fa-step-forward' style='padding-bottom:38px;' aria-hidden='true'></div>
                        <div class='show-gallery-option fas fa-angle-double-right' aria-hidden='true'></div>
                    </div>
                </div>
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
        foreach($medias as $media){
            error_log("render_images id:".$media['post_id']);
            //$img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($media['post_id'], "medium");
            if ($url_img != false) {
                $html.="<img src='$url_img[0]' alt='Image 1' class='imgNotSelected'>";
            }
        }
        return $html;
    }
 
    private function script_map($medias) {

        // Javascript part
        //$markers_js = "";
        $map_js = "
        <script>

        (function($) {
            'use strict';
            $(window).ready(function(){

                map.setView([0,0], 1);
                /*console.log('COUCOU script_map');
                map.on('movestart', function(e) {
                    console.log('movestart',e);
                });
                map.on('zoomstart', function(e) {
                    console.log('zoomstart',e);
                });*/
                map.on('moveend', function(e) {
                    console.log('movend',e);
                    console.log('movend',map.getBounds());
                    const ne = map.getBounds().getNorthEast();
                    const sw = map.getBounds().getSouthWest();
                    const zoom = map.getZoom();
                    getImagesFromBB(ne.lat, ne.lng, sw.lat, sw.lng, zoom);
                    /*console.log('movend',map.getZoom());*/
                });
                map.on('zoomend', function(e) {
                    console.log('zoomend',e);
                    console.log('zoomST_end',map.getBounds());
                    getImagesFromBB(map.getBounds().getNorthEast(), map.getBounds().getSouthWest());
                });
                let icon;";
 
         
                $minlat = 90.0; 
                $maxlat = -90.0;
                $minlng = 180.0;
                $maxlng = -180.0;
        
                foreach($medias as $media){
                    $id = $media['post_id'];
                    error_log("render_images id:".$id);
                    //$img_src = $item->guid;
                    $url_img = wp_get_attachment_image_src($id, "medium");
                    if ($url_img != false) {
                        $img_src = $url_img[0];
                    
                        $latitude = get_post_meta($id, 'latitude', true);
                        $longitude = get_post_meta($id, 'longitude', true);
                        error_log("latitude=".$latitude."longitude=".$longitude);
                        if ($latitude && $longitude) {

                            // keep min and max
                            $minlat = min($minlat, $latitude);
                            $maxlat = max($maxlat, $latitude);
                            $minlng = min($minlng, $longitude);
                            $maxlng = max($maxlng, $longitude);
                            error_log("minlat=".$minlat.", maxlat=".$maxlat.",minlng=".$minlng.", maxlng=".$maxlng);
        
                            
                            //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
                            $map_js .= "icon = new LeafIcon({iconUrl: '". $img_src ."'});";
                            $map_js .= "markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}).addTo(map).bindPopup('I am a green leaf.'));";
                        }
                    }
                } // end foreach image

                $map_js .= "
                map.addLayer(markers);
                /*const bbox = [[$minlat,$minlng],[$maxlat,$maxlng]];
                L.rectangle(bbox).addTo(map);
                map.fitBounds(bbox);*/

            })
        })(jQuery);
        </script>";
        //$map_view .= "";
        //error_log("script_map = [".$map_js."]");
        return $map_js;
    
    }// end ays_add_makers()    

 
    // $id = gallery id
    // return an array with image IDs
    // TODO, used elsewhere, to be defined in a static method
    public function get_bb_images() {

        error_log("get_bb_images IN");
        error_log("get_bb_images REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'show_planet' ) ) {
            error_log("get_bb_images nonce not found");
            wp_send_json_error( "NOK.", 403 );
        }

        $results = Pg_Geoposts_Table::get_boundingbox_images(
            $_REQUEST['ne_lat'],
            $_REQUEST['ne_lng'],
            $_REQUEST['sw_lat'],
            $_REQUEST['sw_lng'],
            $_REQUEST['zoom']
        );

        if ($results){

            $data = array();

            foreach($results as $resu){
                $url_img = wp_get_attachment_image_src($resu['post_id'], "medium");
                if ($url_img != false) {
                    $image = (object) [
                        'id' => $resu['post_id'],
                        'url' => $url_img[0],
                    ];

                    $data[] = $image;
                }
            }
        } 
        else {
            $data = null;
        }

        wp_send_json_success( $data, 200 );
        wp_die();
}
}
