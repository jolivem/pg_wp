<?php
namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Widget_GPG_Custom_Elementor_Thing extends Widget_Base {
    public function get_name() {
        return 'geolocated-photo';
    }
    public function get_title() {
        return __( 'Geolocated Photos', 'elementor-custom-element' );
    }
    public function get_icon() {
        // Icon name from the Elementor font file, as per http://dtbaker.net/web-development/creating-your-own-custom-elementor-widgets/
        return 'glp_fa_power_off_gpg';
    }
    protected function _register_controls() {
        $this->start_controls_section(
            'section_my_custom_gpg',
            array(
                'label' => esc_html__( 'Geolocated Photos', 'elementor' ),
            )
        );

        $this->add_control(
            'gallery_title',
            array(
                'label' => __( 'Gallery Title', 'elementor-custom-element' ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'title' => __( 'Enter the gallery title', 'elementor-custom-element' ),
            )
        );
        $this->add_control(
            'gallery_title_alignment',
            array(
                'label' => __( 'Title Alignment', 'elementor-custom-element' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => array(
                    'left'      => 'Left',
                    'right'     => 'Right',
                    'center'    => 'Center'
                )
            )
        );
        $this->add_control(
            'gallery_selector',
            array(
                'label' => __( 'Select Gallery', 'elementor-custom-element' ),
                'type' => Controls_Manager::SELECT,
                'default' => $this->get_default_gallery(),
                'options' => $this->get_active_galleries()
            )
        );

        $this->end_controls_section();
    }
    protected function render( $instance = [] ) {
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor_ajax' ) ) {
            echo '<style>                

                div.elementor-widget-gallery-photo-gallery>div.elementor-widget-container {
                    width: 100%;
                    padding: 6px 8px;
                    font-size: 13px;
                    border: 1px solid #757575;
                    border-radius: 2px;
                    background-color: #f0f0f1;
                    color: #2c3338;
                }
            </style>';
        }

        $settings = $this->get_settings_for_display();
        echo ( isset( $settings['gallery_title'] ) && ! empty( $settings['gallery_title'] ) ) ? "<h2 style='text-align: {$settings['gallery_title_alignment']}'>{$settings['gallery_title']}</h2>" : "";
        
        // echo do_shortcode("[gallery_p_gallery id={$settings['gallery_selector']}]");

        echo ("[gallery_p_gallery id={$settings['gallery_selector']}]");
    }

    public function get_active_galleries(){
        global $wpdb;
        $gallery_table = $wpdb->prefix . 'glp_gallery';
        $sql = "SELECT id,title FROM {$gallery_table};";
        $results = $wpdb->get_results( $sql, ARRAY_A );
        $options = array();
        foreach ( $results as $result ){
            $options[$result['id']] = $result['title'];
        }
        return $options;
    }

    public function get_default_gallery(){
        global $wpdb;
        $gallery_table = $wpdb->prefix . 'glp_gallery';
        $sql = "SELECT id FROM {$gallery_table} limit 1;";
        $id = $wpdb->get_var( $sql );

        return intval($id);
    }

    protected function content_template() {}
    public function render_plain_content( $instance = [] ) {}
}
