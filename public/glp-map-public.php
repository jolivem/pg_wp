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
class Glp_Map_Public {

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

    private $images_new;

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
        add_shortcode( 'glp_map', array($this, 'ays_generate_map') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        // TODO remove useless css files
        wp_enqueue_style( 'leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), $this->version, 'all');
        wp_enqueue_style( 'markercluster.css', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css', array(), $this->version, 'all');
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        // TODO lightgallery est payant !!
        wp_enqueue_style( $this->plugin_name . "-lightgallery", plugin_dir_url( __FILE__ ) . 'css/lightgallery.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . "-lg-transitions", plugin_dir_url( __FILE__ ) . 'css/lg-transitions.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        // TODO remove useless JS files
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media();
        wp_enqueue_script( $this->plugin_name.'-imagesloaded.min.js', 'https://unpkg.com/imagesloaded@4.1.4/imagesloaded.pkgd.min.js', array( 'jquery' ), null, true );
        wp_enqueue_script( $this->plugin_name.'-picturefill.min.js', plugin_dir_url( __FILE__ ) . 'js/picturefill.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-lightgallery-all.min.js', plugin_dir_url( __FILE__ ) . 'js/lightgallery-all.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-jquery.mousewheel.min.js', plugin_dir_url( __FILE__ ) . 'js/jquery.mousewheel.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-leaflet.js', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'),  $this->version, true);
        wp_enqueue_script( $this->plugin_name.'-leaflet.markercluster.js', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js', array('jquery'),  $this->version, true);
        
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
        // wp_localize_script($this->plugin_name, 'gal_ajax_public', array('ajax_url' => admin_url('admin-ajax.php')));

    }

    public function enqueue_map_styles_early(){

        // TODO check it is useful
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        
        wp_enqueue_script('jquery');
    }
    
    public function ays_initialize_map_shortcode(){
    }
    
    public function ays_generate_map( $attr ){
        ob_start();

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->ays_show_map( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    public function ays_show_map( $attr ){
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        $map = $this->ays_get_map_by_id($id);
        if(!$map){
            // TODO add "not found"
            return "[glp_map id='".$id."']";
        }
        /*
         * map global settings
         */
        error_log("ays_show_map, map: ".print_r($map, true));

        $map_title = ($map['title'] == '' || $map['title'] === false) ? '' : $map["title"];
        $provider = ($map['provider'] == '' || $map['provider'] === false) ? '' : $map["provider"];
        $gallery_id = ($map['gallery_id'] == '' || $map['gallery_id'] === false) ? '' : $map["gallery_id"];
        $map_options = json_decode($map['options'],true);
        
        $gallery = $this->ays_get_gallery_by_id($gallery_id);
        if(!$gallery){
            return "TODO problem with gallery id";
        }

        $gallery_options = json_decode($gallery['options'],true);

        // TODO change gallery loader, flower is not nice !!
        $gallery_options['gallery_loader']  = (!isset($gallery_options['gallery_loader'])) ? "flower" : $gallery_options['gallery_loader'];
        $ays_gallery_loader = (isset($gallery_options['gallery_loader']) && $gallery_options['gallery_loader'] == 'default') ? "flower" : $gallery_options['gallery_loader'];

        // what to do when page is just loaded
        // TODO test with several maps on the same page
        $map_view .= "<script>
                (function($){
                    'use strict';
                    $(document).ready(function(){
                       var ays_map_containers = document.getElementsByClassName('ays_map_container_".$id."');
                       var ays_map_container_".$id.";
                       for(var ays_i = 0; ays_i < ays_map_containers.length; ays_i++){
                           do{
                                ays_map_container_".$id." = ays_map_containers[ays_i].parentElement.parentElement;
                            }
                            while(ays_map_container_".$id.".style.position === 'relative');
                            ays_map_container_".$id.".style.position = 'static';
                        }
                    });
                })(jQuery);
        </script>
        <style>
            .lg-outer, .lg-backdrop {
                z-index: 999999999999 !important;
            }
            .ays_map_container_".$id." {
                max-width: 100%;
                transition: .5s ease-in-out;
                animation-duration: .5s;
                position: static!important;
            }
            .ays_map_container_".$id." {
                $glp_container_css
            }
            .gpg_loader_".$id." {
                $ays_gal_loader_display;
            }
			#map {
                height: 500px;
            }
            .mydivicon{
                border-radius: 25%;
                border: 3px solid #fff;
            }
            .mydivmarker6{
                border-radius: 25%;
                border: 6px solid #fff;
            }
            .mydivmarker9{
                border-radius: 25%;
                border: 9px solid #fff;
            }
            .mydivmarker12{
                border-radius: 25%;
                border: 12px solid #fff;
            }
            div.slider {
                background-color: #333;
                overflow: auto;
                white-space: nowrap;
                padding: 10px;
            }
            div.slider img {
                padding: 2px;
                height:100px;
                cursor: pointer; /* Add cursor pointer for better usability */
                /*border: 2px solid transparent; /*Initial border style */
            }
            .imgSelected {
                border: 2px solid red; /* Adjust the border style for selected images */
                opacity: 1;
            }
            .imgNotSelected {
                border: 2px solid transparent; /* Adjust the border style for selected images */
                opacity: 0.6;
            }            
        </style>";

        $map_view .= "<div class='ays_map_body_".$id."'>";
        $images_distance = "7";
        $responsive_width_height = "";
        $map_view .= "
		<p>
			This is the self-contained one-file-version for a
			<a href='http://leafletjs.com/'>leaflet</a> map.
			For a version with CSS and Javascript in different files see
			<a href='index.html'>index.html</a>. For a leaflet map without
			Javascript programming try
			<a href='https://lapizistik.github.io/leaflet-easymap/'>Leasymap</a>.
		</p>
		<div id='map'></div>";
        $map_view .= "</div>
        <script>
        (function($){";

            // get map content
            // TODO handle resize, see gallery
            $map_cont = $this->ays_get_map_content($gallery, $gallery_options, $id);

            $images_js = $this->ays_add_makers();
            error_log("images_js 1=[".$images_js."]");
            $images_js = trim(str_replace(array("\n", "\r"), '', $images_js));
            $images_js = trim(preg_replace('/\s+/', ' ', $images_js));
            error_log("images_js 2=[".$images_js."]");

            $map_cont = addslashes($map_cont);
            error_log("map_cont=[".$map_cont."]");
            $map_view .= '
            $(document).ready(function(){
                setTimeout(function(ev){
                    var aysGalleryContent_'.$id.' = $("'.$map_cont.'");
                    $(document).find(".ays_map_body_'.$id.'").append(aysGalleryContent_'.$id.'); 
                },1000);
                var map = L.map("map").setView([51.505, -0.09], 13);
            
                L.tileLayer("https://{s}.tile.osm.org/{z}/{x}/{y}.png", {
                    attribution: "&copy; <a href=\"https://osm.org/copyright\">OpenStreetMap</a> contributors"
                }).addTo(map);
                
                var markers = L.markerClusterGroup({
                    zoomToBoundsOnClick: true,
                    iconCreateFunction: function(cluster) {
        
                        var children = cluster.getAllChildMarkers()[0];
        
                        var iicoon = new L.Icon(children.options.icon.options);
                        var count = cluster.getChildCount();
                        if (count < 6) {
                            iicoon.options.className = "mydivmarker6";    
                        }
                        else if (count < 20) {
                            iicoon.options.className = "mydivmarker9";    
                        }
                        else {
                            iicoon.options.className = "mydivmarker12";    
                        }
                        if (imageSelected != null) {
                            iicoon.options.iconSize = [100,100];
                            iicoon.options.iconUrl = imageSelected;
                            imageSelected = null;
                            setTimeout(function(){
                                cluster.refreshIconOptions({
                                    iconSize:     [60, 60],
                                }, true); 
                            }, 400);                        
                        }
        
                        return iicoon;
                    }
                });

                '.$images_js.'

            });';

/*

        
 
        */


        $map_view .= "
            })(jQuery);
        </script>";
        return $map_view;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    public function ays_get_map_by_id( $id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_map WHERE id={$id}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }
    public function ays_get_gallery_by_id( $id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }


    public function ays_get_gallery_categories() {
        $taxonomy = 'category'; // Change 'your_taxonomy' to the name of your taxonomy
        $terms = get_terms( array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false, // Set to true if you want to hide empty terms
        ) );
        //error_log("terms ".print_r($terms, true));
        return $terms;
    }

    public static function get_gallery_category_by_id($id){
        global $wpdb;

        $sql = "SELECT *
                FROM {$wpdb->prefix}glp_gallery_categories
                WHERE id=" . $id;

        $category = $wpdb->get_row($sql, 'ARRAY_A');

        return $category;
    }

    public static function ays_gallery_autoembed( $content ) {
        global $wp_embed;

        if ( is_null( $content ) ) {
            return $content;
        }

        $content = stripslashes( wpautop( $content ) );
        $content = $wp_embed->autoembed( $content );
        if ( strpos( $content, '[embed]' ) !== false ) {
            $content = $wp_embed->run_shortcode( $content );
        }
        $content = do_shortcode( $content );
        return $content;
    }
    
    // Function to find the value for a given meta_key
    private function findValueByKey($array, $key) {
        //error_log("findValueByKey IN");
        foreach ($array as $item) {
            if ($item['meta_key'] === $key) {
                //error_log("findValueByKey found: ".$item['meta_value']);
                return $item['meta_value'];
            }
        }
        //error_log("findValueByKey not found");
        return null; // Return null if the key is not found
    }

    // Function to find the value for a given country (ex "Albania.geojson")
    // ex return: {"file": "Albania.geojson", "height": "60px", "width": "50px", "zoom": 4}
    private function getCountryOptions($fileName) {
        
        $this->load_countries();
        
        foreach ($this->countries as $item) {
            if ($item['file'] === $fileName) {
                //error_log("getCountryOptions: ".print_r($item, true)."");
                return $item;
            }
        }
        return null; // Return null if the file is not found
    }
    
    private function load_countries() {
        error_log("load_countries: IN");
        if (!count($this->countries)) {
            
            // Add None
            //$dict["None"] = "None";

            $worldfile = GLP_DIR . 'assets/world.json';

            $json = file_get_contents($worldfile); 
            if ($json === false) {
               error_log("load_countries: Error reading file");
               return;
            }
            $this->countries = json_decode($json, true);
            //error_log("countries: ".print_r($this->countries, true));
        }
        else {
            error_log("load_countries: already loaded");
        }

    }

    // Check if geolocation parameters are correct
    // return null if on is wrong
    // return country options if correct
    private function checkGeolocation($latitude, $longitude, $country) {
        //error_log("checkGeolocation IN");
        
        if (!is_numeric($latitude)) {
            return null;
        }

        if (!is_numeric($longitude)) {
            return null;
        }
        //check if country file exists
        return $this->getCountryOptions($country);
        
        //error_log("findValueByKey not found");
        //return null; // Return null if the key is not found
    }
   
    // $type = "masonry" or "grid" or mosaic
    private function ays_add_images_html($show_title, $image_titles, $image_dates, 
        $show_with_date, $image_descs, $image_alts, $image_ids, $id, $images_loading, $disable_lightbox, 
        $show_title_on, $html_hover_icon, $ays_show_caption, $ays_images_loader, $images,
        $image_countries, $image_latitudes, $image_longitudes, $images_categories, $images_distance,
        $column_width, $vignette_display) {

        $leaf_view = "";
        
        // HTML part
        $leaf_view .= "<div class='slider' id='imageSlider'>";

        foreach($this->images_new as $key=>$image){
            
            // if no vignette
                //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
            $leaf_view .= "<img class='imgNotSelected' src='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "'>";
        } // end foreach image
        
        $leaf_view .= "</div>";

        return $leaf_view;
    
    }// end ays_add_images_html()

    private function ays_add_makers() {

        // Javascript part
        $map_view = "";
        $map_view .= "let icon;";
        
        $lon = 51.51;
        $lat = 0.01;
        foreach($this->images_new as $key=>$image){
            
            //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
            $map_view .= "icon = new LeafIcon({iconUrl: '". $image ."'});";
            $lon += 0.01;
            $lat -= 0.01;
            $map_view .= "markers.addLayer(L.marker([".strval($lon).", ".strval($lat)."], {icon: icon}).addTo(map).bindPopup('I am a green leaf.'));";
        } // end foreach image

        //$map_view .= "";
        error_log("ays_add_makers = [".$map_view."]");
        return $map_view;
    
    }// end ays_add_makers()    

    protected function ays_get_map_content($gallery, $gallery_options, $id){
        global $wpdb;
        error_log("ays_get_map_content IN");
        $settings_options = Gallery_Settings_Actions::ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode(stripcslashes($settings_options), true);
        }else{
            $settings_options = array();
        }

        //$width = $gallery["width"];
        $title = $gallery["title"];
        $images_request = ($gallery_options['images_request'] == '' || $gallery_options['images_request'] == false) ? 'selection' : $gallery_options['images_request'];
        
        // get the images knowing the categories
        if ($images_request == "query" ) {
        
            //error_log("query_categories: ".$gallery_options['query_categories
            $query = "SELECT post_id FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'category' AND `meta_value` = '".$gallery_options['query_categories']."'";
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            //error_log("Image ids for the given category: ".print_r($result, true));
            foreach ($result as $item) {
                array_push($image_ids, $item['post_id']);
            }
        }
        else {
            $image_ids = explode( "***", $gallery["images_ids"]);
        }
        error_log("Image ids : ".print_r($image_ids, true));
        $images = array();
        $image_titles = array();
        $image_descs = array();
        $image_alts = array();
        $image_dates = array();

        // Prepare $image_titles, $image_descs, $images and $image_dates
        for( $iid = 0; $iid < count($image_ids); $iid++ ){
            //error_log("image_ids[".$iid."]: ".$image_ids[$iid]);
            $query = "SELECT post_title,post_content,post_excerpt,guid,post_date FROM `".$wpdb->prefix."posts` WHERE `id` = '".$image_ids[$iid]."'";
            //error_log("query: ".$query);
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            //error_log("Images result: ".print_r($result, true));
            
            if (count($result) > 0) {
                //error_log("result : ".print_r($result , true));
                array_push($image_titles, $result[0]['post_title']);
                array_push($image_descs, $result[0]['post_content']);
                //array_push($image_urls2, $result[0]['guid']);
                array_push($images, $result[0]['guid']);
                array_push($image_dates, $result[0]['post_date']);

                $query = "SELECT meta_value FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = '_wp_attachment_image_alt' AND `post_id` = '".$image_ids[$iid]."'";
                //error_log("query: ".$query);
                $result =  $wpdb->get_results( $query, "ARRAY_A" );
                //error_log("Meta result: ".print_r($result, true));
                
                if (count($result) > 0) {
                    //error_log("result : ".print_r($result , true));
                    array_push($image_alts, $result[0]['meta_value']);
                }
                else {
                    array_push($image_alts, '');
                }
            }
        }

        // error_log("images: ".print_r($images, true));
        // error_log("image_titles: ".print_r($image_titles, true));
        // error_log("image_descs: ".print_r($image_descs, true));
        // error_log("image_alts: ".print_r($image_alts, true));
        // error_log("image_dates: ".print_r($image_dates, true));

        // Prepare $image_latitudes, $image_longitudes, and $image_countries
        $image_latitudes = array();
        $image_longitudes = array();
        $image_countries = array();

        for( $iid = 0; $iid < count($image_ids); $iid++ ){
            //error_log("image_ids[iid]: ".$image_ids[$iid]);
            $query = "SELECT meta_key, meta_value FROM `".$wpdb->prefix."postmeta` WHERE `post_id` = '".$image_ids[$iid]."'";
            //error_log("query: ".$query);
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            $options = null;
            $longitude = 0;
            $latitude = 0;
            $vignette = '';
            if (count($result) > 0) {
                //error_log("result : ".print_r($result , true));
                $longitude = $this->findValueByKey($result, 'longitude');
                $latitude = $this->findValueByKey($result, 'latitude');
                $vignette = $this->findValueByKey($result, 'vignette');

                $options = $this->checkGeolocation($latitude, $longitude, $vignette);
                if ($options != null) {
                    array_push($image_latitudes, $latitude);
                    array_push($image_longitudes, $longitude);
                    array_push($image_countries, $options);
                }
            }
            if ($options == null) {
                array_push($image_latitudes, 0);
                array_push($image_longitudes, 0);
                array_push($image_countries, null);
            }
        }

        //TODO tests when there is not title or no description,...

        $images_categories  = isset($gallery["categories_id"]) && $show_filter_cat == 'on' ? explode( "***", $gallery["categories_id"] ) : array();        

        if ($ays_gallery_loader == 'text') {
            $ays_images_loader = "<p class='ays-loader-content'>". $gallery_loader_text_value ."</p>";
        }elseif ($ays_gallery_loader == 'custom_gif') {
            if ($gallery_loader_custom_gif != '') {
                $ays_images_loader = "                
                    <img src='". $gallery_loader_custom_gif ."' style='". $gallery_loader_custom_gif_width_css ."'>";
            }else{
                $ays_images_loader = "<img src='".GLP_PUBLIC_URL."images/flower.svg'>";                
            }
        }else{
            $ays_images_loader = "<img src='".GLP_PUBLIC_URL."images/$ays_gallery_loader.svg'>";
        }

        // remove from arrays
        if ((array_search('', $image_dates) !== 0) && (count($images) != count($image_dates))) {
            $dates_key = array_search('', $image_dates);
            unset($image_dates[$dates_key]);
            unset($image_descs[$dates_key]);
            unset($image_titles[$dates_key]);
            unset($image_alts[$dates_key]);
            unset($image_ids[$dates_key]);
        }   

        $this->images_new     = array();
        $this_site_path = trim(get_site_url(), "https:");
        // TODO get small size for leaflet
        $image_sizes = "medium_large"; // medium_large for gallery
        error_log("public image_sizes=".$image_sizes);
        foreach($images as $i => $img){
            if(strpos(trim($img, "https:"), $this_site_path) !== false){ 
                $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `post_type` = 'attachment' AND `guid` = '".$img."'";
                $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                if(!empty($result_img)){
                    // find the given size
                    $url_img = wp_get_attachment_image_src($result_img[0]['ID'], $image_sizes);
                    if($url_img === false){
                       $this->images_new[] = $img;
                    }else{
                       $this->images_new[] = $url_img[0];
                    }

                    // TODO test content of metada
                    // $metadata = wp_get_attachment_metadata($result_img[0]['ID']);
                    // error_log("image metadata=".print_r($metadata, true));

                }else{
                    $this->images_new[] = $img;
                }                
            }else{
                $this->images_new[] = $img;
            }
        }
        $images_count = count($images);

        //BUILD HTML for all images
        $map_content .= "<div class='ays_map_container_".$id."'>";
        $map_content .= $this->ays_add_images_html($show_title, $image_titles, $image_dates, 
            $show_with_date, $image_descs, $image_alts, $image_ids, $id, $images_loading, $disable_lightbox, 
            $show_title_on, $html_hover_icon, $ays_show_caption, $ays_images_loader, $images,
            $image_countries, $image_latitudes, $image_longitudes, $images_categories, $images_distance,
            $column_width, $vignette_display);
        //error_log("map_view=".$map_content);
        
        $map_content .= "</div>";
       
        $map_content = trim(str_replace(array("\n", "\r"), '', $map_content));
        $map_content = trim(preg_replace('/\s+/', ' ', $map_content));
        error_log("get_map_content=[".$map_content."]");
        return $map_content;
    
    } // end ays_get_map_content()

    private function array_split($array, $pieces) {
        if ($pieces < 2)
            return array($array);
        $newCount = ceil(count($array)/$pieces);
        $a = array_slice($array, 0, $newCount);
        $b = $this->array_split(array_slice($array, $newCount), $pieces-1);
        return array_merge(array($a),$b);
    }
    

    function ays_gallery_wp_get_attachment_image_attributes($attr) {
        if ( isset( $attr ) && !is_array( $attr ) ) {
            $attr = '';
        }

        return $attr;
    }
}
