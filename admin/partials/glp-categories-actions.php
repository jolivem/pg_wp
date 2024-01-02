<?php
    $gpg_settings = new Gallery_Settings_Actions($this->plugin_name);
    $action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';
    $heading = '';
    $id = ( isset( $_GET['gallery_category'] ) ) ? absint( sanitize_text_field( $_GET['gallery_category'] ) ) : null;
    $gallery_category = array(
        'id'            => '',
        'title'         => '',
        'description'   => ''
    );
    switch( $action ) {
        case 'add':
            $heading = __('Add new category', $this->plugin_name);
            break;
        case 'edit':
            $heading = __('Edit category', $this->plugin_name);
            $gallery_category = $this->cats_obj->get_gallery_category( $id );
            break;
    }
    if( isset( $_POST['ays_submit'] ) ) {
        $_POST['id'] = $id;
        $result = $this->cats_obj->add_edit_gallery_category( $_POST );
    }
    if(isset($_POST['ays_apply'])){
        $_POST["id"] = $id;
        $_POST['ays_change_type'] = 'apply';
        $this->cats_obj->add_edit_gallery_category($_POST);
    }

    // General Settings | options
    $gen_options = ($gpg_settings->ays_get_setting('options') === false) ? array() : json_decode( stripcslashes($gpg_settings->ays_get_setting('options') ), true);

    // WP Editor height
    $gpg_wp_editor_height = (isset($gen_options['gpg_wp_editor_height']) && $gen_options['gpg_wp_editor_height'] != '') ? absint( sanitize_text_field($gen_options['gpg_wp_editor_height']) ) : 100 ;

    $next_gallery_cat_id = "";
    if ( isset( $id ) && !is_null( $id ) ) {
        $next_gallery_cat = $this->get_next_or_prev_gallery_cat_by_id( $id, "next" );
        $next_gallery_cat_id = (isset( $next_gallery_cat['id'] ) && $next_gallery_cat['id'] != "") ? absint( $next_gallery_cat['id'] ) : null;
    }

    $loader_iamge = "<span class='display_none glp_loader_box'><img src='". AYS_GPG_ADMIN_URL ."/images/loaders/loading.gif'></span>";
?>
<div class="wrap">
    <div class="glp-heading-box">
        <div class="glp-wordpress-user-manual-box">
            <a href="https://ays-pro.com/wordpress-photo-gallery-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                <i class="ays_fa ays_fa_file_text"></i>
                <span style="margin-left: 3px;text-decoration: underline;"><?php echo __("View Documentation", $this->plugin_name); ?></span>
            </a>
        </div>
    </div>
    <div class="container-fluid">
        <h1><?php echo $heading; ?></h1>
        <hr/>
        <form class="glp-category-form" id="glp-category-form" method="post">
            <div class="form-group row">
                <div class="col-sm-2">
                    <label for='ays-title'>
                        <?php echo __('Title', $this->plugin_name); ?>
                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Title of the category',$this->plugin_name)?>">
                            <i class="ays_fa ays_fa_info_circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-sm-10">
                    <input class='ays-text-input' id='ays-title' name='ays_title' required type='text' value='<?php echo esc_attr(stripslashes( $gallery_category['title'])); ?>'>
                </div>
            </div>

            <hr/>
            <div class='ays-field'>
                <label for='ays-description'>
                    <?php echo __('Description', $this->plugin_name); ?>
                    <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Provide more information about the gallery category',$this->plugin_name)?>">
                        <i class="ays_fa ays_fa_info_circle"></i>
                    </a>
                </label>
                <?php
                $content = (stripslashes(htmlentities($gallery_category['description'])));
                $editor_id = 'glp-description';
                $settings = array('editor_height'=>$gpg_wp_editor_height,'textarea_name'=>'ays_description','editor_class'=>'ays-textarea');
                wp_editor($content,$editor_id,$settings);
                ?>
            </div>

            <hr/>
            <div class="form-group row ays-galleries-button-box">
                <div class="ays-question-button-first-row" style="padding: 0;">
                <?php
                    wp_nonce_field('gallery_category_action', 'gallery_category_action');
                    $other_attributes = array( 'id' => 'ays-button' );
                    $buttons_html = '';
                    $buttons_html .= '<div class="ays_save_buttons_content">';
                        $buttons_html .= '<div class="ays_submit_button ays_save_buttons_box">';
                        echo $buttons_html;

                    $save_attributes = array(
                        'title' => 'Ctrl + s',
                        'data-toggle' => 'tooltip',
                        'data-delay'=> '{"show":"1000"}'
                    );

                    $other_attributes = array( 'id' => 'ays-button' );
                    submit_button( __( 'Save and close', $this->plugin_name ), 'primary glp-save-comp', 'ays_submit', true, $other_attributes );
                    submit_button(__('Save', $this->plugin_name), 'glp-save-comp', 'ays_apply', false, $save_attributes);
                    echo $loader_iamge;
                            
                    $buttons_html = '</div>';
                    echo $buttons_html;
                    $buttons_html = "</div>";
                    echo $buttons_html; 
                ?>
                </div>
                <div class="ays-gallery-button-second-row">
                <?php                                   
                    if ( $next_gallery_cat_id != "" && !is_null( $next_gallery_cat_id ) ) {

                        $other_attributes = array(
                            'id' => 'ays-gallery-next-button',
                            'href' => sprintf( '?page=%s&action=%s&gallery_category=%d', esc_attr( $_REQUEST['page'] ), 'edit', absint( $next_gallery_cat_id ) )
                        );
                        submit_button(__('Next Category', $this->plugin_name), 'button button-primary ays-button ays-gallery-loader-banner', 'ays_gallery_next_button', false, $other_attributes);
                    }
                ?>
                </div>
            </div>
        </form>
    </div>
</div>