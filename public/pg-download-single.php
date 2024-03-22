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

        //wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        //wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'pg-download.css', plugin_dir_url( __FILE__ ) . 'css/pg-download.css', array(), $this->version, 'all' );
		// TODO mettre à jour bootstrap
        //wp_enqueue_style( 'ays_pb_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        // TODO clean all thi stuff
        //wp_enqueue_script( 'jquery-effects-core' );
        //wp_enqueue_script( 'jquery-ui-sortable' );
        //wp_enqueue_media();
        //wp_enqueue_script( $this->plugin_name.'-imagesloaded.min.js', 'https://unpkg.com/imagesloaded@4.1.4/imagesloaded.pkgd.min.js', array( 'jquery' ), null, true );
        //wp_enqueue_script( $this->plugin_name.'-picturefill.min.js', plugin_dir_url( __FILE__ ) . 'js/picturefill.min.js', array( 'jquery' ), $this->version, true );
        //wp_enqueue_script( $this->plugin_name.'-jquery.mousewheel.min.js', plugin_dir_url( __FILE__ ) . 'js/jquery.mousewheel.min.js', array( 'jquery' ), $this->version, true );
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
        error_log("pg_generate_page IN");

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        // TODO check if user id is a valid user
        // $medias = 
        // if(!$medias){
        //     // TODO display no photos yet, upload your first photo
        //     return "[pg_download_single id='".$id."']";
        // }
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('download_single_photo');
        error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);
        //TODO remove input-group class
        $html_code = '
        <div class="container">
            <form id="upload-single-form">
                <label for="fileInput" class="custom-file-upload">
                    Select Photo
                </label>
                <input type="file" id="fileInput" name="custom-file[]">
                <input type="hidden" id="pg_admin_ajax_url" value="'.$admin_ajax_url.'"/>
                <input type="hidden" id="download_nonce" value="'.$nonce.'"/>
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
                    <button type="submit" class="btn btn-primary" id="single-upload">Upload Photo</button>
                </div>
            </form>
            <div id="progressContainer"></div>
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
    public function download_single_photo() {
        error_log("download_single_photo IN");
        error_log("download_single_photo REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'download_single_photo' ) ) {
            error_log("download_single_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
        }
        $title = sanitize_text_field( $_POST['title'] );
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        error_log("download_single_photo movefile ".print_r($movefile, true));
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

            update_post_meta($attachment_id , 'latitude', $_REQUEST['lat']);
            update_post_meta($attachment_id , 'longitude', $_REQUEST['lon']);
            update_post_meta($attachment_id , 'origin', $_REQUEST['origin']);


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
