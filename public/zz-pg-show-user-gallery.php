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
class Pg_Show_User_Gallery_Public {

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
        add_shortcode( 'pg_show_user_gallery', array($this, 'ays_generate_gallery') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), $this->version, 'all');
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        // TODO lightgallery est payant !!
        wp_enqueue_style( $this->plugin_name . "-lightgallery", plugin_dir_url( __FILE__ ) . 'css/lightgallery.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name . "-lg-transitions", plugin_dir_url( __FILE__ ) . 'css/lg-transitions.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'jquery.mosaic.min.css', plugin_dir_url( __FILE__ ) . 'css/jquery.mosaic.min.css?v=4', array(), $this->version, 'all' );
        wp_enqueue_style( 'masonry.pkgd.css', plugin_dir_url( __FILE__ ) . 'css/masonry.pkgd.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media();
        wp_enqueue_script( $this->plugin_name.'-imagesloaded.min.js', 'https://unpkg.com/imagesloaded@4.1.4/imagesloaded.pkgd.min.js', array( 'jquery' ), null, true );
        wp_enqueue_script( $this->plugin_name.'-picturefill.min.js', plugin_dir_url( __FILE__ ) . 'js/picturefill.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-lightgallery-all.min.js', plugin_dir_url( __FILE__ ) . 'js/lightgallery-all.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-jquery.mousewheel.min.js', plugin_dir_url( __FILE__ ) . 'js/jquery.mousewheel.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-jquery.mosaic.min.js', plugin_dir_url( __FILE__ ) . 'js/jquery.mosaic.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-masonry.pkgd.min.js', plugin_dir_url( __FILE__ ) . 'js/masonry.pkgd.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-leaflet.js', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'),  $this->version, true);
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
        // wp_localize_script($this->plugin_name, 'gal_ajax_public', array('ajax_url' => admin_url('admin-ajax.php')));

    }

    public function enqueue_styles_early(){

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function ays_initialize_gallery_shortcode(){
    }
    
    public function ays_generate_gallery( $attr ){
        ob_start();
        // error_log("ays_generate_gallery IN ".print_r($_GET, true));

        // TODO check that the photo belons to the current user
        if (! isset($_GET['guuid'])) {
            my_custom_404();
            wp_die();
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_gallery( $_GET['guuid'] );


        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    public function pg_show_gallery( $uuid ){
        
        global $wpdb;
        
        $gallery = $this->ays_get_gallery_by_uuid($uuid);
        if(!$gallery){
            my_custom_404();
            wp_die();
        }
        /*
         * Gallery global settings
         */
        // error_log("pg_show_gallery, read gallery: ".print_r($gallery, true));
        $id = $gallery['id'];

        $gallery_options = json_decode($gallery['options'],true);
        //error_log("pg_show_gallery read: ".print_r($gallery_options, true));
        $gal_lightbox_options = json_decode($gallery['lightbox_options'],true);
        
        $gallery_options['gallery_loader']  = (!isset($gallery_options['gallery_loader'])) ? "flower" : $gallery_options['gallery_loader'];
        $ays_gallery_loader = (isset($gallery_options['gallery_loader']) && $gallery_options['gallery_loader'] == 'default') ? "flower" : $gallery_options['gallery_loader'];

        // Custom Text
        $gallery_loader_text_value = (isset($gallery_options['gallery_loader_text_value']) && $gallery_options['gallery_loader_text_value'] != '') ? stripslashes($gallery_options['gallery_loader_text_value']) : '';

         // Custom Gif
        $gallery_loader_custom_gif = (isset($gallery_options['gallery_loader_custom_gif']) && $gallery_options['gallery_loader_custom_gif'] != '') ? stripslashes($gallery_options['gallery_loader_custom_gif']) : '';

        // Gallery loader custom gif width
        $gallery_loader_custom_gif_width = (isset($gallery_options['gallery_loader_custom_gif_width']) && $gallery_options['gallery_loader_custom_gif_width'] != '') ? absint( intval( $gallery_options['gallery_loader_custom_gif_width'] ) ) : 100;

        $gallery_loader_custom_gif_width_css = '';
        if ( $gallery_loader_custom_gif_width != '' ) {
            $gallery_loader_custom_gif_width_css = 'width: '. $gallery_loader_custom_gif_width .'px; height: auto; max-width: 100%;';
        }

        $ays_images_border = (!isset($gallery_options['images_border']) ||
                            $gallery_options['images_border'] == false) ? '' : $gallery_options['images_border'];

        $ays_images_border_width    = (!isset($gallery_options['images_border_width']) ||
                                        $gallery_options['images_border_width'] == false) ? '1' : $gallery_options['images_border_width'];
        $ays_images_border_style    = (!isset($gallery_options['images_border_style']) ||
                                        $gallery_options['images_border_style'] == false) ? 'solid' : $gallery_options['images_border_style'];
        $ays_images_border_color    = (!isset($gallery_options['images_border_color']) ||
                                        $gallery_options['images_border_color'] == false) ? '#000000' : $gallery_options['images_border_color'];

        $ays_images_hover_effect = (!isset($gallery_options['images_hover_effect']) || 
                                    $gallery_options['images_hover_effect'] == '' ||
                                    $gallery_options['images_hover_effect'] == null) ? 'simple' : $gallery_options['images_hover_effect'];
        $ays_images_hover_dir_aware = (!isset($gallery_options['hover_dir_aware']) ||
                                      $gallery_options['hover_dir_aware'] == null ||
                                      $gallery_options['hover_dir_aware'] == "") ? "slide" : $gallery_options['hover_dir_aware'];        
        $images_distance = (isset($gallery_options['images_distance']) && $gallery_options['images_distance'] != '') ? absint( intval( $gallery_options['images_distance'] ) ) : '5';
        $glp_lightbox_counter = (!isset($gal_lightbox_options['lightbox_counter']) ||
                                    $gal_lightbox_options['lightbox_counter'] == false) ? "true" : $gal_lightbox_options['lightbox_counter'];
        $glp_lightbox_autoplay = (!isset($gal_lightbox_options['lightbox_autoplay']) ||
                                    $gal_lightbox_options['lightbox_autoplay'] == false) ? "true" : $gal_lightbox_options['lightbox_autoplay'];
        $glp_lightbox_pause  = (!isset($gal_lightbox_options['lb_pause']) ||
                                    $gal_lightbox_options['lb_pause'] == '') ? "5000" : $gal_lightbox_options['lb_pause'];
        $glp_lg_keypress                = (!isset($gal_lightbox_options["lb_keypress"])) ? "true" : $gal_lightbox_options["lb_keypress"];
        $glp_lg_esckey                  = (!isset($gal_lightbox_options["lb_esckey"])) ? "true" : $gal_lightbox_options["lb_esckey"];

        $mosaic_row_size = (isset($gallery_options['mosaic_row_size']) && $gallery_options['mosaic_row_size'] != '') ? absint( intval( $gallery_options['mosaic_row_size'] ) ) : '500';
        $images_hover_zoom = (!isset($gallery_options['hover_zoom']) ||
                              $gallery_options['hover_zoom'] == '' ||
                              $gallery_options['hover_zoom'] == false) ? 'no' : $gallery_options['hover_zoom'];

        //Hover zoom animation Speed
        $hover_zoom_animation_speed = (isset($gallery_options['hover_zoom_animation_speed']) && $gallery_options['hover_zoom_animation_speed'] !== '') ? abs($gallery_options['hover_zoom_animation_speed']) : 0.5;
        $images_loading = ($gallery_options['images_loading'] == '' || $gallery_options['images_loading'] == false) ? 'load_all' : $gallery_options['images_loading'];
        //$images_request = ($gallery_options['images_request'] == '' || $gallery_options['images_request'] == false) ? 'selection' : $gallery_options['images_request'];
        $gallery_options['enable_light_box'] = isset($gallery_options['enable_light_box']) ? $gallery_options['enable_light_box'] : "off";

        $disable_lightbox = (isset($gallery_options['enable_light_box']) && $gallery_options['enable_light_box'] == "off" || $gallery_options['enable_light_box'] == "") ? true : false;

        $hover_effect = (!isset($gallery_options['hover_effect']) || $gallery_options['hover_effect'] == null) ? "fadeIn" : $gallery_options['hover_effect'];
        
        // TODO remove img_load_effect
        //$img_load_effect = (!isset($gallery_options['img_load_effect']) || $gallery_options['img_load_effect'] == null) ? "fadeIn" : $gallery_options['img_load_effect'];

        $redircet_url_tab   = (isset($gallery_options['redirect_url_tab']) && $gallery_options['redirect_url_tab'] != '') ? $gallery_options['redirect_url_tab'] : "_blank";

        //$gpg_resp_width     = isset($gallery_options['resp_width']) && $gallery_options['resp_width'] == "on" ? true :false;
        $gpg_resp_width = false;
        //$gpg_height_width_ratio = isset($gallery_options['height_width_ratio']) && !empty($gallery_options['height_width_ratio']) ? wp_unslash(sanitize_text_field($gallery_options['height_width_ratio'])) : 1;        
        //$resp_width_ratio = $gpg_resp_width ? $gpg_height_width_ratio : 1;        

        /*
         * Gallery image settings
         */
        $columns            = (!isset($gallery_options['columns_count'])) ? 3 : $gallery_options['columns_count'];
        $view               = $gallery_options['view_type'];
        
        if($columns == null || $columns == 0){
            $columns = 3;
        }
        //error_log("columns=".$columns);
        if($images_hover_zoom == "yes"){
            $hover_zoom_In = "$(this).find('img').css({'transform': 'scale(1.15)', 'transition': '.5s ease-in-out', 'transition-duration':'". $hover_zoom_animation_speed ."s'});";
            $hover_zoom_Out = "$(this).find('img').css({'transform': 'scale(1)', 'transition': '.5s ease-in-out', 'transition-duration':'". $hover_zoom_animation_speed ."s'});";
        }else{
            $hover_zoom_In = "";
            $hover_zoom_Out = "";
        }
        $gallery_view_class_for_filter = "";
        switch($view){
            case 'grid':
                $gallery_item_class = ".ays_gallery_container_".$id." div.ays_grid_column_".$id;
                $gallery_view_selector = "#ays_grid_row_".$id;
                $gallery_view_class_for_filter = "ays_grid_row";
                $gallery_lightbox_selector = "ays_grid_column_".$id;

                $column_width = 100 / $columns;
                break;
            case 'mosaic':                        
                $gallery_item_class = ".ays_gallery_container_".$id." .ays_mosaic_column_item_".$id;
                $gallery_view_selector = "#ays_mosaic_".$id;
                $gallery_lightbox_selector = "ays_mosaic_column_item_".$id;
                $column_width = 100 / $columns;
                break;
            case 'masonry':                        
                $gallery_item_class = ".ays_gallery_container_".$id." .ays_masonry_item_".$id;
                $gallery_view_selector = "#ays_masonry_grid_".$id;
                $gallery_lightbox_selector = "ays_masonry_item_".$id;
                $column_width = 100 / $columns;
                break;
            default:
                $gallery_item_class = ".ays_gallery_container_".$id." div.ays_grid_column_".$id;
                $gallery_view_selector = "#ays_grid_row_".$id;
                $gallery_lightbox_selector = "ays_grid_column_".$id;

                $column_width = 100 / $columns;
                break;
        }
        $hover_effect__simple_js = "";
        $ays_hover_dir_aware_js = "";
        if($ays_images_hover_effect == "simple"){
            
            $hover_out_effect = str_replace("In", "Out", $hover_effect);
            $hover_effect__simple_js = "$(document).find('$gallery_item_class').hover(function(){
                                            $(this).find('.ays_hover_mask').css('animation-name', '".$hover_effect."');
                                            $(this).find('.ays_hover_mask').css('animation-duration', '.5s');
                                            $(this).find('.ays_hover_mask').css('opacity', '1');"
                                            . $hover_zoom_In ."
                                        }, 
                                        function(){
                                            $(this).find('.ays_hover_mask').css('animation-name', '".$hover_out_effect."');
                                            $(this).find('.ays_hover_mask').css('animation-duration', '.5s');
                                            $(this).find('.ays_hover_mask').css('opacity', '0');
                                            $hover_zoom_Out
                                        });";
        }elseif($ays_images_hover_effect == "dir_aware"){
            $ays_hover_dir_aware_js = "";
            
            if($ays_images_hover_dir_aware == "slide"){
                $ays_hover_dir_aware_js .= "
                    $(document).find('$gallery_item_class').hover(function(e){
                        var ays_x = e.pageX - this.offsetLeft;
                        var ays_y = e.pageY - this.offsetTop;
                        var ays_edge = ays_closestEdge(ays_x,ays_y,this.clientWidth, this.clientHeight);
                        var ays_overlay = $(this).find('.ays_hover_mask');
                        var ays_hover_dir = ays_getDirectionKey(e, e.currentTarget);
                        switch(ays_edge) {
                            case 'top':
                                ays_overlay.css('display', 'flex');
                                ays_overlay.css('animation', 'slideInDown .3s');$hover_zoom_In
                                break;
                            case 'right':
                                ays_overlay.css('display', 'flex');
                                ays_overlay.css('animation', 'slideInRight .3s');$hover_zoom_In
                                break;
                            case 'bottom':
                                ays_overlay.css('display', 'flex');
                                ays_overlay.css('animation', 'slideInUp .3s');$hover_zoom_In
                                break;
                            case 'left':
                                ays_overlay.css('display', 'flex');
                                ays_overlay.css('animation', 'slideInLeft .3s');$hover_zoom_In
                                break;
                        }
                    },
                    function(e){
                        var ays_x = e.pageX - this.offsetLeft;
                        var ays_y = e.pageY - this.offsetTop;
                        var ays_edge = ays_closestEdge(ays_x,ays_y,this.clientWidth, this.clientHeight);
                        var ays_overlay = $(this).find('.ays_hover_mask');
                        var ays_hover_dir = ays_getDirectionKey(e, e.currentTarget);
                        switch(ays_edge) {
                            case 'top':
                            ays_overlay.css('animation', 'slideOutUp .3s');$hover_zoom_Out
                            setTimeout( function(){ ays_overlay.css('display', 'none');}, 250);
                            break;
                            case 'right':
                            ays_overlay.css('animation', 'slideOutRight .3s');$hover_zoom_Out
                            setTimeout( function(){ ays_overlay.css('display', 'none');}, 250);
                            break;
                            case 'bottom':
                            ays_overlay.css('animation', 'slideOutDown .3s');$hover_zoom_Out
                            setTimeout( function(){ ays_overlay.css('display', 'none');}, 250);
                            break;
                            case 'left':
                            ays_overlay.css('animation', 'slideOutLeft .3s');$hover_zoom_Out
                            setTimeout( function(){ ays_overlay.css('display', 'none'); }, 250);
                            break;
                        }
                    });";
            }
        }

        $ays_images_all_loaded = '';
        if($images_loading == 'load_all'){        
            $ays_gal_loader_display = "display: block";
            if ($ays_gallery_loader == 'text') {
                $ays_images_all_loaded = "<div class='gpg_loader_".$id." glp_loader'>
                    <p class='ays-loader-content'>". $gallery_loader_text_value ."</p>
                </div>";
            }elseif ($ays_gallery_loader == 'custom_gif') {
                if ($gallery_loader_custom_gif != '') {
                    $ays_images_all_loaded = "<div class='gpg_loader_".$id." glp_loader'>
                        <img src='". $gallery_loader_custom_gif ."' style='". $gallery_loader_custom_gif_width_css ."'>
                        </div>";
                }else{
                    $ays_images_all_loaded = "<div class='gpg_loader_".$id." glp_loader'>
                        <img src='".GLP_PUBLIC_URL."images/flower.svg'>
                    </div>";
                }
            }else{
                $ays_images_all_loaded = "<div class='gpg_loader_".$id." glp_loader'>
                        <img src='".GLP_PUBLIC_URL."images/$ays_gallery_loader.svg'>
                    </div>";
            }
            $ays_gal_loader_class = ".gpg_loader_".$id."";
        }else{
            $ays_images_all_loaded = '';
            $ays_gal_loader_display = "display: none";
        }

        if($disable_lightbox){
            $cat_show_lightbox = "$(document).find('.gpgFilteredContainer').lightGallery(gpgFilterLightboxOptions);";
            $srch_show_lightbox = "$(document).find('".$gallery_view_selector."').lightGallery(gpgSearchLightboxOptions);";
        }else{
             $cat_show_lightbox = "";
             $srch_show_lightbox = "";
        }
        
        $glp_container_display_none_js = "";
        $glp_container_display_block_js = "";
        $glp_container_error_message_js = "";
        $ays_gal_loader_display_js = "";
        $ays_gal_loader_none_js = "";
        $glp_lazy_load_masonry = "";
        $glp_lazy_load_grid = "";
        $glp_lazy_load_animation = '';
        $glp_lazy_load_mosaic = "";
        $glp_lazy_load_mosaic_css = "";

        if($images_loading == 'lazy_load'){ // TODO lazy_load
            
            // for masonry
            $glp_lazy_load_animation = "
            $(document).find('.ays_gallery_container_".$id." .$gallery_lightbox_selector > img').each(function(e, img){
                img.onload = function(e){
                    img.classList.add('lazyloaded');
                    aysgrid_".$id.".masonry('layout');
                }
            });";
            $glp_container_css = "display: block;";
            $ays_images_lazy_loader_css = ".ays_gallery_container_".$id." .ays_image_loading_div {
                                                display: flex;
                                            }";
            if ($view == 'grid') {
                $glp_lazy_load_grid = "
                    document.addEventListener('scroll', lazyload_max);
                    window.addEventListener('resize', lazyload_max);
                    window.addEventListener('orientationChange', lazyload_max);
                    lazyload_max();";
            }
            else if ($view == 'masonry') {
                $glp_lazy_load_masonry = "
                    document.addEventListener('scroll', lazyload_max);
                    window.addEventListener('resize', lazyload_max);
                    window.addEventListener('orientationChange', lazyload_max);
                    lazyload_max();";
            }
            else { //mosaic
            
                $glp_lazy_load_mosaic = "$(document).find('.ays_gallery_container_".$id." .mosaic_".$id."').Mosaic({
                                            innerGap: {$images_distance},
                                            refitOnResize: true,
                                            showTailWhenNotEnoughItemsForEvenOneRow: true,
                                            maxRowHeight: {$mosaic_row_size},
                                            maxRowHeightPolicy: 'tail'
                                        });";
                $glp_lazy_load_mosaic_css = ".ays_mosaic_column_item_".$id." a>img {
                                                opacity: 0;
                                             }
                                             .ays_mosaic_column_item_".$id." a div.ays_hover_mask {
                                                display: none;
                                             }";
            }
        // end if($images_loading == 'lazy_load'){
        }else{     // no lazy loading    
            $glp_container_display_none_js = "$(document).find('.ays_gallery_container_".$id."').css({'display': 'none'});";
            
            $ays_gal_loader_display_js = "$(document).find('".$ays_gal_loader_class."').css({'display': 'flex', 'animation-name': 'fadeIn'});";
            $ays_gal_loader_none_js = "$(document).find('".$ays_gal_loader_class."').css({'display': 'none', 'animation-name': 'fadeOut'});";
            $glp_container_display_block_js = "$(document).find('.ays_gallery_container_".$id."').css({'display': 'block', 'animation-name': 'fadeIn'});";
            $glp_container_error_message_js = "$(document).find('.ays_gallery_container_".$id."').prepend(errorImage);";
            $glp_container_css = "display: none;";
            $ays_images_lazy_loader_css = ".ays_gallery_container_".$id." .ays_image_loading_div {
                                                display: none;
                                            }";
        }        
        $gallery_view = $ays_images_all_loaded;

        $gallery_view .= "<script>
                (function($){
                    'use strict';
                    $(document).ready(function(){
                       var ays_gallery_containers = document.getElementsByClassName('ays_gallery_container_".$id."');
                       var ays_gallery_container_".$id.";
                       for(var ays_i = 0; ays_i < ays_gallery_containers.length; ays_i++){
                           do{
                                ays_gallery_container_".$id." = ays_gallery_containers[ays_i].parentElement.parentElement;
                            }
                            while(ays_gallery_container_".$id.".style.position === 'relative');
                            ays_gallery_container_".$id.".style.position = 'static';
                        }
                        
                        var gpgFilterLightboxOptions = {
                            selector: '.$gallery_lightbox_selector',
                            share: false,
                            hash: false,
                            addClass:'glp_lightbox_".$id."',
                            fullScreen: false,
                            autoplay: false,
                            pause: $glp_lightbox_pause,
                            mousewheel: false,
                            keyPress: false,
                            actualSize: false,
                            pager: false,
                            download: false,
                            autoplayControls: $glp_lightbox_autoplay,
                            counter: $glp_lightbox_counter,
                            showThumbByDefault: false,
                            getCaptionFromTitleOrAlt: false,
                            subHtmlSelectorRelative: true
                        };                        

                        $(document).on('click', '.glp_category_filter', function(e) {
                            var ays_cat = $(this).data('cat');
                            var ays_img_cat = document.getElementsByClassName('ays_grid_column_".$id."');
                            var galContainer = $(this).parents('.ays_gallery_container_".$id."');
                            var anim_effect = galContainer.find('.ays_gallery_filter_cat').attr('data-anim');
                            galContainer.find('.gpgFilteredContainer').remove();

                            var mainGallery = galContainer.find('.ays_grid_row');
                            if(ays_cat == 'all'){
                                mainGallery.css('animation-name', anim_effect);
                                mainGallery.css('animation-duration', '.5s');
                                mainGallery.find('.ays_grid_column_".$id."').hide();
                                mainGallery.removeClass('glp_display_none');
                                mainGallery.find('.ays_grid_column_".$id."').fadeIn(400);
                                mainGallery.attr('id','ays_grid_row_".$id."');
                            }else{
                                var filterElements = mainGallery.find('[data-cat*=\"'+ays_cat+'\"]');
                                var filterContainer = $('<div class=\"gpgFilteredContainer ".$gallery_view_class_for_filter."\" id=\"ays_grid_row_".$id."\"></div>');
                                filterElements.each(function(){
                                    var fclone = $(this).clone();
                                    fclone.hide();
                                    filterContainer.append(fclone);
                                });
                                galContainer.append(filterContainer);
                                galContainer.find('.gpgFilteredContainer').css('animation-name', anim_effect);
                                galContainer.find('.gpgFilteredContainer').css('animation-duration', '.5s');
                                galContainer.find('.ays_grid_column_".$id."').fadeIn(400);
                                mainGallery.addClass('glp_display_none');
                                mainGallery.removeAttr('id');
                                ". $cat_show_lightbox ."
                                ". $hover_effect__simple_js ."
                                ". $ays_hover_dir_aware_js ."
                                
                            }
                        });
                        ". $glp_lazy_load_grid 
                        . $glp_lazy_load_masonry ."
                    });
                })(jQuery);
        </script>
        <style>
            $ays_images_lazy_loader_css
            
            .ays_gallery_container_".$id." {
                $glp_container_css
            }
            $glp_lazy_load_mosaic_css
            .gpg_gal_loader_".$id."  {
                $ays_gal_loader_display;
                justify-content: center;
                align-items: center;
                animation-duration: .5s;
                transition: .5s ease-in-out;
                margin-bottom: 20px;
                width: 50px;
                height: 50px;
                margin: auto !important;
            }
            .gpg_loader_".$id." {
                $ays_gal_loader_display;
            }
        </style>";

        if($ays_images_border === "on"){
            $show_images_with_border = "border: ".$ays_images_border_width."px ".$ays_images_border_style." ".$ays_images_border_color.";";
            $show_mosaic_border_js = "setTimeout(function(){
                                $(document).find('.ays_gallery_container_".$id." .mosaic_".$id." .ays_mosaic_column_item_".$id."').css('border', '".$ays_images_border_width."px ".$ays_images_border_style." ".$ays_images_border_color."');
                            }, 500);";
        }else{
            $show_images_with_border = "border: none";
            $show_mosaic_border_js = "";
        }
        $custom_class = isset($gallery_options['custom_class']) && $gallery_options['custom_class'] != "" ? $gallery_options['custom_class'] : "";
        $gallery_view .= "<div class='ays_gallery_body_".$id." ".$custom_class."'>";
        if($images_loading == 'lazy_load'){
            $gallery_view .= $this->ays_get_gallery_content($gallery, $gallery_options, $gal_lightbox_options, $id);
        }
        
        $responsive_width_height = "";
        // if ($gpg_resp_width) {
        //      if($images_loading == 'load_all'){
        //         $responsive_width_height = "
        //         $('#ays_grid_row_".$id." img.ays_gallery_image').each(function(){
        //                 var realWidth = this.width;                    
        //                 var ratio = parseFloat(".$resp_width_ratio.")*realWidth;                    
        //                 $('#ays_grid_row_".$id." img.ays_gallery_image').each(function(e) {
        //                     $(this).height(ratio);
        //                 });

        //             });
        //        ";
        //     }elseif($images_loading == 'lazy_load'){
        //         $responsive_width_height = "
        //             $('#ays_grid_row_".$id." img.ays_gallery_image').each(function(){
        //                 var realWidth = $(this).parents('.ays_grid_column_".$id."').width();                    
        //                 var ratio = parseFloat(".$resp_width_ratio.") * realWidth;                    
        //                 $(this).parents('.ays_grid_column_".$id."').height(ratio);
        //             });";
        //     }
        // }

        $gallery_view .= "</div>
        <script>
        (function($){";
            // if no lazy loading
            if($images_loading == 'load_all'){
                $gallery_cont = $this->ays_get_gallery_content($gallery, $gallery_options, $gal_lightbox_options, $id);
                $gallery_cont = addslashes($gallery_cont);
                $gallery_view .= '
                $(document).ready(function(){
                    setTimeout(function(ev){
                        var aysGalleryContent_'.$id.' = $("'.$gallery_cont.'");
                        $(document).find(".ays_gallery_body_'.$id.'").append(aysGalleryContent_'.$id.'); 
                        $( window ).resize(function() {
                            $(document).find(".mosaic_'.$id.'").Mosaic({
                                innerGap: '.$images_distance.',
                                refitOnResize: true,
                                showTailWhenNotEnoughItemsForEvenOneRow: true,
                                maxRowHeight: '.$mosaic_row_size.',
                                maxRowHeightPolicy: "tail"
                            });
                        });
                        '.$hover_effect__simple_js.'
                        '.$ays_hover_dir_aware_js.'
                        $(document).find("'.$gallery_lightbox_selector.'").on("mouseover", function(){
                            if($(this).find(".ays_hover_mask .ays_image_title").length != 0){
                                $(this).find(".ays_hover_mask>div:first-child").css("margin-bottom", "40px");
                            }
                        });
                        $(document).find(".'.$gallery_lightbox_selector.' div.ays_hover_mask").find(".ays_image_url").on("click", function(e){
                            setTimeout(function(){
                                $(document).find(".lg-close.lg-icon").trigger("click");
                            },450);
                            window.open($(this).attr("data-url"),"'.$redircet_url_tab.'");
                        }); 
                        $(document).find(".'.$gallery_lightbox_selector.' div.ays_link_whole_image_url").on("click", function(e){
                            setTimeout(function(){
                                $(document).find(".lg-close.lg-icon").trigger("click");
                            },450);
                            window.open($(this).attr("data-url"),"_blank");
                        });'
                        .$ays_gal_loader_display_js.'
                        var aysgrid_'.$id.' = $("#ays_masonry_grid_'.$id.'").masonry({  
                            percentPosition: false,
                            itemSelector: ".ays_masonry_grid-item",
                            columnWidth: ".ays_masonry_item_'.$id.'",
                            transitionDuration: ".8s",
                            gutter: '.$images_distance.',
                            
                        }); 
                        '.$glp_lazy_load_animation.'
                        '.$glp_lazy_load_mosaic.'

                        setTimeout(function(){
                            $(document).find(".ays_gallery_container_'.$id.' .mosaic_'.$id.'").Mosaic({
                                innerGap: '.$images_distance.',
                                refitOnResize: true,
                                showTailWhenNotEnoughItemsForEvenOneRow: true,
                                maxRowHeight: '.$mosaic_row_size.',
                                maxRowHeightPolicy: "tail"
                            });
                            aysgrid_'.$id.'.masonry("layout");
                        },300);
                        
                        var gpgSearchLightboxOptions = {
                            selector: ".gpgSearchContainer",
                            share: false,
                            hash: false,
                            addClass:"glp_lightbox_'.$id.'",
                            fullScreen: false,
                            autoplay: false,
                            pause: '.$glp_lightbox_pause.',
                            mousewheel: false,
                            keyPress: false,
                            actualSize: false,
                            pager: false,
                            download: false,
                            autoplayControls: '.$glp_lightbox_autoplay.',
                            counter: '.$glp_lightbox_counter.',
                            showThumbByDefault: false,
                            getCaptionFromTitleOrAlt: false,
                            subHtmlSelectorRelative: true
                        };';

                        if($disable_lightbox){
                           $gallery_view .= "$(document).find('$gallery_view_selector').lightGallery({
                                selector: '.$gallery_lightbox_selector',
                                share: false,
                                hash: false,
                                addClass:'glp_lightbox_".$id."',
                                fullScreen: false,
                                autoplay: false,
                                pause: $glp_lightbox_pause,
                                mousewheel: false,
                                keyPress: $glp_lg_keypress,
                                escKey: $glp_lg_esckey,
                                actualSize: false,
                                pager: false,
                                download: false,
                                autoplayControls: $glp_lightbox_autoplay,
                                counter: $glp_lightbox_counter,
                                showThumbByDefault: false,
                                getCaptionFromTitleOrAlt: false,
                                subHtmlSelectorRelative: true
                            });";
                        }
                        $gallery_view .= "$(document).find('.ays_gallery_container_".$id."').imagesLoaded()
                            .done( function( instance ){
                                $glp_container_display_block_js
                                ".$responsive_width_height."
                                $ays_gal_loader_none_js
                                $(document).find('.ays_gallery_container_".$id." .mosaic_".$id."').Mosaic({
                                    innerGap: {$images_distance},
                                    refitOnResize: true,
                                    showTailWhenNotEnoughItemsForEvenOneRow: true,
                                    maxRowHeight: {$mosaic_row_size},
                                    maxRowHeightPolicy: 'tail'
                                });
                                setTimeout(function(){
                                    aysgrid_".$id.".masonry('layout');
                                },300);
                                var ays_gallery_containers = $(document).find('.ays_gallery_container_".$id."');
                                ays_gallery_containers.css({
                                   position: 'static'
                                });
                                $show_mosaic_border_js
                                $(window).trigger('resize');
                            })
                            .fail( function() {
                                var errorImage = $('<div><p>".__("Some of the images haven\'t been loaded", $this->plugin_name)."</p></div>');
                                $glp_container_error_message_js
                                $glp_container_display_block_js
                                $ays_gal_loader_none_js
                                $(document).find('.ays_masonry_item_".$id.">img, .ays_mosaic_column_item_".$id.">img, .ays_grid_column_".$id.">img').each(function(e, img){
                                    if(img.getAttribute('src') == '' || img.getAttribute('src') == undefined){
                                        $(this).css('display', 'none');
                                        $(this).parent().find('.ays_hover_mask').remove();
                                        $(this).parent().find('.ays_image_loading_div').css({
                                            'opacity': '1',
                                            'animation-name': 'tada',
                                            'animation-duration': '.5s',
                                            'display': 'block',
                                            'padding-top': 'calc(".(($view == 'grid')?100:50)."% - 25px)',
                                        });
                                        $(this).parent().find('.ays_image_loading_div').find('img').css('position', 'static');
                                        $(this).parent().find('.ays_image_loading_div').find('img').attr('src', '".GLP_PUBLIC_URL."/images/error-404.png');
                                        var ays_err_massage = $('<span>Image not found!</span>');
                                        var img_parent = $(this).parent().find('.ays_image_loading_div').eq(0);
                                        img_parent.append(ays_err_massage);
                                        ".
                                        (($view == 'masonry')?"$(this).parent().css('height', '200px');":"")
                                        ."$(this).remove();
                                    }
                                });
                                setTimeout(function(){
                                    aysgrid_".$id.".masonry('layout');                                
                                    $(window).trigger('resize');
                                },300);
                            });
                        },1000);
                    });";
            }
            // else lazy_load TODO lazy loading
            else{
                // when page is loaded
                $gallery_view .= "
                ".$responsive_width_height." 
                window.addEventListener('load', function(e){
                    setTimeout(function(){
                        var aysGalleryImages_".$id." = JSON.parse(window.atob(window.aysGalleryOptions.galleryImages[".$id."]));
                        $(window).resize(function() {
                            $(document).find('.mosaic_".$id."').Mosaic({
                                innerGap: {$images_distance},
                                refitOnResize: true,
                                showTailWhenNotEnoughItemsForEvenOneRow: true,
                                maxRowHeight: {$mosaic_row_size},
                                maxRowHeightPolicy: 'tail'
                            });
                        });
                        $hover_effect__simple_js
                        $ays_hover_dir_aware_js
                        $(document).find('.$gallery_lightbox_selector').on('mouseover', function(){
                            if($(this).find('.ays_hover_mask .ays_image_title').length != 0){
                                $(this).find('.ays_hover_mask>div:first-child').css('margin-bottom', '40px');
                            }
                        });
                        $(document).find('.$gallery_lightbox_selector div.ays_hover_mask').find('.ays_image_url').on('click', function(e){
                            setTimeout(function(){
                                $(document).find('.lg-close.lg-icon').trigger('click');
                            },450);                        
                            window.open($(this).attr('data-url'),'".$redircet_url_tab."');
                        });
                        $(document).find('.$gallery_lightbox_selector div.ays_link_whole_image_url').on('click', function(e){
                            setTimeout(function(){
                                $(document).find('.lg-close.lg-icon').trigger('click');
                            },450);
                            window.open($(this).attr('data-url'),'".$redircet_url_tab."');
                        });
                        var aysgrid_".$id." = $('#ays_masonry_grid_".$id."').masonry({
                            percentPosition: false,
                            itemSelector: '.ays_masonry_grid-item',
                            columnWidth: '.ays_masonry_item_".$id."',
                            gutter: {$images_distance},

                        });
                        aysgrid_".$id.".masonry('layout');
                        aysgrid_".$id.".on( 'layoutComplete', function() {
                            lazyload_single();
                        });
                      
                        ".$ays_gal_loader_display_js." 
                        ".$glp_lazy_load_animation." 
                        ".$glp_lazy_load_mosaic." ";

                        $gallery_view .='var gpgSearchLightboxOptions = {
                            selector: ".gpgSearchContainer",
                            share: false,
                            hash: false,
                            addClass:"glp_lightbox_'.$id.'",
                            fullScreen: false,
                            autoplay: false,
                            pause: '.$glp_lightbox_pause.',
                            mousewheel: false,
                            keyPress: false,
                            actualSize: false,
                            pager: false,
                            download: false,
                            autoplayControls: '.$glp_lightbox_autoplay.',
                            counter: '.$glp_lightbox_counter.',
                            showThumbByDefault: false,
                            getCaptionFromTitleOrAlt: false,
                            subHtmlSelectorRelative: true
                        };';

                        if($disable_lightbox){
                        $gallery_view .= "$(document).find('$gallery_view_selector').lightGallery({
                                selector: '.$gallery_lightbox_selector',
                                share: false,
                                hash: false,
                                addClass:'glp_lightbox_".$id."',
                                fullScreen: false,
                                autoplay: false,
                                pause: $glp_lightbox_pause,
                                mousewheel: false,
                                keyPress: $glp_lg_keypress,
                                escKey: $glp_lg_esckey,
                                actualSize: false,
                                pager: false,
                                download: false,
                                autoplayControls: $glp_lightbox_autoplay,
                                counter: $glp_lightbox_counter,
                                showThumbByDefault: false,
                                getCaptionFromTitleOrAlt: false,
                                subHtmlSelectorRelative: true
                            });";

                    }
                    $gallery_view .= "console.log('timeout out'); 
                },1000);
                }, false);";
            }
        $gallery_view .= "
            })(jQuery);
        </script>";
        return $gallery_view;
    } // end ays_show_galery()

    public function ays_gallery_replace_message_variables($content, $data){
        foreach($data as $variable => $value){
            $content = str_replace("%%".$variable."%%", $value, $content);
        }
        return $content;
    }

    public function ays_get_gallery_by_uuid( $uuid ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_gallery WHERE uuid={$uuid}";

        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        return $result;
    }

    public function ays_get_gallery_categories() {
        $taxonomy = 'category'; // Change 'your_taxonomy' to the name of your taxonomy
        $terms = get_terms( array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false, // Set to true if you want to hide empty terms
        ) );
        //error_log("terms ".print_r($terms, true));
        return $terms;
    }

    public static function get_gallery_category_by_id($id){
        global $wpdb;

        $sql = "SELECT *
                FROM {$wpdb->prefix}glp_gallery_categories
                WHERE id=" . $id;

        $category = $wpdb->get_row($sql, 'ARRAY_A');

        return $category;
    }

    public static function ays_gallery_autoembed( $content ) {
        global $wp_embed;

        if ( is_null( $content ) ) {
            return $content;
        }

        $content = stripslashes( wpautop( $content ) );
        $content = $wp_embed->autoembed( $content );
        if ( strpos( $content, '[embed]' ) !== false ) {
            $content = $wp_embed->run_shortcode( $content );
        }
        $content = do_shortcode( $content );
        return $content;
    }
    
    // Function to find the value for a given meta_key
    private function findValueByKey($array, $key) {
        //error_log("findValueByKey IN");
        foreach ($array as $item) {
            if ($item['meta_key'] === $key) {
                //error_log("findValueByKey found: ".$item['meta_value']);
                return $item['meta_value'];
            }
        }
        //error_log("findValueByKey not found");
        return null; // Return null if the key is not found
    }

    // Function to find the value for a given country (ex "Albania.geojson")
    private function getCountryOptions($fileName) {
        error_log("getCountryOptions in filename=".$fileName);
        $this->load_countries();
        
        foreach ($this->countries as $item) {
            if ($item['file'] === $fileName) {
                //error_log("getCountryOptions: ".print_r($item, true)."");
                return $item;
            }
        }
        // error_log("getCountryOptions out null");
        return null; // Return null if the file is not found
    }
    
    private function load_countries() {
        // error_log("load_countries: IN");
        if (!count($this->countries)) {
            
            // Add None
            //$dict["None"] = "None";

            $worldfile = GLP_DIR . 'assets/world.json';

            $json = file_get_contents($worldfile); 
            if ($json === false) {
               error_log("load_countries: Error reading file");
               return;
            }
            $this->countries = json_decode($json, true);
            //error_log("countries: ".print_r($this->countries, true));
        }
        else {
            error_log("load_countries: already loaded");
        }

    }

    // Check if geolocation parameters are correct
    // return null if on is wrong
    // return country options if correct
    private function checkGeolocation($latitude, $longitude, $country) {
        // error_log("checkGeolocation IN country=$country");
        
        if (!is_numeric($latitude)) {
            error_log("checkGeolocation lat out NULL");
            return null;
        }

        if (!is_numeric($longitude)) {
            error_log("checkGeolocation lon out NULL");
            return null;
        }
        //check if country file exists
        return $this->getCountryOptions($country);
        
        //error_log("findValueByKey not found");
        //return null; // Return null if the key is not found
    }
   
    // $type = "masonry" or "grid" or mosaic
    private function ays_add_images($view, $images_new, $show_title, $image_titles, $image_dates, 
        $show_with_date, $image_descs, $image_alts, $image_ids, $id, $images_loading, $disable_lightbox, 
        $show_title_on, $html_hover_icon, $ays_show_caption, $ays_images_loader, $images,
        $image_countries, $image_latitudes, $image_longitudes, $images_categories, $images_distance,
        $column_width, $vignette_display) {
        // error_log("ays_add_images IN  vignette_display=". $vignette_display);
        // error_log("ays_add_images IN  view=". $view);
        $gallery_view = "";
        if ($view == "masonry") {
            $gallery_view .= "<div class='ays_masonry_grid' id='ays_masonry_grid_".$id."'><div class='ays_masonry_grid-sizer'></div>";
        }
        elseif ($view == "mosaic") {
            $gallery_image_sizes = '';
            $gallery_view .= "<div class='mosaic_".$id."' id='ays_mosaic_".$id."' style='clear:both;'>";
        }
        elseif ($view == "grid") {
            $gallery_view .= "<div class='ays_grid_row' id='ays_grid_row_".$id."'>";
        }

        foreach($images_new as $key=>$image){
            
            if($show_title == 'on' && $image_titles[$key] != ''){
                if($show_with_date == 'on'){
                    $ays_show_with_date = "<span>".date( "F d, Y", intval($image_dates[$key]))."</span>";
                }else{
                    $ays_show_with_date = "";
                }
                $ays_show_title = "<div class='ays_image_title'>
                                        <span>".wp_unslash($image_titles[$key])."</span>
                                        $ays_show_with_date
                                        </div>";
            }else{
                $ays_show_title = '';
            }

            if (isset($images_categories[$key])) {
                if($images_categories[$key] == ""){
                    $images_cat_data_id = "";
                }else{
                    $img_cat_id = explode(',', $images_categories[$key]);                        
                    $img_cat_ids = implode(" ", $img_cat_id);
                    $images_cat_data_id = " data-cat='".wp_unslash($img_cat_ids)."' ";
                }                        
            }else{
                $images_cat_data_id = "";
            }
            // TODO test lightbox when lazy loading
            if($images_loading == 'lazy_load'){
                $current_image = '';
                $image_class = 'ays_gallery_image lazy';
                $src_attribute = 'data-src';
            }else{
                $current_image = $image;
                $image_class = 'ays_gallery_image';
                $src_attribute = 'src';
            }

            $img_tag = "";
            $vignette_div = "";
            // if no vignette
            if ($image_countries[$key] == null) {
                // error_log("ays_add_images NO vignette");
                //$img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "' onload='console.log(\"ID=".$image_ids[$key]."\")'>";
                $img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) . "'>";
            }
            else {
                $lat = $image_latitudes[$key];
                $lon = $image_longitudes[$key];
                $file = $image_countries[$key]['file'];
                $geo_height = $image_countries[$key]['height'];
                $geo_width = $image_countries[$key]['width'];
                $zoom = $image_countries[$key]['zoom'];
                $lmapId = "lmap-".$image_ids[$key]."";
                //$imgId = "img-".$image_ids[$key]."";
                $img_tag ="<img class='". $image_class ."' ". $src_attribute ."='". $image ."' alt='" . wp_unslash($image_alts[$key]) 
                          ."' onload='ays_add_vignette_to_image(\"".esc_attr("$lmapId")."\",\"".esc_attr($file)."\",".$lat.",".$lon.",".$zoom.")'>";
                              
                $vignette_div ="<div id='".$lmapId."' class='overlay-image' style='width: ".$geo_width."; height: ".$geo_height.";'></div>";
                // error_log("ays_add_images vignette_div=".$vignette_div);
            }
            
            $image_url = "";
            // if($image_urls[$key] == ""){
            //     $image_url = "";
            // }else{
            //     $image_url = "<button type='button' data-url='".$image_urls[$key]."' class='ays_image_url'><i class='glp_fa glp_fa_for_gallery glp_fa_link'></i></button>";
            // }
            if($show_title_on == 'gallery_image'){
                $hiconpos = ($show_title=='on')?" style='margin-bottom:40px;' ":"";
                if($disable_lightbox){

                    $show_title_in_hover = "<div class='ays_hover_mask animated'>".$vignette_div."<div $hiconpos>".$html_hover_icon."</div></div> $ays_show_title ";
                }else{
                    $show_title_in_hover = "<div class=''><div $hiconpos></div></div> $ays_show_title ";
                }
            }elseif($show_title_on == 'image_hover'){
                if($disable_lightbox){

                    $show_title_in_hover = "<div class='ays_hover_mask animated'>".$vignette_div."<div>".$html_hover_icon."</div>$ays_show_title</div>";
                }else{
                    if ($view == "mosaic") {                    
                        $show_title_in_hover = "<div class=''><div></div>$ays_show_title </div>";
                    }
                    if ($view == "masonry" || $view == "grid") {                    
                        $show_title_in_hover = "<div class=''>$ays_show_title </div>";
                    }
                }
            }
            // TODO check that if lightbox is disabled, there is no hover
            // $show_title_in_hover = $disable_lightbox ? $show_title_in_hover : '';

            $ays_caption = "";
            $ays_data_sub_html = "";
            if($ays_show_caption){
                $ays_caption = "<div class='ays_caption_wrap'>
                            <div class='ays_caption'>
                                <h4>".$image_titles[$key]."</h4>
                                <p>" . wp_unslash($image_descs[$key]) . "</p>
                            </div>
                        </div>";
                $ays_data_sub_html = " data-sub-html='.ays_caption_wrap' ";
            }
     
            $wh_attr = '';
            if (!empty($image)) {

                // TODO use image size if possible, lazy loading, ....
                // $img_attr = getimagesize("/uploads/2024/02/20230727_165757-scaled.jpg", $info);

                // error_log(" http ->".print_r($http_response_header, true));
                // error_log(" image ->".print_r($img_attr, true));
                // error_log(" image info->".print_r($info, true));
                //$wh_attr = " width='".$img_attr[0]."' height='".$img_attr[1]."' ";
            }
         
            
            if ($view == "mosaic") {
                $gallery_view .= "<div class='item withImage ays_mosaic_column_item_".$id." ays_count_views' data-src='" . $images[$key] .
                    "' data-desc='" . $image_titles[$key] ." ". $image_alts[$key] ." ". $image_descs[$key] ."' ".$ays_data_sub_html.">".
                    "<img src='" . $image . "' alt='" . wp_unslash($image_alts[$key]) . "' />";

            }
            elseif ($view == "masonry") {
                //MJO TODO remove console.log in onload
                $gallery_view .= "<div class='ays_masonry_grid-item ays_masonry_item_".$id." ays_count_views' data-src='" . $images[$key] .
                    "' data-desc='" . $image_titles[$key] ." ". $image_alts[$key] ." ". $image_descs[$key] ."' ".$ays_data_sub_html.">";
                    $gallery_view .=$img_tag;
            }
            elseif ($view == "grid") {
                //MJO TODO remove data-src ?
                $gallery_view .="<div class='ays_grid_column_".$id." ays_count_views' style='width: calc(".($column_width)."% - ".($images_distance)."px);' ".
                    $images_cat_data_id." data-src='" . $images[$key] . "' data-desc='" . $image_titles[$key] ." ". $image_alts[$key] ." ". $image_descs[$key] ."' ".$ays_data_sub_html.">";
                $gallery_view .=$img_tag;
            }

                        
            //$gallery_view .=$img_tag;
            if ($image_countries[$key] != null && $vignette_display == 'permanent' ) {
                // add the vignette div beside the image
                $gallery_view .=$vignette_div;
            }

            $gallery_view .= 
                        "$ays_caption
                        $show_title_in_hover
                        <a href='javascript:void(0);'></a>
                    </div>";
        } // end foreach image
        
        if ($view == "mosaic") {
            $gallery_view .= "</div><div style='clear:both;'></div>";
        }
        elseif ($view == "masonry" || $view == "grid") {
            $gallery_view .= "</div>";
        }

        // if lazy loading
        if($images_loading == 'lazy_load'){ // TODO lazy_load
            $gallery_view .= "<script>
                if(typeof aysGalleryOptions === 'undefined'){
                    var aysGalleryOptions = {};
                }
                if(typeof aysGalleryOptions.galleryImages === 'undefined'){
                    aysGalleryOptions.galleryImages = [];
                }
                console.log('aysGalleryOptions.galleryImages', aysGalleryOptions.galleryImages);
                aysGalleryOptions.galleryImages[".$id."] = '" . base64_encode( json_encode( $images_new ) ) . "';
            </script>";
        }       

        return $gallery_view;
    
    }// end ays_add_images()

    private function get_over_icon($ays_hover_icon) {
        switch($ays_hover_icon){
            case 'none':
                $html_icon = "";
                break;
            case 'search_plus':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_search_plus'></i>";
                break;
            case 'search':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_search'></i>";
                break;
            case 'plus':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_plus'></i>";
                break;
            case 'plus_circle':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_plus_circle'></i>";
                break;
            case 'plus_square_fas': 
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_plus_square_fas'></i>";
                break;
            case 'plus_square_far':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_plus_square_far'></i>";
                break;
            case 'expand':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_expand'></i>";
                break;
            case 'image_fas':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_image_fas'></i>";
                break;
            case 'image_far':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_image_far'></i>";
                break;
            case 'images_fas':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_images_fas'></i>";
                break;
            case 'images_far':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_images_far'></i>";
                break;
            case 'eye_fas':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_eye_fas'></i>";
                break;
            case 'eye_far':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_eye_far'></i>";
                break;
            case 'camera_retro':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_camera_retro'></i>";
                break;
            case 'camera':
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_camera'></i>";
                break;
            default:
                $html_icon = "<i class='glp_fa glp_fa_for_gallery glp_fa_search_plus'></i>";
                break;
        }
        return $html_icon;
    }
    
    protected function ays_get_gallery_content($gallery, $gallery_options, $gal_lightbox_options, $id){
        global $wpdb;
        // error_log("ays_get_gallery_content IN");
        $settings_options = Gallery_Settings_Actions::ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode(stripcslashes($settings_options), true);
        }else{
            $settings_options = array();
        }

        $gal_categories = $this->ays_get_gallery_categories();
        $gal_cat_id = array();
        $gal_cat_title = array();
        foreach ($gal_categories as $cat_key => $cat_value) {
            //error_log("cat_key: ".$cat_key);
            //error_log("cat_value: ".$cat_value->name);
            $gal_cat_id[$cat_key] = $cat_value->term_id;
            $gal_cat_title[$cat_key] = $cat_value->name;
        }
        //$width = $gallery["width"];
        $title = $gallery["title"];
        // $description = $gallery["description"];
        $description = Glp_Gallery_Data::glp_autoembed( $gallery["description"] );
        $custom_css = ($gallery['custom_css'] == '' || $gallery['custom_css'] === false) ? '' : $gallery["custom_css"];
        $hover_opacity = ($gallery_options['hover_opacity'] == '' || $gallery_options['hover_opacity'] === false) ? '0.5' : $gallery_options['hover_opacity'];
        $lightbox_color = ($gallery_options['lightbox_color'] == '' || $gallery_options['lightbox_color'] === false) ? '#27AE60' : $gallery_options['lightbox_color'];
        $images_orderby = ($gallery_options['images_orderby'] == '' || $gallery_options['images_orderby'] === false) ? 'noordering' : $gallery_options['images_orderby'];
        $ays_hover_icon = ($gallery_options['hover_icon'] == '' || $gallery_options['hover_icon'] == false) ? 'search_plus' : $gallery_options['hover_icon'];
        //error_log("hover_icon hover_icon".$gallery_options['hover_icon']);
        //error_log("hover_icon ays_hover_icon".$ays_hover_icon);
        $show_title = ($gallery_options['show_title'] == '' || $gallery_options['show_title'] == false) ? '' : $gallery_options['show_title'];
        $show_title_on = ($gallery_options['show_title_on'] == '' || $gallery_options['show_title_on'] == false) ? 'gallery_image' : $gallery_options['show_title_on'];
        $show_with_date = ($gallery_options['show_with_date'] == '' || $gallery_options['show_with_date'] == false) ? '' : $gallery_options['show_with_date'];
        $images_distance = (isset($gallery_options['images_distance']) && $gallery_options['images_distance'] != '') ? absint( intval( $gallery_options['images_distance'] ) ) : '5';
        $images_request = ($gallery_options['images_request'] == '' || $gallery_options['images_request'] == false) ? 'selection' : $gallery_options['images_request'];
        $images_loading = ($gallery_options['images_loading'] == '' || $gallery_options['images_loading'] == false) ? 'load_all' : $gallery_options['images_loading'];
        
        $gallery_options['gallery_loader']  = (!isset($gallery_options['gallery_loader'])) ? "flower" : $gallery_options['gallery_loader'];        
        $ays_gallery_loader = (isset($gallery_options['gallery_loader']) && $gallery_options['gallery_loader'] == 'default') ? "flower" : $gallery_options['gallery_loader'];

        // Custom Text
        $gallery_loader_text_value = (isset($gallery_options['gallery_loader_text_value']) && $gallery_options['gallery_loader_text_value'] != '') ? stripslashes($gallery_options['gallery_loader_text_value']) : '';

        // Custom Gif
        $gallery_loader_custom_gif = (isset($gallery_options['gallery_loader_custom_gif']) && $gallery_options['gallery_loader_custom_gif'] != '') ? stripslashes($gallery_options['gallery_loader_custom_gif']) : '';

        // Gallery loader custom gif width
        $gallery_loader_custom_gif_width = (isset($gallery_options['gallery_loader_custom_gif_width']) && $gallery_options['gallery_loader_custom_gif_width'] != '') ? absint( intval( $gallery_options['gallery_loader_custom_gif_width'] ) ) : 100;

        $gallery_loader_custom_gif_width_css = '';
        if ( $gallery_loader_custom_gif_width != '' ) {
            $gallery_loader_custom_gif_width_css = 'width: '. $gallery_loader_custom_gif_width .'px; height: auto; max-width: 100%;';
        }

        // TODO check if ays_thumb_height_xxx works !
        $ays_thumb_height_mobile  = (!isset($gallery_options['thumb_height_mobile'])) ? "170" : $gallery_options['thumb_height_mobile'];
        $ays_thumb_height_desktop  = (!isset($gallery_options['thumb_height_desktop'])) ? "260" : $gallery_options['thumb_height_desktop'];

        $thumnail_title_color = (isset($gallery_options['glp_title_color']) && $gallery_options['glp_title_color'] != '') ? $gallery_options['glp_title_color'] : '#ffffff'; 

        $gallery_title_color = (isset($gallery_options['ays_gallery_title_color']) && $gallery_options['ays_gallery_title_color'] != '') ? $gallery_options['ays_gallery_title_color'] : '#000'; 

        $gallery_desc_color = (isset($gallery_options['ays_gallery_desc_color']) && $gallery_options['ays_gallery_desc_color'] != '') ? $gallery_options['ays_gallery_desc_color'] : '#000'; 
        
        //error_log("YYYY query_categories");
        //error_log("YYZZ query_categories ". $gallery_options['query_categories']);

        $images_b_radius = (!isset($gallery_options['border_radius']) ||
                            $gallery_options['border_radius'] == '' ||
                            $gallery_options['border_radius'] == false) ? '0' : $gallery_options['border_radius'];
        $images_hover_icon_size = (!isset($gallery_options['hover_icon_size']) ||
                            $gallery_options['hover_icon_size'] == '' ||
                            $gallery_options['hover_icon_size'] == '0') ? '20' : $gallery_options['hover_icon_size'];
        $thumbnail_title_size = (!isset($gallery_options['thumbnail_title_size']) ||
                            $gallery_options['thumbnail_title_size'] == '' ||
                            $gallery_options['thumbnail_title_size'] == '0') ? '12' : $gallery_options['thumbnail_title_size'];
        $ays_images_border = (!isset($gallery_options['images_border']) ||
                            $gallery_options['images_border'] == false) ? '' : $gallery_options['images_border'];

        $ays_images_border_width    = (!isset($gallery_options['images_border_width']) ||
                                        $gallery_options['images_border_width'] == false) ? '1' : $gallery_options['images_border_width'];
        $ays_images_border_style    = (!isset($gallery_options['images_border_style']) ||
                                        $gallery_options['images_border_style'] == false) ? 'solid' : $gallery_options['images_border_style'];
        $ays_images_border_color    = (!isset($gallery_options['images_border_color']) ||
                                        $gallery_options['images_border_color'] == false) ? '#000000' : $gallery_options['images_border_color'];
        
        // $glp_lightbox_counter = (!isset($gal_lightbox_options['lightbox_counter']) ||
        //                             $gal_lightbox_options['lightbox_counter'] == false) ? "true" : $gal_lightbox_options['lightbox_counter'];
        // $glp_lightbox_autoplay = (!isset($gal_lightbox_options['lightbox_autoplay']) ||
        //                             $gal_lightbox_options['lightbox_autoplay'] == false) ? "true" : $gal_lightbox_options['lightbox_autoplay'];
        // $glp_lightbox_pause  = (!isset($gal_lightbox_options['lb_pause']) ||
        //                             $gal_lightbox_options['lb_pause'] == '') ? "5000" : $gal_lightbox_options['lb_pause'];

        $vignette_display = (!isset($gallery_options['vignette_display']) ||
            $gallery_options['vignette_display'] == '' ||
            $gallery_options['vignette_display'] == false) ? 'permanent' : $gallery_options['vignette_display'];
        // error_log("ays_get_gallery_content vignette_display=".$vignette_display);
      
        //Gallery image position
        $gallery_img_positions = (!isset($gallery_options['gallery_img_position']) ||
                                    $gallery_options['gallery_img_position'] === null ) ? "center-center" : $gallery_options['gallery_img_position'];

        $gallery_img_position = (isset($gallery_options['gallery_img_position_l']) && isset($gallery_options['gallery_img_position_r'])) ? $gallery_options['gallery_img_position_l'].'-'.$gallery_options['gallery_img_position_r'] : $gallery_img_positions;

        $gallery_img_position = str_replace("-"," ", $gallery_img_position);

        $ays_ordering_asc_desc = (isset($gallery_options['ordering_asc_desc']) && $gallery_options['ordering_asc_desc'] != '') ? $gallery_options['ordering_asc_desc'] : 'ascending';
        
        $ays_show_caption = true;
        if(isset($gal_lightbox_options['lb_show_caption'])){
            switch($gal_lightbox_options['lb_show_caption']){
                case "true":
                    $ays_show_caption = true;
                break;
                case "false":
                    $ays_show_caption = false;
                break;
            }
        }

        // All images text
        $gpg_all_images_text = (isset($settings_options['gpg_all_images_text']) && $settings_options['gpg_all_images_text'] != '') ?  stripslashes( esc_attr($settings_options['gpg_all_images_text'])) : 'All';        
        
        $show_filter_cat = (!isset($gallery_options['ays_filter_cat'])) ? 'off' : $gallery_options['ays_filter_cat'];

        $show_gal_title = (!isset($gallery_options['show_gal_title'])) ? 'off' : $gallery_options['show_gal_title'];
        //$show_gal_desc = (!isset($gallery_options['show_gal_desc'])) ? 'off' : $gallery_options['show_gal_desc'];

        $lightbox_color_rgba = $this->hex2rgba($lightbox_color, 0.5);
        
        $columns            = (!isset($gallery_options['columns_count'])) ? 3 : $gallery_options['columns_count'];
        $view               = $gallery_options['view_type'];
        // $images             = explode( "***", $gallery["images"]        );
        // $image_descs        = explode( "***", $gallery["images_descs"]  );
        // $image_alts         = explode( "***", $gallery["images_alts"]   );
        //$image_urls         = explode( "***", $gallery["images_urls"]   );
        $image_ids = array();
        // error_log("images: ".print_r($images, true));
        // error_log("image_descs: ".print_r($image_descs, true));
        // error_log("image_alts: ".print_r($image_alts, true));
        //error_log("image_ids: ".print_r($image_ids, true));
        
        // get the images knowing the categories
        if ($images_request == "query" ) {
        
            //error_log("query_categories: ".$gallery_options['query_categories
            $query = "SELECT post_id FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = 'category' AND `meta_value` = '".$gallery_options['query_categories']."'";
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            //error_log("Image ids for the given category: ".print_r($result, true));
            foreach ($result as $item) {
                array_push($image_ids, $item['post_id']);
            }
        }
        else {
            $image_ids = explode( "***", $gallery["images_ids"]);
        }
        // error_log("Image ids : ".print_r($image_ids, true));
        $images = array();
        $image_titles = array();
        $image_descs = array();
        $image_alts = array();
        $image_dates = array();

        // Prepare $image_titles, $image_descs, $images and $image_dates
        for( $iid = 0; $iid < count($image_ids); $iid++ ){
            //error_log("image_ids[".$iid."]: ".$image_ids[$iid]);
            $query = "SELECT post_title,post_content,post_excerpt,guid,post_date FROM `".$wpdb->prefix."posts` WHERE `id` = '".$image_ids[$iid]."'";
            //error_log("query: ".$query);
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            //error_log("Images result: ".print_r($result, true));
            
            if (count($result) > 0) {
                //error_log("result : ".print_r($result , true));
                array_push($image_titles, $result[0]['post_title']);
                array_push($image_descs, $result[0]['post_content']);
                //array_push($image_urls2, $result[0]['guid']);
                array_push($images, $result[0]['guid']);
                array_push($image_dates, $result[0]['post_date']);

                $query = "SELECT meta_value FROM `".$wpdb->prefix."postmeta` WHERE `meta_key` = '_wp_attachment_image_alt' AND `post_id` = '".$image_ids[$iid]."'";
                //error_log("query: ".$query);
                $result =  $wpdb->get_results( $query, "ARRAY_A" );
                //error_log("Meta result: ".print_r($result, true));
                
                if (count($result) > 0) {
                    //error_log("result : ".print_r($result , true));
                    array_push($image_alts, $result[0]['meta_value']);
                }
                else {
                    array_push($image_alts, '');
                }
            }
        }

        // Prepare $image_latitudes, $image_longitudes, and $image_countries
        $image_latitudes = array();
        $image_longitudes = array();
        $image_countries = array();

        for( $iid = 0; $iid < count($image_ids); $iid++ ){
            //error_log("image_ids[iid]: ".$image_ids[$iid]);
            $query = "SELECT meta_key, meta_value FROM `".$wpdb->prefix."postmeta` WHERE `post_id` = '".$image_ids[$iid]."'";
            //error_log("query: ".$query);
            $result =  $wpdb->get_results( $query, "ARRAY_A" );
            $options = null;
            $longitude = 0;
            $latitude = 0;
            $vignette = '';
            if (count($result) > 0) {
                // error_log("result : ".print_r($result , true));
                $longitude = $this->findValueByKey($result, 'longitude');
                $latitude = $this->findValueByKey($result, 'latitude');
                $vignette = $this->findValueByKey($result, 'vignette');

                $options = $this->checkGeolocation($latitude, $longitude, $vignette);
                if ($options != null) {
                    array_push($image_latitudes, $latitude);
                    array_push($image_longitudes, $longitude);
                    array_push($image_countries, $options);
                }
            }
            if ($options == null) {
                array_push($image_latitudes, 0);
                array_push($image_longitudes, 0);
                array_push($image_countries, null);
            }
        }
        // error_log("image_latitudes: ".print_r($image_latitudes, true));
        // error_log("image_longitudes: ".print_r($image_longitudes, true));
        // error_log("image_countries: ".print_r($image_countries, true));

        //TODO tests when there is not title or no description,...

        $images_categories  = isset($gallery["categories_id"]) && $show_filter_cat == 'on' ? explode( "***", $gallery["categories_id"] ) : array();        
        
        $gallery_options['enable_light_box'] = isset($gallery_options['enable_light_box']) ? $gallery_options['enable_light_box'] : "off";

        $disable_lightbox = (isset($gallery_options['enable_light_box']) && $gallery_options['enable_light_box'] == "off" || $gallery_options['enable_light_box'] == "") ? true : false;

        //$link_on_whole_img = (isset($gallery_options['link_on_whole_img']) && $gallery_options['link_on_whole_img'] == "on") ? true : false;

        //$ays_width = $width == 0 ? '100%' : $width.'px'; 
        $ays_width = '100%';

        if($columns == null || $columns == 0){
            $columns = 3;
        }
        switch($view){
            case 'grid':
                $column_width = (100 / $columns); // TODO take into account border size !!
                break;
            case 'mosaic':
                $column_width = 100 / $columns;
                break;
            case 'masonry':
                $column_width = 100 / $columns;
                break;
            default:
                $column_width = 100 / $columns;
                break;
        }

        $html_hover_icon = $this->get_over_icon($ays_hover_icon);

        if ($ays_gallery_loader == 'text') {
            $ays_images_loader = "<p class='ays-loader-content'>". $gallery_loader_text_value ."</p>";
        }elseif ($ays_gallery_loader == 'custom_gif') {
            if ($gallery_loader_custom_gif != '') {
                $ays_images_loader = "                
                    <img src='". $gallery_loader_custom_gif ."' style='". $gallery_loader_custom_gif_width_css ."'>";
            }else{
                $ays_images_loader = "<img src='".GLP_PUBLIC_URL."images/flower.svg'>";                
            }
        }else{
            $ays_images_loader = "<img src='".GLP_PUBLIC_URL."images/$ays_gallery_loader.svg'>";
        }

        if ((array_search('', $image_dates) !== 0) && (count($images) != count($image_dates))) {
            $dates_key = array_search('', $image_dates);
            unset($image_dates[$dates_key]);
            unset($image_descs[$dates_key]);
            unset($image_titles[$dates_key]);
            unset($image_alts[$dates_key]);
            //unset($image_urls[$dates_key]);
            unset($image_ids[$dates_key]);
        }   

        $images_categories = array_pad($images_categories, count($image_titles), "");
        $ordering_asc_desc = SORT_ASC;
        if ($ays_ordering_asc_desc == 'descending') {
            $ordering_asc_desc = SORT_DESC;
        }
        switch($images_orderby){
            case 'title':
                array_multisort($image_titles, $ordering_asc_desc, SORT_STRING, $images, $image_descs, $image_alts, $image_dates, $images_categories, $image_latitudes, $image_longitudes, $image_countries);
                break;
            case 'date':
                array_multisort($image_dates, $ordering_asc_desc, SORT_NUMERIC, $images, $image_titles, $image_descs, $image_alts, $images_categories, $image_latitudes, $image_longitudes, $image_countries);
                break;
            case 'random':
                $images_indexes = range(0, count($images)-1);
                shuffle($images_indexes);
                array_multisort($images_indexes, $images, $image_titles, $image_descs, $image_alts, $image_dates, $images_categories, $image_latitudes, $image_longitudes, $image_countries);
                break;
            default:
                if ($ays_ordering_asc_desc == 'descending') {
                    $images = array_reverse($images, true);         
                }
                break;
        }

        $images_new     = array();
        $img_cat_id     = array();
        $imgs_cat_id     = array();
        $this_site_path = trim(get_site_url(), "https:");
        // TODO get full size for lightbox
        $image_sizes = "medium_large"; // medium_large for gallery
        error_log("public image_sizes=".$image_sizes);
        foreach($images as $i => $img){
            if(strpos(trim($img, "https:"), $this_site_path) !== false){ 
                $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `post_type` = 'attachment' AND `guid` = '".$img."'";
                $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                if(!empty($result_img)){
                    $url_img = wp_get_attachment_image_src($result_img[0]['ID'], $image_sizes);
                    if($url_img === false){
                       $images_new[] = $img;
                    }else{
                       $images_new[] = $url_img[0];
                    }

                    // TODO test content of metada
                    // $metadata = wp_get_attachment_metadata($result_img[0]['ID']);
                    // error_log("image metadata=".print_r($metadata, true));

                }else{
                    $images_new[] = $img;
                }                
            }else{
                $images_new[] = $img;
            }
        }
        $images_count = count($images);
        
        $user_first_name        = '';
        $user_last_name         = '';
        $user_display_name      = '';
        $user_email             = '';
        $user_nickname          = '';
        $user_wordpress_roles   = '';
        $user_ip_address        = '';
        $user_id = get_current_user_id();
        
        if($user_id != 0){
            $usermeta = get_user_meta( $user_id );
            if($usermeta !== null){
                $user_first_name = (isset($usermeta['first_name'][0]) && sanitize_text_field( $usermeta['first_name'][0] != '') ) ? sanitize_text_field( $usermeta['first_name'][0] ) : '';
                $user_last_name  = (isset($usermeta['last_name'][0]) && sanitize_text_field( $usermeta['last_name'][0] != '') ) ? sanitize_text_field( $usermeta['last_name'][0] ) : '';
                $user_nickname   = (isset($usermeta['nickname'][0]) && sanitize_text_field( $usermeta['nickname'][0] != '') ) ? sanitize_text_field( $usermeta['nickname'][0] ) : '';
            }

            $current_user_data = get_userdata( $user_id );
            if ( ! is_null( $current_user_data ) && $current_user_data ) {
                $user_display_name = ( isset( $current_user_data->data->display_name ) && $current_user_data->data->display_name != '' ) ? sanitize_text_field( $current_user_data->data->display_name ) : "";
                $user_email = ( isset( $current_user_data->data->user_email ) && $current_user_data->data->user_email != '' ) ? sanitize_text_field( $current_user_data->data->user_email ) : "";

                $user_wordpress_roles = ( isset( $current_user_data->roles ) && ! empty( $current_user_data->roles ) ) ? $current_user_data->roles : "";
                $user_ip_address = $this->ays_gallery_get_user_ip();

                if ( !empty( $user_wordpress_roles ) && $user_wordpress_roles != "" ) {
                    if ( is_array( $user_wordpress_roles ) ) {
                        $user_wordpress_roles = implode(",", $user_wordpress_roles);
                    }
                }
            }
        }else{
            $user_id = '';
        }
        $message_data = array(                    
                'user_first_name'      => $user_first_name,
                'user_last_name'       => $user_last_name,
                'user_display_name'    => $user_display_name,
                'user_nickname'        => $user_nickname,
                'user_wordpress_email' => $user_email,
                'user_wordpress_roles' => $user_wordpress_roles,
                'user_ip_address'      => $user_ip_address,
                'user_id'              => $user_id,
                'gallery_id'           => $id,
            );

        $description = $this->ays_gallery_replace_message_variables($description, $message_data);
        
        if($show_gal_title == "on"){
            $show_gallery_title = "<h2 class='ays_gallery_title'>" . stripslashes($title) . "</h2>";
        }else{
            $show_gallery_title = "";
        }
        // if($show_gal_desc == "on"){
        //     $show_gallery_desc = "<h4 class='ays_gallery_description'>" . stripslashes($description) . "</h4>";
        // }else{
        //     $show_gallery_desc = "";
        // }
        if($show_gal_title != "on"){// && $show_gal_desc != "on"){
            $show_gallery_head = "";
        }else{
            $show_gallery_head = "<div class='ays_gallery_header'>
                                    $show_gallery_title
                                </div>";
        }
        if($ays_images_border === "on"){
            $show_images_with_border = "border: ".$ays_images_border_width."px ".$ays_images_border_style." ".$ays_images_border_color.";";
        }else{
            $show_images_with_border = "border: none";
        }
        $gallery_view = "<div class='ays_gallery_container_".$id."' style='width: ".$ays_width."'>".$show_gallery_head;
  
        
        //BUILD HTML for all images
        ///////////////////////////////
        $gallery_view .= $this->ays_add_images($view, $images_new, $show_title, $image_titles, $image_dates, 
            $show_with_date, $image_descs, $image_alts, $image_ids, $id, $images_loading, $disable_lightbox, 
            $show_title_on, $html_hover_icon, $ays_show_caption, $ays_images_loader, $images,
            $image_countries, $image_latitudes, $image_longitudes, $images_categories, $images_distance,
            $column_width, $vignette_display);
        //error_log("gallery_view=".$gallery_view);
        
        ///////////////////////////////


        $title_pos = (isset($gallery_options['title_position']) && $gallery_options['title_position'] !== null) ? $gallery_options['title_position'] : 'bottom';
        

        if ($title_pos == 'top') {
            $title_pos_bottom = 'bottom: unset;';
            $title_pos_top = 'top: 0;';
        }else{
            $title_pos_top = 'top: unset;';
            $title_pos_bottom = 'bottom: 0;';
        }

        $hover_color = (isset($gallery_options['hover_color']) && $gallery_options['hover_color'] !== null) ? $gallery_options['hover_color'] : '#000000';

        $hover_scale = (isset($gallery_options['hover_scale']) && $gallery_options['hover_scale'] !== null) ? $gallery_options['hover_scale'] : 'no';

        //Hover scale animation Speed
        $hover_scale_animation_speed = (isset($gallery_options['hover_scale_animation_speed']) && $gallery_options['hover_scale_animation_speed'] !== '') ? abs($gallery_options['hover_scale_animation_speed']) : 1;

        //Hover animation Speed        
        $hover_animation_speed = (isset($gallery_options['hover_animation_speed']) && $gallery_options['hover_animation_speed'] !== '') ? abs($gallery_options['hover_animation_speed']) : 0.5;

        //$filter_thubnail = (isset($gallery_options['filter_thubnail']) && $gallery_options['filter_thubnail'] == "on") ? true : false;
        $filter_thubnail_opt = 'none';
        $filter_lightbox_opt = (isset($gal_lightbox_options['filter_lightbox_opt'])) ? $gal_lightbox_options['filter_lightbox_opt'] : 'none';        

        $gpg_filter_image = 'none';

        switch ($filter_lightbox_opt) {
            case 'blur':
                $gpg_filter_lightbox_image = 'blur(3px)';
                break;
            case 'brightness':
                $gpg_filter_lightbox_image = 'brightness(200%)';
                break;
            case 'contrast':
                $gpg_filter_lightbox_image = 'contrast(200%)';
                break;
            case 'grayscale':
                $gpg_filter_lightbox_image = 'grayscale(100%)';
                break;
            case 'hue_rotate':
                $gpg_filter_lightbox_image = 'hue-rotate(90deg)';
                break;
            case 'invert':
                $gpg_filter_lightbox_image = 'invert(100%)';
                break;
            case 'saturate':
                $gpg_filter_lightbox_image = 'saturate(8)';
                break;
            case 'sepia':
                $gpg_filter_lightbox_image = 'sepia(100%)';
                break;                
            default:
                $gpg_filter_lightbox_image = 'none';
                    break;
        }

        if ($hover_scale == "yes") {
            $images_hover_scale = "div.ays_grid_row div.ays_grid_column_".$id.":hover, div.ays_masonry_grid-item.ays_masonry_item_".$id.":hover, div.ays_mosaic_column_item_".$id.":hover {
                    /* filter: drop-shadow(4px 4px 4px black);*/
                    box-shadow: 4px 4px 4px black;                 
                    transform: scale(1.05);
                    transition-duration: ".$hover_scale_animation_speed."s;
                    z-index: 10;}";
        }else{
            $images_hover_scale = "";
        }

        //$gpg_resp_width = isset($gallery_options['resp_width']) && $gallery_options['resp_width'] == "on" ? true :false;
        $gpg_resp_width = false;

        if ($gpg_resp_width) {
            $ays_thumb_height_mobile = "";
            $ays_thumb_height_desktop = "";
        }
        $gallery_options['enable_rtl_direction']  = (isset($gallery_options['enable_rtl_direction']) && $gallery_options['enable_rtl_direction'] == 'on' ) ? $gallery_options['enable_rtl_direction'] : 'off';
        $enable_rtl_direction  = (isset($gallery_options['enable_rtl_direction']) && $gallery_options['enable_rtl_direction'] == 'on' ) ? true : false;

        $rtl_style = '';
        $search_img = ''; // TODO remove all search
        if($enable_rtl_direction){
            $rtl_style = '
                text-align: right;
                direction: rtl;
            ';
            $search_img = '
                justify-content: flex-start;
            ';
        }else{
            $rtl_style  = '';
            $search_img = '';
        }

        $date_on = '';
        $gallery_view .= "
        </div>
            <style>                
                .lg-outer, .lg-backdrop {
                    z-index: 999999999999 !important;
                }
                .ays_gallery_container_".$id." {
                    max-width: 100%;
                    transition: .5s ease-in-out;
                    animation-duration: .5s;
                    position: static!important;
                    ".$rtl_style.";
                }
                .mosaic_".$id." {
                    padding: 6px;
                    max-width: 100%;
                }

                .mosaic_".$id." a {
                    width: 100%;
                    height: 100%;
                }

                .ays_masonry_item_".$id."{
                    width: calc(".($column_width - 0.001)."% - ".($images_distance)."px);
                    margin-bottom: ".$images_distance."px !important;
                    position: relative;
                }                

                .ays_image_title {                    
                    ".$title_pos_top."
                    ".$title_pos_bottom."
                    ".$date_on.";
                }              

                .ays_gallery_container_".$id." .ays_gallery_header h2.ays_gallery_title{
                    color:".$gallery_title_color."
                }               

                .ays_gallery_container_".$id." .ays_gallery_header h4.ays_gallery_description{
                    color:".$gallery_desc_color."
                }           

                .ays_image_title>span {
                    font-size:".$thumbnail_title_size."px;
                    color:".$thumnail_title_color."
                }

                .ays_masonry_item_".$id." a, .ays_masonry_item_".$id.":hover,
                .mosaic_".$id." a, .mosaic_".$id.":hover {
                   box-shadow: none;
                }
                .ays_mosaic_column_item_".$id." {
                    font-size: 0;
                    margin-bottom: 0;
                    margin-right: 0;
                    overflow: hidden;
                    perspective: 200px;
                    box-sizing: border-box;
                }
                
                .ays_masonry_item_".$id." img,
                .ays_mosaic_column_item_".$id." img {
                    width: 100%;
                    max-width: 100%;
                    height: 100%;
                    margin: 0 auto;
                    object-fit: cover;
                }

                .ays_grid_column_".$id.":nth-child(".$columns."n) {
                    margin-right:0px;
                }

                div.ays_grid_row div.ays_grid_column_".$id." {
                    height: {$ays_thumb_height_desktop}px;
                    min-height: {$ays_thumb_height_desktop}px;
                    background-size: cover;
                    margin-bottom: ". ( $images_distance/2 ) ."px;
                    margin-right: ". ( $images_distance/2 ) ."px;
                    background-position: center;
                    position: relative;
                    z-index: 1;
                    overflow: hidden;
                    transition: .5s ease-in-out;
                    perspective: 200px;
                    line-height: 0;
                }
                .lazy {
                    transform: scale(0);
                    transition: all 1s;
                }
                .lazyloaded {
                    transform: scale(1);
                }
                .ays_gallery_body_".$id." .ays_gallery_image, .ays_masonry_grid-item img, 
                .ays_mosaic_column_item_".$id." img, 
                .ays_masonry_item_".$id." img{
                    filter: ".$gpg_filter_image.";
                }

                .glp_lightbox_".$id." .lg-image, .glp_lightbox_".$id." .lg-thumb-item img{
                    filter: ".$gpg_filter_lightbox_image.";
                }
                
                ".$images_hover_scale."
                
                div.ays_masonry_grid div.ays_masonry_item_".$id.",
                div.ays_grid_row div.ays_grid_column_".$id." {
                    $show_images_with_border
                }

                @media screen and (max-width: 768px){
                    div.ays_grid_row div.ays_grid_column_".$id." {
                        height: {$ays_thumb_height_mobile}px;
                        min-height: {$ays_thumb_height_mobile}px;
                    }
                }

                div.ays_grid_row div.ays_grid_column_".$id." > img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: ".$gallery_img_position.";
                }

                div.ays_masonry_grid div.ays_masonry_item_".$id." a,
                div.ays_grid_row div.ays_grid_column_".$id." a {
                    display: block;
                    z-index: 3;
                    box-shadow: none;
                }
                
                div.ays_grid_row div.ays_grid_column_".$id." a:hover{
                    box-shadow: none;
                }
                
                div.ays_masonry_grid div.ays_masonry_item_".$id.":hover,
                div.mosaic_".$id." div.ays_mosaic_column_item_".$id.":hover,
                div.ays_grid_row div.ays_grid_column_".$id.":hover{
                    cursor: pointer;
                }

                div.mosaic_".$id." .ays_mosaic_column_item_".$id." > div.ays_hover_mask {
                    font-size: 40px !important;
                }
                div.ays_masonry_grid div.ays_masonry_item_".$id." div.ays_hover_mask i.glp_fa,
                div.ays_masonry_grid div.ays_masonry_item_".$id." div.ays_hover_mask .glp_fa,
                div.ays_grid_row div.ays_grid_column_".$id." div.ays_hover_mask i.glp_fa,
                div.ays_grid_row div.ays_grid_column_".$id." div.ays_hover_mask .glp_fa,
                .mosaic_".$id." .ays_mosaic_column_item_".$id." div.ays_hover_mask i.glp_fa,
                .mosaic_".$id." .ays_mosaic_column_item_".$id." div.ays_hover_mask .glp_fa {
                    font-size: {$images_hover_icon_size}px !important;
                    opacity: 1 !important;
                }

                .ays_mosaic_column_".$id." {
                    width: 25%;
                    margin-right: 0;
                }
                div.ays_masonry_item_".$id.":hover > div.ays_hover_mask,
                div.mosaic_".$id." .ays_mosaic_column_item_".$id.":hover > div.ays_hover_mask,
                div.ays_grid_row div.ays_grid_column_".$id.":hover > div.ays_hover_mask {
                    opacity: 1 !important;
                    transition: all .5s;
                }
                
                div.mosaic_".$id." div.ays_mosaic_column_item_".$id.",
                div.ays_masonry_grid div.ays_masonry_item_".$id.",
                div.ays_grid_row div.ays_grid_column_".$id." {
                    border-radius: {$images_b_radius}px;
                    /*transition: transform 1s; too much transition*/
                }

                div.ays_masonry_item_".$id." div.ays_hover_mask,
                div.mosaic_".$id." div.ays_mosaic_column_item_".$id." div.ays_hover_mask,
                div.ays_grid_row div.ays_grid_column_".$id." div.ays_hover_mask {
                    background: ".$this->hex2rgba($hover_color, $hover_opacity).";
                    animation-duration: ".$hover_animation_speed."s !important;
                }
                
                div.ays_masonry_grid div.ays_masonry_item_".$id." div.ays_hover_mask .ays_image_url,
                div.mosaic_".$id." div.ays_mosaic_column_item_".$id." div.ays_hover_mask .ays_image_url,
                div.ays_grid_row div.ays_grid_column_".$id." div.ays_hover_mask .ays_image_url {
                    position: relative;
                    z-index: 10000;
                    padding: 0;
                    margin: auto;
                    background-color: transparent;
                    margin-left: 10px;
                    outline: 0;
                    border: none;
                    box-shadow: none;
                    cursor: pointer;
                }
                
                                
                div.ays_masonry_grid div.ays_masonry_item_".$id." div.ays_hover_mask .ays_image_url:hover *,
                div.mosaic_".$id." div.ays_mosaic_column_item_".$id." div.ays_hover_mask .ays_image_url:hover *,
                div.ays_grid_row div.ays_grid_column_".$id." div.ays_hover_mask .ays_image_url:hover * {
                    color: #ccc;
                }
                

                div.mosaic_".$id." a,
                div.ays_masonry_item_".$id." a,
                div.ays_grid_row div.ays_grid_column_".$id." a {
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    top: 0;
                }
                
                div.ays_masonry_grid div.ays_masonry_item_".$id." .ays_image_title,
                div.mosaic_".$id." .ays_image_title,
                div.ays_grid_row div.ays_grid_column_".$id." .ays_image_title                
                {
                    background-color: ".$lightbox_color.";
                    background-color: ".$lightbox_color_rgba.";
                    z-index: 999999;
                }

                .ays_gallery_search_img{
                    ".$search_img.";
                }

                ".stripslashes( htmlspecialchars_decode($custom_css))."
            </style>";
        $gallery_view = trim(str_replace(array("\n", "\r"), '', $gallery_view));
        $gallery_view = trim(preg_replace('/\s+/', ' ', $gallery_view));
        return $gallery_view;
    
    } // end ays_get_gallery_content()

    private function array_split($array, $pieces) {
        if ($pieces < 2)
            return array($array);
        $newCount = ceil(count($array)/$pieces);
        $a = array_slice($array, 0, $newCount);
        $b = $this->array_split(array_slice($array, $newCount), $pieces-1);
        return array_merge(array($a),$b);
    }
    
    private function hex2rgba( $color, $opacity = false ) {

        $default = 'rgba(39, 174, 96, 0.5)';
        /**
         * Return default if no color provided
         */
        if( empty( $color ) ) {
            return $default;
        }
        /**
         * Sanitize $color if "#" is provided
         */
        if ( $color[0] == '#' ) {
            $color = substr( $color, 1 );
        }

        if ( substr( $color, 0, 4 ) == 'rgba' ) {
            return $color;
        }

        /**
         * Check if color has 6 or 3 characters and get values
         */
        if ( strlen($color) == 6 ) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        /**
         * [$rgb description]
         * @var array
         */
        $rgb =  array_map( 'hexdec', $hex );
        /**
         * Check if opacity is set(rgba or rgb)
         */
        if( $opacity ) {
            if( abs( $opacity ) > 1 )
                $opacity = 1.0;
                $output = 'rgba( ' . implode( "," ,$rgb ) . ',' . $opacity . ' )';
        } else {
            $output = 'rgb( ' . implode( "," , $rgb ) . ' )';
        }
        /**
         * Return rgb(a) color string
         */
        return $output;
    }

    private static function ays_gallery_get_user_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    function ays_gallery_wp_get_attachment_image_attributes($attr) {
        if ( isset( $attr ) && !is_array( $attr ) ) {
            $attr = '';
        }

        return $attr;
    }
}
