<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Gallery_Photo_Gallery
 * @subpackage Gallery_Photo_Gallery/admin/partials
 */


    $action = ( isset($_GET['action']) ) ? sanitize_text_field( $_GET['action'] ) : '';
    $id     = ( isset($_GET['gallery']) ) ? absint(sanitize_text_field( $_GET['gallery'] )) : null;
    $nonce  = ( isset($_REQUEST['_wpnonce']) ) ? esc_attr( $_REQUEST["_wpnonce"] ) : null;

    if($action == 'duplicate' && wp_verify_nonce( $nonce, $this->plugin_name . "-duplicate-gallery" )){
        $this->gallery_obj->duplicate_galleries($id);
    }

    $gallery_max_id = Gallery_Photo_Gallery_Admin::get_gallery_max_id('gallery');

    $plus_icon_svg = "<span class=''><img src='". AYS_GPG_ADMIN_URL ."/images/icons/plus=icon.svg'></span>";
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap ays-gpg-list-table">
    <div class="ays-gpg-heading-box">
        <div class="ays-gpg-wordpress-user-manual-box">
            <a href="https://ays-pro.com/wordpress-photo-gallery-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                <i class="ays_fa ays_fa_file_text"></i>
                <span style="margin-left: 3px;text-decoration: underline;"><?php echo __("View Documentation", $this->plugin_name); ?></span>
            </a>
        </div>
    </div>
    <h1 class="wp-heading-inline">
        <?php
        if (!isset($_COOKIE['ays_gpg_page_tab_free'])) {
            setcookie('ays_gpg_page_tab_free', 'tab_0', time() + 3600);
        } else {
            $_COOKIE['ays_gpg_page_tab_free'] = 'tab_0';
        }
        echo esc_html(get_admin_page_title());        
        echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-gpg-add-new-button ays-gpg-add-new-button-new-design"> %s '  . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg);
        
        ?>
    </h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $this->gallery_obj->prepare_items();
                        $search = __( "Search", $this->plugin_name );
                        $this->gallery_obj->search_box($search, $this->plugin_name);
                        $this->gallery_obj->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
 
    <!-- <?php if($gallery_max_id <= 3): ?>
        <div class="ays-gpg-create-gallery-video-box" style="margin: 80px auto 30px;">
            <div class="ays-gpg-create-gallery-youtube-video-button-box">
                <?php echo sprintf( '<a href="?page=%s&action=%s" class="ays-gpg-add-new-button-video ays-gpg-add-new-button-new-design"> %s ' . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg); ?>
            </div>
            <div class="ays-gpg-create-gallery-title">
                <h4><?php echo __( "Create Your First Gallery in Under One Minute", $this->plugin_name ); ?></h4>
            </div>
            <div class="ays-gpg-create-gallery-youtube-video">                
                <iframe width="560" height="315" loading="lazy" src="https://www.youtube.com/embed/bRrrBEQVZk8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
            <div class="ays-gpg-create-gallery-youtube-video-button-box">
                <?php echo sprintf( '<a href="?page=%s&action=%s" class="ays-gpg-add-new-button-video ays-gpg-add-new-button-new-design"> %s ' . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="ays-gpg-create-gallery-video-box" style="margin: auto;">
            <div class="ays-gpg-create-gallery-youtube-video-button-box">
                <?php echo sprintf( '<a href="?page=%s&action=%s" class="ays-gpg-add-new-button-video ays-gpg-add-new-button-new-design"> %s ' . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg); ?>
            </div>
            <div class="ays-gpg-create-gallery-youtube-video">
                <a href="https://www.youtube.com/watch?v=bRrrBEQVZk8" target="_blank" title="YouTube video player" >How to create a Gallery in Under One Minute</a>
            </div>
        </div>
    <?php endif ?> -->
</div>
