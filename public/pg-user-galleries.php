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
class Glp_User_Galleries_Public {

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
        add_shortcode( 'pg_user_galleries', array($this, 'pg_generate_page') );
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
        wp_enqueue_style( $this->plugin_name.'-pg-public.css', plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );    

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
    }

    public function enqueue_styles_early(){
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        // error_log("Glp_User_Galleries_Public::pg_generate_page IN ");

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page();

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    static public function get_page_url_from_slug($slug) {
        $locale = get_locale(); // ex en_US
        $lang = substr($locale, 0, 2); // ex en
        $slug = $slug . "-" . $lang;
        $page = get_page_by_path($slug);
        if ($page == null) {
            error_log("get_page_url_from_slug error page not found for slug=".$slug);
            return false;
        }
        // error_log("get_page_url_from_slug slug=".$slug);
        // error_log("get_page_url_from_slug page->ID=".$page->ID);
        $result = get_permalink($page->ID);
        //error_log("get_page_url_from_slug permalink=".$result);
        if ($result == false) {
            error_log("get_page_url_from_slug: ERROR not found");
        }
        return $result;
    }
    
    // attr should have the user id
    public function pg_show_page(){
        
        $user_id = get_current_user_id();
        // error_log("pg_show_page IN user_id: ".$user_id);
        if ($user_id == 0) {
            return "";
        }
        $page = get_page_by_path("edit-photo");
        //error_log("pg_show_page page from slug: ".print_r($page, true));
        $edit_gallery_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_GALLERY); // TODO move 186 to a global constant or get by Title

        $html_code = "";
        $galleries = $this->pg_get_galleries_by_user_id($user_id);
        if(empty($galleries)){
            // TODO display No galleries, create your first gallery
            // TODO add a link the the gallery creationAdd gal
            $edit_gallery_url .= "?gid=-1";
            //<input type='hidden' id='pg_edit_gallery_url' value='$edit_gallery_url'/>
            $html_code = "
            <div class='pg-container'>
                <h3>".esc_html__("Mes galeries", $this->plugin_name)."</h3>
                <br/>
                <div>".esc_html__("Aucune galerie.", $this->plugin_name)."&nbsp<a href='$edit_gallery_url'>".esc_html__("Créez votre première galerie", $this->plugin_name)."</a> ".esc_html__("et ajoutez des photos.", $this->plugin_name)."<div>
            </div>";
            return $html_code;    
        }

        //$edit_gallery_url = substr($edit_gallery_url, 0, -1);
        $show_gallery_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_SHOW_GALLERY); 
        //$show_gallery_url = substr($show_gallery_url, 0, -1);
        $nonce = wp_create_nonce('user_galleries');
        $admin_ajax_url = admin_url('admin-ajax.php');
        $hide_help = get_user_meta( $user_id, 'hide_galleries_help', true); 

        $html_code .= "
        <input type='hidden' id='pg_edit_gallery_url' value='$edit_gallery_url'/>
        <input type='hidden' id='pg_show_gallery_url' value='$show_gallery_url'/>
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <div class='pg-container' id='user-item-list'>
            <h3>".esc_html__("Mes galeries", $this->plugin_name)."</h3>";
        if ($hide_help != 'true') {
            $html_code .= "
            <div class='alert alert-info' role='alert'>
                <div>".esc_html__("Vos galeries sont privées.", $this->plugin_name)."</div>
                <div> - ".esc_html__("Utilisez", $this->plugin_name)." &nbsp<i class='fas fa-edit'></i>&nbsp ".esc_html__("pour modifier une galerie.", $this->plugin_name)."</div>
                <div> - ".esc_html__("", $this->plugin_name)." &nbsp<i class='fas fa-eye'></i>&nbsp ".esc_html__("pour visualiser une galerie quand elle est partagée.", $this->plugin_name)."</div>
                <div> - ".esc_html__("", $this->plugin_name)." &nbsp<i class='fas fa-share-alt'></i>&nbsp ".esc_html__("pour copier le lien d'une galerie en vue de la partager.", $this->plugin_name)."</div>
                </br>
                <div class='form-check form-switch'>
                    <input  id='galleries_help' class='form-check-input' type='checkbox' role='switch'>
                    <label class='form-check-label' for='galleries_help'>".esc_html__("Ne plus afficher", $this->plugin_name)."</label>
                </div>
            </div>
            <br/>";
        }
        $html_code .= "
            <div class='tab-pane fade show active' id='nav-photos' role='tabpanel' aria-labelledby='nav-photos-tab'>
                <button type='button' class='btn btn-primary' id='user-galleries-create' style='margin-bottom: 10px;'>
                    ".esc_html__("Ajouter une galerie...", $this->plugin_name)."
                </button>
                <br/>
            </div>";

        $html_code .= $this->render_galleries($galleries);
        $html_code .= 
        "</div>";
        //$html_code .= $this->pg_create_modal_for_delete_confirmation();
        $html_code .= 
        '<div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="copy-to-clipboard" class="toast align-items-center text-white bg-success bg-gradient border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        '.esc_html__("Adresse copiée dans le presse-papier !", $this->plugin_name).'
                    </div>
                </div>
            </div>
        </div>';
        return $html_code;
    } 

    function render_galleries($galleries){
        $html='';

        // loop for each media
        foreach($galleries as $item){
            //error_log("render_galleries item:".print_r($item, true));
            $title = stripslashes($item["title"]);
            $desc = stripslashes($item["description"]);
            
            // get the first image og the gallery
            $image_ids = $this->pg_get_images_by_id($item["id"]);
            //error_log("render_galleries item:".print_r($image_ids, true));
            $img_src1 = "";
            $img_src2 = "";
            $img_src3 = "";
            $count_image = 0;
            foreach( $image_ids as $id) {
                $url_img = wp_get_attachment_image_src($id, "thumbnail");
                //error_log("render_galleries id=".$id." url=".print_r($url_img, true));
                if ($url_img != false) { // return false if no image
                    if ($count_image == 0) {
                        $img_src1 = $url_img[0];
                    }
                    if ($count_image == 1) {
                        $img_src2 = $url_img[0];
                    }
                    if ($count_image == 2) {
                        $img_src3 = $url_img[0];
                        break;
                    }
                    $count_image ++;
                }
            }

            $datetime = explode( " ", $item['date_update']);
            $date = $this->format_date($datetime[0]);

            //error_log("render_galleries url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="pdb-container" data-galid="'.$item["id"].'">
                <div class="miniature1" style="background-image: url('.$img_src1.')"></div>
                <div class="miniature2" style="background-image: url('.$img_src2.')"></div>
                <div class="miniature3" style="background-image: url('.$img_src3.')"></div>
                <div class="pdb-descr-container">
                    <div class="pdb-descr-header pdb-descr-font">'.$title.'</div>
                    <div class="pdb-descr-footer pdb-descr-font-small">'.$date.'</div>
                </div>
                <div class="options-photo-gallery" style="background-color: lightgreen">
                    <div class="user-gallery-option pointer-icon fas fa-edit" aria-hidden="true" data-galid="'.$item["id"].'"></div>
                    <div class="user-gallery-option pointer-icon fas fa-eye" aria-hidden="true" data-galuuid="'.$item["uuid"].'"></div>
                    <div class="user-gallery-option pointer-icon fas fa-share-alt" aria-hidden="true" data-galuuid="'.$item["uuid"].'"></div>
                </div>
            </div>';

        }
        return $html;
    }    

    function format_date($date_str) {
        
        $date = new DateTime($date_str);
        // Define an array of French month names
        $locale = get_locale();
        $formatter = new IntlDateFormatter( $locale,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE);

        $day = $formatter->format($date );

        return $day;
    }


    // Get the list of galleries for a given user_id
    // return empty array if none
    static public function pg_get_galleries_by_user_id( $user_id ){
        global $wpdb;

        // error_log("pg_get_galleries_by_user_id id: ".$user_id);

        $gallery_table = esc_sql($wpdb->prefix . "glp_gallery");

        $user_id = absint( sanitize_text_field( $user_id ));
        $sql = "SELECT * FROM ".$gallery_table." WHERE user_id=$user_id";
        //error_log("pg_get_galleries_by_user_id sql: ".$sql);
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        //error_log("pg_get_galleries_by_user_id result: ".print_r($results, true));
        if(count($results) > 0){
            return $results;
        }else{
            return array();
        }
    }

    // Get the list of galleries for a given user_id
    // return empty array if none
    static public function pg_is_current_user_gallery( $gal_id ){
        global $wpdb;

        $user_id = get_current_user_id();
        // error_log("pg_is_current_user_gallery id: ".$user_id);

        $gallery_table = esc_sql($wpdb->prefix . "glp_gallery");

        $sql = "SELECT id FROM ".$gallery_table." WHERE id=$gal_id AND user_id=$user_id";
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        // error_log("pg_is_current_user_gallery result: ".print_r($results, true));
        if(count($results) == 1){
            return true;
        }
        
        return false;
        
    }


    // Get the first image of the gallery
    // Return the image id or null if none
    static public function pg_get_images_by_id( $gallery_id ) {
        global $wpdb;
        //error_log("pg_get_first_image_by_id id: ".$gallery_id);

        $gallery_table = esc_sql($wpdb->prefix . "glp_gallery");

        $sql = "SELECT images_ids FROM {$gallery_table} WHERE id={$gallery_id}";
        //error_log("pg_get_first_image_by_id sql: ".$sql);
        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        //error_log("pg_get_first_image_by_id all ids: ".print_r($result, true));
        $image_ids = explode( "***", $result["images_ids"]);
        return $image_ids;
        // if (count($image_ids) > 0) {
        //     return $image_ids[0];
        // }

        // return null;
    }
    
    // callback on Ajax request
    public function hide_galleries_help() {
        // error_log("hide_galleries_help IN");
        // error_log("hide_galleries_help REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("hide_galleries_help No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'user_galleries' ) ) {
            error_log("hide_galleries_help nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        if( isset( $_REQUEST['hide'] ) and $_REQUEST['hide'] == 'true') {
            add_user_meta( $user_id, 'hide_galleries_help', 'true', false);
        }
        else {
            delete_user_meta( $user_id, 'hide_galleries_help');
        }

        // error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }    
}
