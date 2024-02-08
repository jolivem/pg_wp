<?php
    /**
     * Enqueue front end and editor JavaScript
     */

    function glp_gutenberg_scripts() {        
        global $current_screen;
        global $wp_version;
        $version1 = $wp_version;
        $operator = '>=';
        $version2 = '5.3.1';
        $versionCompare = aysGalleryVersionCompare($version1, $operator, $version2);
        if( ! $current_screen ){
            return null;
        }

        if( ! $current_screen->is_block_editor ){
            return null;
        }

        // Enqueue the bundled block JS file
        if( $versionCompare ){
            wp_enqueue_script(
                'glp-block-js',
                GLP_BASE_URL ."/gallery/glp-new.js",
                array( 'jquery', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
                GLP_GALLERY_VERSION, true
            );
        }
        else{
            wp_enqueue_script(
                'glp-block-js',
                GLP_BASE_URL ."/gallery/glp-block.js",
                array( 'jquery', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
                GLP_GALLERY_VERSION, true
            );
        }
        
        // Enqueue the bundled block CSS file
        if( $versionCompare ){            
            wp_enqueue_style(
                'glp-block-css',
                GLP_BASE_URL ."/gallery/glp-block-new.css",
                array(),
                GLP_GALLERY_VERSION, 'all'
            );
        }
        else{            
            wp_enqueue_style(
                'glp-block-css',
                GLP_BASE_URL ."/gallery/glp-block.css",
                array(),
                GLP_GALLERY_VERSION, 'all'
            );
        }
    }

    function glp_gutenberg_block_register() {
        
        global $wpdb;
        $block_name = 'gallery';
        $block_namespace = 'geolocated-photo/' . $block_name;
        
        $sql = "SELECT * FROM ". $wpdb->prefix . "glp_gallery";
        $results = $wpdb->get_results($sql, "ARRAY_A");
        
        register_block_type(
            $block_namespace, 
            array(
                'render_callback'   => 'glp_gallery_render_callback',
                'editor_script'     => 'glp-block-js',
                'style'             => 'glp-block-css',
                'attributes'	    => array(
                    'idner' => $results,
                    'metaFieldValue' => array(
                        'type'  => 'integer', 
                    ),
                    'shortcode' => array(
                        'type'  => 'string',				
                    ),
                    'className' => array(
                        'type'  => 'string',
                    ),
                    'openPopupId' => array(
                        'type'  => 'string',
                    ),
                ),                
            )
        );       
    }    
    
    function glp_gallery_render_callback( $attributes ) { 

        $ays_html = "<div class='ays-gallery-render-callback-box'></div>";

        if(isset($attributes["metaFieldValue"]) && $attributes["metaFieldValue"] === 0) {
            return $ays_html;
        }

        if(isset($attributes["shortcode"]) && $attributes["shortcode"] != '') {
            // $ays_html = do_shortcode( $attributes["shortcode"] );
            $ays_html = $attributes["shortcode"] ;
        }
        return $ays_html;
    }

    function aysGalleryVersionCompare($version1, $operator, $version2) {
    
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

    if(function_exists("register_block_type")){
            // Hook scripts function into block editor hook
        add_action( 'enqueue_block_editor_assets', 'glp_gutenberg_scripts' );
        add_action( 'init', 'glp_gutenberg_block_register' );
    }