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
class Pg_Show_User_Map_Public {

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
        add_shortcode( 'pg_show_user_map', array($this, 'pg_generate_page') );
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

        wp_enqueue_style( $this->plugin_name."-simple-lightbox.css", plugin_dir_url( __FILE__ ) . 'css/simple-lightbox.css', array(), $this->version, 'all' );
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

        wp_enqueue_script( $this->plugin_name.'-simple-lightbox.js', plugin_dir_url( __FILE__ ) . 'js/simple-lightbox.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-pg-map.js', plugin_dir_url( __FILE__ ) . 'js/pg-map.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-map.js', 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        error_log("Pg_Show_User_Map_Public::pg_generate_page IN ".print_r($_GET, true));
        //error_log("pg_generate_page IN ".print_r($attr, true));

        // TODO check that the photo belons to the current user
        if (! isset($_GET['guuid'])) {
            return "";
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $_GET['guuid'] );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }
    
    // attr should have the user id
    public function pg_show_page( $guuid ){

        error_log("pg_show_page IN guuid=".$guuid);
        
        //global $wpdb;

        $gallery = $this->pg_get_gallery_by_uuid($guuid);
        if(!$gallery){
            error_log("pg_show_page Gallery not found");
            return "";
        }
        $id = $gallery["id"];
        $gtitle = stripslashes($gallery["title"]);
        $gdescription = stripslashes($gallery["description"]);
        if(!$gallery){
            error_log("pg_show_page Gallery not found");
            return "";
        }
  
        $medias = $this->pg_get_medias_by_gallery($id);

        if ($medias != null) {
            $html_slider = $this->render_slider($medias);
            $html_descr = $this->render_medias_descriptions($medias);
        }
  
        $html_code = "
        <div class='pg-container'>
            <div class='desc-block'>
                <div class='desc-gallery'>
                    <div class='desc-title'>$gtitle</div>
                    <p class='desc-description'>$gdescription</p>
                </div>
            </div>
            </br>
            <div class='pg-map'>
                <div id='map' style='height:300px;'></div>
            </div>
            <div class='flex-container-slider'>
                <div class='slider-options-left' style='background-color: lightgreen'>
                    <div>
                        <div class='show-gallery-option fas fa-step-backward' style='padding-bottom:38px;' aria-hidden='true'></div>
                        <div class='show-gallery-option fas fa-angle-double-left' aria-hidden='true'></div>
                    </div>
                </div>
                <div class='gallery-slider' id='imageSlider'>
                    $html_slider 
                </div>
                <div class='slider-options-right' style='background-color: lightgreen'>
                    <div>
                        <div class='show-gallery-option fas fa-step-forward' style='padding-bottom:38px;' aria-hidden='true'></div>
                        <div class='show-gallery-option fas fa-angle-double-right' aria-hidden='true'></div>
                    </div>
                </div>
            </div>
            <div id='imageDescr' class='desc-block'>$html_descr</div> 
         </div>";

        $js = $this->script_map($medias);
        $html_code .= $js;

        return $html_code;
    } // end ays_show_galery()

       // render all the images 
    function render_slider($medias){
        error_log("render_images IN images=".print_r($medias, true));
        $html='';
        $num=0;
        // loop for each media
        foreach($medias as $id){
            //error_log("render_images id:".$id);
            //$img_src = $item->guid;
            $img_src_medium = "";
            $img_src_full = "";
            $url_img = wp_get_attachment_image_src($id, "medium");
            $post = get_post($id);
            if ($post != null && $url_img != false) {
                $img_src_medium = $url_img[0];

                $url_img = wp_get_attachment_image_src($id, "full");
                if ($url_img != false) {
                    $img_src_full = $url_img[0];
                }

                $html.="
                <div class='slider-item'>
                    <div class='slider-lb' data-full='$img_src_full'>
                        <img src='$img_src_medium' id='slider-$id' class='imgNotSelected' data-full='$img_src_full'>";
                // description of the photo INSIDE the lightbox
                if ($post->post_content != '') {
                    $html.="
                        <div class='slider-descr'>
                            <div class='desc-lightbox-title'>$post->post_content</div>
                        </div>";
                }
                $html.="
                    </div>
                    <div class='slider-overlay-circle'>
                        <i class='far fa-dot-circle slider-icon' data-num='$num'></i>
                    </div>
                    <div class='slider-overlay-text'>
                        <i class='fas fa-align-center slider-icon' data-num='$num'></i>
                    </div>
                </div>";
               $num = $num + 1;
            }
        }
        return $html;
    }
 
    function render_medias_descriptions($medias){
        //error_log("render_images IN images=".print_r($medias, true));
        $html='</br>';
        
        // loop for each media
        foreach($medias as $id){

            $post = get_post($id);
            if ($post != null) {
                $content = $post->post_content;
                $title = $post->post_title;

                if ($content != "") {
                    $html.="<div id='desc-$id' class='desc-slider desc-display'>
                                <div class='desc-slider-title'>$content</div>
                            </div>";
                }
            }
        }
        return $html;
    }

    function pg_get_gallery_by_uuid( $uuid ) {
        global $wpdb;

        //$sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE uuid={$uuid}";
        $table = $wpdb->prefix . "glp_gallery";
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE uuid = %s",
            $uuid
        );

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
                let icon;";
        
                $minlat = 90.0; 
                $maxlat = -90.0;
                $minlng = 180.0;
                $maxlng = -180.0;
        
                foreach($medias as $id){
                    //error_log("render_images id:".$id);
                    //$img_src = $item->guid;
                    $url_img = wp_get_attachment_image_src($id, "medium");
                    if ($url_img != false) {
                        $img_src = $url_img[0];
                    
                        $latitude = get_post_meta($id, 'latitude', true);
                        $longitude = get_post_meta($id, 'longitude', true);
                        //error_log("latitude=".$latitude."longitude=".$longitude);
                        if ($latitude && $longitude) {

                            // keep min and max
                            $minlat = min($minlat, $latitude);
                            $maxlat = max($maxlat, $latitude);
                            $minlng = min($minlng, $longitude);
                            $maxlng = max($maxlng, $longitude);
                            //error_log("minlat=".$minlat.", maxlat=".$maxlat.",minlng=".$minlng.", maxlng=".$maxlng);
        
                            $map_js .= "icon = new g_LeafIcon({iconUrl: '". $img_src ."'});";
                            $map_js .= "g_markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}));";
                        }
                    }
                } // end foreach image

                $map_js .= "
                g_map.addLayer(g_markers);
                const bbox = [[$minlat,$minlng],[$maxlat,$maxlng]];
                /*L.rectangle(bbox).addTo(g_map);*/
                g_map.fitBounds(bbox);
                
                /* dezoom one level */
                const currentLevel = g_map.getZoom();
                console.log('INIT currentlevel', currentLevel);
                if (currentLevel > 1) {
                    if (currentLevel > 10) {
                        g_map.setZoom(currentLevel - 2);
                    }
                    else {
                        g_map.setZoom(currentLevel - 1);
                    }
                }
                console.log('INIT map', g_map);
                g_markers.refreshClusters();
                
                /* add lightbox */ 
                g_lightbox = new SimpleLightbox('#imageSlider .slider-lb', {
                    sourceAttr: 'data-full',
                    captionSelector: '.slider-descr',
                    captionType: 'text',
                    widthRatio: 0.9,
                    captionPosition: 'bottom'
                });
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
