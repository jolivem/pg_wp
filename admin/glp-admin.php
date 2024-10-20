<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/admin
 * @author     GLP <info@glp-plugin.com>
 */
class GLP_Admin {

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

	private $gallery_obj;
	private $map_obj;
    private $cats_obj;
    private $settings_obj;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
        $per_page_array = array(
            'galleries_per_page',
            'maps_per_page',
            //'gallery_categories_per_page',
        );
        foreach($per_page_array as $option_name){
            add_filter('set_screen_option_'.$option_name, array(__CLASS__, 'set_screen'), 10, 3);
        }

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook_suffix) {
        
	    wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wp-color-picker' );
        
        //TODO remove file glp-banner.css
        // wp_enqueue_style( $this->plugin_name . "-banner", plugin_dir_url( __FILE__ ) . 'css/glp-banner.css', array(), $this->version, 'all' );

        wp_enqueue_style('leaflet.css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');

        wp_enqueue_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-admin.css', array(), $this->version, 'all' );        

        if(false === strpos($hook_suffix, $this->plugin_name))
            return;

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Geolocated_Photo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Geolocated_Photo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_enqueue_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        wp_enqueue_style('glp_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all');
		// TODO mettre à jour bootstrap
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-mosaic.css", plugin_dir_url( __FILE__ ) . 'css/jquery.mosaic.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name."-masonry.css", plugin_dir_url( __FILE__ ) . 'css/masonry.pkgd.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook_suffix) {
        global $wp_version;
        
        $version1 = $wp_version;
        $operator = '>=';
        $version2 = '5.5';
        $versionCompare = $this->versionCompare($version1, $operator, $version2);

        if ($versionCompare) {
            wp_enqueue_script( $this->plugin_name.'-wp-load-scripts', plugin_dir_url(__FILE__) . 'js/ays-wp-load-scripts.js', array(), $this->version, true);
        }

        wp_enqueue_script( $this->plugin_name . "banner", plugin_dir_url( __FILE__ ) . 'js/glp-banner.js', array( 'jquery' ), $this->version, true );

        if (false !== strpos($hook_suffix, "plugins.php")){
            wp_enqueue_script( 'sweetalert-js', '//cdn.jsdelivr.net/npm/sweetalert2@7.26.29/dist/sweetalert2.all.min.js', array('jquery'), $this->version, true );
            wp_enqueue_script( $this->plugin_name . '-adminjs', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), $this->version, true );
            wp_localize_script($this->plugin_name . '-adminjs',  'glp_admin_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
        }
        //wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet/dist/leaflet.js', array('jquery'), '1.7.1', true);

        // if(false === strpos($hook_suffix, $this->plugin_name)){
        //     echo "RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR ".$hook_suffix;
        //     echo "  ".$this->plugin_name;
        //     return;
        // }
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Geolocated_Photo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Geolocated_Photo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media();
        
		wp_enqueue_script( "ays_pb_popper", 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_style('glp_code_mirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.4/codemirror.css', array(), $this->version, 'all');
		wp_enqueue_script( "ays_pb_bootstrap", 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'select2js', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', array('jquery'), $this->version, true );
        wp_enqueue_script( 'imagesloaded.min.js', 'https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name."-mosaic.js", plugin_dir_url( __FILE__ ) . 'js/jquery.mosaic.min.js', array( 'jquery', 'wp-color-picker'  ), $this->version, true );
		wp_enqueue_script( $this->plugin_name."-masonry.js", plugin_dir_url( __FILE__ ) . 'js/masonry.pkgd.min.js', array( 'jquery', 'wp-color-picker'  ), $this->version, true );
		wp_enqueue_script( $this->plugin_name."-cookie.js", plugin_dir_url( __FILE__ ) . 'js/cookie.js', array( 'jquery' ), $this->version, true );

        // can be removed ?
        wp_enqueue_script( $this->plugin_name . "admin", plugin_dir_url( __FILE__ ) . 'js/glp-admin.js', array( 'jquery', 'wp-color-picker'), $this->version, true );
        wp_localize_script($this->plugin_name . "admin",  'ays_vars', array('base_url' => GLP_BASE_URL));

        wp_localize_script($this->plugin_name . "admin", 'gallery_ajax', array(
            'ajax_url'          => admin_url('admin-ajax.php'),            
            'selectUser'        => __( 'Select user', $this->plugin_name),
            'pleaseEnterMore'   => __( "Please enter 1 or more characters", $this->plugin_name ),
            'searching'         => __( "Searching...", $this->plugin_name ),
        ));

        $gpg_banner_date = $this->glp_update_banner_time();
        wp_localize_script( $this->plugin_name . "admin", 'galleryLangObj', array(
            'gpgBannerDate'      => $gpg_banner_date,
            'copied'             => __( 'Copied!', $this->plugin_name),
            'clickForCopy'       => __( 'Click for copy.', $this->plugin_name),
            'addGif'             => __( 'Add Gif', $this->plugin_name),
            'somethingWentWrong' => __( "Maybe something went wrong.", $this->plugin_name ),
            'errorMsg'           => __( "Error", $this->plugin_name )
        ) );
        
        wp_enqueue_script( $this->plugin_name.'-wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js',array( 'wp-color-picker' ),$this->version, true );

        $color_picker_strings = array(
            'clear'            => __( 'Clear', $this->plugin_name ),
            'clearAriaLabel'   => __( 'Clear color', $this->plugin_name ),
            'defaultString'    => __( 'Default', $this->plugin_name ),
            'defaultAriaLabel' => __( 'Select default color', $this->plugin_name ),
            'pick'             => __( 'Select Color', $this->plugin_name ),
            'defaultLabel'     => __( 'Color value', $this->plugin_name ),
        );
        wp_localize_script( $this->plugin_name.'-wp-color-picker-alpha', 'wpColorPickerL10n', $color_picker_strings );
	}

    function codemirror_enqueue_scripts($hook) {
        if (false === strpos($hook, $this->plugin_name)){
            return;
        }
        if(function_exists('wp_enqueue_code_editor')){
            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array(
                'type' => 'text/css',
                'codemirror' => array(
                    'inputStyle' => 'contenteditable',
                    'theme' => 'cobalt',
                )
            ));

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_localize_script('wp-theme-plugin-editor', 'cm_gpg_settings', $cm_settings);
        
            wp_enqueue_style('wp-codemirror');
        }
    }

    function versionCompare($version1, $operator, $version2) {
   
        $_fv = intval ( trim ( str_replace ( '.', '', $version1 ) ) );
        $_sv = intval ( trim ( str_replace ( '.', '', $version2 ) ) );
       
        if (strlen ( $_fv ) > strlen ( $_sv )) {
            $_sv = str_pad ( $_sv, strlen ( $_fv ), 0 );
        }
       
        if (strlen ( $_fv ) < strlen ( $_sv )) {
            $_fv = str_pad ( $_fv, strlen ( $_sv ), 0 );
        }
       
        return version_compare ( ( string ) $_fv, ( string ) $_sv, $operator );
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */

    public function add_plugin_admin_menu() {
        
        $hook_gallery = add_menu_page( 
            __('Planet Gallery', $this->plugin_name), 
            __('Planet Gallery', $this->plugin_name), 
            'manage_options', 
            $this->plugin_name, 
            array($this, 'display_galleries_page'), GLP_ADMIN_URL . 'images/icons/icon-gpg-128x128.svg', 6);
        add_action( "load-$hook_gallery", array( $this, 'screen_option_gallery' ) );
        
        $hook_gallery = add_submenu_page(
            $this->plugin_name,
            __('Galleries', $this->plugin_name),
            __('Galleries', $this->plugin_name),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_galleries_page')
        );
        add_action( "load-$hook_gallery", array( $this, 'screen_option_gallery' ) );
    }
    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */

    public function add_action_links( $links ) {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>'
            // '<a href="https://ays-demo.com/wordpress-photo-gallery-plugin-free-demo/" target="_blank">' . __('Demo', $this->plugin_name) . '</a>',
            // '<a href="https://glp-plugin.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=buy-now-gallery" target="_blank" class="ays-admin-plugins-upgrade-link" style="font-weight:bold;">' . __('Upgrade 20% Sale', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    public function add_plugin_row_meta($meta, $file) {

        if ($file == GLP_BASENAME) {
            $meta[] = '<a href="https://wordpress.org/support/plugin/geolocated-photo/" target="_blank">' . esc_html__( 'Free Support', $this->plugin_name ) . '</a>';
        }

        return $meta;
    }

    function filter_secondary_menu_user($items, $args) {
        //error_log("filter_secondary_menu_user items: ".print_r($items, true));
        //error_log("filter_secondary_menu_user args: ".print_r($args, true));
        //error_log("filter_secondary_menu_user theme_location: ".$args->theme_location);
        //error_log("filter_secondary_menu_user menu: ".print_r($args->menu, true));
        //$logged_in = is_user_logged_in();
        $is_admin = current_user_can( 'manage_options' );

        // error_log("filter_secondary_menu_user logged_in: ".$logged_in);
        // error_log("filter_secondary_menu_user is_admin: ".$is_admin);

        // error_log("filter_secondary_menu_user slug: ".$args->menu->slug);
        
        if ( isset($args->menu) && is_object($args->menu) && property_exists($args->menu, "slug") && str_contains($args->menu->slug, "menu-login") ) {

            foreach( $items as $key => $item) {
                // error_log("filter_secondary_menu_user items title: ".$item->title);
                // error_log("filter_secondary_menu_user items url: ".$item->url);
                if ( str_contains($item->url, "login") ) {
                    if (is_user_logged_in()) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                if ( str_contains($item->url, "check-user-blog") ) {
                    if (!$is_admin) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                if ( str_contains($item->url, "check-photos") ) {
                    if (!$is_admin) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                if ( str_contains($item->url, "my-photos") ) {
                    //error_log("filter_secondary_menu_user logged_in: ".is_user_logged_in());
                    if (! is_user_logged_in()) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                if ( str_contains($item->url, "my-galleries") ) {
                    if (! is_user_logged_in()) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                if ( str_contains($item->url, "my-account") ) {
                    if (! is_user_logged_in()) {
                        // error_log("filter_secondary_menu_user unset key: ".$key);
                        unset($items[$key]);
                    }
                }
                
            }
        }
        
        return $items;
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_galleries_page() {
        $this->settings_obj = new Gallery_Settings_Actions($this->plugin_name);
        $action = (isset($_GET['action'])) ? sanitize_text_field( $_GET['action'] ) : '';
        switch ( $action ) {
            case 'add':
                include_once( 'partials/glp-galleries-actions.php' );
                break;
            case 'edit':
                include_once( 'partials/glp-galleries-actions.php' );
                break;
            default:
                include_once( 'partials/glp-galleries-display.php' );
        }
    }


    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    public function screen_option_gallery() {
        $option = 'per_page';
        $args   = [
            'label'   => __('Galleries', $this->plugin_name),
            'default' => 20,
            'option'  => 'galleries_per_page'
        ];

        add_screen_option( $option, $args );
        $this->gallery_obj = new Glp_Galleries_List_Table($this->plugin_name);
    }

    public static function ays_get_categories(){

        $taxonomy = 'category'; // Change 'your_taxonomy' to the name of your taxonomy
        $terms = get_terms( array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false, // Set to true if you want to hide empty terms
        ) );
        //error_log("terms XX ".print_r($terms, true));
        return $terms;
    }

    public static function ays_get_gallery_options(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'glp_gallery';
        $res = $wpdb->get_results("SELECT id, title, width, height FROM ".$table_name."");
        $aysGlobal_array = array();

        foreach($res as $ays_res_options){
            $aysStatic_array = array();
            $aysStatic_array[] = $ays_res_options->id;
            $aysStatic_array[] = $ays_res_options->title;
            $aysStatic_array[] = $ays_res_options->width;
            $aysStatic_array[] = $ays_res_options->height;
            $aysGlobal_array[] = $aysStatic_array;
        }
        return $aysGlobal_array;
      }
    
    function glp_register_tinymce_plugin($plugin_array) {
        $plugin_array['glp_button_mce'] = GLP_BASE_URL .'/glp_shortcode.js';
        return $plugin_array;
    }
    
    function glp_add_tinymce_button($buttons) {
        $buttons[] = "glp_button_mce";
        return $buttons;
    }
    
    function gen_glp_shortcode_callback() {
        $shortcode_data = $this->ays_get_gallery_options();

        ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title><?php echo __( 'Gallery Photo Gallery', $this->plugin_name ); ?></title>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
                <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
                <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>

                <?php
                    wp_print_scripts('jquery');
                ?>
                <base target="_self">
            </head>
            <body id="link" onLoad="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" dir="ltr" class="forceColors">
                <div class="select-sb">

              <table align="center">
                  <tr>
                    <td><label for="ays_gpg">Gallery</label></td>
                    <td>
                      <span>
                        <select id="ays_gpg" style="padding: 2px; height: 25px; font-size: 16px;width:100%;">
                            <option>--Select Gallery--</option>
                                <?php foreach($shortcode_data as $index=>$data)
                                    echo '<option id="'.$data[0].'" value="'.$data[0].'" mw="'.$data[2].'" mh="'.$data[3].'" class="glp_options">'.$data[1].'</option>';
                                ?>
                        </select>
                        </span>
                    </td>
                  </tr>
              </table>
                </div>
                <div class="mceActionPanel">
                    <input type="submit" id="insert" name="insert" value="Insert" onClick="gpg_insert_shortcode();"/>
                </div>
            <script type="text/javascript">
                function gpg_insert_shortcode() {
                    var tagtext = '[glp_gallery id="' + document.getElementById('ays_gpg')[document.getElementById('ays_gpg').selectedIndex].id + '"]';
                    window.tinyMCE.execCommand('mceInsertContent', false, tagtext);
                    tinyMCEPopup.close();
                }
              </script>

            </body>
          </html>
          <?php
          die();
      }
    
    public static function ays_restriction_string($type, $x, $length){
        $output = "";
        switch($type){
            case "char":                
                if(strlen($x)<=$length){
                    $output = $x;
                } else {
                    $output = substr($x,0,$length) . '...';
                }
                break;
            case "word":
                $res = explode(" ", $x);
                if(count($res)<=$length){
                    $output = implode(" ",$res);
                } else {
                    $res = array_slice($res,0,$length);
                    $output = implode(" ",$res) . '...';
                }
            break;
        }
        return $output;
    }
    
    public function vc_before_init_actions() {
        require_once( GLP_DIR.'pb_templates/geolocated-photo-wpbvc.php' );
    }

    public function gpg_el_widgets_registered() {
        // We check if the Elementor plugin has been installed / activated.
        wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
        if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
            // get our own widgets up and running:
            // copied from widgets-manager.php
            if ( class_exists( 'Elementor\Plugin' ) ) {
                if ( is_callable( 'Elementor\Plugin', 'instance' ) ) {
                    $elementor = Elementor\Plugin::instance();
                    if ( isset( $elementor->widgets_manager ) ) {
                        if ( method_exists( $elementor->widgets_manager, 'register_widget_type' ) ) {
                            $widget_file   = 'plugins/elementor/geolocated-photo-elementor.php';
                            $template_file = locate_template( $widget_file );
                            if ( !$template_file || !is_readable( $template_file ) ) {
                                $template_file = GLP_DIR.'pb_templates/geolocated-photo-elementor.php';
                            }
                            if ( $template_file && is_readable( $template_file ) ) {
                                require_once $template_file;
                                Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Elementor\Widget_GPG_Custom_Elementor_Thing() );
                            }
                        }
                    }
                }
            }
        }
    }

    public function deactivate_plugin_option(){
        $request_value = $_REQUEST['upgrade_plugin'];
        $upgrade_option = get_option('glp_upgrade_plugin','');
        if($upgrade_option === ''){
            add_option('glp_upgrade_plugin',$request_value);
        }else{
            update_option('glp_upgrade_plugin',$request_value);
        }
        echo json_encode(array('option'=>get_option('glp_upgrade_plugin','')));
        wp_die();
    }

    public function gallery_admin_footer($a){
        if(isset($_REQUEST['page'])){
            if(false !== strpos( sanitize_text_field( $_REQUEST['page'] ), $this->plugin_name)){
                ?><hr/><?php
            }
        }
    }

    public static function validateDate($date, $format = 'Y-m-d H:i:s'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function glp_restriction_string($type, $x, $length){
        $output = "";
        switch($type){
            case "char":                
                if(strlen($x)<=$length){
                    $output = $x;
                } else {
                    $output = substr($x,0,$length) . '...';
                }
                break;
            case "word":
                $res = explode(" ", $x);
                if(count($res)<=$length){
                    $output = implode(" ",$res);
                } else {
                    $res = array_slice($res,0,$length);
                    $output = implode(" ",$res) . '...';
                }
            break;
        }
        return $output;
    }

    public static function get_gpg_listtables_title_length( $listtable_name ) {
        global $wpdb;

        $settings_table = $wpdb->prefix . "glp_gallery_settings";
        $sql = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = 'options'";
        $result = $wpdb->get_var($sql);
        $options = ($result == "") ? array() : json_decode(stripcslashes($result), true);

        $listtable_title_length = 5;
        if(! empty($options) ){
            switch ( $listtable_name ) {
                case 'galleries':
                    $listtable_title_length = (isset($options['galleries_title_length']) && intval($options['galleries_title_length']) != 0) ? absint(intval($options['galleries_title_length'])) : 5;
                    break;
                // case 'gallery_categories':
                //     $listtable_title_length = (isset($options['gpg_categories_title_length']) && intval($options['gpg_categories_title_length']) != 0) ? absint(sanitize_text_field($options['gpg_categories_title_length'])) : 5;
                //     break;               
                // TODO add maps here
                default:
                    $listtable_title_length = 5;
                    break;
            }
            return $listtable_title_length;
        }
        return $listtable_title_length;
    }

    public function get_next_or_prev_gallery_by_id( $id, $type = "next" ) {
        global $wpdb;

        $gallery_table = esc_sql( $wpdb->prefix . "glp_gallery" );

        $where = array();
        $where_condition = "";

        $id     = (isset( $id ) && $id != "" && absint($id) != 0) ? absint( sanitize_text_field( $id ) ) : null;
        $type   = (isset( $type ) && $type != "") ? sanitize_text_field( $type ) : "next";

        if ( is_null( $id ) || $id == 0 ) {
            return null;
        }

        switch ( $type ) {            
            case 'prev':
                $where[] = ' `id` < ' . $id . ' ORDER BY `id` DESC ';;
                break;
            case 'next':
            default:
                $where[] = ' `id` > ' . $id;
                break;
        }

        if( ! empty($where) ){
            $where_condition = " WHERE " . implode( " AND ", $where );
        }

        $sql = "SELECT `id` FROM {$gallery_table} ". $where_condition ." LIMIT 1;";
        $results = $wpdb->get_row( $sql, 'ARRAY_A' );

        return $results;

    }

    public function get_next_or_prev_map_by_id( $id, $type = "next" ) {
        global $wpdb;

        $gallery_table = esc_sql( $wpdb->prefix . "glp_map" );

        $where = array();
        $where_condition = "";

        $id     = (isset( $id ) && $id != "" && absint($id) != 0) ? absint( sanitize_text_field( $id ) ) : null;
        $type   = (isset( $type ) && $type != "") ? sanitize_text_field( $type ) : "next";

        if ( is_null( $id ) || $id == 0 ) {
            return null;
        }

        switch ( $type ) {            
            case 'prev':
                $where[] = ' `id` < ' . $id . ' ORDER BY `id` DESC ';;
                break;
            case 'next':
            default:
                $where[] = ' `id` > ' . $id;
                break;
        }

        if( ! empty($where) ){
            $where_condition = " WHERE " . implode( " AND ", $where );
        }

        $sql = "SELECT `id` FROM {$gallery_table} ". $where_condition ." LIMIT 1;";
        $results = $wpdb->get_row( $sql, 'ARRAY_A' );

        return $results;

    }    
 
    public function glp_author_user_search() {
        $search = isset($_REQUEST['search']) && $_REQUEST['search'] != '' ? sanitize_text_field( $_REQUEST['search'] ) : null;
        $checked = isset($_REQUEST['val']) && $_REQUEST['val'] !='' ? sanitize_text_field( $_REQUEST['val'] ) : null;

        $args = 'search=';
        if($search !== null){
            $args .= '*';
            $args .= $search;
            $args .= '*';
        }

        $users = get_users($args);

        $content_text = array(
            'results' => array()
        );

        foreach ($users as $key => $value) {
            if ($checked !== null) {
                if ( !is_array( $checked ) ) {
                    $checked2 = $checked;
                    $checked = array();
                    $checked[] = absint($checked2);
                }
                if (in_array($value->ID, $checked)) {
                    continue;
                }else{
                    $content_text['results'][] = array(
                        'id' => $value->ID,
                        'text' => $value->data->display_name,
                    );
                }
            }else{
                $content_text['results'][] = array(
                    'id' => $value->ID,
                    'text' => $value->data->display_name,
                );
            }
        }

        ob_end_clean();
        echo json_encode($content_text);
        wp_die();
    }

    //TODO check can be removed
    public function ays_gallery_generate_message_vars_html( $gallery_message_vars ) {
        $content = array();
        $var_counter = 0; 

        $content[] = '<div class="glp-message-vars-box">';
            $content[] = '<div class="glp-message-vars-icon">';
                $content[] = '<div>';
                    $content[] = '<i class="ays_glp glp_fa_link"></i>';
                $content[] = '</div>';
                $content[] = '<div>';
                    $content[] = '<span>'. __("Message Variables" , $this->plugin_name) .'</span>';
                    $content[] = '<a class="ays_help" data-toggle="tooltip" data-html="true" title="'. __("Insert your preferred message variable into the editor by clicking." , $this->plugin_name) .'">';
                        $content[] = '<i class="fas fa-info-circle"></i>';
                    $content[] = '</a>';
                $content[] = '</div>';
            $content[] = '</div>';
            $content[] = '<div class="glp-message-vars-data">';
                foreach($gallery_message_vars as $var => $var_name){
                    $var_counter++;
                    $content[] = '<label class="glp-message-vars-each-data-label">';
                        $content[] = '<input type="radio" class="glp-message-vars-each-data-checker" hidden id="glp_message_var_count_'. $var_counter .'" name="glp_message_var_count">';
                        $content[] = '<div class="glp-message-vars-each-data">';
                            $content[] = '<input type="hidden" class="glp-message-vars-each-var" value="'. $var .'">';
                            $content[] = '<span>'. $var_name .'</span>';
                        $content[] = '</div>';
                    $content[] = '</label>';
                }
            $content[] = '</div>';
        $content[] = '</div>';

        $content = implode( '', $content );

        return $content;
    }

    public static function get_gallery_max_id( $table ) {
        global $wpdb;
        $db_table = $wpdb->prefix . 'glp_'.$table;;

        $sql = "SELECT MAX(id) FROM {$db_table}";

        $result = intval( $wpdb->get_var( $sql ) );

        return $result;
    }

/*    public function display_plugin_add_new_gallery_page() {
        $add_new_gpg_url = admin_url('admin.php?page=' . $this->plugin_name . '&action=add');
        wp_redirect($add_new_gpg_url);
    }*/

    public function glp_dismiss_button(){

        $data = array(
            'status' => false,
        );

        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'glp_dismiss_button') { 
            if( (isset( $_REQUEST['_ajax_nonce'] ) && wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'photo-gallery-sale-banner' )) && current_user_can( 'manage_options' )){
                update_option('glp_sale_btn', 1);
                update_option('glp_sale_date', current_time( 'mysql' ));
                $data['status'] = true;
            }
        }

        ob_end_clean();
        $ob_get_clean = ob_get_clean();
        echo json_encode($data);
        wp_die();
    }

    public function glp_update_banner_time(){

        $date = time() + ( 3 * 24 * 60 * 60 ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
        // $date = time() + ( 60 ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS); // for testing | 1 min
        $next_3_days = date('M d, Y H:i:s', $date);

        $glp_banner_time = get_option('glp_banner_time');

        if ( !$glp_banner_time || is_null( $glp_banner_time ) ) {
            update_option('glp_banner_time', $next_3_days ); 
        }

        $get_glp_banner_time = get_option('glp_banner_time');

        $val = 60*60*24*0.5; // half day
        // $val = 60; // for testing | 1 min

        $current_date = current_time( 'mysql' );
        $date_diff = strtotime($current_date) - intval(strtotime($get_glp_banner_time));

        $days_diff = $date_diff / $val;
        if(intval($days_diff) > 0 ){
            update_option('glp_banner_time', $next_3_days);
        }

        return $get_glp_banner_time;
    }


}
