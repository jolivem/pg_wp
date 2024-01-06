<?php
class Gallery_Settings_Actions {
    private $plugin_name;

    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->check_setting_options();
    }

    public function store_data($data){
        global $wpdb;
        $settings_table = $wpdb->prefix . "glp_gallery_settings";
        if( isset($data["settings_action"]) && wp_verify_nonce( $data["settings_action"], 'settings_action' ) ){
            $success = 0;
            // WP Editor height
            $gpg_wp_editor_height = (isset($data['glp_wp_editor_height']) && $data['glp_wp_editor_height'] != '') ? absint( sanitize_text_field($data['glp_wp_editor_height']) ) : 100 ;

            // All images text
            $gpg_all_images_text = (isset($data['glp_all_images_text']) && $data['glp_all_images_text'] != '') ? stripslashes( esc_attr($data['glp_all_images_text']) ) : 'All';
            
            // Gallery title length
            $ays_galleries_title_length = (isset($data['ays_galleries_title_length']) && $data['ays_galleries_title_length'] != '') ? absint( sanitize_text_field($data['ays_galleries_title_length']) ) : 5 ;

            // Gallery categories title length
            $gpg_categories_title_length = (isset($data['glp_categories_title_length']) && intval($data['glp_categories_title_length']) != 0) ? absint(sanitize_text_field($data['glp_categories_title_length'])) : 5;

            // General CSS File
            $gpg_exclude_general_css = (isset( $data['glp_exclude_general_css'] ) && sanitize_text_field( $data['glp_exclude_general_css'] ) == 'on') ? 'on' : 'off';

            $options = array(
                "gpg_wp_editor_height"          => $gpg_wp_editor_height,
                "gpg_all_images_text"           => $gpg_all_images_text,
                "galleries_title_length"        => $ays_galleries_title_length,
                "gpg_categories_title_length"   => $gpg_categories_title_length,
                "gpg_exclude_general_css"       => $gpg_exclude_general_css
            );
            
            $result = $this->ays_update_setting('options', json_encode($options, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));            
            if ($result) {
                $success++;
            }            

            $message = "saved";
            if($success > 0){
                $tab = "";
                if(isset($data['glp_tab'])){
                    $tab = "&glp_tab=".$data['glp_tab'];
                }
                $url = admin_url('admin.php') . "?page=glp-settings" . $tab . '&status=' . $message;
                wp_redirect( $url );
            }
        }

    }

    public function get_db_data(){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "glp_gallery_settings");
        $sql = "SELECT * FROM ".$settings_table;
        
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        if(count($results) > 0){
            return $results;
        }else{
            return array();
        }
    }    

    public static function ays_get_setting($meta_key){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "glp_gallery_settings");

        if($wpdb->get_var("SHOW TABLES LIKE '$settings_table'") != $settings_table) {
            return false;
        }
        
        $key_meta = esc_sql($meta_key);
        $sql = "SELECT meta_value FROM ".$settings_table." WHERE meta_key = %s";

        $result = $wpdb->get_var(
                    $wpdb->prepare( $sql, $key_meta)
                  );

        if($result != ""){
            return $result;
        }
        return false;
    }

    public function check_setting_options(){
        global $wpdb;
        $settings_table = esc_sql($wpdb->prefix . "glp_gallery_settings");
        $options = esc_sql('options');
        $sql = "SELECT COUNT(*) FROM ".$settings_table." WHERE meta_key = %s";

        $result = $wpdb->get_var(
                    $wpdb->prepare( $sql, $options)
                  );

        if(intval($result) == 0){
            $this->ays_add_setting("options", "", "", "");
        }
        return false;
    }

    public function ays_add_setting($meta_key, $meta_value, $note = "", $options = ""){
        global $wpdb;
        $settings_table = $wpdb->prefix . "glp_gallery_settings";
        $result = $wpdb->insert(
            $settings_table,
            array(
                'meta_key'    => $meta_key,
                'meta_value'  => $meta_value,
                'note'        => $note,
                'options'     => $options
            ),
            array( '%s', '%s', '%s', '%s' )
        );
        if($result >= 0){
            return true;
        }
        return false;
    }

    public function ays_update_setting($meta_key, $meta_value, $note = null, $options = null){
        global $wpdb;

        $settings_table = $wpdb->prefix . "glp_gallery_settings";
        $value = array(
            'meta_value'  => $meta_value,
        );
        $value_s = array( '%s' );
        if($note != null){
            $value['note'] = $note;
            $value_s[] = '%s';
        }
        if($options != null){
            $value['options'] = $options;
            $value_s[] = '%s';
        }
        $result = $wpdb->update(
            $settings_table,
            $value,
            array( 'meta_key' => $meta_key ),
            $value_s,
            array( '%s' )
        );
        if($result >= 0){
            return true;
        }
        return false;
    }

    public function ays_delete_setting($meta_key){
        global $wpdb;
        $settings_table = $wpdb->prefix . "glp_gallery_settings";
        $wpdb->delete(
            $settings_table,
            array( 'meta_key' => $meta_key ),
            array( '%s' )
        );
    }

    public function gallery_settings_notices($status){

        if ( empty( $status ) )
            return;

        if ( 'saved' == $status )
            $updated_message = esc_html( __( 'Changes saved.', $this->plugin_name ) );
        elseif ( 'updated' == $status )
            $updated_message = esc_html( __( 'Gallery attribute .', $this->plugin_name ) );
        elseif ( 'deleted' == $status )
            $updated_message = esc_html( __( 'Gallery attribute deleted.', $this->plugin_name ) );

        if ( empty( $updated_message ) )
            return;

        ?>
        <div class="notice notice-success is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }

}