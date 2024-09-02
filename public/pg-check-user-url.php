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
class Glp_Check_User_Url_Public {

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
        add_shortcode( 'glp_check_user_url', array($this, 'pg_generate_page') );
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
        wp_enqueue_style( $this->plugin_name.'-pg-public.css', plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_localize_script($this->plugin_name, 'ays_vars', array('base_url' => GLP_BASE_URL));

    }

    public function enqueue_styles_early(){

        // General CSS File
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){

        $user_id = get_current_user_id();
        if ($user_id != 1) {
            error_log("admin_reject_photo No ADMIN");
            // TODO 404 NOT FOUND
            my_custom_404();
            wp_die();
        }

        ob_start();
        //error_log("Glp_Check_User_Url_Public::pg_generate_page IN ".print_r($attr, true));
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        $users = $this->get_users_by_meta('user_url', 'to_be_checked');
        if(!$users){
            $html_code = "
            <div>Aucune url à vérifier.<div>";
            return $html_code;    
        }

        $admin_ajax_url = admin_url('admin-ajax.php');
        //$admin_post_url = admin_url('admin-post.php');
        $nonce = wp_create_nonce('admin_check');

        $html_code = "
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <div class='container' id='user-item-list'>";

        $html_code .= $this->render_user_urls($users);
        $html_code .= 
        '</div>';

        return $html_code;
    } 

    function render_user_urls($users){
        $html='';

        // loop for each media
        foreach($users as $user){
            //error_log("render_images item:".print_r($item, true));
            
            //error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            '<div class="pdb-container">
                <div>'.$user->user_login.'</br>'.$user->user_email.'</br>ID='.$user->ID.'</div>
                <div class="url-text-container">
                    <a href="'.$user->user_url.'">'.$user->user_url.'</a>
                </div>
                <div class="options-photo-gallery" style="background-color: lightgreen">
                    <i class="admin-url-option fas fa-thumbs-up" aria-hidden="true" data-userid="'.$user->ID.'"></i>
                    <i class="admin-url-option fas fa-thumbs-down" aria-hidden="true" data-userid="'.$user->ID.'"></i>
                </div>
            </div>';
            

        }
        return $html;
    }

    function get_users_by_meta($meta_key, $meta_value) {
        $args = array(
            'meta_key'   => $meta_key,
            'meta_value' => $meta_value,
            'number'     => -1 // Get all users
        );
    
        $user_query = new WP_User_Query($args);
    
        // Check for results
        if (!empty($user_query->get_results())) {
            return $user_query->get_results();
        } else {
            return array();
        }
    }

    // callback on request to delete a photo
    public function admin_valid_url() {
        //error_log("admin_valid_url IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_valid_url nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id!== 1) {
            error_log("admin_valid_url No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['uid'] )){
            error_log("admin_valid_url no uid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $uid = sanitize_text_field($_REQUEST['uid']);

        update_user_meta($uid , 'user_url', 'OK');
        
        //error_log( "admin_valid_url Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    } 

    public function admin_reject_url() {
        //error_log("admin_reject_url IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_reject_url nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id != 1) {
            error_log("admin_reject_url No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['uid'] )){
            error_log("admin_reject_url no uid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $uid = sanitize_text_field($_REQUEST['uid']);
        update_user_meta($uid , 'user_url', 'NOK');
        //$this->update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);

        //error_log( "admin_reject_url Respond success");
        wp_send_json_success( null, 200);
        wp_die();
    }
}

