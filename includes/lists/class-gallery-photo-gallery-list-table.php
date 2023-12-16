<?php
ob_start();
class Galleries_List_Table extends WP_List_Table{
    private $plugin_name;
    private $title_length;
    /** Class constructor */
    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->title_length = Gallery_Photo_Gallery_Admin::get_gpg_listtables_title_length('galleries');
        parent::__construct( array(
            "singular" => __( "Gallery", $this->plugin_name ), //singular name of the listed records
            "plural"   => __( "Galleries", $this->plugin_name ), //plural name of the listed records
            "ajax"     => false //does this table support ajax?
        ) );
        add_action( "admin_notices", array( $this, "gallery_notices" ) );

    }


    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_galleries( $per_page = 5, $page_number = 1 , $search = '' ) {

        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_gallery";

        $where = array();

        if( $search != '' ){
            $where[] = $search;
        }

        if( ! empty($where) ){
            $sql .= " WHERE " . implode( " AND ", $where );
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {

            $order_by  = ( isset( $_REQUEST['orderby'] ) && sanitize_text_field( $_REQUEST['orderby'] ) != '' ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'ordering';
            $order_by .= ( ! empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) == 'asc' ) ? ' ASC' : ' DESC';

            $sql_orderby = sanitize_sql_orderby($order_by);

            if ( $sql_orderby ) {
                $sql .= ' ORDER BY ' . $sql_orderby;
            } else {
                $sql .= ' ORDER BY id DESC';
            }
        }else{
            $sql .= ' ORDER BY id DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= " OFFSET " . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, "ARRAY_A" );

        return $result;
    }

    public function get_gallery_by_id( $id ){
        global $wpdb;

        $gallery_table = esc_sql($wpdb->prefix . "ays_gallery");

        $id = absint( sanitize_text_field( $id ));
        $sql = "SELECT * FROM ".$gallery_table." WHERE id = %d";

        $result = $wpdb->get_row(
                    $wpdb->prepare( $sql, $id)
                  , "ARRAY_A");

        return $result;
    }

    public function add_or_edit_gallery($data){
        global $wpdb;
        $gallery_table = $wpdb->prefix . "ays_gallery";
        error_log("add_or_edit_gallery() IN data: ".print_r($data, true));//TODO
        if( isset($data["ays_gallery_action"]) && wp_verify_nonce( $data["ays_gallery_action"],"ays_gallery_action" ) ) {
            
            error_log("add_or_edit_gallery() IN data: ".print_r($data, true));//TODO    
            
            // List of images
            if ($data["ays-image-path"] != NULL) {
                error_log("add_or_edit_gallery() images selected ! ");
                $image_paths            = (isset($data["ays-image-path"]) && $data["ays-image-path"] != '') ? sanitize_text_field( implode( "***", array_filter($data["ays-image-path"] )) ) : '';
                $image_ids              = (isset($data["ays-image-id"]) && $data["ays-image-id"] != '') ? sanitize_text_field( implode( "***", array_filter($data["ays-image-id"] )) ) : '';
                //error_log("TODO image id:".$image_ids);
                // $image_titles           = (isset($data["ays-image-title"]) && $data["ays-image-title"] != '') ? stripslashes(sanitize_text_field( implode( "***", $data["ays-image-title"] ) ) ) : '';
                //$image_alts             = (isset($data["ays-image-alt"]) && $data["ays-image-alt"] != '') ? stripslashes (sanitize_text_field( implode( "***", $data["ays-image-alt"] ) ) ) : '';
                //$image_descriptions     = (isset($data["ays-image-description"]) && $data["ays-image-description"] != '') ? stripslashes(sanitize_text_field( implode( "***", $data["ays-image-description"] ) ) ) : '';
                // TODO: image_external_urls can be removed
                //$image_external_urls    = (isset($data["ays-image-url"]) && $data["ays-image-url"] != '') ? sanitize_text_field( implode( "***", $data["ays-image-url"] ) ) : '';
                //$images_dates           = (isset($data['ays-image-date']) &&  $data['ays-image-date'] != '') ? sanitize_text_field( implode( "***", $data['ays-image-date'] ) ) : '';
                $image_categories       = (isset($data['ays_gallery_category']) && $data['ays_gallery_category'] != '') ? sanitize_text_field( implode('***', $data['ays_gallery_category']) ) : '';
            }
            else {
                error_log("add_or_edit_gallery() NO images ! ");
                $image_paths            = '';
                $image_ids              = '';
                //$image_titles           = '';
                //$image_alts             = '';
                //$image_descriptions     = '';
                //$image_external_urls    = ''; //TODO remove
                $images_dates           = '';
                $image_categories       = '';
            }

            $id                     = ( $data["id"] != NULL ) ? absint( intval( $data["id"] ) ) : null;
            
            // Gallery settings
            error_log("TODO gallery id:".$id);
            $title                  = (isset($data["gallery_title"]) && $data["gallery_title"] != '') ? stripslashes(sanitize_text_field( $data["gallery_title"] )) : '';
            $description            = !isset($data['gallery_description']) ? '' : wp_kses_post( $data['gallery_description'] );
            $width                  = (isset($data['gallery_width']) && $data['gallery_width'] != '') ? wp_unslash(sanitize_text_field( $data['gallery_width'] )) : '';
            $height                 = 0;
            $view_type              = isset($data['ays-view-type']) && $data['ays-view-type'] != '' ? sanitize_text_field( $data['ays-view-type'] ) : '';
            $columns_count          = (isset($data['ays-columns-count']) && $data['ays-columns-count'] != '') ? absint( intval( $data['ays-columns-count'] ) ) : '';
            $images_distance        = (isset($data['ays-gpg-images-distance']) && $data['ays-gpg-images-distance'] != '') ? absint( intval( $data['ays-gpg-images-distance'] ) ) : '5';
            $hover_effect           = (isset($data['ays_hover_simple']) && $data['ays_hover_simple'] != '') ? sanitize_text_field( $data['ays_hover_simple'] ) : '';
            $img_load_effect        = (isset($data['ays_img_load_effect']) && $data['ays_img_load_effect'] != '') ? sanitize_text_field( $data['ays_img_load_effect'] ) : '';
            $hover_opacity          = (isset($data['ays-gpg-image-hover-opacity']) && $data['ays-gpg-image-hover-opacity'] != '') ? sanitize_text_field( $data['ays-gpg-image-hover-opacity'] ) : '';
            $hover_color            = (isset($data['ays-gpg-hover-color']) && $data['ays-gpg-hover-color'] != '') ? sanitize_text_field( $data['ays-gpg-hover-color'] ) : '';
            $hover_icon             = (isset($data['ays-gpg-image-hover-icon']) && $data['ays-gpg-image-hover-icon'] != '') ? sanitize_text_field( $data['ays-gpg-image-hover-icon'] ) : '';
            $image_sizes            = (isset($data['ays_image_sizes']) && $data['ays_image_sizes'] != '') ? sanitize_text_field( $data['ays_image_sizes'] ) : '';
            $custom_css             = isset($data['gallery_custom_css']) && $data['gallery_custom_css'] != '' ? stripslashes( esc_attr($data['gallery_custom_css'])) : '';
            $lightbox_color         = (isset($data['ays-gpg-lightbox-color']) && $data['ays-gpg-lightbox-color'] != '') ? wp_unslash(sanitize_text_field( $data['ays-gpg-lightbox-color'] )) : '';
            $images_orderby         = (isset($data['ays_images_ordering']) && $data['ays_images_ordering'] != '') ? wp_unslash(sanitize_text_field( $data['ays_images_ordering'] )) : '';
            $show_title             =  wp_unslash(sanitize_text_field( isset($data['ays_gpg_show_title']) ? $data['ays_gpg_show_title'] : '' ));
            $show_title_on          = (isset($data['ays_gpg_show_title_on']) && $data['ays_gpg_show_title_on'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_show_title_on'] )) : '';
            $title_position         = (isset($data['image_title_position']) && $data['image_title_position'] != '')? wp_unslash(sanitize_text_field( $data['image_title_position'] )) : '';
            $show_with_date         = wp_unslash(sanitize_text_field( isset($data['ays_gpg_show_with_date']) ? $data['ays_gpg_show_with_date'] : '' ));
            $ays_images_loading     = (isset($data['ays_images_loading']) && $data['ays_images_loading'] != '') ? sanitize_text_field( $data['ays_images_loading'] ) : 'all_loaded';
            $gpg_redirect_url_tab   = (isset($data['gpg_redirect_url_tab']) && $data['gpg_redirect_url_tab'] != '') ? sanitize_text_field( $data['gpg_redirect_url_tab'] ) : '_blank';
            $ays_admin_pagination   = (isset($data['ays_admin_pagination']) && $data['ays_admin_pagination'] != '') ? wp_unslash(sanitize_text_field( $data['ays_admin_pagination'] )) : '';
            $ays_gpg_hover_zoom     = (isset($data['ays_gpg_hover_zoom']) && $data['ays_gpg_hover_zoom'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_hover_zoom'] )) : '';
            $vignette_display       = (isset($data['ays_gpg_vignette_display']) && $data['ays_gpg_vignette_display'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_vignette_display'] )) : '';
            //Hover zoom animation speed
            $hover_zoom_animation_speed = (isset($data['gpg_hover_zoom_animation_speed']) && $data['gpg_hover_zoom_animation_speed'] !== '') ? abs($data['gpg_hover_zoom_animation_speed']) : 0.5;
            //Hover animation speed
            $hover_animation_speed = (isset($data['ays_gpg_hover_animation_speed']) && $data['ays_gpg_hover_animation_speed'] !== '') ? abs($data['ays_gpg_hover_animation_speed']) : 0.5;
            //Hover scale animation speed
            $hover_scale_animation_speed = (isset($data['gpg_hover_scale_animation_speed']) && $data['gpg_hover_scale_animation_speed'] !== '') ? abs($data['gpg_hover_scale_animation_speed']) : 1;
            $ays_gpg_hover_scale    = wp_unslash(sanitize_text_field( isset($data['ays_gpg_hover_scale']) ? $data['ays_gpg_hover_scale'] : 'no' ));
            $ays_images_b_radius    = (isset($data['ays-gpg-images-border-radius']) && $data['ays-gpg-images-border-radius'] != '') ? wp_unslash(sanitize_text_field( $data['ays-gpg-images-border-radius'] )) : '';
            $ays_hover_icon_size    = (isset($data['ays-gpg-hover-icon-size']) && $data['ays-gpg-hover-icon-size'] != '') ? wp_unslash(sanitize_text_field( $data['ays-gpg-hover-icon-size'] )) : '';
            $ays_gpg_thumbnail_title_size = (isset($data['ays_gpg_thumbnail_title_size']) && $data['ays_gpg_thumbnail_title_size'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_thumbnail_title_size'] )) : '';
            $ays_gpg_loader         = (isset($data['ays_gpg_loader']) && $data['ays_gpg_loader'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_loader'] )) : '';

            // Gallery loader text value
            $gallery_loader_text_value = (isset($data['ays_gpg_loader_text_value']) && $data['ays_gpg_loader_text_value'] != '') ? sanitize_text_field( $data['ays_gpg_loader_text_value'] ) : '';

            // Gallery loader custom gif
            $gallery_loader_custom_gif = (isset($data['ays_gallery_loader_custom_gif']) && $data['ays_gallery_loader_custom_gif'] != '') ? sanitize_text_field( $data['ays_gallery_loader_custom_gif'] ) : '';

            if ($gallery_loader_custom_gif != '' && exif_imagetype( $gallery_loader_custom_gif ) != IMAGETYPE_GIF) {
                $gallery_loader_custom_gif = '';
            }

            // Gallery loader custom gif width
            $gallery_loader_custom_gif_width = (isset($data['ays_gallery_loader_custom_gif_width']) && sanitize_text_field( $data['ays_gallery_loader_custom_gif_width'] ) != '') ? absint( intval( $data['ays_gallery_loader_custom_gif_width'] ) ) : 100;
            
            $ays_images_border      = wp_unslash(sanitize_text_field( isset($data['ays_gpg_images_border']) ? $data['ays_gpg_images_border'] : '' ));
            $ays_images_b_width     = (isset($data['ays_gpg_images_border_width']) && $data['ays_gpg_images_border_width'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_images_border_width'] )) : '';
            $ays_images_b_style     = (isset($data['ays_gpg_images_border_style']) && $data['ays_gpg_images_border_style'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_images_border_style'] )) : '';
            $ays_images_b_color     = (isset($data['ays_gpg_border_color']) && $data['ays_gpg_border_color'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_border_color'] )) : '';
            $thumb_height_mobile    = wp_unslash(sanitize_text_field( isset($data['ays-thumb-height-mobile']) ? $data['ays-thumb-height-mobile'] : '' ));
            $thumb_height_desktop   = wp_unslash(sanitize_text_field( isset($data['ays-thumb-height-desktop']) ? $data['ays-thumb-height-desktop'] : '' ));
        
            $ays_lightbox_counter   = (isset($data['ays_gpg_lightbox_counter']) && $data['ays_gpg_lightbox_counter'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_lightbox_counter'] )) : '';
            $ays_lightbox_autoplay  = (isset($data['ays_gpg_lightbox_autoplay']) && $data['ays_gpg_lightbox_autoplay'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_lightbox_autoplay'] )) : '';
            $ays_lg_pause           = (isset($data['ays_gpg_lightbox_pause']) && $data['ays_gpg_lightbox_pause'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_lightbox_pause'] )) : '';
            $ays_lg_show_caption    = (isset($data['ays_gpg_show_caption']) && $data['ays_gpg_show_caption'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_show_caption'] )) : '';
            
            $gallery_img_position = (isset($data['gallery_img_position']) && $data['gallery_img_position'] != '') ? wp_unslash(sanitize_text_field( $data['gallery_img_position'] )) : 'center-center';
        
            $ays_show_gal_title     = (isset($data['ays_gpg_title_show']) && $data['ays_gpg_title_show'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_title_show'] )) : '';
            //$ays_show_gal_desc      = (isset($data['ays_gpg_desc_show']) && $data['ays_gpg_desc_show'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_desc_show'] )) : '';
            $images_hover_effect    = (isset($data['ays_images_hover_effect']) && $data['ays_images_hover_effect'] != '') ? sanitize_text_field( $data['ays_images_hover_effect'] ) : '';
            $hover_dir_aware        = (isset($data['ays_hover_dir_aware']) && $data['ays_hover_dir_aware'] != '') ? sanitize_text_field( $data['ays_hover_dir_aware'] ) : '';

            $enable_light_box       = isset($data['av_light_box']) && $data['av_light_box'] == "on" ? "on" :"off";
            $enable_search_img      = isset($data['gpg_search_img']) && $data['gpg_search_img'] == "on" ? "on" :"off";
            $ays_filter_cat         = isset($data['ays_filter_cat']) && $data['ays_filter_cat'] == "on" ? "on" :"off";
            $ays_gpg_filter_thubnail_opt = isset($data['ays_gpg_filter_thubnail_opt']) ? $data['ays_gpg_filter_thubnail_opt'] : "";
            $ays_gpg_filter_lightbox_opt = isset($data['ays_gpg_filter_lightbox_opt']) ? $data['ays_gpg_filter_lightbox_opt'] : "";
            $custom_class = (isset($data['ays_custom_class']) && $data['ays_custom_class'] != "") ? $data['ays_custom_class'] : '';

            $ays_gpg_ordering_asc_desc = (isset($data['ays_gpg_ordering_asc_desc']) && $data['ays_gpg_ordering_asc_desc'] != '') ? $data['ays_gpg_ordering_asc_desc'] : "ascending";
        
            $gpg_resp_width            = isset($data['gpg_resp_width']) && $data['gpg_resp_width'] == "on" ? "on" :"off";
            $gpg_height_width_ratios   = isset($data['gpg_height_width_ratio']) && !empty($data['gpg_height_width_ratio']) ? wp_unslash(sanitize_text_field($data['gpg_height_width_ratio'])) : 1;
            $gpg_height_width_ratio = floatval($gpg_height_width_ratios) < 0.1 ? 1 : $gpg_height_width_ratios;
            $gpg_enable_rtl            = (isset($data['ays_galery_enable_rtl_direction']) && $data['ays_galery_enable_rtl_direction'] == "on") ? "on" :"off";

            //Gallery title color 
            $ays_gallery_title_color = (isset($data['ays_gallery_title_color']) && $data['ays_gallery_title_color'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gallery_title_color'] )) : '#000';

            //Gallery description color 
            $ays_gallery_desc_color = (isset($data['ays_gallery_desc_color']) && $data['ays_gallery_desc_color'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gallery_desc_color'] )) : '#000';

            //Thubnail title color 
            $ays_gpg_title_color = (isset($data['ays_gpg_thumbnail_title_color']) && $data['ays_gpg_thumbnail_title_color'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_thumbnail_title_color'] )) : '#ffffff';
            
            $ays_gpg_filter_cat_anim = (isset($data['ays_filter_cat_animation']) && $data['ays_filter_cat_animation'] != '') ? sanitize_text_field( $data['ays_filter_cat_animation'] ) : 'fadeIn';
            $ays_gpg_lg_keypress    = (isset($data['ays_gpg_lg_keypress']) && $data['ays_gpg_lg_keypress'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_lg_keypress'] )) : '';
            $ays_gpg_lg_esckey    = (isset($data['ays_gpg_lg_esckey']) && $data['ays_gpg_lg_esckey'] != '') ? wp_unslash(sanitize_text_field( $data['ays_gpg_lg_esckey'] )) : '';

            $link_on_whole_img       = isset($data['link_on_whole_img']) && $data['link_on_whole_img'] == "on" ? "on" :"off";

            $gpg_create_date = !isset($data['ays_gpg_create_date']) ? '0000-00-00 00:00:00' : sanitize_text_field( $data['ays_gpg_create_date'] );

            $author = ( isset($data['ays_gpg_author']) && $data['ays_gpg_author'] != "" ) ? stripcslashes( sanitize_text_field( $data['ays_gpg_author'] ) ) : '';

            // Change the author of the current gallery
            $gpg_create_author = ( isset($data['ays_gallery_create_author']) && $data['ays_gallery_create_author'] != "" ) ? absint( sanitize_text_field( $data['ays_gallery_create_author'] ) ) : '';
            if ( $gpg_create_author != "" && $gpg_create_author > 0 ) {
                $user = get_userdata($gpg_create_author);
                if ( ! is_null( $user ) && $user ) {
                    $gpg_author = array(
                        'id' => $user->ID."",
                        'name' => $user->data->display_name
                    );

                    $author = json_encode($gpg_author, JSON_UNESCAPED_SLASHES);
                } else {
                    $author_data = json_decode($author, true);
                    $gpg_create_author = (isset( $author_data['id'] ) && $author_data['id'] != "") ? absint( sanitize_text_field( $author_data['id'] ) ) : get_current_user_id();
                }
            }
            $options = array(
                'columns_count'             => $columns_count,
                'view_type'                 => $view_type,
                'border_radius'             => $ays_images_b_radius,
                'admin_pagination'          => $ays_admin_pagination,
                'hover_zoom'                => $ays_gpg_hover_zoom,
                'vignette_display'          => $vignette_display,
                'hover_zoom_animation_speed'=> $hover_zoom_animation_speed,
                'hover_animation_speed'     => $hover_animation_speed,
                'hover_scale_animation_speed'=> $hover_scale_animation_speed,
                'hover_scale'               => $ays_gpg_hover_scale,
                'show_gal_title'            => $ays_show_gal_title,
                //'show_gal_desc'             => $ays_show_gal_desc,
                "images_hover_effect"       => $images_hover_effect,
                "hover_dir_aware"           => $hover_dir_aware,
                "images_border"             => $ays_images_border,
                "images_border_width"       => $ays_images_b_width,
                "images_border_style"       => $ays_images_b_style,
                "images_border_color"       => $ays_images_b_color,
                "hover_effect"              => $hover_effect,
                "img_load_effect"           => $img_load_effect,
                "gallery_img_position"      => $gallery_img_position,
                "hover_opacity"             => $hover_opacity,
                "hover_color"               => $hover_color,
                "image_sizes"               => $image_sizes,
                "lightbox_color"            => $lightbox_color,
                "images_orderby"            => $images_orderby,
                "hover_icon"                => $hover_icon,
                "show_title"                => $show_title,
                "show_title_on"             => $show_title_on,
                "title_position"            => $title_position,
                "show_with_date"            => $show_with_date,
                "images_distance"           => $images_distance,
                "images_loading"            => $ays_images_loading,
                "redirect_url_tab"          => $gpg_redirect_url_tab,
                "gallery_loader"            => $ays_gpg_loader,
                "gallery_loader_text_value" => $gallery_loader_text_value,
                "hover_icon_size"           => $ays_hover_icon_size,
                "thumbnail_title_size"      => $ays_gpg_thumbnail_title_size,
                "thumb_height_mobile"       => $thumb_height_mobile,
                "thumb_height_desktop"      => $thumb_height_desktop,
                "enable_light_box"          => $enable_light_box,
                "enable_search_img"         => $enable_search_img,
                "ays_filter_cat"            => $ays_filter_cat,
                "filter_thubnail_opt"       => $ays_gpg_filter_thubnail_opt,
                "ordering_asc_desc"         => $ays_gpg_ordering_asc_desc,
                "custom_class"              => $custom_class,
                "resp_width"                => $gpg_resp_width,
                "height_width_ratio"        => $gpg_height_width_ratio,
                "enable_rtl_direction"      => $gpg_enable_rtl,
                "ays_gallery_title_color"   => $ays_gallery_title_color,
                "ays_gallery_desc_color"    => $ays_gallery_desc_color,
                "ays_gpg_title_color"       => $ays_gpg_title_color,
                "ays_gpg_filter_cat_anim"   => $ays_gpg_filter_cat_anim,
                "link_on_whole_img"         => $link_on_whole_img,
                "create_date"               => $gpg_create_date,
                "author"                    => $author,
                'gpg_create_author'         => $gpg_create_author,
                "gallery_loader_custom_gif" => $gallery_loader_custom_gif,
                "gallery_loader_custom_gif_width" => $gallery_loader_custom_gif_width,
            );
            $lightbox_options = array(
                "lightbox_counter"          => $ays_lightbox_counter,
                "lightbox_autoplay"         => $ays_lightbox_autoplay,
                "lb_pause"                  => $ays_lg_pause,
                "lb_show_caption"           => $ays_lg_show_caption,
                "filter_lightbox_opt"       => $ays_gpg_filter_lightbox_opt,
                "lb_keypress"               => $ays_gpg_lg_keypress,
                "lb_esckey"                 => $ays_gpg_lg_esckey,
                
            );
            $submit_type = (isset($data['submit_type'])) ?  $data['submit_type'] : '';
            if( $id == null ){
                $gallery_result = $wpdb->insert(
                    $gallery_table,
                    array(
                        "title"             => $title,
                        "description"       => $description,
                        "images"            => '',
                        "images_titles"     => '',
                        "images_descs"      => '',
                        "images_alts"       => '', //TODO remove
                        "images_urls"       => '', //TODO remove
                        "categories_id"     => $image_categories,
                        "width"             => $width,
                        "height"            => $height,
                        "options"           => json_encode($options),
                        "lightbox_options"  => json_encode($lightbox_options),
                        "custom_css"        => $custom_css,
                        "images_dates"      => '',
                        "images_ids"        => $image_ids
                    ),
                    array( "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s" )
                );
                $message = "created";
            }else{
                $gallery_result = $wpdb->update(
                    $gallery_table,
                    array(
                        "title"             => $title,
                        "description"       => $description,
                        "images"            => $image_paths,
                        "images_titles"     => '',
                        "images_descs"      => '',
                        "images_alts"       => '', //TODO remove
                        "images_urls"       => '', //TODO remove
                        "categories_id"     => $image_categories,
                        "width"             => $width,
                        "height"            => $height,
                        "options"           => json_encode($options),
                        "lightbox_options"  => json_encode($lightbox_options),
                        "custom_css"        => $custom_css,
                        "images_dates"      => '',
                        "images_ids"        => $image_ids
                    ),
                    array( "id" => $id ),
                    array( "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%d", "%d", "%s", "%s", "%s", "%s", "%s" ),
                    array( "%d" )
                );
                $message = "updated";
            }
            $ays_gpg_tab = isset($data['ays_gpg_settings_tab']) ? $data['ays_gpg_settings_tab'] : 'tab1';
            if( $gallery_result >= 0 ){
                if($submit_type == ''){
                    $url = esc_url_raw( remove_query_arg(["action", "gallery"]  ) ) . "&status=" . $message . "&type=success";
                    wp_redirect( $url );
                    exit();
                }else{
                    if($id == null){
                        $url = esc_url_raw( add_query_arg( array(
                            "action"                => "edit",
                            "gallery"               => $wpdb->insert_id,
                            "ays_gpg_settings_tab"  => $ays_gpg_tab,
                            "status"                => $message
                        ) ) );
                    }else{
                        $url = esc_url_raw( remove_query_arg(false) ) . '&ays_gpg_settings_tab='.$ays_gpg_tab.'&status=' . $message;
                    }

                    wp_redirect( $url );
                    exit();
                }
            }
        }
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_galleries( $id ) {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}ays_gallery",
            array( "id" => $id ),
            array( "%d" )
        );
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $filter = array();

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_gallery";

        $search = ( isset( $_REQUEST['s'] ) ) ? esc_sql( sanitize_text_field( $_REQUEST['s'] ) ) : false;
        if( $search ){
            $filter[] = sprintf(" title LIKE '%%%s%%' ", esc_sql( $wpdb->esc_like( $search ) ) );
        }
        
        if(count($filter) !== 0){
            $sql .= " WHERE ".implode(" AND ", $filter);
        }


        return $wpdb->get_var( $sql );
    }


    /** Text displayed when no customer data is available */
    public function no_items() {
        echo __( "There are no galleries yet.", $this->plugin_name );
    }
    //TODO test duplicate
    public function duplicate_galleries( $id ){
        global $wpdb;
        $galleries_table = $wpdb->prefix."ays_gallery";
        $gallery = $this->get_gallery_by_id($id);
       
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $author = array(
            'id' => $user->ID,
            'name' => $user->data->display_name
        );
        
        $max_id = $this->get_max_id();
        $ordering = ( $max_id != NULL ) ? ( $max_id + 1 ) : 1;
        
        $options = json_decode($gallery['options'], true);
        
        $options['create_date'] =  current_time( 'mysql' );
        $options['author'] = $author;
        
        $result = $wpdb->insert(
            $galleries_table,
            array(
                'title'             => "Copy - ".sanitize_text_field($gallery['title']),
                'description'       => sanitize_text_field($gallery['description']),
                'images'            => '',
                'images_titles'     => '',
                'images_descs'      => '',
                'images_alts'       => '',
                'images_urls'       => '',
                'images_dates'      => '',
                'width'             => sanitize_text_field($gallery['width']),
                'height'            => sanitize_text_field($gallery['height']),
                'options'           => json_encode($options),
                'lightbox_options'  => sanitize_text_field($gallery['lightbox_options']),
                'categories_id'     => sanitize_text_field($gallery['categories_id']),
                'custom_css'        => $gallery['custom_css']
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        if( $result >= 0 ){
            $message = "duplicated";
            $url = esc_url_raw( remove_query_arg(array('action', 'question')  ) ) . '&status=' . $message;
            wp_redirect( $url );
        }
        
    }

    private function get_max_id() {
        global $wpdb;
        $gallery_table = $wpdb->prefix . 'ays_gallery';

        $sql = "SELECT max(id) FROM {$gallery_table}";

        $result = $wpdb->get_var($sql);

        return $result;
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case "title":
            case "image":
            case "description":
                return wp_unslash($item[ $column_name ]);
                break;
            case "shortcode":
            case 'create_date':
            case "items":
            case "id":
                return $item[ $column_name ];
                break;
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            "<input type='checkbox' name='bulk-delete[]' value='%s' />", $item["id"]
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_image( $item ) {
        error_log("column_image IN");
        global $wpdb;
        $gallery_images = isset($item['images']) && $item['images'] != "" ? explode('***', $item['images']) : array();
        $gallery_image  = "";

        $image_html     = array();
        $edit_page_url  = '';

        if(!empty($gallery_images)){
            $gallery_image = isset($gallery_images[0]) && $gallery_images[0] != "" ? $gallery_images[0] : "";

            if ( isset( $item['id'] ) && absint( $item['id'] ) > 0 ) {
                $edit_page_url = sprintf( 'href="?page=%s&action=%s&gallery=%d"', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ) );
            }

            $gallery_image_url = $gallery_image;
            $this_site_path = trim( get_site_url(), "https:" );
            if( strpos( trim( $gallery_image_url, "https:" ), $this_site_path ) !== false ){ 
                $query = "SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'attachment' AND `guid` = '" . $gallery_image_url . "'";
                $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                if( ! empty( $result_img ) ){
                    $url_img = wp_get_attachment_image_src( $result_img[0]['ID'], 'thumbnail' );
                    if( $url_img !== false ){
                        $gallery_image_url = $url_img[0];
                    }
                }
            }

            $image_html[] = '<div class="ays-gallery-image-list-table-column">';
                $image_html[] = '<a '. $edit_page_url .' class="ays-gallery-image-list-table-link-column">';
                    $image_html[] = '<img src="'. $gallery_image_url .'" class="ays-gallery-list-table-main-image">';
                $image_html[] = '</a>';
            $image_html[] = '</div>';
        }
        
        $image_html = implode('', $image_html);

        return $image_html;
    }    

    function column_title( $item ) {
        error_log("column_title IN");
        $delete_nonce = wp_create_nonce( $this->plugin_name . "-delete-gallery" );
        $duplicate_nonce = wp_create_nonce( $this->plugin_name . "-duplicate-gallery" );
        $gallery_title = esc_attr(stripcslashes($item['title']));

        $q = esc_attr($gallery_title);
        $gallery_title_length = intval( $this->title_length );

        $restitle = Gallery_Photo_Gallery_Admin::ays_gpg_restriction_string("word", $gallery_title, $gallery_title_length);

        $title = sprintf( '<a href="?page=%s&action=%s&gallery=%d" title="%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ), $q, $restitle);

        $actions = array(
            "edit" => sprintf( "<a href='?page=%s&action=%s&gallery=%d'>". __('Edit', $this->plugin_name) ."</a>", esc_attr( $_REQUEST["page"] ), "edit", absint( $item["id"] ) ),
            'duplicate' => sprintf( '<a href="?page=%s&action=%s&gallery=%d&_wpnonce=%s">'. __('Duplicate', $this->plugin_name) .'</a>', esc_attr( $_REQUEST['page'] ), 'duplicate', absint( $item['id'] ), $duplicate_nonce ),
            "delete" => sprintf( "<a href='?page=%s&action=%s&gallery=%s&_wpnonce=%s'>". __('Delete', $this->plugin_name) ."</a>", esc_attr( $_REQUEST["page"] ), "delete", absint( $item["id"] ), $delete_nonce )
        );

        return $title . $this->row_actions( $actions );
    }

    function column_shortcode( $item ) {
        error_log("column_shortcode IN");
        return sprintf('<div class="ays-gpg-shortcode-container">
                    <div class="ays-gpg-copy-image" data-bs-toggle="tooltip" title="'. esc_html(__('Click to copy',$this->plugin_name)).'">
                            <img src="'. esc_url(AYS_GPG_ADMIN_URL) . '/images/icons/copy-image.svg">
                    </div>                                            
                    <input type="text" class="ays-gpg-shortcode-input" readonly value="'. esc_attr('[gallery_p_gallery id="%s"]').'" />
                </div>', $item["id"]);
        // return sprintf("<input type='text' onClick='this.setSelectionRange(0, this.value.length)' readonly value='[gallery_p_gallery id=%s]'  />", $item["id"]);        
    }

    function column_items( $item ) {
       $item_count = explode('***', $item['images']);
       return count($item_count);
    }

    function column_create_date( $item ) {
        
        $options = json_decode($item['options'], true);
        $date = isset($options['create_date']) && $options['create_date'] != '' ? $options['create_date'] : "0000-00-00 00:00:00";
        if(isset($options['author'])){
            if(is_array($options['author'])){
                $author = $options['author'];
            }else{
                $author = json_decode($options['author'], true);
            }
        }else{
            $author = array("name"=>"Unknown");
        }
        $text = "";
        if(Gallery_Photo_Gallery_Admin::validateDate($date)){
            $text .= "<p><b>Date:</b> ".$date."</p>";
        }
        if($author['name'] !== "Unknown"){
            $text .= "<p><b>Author:</b> ".$author['name']."</p>";
        }
        return $text;
    } 

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            "cb"                => "<input type='checkbox' />",
            "title"             => __( "Title", $this->plugin_name ),
            // "image"             => __( "Image", $this->plugin_name ),
            // "description"       => __( "Description", $this->plugin_name ),
           "shortcode"         => __( "Shortcode", $this->plugin_name ),
            "items"             => __( "Nb photos", $this->plugin_name ),
            "create_date"       => __( 'Created', $this->plugin_name ),
            //"id"                => __( "ID", $this->plugin_name ),
        );

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            "title"         => array( "title", true ),
            "id"            => array( "id", true ),
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            "bulk-delete" => __("Delete", $this->plugin_name)
        );

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        global $wpdb;
        
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( "galleries_per_page", 20 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( array(
            "total_items" => $total_items, //WE have to calculate the total number of items
            "per_page"    => $per_page //WE have to determine how many items to show on a page
        ) );

        $search = ( isset( $_REQUEST['s'] ) ) ? esc_sql( sanitize_text_field( $_REQUEST['s'] ) ) : false;
        $do_search = ( $search ) ? sprintf(" title LIKE '%%%s%%' ", esc_sql( $wpdb->esc_like( $search ) ) ) : '';

        $this->items = self::get_galleries( $per_page, $current_page,$do_search );
    }

    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        $message = "deleted";
        if ( "delete" === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST["_wpnonce"] );

            if ( ! wp_verify_nonce( $nonce, $this->plugin_name . "-delete-gallery" ) ) {
                die( "Go get a life script kiddies" );
            }
            else {
                self::delete_galleries( absint( $_GET["gallery"] ) );

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url

                $url = esc_url_raw( remove_query_arg(array("action", "gallery", "_wpnonce")  ) ) . "&status=" . $message . "&type=success";
                wp_redirect( $url );
                exit();
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST["action"] ) && $_POST["action"] == "bulk-delete" )
            || ( isset( $_POST["action2"] ) && $_POST["action2"] == "bulk-delete" )
        ) {

            $delete_ids = ( isset( $_POST['bulk-delete'] ) && ! empty( $_POST['bulk-delete'] ) ) ? esc_sql( $_POST['bulk-delete'] ) : array();

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_galleries( $id );

            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url

            $url = esc_url_raw( remove_query_arg(array("action", "gallery", "_wpnonce")  ) ) . "&status=" . $message . "&type=success";
            wp_redirect( $url );
            exit();
        }
    }

    public function gallery_notices(){
        $status = (isset($_REQUEST["status"])) ? sanitize_text_field( $_REQUEST["status"] ) : "";
        $type = (isset($_REQUEST["type"])) ? sanitize_text_field( $_REQUEST["type"] ) : "success";

        if ( empty( $status ) )
            return;

        if ( "created" == $status )
            $updated_message = esc_html( __( "Gallery created.", $this->plugin_name ) );
        elseif ( "updated" == $status )
            $updated_message = esc_html( __( "Gallery saved.", $this->plugin_name ) );
        elseif ( 'duplicated' == $status )
            $updated_message = esc_html( __( 'Gallery duplicated.', $this->plugin_name ) );
        elseif ( "deleted" == $status )
            $updated_message = esc_html( __( "Gallery deleted.", $this->plugin_name ) );
        elseif ( "error" == $status )
            $updated_message = __( "You're not allowed to add gallery for more galleries please checkout to ", $this->plugin_name )."<a href='http://ays-pro.com/wordpress/photo-gallery' target='_blank'>PRO ".__( "version", $this->plugin_name )."</a>.";

        if ( empty( $updated_message ) )
            return;

        ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }
}
