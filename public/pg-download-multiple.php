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
class Pg_Download_Multiple_Public {

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
        add_shortcode( 'pg_download_multiple', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'pg-download.css', plugin_dir_url( __FILE__ ) . 'css/pg-download.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-glp-public.js', plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-exif-js.js', plugin_dir_url( __FILE__ ) . 'js/exif-js.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-pg-download.js', plugin_dir_url( __FILE__ ) . 'js/pg-download.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
        // wp_localize_script($this->plugin_name, 'gal_ajax_public', array('ajax_url' => admin_url('admin-ajax.php')));

    }

    public function enqueue_styles_early(){

        $settings_options = Gallery_Settings_Actions::ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode(stripcslashes($settings_options), true);
        }else{
            $settings_options = array();
        }

        // General CSS File
        $settings_options['gpg_exclude_general_css'] = isset($settings_options['gpg_exclude_general_css']) ? esc_attr( $settings_options['gpg_exclude_general_css'] ) : 'off';
        $gpg_exclude_general_css = (isset($settings_options['gpg_exclude_general_css']) && esc_attr( $settings_options['gpg_exclude_general_css'] ) == "on") ? true : false;

        if ( ! $gpg_exclude_general_css ) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        }else {
            if ( ! is_front_page() ) {
                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
            }
        }
        
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){
        
        global $wpdb;
        //$id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        // TODO check if user id is a valid user
        // $medias = 
        // if(!$medias){
        //     // TODO display no photos yet, upload your first photo
        //     return "[pg_download_multiple id='".$id."']";
        // }
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('download_multiple_photos');
        error_log("pg_show_page admin_ajax_url=".$admin_ajax_url);
        $html_code = '
        <div class="modal fade" id="multipleDowloadModal" tabindex="-1" aria-labelledby="multipleDowloadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="container">
                        <div class="modal-header">
                            <h5 class="modal-title" id="multipleDowloadModalLabel">Sélection multiple de photos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>                            
                        <div class="modal-body">
                            <form id="custom-upload-form" style="text-align=center;">
                                <input type="hidden" id="pg_admin_ajax_url" value="'.$admin_ajax_url.'"/>
                                <input type="hidden" id="download_nonce" value="'.$nonce.'"/>
                                <label for="fileInput" class="custom-file-upload">
                                    Select photos...
                                </label>
                                <br/>
                                <input type="file" id="fileInput" name="custom-file[]" multiple>
                                <div id="modal-item-list"></div>
                                <br/>
                                <button type="submit" id="multiple-upload" class="btn btn-primary" style="display: none">Télécharger</button>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="close-multiple-modal" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                        <div id="progressContainer"></div>
                    </div>
                </div>
            </div>
        </div>';

        return $html_code;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    // callback on request to download photos
    public function download_multiple_photos() {
        error_log("download_multiple_photos IN REQUEST ".print_r($_REQUEST, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'download_multiple_photos' ) ) {
            error_log("download_multiple_photos nonce not found");
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
        // check if gallery exists
        $gallery = $gallery = Glp_Galleries_List_Table::get_gallery_by_id($_REQUEST['galleryId']);
        if (!$gallery) {
            error_log("download_multiple_photos Gallery Not found ");
            wp_send_json_error( "NOK.", 404 );
            return;
        }

        $title = sanitize_text_field( $_POST['title'] );
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        error_log("download_multiple_photos movefile ".print_r($movefile, true));
        // echo $movefile['url'];
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


            //update_post_meta($attachment_id , 'latitude', $_REQUEST['lat']);
            //update_post_meta($attachment_id , 'longitude', $_REQUEST['lon']);
            //update_post_meta($attachment_id , 'origin', $_REQUEST['origin']);
            update_post_meta($attachment_id , 'altitude', $_REQUEST['altitude']);
            //update_post_meta($attachment_id , 'date', $_REQUEST['date']); // date of shooting

            Pg_Geoposts_Table::insert_post( $attachment_id,
                $_REQUEST['lat'],
                $_REQUEST['lon'],
                $_REQUEST['origin'],
                $_REQUEST['date']);
            
            // if gallery id , add to gallery
            if ($_REQUEST['galleryId']) {
                $this->update_gallery_image($_REQUEST['galleryId'], $attachment_id);
            }
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

    // $gallery_id = id of the gallery
    // $images = array of image id
    function update_gallery_image($gallery_id, $image_id){
        error_log("update_gallery_image IN gallery_id=".$gallery_id.", image_id=".$image_id);
        global $wpdb;
        $gallery_table = $wpdb->prefix . "glp_gallery";

        if( isset($image_id) && $image_id != '' && isset($gallery_id) && $gallery_id != '') {

            // first get actual list of images ids
            $image_ids = $this->pg_get_medias_by_gallery($gallery_id);
            if ($image_ids != null ) {
                // add to array
                $image_ids[] = $image_id;
            }
            else {
                // create array
                $image_ids = array($image_id);
            }

            // then update the gallery
            $images = sanitize_text_field( implode( "***", array_filter($image_ids)) );
            //error_log("update_gallery_image ");
            $gallery_result = $wpdb->update(
                $gallery_table,
                array("images_ids" => $images),
                array( "id" => $gallery_id ),
                array( "%s" ),
                array( "%d" )
            );
            error_log("update_gallery_image OUT");
        }
    }

    public function pg_get_medias_by_gallery( $id ) {
        error_log("pg_get_medias_by_gallery IN gallery_id=".$id);
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";
        $result = $wpdb->get_row( $sql, "ARRAY_A" );
        if ( is_null( $result ) || empty( $result ) ) {
            error_log("pg_get_medias_by_gallery OUT null");
            return null;
        }
        error_log("pg_get_medias_by_gallery images_id=".$result["images_ids"]);
        $image_ids = explode( "***", $result["images_ids"]);
        return $image_ids;
    }
}
