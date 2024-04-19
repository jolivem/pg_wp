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
class Glp_User_Galleries_Public {

    const PAGE_ID_EDIT_GALLERY = 11;
    //const PAGE_ID_EDIT_GALLERY = 189;
    const PAGE_ID_SHOW_GALLERY = 36;
    //const PAGE_ID_SHOW_GALLERY = 272;

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
        add_shortcode( 'pg_user_galleries', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . 'css/animate.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/glp-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));
    }

    public function enqueue_styles_early(){

        $settings_options = Gallery_Settings_Actions::ays_get_setting('options');
        if($settings_options){
            $settings_options = json_decode(stripcslashes($settings_options), true);
        }else{
            $settings_options = array();
        }

        // General CSS File
        $settings_options['gpg_exclude_general_css'] = isset($settings_options['gpg_exclude_general_css']) ? esc_attr( $settings_options['gpg_exclude_general_css'] ) : 'off';
        $gpg_exclude_general_css = (isset($settings_options['gpg_exclude_general_css']) && esc_attr( $settings_options['gpg_exclude_general_css'] ) == "on") ? true : false;

        if ( ! $gpg_exclude_general_css ) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
        }else {
            if ( ! is_front_page() ) {
                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/glp-public.css', array(), $this->version, 'all' );
            }
        }
        
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        ob_start();

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page();

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page(){
        
        $user_id = get_current_user_id();
        error_log("pg_show_page IN user_id: ".$user_id);

        $html_code = "";
        $galleries = $this->pg_get_galleries_by_user_id($user_id);
        if(empty($galleries)){
            // TODO display No galleries, create your first gallery
            $html_code .= "<p>No galleries, create your first gallery</p>";
        }

        $edit_gallery_url = get_permalink(self::PAGE_ID_EDIT_GALLERY); // TODO move 186 to a global constant or get by Title
        $show_gallery_url = get_permalink(self::PAGE_ID_SHOW_GALLERY); // TODO move 186 to a global constant or get by Title
        $nonce = wp_create_nonce('user_galleries');
        $admin_ajax_url = admin_url('admin-ajax.php');

        $html_code .= "
        <input type='hidden' id='pg_edit_gallery_url' value='$edit_gallery_url'/>
        <input type='hidden' id='pg_show_gallery_url' value='$show_gallery_url'/>
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <div class='container' id='user-item-list'>
            <br/>
            <div class='tab-pane fade show active' id='nav-photos' role='tabpanel' aria-labelledby='nav-photos-tab'>
                <button type='button' class='btn btn-primary' id='user-galleries-create'>
                    Add a gallery...
                </button>
            </div>";

        $html_code .= $this->render_galleries($galleries);
        $html_code .= 
        "</div>";
        //$html_code .= $this->pg_create_modal_for_delete_confirmation();

        return $html_code;
    } 

    function render_galleries($galleries){
        $html='';

        // loop for each media
        foreach($galleries as $item){
            error_log("render_galleries item:".print_r($item, true));
            
            // get the first image og the gallery
            $image_id = $this->pg_get_first_image_by_id($item["id"]);
            $img_src = "";
            if ($image_id != null) {
                // get the image source
                $url_img = wp_get_attachment_image_src($image_id, "thumbnail");
                if ($url_img != false) {
                    $img_src = $url_img[0];
                }
            }
            $datetime = explode( " ", $item['date_update']);
            $date = $datetime[0];
            // $url_img = wp_get_attachment_image_src($item->ID, "thumbnail");
            // if ($url_img != false) {
            //     $img_src = $url_img[0];
            // }
            //error_log("render_galleries url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="flex-container">
                <div class="miniature" style="background-image: url('.$img_src.')"></div>
                <div class="photo-text-container" style="background-color: lightyellow";>
                    <div class="gallery-title">'.$item["title"].'</div>
                    <div class="gallery-text">'.$item["description"].'</div>
                    <div class="footer-user-galleries">Modifiée le '.$date.'</div>
                </div>
                <div class="options" style="background-color: lightgreen">
                    <div class="flex-options-2">
                        <div class="user-gallery-option pointer-icon fas fa-edit" aria-hidden="true" data-galid="'.$item["id"].'"></div>
                        <div class="user-gallery-option pointer-icon fas fa-eye" aria-hidden="true" data-galid="'.$item["id"].'"></div>
                        <div class="user-gallery-option pointer-icon fas fa-share-alt" aria-hidden="true" data-galid="'.$item["id"].'"></div>
                    </div>
                </div>
            </div>';
            

        }
        return $html;
    }    

    // Get the list of galleries for a given user_id
    // return empty array if none
    static public function pg_get_galleries_by_user_id( $user_id ){
        global $wpdb;

        error_log("pg_get_galleries_by_user_id id: ".$user_id);

        $gallery_table = esc_sql($wpdb->prefix . "glp_gallery");

        $user_id = absint( sanitize_text_field( $user_id ));
        $sql = "SELECT * FROM ".$gallery_table." WHERE user_id=$user_id";
        //error_log("pg_get_galleries_by_user_id sql: ".$sql);
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        //error_log("pg_get_galleries_by_user_id result: ".print_r($results, true));
        if(count($results) > 0){
            return $results;
        }else{
            return array();
        }
    }

    // Get the first image of the gallery
    // Return the image id or null if none
    function pg_get_first_image_by_id( $gallery_id ) {
        global $wpdb;
        //error_log("pg_get_first_image_by_id id: ".$gallery_id);

        $gallery_table = esc_sql($wpdb->prefix . "glp_gallery");

        $sql = "SELECT images_ids FROM {$gallery_table} WHERE id={$gallery_id}";
        //error_log("pg_get_first_image_by_id sql: ".$sql);
        $result = $wpdb->get_row( $sql, "ARRAY_A" );

        //error_log("pg_get_first_image_by_id all ids: ".print_r($result, true));
        $image_ids = explode( "***", $result["images_ids"]);

        if (count($image_ids) > 0) {
            return $image_ids[0];
        }

        return null;
    }

}
