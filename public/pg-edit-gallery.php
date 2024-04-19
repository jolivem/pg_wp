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
class Pg_Edit_Gallery_Public {

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


    const PAGE_ID_EDIT_PHOTO = 33;
    //const PAGE_ID_EDIT_PHOTO = 186;
    const PAGE_ID_USER_GALLERIES = 20;

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
        add_shortcode( 'pg_edit_gallery', array($this, 'pg_generate_page') );
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
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-glp-public.js', plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        //wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);

        wp_enqueue_script( $this->plugin_name.'-pg-vignette.js', plugin_dir_url( __FILE__ ) . 'js/pg-vignette.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name.'-pg-vignette.js', 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();
        error_log("pg_generate_page IN ".print_r($_GET, true));

        // TODO check that the photo belons to the current user

        //Test with ID=67
        if (! isset($_GET['gid'])) {
            // TODO return 404
            return "";
        }

        //use the post ID provided in the URL
        $id=$_GET['gid']; 

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $id, $_GET['page_id'] );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    
    // function pg_add_nav_bar() {
    //     $code = '
    //     <!-- Tab Nav -->
    //     <nav>
    //         <div class="nav nav-tabs" id="nav-tab" role="tablist">
    //             <button class="nav-link active" id="nav-photos-tab" data-bs-toggle="tab" data-bs-target="#nav-photos" type="button" role="tab" aria-controls="nav-photos" aria-selected="true">Photos</button>
    //             <button class="nav-link" id="nav-desc-tab" data-bs-toggle="tab" data-bs-target="#nav-desc" type="button" role="tab" aria-controls="nav-desc" aria-selected="false">Title</button>
    //             <button class="nav-link" id="nav-config-tab" data-bs-toggle="tab" data-bs-target="#nav-config" type="button" role="tab" aria-controls="nav-config" aria-selected="false">Adanced</button>
    //         </div>
    //     </nav>
    //     <!-- End of Tab Nav -->';
    //     return $code;
    // }
    
    // attr should have the user id
    public function pg_show_page( $id, $page_id ){

        error_log("pg_show_page IN id=".$id);
        
        if ($id == -1) {
            // create a new gallery
            $id = $this->create_gallery();
            error_log("pg_show_page after id=".$id);
            if ( $id == 0 ) {
                // internal error
                // TODO 404
                return;
            }

            //$gallery = $this->pg_get_gallery_by_id($id);

            $url = esc_url_raw( add_query_arg( array(
                "page_id"           => $page_id,
                "gid"               => $id) ) );
            wp_redirect( $url );

            // $title = "";
            // $description = "";
            // $html_images = "";

        }
        else {
            // get an existing gallery
            $gallery = $this->pg_get_gallery_by_id($id);
            if(!$gallery){
                error_log("pg_show_page Gallery not found");
                return "";
            }
            $title = $gallery["title"];
            $description = $gallery["description"];
            $medias = $this->pg_get_medias_by_gallery($id);
            if ($medias != null) {
                $html_images = $this->render_images($medias);
            }
        }
        $user_galleries_url = get_permalink(self::PAGE_ID_USER_GALLERIES);
        $edit_photo_url = get_permalink(self::PAGE_ID_EDIT_PHOTO); // TODO move 186 to a global constant or get by Title

        $admin_ajax_url = admin_url('admin-ajax.php');
        //$admin_post_url = admin_url('admin-post.php');
        $nonce = wp_create_nonce('edit_gallery');
        error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);

        //error_log("render_images url:".print_r($url_img, true));
        // TODO check url_img is OK, add try catch
        //$navbar = $this->pg_add_nav_bar();
        $html_code = "
        <div class='toast-container position-absolute bottom-0 end-0 p-3'>
            <div id='save-gallery-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        Enregistré !
                    </div>
                </div>
            </div>
        </div>
        <div class='container'>
            <input type='hidden' id='gallery-id' name='gallery-id' value='$id'/>
            <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
            <input type='hidden' id='pg_edit_photo_url' value='$edit_photo_url'/>
            <input type='hidden' id='pg_user_galleries_url' value='$user_galleries_url'/>
            <input type='hidden' id='pg_nonce' value='$nonce'/>
            <div class='tab-content'>
                <div>
                    <div class='form-floating mb-3'>
                        <input type='text' name='title' class='form-control' id='gallery-title' aria-describedby='titleHelp' placeholder='' value='$title'>
                        <label for='gallery-title'>Titre</label>
                    </div>
                    <div class='form-floating mb-3'>
                        <textarea rows='4' name='desc' style='height:100%;' class='form-control' placeholder='' id='gallery-description'>$description</textarea>
                        <label for='gallery-description'>Description</label>                        
                    </div>
                </div>
                <div>
                    <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#multipleDowloadModal'>
                        Add photos...
                    </button>
                    <button type='button' class='btn btn-primary align-right' id='edit-gallery-save'>Enregistrer</button>
            </div>
                <div id='gallery-item-list'>$html_images</div>
            </div>
            <br/>
            <div>
                <button type='button' class='btn btn-primary align-left' data-bs-toggle='modal' data-bs-target='#delete-confirmation'>
                    Supprimer la gallerie
                </button>";
        if ($html_images != "") {
            $html_code .= "
                <button type='button' class='btn btn-primary align-right' id='edit-gallery-save'>Enregistrer</button>";
        }
        $html_code .= "
            </div>
        </div>";

        $html_code .= $this->get_html_for_delete_confirmation();

        return $html_code;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    // $id = gallery id
    // return an array with image IDs
    public function pg_get_medias_by_gallery( $id ) {
        error_log("pg_get_medias_by_gallery IN gallery_id=".$id);
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";
        $result = $wpdb->get_row( $sql, "ARRAY_A" );
        if ( is_null( $result ) || empty( $result ) ) {
            error_log("pg_get_medias_by_gallery OUT null");
            return null;
        }
        if ( is_null( $result["images_ids"] ) || empty( $result["images_ids"] ) ) {
            error_log("pg_get_medias_by_gallery images_id empty");
            return null;
        }
        $image_ids = explode( "***", $result["images_ids"]);
        return $image_ids;
    }

    // render all the images 
    function render_images($medias){
        //error_log("render_images IN images=".print_r($medias, true));
        $html='<div class="sortable-list" id="item-list">';

        // loop for each media
        foreach($medias as $id){
            //error_log("render_images id:".$id);
            //$img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($id, "thumbnail");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }

            $post = get_post($id);
            //error_log("render_images content=".print_r($post, true));
            $content = $post->post_content;
            $title = $post->post_title;

            $meta = get_post_meta($id);
            //error_log("render_images meta=".print_r($meta, true));
            error_log("render_images type=".gettype($meta['status'][0]));
            if ($meta['worldmap'][0] != Pg_Edit_Photo_Public::WORLDMAP_ON){
                $statext = "Private";
            }
            else {
                //TODO get if its shared or not
                if ($meta['status'][0] == Pg_Edit_Photo_Public::STATUS_PUBLIC_OK) {
                    $statext = "Shared";
                }
                else {
                    $statext = "Shared not validated";
                }
            }

            //TODO get title and text
            //error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<li class="item" draggable="true" data-id="'.$id.'">
                <div class="flex-container" style="margin-top:0px">
                    <div class="miniature" style="background-image: url('.$img_src.')"></div>
                    <div class="photo-text-container" style="background-color: lightyellow";>
                        <div class="photo-title">'.$title.'</div>
                        <div class="photo-text-gallery">'.$content.'</div>
                        <div class="footer-edit-gallery">
                            <div>Date : '.$post->post_date.'</div>
                            <div>'.$statext.'</div>
                        </div>
                    </div>
                    <div class="options-photo-gallery" data-id="'.$id.'" style="background-color: lightgreen">
                        <div class="gallery-photo-option pointer-icon fas fa-edit" aria-hidden="true"></div>
                        <div class="gallery-photo-option pointer-icon fas fa-trash" aria-hidden="true"></div>
                    </div>
                </div>
            </li>';
        }
        $html.='</div>';
        // TODO make it work on mobiles
        return $html;
    }

    //////////////////////////////////////
    // Received and process POST request
    //////////////////////////////////////

    // callback on request to submit gallery settings
    public function user_edit_gallery() {
        error_log("user_edit_gallery IN");
        //error_log("user_edit_gallery REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_gallery' ) ) {
            error_log("user_edit_gallery nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("user_edit_gallery No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        $gallery_id = sanitize_text_field( $_REQUEST['gallery_id'] );
        $title = sanitize_text_field( $_REQUEST['title'] );
        $desc = sanitize_text_field( $_REQUEST['desc'] );

        $this->update_gallery($_REQUEST);

        error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
    }

  
    // callback on request to delete a gallery
    public function user_delete_gallery() {
        error_log("user_delete_gallery IN");
        error_log("user_delete_gallery REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        $user_id = get_current_user_id();
        if ($user_id == 0) {
            error_log("user_delete_gallery No USER");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_gallery' ) ) {
            error_log("user_delete_gallery nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        if( ! isset( $_REQUEST['gid'] )){
            error_log("user_delete_gallery no gid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        // find the gallery
        $gallery = Glp_Galleries_List_Table::get_gallery_by_id($_REQUEST['gid']);
        if (!$gallery) {
            error_log("user_delete_gallery gallery noty found");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        Glp_Galleries_List_Table::delete_gallery($_REQUEST['gid']);

        error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }    


    // callback on request to submit gallery settings
    // public function user_remove_photo() {
    //     error_log("user_remove_photo IN");
    //     //error_log("user_remove_photo REQUEST ".print_r($_REQUEST, true));
    //     //error_log("download_single_photo FILES ".print_r($_FILES, true));

    //     if( ! isset( $_REQUEST['nonce'] ) or 
    //         ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_gallery' ) ) {
    //         error_log("user_remove_photo nonce not found");
    //         wp_send_json_error( "NOK.", 403 );
    //     }

    //     $gallery_id = sanitize_text_field( $_REQUEST['gid'] );
    //     $post_id = sanitize_text_field( $_REQUEST['postid'] );


    //     $this->remove_post_from_gallery($post_id, $gallery_id);

    //     error_log( "Respond success");
    //     wp_send_json_success( null, 200);
    //     wp_die();
    // }

    function pg_get_gallery_by_id( $id ) {
        global $wpdb;
        error_log( "pg_get_gallery_by_id IN id=".$id);

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE id={$id}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }

    private function get_default_options(){
       
        $options = array(
            'columns_count'         => '3',
            'view_type'             => 'masonry',
            "border_radius"         => "0",
            "admin_pagination"      => "all",
            "hover_zoom"            => "no",
            "vignette_display"      => "hover",
            "show_gal_title"        => "off",
            //"show_gal_desc"         => "off",
            "images_hover_effect"   => "",
            "hover_dir_aware"       => "slide",
            "images_border"         => "",
            "images_border_width"   => "1",
            "images_border_style"   => "solid",
            "images_border_color"   => "#000000",
            "hover_effect"          => "fadeIn",
            "hover_opacity"         => "0.5",
            "image_sizes"           => "full_size",
            "lightbox_color"        => "rgba(0,0,0,0)",
            "images_orderby"        => "noordering",
            "hover_icon"            => "search_plus",
            "show_title"            => "on",
            "show_title_on"         => "image_hover",
            "title_position"        => "bottom",
            "show_with_date"        => "",
            "images_distance"       => "5",
            "images_loading"        => "load_all",
            "images_request"        => "selection",
            "gallery_loader"        => "",
            "hover_icon_size"       => "20",
            "thumbnail_title_size"  => "12",
            "thumb_height_mobile"   => "170",
            "thumb_height_desktop"  => "260",
            "enable_light_box"      => "off",
            "ays_filter_cat"        => "off",
            "filter_thubnail_opt"   => "none",
            "ordering_asc_desc"     => "ascending",
            "custom_class"          => "",
            //"link_on_whole_img"     => "off",
            "create_date"           => current_time( 'mysql' ),
            "author"                => '',
            'gpg_create_author'     => '',
            "mosaic_row_size"       => "500",
        );     
        return $options;
    }

    private function get_default_lightbox_options(){

        //lightbox option not displayed, can be removed ??
        $options = array(
            "lightbox_counter"      => "true",
            "lightbox_autoplay"     => "true",
            "lb_pause"              => "5000",
            "lb_show_caption"       => "true",
            "filter_lightbox_opt"   => "none",
        );

        return $options;
    }

    public function create_gallery(){
        error_log( "create_gallery IN");
        global $wpdb;
        $gallery_table = $wpdb->prefix . "glp_gallery";
    
        $user_id = get_current_user_id();
        $g_options=$this->get_default_options();
        $g_l_options=$this->get_default_lightbox_options();

        $date_now = date('Y-m-d H:i:s');
 
        $gallery_result = $wpdb->insert(
            $gallery_table,
            array(
                "title"             => '',
                "description"       => '',
                "categories_id"     => '',
                "width"             => 0,
                "height"            => 0,
                "options"           => json_encode($g_options,true),
                "lightbox_options"  => json_encode($g_l_options,true),
                "custom_css"        => '',
                "user_id"           => $user_id,
                "date_creation"     => $date_now,
                "date_update"       => $date_now,
                "images_ids"        => ''
            ),
            array( "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s" )
        );

        error_log( "create_gallery, id = ".$wpdb->insert_id);
        return $wpdb->insert_id;
    }
    
    
    public function update_gallery($data){
        global $wpdb;
        $gallery_table = $wpdb->prefix . "glp_gallery";

        // List of images
        if (isset($data["images_id"]) && !empty($data["images_id"])) {
            error_log("update_gallery() images selected ! ");
            $images_ids = str_replace(",", "***", $data["images_id"]);
            error_log("update_gallery() images=".$image_ids);
        }
        else {
            error_log("update_gallery() NO images ! ");
            //$image_paths            = '';
            $images_ids = '';
        }
        error_log("image_ids ".$images_ids);
        $id = ( $data["gallery_id"] != NULL ) ? absint( intval( $data["gallery_id"] ) ) : null;
        $title = (isset($data["title"]) && $data["title"] != '') ? stripslashes(sanitize_text_field( $data["title"] )) : '';
        $description = !isset($data['desc']) ? '' : wp_kses_post( $data['desc'] );

        // TODO get current author
    
        $user_id = get_current_user_id();
        $submit_type = (isset($data['submit_type'])) ?  $data['submit_type'] : '';

        $g_options=$this->get_default_options();
        $g_l_options=$this->get_default_lightbox_options();

        $gallery_result = $wpdb->update(
            $gallery_table,
            array(
                "title"             => $title,
                "description"       => $description,
                "categories_id"     => '',
                "width"             => 0,
                "height"            => 0,
                "options"           => json_encode($g_options,true),
                "lightbox_options"  => json_encode($g_l_options,true),
                "custom_css"        => '',
                "user_id"           => $user_id,
                "images_ids"        => $images_ids
            ),
            array( "id" => $id ),
            array( "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s" ),
            array( "%d" )
        );

        $glp_tab = isset($data['glp_settings_tab']) ? $data['glp_settings_tab'] : 'tab1';
        return $gallery_result;
    }

    function get_html_for_delete_confirmation() {
        $html_code = '
        <div class="modal fade" id="delete-confirmation" tabindex="-1" aria-labelledby="delete-confirmation-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="container">
                        <div class="modal-header">
                            <h5 class="modal-title" id="delete-confirmation-label">Suppression de la galerie</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>                            
                        <div class="modal-body">
                            <p>La galerie va être supprimée définitivement.</p>
                            <p>Note : Les photos de la galerie sont conservées et accessibles dans le menu TODO<p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="close-modal" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="modal-delete-gallery" class="btn btn-primary">Confirmer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';   
        
        return $html_code;
    }

    // public function remove_post_from_gallery($post_id, $gallery_id){
    //     global $wpdb;
    //     $gallery_table = $wpdb->prefix . "glp_gallery";

    //     $gallery = $this->pg_get_gallery_by_id($gallery_id);
    //     if ($gallery) {

    //         $image_ids = explode( "***", $result["images_ids"]);

    //         // search post_id and delete it
    //         if(($key = array_search($post_id, $image_ids)) !== false) {
    //             unset($image_ids[$key]);

    //             // update table
    //             $gallery_table = $wpdb->prefix . "glp_gallery";

    //             $images = sanitize_text_field( implode( "***", array_filter($image_ids)) );
    //             //error_log("remove_post_from_gallery");
    //             $gallery_result = $wpdb->update(
    //                 $gallery_table,
    //                 array("images_ids" => $images),
    //                 array( "id" => $gallery_id ),
    //                 array( "%s" ),
    //                 array( "%d" )
    //             );
    //             error_log("remove_post_from_gallery OUT");                
            
    //         }
    //     }

    // }

}
