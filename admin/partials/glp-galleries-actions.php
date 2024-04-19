<?php
//TODO fix nb of images per page, ex selected 25 -> count 5
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
    "categories_id"     => "",
    "width"             => "",
    "height"            => "1000",
    "options"           => json_encode($g_options,true),
    "lightbox_options"  => json_encode($g_l_options,true),
    "custom_css"        => "",
    "user_id"      => "",
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

$gallery_message_vars_html = $this->ays_gallery_generate_message_vars_html( $gallery_message_vars );

if(isset($_POST["ays-submit"]) || isset($_POST["ays-submit-top"])){
    $_POST["id"] = $id;
    $this->gallery_obj->add_or_edit_gallery($_POST);
}
if(isset($_POST["ays-apply"]) || isset($_POST["ays-apply-top"])){
    $_POST["id"] = $id;
    $_POST["submit_type"] = 'apply';
    $this->gallery_obj->add_or_edit_gallery($_POST);
}

$next_gallery_id = "";
$prev_gallery_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $next_gallery = $this->get_next_or_prev_gallery_by_id( $id, "next" );
    $next_gallery_id = (isset( $next_gallery['id'] ) && $next_gallery['id'] != "") ? absint( $next_gallery['id'] ) : null;
    $prev_gallery = $this->get_next_or_prev_gallery_by_id( $id, "prev" );
    $prev_gallery_id = (isset( $prev_gallery['id'] ) && $prev_gallery['id'] != "") ? absint( $prev_gallery['id'] ) : null;
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

$gallery_categories = $this->ays_get_categories();

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

// General Settings | options
$gen_options = ($this->settings_obj->ays_get_setting('options') === false) ? array() : json_decode( stripcslashes($this->settings_obj->ays_get_setting('options') ), true);

// WP Editor height
$gpg_wp_editor_height = (isset($gen_options['gpg_wp_editor_height']) && $gen_options['gpg_wp_editor_height'] != '') ? absint( sanitize_text_field($gen_options['gpg_wp_editor_height']) ) : 100 ;

// Change the author of the current gallery
//$change_gpg_create_author = (isset($gal_options['gpg_create_author']) && $gal_options['gpg_create_author'] != '') ? absint( sanitize_text_field( $gal_options['gpg_create_author'] ) ) : $user_id;

// Images distance
$images_distance = (isset($gal_options['images_distance']) && $gal_options['images_distance'] != '') ? absint( intval( $gal_options['images_distance'] ) ) : '5';

// mosaic row size
$mosaic_row_size = (isset($gal_options['mosaic_row_size']) && $gal_options['mosaic_row_size'] != '') ? absint( intval( $gal_options['mosaic_row_size'] ) ) : '500';
error_log("from DB: mosaic_row_size=".$mosaic_row_size);
// if( $change_gpg_create_author  && $change_gpg_create_author > 0 ){
//     global $wpdb;
//     $users_table = esc_sql( $wpdb->prefix . 'users' );

//     $sql_users = "SELECT ID,display_name FROM {$users_table} WHERE ID = {$change_gpg_create_author}";

//     $glp_create_author_data = $wpdb->get_row($sql_users, "ARRAY_A");
// } else {
//     $change_gpg_create_author = $user_id;
//     $glp_create_author_data = array(
//         "ID" => $user_id,
//         "display_name" => $user->data->display_name,
//     );
// }

$query_categories = isset($gal_options['query_categories']) ? $gal_options['query_categories'] : '';
error_log( "CATEGORIES from options: ".$query_categories);

$loader_iamge = "<span class='display_none glp_loader_box'><img src='". GLP_ADMIN_URL ."/images/loaders/loading.gif'></span>";
?>

<div class="wrap">
    <div class="glp-heading-box">
        <div class="glp-wordpress-user-manual-box">
            <a href="https://glp-plugin.com/wordpress-photo-gallery-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                <i class="ays_glp glp_fa_file_text"></i>
                <span style="margin-left: 3px;text-decoration: underline;"><?php echo __("View Documentation", $this->plugin_name); ?></span>
            </a>
        </div>
    </div>
    <div class="container-fluid">
        <form id="glp-form" method="post">
        <input type="hidden" id="ays_submit_name">
        <input type="hidden" name="glp_settings_tab" value="<?php echo esc_attr($glp_tab); ?>">
        <input type="hidden" name="glp_create_date" value="<?php echo $gpg_create_date; ?>">    
        <input type="hidden" name="glp_author" value="<?php echo esc_attr(json_encode($gpg_author, JSON_UNESCAPED_SLASHES)); ?>">
        <h1 class="wp-heading-inline">
            <?php echo $heading; ?>
            <input type="submit" name="ays-submit-top" class="ays-submit button action-button button-primary glp-save-comp" value="<?php echo __("Save and close", $this->plugin_name);?>" gpg_submit_name="ays-submit" />        
            <input type="submit" name="ays-apply-top" class="ays-submit action-button button glp-save-comp" id="ays-button-top-apply" title="Ctrl + s" data-toggle="tooltip" data-delay='{"show":"1000"}' value="<?php echo __("Save", $this->plugin_name);?>" gpg_submit_name="ays-apply"/>
            <?php echo $loader_iamge; ?>
        </h1>
        <hr>
        <h3><?php echo esc_attr( stripslashes( $gallery["title"] ) ); ?></h3>
        <div class="form-group row">
            <div class="col-sm-3">
                <label for="gallery_title">
                    <?php echo __("Gallery Title", $this->plugin_name);?>
                </label>
            </div>
            <div class="col-sm-9">
                <input type="text" required name="gallery_title" id="gallery_title" class="ays-text-input" placeholder="<?php echo __("Gallery Title", $this->plugin_name);?>" value="<?php echo stripslashes(htmlentities($gallery["title"])); ?>"/>
            </div>
        </div>

        <div>     
            <?php if($id !== null): ?>
            <div class="row">
                <div class="col-sm-3">
                    <label> <?php echo __( "Shortcode", $this->plugin_name ); ?> </label>
                </div>
                <div class="col-sm-9">
                    <p style="font-size:14px; font-style:italic;">
                        <strong class="ays-gallery-shortcode-box" onClick="selectElementContents(this)" data-toggle="tooltip" title="<?php echo __('Click for copy.', $this->plugin_name);?>" style="font-size:16px; font-style:normal;"><?php echo "[glp_gallery id=".$id."]"; ?></strong>
                    </p>
                </div>
            </div>
            <?php endif;?>
        </div>
        <hr>
        <?php
            echo "<style>
                    .ays_ays_img {
                        display: block;
                        width: 100%;
                        height: 100%;
                        background-image: url('".GLP_ADMIN_URL .'images/no-photo.png'."');
                        background-size: cover;
                        background-position: center center;
                    }
                </style>";
        ?>
        <div class="glp-top-menu-wrapper">
            <div class="glp_menu_left" data-scroll="0"><i class="ays_glp glp_fa_angle_left"></i></div>
            <div class="glp-top-menu">
                <div class="nav-tab-wrapper glp-top-tab-wrapper">
                    <a href="#tab1" data-tab="tab1" class="nav-tab <?php echo ($glp_tab == 'tab1') ? 'nav-tab-active' : ''; ?>">
                        <?php echo __("Images", $this->plugin_name);?>
                    </a>
                    <a href="#tab2" data-tab="tab2" class="nav-tab <?php echo ($glp_tab == 'tab2') ? 'nav-tab-active' : ''; ?>">
                        <?php echo __("Settings", $this->plugin_name);?>
                    </a>
                </div>  
            </div>              
            <div class="glp_menu_right" data-scroll="-1"><i class="ays_glp glp_fa_angle_right"></i></div>
        </div>
        <div id="tab1" class="ays-gallery-tab-content <?php echo ($glp_tab == 'tab1') ? 'ays-gallery-tab-content-active' : ''; ?>">
            <br>
            <!-- MJO removed description -->
            <h6 class="ays-subtitle"><?php echo  __('Add Images', $this->plugin_name) ?>
            <span class="ays_title_note">
                    <?php echo __("Set the image list: manual selection or by category", $this->plugin_name);?>
                </span>
            </h6>

            <div>
                <label class="glp_image_hover_icon" id="gpg_images_request_selection"><?php echo __("Select from library ", $this->plugin_name);?>
                    <input type="radio" id="glp_images_request_selection" name="ays_images_request" value="selection"
                        <?php echo ($image_request_type == "selection") ? "checked" : ""; ?> />
                </label>
                <label class="glp_image_hover_icon" id="gpg_images_request_query"><?php echo __("By category ", $this->plugin_name);?> 
                    <input type="radio" id="glp_images_request_query" name="ays_images_request" value="query" 
                        <?php echo ($image_request_type == "query") ? "checked" : ""; ?>/>
                </label>
            </div>

            <div id="image_selection">

                <!-- <h6><?php echo  __('Upload images for your gallery', $this->plugin_name) ?></h6> -->
                <hr/>
                <!-- <button type="button" class="ays-add-images button"><?php //echo __("Add image +", $this->plugin_name); ?></button> -->
                <button type="button" class="ays-add-multiple-images button"><?php echo __("Add multiple images +", $this->plugin_name); ?></button>
                <!-- <button class="ays-add-video button"><?php //echo __("Add video +", $this->plugin_name); ?></button>-->
                <button type="button" class="ays_bulk_del_images button" disabled><?php echo __("Delete", $this->plugin_name); ?></button>
                <button type="button" class="ays_select_all_images button"><?php echo __("Select all", $this->plugin_name); ?></button>
                <input type="hidden" id="ays_image_lang_title" value="<?php echo __("It shows the name of the inserted picture", $this->plugin_name ); ?>">
                <input type="hidden" id="ays_image_lang_alt" value="<?php echo __("This field shows the alternate text when the picture is not loaded or not found", $this->plugin_name ); ?>">
                <input type="hidden" id="ays_image_lang_desc" value="<?php echo __("This field shows the description of the chosen image", $this->plugin_name ); ?>">
                <input type="hidden" id="ays_image_lang_url" value="<?php echo __("This section is for the URL address", $this->plugin_name ); ?>">
                <input type="hidden" id="ays_image_cat" value="<?php echo __("Select image categories", $this->plugin_name ); ?>">
                <hr/>
                <div class="glp_page_sort">
                    <div id="glp_pagination">
                        <select name="ays_admin_pagination" id="ays_admin_pagination">
                            <option <?php echo $admin_pagination == "all" ? "selected" : ""; ?> value="all"><?php echo __( "All", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "5" ? "selected" : ""; ?> value="5"><?php echo __( "5", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "10" ? "selected" : ""; ?> value="10"><?php echo __( "10", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "15" ? "selected" : ""; ?> value="15"><?php echo __( "15", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "20" ? "selected" : ""; ?> value="20"><?php echo __( "20", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "25" ? "selected" : ""; ?> value="25"><?php echo __( "25", $this->plugin_name ); ?></option>
                            <option <?php echo $admin_pagination == "30" ? "selected" : ""; ?> value="30"><?php echo __( "30", $this->plugin_name ); ?></option>
                        </select>            
                        <span class="glp_image_hover_icon_text"><?php echo __( "Pagination", $this->plugin_name ); ?></span>
                    </div>
                    <div id="glp_sort_cont">    
                        <button class="glp_sort">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </div>
                <hr/>
                <div class="paged_ays_accordion">
                <?php
                    if($id == null) : // Gallery id NULL --> NEW 
                        error_log("New gallery, id is null");
                ?>
                
                        <ul class="ays-accordion">
                        </ul>
                <?php
                else:
                    // Gallery id NOT NULL
                    //error_log("^^^^^^^^^^^^^^ id is not null: ".$id);
                    $images_ids = explode( "***", $gallery["images_ids"] );
                    //error_log("images_ids: ".print_r($images_ids, true));//TODO

                    $gal_cat_ids = isset($gallery['categories_id']) && $gallery['categories_id'] != '' ? explode( "***", $gallery["categories_id"] ) : array();
                    if($admin_pagination != "all"){
                        $pages = intval(ceil(count($images_ids)/$admin_pagination));
                        $qanak = 0;
                        if(isset($_COOKIE['glp_page_tab_free'])){
                            $ays_page_cookie = explode("_", $_COOKIE['glp_page_tab_free']);
                            if($ays_page_cookie[1] >= $pages){
                                unset($_COOKIE['glp_page_tab_free']);
                                setcookie('glp_page_tab_free', "", time() - 3600, "/");
                                setcookie('glp_page_tab_free', 'tab_'.($pages-1), time() + 3600);
                                $_COOKIE['glp_page_tab_free'] = 'tab_'.($pages-1);
                            }
                        }

                        for($i = 0; $i < $pages; $i++){
                            $accordion_active = (isset($_COOKIE['glp_page_tab_free']) && $_COOKIE['glp_page_tab_free'] == "tab_".($i)) ? 'ays_accordion_active' : '';
                ?>
                
                <ul class="ays-accordion ays_accordion <?php echo $accordion_active; ?>" id="page_<?php echo $i; ?>">
                
                <?php
                    //error_log("admin_pagination=".$admin_pagination);//TODO
                    for ($key = $qanak, $j = 0; $key < count($images_ids); $key++, $j++ ) {
                        if($j >= $admin_pagination){
                            $qanak = $key;
                            break;
                        }

                        // query from table postmeta
                        $query = "SELECT meta_key, meta_value FROM `".$wpdb->prefix."postmeta` WHERE `post_id` = '".$images_ids[$key]."'";
                        //error_log("query: ".$query);
                        $result_meta =  $wpdb->get_results( $query, "ARRAY_A" );
                        $category_id = -1;
                        $category = '';
                        if (count($result_meta) > 0) {
                            foreach ($result_meta as $item) {
                                if ($item['meta_key'] === 'category') {
                                    $category_id = $item['meta_value'];
                                }
                            }
                            if (count($gallery_categories) > 0 && $category_id > 0) {
                                foreach ($result_categories as $item) {
                                    if ($item->term_id === $category_id) {
                                        $category = $item->name;
                                    }
                                }
                            }
                        }

                        //TODO test: remove the photo from the media gallery and check the galleries
                        $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `id` = '".$images_ids[$key]."'";
                        $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                        if (!empty($result_img)) {

                            $img_id = $result_img[0]['ID'];
                            $image = $result_img[0]['guid'];

                            $img_thmb_src = wp_get_attachment_image_src($img_id,  'thumbnail');
                            if($img_thmb_src === false){
                                $img_thmb_src = $image;
                            }else{
                                $img_thmb_src = !empty($img_thmb_src) ? $img_thmb_src[0] : $image_no_photo;
                            }
                            
                            $img_title = $result_img[0]['post_title'];
                            $img_description = $result_img[0]['post_content'];
                            $img_caption = $result_img[0]['post_excerpt'];
                            $img_url = $result_img[0]['guid'];
                            $img_date = $result_img[0]['post_date'];
                            error_log("#*# img_id=".$img_id.", img_title=".$img_title.", img_description=".$img_description.", img_caption=".$img_caption.", img_url=".$img_url.", img_date=".$img_date);

                            $img_thmb_html = !empty($img_thmb_src) ? '<img class="ays_ays_img" style="background-image:none;" src="'.$img_thmb_src.'">' : '<img class="ays_ays_img">';
                            ?>
                            <!-- id NOT NULL GALLERY -->
                            <li class="ays-accordion_li">
                                <input type="hidden" name="ays-image-path[]" value="<?php echo $image; ?>">
                                <input type="hidden" name="ays-image-id[]" value="<?php echo $img_id; ?>">
                                <div class="ays-image-attributes">
                                    <div class='ays_image_div'>                      
                                        <div class="ays_image_thumb" style="display: block; position: relative;">
                                            <div class="ays_image_edit_div" style="position: absolute;"><i class="ays-move-images"></i></div>
                                            <div class='ays_image_thumb_img'><?php echo $img_thmb_html; ?></div>
                                        </div>
                                    </div>
                                    <div class="ays_image_attr_item_cat">
                                        <div class="ays_image_attr_item_parent">
                                            <div>
                                                <?php echo __("Title: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr($img_title)); ?></b>
                                                <input type="hidden" name="ays-image-title[]" value="<?php echo stripslashes(esc_attr($img_title)); ?>"/>
                                            </div>
                                            <div>
                                                <?php echo __("Description: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr($img_description)); ?></b>
                                                <input type="hidden" name="ays-image-description[]" value="<?php echo stripslashes(esc_attr($img_description)); ?>"/>
                                            </div>
                                            <div>
                                                <?php echo __("Category: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr($category)); ?></b>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ays-image-date[]" class="ays_img_date" value="<?php echo $img_date; ?>" />
                                    <div class="ays_del_li_div"><input type="checkbox" class="ays_del_li"/></div>
                                    <div class='ays-delete-image_div'><i class="ays-delete-image"></i></div>
                                </div>
                            </li>
                            <?php
                            }
                        }
                    ?>
                </ul>
                    <?php 
                        }
                    }else{
                ?>
                <ul class="ays-accordion">
                <?php

                    //echo "images=".$images[0];
                    // loop for all image ids
                    foreach ( $images_ids as $key => $id ) {
                        
                        // query from table postmeta
                        $query = "SELECT meta_key, meta_value FROM `".$wpdb->prefix."postmeta` WHERE `post_id` = '".$id."'";
                        //error_log("query: ".$query);
                        $result_meta =  $wpdb->get_results( $query, "ARRAY_A" );
                        $category_id = -1;
                        $category = '';
                        if (count($result_meta) > 0) {
           
                            foreach ($result_meta as $item) {
                                if ($item['meta_key'] === 'category') {
                                    $category_id = $item['meta_value'];
                                }
                            }
                            if (count($gallery_categories) > 0 && $category_id > 0) {
                                foreach ($gallery_categories as $item) {
                                    if ($item->term_id === $category_id) {
                                        $category = $item->name;
                                    }
                                }
                            }
                        }    
                        
                        $query = "SELECT * FROM `".$wpdb->prefix."posts` WHERE `id` = '".$id."'";
                        //error_log( "query=".$query);
                        $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                        //error_log("#########images: ".print_r($result_img, true));
                        //error_log("#########images: ".print_r($result_img, true));
                        // error_log("#########id: ".$result_img[0]['ID']);
                        // error_log("#########count: ".count($result_img));
                        // error_log("#########this_site_path: ".$this_site_path);
                        if (!empty($result_img)) {

                            $image = $result_img[0]['guid'];
                            $img_id = $result_img[0]['ID'];

                            $img_thmb_src = wp_get_attachment_image_src($img_id,  'thumbnail');
                            if($img_thmb_src === false){
                                //$img_thmb_src = $images[$key]; TODO test several thumnails, what about img_thmb_src[0]
                                $img_thmb_src = $image;
                            }else{
                                $img_thmb_src = !empty($img_thmb_src) ? $img_thmb_src[0] : $image_no_photo;
                            }
                            //TODO test: remove the photo from the media gallery and check the galleries
                            
                            $img_title = $result_img[0]['post_title'];
                            $img_description = $result_img[0]['post_content'];
                            $img_caption = $result_img[0]['post_excerpt'];
                            $img_url = $result_img[0]['guid'];
                            $img_date = $result_img[0]['post_date'];
                            //error_log("#-# img_id=".$img_id.", img_title=".$img_title.", img_description=".$img_description.", img_caption=".$img_caption.", img_url=".$img_url.", img_date=".$img_date);

                            $img_thmb_html = !empty($img_thmb_src) ? '<img class="ays_ays_img" style="background-image:none;" src="'.$img_thmb_src.'">' : '<img class="ays_ays_img">';
                            ?>
                            <!-- TOTO FOR EACH SAVED IMAGE -->
                            <li class="ays-accordion_li">
                                <input type="hidden" name="ays-image-path[]" value="<?php echo $image; ?>">
                                <input type="hidden" name="ays-image-id[]" value="<?php echo $img_id; ?>">
                                <div class="ays-image-attributes">
                                    <div class='ays_image_div'>                      
                                        <div class="ays_image_thumb" style="display: block; position: relative;">
                                            <div class="ays_image_edit_div" style="position: absolute;"><i class="ays-move-images"></i></div>
                                            <div class='ays_image_thumb_img'><?php echo $img_thmb_html; ?></div>
                                        </div>
                                    </div>
                                    <div class="ays_image_attr_item_cat">
                                        <div class="ays_image_attr_item_parent">
                                            <div>
                                                <?php echo __("Title: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr($img_title)); ?></b>
                                                <input type="hidden" name="ays-image-title[]" value="<?php echo stripslashes(esc_attr($img_date)); ?>"/>
                                            </div>
                                            <div>
                                                <?php echo __("Description: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr("$img_description")); ?></b>
                                                <input type="hidden" name="ays-image-description[]" value="<?php echo stripslashes(esc_attr("$img_description")); ?>"/>
                                            </div>
                                            <div>
                                                <?php echo __("Category: ", $this->plugin_name);?>
                                                <b><?php echo stripslashes(esc_attr("$category")); ?></b>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ays-image-date[]" class="ays_img_date" value="<?php echo $img_date; ?>" /> 
                                    <div class="ays_del_li_div"><input type="checkbox" class="ays_del_li"/></div>
                                    <div class='ays-delete-image_div'><i class="ays-delete-image"></i></div>
                                </div>
                            </li>
                            <?php
                            }
                        }
                    } // end foreach image
                    endif;
                ?>
                </ul>
                </div>
            </div> <!-- end image_selection -->
            <div id="image_query">

                <hr/>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="ays_filter_cat">
                            <?php echo __("Select categories", $this->plugin_name);?>
                            <span class="ays_option_note">
                                <?php echo __("Leave empty will display zero photos", $this->plugin_name);?>
                            </span>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <select class="ays-category form-control" multiple="multiple">
                            <?php
                            $gal_cats_ids = $query_categories != '' ? explode( ",", $query_categories) : array();
                            
                            foreach ( $gallery_categories as $term ) {
                                $checked = (in_array($term->term_id, $gal_cats_ids)) ? "selected" : "";
                                echo "<option value='".$term->term_id."' ".$checked.">".$term->name."</option>";
                            }                                            
                            ?>
                        </select>
                        <input type="hidden" class="for_select_name" name="ays_query_category[]">
                    </div>
                </div>

            </div><!-- end image_query -->

            <div class="ays_admin_pages">
                <ul>
                    <?php
                        if($admin_pagination != "all"){
                            if($pages > 0){
                                for($page = 0; $page < $pages; $page++ ){
                                    if(isset($_COOKIE['glp_page_tab_free']) && $_COOKIE['glp_page_tab_free'] == "tab_".($page)){
                                        $page_active = 'ays_page_active';
                                    }else{                                        
                                        $page_active = '';
                                    }
                                    echo "<li><a class='ays_page $page_active' data-tab='tab_".($page)."' href='#page_".($page)."'>".($page+1)."</a></li>";
                                }
                            }
                        }
                    ?>
                </ul>
            </div>
		</div>
        <?php
            $view_type_names = array(
                "grid"      => "grid.PNG",
                "mosaic"    => "mosaic.png",
                "masonry"   => "masonry.png"
            );

        ?>
		<div id="tab2" class="ays-gallery-tab-content <?php echo ($glp_tab == 'tab2') ? 'ays-gallery-tab-content-active' : ''; ?>">
            <h6 class="ays-subtitle"><?php echo  __('Gallery options', $this->plugin_name) ?>
            <span class="ays_title_note">
                <?php echo __("Select the gallery general appearance and the way to load the images", $this->plugin_name);?>
            </span>
            </h6>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Gallery view type", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <div>
                        <?php
                            foreach($view_type_names as $key => $name):
                        ?>
                        <label class="glp_view_type_radio">
                            <input type="radio" class="ays-view-type" name="ays-view-type" 
                                   <?php echo ($glp_view_type == $key) ? "checked" : ""; ?>
                                   <?php echo ("grid" == $key || "mosaic" == $key || "masonry" == $key) ? "" : "disabled"; ?>
                                   value="<?php echo $key; ?>"/>
                            <?php if($key == "grid" || $key == "mosaic" || $key == "masonry"): ?>
                                <span><?php echo __( ucfirst($key)." ", $this->plugin_name);?></span>
                            <?php endif; ?>
                            <?php if($key == "grid" || $key == "mosaic" || $key == "masonry"): ?>
                                <img src="<?php echo GLP_ADMIN_URL . "images/" . $name; ?>">
                            <?php endif; ?>
                        </label>
                        <?php
                            endforeach;
                        ?>
                    </div>
                </div>
            </div>
           
            <?php
                $bacel = $gal_options['view_type'] == 'grid' ? "style='display: flex;'" : "style='display: none;'";
                $resp_width = $responsive_width == "on" && $gal_options['view_type'] == 'grid' ? true : false;

                if ($resp_width) {
                    $height_width_ratio = "style='display: flex;'";
                    $thumb_height = "style='display: none;'";
                }else{
                    $height_width_ratio = "style='display: none;'";
                    $thumb_height = "style='display: flex;'";
                }

                switch ($gal_options['view_type']) {
                    case 'mosaic':
                        $pakel1 = "style='display: none;'";
                        $pakel2 = "style='display: none;'";
                        break;
                    case 'masonry':
                        $pakel1 = "style='display: none;'";
                        $pakel2 = "style='display: block;'";
                        break;
                    
                    default:
                        $pakel1 = "style='display: block;'";
                        $pakel2 = "style='display: block;'";
                        break;
                }
                
            ?>
            <hr class="hr_pakel" <?php echo $pakel1;?> >
            <!-- TODO Thumbnail height only for grids, what is it for ??? -->
            <div id="ays-thumb-height" class="form-group row pakel3" <?php echo $thumb_height;?>>
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Thumbnails height", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9" style="border-left:1px solid #ccc;">                   
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label for="ays_thumb_height_mobile">
                                <?php echo __("For mobile:", $this->plugin_name);?>
                            </label>
                        </div>                        
                        <div class="col-sm-9 glp_display_flex_width">
                            <div>
                               <input type="number" id="ays_thumb_height_mobile" name="ays-thumb-height-mobile" class="ays-text-input ays-text-input-short" value="<?php echo $ays_thumb_height_mobile; ?>"/>
                            </div>
                            <div class="glp_dropdown_max_width">
                                <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label for="ays_thumb_height_desktop">
                                <?php echo __("For desktop:", $this->plugin_name);?>
                            </label>
                        </div>
                        <div class="col-sm-9 glp_display_flex_width">
                            <div>
                               <input type="number" id="ays_thumb_height_desktop" name="ays-thumb-height-desktop" class="ays-text-input ays-text-input-short" value="<?php echo $ays_thumb_height_desktop; ?>"/>
                            </div>
                            <div class="glp_dropdown_max_width">
                                <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            <div id="glp-columns-count" class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Columns count", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input type="number" name="glp-columns-count" class="ays-text-input ays-text-input-short" placeholder="<?php echo __("Default", $this->plugin_name);?>: 3" value="<?php echo isset($gal_options['columns_count']) ? $gal_options['columns_count'] : 3; ?>"/>
                </div>
            </div>
            <div id="glp-mosaic-row-size" class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Row height", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="glp-mosaic-row-size" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $mosaic_row_size; ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <hr>            
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="ays_images_ordering">
                        <?php echo __("Images order by", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-3">				
                    <select name="ays_images_ordering" class="ays-text-input ays-text-input-short" id="ays_images_ordering">
                        <option <?php echo ($gal_options['images_orderby'] == "noordering") ? "selected" : ""; ?> value="noordering"><?php echo __("No ordering", $this->plugin_name);?></option>
                        <option <?php echo ($gal_options['images_orderby'] == "title") ? "selected" : ""; ?> value="title"><?php echo __("Title", $this->plugin_name);?></option>
                        <option <?php echo ($gal_options['images_orderby'] == "date") ? "selected" : ""; ?> value="date"><?php echo __("Date", $this->plugin_name);?></option>
                        <option <?php echo ($gal_options['images_orderby'] == "random") ? "selected" : ""; ?> value="random"><?php echo __("Random", $this->plugin_name);?></option>
                    </select>
                </div>
                <div class="col-sm-6">                
                    <select name="glp_ordering_asc_desc" class="ays-text-input ays-text-input-short" id="glp_ordering_asc_desc" <?php echo ($gal_options['images_orderby'] == "random" || $gal_options['images_orderby'] == "noordering") ? "style='display:none;'" : ""; ?>>
                        <option <?php echo ($ordering_asc_desc == "ascending") ? "selected" : ""; ?> value="ascending"><?php echo __("Ascending", $this->plugin_name);?></option>
                        <option <?php echo ($ordering_asc_desc == "descending") ? "selected" : ""; ?> value="descending"><?php echo __("Descending", $this->plugin_name);?></option>
                    </select>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Images loading", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <div>
                        <label class="glp_image_hover_icon" id="gpg_image_global_loading"><?php echo __("Global loading ", $this->plugin_name);?>
                            <input type="radio" id="glp_images_global_loading" name="ays_images_loading" value="load_all"
                            <?php echo ($loading_type == "load_all") ? "checked" : ""; ?> />
                        </label>
                        <label class="glp_image_hover_icon" id="gpg_image_lazy_loading"><?php echo __("Lazy loading ", $this->plugin_name);?>
                            <input type="radio" id="glp_images_lazy_loading" name="ays_images_loading" value="lazy_load" 
                            <?php echo ($loading_type == "lazy_load") ? "checked" : ""; ?>/>
                        </label>
                    </div>
                </div>
            </div>
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Vignette options', $this->plugin_name) ?>
            <span class="ays_title_note">
                <?php echo __("Select where to display the vignettes", $this->plugin_name);?>
            </span>
            </h6>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Vignette display", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <div>
                        <label class="glp_image_hover_icon glp_vignette_display"><?php echo __("Permanent ", $this->plugin_name); ?>
                            <input name="glp_vignette_display" type="radio" value="permanent" <?php echo ($ays_vignette_display == "permanent") ? "checked" : ''; ?>>
                        </label>
                        <label class="glp_image_hover_icon glp_vignette_display"><?php echo __("When hover ", $this->plugin_name); ?>
                            <input name="glp_vignette_display" type="radio" value="hover" <?php echo ($ays_vignette_display == "hover") ? "checked" : ''; ?>>
                        </label>
                    </div>                
                </div>
            </div>
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Border options', $this->plugin_name) ?>
            <span class="ays_title_note">
                <?php echo __("Select the size and the color of the border between images", $this->plugin_name);?>
            </span>
            </h6>    
            <hr/>            
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Distance inter images", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="glp-images-distance" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $images_distance; ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="glp_images_border">
                        <?php echo __("Add border", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input id="glp_images_border" name="glp_images_border" class="" type="checkbox" <?php echo ($ays_images_border == 'on') ? 'checked' : ''; ?>>
                    <div class="glp_border_options">
                        <div class="glp_border_options_div glp_display_flex_width">
                            <div>
                               <input type="number" class="glp_images_border_width" style="width: 50px;" min="0" max="10" maxlength="2" name="glp_images_border_width" value="<?php echo $ays_images_border_width; ?>" onkeypress="if(this.value.length==2) return false;">
                            </div>
                            <div class="glp_dropdown_max_width">
                                <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                            </div>                            
                        </div>
                        <div class="glp_border_options_div gpg_images_border_style">
                            <select name="glp_images_border_style">
                            <option value="solid" <?php echo $ays_images_border_style == "solid" ? 'selected' : ''; ?>>Solid</option>
                            <option value="dashed" <?php echo $ays_images_border_style == "dashed" ? 'selected' : ''; ?>>Dashed</option>
                            <option value="dotted" <?php echo $ays_images_border_style == "dotted" ? 'selected' : ''; ?>>Dotted</option>
                            <option value="double" <?php echo $ays_images_border_style == "double" ? 'selected' : ''; ?>>Double</option>
                            <option value="groove" <?php echo $ays_images_border_style == "groove" ? 'selected' : ''; ?>>Groove</option>
                            <option value="ridge" <?php echo $ays_images_border_style == "ridge" ? 'selected' : ''; ?>>Ridge</option>
                            <option value="inset" <?php echo $ays_images_border_style == "inset" ? 'selected' : ''; ?>>Inset</option>
                            <option value="outset" <?php echo $ays_images_border_style == "outset" ? 'selected' : ''; ?>>Outset</option>
                            <option value="none" <?php echo $ays_images_border_style == "none" ? 'selected' : ''; ?>>None</option>
                        </select>
                        </div>
                        <div class="glp_border_options_div">
                            <input name="glp_border_color" class="glp_border_color" type="text" data-alpha="true" value="<?php echo $ays_images_border_color; ?>" data-default-color="#000000">
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Border radius", $this->plugin_name);?>
                    </label>
                </div>                
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="glp-images-border-radius" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $glp_border_radius ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Title options', $this->plugin_name) ?>
            <span class="ays_title_note">
                <?php echo __("Display or not the image titles and select the colors", $this->plugin_name);?>
            </span>
            </h6>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="show_title">
                        <?php echo __("Show title", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <input type="checkbox" id="show_title" class="" name="glp_show_title" <?php echo ($gal_options['show_title'] == "on") ? "checked" : ""; ?>/>
                        </div>
                        <div class="col-sm-9 show_with_date">
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label >
                                        <?php echo __("Show on", $this->plugin_name); ?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <label class="glp_image_hover_icon"><?php echo __( "Thumbnail hover ", $this->plugin_name); ?><input type="radio" class="glp_show_title_on" name="glp_show_title_on" <?php echo ($show_thumb_title_on == "image_hover") ? "checked" : ""; ?> value="image_hover"/></label>
                                    <label class="glp_image_hover_icon"><?php echo __( "Gallery thumbnail ", $this->plugin_name); ?><input type="radio" class="glp_show_title_on" name="glp_show_title_on" <?php echo ($show_thumb_title_on == "gallery_image") ? "checked" : ""; ?> value="gallery_image"/></label>
                                </div>                                
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label >
                                        <?php echo __("Image title position", $this->plugin_name);?>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <label class="glp_image_hover_icon"><?php echo __("Bottom", $this->plugin_name);?> <input type="radio" class="image_title_position_bottom" name="image_title_position" <?php echo ($thumb_title_position == "bottom") ? "checked" : ""; ?> value="bottom"/></label>
                                    <label class="glp_image_hover_icon"><?php echo __("Top", $this->plugin_name);?> <input type="radio" class="image_title_position_top" name="image_title_position" <?php echo ($thumb_title_position == "top") ? "checked" : ""; ?> value="top"/></label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label><?php echo __( "Show with date ", $this->plugin_name); ?>
                                    </label>
                                </div>    
                                <div class="col-sm-8">
                                    <label class="glp_image_hover_icon"><?php echo __("Yes", $this->plugin_name);?> <input type="radio" class="image_title_position_bottom" name="glp_show_with_date" <?php echo ($gal_options['show_with_date'] == "on") ? "checked" : ""; ?> value="on"/></label>
                                    <label class="glp_image_hover_icon"><?php echo __("No", $this->plugin_name);?> <input type="radio" class="image_title_position_bottom" name="glp_show_with_date" <?php echo ($gal_options['show_with_date'] == "") ? "checked" : ""; ?> value=""/></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>   
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Title size", $this->plugin_name);?>
                    </label>
                </div>               
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="glp_thumbnail_title_size" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $glp_thumbnail_title_size; ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <hr/>

            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Title background color", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input name="glp-lightbox-color" class="glp_lightbox_color" data-alpha="true" type="text" value="<?php echo isset($gal_options['lightbox_color']) ? esc_attr(stripslashes($gal_options['lightbox_color'])) : 'rgba(0,0,0,0)'; ?>" data-default-color="rgba(0,0,0,0)">
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for='glp_thumbnail_title_color'>
                        <?php echo __("Title text color", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input id="glp_thumbnail_title_color" name="glp_thumbnail_title_color" data-alpha="true" type="text" value="<?php echo $thumbnail_title_color; ?>" data-default-color="#ffffff">
                </div>
            </div>
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Mouse hover options', $this->plugin_name) ?>
                <span class="ays_title_note">
                    <?php echo __("Set the behavior when the mouse slides hover an image", $this->plugin_name);?>
                </span>
            </h6>
            <hr/>            
            <div class="ays-field ays_effect_simple">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="gallery_img_hover_simple">
                            <?php echo __("Hover animation", $this->plugin_name);?>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <select id="gallery_img_hover_simple" class="ays-text-input ays-text-input-short" name="ays_hover_simple">
                            <optgroup label="Fading Entrances">
                                <option <?php echo 'fadeIn' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="fadeIn">Fade In</option>
                                <option <?php echo 'fadeInDown' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="fadeInDown">Fade In Down</option>
                                <option <?php echo 'fadeInLeft' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="fadeInLeft">Fade In Left</option>
                                <option <?php echo 'fadeInRight' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="fadeInRight">Fade In Right</option>
                                <option <?php echo 'fadeInUp' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="fadeInUp">Fade In Up</option>
                            </optgroup>
                            <optgroup label="Sliding Entrances">
                                <option <?php echo ($gal_options['hover_effect'] == "slideInUp") ? "selected" : ""; ?> value="slideInUp"><?php echo __("Slide Up", $this->plugin_name);?></option>
                                <option <?php echo ($gal_options['hover_effect'] == "slideInDown") ? "selected" : ""; ?> value="slideInDown"><?php echo __("Slide Down", $this->plugin_name);?></option>
                                <option <?php echo ($gal_options['hover_effect'] == "slideInLeft") ? "selected" : ""; ?> value="slideInLeft"><?php echo __("Slide Left", $this->plugin_name);?></option>
                                <option <?php echo ($gal_options['hover_effect'] == "slideInRight") ? "selected" : ""; ?> value="slideInRight"><?php echo __("Slide Right", $this->plugin_name);?></option>
                            </optgroup>                                
                            <optgroup label="Zoom Entrances">
                                <option <?php echo 'zoomIn' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="zoomIn">Zoom In</option> 
                                <option <?php echo 'zoomInDown' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="zoomInDown">Zoom In Down</option> 
                                <option <?php echo 'zoomInLeft' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="zoomInLeft">Zoom In Left</option> 
                                <option <?php echo 'zoomInRight' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="zoomInRight">Zoom In Right</option> 
                                <option <?php echo 'zoomInUp' == $gal_options['hover_effect'] ? 'selected' : ''; ?> value="zoomInUp">Zoom In Up</option> 
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <div class="gpg_animation_demo">
                            <div class="gpg_animation_demo_text ">
                                <?php echo __("Hover animation preview", $this->plugin_name);?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 ays_animation_preview_block"> 
                        <a class="ays_animation_preview">                            
                            <i class="far fa-eye"></i>
                        </a>
                    </div>
                </div>
                <hr/>
            </div>
            <div class="ays-field ays_effect_dir_aware">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="gallery_img_hover_dir_aware">
                            <?php echo __("Hover animation", $this->plugin_name);?>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <select id="gallery_img_hover_dir_aware" class="ays-text-input ays-text-input-short" name="ays_hover_dir_aware">
                            <option <?php echo 'slide' == $ays_images_hover_dir_aware ? 'selected' : ''; ?> value="slide"><?php echo __("Slide", $this->plugin_name);?></option>
                            <option <?php echo 'rotate3d' == $ays_images_hover_dir_aware ? 'selected' : ''; ?> value="rotate3d"><?php echo __("Rotate 3D", $this->plugin_name);?></option>
                        </select>
                    </div>
                    <div class="col-sm-6" style="position: initial;">                    
                        <div class="gpg_animation_demo_dAware demo_<?php echo $ays_images_hover_dir_aware; ?>">
                            <div class="text_before_effect gpg_animation_demo_text" style="background: revert; color:#3c434a;">
                                <?php echo __("Hover this preview block", $this->plugin_name);?>
                            </div>
                            <div class="gpg_animation_demo_text ays_hover_mask">
                                <?php echo __("Hover animation preview", $this->plugin_name);?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
            </div>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="glp_hover_animation_speed">
                        <span>
                            <?php echo  __('Animation speed', $this->plugin_name) ?>
                        </span>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input id="glp_hover_animation_speed" type="number" class="ays-text-input ays-text-input-short" name="glp_hover_animation_speed" value="<?php echo $hover_animation_speed; ?>" step="0.1">
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Hover opacity", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-3 gpg_range_div">
                    <div>
                        <input class="gpg_opacity_demo_val form-control-range" id="formControlRange" name="glp-image-hover-opacity" type="range" min="0" max="1" step="0.01" value="<?php echo isset($gal_options['hover_opacity']) ? $gal_options['hover_opacity'] : '0.5'; ?>">
                    </div>                    
                </div>
                <div class="col-sm-6">                    
                    <div class="gpg_opacity_demo"><?php echo __("Hover opacity preview", $this->plugin_name);?></div>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Hover color", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input name="glp-hover-color" class="glp_hover_color" data-alpha="true" type="text" value="<?php echo isset($gal_options['hover_color']) ? esc_attr(stripslashes($gal_options['hover_color'])) : '#000000'; ?>" data-default-color="#000000">
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Hover zoom icon", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <?php 
                        if($gal_options['hover_icon'] == false || $gal_options['hover_opacity'] == ''){
                            $ays_hover_icon = 'search_plus';
                        }else{
                            $ays_hover_icon = $gal_options['hover_icon'];
                        }
                    ?>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="none" <?php echo $ays_hover_icon == 'none' ? 'checked' : ''; ?> />
                        <i>None</i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="search_plus" <?php echo $ays_hover_icon == 'search_plus' ? 'checked' : ''; ?> />
                        <i class="fas fa-search-plus"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="search" <?php echo $ays_hover_icon == 'search' ? 'checked' : ''; ?>/>
                        <i class="fas fa-search"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="plus" <?php echo $ays_hover_icon == 'plus' ? 'checked' : ''; ?>/>
                        <i class="fas fa-plus"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="plus_circle" <?php echo $ays_hover_icon == 'plus_circle' ? 'checked' : ''; ?>/>
                        <i class="fas fa-plus-circle"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="plus_square_fas" <?php echo $ays_hover_icon == 'plus_square_fas' ? 'checked' : ''; ?>/>
                        <i class="fas fa-plus-square"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="plus_square_far" <?php echo $ays_hover_icon == 'plus_square_far' ? 'checked' : ''; ?>/>
                        <i class="far fa-plus-square"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="expand" <?php echo $ays_hover_icon == 'expand' ? 'checked' : ''; ?>/>
                        <i class="fas fa-expand"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="image_fas" <?php echo $ays_hover_icon == 'image_fas' ? 'checked' : ''; ?>/>
                        <i class="fas fa-image"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="image_far" <?php echo $ays_hover_icon == 'image_far' ? 'checked' : ''; ?>/>
                        <i class="far fa-image"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="images_fas" <?php echo $ays_hover_icon == 'images_fas' ? 'checked' : ''; ?>/>
                        <i class="fas fa-images"></i>
                    </label>
                    <label class="glp_image_hover_icon">
                        <input type="radio" name="glp-image-hover-icon" value="images_far" <?php echo $ays_hover_icon == 'images_far' ? 'checked' : ''; ?>/>
                        <i class="far fa-images"></i>
                    </label>
                    <p class="glp_image_hover_icon_text"><span><?php echo __("Select icon for the gallery images", $this->plugin_name);?></span></p>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Hover zoom icon size", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="glp-hover-icon-size" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $glp_hover_icon_size; ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Lightbox options', $this->plugin_name) ?>
            <span class="ays_title_note">
                    <?php echo __("Set the behavior of the lightbox, displayed when zooming on an image", $this->plugin_name);?>
                </span>
            </h6>

            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="light_box">
                        <?php echo __("Disable lightbox", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input type="checkbox" id="light_box" class="" name="av_light_box" <?php echo (isset($gal_options['enable_light_box']) && $gal_options['enable_light_box'] == "on") ? "checked" : ""; ?> />
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-2">
                    <label>
                        <?php echo __("Images counter", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-10">
                    <label class="glp_image_hover_icon"><?php echo __("Enable ", $this->plugin_name);?>
                        <input type="radio" class="" name="glp_lightbox_counter" <?php echo ($glp_lightbox_counter == "true") ? "checked" : ""; ?> value="true"/>
                    </label>
                    <label class="glp_image_hover_icon"><?php echo __("Disable ", $this->plugin_name);?> 
                        <input type="radio" class="" name="glp_lightbox_counter" <?php echo ($glp_lightbox_counter == "false") ? "checked" : ""; ?> value="false"/>
                    </label>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-2">
                    <label>
                        <?php echo __("Show caption in lightbox", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-10">
                    <label class="glp_image_hover_icon"><?php echo __("Enable ", $this->plugin_name);?>
                        <input type="radio" class="" name="glp_show_caption" <?php echo ($glp_show_caption == "true") ? "checked" : ""; ?> value="true"/>
                    </label>
                    <label class="glp_image_hover_icon"><?php echo __("Disable ", $this->plugin_name);?> 
                        <input type="radio" class="" name="glp_show_caption" <?php echo ($glp_show_caption == "false") ? "checked" : ""; ?> value="false"/>
                    </label>
                </div>
            </div>
            <hr/>
            <div class="form-group row">
                <div class="col-sm-2">
                    <label>
                        <?php echo __("Images slide show", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-2">
                    <label class="glp_image_hover_icon"><?php echo __("Enable ", $this->plugin_name);?>
                        <input type="radio" class="ays_enable_disable" name="glp_lightbox_autoplay" <?php echo ($glp_lightbox_autoplay == "true") ? "checked" : ""; ?> value="true"/>
                    </label>
                    <label class="glp_image_hover_icon"><?php echo __("Disable ", $this->plugin_name);?> 
                        <input type="radio" class="ays_enable_disable" name="glp_lightbox_autoplay" <?php echo ($glp_lightbox_autoplay == "false") ? "checked" : ""; ?> value="false"/>
                    </label>
                </div>
                <div class="col-sm-8 ays_hidden">
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>
                                <?php echo __("Slide duration", $this->plugin_name);?>
                            </label>
                        </div>                        
                        <div class="col-sm-9 glp_display_flex_width">
                            <div>
                               <input type="number" class="ays-text-input" name="glp_lightbox_pause" value="<?php echo $glp_lightbox_pause; ?>" />
                                <span class="glp_image_hover_icon_text"><?php echo __("1 sec = 1000 ms", $this->plugin_name);?></span>
                            </div>
                            <div class="glp_dropdown_max_width">
                                <input type="text" value="ms" class="glp-form-hint-for-size" disabled="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>

            <div class="form-group row">
                <div class="col-sm-2">
                    <label><?php echo __("Allow key control", $this->plugin_name);?></label>
                </div>
                <div class="col-sm-2">
                    <label class="glp_image_hover_icon"><?php echo __("Enable ", $this->plugin_name);?>
                        <input type="radio" class="ays_enable_disable" name="glp_lg_keypress" <?php echo ($glp_lg_keypress == "true") ? "checked" : ""; ?> value="true"/>
                    </label>
                    <label class="glp_image_hover_icon"><?php echo __("Disable ", $this->plugin_name);?> 
                        <input type="radio" class="ays_enable_disable" name="glp_lg_keypress" <?php echo ($glp_lg_keypress == "false") ? "checked" : ""; ?> value="false"/>
                    </label>
                </div>
                <div class="col-sm-8 ays_hidden">
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label><?php echo __("Allow Esc key", $this->plugin_name);?></label>
                        </div>
                        <div class="col-sm-8">
                            <label class="glp_image_hover_icon"><?php echo __("Enable ", $this->plugin_name);?>
                                <input type="radio" class="" name="glp_lg_esckey" <?php echo ($glp_lg_esckey == "true") ? "checked" : ""; ?> value="true"/>
                            </label>
                            <label class="glp_image_hover_icon"><?php echo __("Disable ", $this->plugin_name);?> 
                                <input type="radio" class="" name="glp_lg_esckey" <?php echo ($glp_lg_esckey == "false") ? "checked" : ""; ?> value="false"/>
                            </label>
                        </div>
                    </div>                    
                </div>
            </div>            
            <hr class="ays_option_separation">
            <h6 class="ays-subtitle"><?php echo  __('Various options', $this->plugin_name) ?></h6>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="ays_galery_enable_rtl_direction">
                        <?php echo __('Right To Left text direction',$this->plugin_name)?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input type="checkbox" class="ays-enable-timerl" id="ays_galery_enable_rtl_direction"
                        name="ays_galery_enable_rtl_direction"
                        value="on" <?php echo ($enable_rtl_direction == 'on') ? 'checked' : '';?>/>
                </div>
            </div> <!-- Use RTL direction -->
            <hr/>
            <div class="form-group row ays-galleries-button-box">
                <div class="ays-question-button-first-row" style="padding: 0;">
                <?php
                    wp_nonce_field('ays_gallery_action', 'ays_gallery_action');
                    $other_attributes = array();
                    $buttons_html = '';
                    $buttons_html .= '<div class="ays_save_buttons_content">';
                        $buttons_html .= '<div class="ays_submit_button ays_save_buttons_box">';
                        echo $buttons_html;
                ?>
                    <input type="submit" name="ays-submit" class="button ays-submit ays-button button-primary glp-save-comp" value="<?php echo __("Save and close", $this->plugin_name);?>" gpg_submit_name="ays-submit" />            
                    <input type="submit" name="ays-apply" id="ays_submit_apply" class="button ays-button ays-submit glp-save-comp" title="Ctrl + s" data-toggle="tooltip" data-delay='{"show":"1000"}' value="<?php echo __("Save", $this->plugin_name);?>" gpg_submit_name="ays-apply"/>
                    <?php echo $loader_iamge; ?> 
                <?php
                            
                        $buttons_html = '</div>';
                        echo $buttons_html;
                    $buttons_html = "</div>";
                    echo $buttons_html; 
                ?>
                </div>
                <div class="ays-gallery-button-second-row">
                <?php
                    if ( $prev_gallery_id != "" && !is_null( $prev_gallery_id ) ) {

                        $other_attributes = array(
                            'id' => 'ays-gallery-prev-button',
                            'href' => sprintf( '?page=%s&action=%s&gallery=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $prev_gallery_id ) )
                        );
                        submit_button(__('Previous Gallery', $this->plugin_name), 'button button-primary ays-button ays-gallery-loader-banner', 'ays_gallery_prev_button', false, $other_attributes);
                    }
                    if ( $next_gallery_id != "" && !is_null( $next_gallery_id ) ) {

                        $other_attributes = array(
                            'id' => 'ays-gallery-next-button',
                            'href' => sprintf( '?page=%s&action=%s&gallery=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $next_gallery_id ) )
                        );
                        submit_button(__('Next Gallery', $this->plugin_name), 'button button-primary ays-button ays-gallery-loader-banner', 'ays_gallery_next_button', false, $other_attributes);
                    }
                ?>
                </div>
            </div>            
            <button class="ays_gallery_live_save" type="submit" name="ays-apply"><i class="far fa-save" gpg_submit_name="ays-apply"></i></button>
            <input type="hidden" id="glp_admin_url" value="<?php echo GLP_ADMIN_URL; ?>"/>
		</div>
    </form>
    
    <!--  Start modal preview -->
    <div class="ays_gallery_live_preview_popup">
        <a class="ays_live_preview_close"><i class="glp_fa glp_fa_times_circle"></i></a>
        <div class='ays_gallery_container'>
        </div>
        <div class="glp_live_overlay"></div>
    </div>
    <!--  End modal preview -->
</div>