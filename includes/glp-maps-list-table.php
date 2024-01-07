<?php
ob_start();
class Glp_Maps_List_Table extends WP_List_Table{
    private $plugin_name;
    private $title_length;
    /** Class constructor */
    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->title_length = GLP_Admin::get_gpg_listtables_title_length('maps');
        parent::__construct( array(
            "singular" => __( "Map", $this->plugin_name ), //singular name of the listed records
            "plural"   => __( "Maps", $this->plugin_name ), //plural name of the listed records
            "ajax"     => false //does this table support ajax?
        ) );
        add_action( "admin_notices", array( $this, "map_notices" ) );

    }


    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_maps( $per_page = 5, $page_number = 1 , $search = '' ) {

        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}glp_map";

        $where = array();

        if( $search != '' ){
            $where[] = $search;
        }

        if( ! empty($where) ){
            $sql .= " WHERE " . implode( " AND ", $where );
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {

            $order_by  = ( isset( $_REQUEST['orderby'] ) && sanitize_text_field( $_REQUEST['orderby'] ) != '' ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'ordering';
            $order_by .= ( ! empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) == 'asc' ) ? ' ASC' : ' DESC';

            $sql_orderby = sanitize_sql_orderby($order_by);

            if ( $sql_orderby ) {
                $sql .= ' ORDER BY ' . $sql_orderby;
            } else {
                $sql .= ' ORDER BY id DESC';
            }
        }else{
            $sql .= ' ORDER BY id DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= " OFFSET " . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, "ARRAY_A" );

        return $result;
    }

    public function get_map_by_id( $id ){
        global $wpdb;

        $gallery_table = esc_sql($wpdb->prefix . "glp_map");

        $id = absint( sanitize_text_field( $id ));
        $sql = "SELECT * FROM ".$gallery_table." WHERE id = %d";

        $result = $wpdb->get_row(
                    $wpdb->prepare( $sql, $id)
                  , "ARRAY_A");

        return $result;
    }

    public function add_or_edit_map($data){
        global $wpdb;
        error_log("add_or_edit_map data: ".print_r($data, true));
        $gallery_table = $wpdb->prefix . "glp_map";
        if( isset($data["ays_gallery_action"]) && wp_verify_nonce( $data["ays_gallery_action"],"ays_gallery_action" ) ) {
            

            $id                     = ( $data["id"] != NULL ) ? absint( intval( $data["id"] ) ) : null;
            
            // Gallery settings
            //error_log("TODO gallery id:".$id);
            $title                  = (isset($data["gallery_title"]) && $data["gallery_title"] != '') ? stripslashes(sanitize_text_field( $data["gallery_title"] )) : '';
            $description            = !isset($data['gallery_description']) ? '' : wp_kses_post( $data['gallery_description'] );
            $width                  = (isset($data['gallery_width']) && $data['gallery_width'] != '') ? wp_unslash(sanitize_text_field( $data['gallery_width'] )) : '';
            $height                 = 0;
            $view_type              = isset($data['ays-view-type']) && $data['ays-view-type'] != '' ? sanitize_text_field( $data['ays-view-type'] ) : '';

            $options = array(

                "images_request"            => $title
            );
            $lightbox_options = array(
                "lightbox_counter"          => $title,
            );
            $submit_type = (isset($data['submit_type'])) ?  $data['submit_type'] : '';
            if( $id == null ){
                $sql_result = $wpdb->insert(
                    $gallery_table,
                    array(
                        "title"             => $title,
                        "provider"          => 'provider',
                        "options"           => json_encode($options),
                        "lightbox_options"  => json_encode($lightbox_options)
                    ),
                    array( "%s", "%s", "%s", "%s" )
                );
                $message = "created";
            }else{
                $sql_result = $wpdb->update(
                    $gallery_table,
                    array(
                        "title"             => $title,
                        "provider"          => 'provider',
                        "options"           => json_encode($options),
                        "lightbox_options"  => json_encode($lightbox_options)
                    ),
                    array( "id" => $id ),
                    array( "%s", "%s", "%s", "%s" ),
                    array( "%d" )
                );
                $message = "updated";
            }
            $glp_tab = isset($data['glp_settings_tab']) ? $data['glp_settings_tab'] : 'tab1';
            if( $sql_result >= 0 ){
                if($submit_type == ''){
                    $url = esc_url_raw( remove_query_arg(["action", "map"]  ) ) . "&status=" . $message . "&type=success";
                    wp_redirect( $url );
                    exit();
                }else{
                    if($id == null){
                        $url = esc_url_raw( add_query_arg( array(
                            "action"                => "edit",
                            "gallery"               => $wpdb->insert_id,
                            "glp_settings_tab"      => $glp_tab,
                            "status"                => $message
                        ) ) );
                    }else{
                        $url = esc_url_raw( remove_query_arg(false) ) . '&glp_settings_tab='.$glp_tab.'&status=' . $message;
                    }

                    wp_redirect( $url );
                    exit();
                }
            }
        }
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_galleries( $id ) {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}glp_map",
            array( "id" => $id ),
            array( "%d" )
        );
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $filter = array();

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}glp_map";

        $search = ( isset( $_REQUEST['s'] ) ) ? esc_sql( sanitize_text_field( $_REQUEST['s'] ) ) : false;
        if( $search ){
            $filter[] = sprintf(" title LIKE '%%%s%%' ", esc_sql( $wpdb->esc_like( $search ) ) );
        }
        
        if(count($filter) !== 0){
            $sql .= " WHERE ".implode(" AND ", $filter);
        }


        return $wpdb->get_var( $sql );
    }


    /** Text displayed when no customer data is available */
    public function no_items() {
        echo __( "There are no galleries yet.", $this->plugin_name );
    }
    //TODO test duplicate
    public function duplicate_maps( $id ){
        global $wpdb;
        $galleries_table = $wpdb->prefix."glp_map";
        $gallery = $this->get_map_by_id($id);
       
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        $author = array(
            'id' => $user->ID,
            'name' => $user->data->display_name
        );
        
        $max_id = $this->get_max_id();
        $ordering = ( $max_id != NULL ) ? ( $max_id + 1 ) : 1;
        
        $options = json_decode($gallery['options'], true);
        
        $options['create_date'] =  current_time( 'mysql' );
        $options['author'] = $author;
        
        $result = $wpdb->insert(
            $galleries_table,
            array(
                'title'             => "Copy - ".sanitize_text_field($gallery['title']),
                'description'       => sanitize_text_field($gallery['description']),
                'images'            => '',
                'images_titles'     => '',
                'images_descs'      => '',
                'images_alts'       => '',
                'images_urls'       => '',
                'images_dates'      => '',
                'width'             => sanitize_text_field($gallery['width']),
                'height'            => sanitize_text_field($gallery['height']),
                'options'           => json_encode($options),
                'lightbox_options'  => sanitize_text_field($gallery['lightbox_options']),
                'categories_id'     => sanitize_text_field($gallery['categories_id']),
                'custom_css'        => $gallery['custom_css']
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        if( $result >= 0 ){
            $message = "duplicated";
            $url = esc_url_raw( remove_query_arg(array('action', 'question')  ) ) . '&status=' . $message;
            wp_redirect( $url );
        }
        
    }

    private function get_max_id() {
        global $wpdb;
        $gallery_table = $wpdb->prefix . 'glp_map';

        $sql = "SELECT max(id) FROM {$gallery_table}";

        $result = $wpdb->get_var($sql);

        return $result;
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case "title":
            case "image":
            case "description":
                return wp_unslash($item[ $column_name ]);
                break;
            case "shortcode":
            case 'create_date':
            case "items":
            case "id":
                return $item[ $column_name ];
                break;
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            "<input type='checkbox' name='bulk-delete[]' value='%s' />", $item["id"]
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_image( $item ) {
        error_log("column_image IN");
        global $wpdb;
        $gallery_images = isset($item['images']) && $item['images'] != "" ? explode('***', $item['images']) : array();
        $gallery_image  = "";

        $image_html     = array();
        $edit_page_url  = '';

        if(!empty($gallery_images)){
            $gallery_image = isset($gallery_images[0]) && $gallery_images[0] != "" ? $gallery_images[0] : "";

            if ( isset( $item['id'] ) && absint( $item['id'] ) > 0 ) {
                $edit_page_url = sprintf( 'href="?page=%s&action=%s&gallery=%d"', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ) );
            }

            $gallery_image_url = $gallery_image;
            $this_site_path = trim( get_site_url(), "https:" );
            if( strpos( trim( $gallery_image_url, "https:" ), $this_site_path ) !== false ){ 
                $query = "SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'attachment' AND `guid` = '" . $gallery_image_url . "'";
                $result_img =  $wpdb->get_results( $query, "ARRAY_A" );
                if( ! empty( $result_img ) ){
                    $url_img = wp_get_attachment_image_src( $result_img[0]['ID'], 'thumbnail' );
                    if( $url_img !== false ){
                        $gallery_image_url = $url_img[0];
                    }
                }
            }

            $image_html[] = '<div class="ays-gallery-image-list-table-column">';
                $image_html[] = '<a '. $edit_page_url .' class="ays-gallery-image-list-table-link-column">';
                    $image_html[] = '<img src="'. $gallery_image_url .'" class="ays-gallery-list-table-main-image">';
                $image_html[] = '</a>';
            $image_html[] = '</div>';
        }
        
        $image_html = implode('', $image_html);

        return $image_html;
    }    

    function column_title( $item ) {
        //error_log("column_title IN");
        $delete_nonce = wp_create_nonce( $this->plugin_name . "-delete-gallery" );
        $duplicate_nonce = wp_create_nonce( $this->plugin_name . "-duplicate-gallery" );
        $gallery_title = esc_attr(stripcslashes($item['title']));

        $q = esc_attr($gallery_title);
        $gallery_title_length = intval( $this->title_length );

        $restitle = GLP_Admin::glp_restriction_string("word", $gallery_title, $gallery_title_length);

        $title = sprintf( '<a href="?page=%s&action=%s&gallery=%d" title="%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ), $q, $restitle);

        $actions = array(
            "edit" => sprintf( "<a href='?page=%s&action=%s&gallery=%d'>". __('Edit', $this->plugin_name) ."</a>", esc_attr( $_REQUEST["page"] ), "edit", absint( $item["id"] ) ),
            'duplicate' => sprintf( '<a href="?page=%s&action=%s&gallery=%d&_wpnonce=%s">'. __('Duplicate', $this->plugin_name) .'</a>', esc_attr( $_REQUEST['page'] ), 'duplicate', absint( $item['id'] ), $duplicate_nonce ),
            "delete" => sprintf( "<a href='?page=%s&action=%s&gallery=%s&_wpnonce=%s'>". __('Delete', $this->plugin_name) ."</a>", esc_attr( $_REQUEST["page"] ), "delete", absint( $item["id"] ), $delete_nonce )
        );

        return $title . $this->row_actions( $actions );
    }

    function column_shortcode( $item ) {
        error_log("column_shortcode IN");
        return sprintf('<div class="glp-shortcode-container">
                    <div class="glp-copy-image" data-bs-toggle="tooltip" title="'. esc_html(__('Click to copy',$this->plugin_name)).'">
                            <img src="'. esc_url(GLP_ADMIN_URL) . '/images/icons/copy-image.svg">
                    </div>                                            
                    <input type="text" class="glp-shortcode-input" readonly value="'. esc_attr('[glp_map id="%s"]').'" />
                </div>', $item["id"]);
    }

    function column_items( $item ) {
       $item_count = explode('***', $item['images']);
       return count($item_count);
    }

    function column_create_date( $item ) {
        
        $options = json_decode($item['options'], true);
        $date = isset($options['create_date']) && $options['create_date'] != '' ? $options['create_date'] : "0000-00-00 00:00:00";
        if(isset($options['author'])){
            if(is_array($options['author'])){
                $author = $options['author'];
            }else{
                $author = json_decode($options['author'], true);
            }
        }else{
            $author = array("name"=>"Unknown");
        }
        $text = "";
        if(GLP_Admin::validateDate($date)){
            $text .= "<p><b>Date:</b> ".$date."</p>";
        }
        if($author['name'] !== "Unknown"){
            $text .= "<p><b>Author:</b> ".$author['name']."</p>";
        }
        return $text;
    } 

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            "cb"                => "<input type='checkbox' />",
            "title"             => __( "Title", $this->plugin_name ),
            // "image"             => __( "Image", $this->plugin_name ),
            // "description"       => __( "Description", $this->plugin_name ),
            "shortcode"         => __( "Shortcode", $this->plugin_name ),
            "create_date"       => __( 'Created', $this->plugin_name ),
            //"id"                => __( "ID", $this->plugin_name ),
        );

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            "title"         => array( "title", true ),
            "id"            => array( "id", true ),
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            "bulk-delete" => __("Delete", $this->plugin_name)
        );

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        global $wpdb;
        
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( "galleries_per_page", 20 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( array(
            "total_items" => $total_items, //WE have to calculate the total number of items
            "per_page"    => $per_page //WE have to determine how many items to show on a page
        ) );

        $search = ( isset( $_REQUEST['s'] ) ) ? esc_sql( sanitize_text_field( $_REQUEST['s'] ) ) : false;
        $do_search = ( $search ) ? sprintf(" title LIKE '%%%s%%' ", esc_sql( $wpdb->esc_like( $search ) ) ) : '';

        $this->items = self::get_maps( $per_page, $current_page,$do_search );
    }

    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        $message = "deleted";
        if ( "delete" === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST["_wpnonce"] );

            if ( ! wp_verify_nonce( $nonce, $this->plugin_name . "-delete-gallery" ) ) {
                die( "Go get a life script kiddies" );
            }
            else {
                self::delete_galleries( absint( $_GET["gallery"] ) );

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url

                $url = esc_url_raw( remove_query_arg(array("action", "gallery", "_wpnonce")  ) ) . "&status=" . $message . "&type=success";
                wp_redirect( $url );
                exit();
            }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST["action"] ) && $_POST["action"] == "bulk-delete" )
            || ( isset( $_POST["action2"] ) && $_POST["action2"] == "bulk-delete" )
        ) {

            $delete_ids = ( isset( $_POST['bulk-delete'] ) && ! empty( $_POST['bulk-delete'] ) ) ? esc_sql( $_POST['bulk-delete'] ) : array();

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_galleries( $id );

            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url

            $url = esc_url_raw( remove_query_arg(array("action", "gallery", "_wpnonce")  ) ) . "&status=" . $message . "&type=success";
            wp_redirect( $url );
            exit();
        }
    }

    public function map_notices(){
        $status = (isset($_REQUEST["status"])) ? sanitize_text_field( $_REQUEST["status"] ) : "";
        $type = (isset($_REQUEST["type"])) ? sanitize_text_field( $_REQUEST["type"] ) : "success";

        if ( empty( $status ) )
            return;

        if ( "created" == $status )
            $updated_message = esc_html( __( "Map created.", $this->plugin_name ) );
        elseif ( "updated" == $status )
            $updated_message = esc_html( __( "Map saved.", $this->plugin_name ) );
        elseif ( 'duplicated' == $status )
            $updated_message = esc_html( __( 'Map duplicated.', $this->plugin_name ) );
        elseif ( "deleted" == $status )
            $updated_message = esc_html( __( "Map deleted.", $this->plugin_name ) );
        elseif ( "error" == $status )
            // TODO check this limitation
            $updated_message = __( "You're not allowed to add more maps. Please checkout to ", $this->plugin_name )."<a href='http://glp-plugin.com/wordpress/photo-gallery' target='_blank'>PRO ".__( "version", $this->plugin_name )."</a>.";

        if ( empty( $updated_message ) )
            return;

        ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }
}
