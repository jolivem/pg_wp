<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://ays-pro.com/
 * @since      1.0.0
 *
 * @package    Gallery_Photo_Gallery
 * @subpackage Gallery_Photo_Gallery/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Gallery_Photo_Gallery
 * @subpackage Gallery_Photo_Gallery/includes
 * @author     AYS Pro LLC <info@ays-pro.com>
 */
class Photo_Gallery_Data {    

    public static function ays_gpg_sale_baner(){

        if(isset($_POST['ays_gpg_sale_btn']) && (isset( $_POST['photo-gallery-sale-banner'] ) && wp_verify_nonce( $_POST['photo-gallery-sale-banner'], 'photo-gallery-sale-banner' )) &&
              current_user_can( 'manage_options' )){        
            update_option('ays_gpg_sale_btn', 1);
            update_option('ays_gpg_sale_date', current_time( 'mysql' ));
        }

        if(isset($_POST['ays_gpg_sale_btn_for_two_months'])){
            update_option('ays_gpg_sale_btn_for_two_months', 1);
            update_option('ays_gpg_sale_date', current_time( 'mysql' ));
        }

        $ays_gpg_sale_date = get_option('ays_gpg_sale_date');
       
        $ays_gpg_sale_two_months = get_option('ays_gpg_sale_btn_for_two_months');

        $val = 60*60*24*5;
        if($ays_gpg_sale_two_months == 1){
            $val = 60*60*24*61;
        }

        $current_date = current_time( 'mysql' );
        $date_diff = strtotime($current_date) -  intval(strtotime($ays_gpg_sale_date)) ;
       
        $days_diff = $date_diff / $val;

        if(intval($days_diff) > 0 ){
         
            update_option('ays_gpg_sale_btn', 0);
            update_option('ays_gpg_sale_btn_for_two_months', 0);
        }

       
        $ays_gpg_ishmar = intval(get_option('ays_gpg_sale_btn'));
        $ays_gpg_ishmar += intval(get_option('ays_gpg_sale_btn_for_two_months'));
        if($ays_gpg_ishmar == 0 ){
            if (isset($_GET['page']) && strpos($_GET['page'], AYS_GALLERY_NAME) !== false) {
                self::ays_gpg_sale_new_message($ays_gpg_ishmar);
                // self::ays_gpg_helloween_message($ays_gpg_ishmar);
            }
        }
    }    


    public static function ays_autoembed( $content ) {
        global $wp_embed;

        if ( is_null( $content ) ) {
            return $content;
        }

        $content = stripslashes( wpautop( $content ) );
        $content = $wp_embed->autoembed( $content );
        if ( strpos( $content, '[embed]' ) !== false ) {
            $content = $wp_embed->run_shortcode( $content );
        }
        $content = do_shortcode( $content );
        return $content;
    }
    
    public static function get_galleries(){
        global $wpdb;        
        $galleries_table = $wpdb->prefix . 'ays_gallery';
        $sql = "SELECT id,title
                FROM {$galleries_table}";

        $galleries = $wpdb->get_results( $sql , "ARRAY_A" );

        return $galleries;
    }

    // New Mega Bundle
    public static function ays_gpg_sale_new_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-gpg-new-mega-bundle-dicount-month-main" class="notice notice-success is-dismissible ays_gpg_dicount_info">';
                $content[] = '<div id="ays-gpg-dicount-month" class="ays_gpg_dicount_month">';
                    $content[] = '<div class="ays-gpg-dicount-wrap-box ays-gpg-dicount-wrap-text-box">';
                        $content[] = '<div class="ays-gpg-dicount-wrap-box ays-gpg-dicount-wrap-text-boxes">';
                        $content[] = '<div>';
                            $content[] = '<span class="ays-gpg-new-mega-bundle-title">';
                                 $content[] = __( "<span><a href='https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=sale-banner-gallery' target='_blank' style='color:#ffffff; text-decoration: underline;'>Photo Gallery</a></span>", AYS_GALLERY_NAME );
                            $content[] = '</span>';                                
                        $content[] = '</div>';
                        $content[] = '<div>';
                            $content[] = '<img src="' . AYS_GPG_ADMIN_URL . '/images/ays-gpg-banner-sale-20.svg" style="width: 70px;">';
                        $content[] = '</div>';
                        
                        $content[] = '</div>'; 
                        $content[] = '<div>';
                                $content[] = '<img class="ays-gpg-new-mega-bundle-guaranteeicon" src="' . AYS_GPG_ADMIN_URL . '/images/gallery-guaranteeicon.svg" style="width: 30px;">';
                                $content[] = '<span class="ays-gpg-new-mega-bundle-desc">';
                                    $content[] = __( "30 Days Money Back Guarantee", AYS_GALLERY_NAME );
                                $content[] = '</span>';
                            $content[] = '</div>';                     

                        $content[] = '<div style="position: absolute;right: 10px;bottom: 1px;" class="ays-gpg-dismiss-buttons-container-for-form">';

                            $content[] = '<form action="" method="POST">';
                                $content[] = '<div id="ays-gpg-dismiss-buttons-content">';
                                    $content[] = '<button class="btn btn-link ays-button" name="ays_gpg_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0">Dismiss ad</button>';
                                    $content[] = wp_nonce_field( 'photo-gallery-sale-banner' ,  'photo-gallery-sale-banner' );
                                $content[] = '</div>';
                            $content[] = '</form>';
                            
                        $content[] = '</div>';

                    $content[] = '</div>';

                    $content[] = '<div class="ays-gpg-dicount-wrap-box ays-gpg-dicount-wrap-countdown-box">';

                        $content[] = '<div id="ays-gpg-countdown-main-container">';
                            $content[] = '<div class="ays-gpg-countdown-container">';

                                $content[] = '<div id="ays-gpg-countdown">';

                                    $content[] = '<div>';
                                        $content[] = __( "Offer ends in:", AYS_GALLERY_NAME );
                                    $content[] = '</div>';

                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-gpg-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-gpg-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';
                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-gpg-dicount-wrap-box ays-gpg-dicount-wrap-button-box">';
                        $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=sale-banner-gallery" class="button button-primary ays-button" id="ays-button-top-buy-now" target="_blank">' . __( 'Buy Now', AYS_GALLERY_NAME ) . '</a>';
                        $content[] = '<span class="ays-gpg-dicount-one-time-text">';
                            $content[] = __( "One-time payment", AYS_GALLERY_NAME );
                        $content[] = '</span>';
                    $content[] = '</div>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }    

    public static function ays_gpg_sale_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-gpg-dicount-month-main" class="notice notice-success is-dismissible ays_gpg_dicount_info">';
                $content[] = '<div id="ays-gpg-dicount-month" class="ays_gpg_dicount_month">';                    
                    $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery" target="_blank" class="ays-gpg-sale-banner-link"><img src="' . AYS_GPG_ADMIN_URL . '/images/gallery.png"></a>';
                    $content[] = '<div class="ays-gpg-dicount-wrap-box">';

                        $content[] = '<strong style="font-weight: bold;">';
                            $content[] = __( "Limited Time <span class='ays-gpg-dicount-wrap-color'>20%</span> SALE on <span><a href='https://ays-pro.com/wordpress/photo-gallery' target='_blank' class='ays-gpg-dicount-wrap-color ays-gpg-dicount-wrap-text-decoration' style='display:block;'>Photo Gallery</a></span>", AYS_GALLERY_NAME );
                        $content[] = '</strong>';
                        $content[] = '<strong>';
                                $content[] = __( "Hurry up! <a href='https://ays-pro.com/wordpress/photo-gallery' target='_blank'>Check it out!</a>", AYS_GALLERY_NAME );
                        $content[] = '</strong>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-gpg-dicount-wrap-box">';

                        $content[] = '<div id="ays-gpg-countdown-main-container">';
                            $content[] = '<div class="ays-gpg-countdown-container">';

                                $content[] = '<div id="ays-gpg-countdown">';
                                    $content[] = '<ul>';
                                        $content[] = '<li><span id="ays-gpg-countdown-days"></span>days</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-hours"></span>Hours</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-minutes"></span>Minutes</li>';
                                        $content[] = '<li><span id="ays-gpg-countdown-seconds"></span>Seconds</li>';
                                    $content[] = '</ul>';
                                $content[] = '</div>';

                                $content[] = '<div id="ays-gpg-countdown-content" class="emoji">';
                                    $content[] = '<span>🚀</span>';
                                    $content[] = '<span>⌛</span>';
                                    $content[] = '<span>🔥</span>';
                                    $content[] = '<span>💣</span>';
                                $content[] = '</div>';

                            $content[] = '</div>';                           

                        $content[] = '</div>';
                            
                    $content[] = '</div>';

                    $content[] = '<div class="ays-gpg-dicount-wrap-box ays-buy-now-button-box">';
                        $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery" class="button button-primary ays-buy-now-button" id="ays-button-top-buy-now" target="_blank" style="" >' . __( 'Buy Now !', AYS_GALLERY_NAME ) . '</a>';
                    $content[] = '</div>';              

                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;" class="ays-gpg-dismiss-buttons-container-for-form">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-gpg-dismiss-buttons-content">';                         
                            $content[] = '<button class="btn btn-link ays-button" name="ays_gpg_sale_btn" style="height: 32px; margin-left: 0;padding-left: 0; color: #979797;">Dismiss ad</button>';
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';

            $content[] = '</div>';

            $content = implode( '', $content );
            echo $content;
        }
    }

    public static function ays_gpg_helloween_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-gpg-dicount-month-main-helloween" class="notice notice-success is-dismissible ays_gpg_dicount_info">';
                $content[] = '<div id="ays-gpg-dicount-month-helloween" class="ays_gpg_dicount_month_helloween">';
                    $content[] = '<div class="ays-gpg-dicount-wrap-box-helloween-limited">';

                        $content[] = '<p>';
                            $content[] = __( "Limited Time 
                            <span class='ays-gpg-dicount-wrap-color-helloween' style='color:#b2ff00;'>20%</span> 
                            <span>
                                SALE on
                            </span> 
                            <br>
                            <span style='' class='ays-gpg-helloween-bundle'>
                                <a href='https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=sale-banner-gallery' target='_blank' class='ays-gpg-dicount-wrap-color-helloween ays-gpg-dicount-wrap-text-decoration-helloween' style='display:block; color:#b2ff00;margin-right:6px;'>
                                    Photo Gallery
                                </a>
                            </span>", AYS_GPG_ADMIN_URL );
                        $content[] = '</p>';
                        $content[] = '<p>';
                                $content[] = __( "Hurry up! 
                                                <a href='https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=sale-banner-gallery' target='_blank' style='color:#ffc700;'>
                                                    Check it out!
                                                </a>", AYS_GPG_ADMIN_URL );
                        $content[] = '</p>';
                            
                    $content[] = '</div>';

                    
                    $content[] = '<div class="ays-gpg-helloween-bundle-buy-now-timer">';
                        $content[] = '<div class="ays-gpg-dicount-wrap-box-helloween-timer">';
                            $content[] = '<div id="ays-gpg-countdown-main-container" class="ays-gpg-countdown-main-container-helloween">';
                                $content[] = '<div class="ays-gpg-countdown-container-helloween">';
                                    $content[] = '<div id="ays-gpg-countdown">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><p><span id="ays-gpg-countdown-days"></span><span>days</span></p></li>';
                                            $content[] = '<li><p><span id="ays-gpg-countdown-hours"></span><span>Hours</span></p></li>';
                                            $content[] = '<li><p><span id="ays-gpg-countdown-minutes"></span><span>Mins</span></p></li>';
                                            $content[] = '<li><p><span id="ays-gpg-countdown-seconds"></span><span>Secs</span></p></li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';

                                    $content[] = '<div id="ays-gpg-countdown-content" class="emoji">';
                                        $content[] = '<span>🚀</span>';
                                        $content[] = '<span>⌛</span>';
                                        $content[] = '<span>🔥</span>';
                                        $content[] = '<span>💣</span>';
                                    $content[] = '</div>';

                                $content[] = '</div>';

                            $content[] = '</div>';
                                
                        $content[] = '</div>';
                        $content[] = '<div class="ays-gpg-dicount-wrap-box ays-buy-now-button-box-helloween">';
                            $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery?utm_source=dashboard-gallery&utm_medium=free-gallery&utm_campaign=sale-banner-gallery" class="button button-primary ays-buy-now-button-helloween" id="ays-button-top-buy-now-helloween" target="_blank" style="" >' . __( 'Buy Now !', AYS_GPG_ADMIN_URL ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';

                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-gpg-dismiss-buttons-container-for-form-helloween">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-gpg-dismiss-buttons-content-helloween">';
                            $content[] = '<button class="btn btn-link ays-button-helloween" name="ays_gpg_sale_btn" style="">Dismiss ad</button>';
                            $content[] = wp_nonce_field( 'photo-gallery-sale-banner' ,  'photo-gallery-sale-banner' );
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );

            echo $content;
        }
    }

    // Black Friday
    public static function ays_gpg_black_friday_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-gpg-dicount-black-friday-month-main" class="notice notice-success is-dismissible ays_gpg_dicount_info">';
                $content[] = '<div id="ays-gpg-dicount-black-friday-month" class="ays_gpg_dicount_month">';
                    $content[] = '<div class="ays-gpg-dicount-black-friday-box">';
                        $content[] = '<div class="ays-gpg-dicount-black-friday-wrap-box ays-gpg-dicount-black-friday-wrap-box-80" style="width: 70%;">';
                            $content[] = '<div class="ays-gpg-dicount-black-friday-title-row">' . __( 'Limited Time', AYS_GALLERY_NAME ) .' '. '<a href="https://ays-pro.com/wordpress/photo-gallery" class="ays-gpg-dicount-black-friday-button-sale" target="_blank">' . __( 'Sale', AYS_GALLERY_NAME ) . '</a>' . '</div>';
                            $content[] = '<div class="ays-gpg-dicount-black-friday-title-row">' . __( 'Photo Gallery plugin', AYS_GALLERY_NAME ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-gpg-dicount-black-friday-wrap-box ays-gpg-dicount-black-friday-wrap-text-box">';
                            $content[] = '<div class="ays-gpg-dicount-black-friday-text-row">' . __( '20% off', AYS_GALLERY_NAME ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-gpg-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<div id="ays-gpg-countdown-main-container">';
                                $content[] = '<div class="ays-gpg-countdown-container">';
                                    $content[] = '<div id="ays-gpg-countdown" style="display: block;">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><span id="ays-gpg-countdown-days">0</span>' . __( 'Days', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-hours">0</span>' . __( 'Hours', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-minutes">0</span>' . __( 'Minutes', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-seconds">0</span>' . __( 'Seconds', AYS_GALLERY_NAME ) . '</li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';
                                    $content[] = '<div id="ays-gpg-countdown-content" class="emoji" style="display: none;">';
                                        $content[] = '<span>🚀</span>';
                                        $content[] = '<span>⌛</span>';
                                        $content[] = '<span>🔥</span>';
                                        $content[] = '<span>💣</span>';
                                    $content[] = '</div>';
                                $content[] = '</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-gpg-dicount-black-friday-wrap-box" style="width: 25%;">';
                            $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery" class="ays-gpg-dicount-black-friday-button-buy-now" target="_blank">' . __( 'Get Your Deal', AYS_GALLERY_NAME ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-gpg-dismiss-buttons-container-for-form-black-friday">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-gpg-dismiss-buttons-content-black-friday">';
                            $content[] = '<button class="btn btn-link ays-button-black-friday" name="ays_gpg_sale_btn" style="">' . __( 'Dismiss ad', AYS_GALLERY_NAME ) . '</button>';
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>'; 
            $content[] = '</div>';

            $content = implode( '', $content );

            echo $content;
        }
    }

     // Christmas banner
    public static function ays_gpg_christmas_message($ishmar){
        if($ishmar == 0 ){
            $content = array();

            $content[] = '<div id="ays-gpg-dicount-christmas-month-main" class="notice notice-success is-dismissible ays_gpg_dicount_info">';
                $content[] = '<div id="ays-gpg-dicount-christmas-month" class="ays_gpg_dicount_month">';
                    $content[] = '<div class="ays-gpg-dicount-christmas-box">';
                        $content[] = '<div class="ays-gpg-dicount-christmas-wrap-box ays-gpg-dicount-christmas-wrap-box-80">';
                            $content[] = '<div class="ays-gpg-dicount-christmas-title-row">' . __( 'Limited Time', AYS_GALLERY_NAME ) .' '. '<a href="https://ays-pro.com/wordpress/photo-gallery" class="ays-gpg-dicount-christmas-button-sale" target="_blank">' . __( '20%', AYS_GALLERY_NAME ) . '</a>' . ' SALE</div>';
                            $content[] = '<div class="ays-gpg-dicount-christmas-title-row">' . __( 'Photo Gallery Plugin', AYS_GALLERY_NAME ) . '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-gpg-dicount-christmas-wrap-box" style="width: 25%;">';
                            $content[] = '<div id="ays-gpg-countdown-main-container">';
                                $content[] = '<div class="ays-gpg-countdown-container">';
                                    $content[] = '<div id="ays-gpg-countdown" style="display: block;">';
                                        $content[] = '<ul>';
                                            $content[] = '<li><span id="ays-gpg-countdown-days"></span>' . __( 'Days', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-hours"></span>' . __( 'Hours', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-minutes"></span>' . __( 'Minutes', AYS_GALLERY_NAME ) . '</li>';
                                            $content[] = '<li><span id="ays-gpg-countdown-seconds"></span>' . __( 'Seconds', AYS_GALLERY_NAME ) . '</li>';
                                        $content[] = '</ul>';
                                    $content[] = '</div>';
                                    $content[] = '<div id="ays-gpg-countdown-content" class="emoji" style="display: none;">';
                                        $content[] = '<span>🚀</span>';
                                        $content[] = '<span>⌛</span>';
                                        $content[] = '<span>🔥</span>';
                                        $content[] = '<span>💣</span>';
                                    $content[] = '</div>';
                                $content[] = '</div>';
                            $content[] = '</div>';
                        $content[] = '</div>';

                        $content[] = '<div class="ays-gpg-dicount-christmas-wrap-box" style="width: 25%;">';
                            $content[] = '<a href="https://ays-pro.com/wordpress/photo-gallery" class="ays-gpg-dicount-christmas-button-buy-now" target="_blank">' . __( 'BUY NOW!', AYS_GALLERY_NAME ) . '</a>';
                        $content[] = '</div>';
                    $content[] = '</div>';
                $content[] = '</div>';

                $content[] = '<div style="position: absolute;right: 0;bottom: 1px;"  class="ays-gpg-dismiss-buttons-container-for-form-christmas">';
                    $content[] = '<form action="" method="POST">';
                        $content[] = '<div id="ays-gpg-dismiss-buttons-content-christmas">';
                            $content[] = '<button class="btn btn-link ays-button-christmas" name="ays_gpg_sale_btn" style="">' . __( 'Dismiss ad', AYS_GALLERY_NAME ) . '</button>';
                        $content[] = '</div>';
                    $content[] = '</form>';
                $content[] = '</div>';
            $content[] = '</div>';

            $content = implode( '', $content );

            echo $content;
        }
    }

}
