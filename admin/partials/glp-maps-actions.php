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
$id = ( isset( $_GET['map'] ) ) ? absint( sanitize_text_field( $_GET['map'] ) ) : null;

$user_id = get_current_user_id();
$user = get_userdata($user_id);
$author = array(
    'id' => $user->ID,
    'name' => $user->data->display_name
);
$m_options = array(
    "lightbox_options"      => "",
    "gallery_id"            => "",
    "tile_provider"         => "",
    "create_date"           => current_time( 'mysql' ),
    "author"                => $author,
    'gpg_create_author'     => $user_id,
);


// initiate new map
$map = array(
    "id"                => "",
    "title"             => "Map title",
    "options"           => json_encode($m_options,true)
);
switch( $action ) {
    case 'add':
        $heading = __('Add new map', $this->plugin_name);
        break;
    case 'edit':
        $heading = __('Edit map', $this->plugin_name);
        $map = $this->map_obj->get_map_by_id($id);
        break;
}

if(isset($_POST["ays-submit"]) || isset($_POST["ays-submit-top"])){
    $_POST["id"] = $id;
    $this->map_obj->add_or_edit_map($_POST);
}
if(isset($_POST["ays-apply"]) || isset($_POST["ays-apply-top"])){
    $_POST["id"] = $id;
    $_POST["submit_type"] = 'apply';
    $this->map_obj->add_or_edit_map($_POST);
}

$next_map_id = "";
$prev_map_id = "";
if ( isset( $id ) && !is_null( $id ) ) {
    $next_map = $this->get_next_or_prev_map_by_id( $id, "next" );
    $next_map_id = (isset( $next_map['id'] ) && $next_map['id'] != "") ? absint( $next_map['id'] ) : null;
    $prev_map = $this->get_next_or_prev_map_by_id( $id, "prev" );
    $prev_map_id = (isset( $prev_map['id'] ) && $prev_map['id'] != "") ? absint( $prev_map['id'] ) : null;
}

$this_site_path         = trim(get_site_url(), "https:");
$map_options            = json_decode($map['options'], true);
$map_gallery_id         = (isset($map['gallery_id']) && $map['gallery_id'] != '') ? absint( intval( $map['gallery_id'] ) ) : '-1';

$get_all_galleries      = Glp_Gallery_Data::get_galleries();

$map_slider_color       = (!isset($map_options['map_slider_color'])) ? '#000000' : esc_attr(stripslashes( $map_options['map_slider_color'] ));
$map_images_distance    = (isset($map_options['map_images_distance']) && $map_options['map_images_distance'] != '') ? absint( intval( $map_options['map_images_distance'] ) ) : '5';
$map_provider_id        = (isset($map_options['map_provider_id']) && $map_options['map_provider_id'] != 'OSM') ? $map_options['map_provider_id'] : 'OSM';

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
            <h3><?php echo esc_attr( stripslashes( $map["title"] ) ); ?></h3>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="map_title">
                        <?php echo __("Map Title", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input type="text" required name="map_title" id="map_title" class="ays-text-input" placeholder="<?php echo __("Map Title", $this->plugin_name);?>" value="<?php echo stripslashes(htmlentities($map["title"])); ?>"/>
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
                            <strong class="ays-gallery-shortcode-box" onClick="selectElementContents(this)" data-toggle="tooltip" title="<?php echo __('Click for copy.', $this->plugin_name);?>" style="font-size:16px; font-style:normal;"><?php echo "[glp_map id=".$id."]"; ?></strong>
                        </p>
                    </div>
                </div>
                <?php endif;?>
            </div>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="map_gallery_id">
                        <?php echo __("Associated gallery", $this->plugin_name);?>
                    </label>
                </div>

                <div class="col-sm-9">
                    <select name="map_gallery_id" id="map_gallery_id">
                        <?php foreach($get_all_galleries as $var => $var_name):?>
                            <option <?php echo $map_gallery_id == $var_name['id'] ? 'selected' : ''; ?> value="<?php echo $var_name['id']; ?>">
                                <?php echo $var_name['title']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <hr/>
            <h6 class="ays-subtitle"><?php echo  __('Map provider', $this->plugin_name) ?></h6>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="map_provider_id">
                        <?php echo __("Map source", $this->plugin_name);?>
                    </label>
                </div>

                <div class="col-sm-9">
                    <select name="map_provider_id" id="map_provider_id">
                        <option <?php echo 'OSM' == $map_provider_id ? 'selected' : ''; ?> value="OSM"><?php echo  __('OpenStreetMap', $this->plugin_name) ?></option> 
                    </select>


                    <div id="map_provider_option_api">

                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label >
                                    <?php echo __("API key", $this->plugin_name); ?>
                                </label>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" required name="map_provider_apikey" id="map_provider_apikey" class="ays-text-input" placeholder="<?php echo __("999000999888", $this->plugin_name);?>" value="<?php echo stripslashes(htmlentities($map["title"])); ?>"/>                                
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <hr/>

            <h6 class="ays-subtitle"><?php echo  __('Slider options', $this->plugin_name) ?></h6>         
            <div class="form-group row">
                <div class="col-sm-3">
                    <label>
                        <?php echo __("Distance inter images", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9 glp_display_flex_width">
                    <div>
                        <input name="map_images_distance" id="map_images_distance" class="ays-text-input ays-text-input-short" type="number" value="<?php echo $map_images_distance; ?>">
                    </div>
                    <div class="glp_dropdown_max_width">
                        <input type="text" value="px" class="glp-form-hint-for-size" disabled="">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3">
                    <label for="map_slider_color">
                        <?php echo __("Color", $this->plugin_name);?>
                    </label>
                </div>
                <div class="col-sm-9">
                    <input name="map_slider_color" id="map_slider_color" data-alpha="true" type="text" value="<?php echo $map_slider_color; ?>" data-default-color="rgba(0,0,0,0)">
                </div>
            </div>
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
                    if ( $prev_map_id != "" && !is_null( $prev_map_id ) ) {

                        $other_attributes = array(
                            'id' => 'ays-gallery-prev-button',
                            'href' => sprintf( '?page=%s&action=%s&map=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $prev_map_id ) )
                        );
                        submit_button(__('Previous Gallery', $this->plugin_name), 'button button-primary ays-button ays-gallery-loader-banner', 'ays_gallery_prev_button', false, $other_attributes);
                    }
                    if ( $next_map_id != "" && !is_null( $next_map_id ) ) {

                        $other_attributes = array(
                            'id' => 'ays-gallery-next-button',
                            'href' => sprintf( '?page=%s&action=%s&map=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $next_map_id ) )
                        );
                        submit_button(__('Next Gallery', $this->plugin_name), 'button button-primary ays-button ays-gallery-loader-banner', 'ays_gallery_next_button', false, $other_attributes);
                    }
                ?>
                </div>
            </div>
            
            <button class="ays_gallery_live_save" type="submit" name="ays-apply"><i class="far fa-save" gpg_submit_name="ays-apply"></i></button>
            <input type="hidden" id="glp_admin_url" value="<?php echo GLP_ADMIN_URL; ?>"/>
        </form>
    </div>
    
</div>