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
        wp_enqueue_script( $this->plugin_name.'-glp-public.js', plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        //wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);

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

        // TODO check that the photo belons to the current user

        //Test with ID=67
        $attr['id']=2;

        $this->enqueue_styles();
        $this->enqueue_scripts();
/*
        global $wpdb;
        if (!isset($_COOKIE['glp_page_tab_free'])) {
            setcookie('glp_page_tab_free', 'tab_0', time() + 3600);
        }
        if(isset($_GET['glp_settings_tab'])){
            $glp_tab = sanitize_key( $_GET['glp_settings_tab'] );
        }else{
            $glp_tab = 'tab1';
        }
        $action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';
        $heading = '';
        $id = ( isset( $_GET['gallery'] ) ) ? absint( sanitize_text_field( $_GET['gallery'] ) ) : null;
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $author = array(
            'id' => $user->ID,
            'name' => $user->data->display_name
        );
        $get_all_galleries = Glp_Gallery_Data::get_galleries();
        $g_options = array(
            'columns_count'         => '3',
            'view_type'             => 'grid',
            "border_radius"         => "0",
            "admin_pagination"      => "all",
            "hover_zoom"            => "no",
            "vignette_display"      => "permanent",
            "show_gal_title"        => "off",
            //"show_gal_desc"         => "off",
            "images_hover_effect"   => "simple",
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
            "show_title"            => "",
            "show_title_on"         => "gallery_image",
            "title_position"        => "bottom",
            "show_with_date"        => "",
            "images_distance"       => "5",
            "images_loading"        => "load_all",
            "images_request"        => "selection",
            "gallery_loader"        => "flower",
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
            "author"                => $author,
            'gpg_create_author'     => $user_id,
            "mosaic_row_size"       => "500",
        );
        $g_l_options = array(
            "lightbox_counter"      => "true",
            "lightbox_autoplay"     => "true",
            "lb_pause"              => "5000",
            "lb_show_caption"       => "true",
            "filter_lightbox_opt"   => "none",
        );
        $gallery = array(
            "id"                => "",
            "title"             => "Demo title",
            // "description"       => "Demo description",
            "images"            => "",
            "images_titles"     => "",
            "images_descs"      => "",
            "images_alts"       => "",
            "images_urls"       => "",
            "categories_id"     => "",
            "width"             => "",
            "height"            => "1000",
            "options"           => json_encode($g_options,true),
            "lightbox_options"  => json_encode($g_l_options,true),
            "custom_css"        => "",
            "images_dates"      => "",
            "images_ids"        => "",
        );
        switch( $action ) {
            case 'add':
                $heading = __('Add new gallery', $this->plugin_name);
                break;
            case 'edit':
                $heading = __('Edit gallery', $this->plugin_name);
                $gallery = $this->gallery_obj->get_gallery_by_id($id);
                error_log("glp_gallery-actions gallery, read: ".print_r($gallery, true));
                break;
        }
        
        $gallery_message_vars = array(  
            '%%user_first_name%%'       => __("User's First Name", $this->plugin_name),
            '%%user_last_name%%'        => __("User's Last Name", $this->plugin_name),
            '%%user_display_name%%'     => __("User's Display Name", $this->plugin_name),
            '%%user_nickname%%'         => __("User's Nick Name", $this->plugin_name),
            "%%user_wordpress_email%%"  => __("User's WordPress profile email", $this->plugin_name),
            '%%user_wordpress_roles%%'  => __("User's Wordpress Roles", $this->plugin_name),
            '%%user_ip_address%%'       => __("User's IP address", $this->plugin_name),
            '%%user_id%%'               => __("User's ID", $this->plugin_name),
            '%%gallery_id%%'            => __("Gallery ID", $this->plugin_name),
        );
        
        //$gallery_message_vars_html = $this->ays_gallery_generate_message_vars_html( $gallery_message_vars );
        
        if(isset($_POST["ays-submit"]) || isset($_POST["ays-submit-top"])){
            $_POST["id"] = $id;
            $this->gallery_obj->add_or_edit_gallery($_POST);
        }
        if(isset($_POST["ays-apply"]) || isset($_POST["ays-apply-top"])){
            $_POST["id"] = $id;
            $_POST["submit_type"] = 'apply';
            $this->gallery_obj->add_or_edit_gallery($_POST);
        }
        
        $this_site_path = trim(get_site_url(), "https:");
        $gal_options            = json_decode($gallery['options'], true);
        $gal_lightbox_options   = json_decode($gallery['lightbox_options'], true);
        
        $show_gal_title = (!isset($gal_options['show_gal_title'])) ? 'off' : $gal_options['show_gal_title'];
        //$show_gal_desc = (!isset($gal_options['show_gal_desc'])) ? 'on' : $gal_options['show_gal_desc'];
        
        $admin_pagination = (!isset($gal_options['admin_pagination']) ||
                             $gal_options['admin_pagination'] == null ||
                             $gal_options['admin_pagination'] == '') ? "all" : $gal_options['admin_pagination'];
        $ays_hover_zoom = (!isset($gal_options['hover_zoom']) ||
                           $gal_options['hover_zoom'] == null ||
                           $gal_options['hover_zoom'] == '') ? "no" : $gal_options['hover_zoom'];
        
        $ays_vignette_display = (!isset($gal_options['vignette_display']) ||
                           $gal_options['vignette_display'] == null ||
                           $gal_options['vignette_display'] == '') ? "permanent" : $gal_options['vignette_display'];
        
        //Hover zoom animation Speed
        $hover_zoom_animation_speed = (isset($gal_options['hover_zoom_animation_speed']) && $gal_options['hover_zoom_animation_speed'] !== '') ? abs($gal_options['hover_zoom_animation_speed']) : 0.5;
        
        //Hover animation Speed
        $hover_animation_speed = (isset($gal_options['hover_animation_speed']) && $gal_options['hover_animation_speed'] !== '') ? abs($gal_options['hover_animation_speed']) : 0.5;
        
        //Hover scale animation Speed
        $hover_scale_animation_speed = (isset($gal_options['hover_scale_animation_speed']) && $gal_options['hover_scale_animation_speed'] !== '') ? abs($gal_options['hover_scale_animation_speed']) : 1;
        
        $ays_hover_scale = (!isset($gal_options['hover_scale']) ||
                           $gal_options['hover_scale'] == null ||
                           $gal_options['hover_scale'] == '') ? "no" : $gal_options['hover_scale'];
        $show_thumb_title_on = (!isset($gal_options['show_title_on']) || 
                               $gal_options['show_title_on'] == false ||
                               $gal_options['show_title_on'] == "") ? "gallery_image" : $gal_options['show_title_on'];
        $thumb_title_position = (!isset($gal_options['title_position']) || 
                               $gal_options['title_position'] == false ||
                               $gal_options['title_position'] == "") ? "bottom" : $gal_options['title_position'];
        $ays_images_hover_effect = (!isset($gal_options['images_hover_effect']) || 
                                    $gal_options['images_hover_effect'] == '' ||
                                    $gal_options['images_hover_effect'] == null) ? 'simple' : $gal_options['images_hover_effect'];
        $ays_images_hover_dir_aware = (!isset($gal_options['hover_dir_aware']) ||
                                      $gal_options['hover_dir_aware'] == null ||
                                      $gal_options['hover_dir_aware'] == "") ? "slide" : $gal_options['hover_dir_aware'];
        $ays_images_border = (!isset($gal_options['images_border'])) ? '' : $gal_options['images_border'];
        $ays_images_border_width    = (!isset($gal_options['images_border_width'])) ? '1' : $gal_options['images_border_width'];
        $ays_images_border_style    = (!isset($gal_options['images_border_style'])) ? 'solid' : $gal_options['images_border_style'];
        $ays_images_border_color    = (!isset($gal_options['images_border_color'])) ? '#000000' : esc_attr(stripslashes( $gal_options['images_border_color'] ));
        $ays_gallery_loader  = (!isset($gal_options['gallery_loader'])) ? "flower" : $gal_options['gallery_loader'];
        
        if ($ays_gallery_loader == 'default') {
            $ays_gallery_loader = "flower";
        }
        
        // Gallery loader text value
        $gallery_loader_text_value = (isset($gal_options['gallery_loader_text_value']) && $gal_options['gallery_loader_text_value'] != '') ? stripslashes(esc_attr($gal_options['gallery_loader_text_value'])) : '';
        
        // Gallery loader custom gif value
        $gallery_loader_custom_gif = (isset($gal_options['gallery_loader_custom_gif']) && $gal_options['gallery_loader_custom_gif'] != '') ? stripslashes(esc_url($gal_options['gallery_loader_custom_gif'])) : '';
        
        //  Gallery loader custom gif width
        $gallery_loader_custom_gif_width = (isset($gal_options['gallery_loader_custom_gif_width']) && $gal_options['gallery_loader_custom_gif_width'] != '') ? absint( intval( $gal_options['gallery_loader_custom_gif_width'] ) ) : 100;
        
        $glp_view_type = (!isset($gal_options['view_type']) || $gal_options['view_type'] == "") ? "grid" : $gal_options['view_type'];
        
        $glp_border_radius = !isset($gal_options['border_radius']) || $gal_options['border_radius'] == "" ? "0" : ($gal_options['border_radius']);
        $glp_hover_icon_size = !isset($gal_options['hover_icon_size']) ? "20" : ($gal_options['hover_icon_size']);
        $glp_thumbnail_title_size = !isset($gal_options['thumbnail_title_size']) ? "12" : ($gal_options['thumbnail_title_size']);
        $ays_thumb_height_mobile = !isset($gal_options['thumb_height_mobile']) ? "170" : ($gal_options['thumb_height_mobile']);
        $ays_thumb_height_desktop = !isset($gal_options['thumb_height_desktop']) ? "260" : ($gal_options['thumb_height_desktop']);
        
        $glp_lightbox_counter           = (!isset($gal_lightbox_options['lightbox_counter'])) ? "true" : $gal_lightbox_options['lightbox_counter'];
        $glp_lightbox_autoplay          = (!isset($gal_lightbox_options['lightbox_autoplay'])) ? "true" : $gal_lightbox_options['lightbox_autoplay'];
        $glp_lightbox_pause             = (!isset($gal_lightbox_options['lb_pause'])) ? "5000" : $gal_lightbox_options['lb_pause'];
        $glp_show_caption               = (!isset($gal_lightbox_options['lb_show_caption'])) ? "true" : $gal_lightbox_options['lb_show_caption'];
        
        $glp_lg_keypress = (!isset($gal_lightbox_options["lb_keypress"])) ? "true" : $gal_lightbox_options["lb_keypress"];
        $glp_lg_esckey = (!isset($gal_lightbox_options["lb_esckey"])) ? "true" : $gal_lightbox_options["lb_esckey"];
        
        // Gallery image position
        $gallery_img_position = (isset($gal_options['gallery_img_position']) && $gal_options['gallery_img_position'] != 'center-center') ? $gal_options['gallery_img_position'] : 'center-center';
        $gallery_img_position = (isset($gal_options['gallery_img_position_l']) && isset($gal_options['gallery_img_position_r'])) ? $gal_options['gallery_img_position_l'].'-'.$gal_options['gallery_img_position_r'] : $gallery_img_position;
        
        $image_no_photo = GLP_ADMIN_URL .'images/no-photo.png';
        
        //$gallery_categories = $this->ays_get_categories();
        
        //$img_load_effect = isset($gal_options['img_load_effect']) ? $gal_options['img_load_effect'] : 'fadeIn';
        
        $ordering_asc_desc = (isset($gal_options['ordering_asc_desc']) && $gal_options['ordering_asc_desc'] != '') ? $gal_options['ordering_asc_desc'] : 'ascending';
        $custom_class = (isset($gal_options['custom_class']) && $gal_options['custom_class'] != "") ? $gal_options['custom_class'] : '';
        $gpg_height_width_ratio = isset($gal_options['height_width_ratio']) ? $gal_options['height_width_ratio'] : '1';
        
        $responsive_width = (!isset($gal_options['resp_width'])) ? 'on' : $gal_options['resp_width'];
        
        $enable_rtl_direction = (isset($gal_options['enable_rtl_direction']) && $gal_options['enable_rtl_direction'] == 'on') ? $gal_options['enable_rtl_direction'] : 'off';
        
        $loading_type = (isset($gal_options['images_loading']) && $gal_options['images_loading'] != '') ? $gal_options['images_loading'] : "load_all"; 
        $image_request_type = (isset($gal_options['images_request']) && $gal_options['images_request'] != '') ? $gal_options['images_request'] : "selection"; 
        
        //$redirect_type = (isset($gal_options['redirect_url_tab']) && $gal_options['redirect_url_tab'] != '') ? $gal_options['redirect_url_tab'] : "_blank"; 
        
        //thumbnail title color
        $thumbnail_title_color = isset($gal_options['glp_title_color']) ? esc_attr(stripslashes($gal_options['glp_title_color'])) : '#ffffff';
        
        //Gallery title color
        $gallery_title_color = isset($gal_options['ays_gallery_title_color']) ? esc_attr(stripslashes($gal_options['ays_gallery_title_color'])) : '#000';
        
        //Gallery description color
        $gallery_desc_color = isset($gal_options['ays_gallery_desc_color']) ? esc_attr(stripslashes($gal_options['ays_gallery_desc_color'])) : '#000';
        
        //filter by cat anim
        $glp_filter_cat_anim = isset($gal_options['glp_filter_cat_anim']) ? sanitize_text_field($gal_options['glp_filter_cat_anim']) : 'fadeIn';
        
        $gpg_create_date = (isset($gal_options['create_date']) && $gal_options['create_date'] != '') ? $gal_options['create_date'] : "0000-00-00 00:00:00";
        
        if(isset($gal_options['author']) && $gal_options['author'] != 'null'){
            if ( ! is_array( $gal_options['author'] ) ) {
                $gal_options['author'] = json_decode($gal_options['author'], true);
                $gpg_author = $gal_options['author'];
            } else {
                $gpg_author = array_map( 'stripslashes', $gal_options['author'] );
            }
        } else {
            $gpg_author = array('name' => 'Unknown');
        }
        
        // Custom CSS
        $glp_custom_css = (isset($gallery['custom_css']) && $gallery['custom_css'] != '') ? stripslashes( esc_attr( $gallery['custom_css'] ) ) : '';
        
        // Images distance
        $images_distance = (isset($gal_options['images_distance']) && $gal_options['images_distance'] != '') ? absint( intval( $gal_options['images_distance'] ) ) : '5';
        
        // mosaic row size
        $mosaic_row_size = (isset($gal_options['mosaic_row_size']) && $gal_options['mosaic_row_size'] != '') ? absint( intval( $gal_options['mosaic_row_size'] ) ) : '500';
        error_log("from DB: mosaic_row_size=".$mosaic_row_size);
        
        $query_categories = isset($gal_options['query_categories']) ? $gal_options['query_categories'] : '';
        error_log( "CATEGORIES from options: ".$query_categories);
        
        $loader_iamge = "<span class='display_none glp_loader_box'><img src='". GLP_ADMIN_URL ."/images/loaders/loading.gif'></span>";
*/
        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){

        error_log("pg_show_page IN ".print_r($attr, true));
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('edit_gallery');
        error_log("pg_show_page single admin_ajax_url=".$admin_ajax_url);

        $medias = $this->pg_get_medias_by_gallery($id);

        if ($medias != null) {
            $html_images = $this->render_images($medias);
        }
  
        //error_log("render_images url:".print_r($url_img, true));
        // TODO check url_img is OK, add try catch
        $navbar = $this->pg_add_nav_bar();
        $html_code = '
        <div class="container">
            '.$navbar.'
            <!-- Tab Content -->
            <div>
                <div>
                    <div class="tab-content" id="tabcontent1">
                        <form>
                            <input type="hidden" id="gallery-id" value="'.$id.'"/>
            
                            <div class="tab-pane fade show active" id="tabs-text-1" role="tabpanel" aria-labelledby="tabs-text-1-tab">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    Select more photos...
                                </button>
                                <div id="gallery-item-list">'.$html_images.'</div>

                            </div>
                            <div class="tab-pane fade" id="tabs-text-2" role="tabpanel" aria-labelledby="tabs-text-2-tab">
                                <p>handsome</p>
                            </div>
                            <div class="tab-pane fade" id="tabs-text-3" role="tabpanel" aria-labelledby="tabs-text-3-tab">
                                <p>horrible</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End of Tab Content -->
        </div>';

        return $html_code;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    function pg_add_nav_bar() {
        $code = '
        <!-- Tab Nav -->
        <div>
            <ul class="nav nav-tabs" id="tabs-text" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tabs-text-1-tab" data-bs-toggle="tab" href="#tabs-text-1" role="tab" aria-controls="tabs-text-1" aria-selected="true">Photos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabs-text-2-tab" data-bs-toggle="tab" href="#tabs-text-2" role="tab" aria-controls="tabs-text-2" aria-selected="false">Title</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabs-text-3-tab" data-bs-toggle="tab" href="#tabs-text-3" role="tab" aria-controls="tabs-text-3" aria-selected="false">Advanced</a>
                </li>
            </ul>
        </div>
        <!-- End of Tab Nav -->';
        return $code;
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
        error_log("pg_get_medias_by_gallery images_id=".$result["images_ids"]);
        $image_ids = explode( "***", $result["images_ids"]);
        return $image_ids;
    }

    function render_images($medias){
        $html='';

        // loop for each media
        foreach($medias as $id){
            error_log("render_images id:".$id);
            //$img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($id, "thumbnail");

            if ($url_img != false) {
                $img_src = $url_img[0];
            }
            error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="flex-container">
                <div class="miniature" style="background-image: url('.$img_src.')"></div>
                <div class="photo-text-container" style="background-color: lightyellow";>
                    <div class="photo-title">Ceci est un très grand titre. Ceci est un très grand titre. Ceci est un très grand titre.</div>
                    <div class="photo-text">'.$id->title.'</div>
                    <div class="footer" style="background-color: lightblue";>coucou me voilà</div>
                </div>
                <div class="options" style="background-color: lightgreen">
                    <div class="flex-options">
                        <div class="item-option trash-icon fas fa-trash" aria-hidden="true"></div>
                        <div class="item-option trash-icon fas fa-trash" aria-hidden="true"></div>
                        <div class="item-option edit-icon fas fa-edit" aria-hidden="true"></div>
                    </div>
                </div>
            </div>';
            

        }
        return $html;
    }

    // callback on request to download photos
    public function user_edit_gallery() {
        error_log("user_edit_gallery IN");
        error_log("user_edit_gallery REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'edit_photo' ) ) {
            error_log("user_edit_gallery nonce not found");
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
            error_log("user_edit_gallery not a photo");
        }

        error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }
}
