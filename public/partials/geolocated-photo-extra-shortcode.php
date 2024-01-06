<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://glp-plugin.com/
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
class Ays_Gallery_Extra_Shortcodes_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $html_class_prefix = 'ays-gallery-extra-shortcodes-';
    private $html_name_prefix = 'ays-gallery-';
    private $name_prefix = 'ays_gallery_';
    private $unique_id;
    private $unique_id_in_class;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version){

        $this->plugin_name = $plugin_name;
        $this->version = $version;        
        add_shortcode('ays_gallery_creation_date', array($this, 'ays_generate_gallery_creation_date_method'));
        add_shortcode('ays_gallery_current_author', array($this, 'ays_generate_current_gallery_author_method'));
        add_shortcode('ays_gallery_images_count', array($this, 'ays_generate_images_count_method'));
        add_shortcode('ays_gallery_images_count_by_category', array($this, 'ays_generate_images_count_by_category_method'));
        add_shortcode('ays_gallery_user_first_name', array($this, 'ays_generate_user_first_name_method'));
        add_shortcode('ays_gallery_user_last_name', array($this, 'ays_generate_user_last_name_method'));
        add_shortcode('ays_gallery_user_display_name', array($this, 'ays_generate_user_display_name_method'));
        add_shortcode('ays_gallery_user_email', array($this, 'ays_generate_user_email_method'));
        add_shortcode('ays_gallery_user_nickname', array($this, 'ays_generate_user_nickname_method'));
    }   

    /*
    ==========================================
        Show gallery creation date | Start
    ==========================================
    */

    public function get_curent_gallery_creation_date( $id ){
        global $wpdb;

        $gallery_table = esc_sql( $wpdb->prefix . "glp_gallery" );

        if (is_null($id) || $id == 0 ) {
            return null;
        }

        $id = absint( $id );

        $sql = "SELECT `options` FROM `{$gallery_table}` WHERE `id` = {$id}";

        $results = $wpdb->get_row($sql, 'ARRAY_A');

        if ( is_null( $results ) || empty( $results ) ) {
            $results = null;
        }

        return $results;
    }

    public function ays_generate_gallery_creation_date_method( $attr ){

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $gallery_creation_date = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_creation_date);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $gallery_creation_date = "";
        if(is_user_logged_in()){
            $gallery_creation_date = $this->ays_generate_gallery_creation_date_html( $id );
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_creation_date);
    }

    public function ays_generate_gallery_creation_date_html( $id ){

        $results = $this->get_curent_gallery_creation_date( $id );

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $options = ( json_decode($results['options'], true) != null ) ? json_decode($results['options'], true) : array();

        $gallery_creation_date = (isset($options['create_date']) && $options['create_date'] != '') ? sanitize_text_field( $options['create_date'] ) : "";
        if ( $gallery_creation_date != "" ) {
            $gallery_creation_date = date_i18n( get_option( 'date_format' ), strtotime( $gallery_creation_date ) );
        }

        $content_html[] = "<span class='". $this->html_name_prefix ."gallery-creation-date' id='". $this->html_name_prefix ."gallery-creation-date-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $gallery_creation_date;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show gallery creation date | End
    ==========================================
    */

    /*
    ==========================================
        Show current gallery author | Start
    ==========================================
    */

    public function ays_generate_current_gallery_author_method( $attr ) {

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $gallery_author = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $gallery_author = "";
        if(is_user_logged_in()){
            $gallery_author = $this->ays_generate_current_gallery_author_html( $id );
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
    }

    public function ays_generate_current_gallery_author_html( $id ) {

        $results = $this->get_curent_gallery_creation_date( $id );

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $options = ( json_decode($results['options'], true) != null ) ? json_decode($results['options'], true) : array();

        if(isset($options['author'])){
            if(is_array($options['author'])){
                $author = $options['author'];
            }else{
                $author = json_decode($options['author'], true);
            }
        }else{
            $author = array("name"=>"Unknown");
        }

        if(isset($author['name']) && $author['name'] == "Unknown"){
            $author['name'] = __( "Unknown", $this->plugin_name );
        }

        $gallery_author = (isset($author['name']) && $author['name'] != '') ? sanitize_text_field( $author['name'] ) : "";

        $content_html[] = "<span class='". $this->html_name_prefix ."current-gallery-author' id='". $this->html_name_prefix ."current-gallery-author-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $gallery_author;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show current gallery author | End
    ==========================================
    */

     /*
    ==========================================
        Show gallery images count | Start
    ==========================================
    */

    public function ays_generate_images_count_method( $attr ) {

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $gallery_author = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $gallery_author = $this->ays_generate_images_count_html( $id );

        return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
    }

    public function ays_generate_images_count_html( $id ) {

        $results = $this->get_gallery_images_count( $id );

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $content_html[] = "<span class='". $this->html_name_prefix ."images-count' id='". $this->html_name_prefix ."images-count-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $results;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    public function get_gallery_images_count( $id ){
        global $wpdb;
        $gallery_table = esc_sql( $wpdb->prefix . "glp_gallery" );
        $sql = "SELECT `images`
                FROM `{$gallery_table}`
                WHERE id=" . absint( $id );

        $images_str = $wpdb->get_row( $sql, 'ARRAY_A');        
        
        $count = "";
        if ( !empty( $images_str ) ) {
            $image_str = (isset( $images_str['images'] ) && $images_str['images'] != "") ? $images_str['images'] : "";
            $images_arr = explode('***', $image_str);

            if ( !empty( $images_arr ) ) {
                $count = count($images_arr);
            }
        }

        return $count;
    }

    /*
    ==========================================
       Show gallery images count | End
    ==========================================
    */

     /*
    ==========================================
        Show gallery images count by category | Start
    ==========================================
    */

    public function ays_generate_images_count_by_category_method( $attr ) {

        $id = (isset($attr['id']) && $attr['id'] != '') ? absint( sanitize_text_field($attr['id']) ) : null;

        if (is_null($id) || $id == 0 ) {
            $gallery_author = "";
            return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
        }

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $gallery_author = $this->ays_generate_images_count_by_category_html( $id );

        return str_replace(array("\r\n", "\n", "\r"), "\n", $gallery_author);
    }

    public function ays_generate_images_count_by_category_html( $id ) {

        $results = $this->get_gallery_images_count_by_category( $id );

        $content_html = array();
        
        if( is_null( $results ) || empty( $results ) ){
            $content_html = "";
            return $content_html;
        }

        $content_html[] = "<span class='". $this->html_name_prefix ."images-count' id='". $this->html_name_prefix ."images-count-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $results;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    public function get_gallery_images_count_by_category( $id ){
        global $wpdb;
        $gallery_table = esc_sql( $wpdb->prefix . "glp_gallery" );
        $sql = "SELECT `categories_id`
                FROM `{$gallery_table}`";
       
        $images_cat = $wpdb->get_results($sql, 'ARRAY_A'); 
      
        $count_cat_img = '';
        
        $count_arr = array();
        if ( !empty( $images_cat ) ) {           
            foreach ($images_cat as $val_cat) {
                $images_str = explode('***', $val_cat["categories_id"]);

                foreach ($images_str as $val_str) {
                    $img_str = explode(',', $val_str);
                    foreach ($img_str as $val_c) {
                        if ($id == $val_c) {
                            $count_arr[] = $val_c;
                        }
                    }
                    
                }                

            }

            if ( !empty( $count_arr ) ) {
                $count = count($count_arr);
            }
               
        }

        return $count;
    }

    /*
    ==========================================
       Show gallery images count by category | End
    ==========================================
    */


    /*
    ==========================================
        Show User First Name | Start
    ==========================================
    */

    public function get_user_profile_data(){

        /*
         * Gallery message variables for Start Page
         */

        $user_first_name    = '';
        $user_last_name     = '';
        $user_nickname      = '';
        $user_display_name  = '';
        $user_email         = '';

        $user_id = get_current_user_id();
        if($user_id != 0){
            $usermeta = get_user_meta( $user_id );
            if($usermeta !== null){
                $user_first_name = (isset($usermeta['first_name'][0]) && sanitize_text_field( $usermeta['first_name'][0] != '') ) ? sanitize_text_field( $usermeta['first_name'][0] ) : '';
                $user_last_name  = (isset($usermeta['last_name'][0]) && sanitize_text_field( $usermeta['last_name'][0] != '') ) ? sanitize_text_field( $usermeta['last_name'][0] ) : '';
                $user_nickname   = (isset($usermeta['nickname'][0]) && sanitize_text_field( $usermeta['nickname'][0] != '') ) ? sanitize_text_field( $usermeta['nickname'][0] ) : '';
            }

            $current_user_data = get_userdata( $user_id );
            if ( ! is_null( $current_user_data ) && $current_user_data ) {
                $user_display_name = ( isset( $current_user_data->data->display_name ) && $current_user_data->data->display_name != '' ) ? sanitize_text_field( $current_user_data->data->display_name ) : "";
                $user_email = ( isset( $current_user_data->data->user_email ) && $current_user_data->data->user_email != '' ) ? sanitize_text_field( $current_user_data->data->user_email ) : "";
            }
        }

        $message_data = array(
            'user_first_name'   => $user_first_name,
            'user_last_name'    => $user_last_name,
            'user_nickname'     => $user_nickname,
            'user_display_name' => $user_display_name,
            'user_email'        => $user_email,
        );

        return $message_data;
    }

    public function ays_generate_user_first_name_method(){

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $user_first_name_html = "";
        if(is_user_logged_in()){
            $user_first_name_html = $this->ays_generate_user_first_name_html();
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $user_first_name_html);
    }

    public function ays_generate_user_first_name_html(){

        $results = $this->get_user_profile_data();

        $content_html = array();
        
        if( is_null( $results ) || $results == 0 ){
            $content_html = "";
            return $content_html;
        }

        $user_first_name = (isset( $results['user_first_name'] ) && sanitize_text_field( $results['user_first_name'] ) != "") ? sanitize_text_field( $results['user_first_name'] ) : '';

        $content_html[] = "<span class='". $this->html_name_prefix ."user-first-name' id='". $this->html_name_prefix ."user-first-name-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $user_first_name;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show User First Name | End
    ==========================================
    */

    /*
    ==========================================
        Show User Last Name | Start
    ==========================================
    */

    public function ays_generate_user_last_name_method(){

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $user_last_name_html = "";
        if(is_user_logged_in()){
            $user_last_name_html = $this->ays_generate_user_last_name_html();
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $user_last_name_html);
    }

    public function ays_generate_user_last_name_html(){

        $results = $this->get_user_profile_data();

        $content_html = array();
        
        if( is_null( $results ) || $results == 0 ){
            $content_html = "";
            return $content_html;
        }

        $user_last_name = (isset( $results['user_last_name'] ) && sanitize_text_field( $results['user_last_name'] ) != "") ? sanitize_text_field( $results['user_last_name'] ) : '';

        $content_html[] = "<span class='". $this->html_name_prefix ."user-last-name' id='". $this->html_name_prefix ."user-last-name-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $user_last_name;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show User Last Name | End
    ==========================================
    */

    /*
    ==========================================
        Show User Display name | Start
    ==========================================
    */

    public function ays_generate_user_display_name_method(){

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $user_display_name_html = "";
        if(is_user_logged_in()){
            $user_display_name_html = $this->ays_generate_user_display_name_html();
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $user_display_name_html);
    }

    public function ays_generate_user_display_name_html(){

        $results = $this->get_user_profile_data();

        $content_html = array();
        
        if( is_null( $results ) || $results == 0 ){
            $content_html = "";
            return $content_html;
        }

        $user_display_name = (isset( $results['user_display_name'] ) && sanitize_text_field( $results['user_display_name'] ) != "") ? sanitize_text_field( $results['user_display_name'] ) : '';

        $content_html[] = "<span class='". $this->html_name_prefix ."user-display-name' id='". $this->html_name_prefix ."user-display-name-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $user_display_name;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show User Display name | End
    ==========================================
    */

    /*
    ==========================================
        Show User Email | Start
    ==========================================
    */

    public function ays_generate_user_email_method(){

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $user_email_html = "";
        if(is_user_logged_in()){
            $user_email_html = $this->ays_generate_user_email_html();
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $user_email_html);
    }

    public function ays_generate_user_email_html(){

        $results = $this->get_user_profile_data();

        $content_html = array();
        
        if( is_null( $results ) || $results == 0 ){
            $content_html = "";
            return $content_html;
        }

        $user_email = (isset( $results['user_email'] ) && sanitize_text_field( $results['user_email'] ) != "") ? sanitize_text_field( $results['user_email'] ) : '';

        $content_html[] = "<span class='". $this->html_name_prefix ."user-email' id='". $this->html_name_prefix ."user-email-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $user_email;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show User Email | End
    ==========================================
    */

    /*
    ==========================================
        Show User Nickname | Start
    ==========================================
    */

    public function ays_generate_user_nickname_method(){

        $unique_id = uniqid();
        $this->unique_id = $unique_id;
        $this->unique_id_in_class = $unique_id;

        $user_nickname_html = "";
        if(is_user_logged_in()){
            $user_nickname_html = $this->ays_generate_user_nickname_html();
        }
        return str_replace(array("\r\n", "\n", "\r"), "\n", $user_nickname_html);
    }

    public function ays_generate_user_nickname_html(){

        $results = $this->get_user_profile_data();

        $content_html = array();
        
        if( is_null( $results ) || $results == 0 ){
            $content_html = "";
            return $content_html;
        }

        $user_nickname = (isset( $results['user_nickname'] ) && sanitize_text_field( $results['user_nickname'] ) != "") ? sanitize_text_field( $results['user_nickname'] ) : '';

        $content_html[] = "<span class='". $this->html_name_prefix ."user-nickname' id='". $this->html_name_prefix ."user-nickname-". $this->unique_id_in_class ."' data-id='". $this->unique_id ."'>";
            $content_html[] = $user_nickname;
        $content_html[] = "</span>";

        $content_html = implode( '' , $content_html);

        return $content_html;
    }

    /*
    ==========================================
        Show User Nickname | End
    ==========================================
    */
}
