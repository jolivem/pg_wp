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
class Glp_Check_Photos_Public {

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
        add_shortcode( 'glp_check_photos', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );


        // TODO lightgallery est payant !!
        //wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        $settings_options = Gallery_Settings_Actions::ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode(stripcslashes($settings_options), true);
        }else{
            $settings_options = array();
        }

        // General CSS File
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        error_log("Glp_Check_Photos_Public::pg_generate_page IN ".print_r($attr, true));
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        $user_id = get_current_user_id();
        $medias = $this->pg_get_medias_to_be_checked();
        if(!$medias){
            $html_code = "
            <div>Aucune photo à vérifier.<div>";
            return $html_code;    
        }

        $admin_ajax_url = admin_url('admin-ajax.php');
        //$admin_post_url = admin_url('admin-post.php');
        $nonce = wp_create_nonce('admin_check');
        $edit_photo_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_PHOTO); // TODO move 186 to a global constant or get by Title


        $html_code = "
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <input type='hidden' id='pg_edit_photo_url' value='$edit_photo_url'/>
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div id='delete-photo-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        Supprimée !
                    </div>
                </div>
            </div>
        </div>
        <div class='container' id='user-item-list'>";

        $html_code .= $this->render_images($medias);
        $html_code .= 
        '</div>';

        return $html_code;
    } 

    function render_images($medias){
        $html='';

        // loop for each media
        foreach($medias as $item){
            //error_log("render_images item:".print_r($item, true));
            $img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($item->ID, "large");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }

            $statext = Pg_Edit_Gallery_Public::get_photo_status($item->ID);

            //error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="flex-container">
                
                <img src="'.$img_src.'" class="full-miniature-big"></img>
                <div class="photo-text-container">
                    <div class="footer-desc-font" style="overflow: visible;">'.$item->post_title.'</div>
                    <div class="footer-edit-gallery">
                        <div>Date : '.$item->post_date.'</div>
                    </div>
                </div>
                <div class="options-photo-gallery" style="background-color: lightgreen">
                    <i class="admin-photo-option fas fa-thumbs-up" aria-hidden="true" data-postid="'.$item->ID.'"></i>
                    <i class="admin-photo-option fas fa-thumbs-down" aria-hidden="true" data-postid="'.$item->ID.'"></i>
                </div>
            </div>';
            

        }
        return $html;
    }

    // public function ays_gallery_replace_message_variables($content, $data){
    //     foreach($data as $variable => $value){
    //         $content = str_replace("%%".$variable."%%", $value, $content);
    //     }
    //     return $content;
    // }

    public function pg_get_medias_to_be_checked(  ) {


        $args = array(
            //'author'         => $user_id,
            'post_type'      => 'attachment',
            'post_status'    => 'inherit,private', // Adjust post status as needed
            'posts_per_page' => -1, // Retrieve all attachments
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => 'user_status', 
                    'value' => Pg_Edit_Photo_Public::USER_STATUS_PUBLIC, 
                    'compare' => '='
                ),
                array(
                    'key'   => 'admin_status',
                    'value' => Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_SEEN,
                    'compare' => '=',
                )
            ),            
        );
        
        $query = new WP_Query( $args );
        $medias = $query->get_posts();

        return $medias;
    }

    // callback on request to delete a photo
    public function admin_valid_photo() {
        error_log("admin_valid_photo IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_valid_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id!== 1) {
            error_log("admin_valid_photo No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['pid'] )){
            error_log("admin_valid_photo no pid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $pid = sanitize_text_field($_REQUEST['pid']);

        update_post_meta($pid , 'admin_status', Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK);
        $this->update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK);
        
        error_log( "admin_valid_photo Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    } 

    public function admin_reject_photo() {
        error_log("admin_reject_photo IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_reject_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id != 1) {
            error_log("admin_reject_photo No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['pid'] )){
            error_log("admin_reject_photo no pid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $pid = sanitize_text_field($_REQUEST['pid']);
        update_post_meta($pid , 'admin_status', Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);
        $this->update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);

        error_log( "admin_reject_photo Respond success");
        wp_send_json_success( null, 200);
        wp_die();
    }

    private function update_visibility($post_id, $admin_status) {
        error_log("update_visibility IN id=$post_id admin_status=$admin_status");

        $user_status = get_post_meta($post_id, 'user_status', true);
        error_log("update_visibility user_status=$user_status");

        if ($user_status == Pg_Edit_Photo_Public::USER_STATUS_PUBLIC && $admin_status == Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK) {
            Pg_Geoposts_Table::update_visible($post_id, Pg_Geoposts_Table::PUBLIC_VISIBLE);
        }
        else {
            Pg_Geoposts_Table::update_visible($post_id, Pg_Geoposts_Table::PUBLIC_HIDDEN);
        }
    }

}

