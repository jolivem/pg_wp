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
class Glp_User_Photos_Public {

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
        add_shortcode( 'glp_user_photos', array($this, 'pg_generate_page') );
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
        error_log("Glp_User_Photos_Public::pg_generate_page IN ");
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page();

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page(){
        
        global $wpdb;
        
        $user_id = get_current_user_id();
        error_log("Glp_User_Photos_Public::pg_show_page user_id =$user_id");
        $medias = $this->pg_get_medias_by_user($user_id);
        if(!$medias){
            // TODO add a link the the gallery creationAdd gal
            $edit_gallery_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_GALLERY); // TODO move 186 to a global constant or get by Title
            $edit_gallery_url .= "?gid=-1";
            //<input type='hidden' id='pg_edit_gallery_url' value='$edit_gallery_url'/>
            $html_code = "
            <div>Aucune photo dans la bibliothèque. <a href='$edit_gallery_url'>Créez une galerie</a> et ajoutez des photos.<div>";
            return $html_code;    
        }
        error_log("Glp_User_Photos_Public::pg_show_page count image =". count($medias));
        $admin_ajax_url = admin_url('admin-ajax.php');
        //$admin_post_url = admin_url('admin-post.php');
        $nonce = wp_create_nonce('user_photos');
        $edit_photo_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_PHOTO); // TODO move 186 to a global constant or get by Title


        $html_code = "
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <input type='hidden' id='pg_edit_photo_url' value='$edit_photo_url'/>
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div id='delete-photo-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        Photo supprimée !
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
            $url_img = wp_get_attachment_image_src($item->ID, "thumbnail");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }

            $statext = Pg_Edit_Gallery_Public::get_photo_status($item->ID);
            $metadate = get_post_meta($item->ID, 'date', true);
            $date = Pg_Edit_Gallery_Public::get_photo_date($metadate);
            

            //error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="flex-container">
                <div class="miniature" style="background-image: url('.$img_src.')"></div>
                <div class="photo-text-container";>
                    <div class="photo-title">'.$item->post_title.'</div>
                    <div class="photo-text-user">'.$item->post_content.'</div>
                    <div class="footer-edit-gallery">
                        <div>Date : '.$date.'</div>
                        <div>'.$statext.'</div>
                    </div>
                </div>
                <div class="options" style="background-color: lightblue">
                    <div class="flex-options">
                        <i class="user-photo-option pointer-icon fas fa-edit" aria-hidden="true" data-postid="'.$item->ID.'"></i>
                        <i class="user-photo-option pointer-icon fas fa-trash" aria-hidden="true" data-postid="'.$item->ID.'"></i>
                    </div>
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

    public function pg_get_medias_by_user( $user_id ) {
        //error_log("pg_get_medias_by_user: $user_id");
        $args = array(
            'author'         => $user_id,
            'post_type'      => 'attachment',
            'post_status'    => 'inherit,private', // Adjust post status as needed
            'posts_per_page' => -1, // Retrieve all attachments
        );
        
        $query = new WP_Query( $args );
        $medias = $query->get_posts();

        /*error_log("pg_get_medias_by_user: ".print_r($medias, true));
        Example for one post:
        (
            [ID] => 5
            [post_author] => 1
            [post_date] => 2023-11-08 08:11:38
            [post_date_gmt] => 2023-11-08 08:11:38
            [post_content] => desc earth
            [post_title] => title earth
            [post_excerpt] => caption earth
            [post_status] => inherit
            [comment_status] => open
            [ping_status] => closed
            [post_password] => 
            [post_name] => earth
            [to_ping] => 
            [pinged] => 
            [post_modified] => 2023-12-06 15:01:45
            [post_modified_gmt] => 2023-12-06 15:01:45
            [post_content_filtered] => 
            [post_parent] => 44
            [guid] => http://localhost:8000/wp-content/uploads/2023/11/earth.gif
            [menu_order] => 0
            [post_type] => attachment
            [post_mime_type] => image/gif
            [comment_count] => 0
            [filter] => raw
        )*/

        return $medias;
    }

    // callback on request to delete a photo
    public function user_delete_photo() {
        error_log("user_delete_photo IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'user_photos' ) ) {
            error_log("user_delete_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("user_delete_photo No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['pid'] )){
            error_log("user_delete_photo no pid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $pid = sanitize_text_field($_REQUEST['pid']);

        wp_delete_attachment( $pid, true );
        wp_delete_post( $pid, true);

        // delete also in Geoposts table
        Pg_Geoposts_Table::delete_post($pid);

        error_log( "user_delete_photo Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }    
}
