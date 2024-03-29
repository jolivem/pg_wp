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

        wp_enqueue_script( $this->plugin_name.'-pg-map.js', plugin_dir_url( __FILE__ ) . 'js/pg-map.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-map.js', 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        wp_enqueue_style( $this->plugin_name."-public.css", plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-map.css", plugin_dir_url( __FILE__ ) . 'css/pg-map.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        error_log("pg_generate_page IN ".print_r($attr, true));

        // TODO check that the photo belons to the current user

        //Test with ID=67
        $attr['id']=2;

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }
    
    // attr should have the user id
    public function pg_show_page( $attr ){

        error_log("pg_show_page IN ".print_r($attr, true));
        
        //global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;

        $gallery = $this->pg_get_gallery_by_id($id);
        if(!$gallery){
            error_log("pg_show_page Gallery not found");
            return "";
        }
        
        $title = $gallery["title"];
        $description = $gallery["description"];

        $markers_js = $this->define_markers();

        $html_code = "
        <div class='container'>
            <div id='my-map'></div>
         </div>";

        return $html_code;
    } // end ays_show_galery()

 
    function pg_get_gallery_by_id( $id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }

    private function define_makers() {

        // Javascript part
        //$markers_js = "";
        $markers_js = "let icon;";
        
        $lon = 51.51;
        $lat = 0.01;
        foreach($this->images_new as $key=>$image){
            
            //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
            $markers_js .= "icon = new LeafIcon({iconUrl: '". $image ."'});";
            $lon += 0.01;
            $lat -= 0.01;
            $markers_js .= "markers.addLayer(L.marker([".strval($lon).", ".strval($lat)."], {icon: icon}).addTo(map).bindPopup('I am a green leaf.'));";
        } // end foreach image

        //$map_view .= "";
        error_log("define_makers = [".$markers_js."]");
        return $markers_js;
    
    }// end ays_add_makers()    

/*
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

*/


    private function get_tumbnails_url() {
        $thumbnails     = array();
        $this_site_path = trim(get_site_url(), "https:");
        // TODO get small size for leaflet
        $image_sizes = "thumbnail"; // medium_large for gallery
        error_log("public image_sizes=".$image_sizes);
        foreach($images as $i => $img){
            if(strpos(trim($img, "https:"), $this_site_path) !== false){ 
                $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `post_type` = 'attachment' AND `guid` = '".$img."'";
                $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                if(!empty($result_img)){
                    // find the given size
                    $url_img = wp_get_attachment_image_src($result_img[0]['ID'], $image_sizes);
                    if($url_img === false){
                        $thumbnails[] = $img;
                    }else{
                        $thumbnails[] = $url_img[0];
                    }

                    // TODO test content of metada
                    // $metadata = wp_get_attachment_metadata($result_img[0]['ID']);
                    // error_log("image metadata=".print_r($metadata, true));

                }else{
                    $thumbnails[] = $img;
                }                
            }else{
                $thumbnails[] = $img;
            }
        }        
    }
}
