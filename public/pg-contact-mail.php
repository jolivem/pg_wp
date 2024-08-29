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
class Pg_Contact_Mail_Public {

    private $plugin_name;
    private $version;


    // list of all possible countries and their options (file, width, height)
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
        add_shortcode( 'pg_contact_mail', array($this, 'pg_generate_page') );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( 'ays_pb_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'gpg-fontawesome', 'https://use.fontawesome.com/releases/v5.4.1/css/all.css', array(), $this->version, 'all');
    }        

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        //wp_enqueue_media();
        wp_enqueue_script( $this->plugin_name.'-pg-public.js', plugin_dir_url( __FILE__ ) . 'js/pg-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( $this->plugin_name.'-bootstrap.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, true );
    }

    public function enqueue_styles_early(){

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pg-public.css', array(), $this->version, 'all' );
        wp_enqueue_script('jquery');
    }
    
    public function pg_generate_page( $attr ){
        
        //error_log("Pg_Contact_Mail_Public::pg_generate_page IN ");

        ob_start();

        $this->enqueue_styles();
        $this->enqueue_scripts();

        echo $this->pg_show_page();

        return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    }

    // attr should have the user id
    public function pg_show_page(){

        //error_log("pg_show_page IN" );
        
        $admin_ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('contact_mail');


        // TODO check url_img is OK, add try catch
        $html_code = "
        <input type='hidden' id='pg_admin_ajax_url' value='$admin_ajax_url'/>
        <input type='hidden' id='pg_nonce' value='$nonce'/>";

        $html_code .= "
        <div class='toast-container position-fixed bottom-0 end-0 p-3'>
            <div id='contact-success' class='toast align-items-center text-white bg-success bg-gradient border-0' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='d-flex'>
                    <div class='toast-body'>
                        Envoyé !
                    </div>
                </div>
            </div>
        </div>
        <div class='pg-container'>
            </br>
            <h3>Formulaire de contact</h3>
            <div>
                <div class='form-floating mb-3'>
                    <input type='text' name='email' class='form-control' id='email' aria-describedby='emailHelp' placeholder=''>
                    <label for='email'>Votre email de contact</label>
                    <div class='invalid-input'>
                        Veuillez saisir un email valide.
                    </div>
                </div>
                <div class='form-floating mb-3'>
                    <textarea rows='6' name='desc' style='height:100%;' class='form-control' placeholder='' id='contact-message'></textarea>
                    <label for='contact-message'>Votre message</label>                        
                    <div class='invalid-input'>
                        Veuillez saisir un message.
                    </div>
                </div>
            </div>
            <div>
                <button type='button' class='btn btn-primary align-right' id='contact-mail'>Envoyer</button>
            </div>
            <br/>
        </div>";

        return $html_code;

    } // end ays_show_galery()

    //
    // callback on request to download photos
    //
    public function contact_mail() {
        //error_log("user_edit_photo IN");
        //error_log("contact_mail REQUEST ".print_r($_REQUEST, true));
        //error_log("download_single_photo FILES ".print_r($_FILES, true));

        if( !isset( $_REQUEST['nonce'] ) or 
            !wp_verify_nonce( $_REQUEST['nonce'], 'contact_mail' ) ) {
            error_log("contact_mail nonce not found");
            wp_send_json_error( "NOK.", 403 );
            return;
        }

        $email = sanitize_text_field( $_REQUEST['email'] );
        $message = sanitize_text_field( $_REQUEST['msg'] );

        $to = 'contact@planet-gallery.org';
        $subject = "Contact from $email";
        //$message = get_permalink($msg);
        error_log("contact_mail to=$to, subject=$subject");
        #$headers[] = 'Content-type: text/plain; charset=utf-8';
        $headers = 'From:planet.gallery@gmail.com';
        $ret = wp_mail($to, $subject, $message, $headers);
        $headers = 'From:planetgallery@gmail.com';
        $ret = mail($to, $subject, $message, $headers);
        error_log("contact_mail result=$ret");

        //error_log( "Respond success");
        wp_send_json_success( null, 200);
        wp_die();
        
    }
    
}
