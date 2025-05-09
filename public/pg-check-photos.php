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
class Glp_Check_Photos_Public {

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
        add_shortcode( 'glp_check_photos', array($this, 'pg_generate_page') );
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
            //error_log("admin_reject_photo No ADMIN");
            // TODO 404 NOT FOUND
            my_custom_404();
            wp_die();
        }
        
        ob_start();
        //error_log("Glp_Check_Photos_Public::pg_generate_page IN ".print_r($attr, true));
        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page( $attr );

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page( $attr ){
        
        global $wpdb;
        $id = ( isset($attr['id']) ) ? absint( intval( $attr['id'] ) ) : null;
        
        $user_id = get_current_user_id();
        $medias = $this->pg_get_medias_to_be_checked();
        if(!$medias){
            $html_code = "
            <div>Aucune photo à vérifier.<div>";
            return $html_code;    
        }

        $admin_ajax_url = admin_url('admin-ajax.php');
        //$admin_post_url = admin_url('admin-post.php');
        $nonce = wp_create_nonce('admin_check');
        $edit_photo_url = Glp_User_Galleries_Public::get_page_url_from_slug(Pg_Edit_Gallery_Public::PAGE_SLUG_EDIT_PHOTO); // TODO move 186 to a global constant or get by Title


        $html_code = "
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>
        <div class='container'>";

        $html_code .= $this->render_images($medias);
        $html_code .= 
        '</div>';

        return $html_code;
    } 

    function render_images($medias){
        $html='';

        // loop for each media
        foreach($medias as $item){
            //error_log("render_images item:".print_r($item, true));
            $img_src = $item->guid;
            $url_img = wp_get_attachment_image_src($item->ID, "large");
            if ($url_img != false) {
                $img_src = $url_img[0];
            }
            $addresses=[]; // final addresses to be displayed in the radio list
            try {
                $address_json = get_post_meta($item->ID, 'address_json',true);
                /* for tests only 
                $address_json = '[
                    {
                        "formatted_address": "HQCV+3Q Bourg-Saint-Maurice, France",
                        "place_id": "GhIJr7coDvvIRkARvVKWIY4tG0A",
                        "types": [
                            "plus_code"
                        ]
                    },
                    {
                        "formatted_address": "Route coucou, 73700 Bourg-Saint-Maurice, France",
                        "place_id": "ChIJB9sU57VviUcRMpnD_cDeVOI",
                        "types": [
                            "route"
                        ]
                    },
                    {
                        "formatted_address": "Route sans nom, 73700 Bourg-Saint-Maurice, France",
                        "place_id": "ChIJB9sU57VviUcRMpnD_cDeVOI",
                        "types": [
                            "route"
                        ]
                    },                    {
                        "formatted_address": "73700 Bourg-Saint-Maurice, France",
                        "place_id": "ChIJOXYlgjRmiUcRsLa65CqrCAQ",
                        "types": [
                            "locality",
                            "political"
                        ]
                    }
                ]'; */

                //$address_json = '[{"long_name":"HQCV+3Q","short_name":"HQCV+3Q","types":["plus_code"]}]';
                if ($address_json) {
                    //error_log("render_images addresses:".$address_json);
                    $jaddresses = json_decode($address_json, true);
                    //error_log("render_images jaddresses: ".print_r($jaddresses, true));
                    if ($jaddresses != null) {
                        
                        for ($i = 0 ; $i < count($jaddresses); $i++) {
                            $addresses[] = $this->remove_numeric_words($jaddresses[$i]['formatted_address']);
                        }

                        // remove duplicates
                        //error_log("render_images jaddresses: A. addresses :".print_r($addresses, true));
                        $addresses = array_values(array_unique($addresses));
                        //error_log("render_images jaddresses: B. addresses :".print_r($addresses, true));
                    }

                }
            
            } catch (Exception $e) {
                error_log("render_images Exception");
            }
            //error_log("render_images jaddresses: addresses :".print_r($addresses, true));
            
            //error_log("render_images addresses".print_r($addresses, true));
            $statext = Pg_Edit_Gallery_Public::get_photo_status($item->ID, $this->plugin_name);
            $group_name = "address" . $item->ID;

            //error_log("render_images url:".print_r($url_img, true));
            // TODO check url_img is OK, add try catch
            $html.=
            "<div class='pdb-big-container'>
                <img src='$img_src' class='full-miniature-big'></img>
                <div class='pdb-radio-container'>";
            
            // Select the address among the various possibilities
            for ($i = 0 ; $i < count($addresses); $i++) {
                $checked="";
                if ($item->post_title == $addresses[$i]){
                    $checked=" checked='checked'";
                }
                $html.=
                    "<input type='radio' name='$group_name' value='" . $addresses[$i]."' $checked>";
                $html.=
                    "<label for='html'>" . $addresses[$i]."</label><br>";
                                        
                // $html.=
                //     "<div class='pdb-descr-font' style='overflow: visible;'>$item->post_title/div>";
                // error_log("render_images addresses:" . $addresses[$i]['formatted_address']);
            }
            $html.=
                    "<div class='pdb-descr-footer'>
                        <div>Date : $item->post_date</div>
                    </div>
                </div>
                <div class='options-photo-gallery' style='background-color: lightgreen'>
                    <i class='admin-photo-option fas fa-thumbs-up' aria-hidden='true' data-postid='$item->ID'></i>
                    <i class='admin-photo-option fas fa-thumbs-down' aria-hidden='true' data-postid='$item->ID'></i>
                </div>
            </div>";
            

        }
        return $html;
    }

    private function remove_numeric_words( $input) {
        // remove numerica words and words with a '+'
        //error_log("remove_numeric_words IN " . $input);
        $output = preg_replace('/\b\d+\b/', '', $input);
        $output = preg_replace('/\S*\+\S*/', '', $output);
        //$output = preg_replace('/\b\d+\b/', '', $input);
        
        return trim($output);
    }

    public function pg_get_medias_to_be_checked(  ) {


        $args = array(
            //'author'         => $user_id,
            'post_type'      => 'attachment',
            'post_status'    => 'inherit,private', // Adjust post status as needed
            'posts_per_page' => -1, // Retrieve all attachments
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => 'user_status', 
                    'value' => Pg_Edit_Photo_Public::USER_STATUS_PUBLIC, 
                    'compare' => '='
                ),
                array(
                    'key'   => 'admin_status',
                    'value' => Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_SEEN,
                    'compare' => '=',
                )
            ),            
        );
        
        $query = new WP_Query( $args );
        $medias = $query->get_posts();

        return $medias;
    }

    // callback on request to delete a photo
    public function admin_valid_photo() {
        //error_log("admin_valid_photo IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_valid_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        if ( ! current_user_can( 'administrator' ) ) {
            error_log("admin_valid_photo No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['pid'] )){
            error_log("admin_valid_photo no pid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $pid = sanitize_text_field($_REQUEST['pid']);
        $address = sanitize_text_field($_REQUEST['address']);

        update_post_meta($pid , 'admin_status', Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK);
        $this->update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK);
        
        $my_post = array(
            'ID' => $pid,
            'post_title' => $address
        );
        
        // Set the image meta (e.g. Title, Excerpt, Content)
        wp_update_post( $my_post );

        //error_log( "admin_valid_photo Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    } 

    public function admin_reject_photo() {
        //error_log("admin_reject_photo IN REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        // TODO test current user is gallery user

        if( ! isset( $_REQUEST['nonce'] ) or 
            ! wp_verify_nonce( $_REQUEST['nonce'], 'admin_check' ) ) {
            error_log("admin_reject_photo nonce not found");
            wp_send_json_error( "NOK.", 403 );
            wp_die();
            return;
        }

        if ( ! current_user_can( 'administrator' ) ) {
            error_log("admin_reject_photo No ADMIN");
            // TODO 404 NOT FOUND
            wp_send_json_error( "NOK.", 401 );
            return;
        }

        if( ! isset( $_REQUEST['pid'] )){
            error_log("admin_reject_photo no pid");
            wp_send_json_error( "NOT Found", 404 );
            wp_die();
            return;
        }

        $pid = sanitize_text_field($_REQUEST['pid']);
        update_post_meta($pid , 'admin_status', Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);
        $this->update_visibility($pid, Pg_Edit_Photo_Public::ADMIN_STATUS_NOT_OK);

        //error_log( "admin_reject_photo Respond success");
        wp_send_json_success( null, 200);
        wp_die();
    }

    public static function update_visibility($post_id, $admin_status) {
        //error_log("update_visibility IN id=$post_id admin_status=$admin_status");

        $user_status = get_post_meta($post_id, 'user_status', true);
        //error_log("update_visibility user_status=$user_status");

        if ($user_status == Pg_Edit_Photo_Public::USER_STATUS_PUBLIC && $admin_status == Pg_Edit_Photo_Public::ADMIN_STATUS_PUBLIC_OK) {
            Pg_Geoposts_Table::update_visible($post_id, Pg_Geoposts_Table::PUBLIC_VISIBLE);
        }
        else {
            Pg_Geoposts_Table::update_visible($post_id, Pg_Geoposts_Table::PUBLIC_HIDDEN);
        }
    }

}

