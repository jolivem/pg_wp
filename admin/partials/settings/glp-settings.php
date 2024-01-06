<?php
$actions = $this->settings_obj;

if (isset($_REQUEST['ays_submit'])) {
	$actions->store_data($_REQUEST);
}
if (isset($_GET['glp_tab'])) {
	$glp_tab = sanitize_key($_GET['glp_tab']);
} else {
	$glp_tab = 'tab1';
}
$db_data = $actions->get_db_data();

$options = ($actions->ays_get_setting('options') === false) ? array() : json_decode( stripcslashes( $actions->ays_get_setting('options') ), true);

// WP Editor height
$gpg_wp_editor_height = (isset($options['gpg_wp_editor_height']) && $options['gpg_wp_editor_height'] != '' && $options['gpg_wp_editor_height'] != 0) ? absint( sanitize_text_field($options['gpg_wp_editor_height']) ) : 100 ;

// All images text
$gpg_all_images_text = (isset($options['gpg_all_images_text']) && $options['gpg_all_images_text'] != '') ?  stripslashes( esc_attr($options['gpg_all_images_text'])) : 'All';

//Gallery's title length
$galleries_title_length = (isset($options['galleries_title_length']) && intval($options['galleries_title_length']) != 0) ? absint(intval($options['galleries_title_length'])) : 5;

// Gallery category title length
$gpg_categories_title_length = (isset($options['gpg_categories_title_length']) && intval($options['gpg_categories_title_length']) != 0) ? absint(intval($options['gpg_categories_title_length'])) : 5;

// General CSS File
$options['gpg_exclude_general_css'] = isset($options['gpg_exclude_general_css']) ? esc_attr( $options['gpg_exclude_general_css'] ) : 'off';
$gpg_exclude_general_css = (isset($options['gpg_exclude_general_css']) && esc_attr( $options['gpg_exclude_general_css'] ) == "on") ? true : false;

$loader_iamge = "<span class='display_none glp_loader_box'><img src='". GLP_ADMIN_URL ."/images/loaders/loading.gif'></span>";
?>
<div class="wrap" style="position:relative;">
    <div class="glp-heading-box">
        <div class="glp-wordpress-user-manual-box">
            <a href="https://glp-plugin.com/wordpress-photo-gallery-user-manual" target="_blank" style="text-decoration: none;font-size: 13px;">
                <i class="ays_fa ays_fa_file_text"></i>
                <span style="margin-left: 3px;text-decoration: underline;"><?php echo __("View Documentation", $this->plugin_name); ?></span>
            </a>
        </div>
    </div>
    <div class="container-fluid">
        <form method="post" class="glp-general-settings-form">
            <input type="hidden" name="glp_tab" value="<?php echo esc_attr($glp_tab); ?>">
            <h1 class="wp-heading-inline">
				<?php
				echo __('Settings', $this->plugin_name);
				?>
            </h1>
			<?php
			if (isset($_REQUEST['status'])) {
				$actions->gallery_settings_notices($_REQUEST['status']);
			}
			?>
            <hr/>
            <div class="ays-gen-settings-wrapper">
                <div>
                    <div class="nav-tab-wrapper" style="position:sticky; top:35px;">
                        <a href="#tab1" data-tab="tab1" class="nav-tab <?php echo ($glp_tab == 'tab1') ? 'nav-tab-active' : ''; ?>">
							<?php echo __("General", $this->plugin_name); ?>
                        </a>
                        <a href="#tab2" data-tab="tab2" class="nav-tab <?php echo ($glp_tab == 'tab2') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Shortcodes", $this->plugin_name);?>
                        </a>
                        <a href="#tab3" data-tab="tab3" class="nav-tab <?php echo ($glp_tab == 'tab3') ? 'nav-tab-active' : ''; ?>">
                            <?php echo __("Message variables", $this->plugin_name);?>
                        </a>
                    </div>
                </div>
                <div class="glp-tabs-wrapper">
                    <div id="tab1" class="glp-tab-content <?php echo ($glp_tab == 'tab1') ? 'glp-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle"><?php echo __('General Settings',$this->plugin_name)?></p>
                        <hr/>                        
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="fa ays_fa_question_circle"></i></strong>
                                <h5><?php echo __('Default parameters for gallery',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="glp_wp_editor_height">
                                        <?php echo __( "WP Editor height", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Give the default value to the height of the WP Editor. It will apply to all WP Editors within the plugin on the dashboard.',$this->plugin_name); ?>">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="glp_wp_editor_height" id="glp_wp_editor_height" class="ays-text-input" value="<?php echo $gpg_wp_editor_height; ?>">
                                </div>
                            </div>
                            <hr/>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="glp_all_images_text">
                                        <?php echo __( "Show All images text", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title='<?php echo __('Write the text you prefer for the "All" category to see all the images on the Front-end.',$this->plugin_name); ?>'>
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="glp_all_images_text" id="glp_all_images_text" class="ays-text-input" value="<?php echo $gpg_all_images_text; ?>">
                                </div>
                            </div>
                        </fieldset>
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="fa fa-align-left"></i></strong>
                                <h5><?php echo __('Excerpt words count in list tables',$this->plugin_name)?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="ays_galleries_title_length">
                                        <?php echo __( "Galleries list table", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Determine the length of the galleries name to be shown in the Galleries List Table by putting your preferred count of words in the following field. (For example: if you put 10,  you will see the first 10 words of each galleries name in the Galleries page of your dashboard).', $this->plugin_name); ?>">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="ays_galleries_title_length" id="ays_galleries_title_length" class="ays-text-input" value="<?php echo $galleries_title_length; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="glp_categories_title_length">
                                        <?php echo __( "Gallery categories list table", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('Determine the length of the results to be shown in the Gallery categories List Table by putting your preferred count of words in the following field. (For example: if you put 10,  you will see the first 10 words of each result in the Question categories page of your dashboard.', $this->plugin_name); ?>">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="glp_categories_title_length" id="glp_categories_title_length" class="ays-text-input" value="<?php echo $gpg_categories_title_length; ?>">
                                </div>
                            </div>
                        </fieldset>
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;"><i class="fa ays_fa_file_code"></i></strong>
                                <h5><?php echo __('General CSS File',$this->plugin_name); ?></h5>
                            </legend>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label for="glp_exclude_general_css">
                                        <?php echo __( "Exclude general CSS file from home page", $this->plugin_name ); ?>
                                        <a class="ays_help" data-toggle="tooltip" title="<?php echo __('If the option is enabled, then the gallery general CSS file will not be applied to the home page. Please note, that if you have inserted the gallery on the home page, then the option must be disabled so that the CSS File can normally work for that gallery.',$this->plugin_name); ?>">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox" name="glp_exclude_general_css" id="glp_exclude_general_css" value="on" <?php echo $gpg_exclude_general_css ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </fieldset>                                                       
                    </div>
                    <div id="tab2" class="glp-tab-content <?php echo ($glp_tab == 'tab2') ? 'glp-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle"><?php echo __('Shortcodes',$this->plugin_name)?></p>
                        <hr/>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;">[ ]</strong>
                                <h5><?php echo __('Extra shortcodes',$this->plugin_name); ?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="glp_creation_date">
                                                <?php echo __( "Show gallery creation date", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("You need to insert Your Gallery ID in the shortcode. It will show the creation date of the particular gallery. If there is no gallery available/found with that particular Gallery ID, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="glp_creation_date" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_creation_date id="Your_Gallery_ID"]'>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="glp_current_author">
                                                <?php echo __( "Show current gallery author", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("You need to insert Your Gallery ID in the shortcode. It will show the current author of the particular gallery. If there is no gallery available/found with that particular Gallery ID, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="glp_current_author" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_current_author id="Your_Gallery_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_images_count">
                                                <?php echo __( "Show gallery images count", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("You need to insert Your Gallery ID in the shortcode. It will show the images count of the particular gallery. If there is no gallery available/found with that particular Gallery ID, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_images_count" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_images_count id="Your_Gallery_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_category_images_count">
                                                <?php echo __( "Show gallery images count by category", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("You need to insert Your Category ID in the shortcode. It will show the images count of the particular category. If there is no category available/found with that particular Category ID, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_category_images_count" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_images_count_by_category id="Your_Category_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_user_first_name">
                                                <?php echo __( "Show User First Name", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's First Name. If the user is not logged-in, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_user_first_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_user_first_name]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_user_last_name">
                                                <?php echo __( "Show User Last Name", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Last Name. If the user is not logged-in, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_user_last_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_user_last_name]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_user_display_name">
                                                <?php echo __( "Show User Display name", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Display name. If the user is not logged-in, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_user_display_name" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_user_display_name]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_user_email">
                                                <?php echo __( "Show User Email", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Email. If the user is not logged-in, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_user_email" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_user_email]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_user_nickname">
                                                <?php echo __( "Show User Nickname", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo esc_attr( __("Shows the logged-in user's Nickname. If the user is not logged-in, the shortcode will be empty.",$this->plugin_name) ); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_user_nickname" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_user_nickname]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>   
                        <hr>
                        <fieldset>
                            <legend>
                                <strong style="font-size:30px;">[ ]</strong>
                                <h5><?php echo __('Gallery categories',$this->plugin_name); ?></h5>
                            </legend>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_cat_title">
                                                <?php echo __( "Shortcode", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You need to insert Your Gallery Category ID in the shortcode. It will show the category title. If there is no gallery category available/unavailable with that particular Gallery Category ID, the shortcode will stay empty.',$this->plugin_name); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_cat_title" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_category_title id="Your_Gallery_Category_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row" style="padding:0px;margin:0;">
                                <div class="col-sm-12" style="padding:20px;">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label for="ays_gallery_cat_description">
                                                <?php echo __( "Shortcode", $this->plugin_name ); ?>
                                                <a class="ays_help" data-toggle="tooltip" title="<?php echo __('You need to insert Your Gallery Category ID in the shortcode. It will show the category description. If there is no gallery category available/unavailable with that particular Gallery Category ID, the shortcode will stay empty.',$this->plugin_name); ?>">
                                                    <i class="fa fa-info-circle"></i>
                                                </a>
                                            </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" id="ays_gallery_cat_description" class="ays-text-input" onclick="this.setSelectionRange(0, this.value.length)" readonly="" value='[ays_gallery_category_desctription id="Your_Gallery_Category_ID"]'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>              
                    </div>
                    <div id="tab3" class="glp-tab-content <?php echo ($glp_tab == 'tab3') ? 'glp-tab-content-active' : ''; ?>">
                        <p class="ays-subtitle">
                            <?php echo __('Message variables',$this->plugin_name)?>
                            <a class="ays_help" data-toggle="tooltip" data-html="true" title="<p style='margin-bottom:3px;'><?php echo __( 'You can copy these variables and paste them in the following options from the gallery settings', $this->plugin_name ); ?>:</p>
                                <p style='padding-left:10px;margin:0;'>- <?php echo __( 'Gallery Description', $this->plugin_name ); ?></p>">
                                <i class="fa fa-info-circle"></i>
                            </a>
                        </p>
                        <blockquote>
                            <p><?php echo __( "You can copy these variables and paste them in the following options from the gallery settings", $this->plugin_name ); ?>:</p>
                            <p style="text-indent:10px;margin:0;">- <?php echo __( "Gallery Description", $this->plugin_name ); ?></p>                            
                        </blockquote>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_first_name%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's first name that was filled in their WordPress site during registration.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_last_name%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's last name that was filled in their WordPress site during registration.", $this->plugin_name); ?>
                                    </span>
                                </p>  
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_display_name%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's display name that was filled in their WordPress profile.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_nickname%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's nickname that was filled in their WordPress profile.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_wordpress_email%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's email that was filled in their WordPress profile.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_wordpress_roles%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's role(s) when logged-in. In case the user is not logged-in, the field will be empty.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_ip_address%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The user's IP address.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%user_id%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo __( "The ID of the current user.", $this->plugin_name); ?>
                                    </span>
                                </p>
                                 <p class="vmessage">
                                    <strong>
                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly value="%%gallery_id%%" />
                                    </strong>
                                    <span> - </span>
                                    <span style="font-size:18px;">
                                        <?php echo esc_attr( __( "The ID of the gallery.", $this->plugin_name) ); ?>
                                    </span>
                                </p>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
			<?php
			wp_nonce_field('settings_action', 'settings_action');
            $save_bottom_attributes = array(
                'id' => 'ays_apply',
                'title' => 'Ctrl + s',
                'data-toggle' => 'tooltip',
                'data-delay'=> '{"show":"1000"}'
            );
			submit_button(__('Save changes', $this->plugin_name), 'primary ays-button glp-save-comp', 'ays_submit', true, $save_bottom_attributes);
            echo $loader_iamge;
			?>
        </form>
    </div>
</div>