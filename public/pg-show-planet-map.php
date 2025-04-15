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
        // error_log("Pg_Show_Planet_Map_Public::ctor WW locale =".get_bloginfo('language'));
        // error_log("Pg_Show_Planet_Map_Public::ctor XX locale =".determine_locale());
        // error_log("Pg_Show_Planet_Map_Public::ctor YY locale =".get_locale());
        // error_log("Pg_Show_Planet_Map_Public::ctor  =".print_r($_SERVER, true));
        // error_log("Pg_Show_Planet_Map_Public::ctor  =".print_r($_POST, true));
        // error_log("Pg_Show_Planet_Map_Public::ctor  =".print_r($GLOBALS, true));
        // error_log("Pg_Show_Planet_Map_Public::ctor locale =".get_locale());

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'MarkerCluster', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');

        wp_enqueue_style( $this->plugin_name."-simple-lightbox.css", plugin_dir_url( __FILE__ ) . 'css/simple-lightbox.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-slick.css", plugin_dir_url( __FILE__ ) . 'slick/slick.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-slick-theme.css", plugin_dir_url( __FILE__ ) . 'slick/slick-theme.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-public.css", plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );
        //wp_enqueue_style('leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        //wp_enqueue_media();
        wp_enqueue_script( $this->plugin_name.'-pg-public.js', plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        //wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);
        wp_enqueue_script( $this->plugin_name.'-leaflet.js', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet-src.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-markercluster.js', 'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js', array( 'jquery' ), $this->version, true );

        wp_enqueue_script( $this->plugin_name.'-simple-lightbox.js', plugin_dir_url( __FILE__ ) . 'js/simple-lightbox.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-slick.js', plugin_dir_url( __FILE__ ) . 'slick/slick.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-pg-map.js', plugin_dir_url( __FILE__ ) . 'js/pg-map.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-map.js', 'ays_vars', 
                array('base_url' => GLP_BASE_URL,
                'address_not_found' => esc_html__("Adresse introuvable", $this->plugin_name)));

        wp_enqueue_script( $this->plugin_name.'-pg-vignette.js', plugin_dir_url( __FILE__ ) . 'js/pg-vignette.js', array( 'jquery' ), $this->version, true );
    }

    public function enqueue_styles_early(){

        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        //error_log("Pg_Show_Planet_Map_Public::pg_generate_page IN ".print_r($attr, true));

        // focus on pid: fpid
        // check fpid is present and valid
        $fpid = null;
        if (isset($_GET['fpid'])) {
            if (wp_attachment_is_image( $_GET['fpid'])) {
                $fpid=$_GET['fpid']; 
            }
        }
 
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page($fpid);

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }
    
    // attr should have the user id
    /**
     * @param fpid, focus on the given photo (post) id
     */
    public function pg_show_page($fpid){

        // error_log("pg_show_page IN");
        if ($fpid != null) {
            $latitude = get_post_meta($fpid, 'latitude', true);
            $longitude = get_post_meta($fpid, 'longitude', true);
            error_log("fpid=".$fpid.", latitude=".$latitude.", longitude=".$longitude);

            $medias = Pg_Geoposts_Table::get_all_public_images();
        }
        else {
            $medias = Pg_Geoposts_Table::get_all_public_images();
        }
        //error_log("pg_show_page ".print_r($medias, true));
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('show_planet');
        $site_rul = get_site_url();
        
        //$markers_js = $this->define_markers();
        $ban = 0;
        if ( current_user_can( 'administrator' ) ) {
            $ban = 1;
        }

        $html_code = "
        <input type='hidden' id='pg_site_url' value='$site_rul'/>
        <input type='hidden' id='page_nonce' value='$nonce'/>
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_ban' value='$ban'/>";

        if ($fpid != null) {
            $html_code .= "
            <input type='hidden' id='fpid' value='$fpid'/>
            <input type='hidden' id='flag_fpid'/>";
        }
        else {
             $html_code .= "
            <input type='hidden' id='fpid'/>";
        }

        $html_code .= "
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div id='ban-photo-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                    ".esc_html__("Enregistré !", $this->plugin_name)."
                    </div>
                </div>
            </div>
        </div>
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div id='copy-to-clipboard' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        '".esc_html__("Adresse copiée dans le presse-papier !", $this->plugin_name)."
                    </div>
                </div>
            </div>
        </div>

        <div class='pg-map-container'>
            <form id='searchForm'>
                <div class='input-group mb-3'>
                    <input type='text' class='form-control' id='searchInput' placeholder='".esc_html__("Entrez un lieu", $this->plugin_name)."' aria-label='Entrez un lieu' aria-describedby='searchButton'>
                    <button type='button' id='searchButton' class='btn btn-primary' data-mdb-ripple-init>
                        <i class='fas fa-search'></i>
                    </button>
                </div>            
            </form>
            <div class='pg-map'>
                <div id='map'></div>
            </div>
            <div id='imageSlider' class='slider'></div>
            <div id='imageDescr' class='map-desc-block'></div>
        </div>
        </br>
        </br>
        <div class='pg-map-container'>
            <div class='participate-info'>
            ".esc_html__("Participez au projet Planet-Gallery !", $this->plugin_name)."
                <ul>
                    <li>".esc_html__("Créez des galeries avec vos photos géolocalisées.", $this->plugin_name)."</li>
                    <li>".esc_html__("Partagez des galeries et cartes inédites avec vos proches.", $this->plugin_name)."</li>
                    <li>".esc_html__("Faites connaître votre activité.", $this->plugin_name)."</li>
                    <li>".esc_html__("Les photos publiques sont affichées sur la carte ci-dessus.", $this->plugin_name)."</li>
                </ul>
                ".esc_html__("C'est un projet participatif, tout est gratuit. Inscrivez-vous dès maintenant !", $this->plugin_name)."
            </div>
         </div>";

        $js = $this->script_map($medias, $fpid);
        $html_code .= $js;

        return $html_code;
    } // end ays_show_galery()

    // render all the images 
    // medias is an array of array with 'post_id'
 
    // create map, markers and lightbox
    // and fill them with images
    ///////////////////////////////////
    private function script_map($medias, $fpid) {
        error_log("script_map IN fpid=".$fpid);

        // Javascript part
        //$markers_js = "";
        $map_js = "
        <script>

        (function($) {
            'use strict';
            $(window).ready(function(){

                if (window.innerWidth > 1200) {
                    g_map.setView([0,0], 2);
                }
                else {
                    g_map.setView([0,0], 1);
                }
 
                g_map.on('moveend', function(e) {
                    /*console.log('movend');*/
                    const data = g_lightbox.getLighboxData();
                    if (data.currentImage == undefined) {
                        const ne = g_map.getBounds().getNorthEast();
                        const sw = g_map.getBounds().getSouthWest();
                        const zoom = g_map.getZoom();
                        getImagesFromBB(ne.lat, ne.lng, sw.lat, sw.lng, zoom);
                    }
                });
                g_map.on('zoomend', function(e) {
                    /*console.log('zoomend');*/
                    const data = g_lightbox.getLighboxData();
                    if (data.currentImage == undefined) {
                        const ne = g_map.getBounds().getNorthEast();
                        const sw = g_map.getBounds().getSouthWest();
                        const zoom = g_map.getZoom();
                        getImagesFromBB(ne.lat, ne.lng, sw.lat, sw.lng, zoom);
                    }
                });
                let icon;";

                // Populate all leafs for all public images
                $fpid_lat=null;
                $fpid_lon=null;

                foreach($medias as $media){
                    $id = $media['post_id'];
                    //error_log("script_map id:".$id);
                    $url_img = wp_get_attachment_image_src($id, "medium");
                    if ($url_img != false) {
                        $img_src = $url_img[0];
                    
                        $latitude = get_post_meta($id, 'latitude', true);
                        $longitude = get_post_meta($id, 'longitude', true);
                        //error_log("latitude=".$latitude."longitude=".$longitude);
                        if ($latitude && $longitude) {

                            $map_js .= "icon = new g_LeafIcon({iconUrl: '". $img_src ."', data: 'slider-". $id ."'});";
                            $map_js .= "g_markers.addLayer(L.marker([".strval($latitude).", ".strval($longitude)."], {icon: icon}));";

                            if ($fpid == $id) {
                                $fpid_lat = strval($latitude);
                                $fpid_lon = strval($longitude);
                            }
                        }
                    }
                }
                 // end foreach image
                    // if (fpid_image !== undefined) {
                    //     console.log("fpid_image !== undefined");
                        
                    //     // zoom on the fpid image only once
                    //     var lat = parseFloat(fpid_image.latitude);
                    //     var lon = parseFloat(fpid_image.longitude);
                        
                    //     g_map.setView([lat, lon], 5);
    
                    //     // remove elem to ensure it is done once
                    //     flag_fpid_elem.remove();
                    // }
                error_log("fpid_lat=".$fpid_lat."fpid_lon=".$fpid_lon);
                if ($fpid_lat == null){
                    error_log("script_map wide map");
                    $map_js .= "
                    getImagesFromBB(80.0, 180.0, -80.0, -180.0, 1);";
                }
                else {
                    error_log("script_map g_map.setView([".$fpid_lat.", ".$fpid_lon."]");
                    $map_js .= "
                    g_map.setView([".$fpid_lat.", ".$fpid_lon."], 5);";
                }
                $map_js .= "
                g_map.addLayer(g_markers);
                
                /* add lightbox */ 
                g_lightbox = new SimpleLightbox('#imageSlider .slider-lb', {
                    sourceAttr: 'data-full',
                    captionSelector: '.lightbox-descr',
                    widthRatio: 0.9,
                    captionType: 'text',
                    disableClick: true,
                });

            })
        })(jQuery);
        </script>";
        //$map_view .= "";
        //error_log("script_map = [".$map_js."]");
        return $map_js;
    
    }// end ays_add_makers()    

    public function rework_address($address) {

       // Remove "Route sans nom" if it exists
        $address = str_replace("Route sans nom,", "", $address);
        
        // Remove words with numerals
        $address = preg_replace('/\b\w*\d\w*\b/', '', $address);
        
        // Remove any extra spaces or commas caused by the replacements
        $address = preg_replace('/\s+/', ' ', $address); // Replace multiple spaces with a single space
        $address = preg_replace('/\s*,\s*/', ', ', $address); // Remove extra spaces around commas
        $address = trim($address, ', '); // Remove leading/trailing commas and spaces
        
        return $address;
        
    }
 
    /**
     * @brief find an item by id in a given array 
     * @param $array, example $posts = [["post_id" => 574, "ST_Y(location)" => 6.684474],[...]...]
    **/
    private function findPostById($array, $postId) {
        foreach ($array as $item) {
            if ($item['post_id'] == $postId) {
                return $item; // Return the matching item
            }
        }
        return null; // Return null if no match is found
    }
    
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
        
        //error_log("get_bb_images results=".$results.);

        if ($results){

            $action = $this->find_action($results, $_REQUEST['current_ids']);
            if ($action == "nothing") {
                $data = [
                    "action" => "nothing",
                ];
            }
            else {

                // check if 'focus on pid' is set
                if (isset($_REQUEST['fpid'])) {
                    error_log("get_bb_images  fpid = ".$_REQUEST['fpid']);
                    $item_fpid = $this->findPostById($results, $_REQUEST['fpid']);
                }

                # random sort 
                shuffle($results);

                if (count($results ) > 40) {
                    $results = array_slice($results, 0, 40);

                    // if fpid has been removed randomly, re-add it as the 41 postid
                    if ($item_fpid != null && $this->findPostById($results, $_REQUEST['fpid']) == null) {
                        array_push( $results, $item_fpid);
                    }
                }

                // 
                //error_log("get_bb_images before sort".print_r($results, true));
                // sort on longitude
                usort($results, function($a, $b) {
                    return $a['ST_Y(location)'] <=> $b['ST_Y(location)'];
                });

                //error_log("get_bb_images after sort".print_r($results, true));

                $images = array();

                foreach($results as $resu){
                    $pid = $resu['post_id'];
                    $post = get_post($pid);
                    if ($post != null) {
                        //error_log("get_bb_images post=".print_r($post, true));
                        $url_img_medium = wp_get_attachment_image_src($pid, "medium");
                        $url_img_full = wp_get_attachment_image_src($pid, "full");
                        if ($url_img_medium != false) {
                            $meta = get_post_meta($pid);
                            //error_log("get_bb_images meta=".print_r($meta, true));
                            $metadate = $meta['date'][0];
                            $latitude = $meta['latitude'][0];
                            $longitude = $meta['longitude'][0];
                            $vignette = $meta['vignette'][0];
                            $date = Pg_Edit_Gallery_Public::get_photo_date($metadate);
                            $user = get_user_by( 'ID', $post->post_author );
                            $address = $this->rework_address($post->post_title);

                            $user_url='';
                            $url_checked = get_user_meta( $post->post_author, 'user_url', true);
                            if ($url_checked == 'OK') {
                                $user_url = $user->user_url;
                            }
                            //error_log("get_bb_images user_url=".$user_url);

                            $image = (object) [
                                'id'            => $pid,
                                'url_medium'    => $url_img_medium[0],
                                'url_full'      => $url_img_full[0],
                                'address'       => $address,
                                'content'       => $post->content,
                                'date'          => $date,
                                'user'          => $user->display_name,
                                'user_url'      => $user_url,
                                'latitude'      => $latitude,
                                'longitude'     => $longitude,
                                'vignette'      => $vignette,
                            ];
                            
                            //error_log("get_bb_images image=".print_r($image, true));
                            $images[] = $image;
                            $ids[] = $pid;
                        }
                    }
                }// end foreach

                //error_log("get_bb_images images=".print_r($images, true));
                //error_log("get_bb_images ids=".print_r($ids, true));

                $data = [
                    "images" => $images,
                    "ids" => $ids,
                    "action" => "update"
                ];
                
                // add focus on pid
                if ($item_fpid) {
                    $data["fpid"] = $item_fpid['post_id'];
                }
            }
        } 
        else {
            error_log("get_bb_images result is empty");
            $data = [
                "images" => [],
                "ids" => [],
                "action" => "update",
            ];
        }

        wp_send_json_success( $data, 200 );
        wp_die();
    }

    // $brief : if the current displayed list is included into new list
    // do nothing, keep the list
    private function find_action($results, $current_ids) {

        if (!empty($current_ids)) {
            $list_current = explode(",", $current_ids);
            //error_log("find_action list_current=".print_r($list_current, true));

            $list_new = array_map(function($item) {
                return $item['post_id'];
            }, $results);
            //error_log("find_action list_new=".print_r($list_new, true));
            $string_for_log = implode(",", $list_new);
            //error_log("find_action list_new=".$string_for_log);

            if (empty(array_diff($list_current, $list_new))) {
                // new list fully includes the current list 

                if (count($list_current) == 40) {
                    error_log("find_action count=40, return 'nothing'");
                    return "nothing";
                }

                if (count($list_current) == count($list_new)) {
                    error_log("find_action same sizes, return 'nothing'");
                    return "nothing";
                }
                //error_log("find_action OUT 'nothing'");
            }
        }
        //error_log("find_action OUT ''");
        return "";
    }
    
    // $id = gallery id
    // return an array with image IDs
    // TODO, used elsewhere, to be defined in a static method
    public function ban_image() {

        //error_log("ban_image IN");
        //error_log("ban_image REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'show_planet' ) ) {
            error_log("ban_image nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }

        if ( ! current_user_can( 'administrator' ) ) {
            error_log("ban_image No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }
        
        $pid = sanitize_text_field($_REQUEST['pid']);

        update_post_meta($pid , 'admin_status', Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);
        Glp_Check_Photos_Public::update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);

        wp_send_json_success( null, 200 );
        wp_die();
    }
}
