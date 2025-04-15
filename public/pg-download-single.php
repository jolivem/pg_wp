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
class Pg_Download_Single_Public {

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
        add_shortcode( 'pg_download_single', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'pg-download.css', plugin_dir_url( __FILE__ ) . 'css/pg-download.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name.'-pg-public.css', plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-pg-public.js', plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-exif-js.js', plugin_dir_url( __FILE__ ) . 'js/exif-js.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-pg-download.js', plugin_dir_url( __FILE__ ) . 'js/pg-download.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );

        
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
        // wp_localize_script($this->plugin_name, 'gal_ajax_public', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script( $this->plugin_name.'-geocoding.js', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyANlJ9pdMlkfsy3ZzheOWMKK35iqTHDu0o&v=weekly', array( 'jquery' ), $this->version, 
            array(
                    'strategy' => 'defer'
                )  );

    }

    public function enqueue_styles_early(){

        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){

        // if (! isset($_GET['gid'])) {
        //     error_log("Pg_Download_Single_Public::pg_generate_page Missing parameters");
        //     wp_die();
        // }

        if ( ! current_user_can( 'administrator' ) ) {
            error_log("download_singe No ADMIN");
            my_custom_404();
            wp_die();
        }


        ob_start();
        error_log("Pg_Download_Single_Public::pg_generate_page IN");

        $this->enqueue_styles();
        $this->enqueue_scripts();

        //use the post ID provided in the URL
        $gid=$_GET['gid']; 
        echo $this->pg_show_page( $gid );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $gid ){
        
        global $wpdb;
        
        // TODO check if user id is a valid user
        // $medias = 
        // if(!$medias){
        //     // TODO display no photos yet, upload your first photo
        //     return "[pg_download_single id='".$id."']";
        // }
        $admin_ajax_url = admin_url('admin-ajax.php');
        $edit_gallery_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_GALLERY);
        $edit_gallery_url .= "?gid=$gid";

        $nonce = wp_create_nonce('download_multiple_photos');
        error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);
        //TODO remove input-group class
        $html_code = '
        <input type="hidden" id="gallery-id" name="gallery-id" value="'.$gid.'"/>
        <input type="hidden" id="pg_admin_ajax_url" value="'.$admin_ajax_url.'"/>
        <input type="hidden" id="download_nonce" value="'.$nonce.'"/>
        <div class="container">
            <form id="upload-single-form">
                <label for="fileInput" class="btn btn-primary">
                    Selectionner la photo
                </label>
                <input type="file" id="fileInput" name="custom-file[]">
                <div id="photo-to-download" style="display:flex; justify-content: center;"></div>
                <div id="download-single-block" style="display:none">
                    <h5 id="title-latlon">Saisir les coordonées GPS</h5>
                    <div class="input-group has-validation">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="latitude" aria-describedby="latitudeHelp" placeholder="" required>
                            <label for="latitude">Latitude</label>
                            <div id="latitude-feedback" class="invalid-feedback"></div>
                            <small id="latitudeHelp" class="form-text text-muted">Exemple 39.6983333 ou 39°41\'54.1"N</small>
                        </div>
                    </div>
                    <div class="input-group has-validation">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="longitude"  aria-describedby="longitudeHelp" placeholder="Enter Longitude" required>
                            <label for="longitude" class="form-label">Longitude</label>
                            <div id="longitude-feedback" class="invalid-feedback"></div>
                            <small id="longitudeHelp" class="form-text text-muted">Exemple -31.104722 ou 31°06\'17.6"E</small>
                        </div>
                    </div>
                    <div id="gmap-position">
                        <h5>Ou copier coller la position à partir de GoogleMap</h5>
                        <div class="input-group has-validation">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="input-google-position" placeholder="">
                                <label for="input-google-position">GoogleMap position</label>
                                <small id="emailHelp" class="form-text text-muted">Exemple 39°41\'54.1"S 31°06\'17.6"E</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="single-upload">Télécharger</button>
                </div>
            </form>
            <div id="progressContainer"></div>
            <br/>
            <a href="'.$edit_gallery_url.'">Retour à la galerie</a>
        </div>';

        return $html_code;
    } // end ays_show_galery()

    // public function ays_gallery_replace_message_variables($content, $data){
    //     foreach($data as $variable => $value){
    //         $content = str_replace("%%".$variable."%%", $value, $content);
    //     }
    //     return $content;
    // }

    // callback on request to download photos
    public function download_single_photo() {
        error_log("download_single_photo REQUEST ".print_r($_REQUEST, true));
        error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'download_single_photo' ) ) {
            error_log("download_single_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }
        if( ! isset( $_REQUEST['galleryId'] ) ) {
            error_log("download_multiple_photos No gallery ");
            wp_send_json_error( "NOK.", 403 );
            return;
        }
        if( $_REQUEST['galleryId'] == -1 ) {
            error_log("download_multiple_photos No gallery ");
            wp_send_json_error( "NOK.", 404 );
            return;
        }
        if( $_REQUEST['lat'] == "NaN" ) {
            error_log("download_multiple_photos Latitude = NaN ");
            wp_send_json_error( "NOK.", 404 );
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("download_multiple_photos No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        // check if gallery exists
        $gallery = Glp_Galleries_List_Table::get_gallery_by_id($_REQUEST['galleryId']);
        if (!$gallery) {
            error_log("download_multiple_photos Gallery Not found ");
            wp_send_json_error( "NOK.", 404 );
            return;
        }

        $upload_dir = wp_upload_dir();
        //error_log("download_multiple_photos upload_dir ".print_r($upload_dir, true));
        $image_path = $upload_dir['path'] . "/" . $_FILES['file']['name'];
        //error_log("download_multiple_photos image_path ".$image_path);
        if ( file_exists( $image_path ) ) {
            error_log("download_multiple_photos FILE EXISTS");

            // check if it belongs to the same user and if it is the same date
            $found_id = $this->find_media_by_author_and_name($user_id, $_FILES['file']['name']);
            if ($found_id != 0) {
                // image already found in media library !
                error_log( "File already present in media library, id=$found_id");
    
                // Add image to the current gallery if not already present
                $this->add_image_to_gallery($_REQUEST['galleryId'], $found_id);
                $data=array('message' => 'done');
                wp_send_json_success( $data, 200);
            }
    
        }


        //$title = sanitize_text_field( $_POST['title'] );
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        error_log("download_single_photo movefile ".print_r($movefile, true));
        // echo $movefile['url'];

        $address = $_REQUEST['address'];
        $address_json = $_REQUEST['address_json'];
        //error_log("download_multiple_photos address = $address");
        $country_code = sanitize_text_field( $_REQUEST['country_code'] );
        $title = sanitize_text_field( $_REQUEST['title'] );

        if ($movefile && !isset($movefile['error'])) {
            error_log( "File Upload Successfully");

            // it is time to add our uploaded image into WordPress media library
            $attachment_id = wp_insert_attachment(
                array(
                    'guid'           => $movefile[ 'url' ],
                    'post_mime_type' => $movefile[ 'type' ],
                    'post_title'     => basename( $movefile[ 'file' ] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                ),
                $movefile[ 'file' ]
            );

            if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
                return false;
            }

            // update medatata, regenerate image sizes
            //require_once( ABSPATH . 'wp-admin/includes/image.php' );

            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $movefile[ 'file' ] )
            );

            update_post_meta($attachment_id , 'latitude', $_REQUEST['lat']);
            update_post_meta($attachment_id , 'longitude', $_REQUEST['lon']);
            update_post_meta($attachment_id , 'is_exif', $_REQUEST['is_exif']);


        } else {
            /**
             * Error generated by _wp_handle_upload()
             * @see _wp_handle_upload() in wp-admin/includes/file.php
             */
            error_log( $movefile['error']);
        }
        error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }
}
