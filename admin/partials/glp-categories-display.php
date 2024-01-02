<?php
    $plus_icon_svg = "<span class=''><img src='". AYS_GPG_ADMIN_URL ."/images/icons/plus=icon.svg'></span>";
?>
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
            echo __(esc_html(get_admin_page_title()),$this->plugin_name);
            echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-gpg-add-new-button ays-gpg-add-new-button-new-design"> %s '  . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg);
        ?>
    </h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                            $this->cats_obj->prepare_items();
                            $search = __( "Search", $this->plugin_name );
                            $this->cats_obj->search_box($search, $this->plugin_name);
                            $this->cats_obj->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
