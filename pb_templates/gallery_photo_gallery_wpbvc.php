<?php
/*
Element Description: VC Gallery - Photo Gallery
*/
if( class_exists( 'WPBakeryShortCode' ) ) {
    // Element Class
    class vcGalleryPhotoGallery extends WPBakeryShortCode {

        function __construct() {
            add_action( 'init', array( $this, 'vc_galleryphotogallery_mapping' ) );
            add_shortcode( 'vc_gallery_p_gallery', array( $this, 'vc_galleryphotogallery_html' ) );
        }

        public function vc_gallery_p_gallery_mapping() {
            // Stop all if VC is not enabled
            if ( !defined( 'WPB_VC_VERSION' ) ) {
                return;
            }

            // Map the block with vc_map()
            vc_map(
                array(
                    'name' => __('Gallery - Photo Gallery', 'text-domain'),
                    'base' => 'vc_galleryphotogallery',
                    'description' => __('The Best Gallery - Photo Gallery Ever', 'text-domain'),
                    'category' => __('Gallery - Photo Gallery by AYS', 'text-domain'),
                    'icon' => AYS_GPG_ADMIN_URL . '/images/gall_icon.png',
                    'params' => array(
                        array(
                            'type' => 'dropdown',
                            'holder' => 'div',
                            'class' => 'gallery_vc_select',
                            'heading' => __( 'Gallery - Photo Gallery', 'text-domain' ),
                            'param_name' => 'gallery',
                            'value' => $this->get_active_galleries(),
                            'description' => __( 'Please select your gallery from dropdown', 'text-domain' ),
                            'admin_label' => true,
                            'group' => 'Gallery - Photo Gallery'
                        )
                    )
                )
            );
        }

        public function vc_gallery_p_gallery_html( $atts ) {
            // Params extraction
            extract(
                shortcode_atts(
                    array(
                        'gallery'   => null
                    ),
                    $atts
                )
            );
            // Fill $html var with data

            // Fill $html var with data
            $html = do_shortcode("[gallery_p_gallery id={$gallery}]");

            return $html;
        }

        public function get_active_galleries(){
            global $wpdb;
            $gallery_table = $wpdb->prefix . 'glp_gallery';
            $sql = "SELECT id,title FROM {$gallery_table};";
            $results = $wpdb->get_results( $sql, ARRAY_A );
            $options = array();
            $options['Select Gallery'] = '';
            foreach ( $results as $result ){
                $options[$result['title']] = intval( $result['id'] );
            }

            return $options;
        }
    }

    new vcGalleryPhotoGallery();
}