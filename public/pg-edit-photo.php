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
class Pg_Edit_Photo_Public {

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
        add_shortcode( 'pg_edit_photo', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style('leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
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
        wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);

        wp_enqueue_script( $this->plugin_name.'-pg-map.js', plugin_dir_url( __FILE__ ) . 'js/pg-map.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-map.js', 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        error_log("pg_generate_page IN ".print_r($attr, true));

        // TODO check that gallery belongs to the current user

        //Test with ID=67
        $attr['id']=2; // test withs gallery id = 2

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){

        error_log("pg_show_page IN ".print_r($attr, true));
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        // TODO check if user id is a valid user
        // $medias = 
        // if(!$medias){
        //     // TODO display no photos yet, upload your first photo
        //     return "[pg_download_single id='".$id."']";
        // }

        $latitude = get_post_meta($id, 'latitude', true);
        $longitude = get_post_meta($id, 'longitude', true);
        $vignette = get_post_meta($id, 'vignette', true);

        error_log("pg_show_page latitude=".$latitude.", longitude=".$longitude.", vignette=".$vignette);

        //$vignette_dropdown = '<select id="select-country" name="attachments[' . $post->ID . '][vignette]">';
        $vignette_options = $this->get_vignette_options();
        $html_options = '';
        foreach ($vignette_options as $key => $label) {
            $html_options .= '<option value="' . esc_attr($key) . '" ' . selected($vignette, $key, false) . '>' . esc_html($label) . '</option>';
        }

        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('edit_photo');
        error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);

        $url_img = wp_get_attachment_image_src($id, "medium");
        if ($url_img != false) {
            $img_src = $url_img[0];
        }
        //error_log("render_images url:".print_r($url_img, true));
        // TODO check url_img is OK, add try catch
         $html_code = '
        <div class="container">
            <div style="display:flex; justify-content: center;">
                <img style="height:200px; width:auto; border: 1px solid #BBB; padding:3px; border-radius: 4px" src="'.$img_src.'" alt="">
            </div>
            <br>
            <form id="edit-photo-form">
                <input type="hidden" id="latitude" value="'.$latitude.'"/>
                <input type="hidden" id="longitude" value="'.$longitude.'"/>
                <input type="hidden" id="vignette" value="'.$vignette.'"/>
                <input type="hidden" id="post_id" value="'.$id.'"/>
                <input type="hidden" id="pg_admin_ajax_url" value="'.$admin_ajax_url.'"/>
                <input type="hidden" id="pg_nonce" value="'.$nonce.'"/>
                <div id="download-single-block">
                    <div id="photo-to-download"></div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="photo-title" aria-describedby="titleHelp" placeholder="">
                        <label for="photo-title">Titre</label>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea rows="5" style="height:100%;" class="form-control" placeholder="" id="photo-description"></textarea>
                        <label for="photo-description">Description</label>                        
                    </div>
                    <div class="edit-photo-flex-container" style="background-color: lightyellow">
                        <div class="edit-photo-select">
                            <select id="select-country" class="form-select mb-3" aria-label="">
                                <option selected>Sélectionner la zone</option>'
                                .$html_options.
                            '</select>
                        </div>
                        <div id="leaflet-map" class="edit-photo-map"></div>
                    </div>
                        
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="worldmap" value="on" checked>
                        <label class="form-check-label" for="worldmap">Autoriser l\'affichage sur la carte mondiale</label>
                    </div>
                    <br>
                    <div>
                        <button type="submit" class="btn btn-primary" id="edit-photo">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>';

        return $html_code;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    function get_vignette_options() {

        // Afficher le chemin
        // echo $directory_courant;
        // echo GLP_DIR;
    
        $dict = array();
        // Add None
        $dict["None"] = "None";

        $worldfile = GLP_DIR . 'assets/world.json';
        //echo $worldfile;
        // // Utiliser glob pour obtenir la liste des fichiers dans le dossier
        //$files = glob($directory . '/*');
        $json = file_get_contents($worldfile); 
        if ($json === false) {
            // deal with error...
        }
        
        $json_a = json_decode($json, true);
        if ($json_a === null) {
            // deal with error...
        }
        
        foreach ($json_a as $country) {
            $file = $country['file'];
            //$str = json_encode($country);
            //echo $str
            $option = str_replace('_', ' ', $file);
            $option = str_replace('.geojson', '', $option);
            $dict[$file] = $option;
        }

        return $dict;

    }
    // callback on request to download photos
    public function user_edit_photo() {
        error_log("user_edit_photo IN");
        error_log("user_edit_photo REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_photo' ) ) {
            error_log("user_edit_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
        }


        $post_id = sanitize_text_field( $_REQUEST['post_id'] );
        $title = sanitize_text_field( $_REQUEST['title'] );
        $desc = sanitize_text_field( $_REQUEST['desc'] );

        if ( wp_attachment_is_image( $post_id ) ) {
            //$my_image_title = get_post( $post_ID )->post_title;
            // Create an array with the image meta (Title, Caption,
            // Description) to be updated
            // Note: comment out the Excerpt/Caption or Content/Description
            // lines if not needed
            $my_image_meta = array(
                // Specify the image (ID) to be updated
                'ID' => $post_id,
                // Set image Title to sanitized title
                'post_title' => $title,
                // Set image Caption (Excerpt) to sanitized title
                'post_excerpt' => $title,
                // Set image Description (Content) to sanitized title
                'post_content' => $desc
            );
            
            // Set the image meta (e.g. Title, Excerpt, Content)
            wp_update_post( $my_image_meta );

            // Set the image Alt-Text
            //update_post_meta( $post_id, '_wp_attachment_image_alt', $title );

            // Set the country
            update_post_meta($post_id , 'vignette', $_REQUEST['vignette']);

            // Set the 'worldmap'
            update_post_meta($post_id , 'user-worldmap', $_REQUEST['worldmap']);

        }
        else {
            error_log("user_edit_photo not a photo");
        }

        error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }
}
