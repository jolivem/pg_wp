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
        $html_addresses = $this->render_medias_address($medias);
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
            <div class='flex-container-slider' style='height: 150px;'>
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
            <div id='imageDescr'>$html_addresses</div>
         </div>";

        $js = $this->script_map($medias);
        $html_code .= $js;

        return $html_code;
    } // end ays_show_galery()

    // render all the images 
    // medias is an array of array with 'post_id'
    function render_slider($medias){
        //error_log("render_slider IN images=".print_r($medias, true));
        $html='';
        $num=0;
        // loop for each media
        foreach($medias as $media){
            $id=$media['post_id'];
            //error_log("render_images id:".$id);
            //$img_src = $item->guid;
            $img_src_medium = "";
            $img_src_full = "";
            $url_img = wp_get_attachment_image_src($id, "medium");
            
            if ($url_img != false) {
                error_log("render_slider ".print_r($url_img, true));
                $img_src_medium = $url_img[0];
            
                $url_img = wp_get_attachment_image_src($id, "full");
                if ($url_img != false) {
                    $img_src_full = $url_img[0];
                }

                $html.="
                <div class='slider-item'>
                    <img src='$img_src_medium' id='slider-$id' alt='Image 1' class='imgNotSelected' data-full='$img_src_full'>
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

    // medias is an array of array with 'post_id'
    function render_medias_address($medias){
        //error_log("render_medias_address IN images=".print_r($medias, true));
        $html='</br>';
        
        // loop for each media
        foreach($medias as $media){
            $id = $media['post_id'];
            $post = get_post($id);
            if ($post != null) {
                //$address_json = $post->post_excerpt;
                $title = $post->post_title;
                //$address = $post->post_name;
                $addr = json_decode($post->post_excerpt, true);
                //error_log("render_medias_address address=".print_r($addr, true));
                $small_address = $addr['country']." ".$addr['county']." ". $addr['village'];

                $html.="
                <div id='desc-$id' class='desc-all'>
                    <h4 class='desc-title'>$title</h4>
                    <p class='desc-description'>$small_address</p>
                </div>";
            }
        }
        return $html;
    }

    // create map, markers and lightbox
    // and fill them with images
    ///////////////////////////////////
    private function script_map($medias) {

        // Javascript part
        //$markers_js = "";
        $map_js = "
        <script>

        (function($) {
            'use strict';
            $(window).ready(function(){

                g_map.setView([0,0], 1);
 
                g_map.on('moveend', function(e) {
                    console.log('movend',e);
                    console.log('movend',g_map.getBounds());
                    const ne = g_map.getBounds().getNorthEast();
                    const sw = g_map.getBounds().getSouthWest();
                    const zoom = g_map.getZoom();
                    getImagesFromBB(ne.lat, ne.lng, sw.lat, sw.lng, zoom);
                    /*console.log('movend',g_map.getZoom());*/
                });
                g_map.on('zoomend', function(e) {
                    console.log('zoomend',e);
                    console.log('zoomST_end',g_map.getBounds());
                    const ne = g_map.getBounds().getNorthEast();
                    const sw = g_map.getBounds().getSouthWest();
                    const zoom = g_map.getZoom();
                    getImagesFromBB(ne.lat, ne.lng, sw.lat, sw.lng, zoom);
                });
                let icon;";
 
         
                $minlat = 90.0; 
                $maxlat = -90.0;
                $minlng = 180.0;
                $maxlng = -180.0;
        
                foreach($medias as $media){
                    $id = $media['post_id'];
                    //error_log("script_map id:".$id);
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
        
                            
                            //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
                            $map_js .= "icon = new LeafIcon({iconUrl: '". $img_src ."'});";
                            //$map_js .= "markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}).addTo(g_map).bindPopup('I am a green leaf.'));";
                            $map_js .= "markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}).addTo(g_map));";
                        }
                    }
                } // end foreach image

                $map_js .= "
                g_map.addLayer(markers);
                
                /* add lightbox */ 
                g_lightbox = new SimpleLightbox('#imageSlider img', {
                    sourceAttr: 'data-full'
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
    public function get_bb_images() {

        //error_log("get_bb_images IN");
        //error_log("get_bb_images REQUEST ".print_r($_REQUEST, true));
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
                $pid = $resu['post_id'];
                $post = get_post($pid);
                if ($post != null) {
                    //error_log("get_bb_images post=".print_r($post, true));
                    $url_img_medium = wp_get_attachment_image_src($pid, "medium");
                    $url_img_full = wp_get_attachment_image_src($pid, "full");
                    if ($url_img_medium != false) {
                        $image = (object) [
                            'id'            => $pid,
                            'url_medium'    => $url_img_medium[0],
                            'url_full'      => $url_img_full[0],
                            'address'       => $post->post_name,
                            'title'         => $post->post_title,
                            'address_json'  => $post->post_excerpt
                        ];
                        
                        //error_log("get_bb_images image=".print_r($image, true));
                        $data[] = $image;
                    }
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
