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

    const ADMIN_STATUS_NOT_SEEN = 0;
    const ADMIN_STATUS_PUBLIC_OK = 1;
    const ADMIN_STATUS_NOT_OK = 2;

    const USER_STATUS_PUBLIC = 'public';
    const USER_STATUS_PRIVATE = 'private';

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
        //error_log("Pg_Edit_Photo_Public::ctor plugin_name = ".$plugin_name);
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
        //wp_enqueue_style('leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name.'-pg-public.css', plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );
    }        

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-pg-public.js', plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-public.js', 'ays_vars', 
            array(
                'base_url' => GLP_BASE_URL,
                'public_photo' => esc_html__("Affichage autorisé sur la galerie publique", $this->plugin_name),
                'private_photo' => esc_html__("Photo privée", $this->plugin_name))
            );

    }

    public function enqueue_styles_early(){

        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        
        // error_log("Pg_Edit_Photo_Public::pg_generate_page IN ".print_r($attr, true));
        // error_log("pg_generate_page REQUEST ".print_r($_REQUEST, true));
        // error_log("pg_generate_page GET ".print_r($_GET, true));

        // TODO check that gallery belongs to the current user
        
        if (! isset($_GET['pid']) && ! current_user_can( 'administrator' )) {
            error_log("Pg_Edit_Photo_Public::pg_generate_page Missing parameters");
            my_custom_404();
            wp_die();
        }

        //use the post ID provided in the URL
        $pid=$_GET['pid']; 
        $post = get_post($pid);
        $user_id = get_current_user_id();
        if ($post->post_author != $user_id) {
            error_log("Pg_Edit_Photo_Public Not current user photo");
            // TODO 404 NOT FOUND
            my_custom_404();
            wp_die();
        }

        ob_start();

        // gid is optional, it is present when editing the gallery photos
        $gid=$_GET['gid']; 

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $pid, $gid, $user_id );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $pid, $gid, $user_id){

        // if gid is empty, request comes from the page "My Photos"

        // error_log("pg_show_page IN photo id = $pid, gid = $gid");
        
        global $wpdb;
        $post = get_post($pid);
        if ($post != null) {

            $user_status_checked = "";
            $user_status_label = esc_html__("Photo privée", $this->plugin_name);
            $user_status = self::USER_STATUS_PRIVATE;
            if (get_post_meta($pid, 'user_status', true) == self::USER_STATUS_PUBLIC) {
                $user_status_checked = " checked";
                $user_status = self::USER_STATUS_PUBLIC;
                $user_status_label = esc_html__("Affichage autorisé sur la galerie publique", $this->plugin_name);
            }

            $images_str='';
            if (!empty($gid)) {
                $images_id = Pg_Edit_Gallery_Public::pg_get_medias_by_gallery( $gid );
                if ($images_id == null) {
                    my_custom_404();
                    wp_die();
                }
                $images_str = implode(",", $images_id);
            }
            else {
                $images_id = $this->pg_get_medias_by_user( $user_id );
                if ($images_id == null) {
                    my_custom_404();
                    wp_die();
                }
                $images_str = implode(",", $images_id);
            }

            $content = stripslashes($post->post_content);

            $admin_ajax_url = admin_url('admin-ajax.php');
            $nonce = wp_create_nonce('edit_photo');
            // error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);

            $url_img = wp_get_attachment_image_src($pid, "medium");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }

            $edit_photo_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_PHOTO);
            $my_photo_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_MY_PHOTOS); 
            $edit_gallery_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_GALLERY);
            $edit_gallery_url .= "?gid=$gid";

            // TODO check url_img is OK, add try catch
            $html_code = "
            <input type='hidden' id='images_id' value='$images_str'/>
            <input type='hidden' id='gallery-id' value='$gid'/>
            <input type='hidden' id='pg_edit_photo_url' value='$edit_photo_url'/>
            <input type='hidden' id='post_id' value='$pid'/>
            <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
            <input type='hidden' id='pg_nonce' value='$nonce'/>
            <div class='toast-container position-fixed bottom-0 end-0 p-3'>
                <div id='save-photo-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                    <div class='d-flex'>
                        <div class='toast-body'>
                            ".esc_html__("Enregistré !", $this->plugin_name)."
                        </div>
                    </div>
                </div>
            </div>
            <div class='pg-container'>";
            // add right and left buttons
            $html_code .= "
                <div class='flex-container-photo'>
                    <div class='slider-options-left' style='background-color: lightblue'>
                        <div>
                            <div class='edit-photo-option fas fa-angle-double-left' aria-hidden='true' data-postid='$pid'></div>
                        </div>
                    </div>
                    <div class='pg-edit-img-cont'>
                        <img class='pg-edit-img' src='$img_src' alt=''>
                    </div>
                    <div class='slider-options-right' style='background-color: lightblue'>
                        <div>
                            <div class='edit-photo-option fas fa-angle-double-right' aria-hidden='true' data-postid='$pid'></div>
                        </div>
                    </div>
                </div>
                <div id='cpt-photo' class='cpt-photo'></div>";

            // Add description
            $html_code .= "
                <div class='form-floating mb-3'>
                    <textarea rows='3' style='height:100%;' class='form-control' placeholder='' id='photo-description'>$content</textarea>
                    <label for='photo-description'>".esc_html__("Description", $this->plugin_name)."</label>                        
                </div>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' role='switch' id='user_status' value='$user_status'$user_status_checked>
                    <label class='form-check-label' for='user_status' id='user_status_label'>$user_status_label</label>
                </div>
                <br>
                <div class='flex-space-between'>";
            if (!empty($gid)) {
                $html_code .= "
                    <a href='$edit_gallery_url'>".esc_html__("Retour à la galerie", $this->plugin_name)."</a>";
            }
            else {
                $html_code .= "
                    <a href='$my_photo_url'>".esc_html__("Retour à Mes photos", $this->plugin_name)."</a>";

            }
            $html_code .= "
                    <button type='button' class='btn btn-primary' id='btn-save-photo' style='float: inline-end;'>".esc_html__("Enregistrer", $this->plugin_name)."</button>
                </div>
            </div>";
            return $html_code;
        }
        else {
            return "";
        }
        
    } // end ays_show_galery()

    //
    // callback on request to save photos
    //
    public function user_save_photo() {
        //error_log("user_save_photo IN");
        // error_log("user_save_photo REQUEST ".print_r($_REQUEST, true));
        //error_log("user_save_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_photo' ) ) {
            error_log("user_save_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("user_save_photo No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        $post_id = sanitize_text_field( $_REQUEST['post_id'] );
        //$title = sanitize_text_field( $_REQUEST['title'] );
        $desc = sanitize_text_field( $_REQUEST['desc'] );
        //$vignette = sanitize_text_field( $_REQUEST['vignette'] );
        $user_status = sanitize_text_field( $_REQUEST['user_status'] );

        if ( wp_attachment_is_image( $post_id ) ) {
            //$my_image_title = get_post( $post_ID )->post_title;
            // Create an array with the image meta (Title, Caption,
            // Description) to be updated
            // Note: comment out the Excerpt/Caption or Content/Description
            // lines if not needed
            $my_image_meta = array(
                // Specify the image (ID) to be updated
                'ID' => $post_id,
                // Set image Description (Content) to sanitized title
                'post_content' => $desc
            );
            
            // Set the image meta (e.g. Title, Excerpt, Content)
            wp_update_post( $my_image_meta );

            // Set the image Alt-Text
            //update_post_meta( $post_id, '_wp_attachment_image_alt', $title );

            // Set the country
            //update_post_meta($post_id , 'vignette', $vignette);

            // Set the 'user_status'
            update_post_meta($post_id , 'user_status', $user_status);

            // 
            Glp_Check_Photos_Public::update_visibility($post_id, $user_status);

        }
        else {
            error_log("user_save_photo not a photo");
        }

        // error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }

    //
    // callback on request to get a photo
    //
    public function user_get_photo() {
        //error_log("user_get_photo IN");
        // error_log("user_get_photo REQUEST ".print_r($_REQUEST, true));
        //error_log("user_get_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_photo' ) ) {
            error_log("user_get_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("user_get_photo No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        $pid = sanitize_text_field( $_REQUEST['pid'] );
        //error_log("user_get_photo pid=".$pid);

        if ( wp_attachment_is_image( $pid ) ) {
            $post = get_post($pid);
            //error_log("user_get_photo post=".print_r($post, true));
            if ($post != null) {
    
                $user_status_checked = "";
                $user_status_label = esc_html__("Photo privée", $this->plugin_name);
                $user_status = self::USER_STATUS_PRIVATE;
                if (get_post_meta($pid, 'user_status', true) == self::USER_STATUS_PUBLIC) {
                    $user_status_checked = " checked";
                    $user_status = self::USER_STATUS_PUBLIC;
                    $user_status_label = esc_html__("Affichage autorisé sur la galerie publique", $this->plugin_name);
                }
    
                $content = stripslashes($post->post_content);
                //error_log("user_get_photo content=".$content);
    
                $url_img = wp_get_attachment_image_src($pid, "medium");
                if ($url_img != false) {
                    $img_src = $url_img[0];
                
                    $data = [
                        "img_src" => $img_src,
                        "pid" => $pid,
                        "content" => $content,
                        "user_status" => $user_status,
                        "user_status_label" => $user_status_label
                    ];
                    //error_log("user_get_photo data=".print_r($data, true));
                    wp_send_json_success( $data, 200 );
                }
            }

            error_log("user_save_photo not a photo");
        }

        // error_log( "Respond success");
        wp_send_json_error( "NOT FOUND", 404);
        wp_die();
        
    }

    public function pg_get_medias_by_user( $user_id ) {
        // error_log("pg_get_medias_by_gallery IN gallery_id=".$id);
         global $wpdb;

         $args = array(
            'author'         => $user_id,
            'post_type'      => 'attachment',
            'post_status'    => 'inherit,private', // Adjust post status as needed
            'posts_per_page' => -1, // Retrieve all attachments
        );
        
        $query = new WP_Query( $args );
        $medias = $query->get_posts();
        $ids = [];
        foreach($medias as $item){
            //error_log("render_images item:".print_r($item, true));
            $ids[] = $item->ID;
        }

        return $ids;

     }
 
}
