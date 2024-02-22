<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/admin/partials
 */


    $action = ( isset($_GET['action']) ) ? sanitize_text_field( $_GET['action'] ) : '';
    $id     = ( isset($_GET['gallery']) ) ? absint(sanitize_text_field( $_GET['gallery'] )) : null;
    $nonce  = ( isset($_REQUEST['_wpnonce']) ) ? esc_attr( $_REQUEST["_wpnonce"] ) : null;

    if($action == 'duplicate' && wp_verify_nonce( $nonce, $this->plugin_name . "-duplicate-gallery" )){
        $this->gallery_obj->duplicate_galleries($id);
    }

    $plus_icon_svg = "<span class=''><img src='". GLP_ADMIN_URL ."/images/icons/plus=icon.svg'></span>";
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap glp-list-table">
    <div class="glp-heading-box">
        <div class="glp-wordpress-user-manual-box">
            <a href="https://glp-plugin.com/wordpress-photo-gallery-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                <i class="ays_glp glp_fa_file_text"></i>
                <span style="margin-left: 3px;text-decoration: underline;"><?php echo __("View Documentation", $this->plugin_name); ?></span>
            </a>
        </div>
    </div>
    <h1 class="wp-heading-inline">
        <?php
        if (!isset($_COOKIE['glp_page_tab_free'])) {
            setcookie('glp_page_tab_free', 'tab_0', time() + 3600);
        } else {
            $_COOKIE['glp_page_tab_free'] = 'tab_0';
        }
        echo esc_html(get_admin_page_title());        
        echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary glp-add-new-button glp-add-new-button-new-design"> %s '  . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg);
        
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

</div>
