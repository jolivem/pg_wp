<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/includes
 * @author     GLP <info@glp-plugin.com>
 */
class Geolocated_Photo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Geolocated_Photo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GLP_GALLERY_NAME_VERSION' ) ) {
			$this->version = GLP_GALLERY_NAME_VERSION;
		} else {
			$this->version = '3.0.4';
		}
		$this->plugin_name = 'geolocated-photo';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Geolocated_Photo_Loader. Orchestrates the hooks of the plugin.
	 * - Geolocated_Photo_i18n. Defines internationalization functionality.
	 * - GLP_Admin. Defines all hooks for the admin area.
	 * - Glp_Gallery_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        
        /**
		 * The class responsible for defining all functions for getting all quiz data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-gallery-data.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/glp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/geolocated-photo-category-shortcode.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-user-photos.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-user-galleries.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-download-multiple.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-download-single.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-edit-photo.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-contact-mail.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-check-photos.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-check-user-url.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-show-user-map.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-show-planet-map.php';

		/*require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-show-user-gallery.php';*/

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/pg-edit-gallery.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/glp-gallery-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/geolocated-photo-extra-shortcode.php';

		/**
         * The class is responsible for showing gallery settings
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings/glp-settings-actions.php';

        /*
         * The class is responsible for showing galleries in wordpress default WP_LIST_TABLE style
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-galleries-list-table.php';
        
        //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-categories-list-table.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-maps-list-table.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/pg-geoposts-table.php';

		$this->loader = new Geolocated_Photo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Geolocated_Photo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Geolocated_Photo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new GLP_Admin( $this->get_plugin_name(), $this->get_version() );
		$data_admin   = new Glp_Gallery_Data( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Add menu item
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        
        $this->loader->add_action('wp_ajax_gen_glp_shortcode', $plugin_admin, 'gen_glp_shortcode_callback');
        $this->loader->add_filter("mce_external_plugins", $plugin_admin, "glp_register_tinymce_plugin");
        $this->loader->add_filter('mce_buttons', $plugin_admin, 'glp_add_tinymce_button');

        $this->loader->add_action( 'wp_ajax_deactivate_plugin_option_pm', $plugin_admin, 'deactivate_plugin_option');
        $this->loader->add_action( 'wp_ajax_nopriv_deactivate_plugin_option_pm', $plugin_admin, 'deactivate_plugin_option');

        $this->loader->add_action( 'wp_ajax_glp_author_user_search', $plugin_admin, 'glp_author_user_search' );
        $this->loader->add_action( 'wp_ajax_nopriv_glp_author_user_search', $plugin_admin, 'glp_author_user_search' );

        // Add Settings link to the plugin
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

        // Add row meta link to the plugin
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'add_plugin_row_meta',10 ,2 );

        // Before VC Init
        $this->loader->add_action( 'vcv:api', $plugin_admin, 'vc_before_init_actions' );

        $this->loader->add_action( 'elementor/widgets/widgets_registered', $plugin_admin, 'gpg_el_widgets_registered' );

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'codemirror_enqueue_scripts');
		
		$this->loader->add_action( 'in_admin_footer', $plugin_admin, 'gallery_admin_footer', 1 );

		// Sale Banner
        // $this->loader->add_action( 'admin_notices', $plugin_admin, 'glp_sale_baner', 1 );
        //$this->loader->add_action( 'admin_notices', $data_admin, 'glp_sale_baner', 1 );

        $this->loader->add_action( 'wp_ajax_glp_dismiss_button', $plugin_admin, 'glp_dismiss_button' );
        $this->loader->add_action( 'wp_ajax_nopriv_glp_dismiss_button', $plugin_admin, 'glp_dismiss_button' );

		// custom fields
		//$this->loader->add_filter('attachment_fields_to_edit', $plugin_admin, 'add_custom_fields_to_media_edit_screen', 10, 2);
		//$this->loader->add_filter('attachment_fields_to_save', $plugin_admin, 'save_custom_fields_value', 10, 2);
		

		//$this->loader->add_action( 'add_attachment', $plugin_admin, 'extract_exif_data' );
	
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_user_photos_public = new Glp_User_Photos_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_user_delete_photo', $plugin_user_photos_public, 'user_delete_photo');
        //$this->loader->add_action( 'wp_ajax_nopriv_user_delete_photo', $plugin_user_photos_public, 'user_delete_photo'); // TODO be removed
		
		$plugin_check_urls_public = new Glp_Check_User_Url_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_admin_valid_url', $plugin_check_urls_public, 'admin_valid_url');
        //$this->loader->add_action( 'wp_ajax_nopriv_admin_valid_url', $plugin_check_urls_public, 'admin_valid_url'); // TODO be removed
        $this->loader->add_action( 'wp_ajax_admin_reject_url', $plugin_check_urls_public, 'admin_reject_url');
        //$this->loader->add_action( 'wp_ajax_nopriv_admin_reject_url', $plugin_check_urls_public, 'admin_reject_url'); // TODO be removed

		$plugin_check_photos_public = new Glp_Check_Photos_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_admin_valid_photo', $plugin_check_photos_public, 'admin_valid_photo');
        //$this->loader->add_action( 'wp_ajax_nopriv_admin_valid_photo', $plugin_check_photos_public, 'admin_valid_photo'); // TODO be removed
        $this->loader->add_action( 'wp_ajax_admin_reject_photo', $plugin_check_photos_public, 'admin_reject_photo');
        //$this->loader->add_action( 'wp_ajax_nopriv_admin_reject_photo', $plugin_check_photos_public, 'admin_reject_photo'); // TODO be removed

		$plugin_user_galleries = new Glp_User_Galleries_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_hide_galleries_help', $plugin_user_galleries, 'hide_galleries_help');
        //$this->loader->add_action( 'wp_ajax_nopriv_hide_galleries_help', $plugin_user_galleries, 'hide_galleries_help'); // TODO be removed

        //$plugin_map_public = new Glp_Map_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_gallery_category = new Geolocated_Photo_Category( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_extra_shortcodes = new Ays_Gallery_Extra_Shortcodes_Public( $this->get_plugin_name(), $this->get_version() );

		$plugin_gallery_public = new Glp_Gallery_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $plugin_gallery_public, 'ays_initialize_gallery_shortcode'); // TODO maybe removed
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_gallery_public, 'enqueue_styles_early' );
		$this->loader->add_filter( 'wp_img_tag_add_decoding_attr', $plugin_gallery_public, 'ays_gallery_wp_get_attachment_image_attributes' );

		$plugin_download_multiple = new Pg_Download_Multiple_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_download_multiple_photos', $plugin_download_multiple, 'download_multiple_photos');

		$plugin_download_single = new Pg_Download_Single_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_download_single_photo', $plugin_download_single, 'download_single_photo');

		$plugin_edit_photo = new Pg_Edit_Photo_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_user_save_photo', $plugin_edit_photo, 'user_save_photo'); // save photo
        $this->loader->add_action( 'wp_ajax_user_get_photo', $plugin_edit_photo, 'user_get_photo');

        $plugin_contact_mail = new Pg_Contact_Mail_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_contact_mail', $plugin_contact_mail, 'contact_mail');
		$this->loader->add_action( 'wp_ajax_nopriv_contact_mail', $plugin_contact_mail, 'contact_mail');

        $plugin_edit_gallery = new Pg_Edit_Gallery_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_user_edit_gallery', $plugin_edit_gallery, 'user_edit_gallery');
        $this->loader->add_action( 'wp_ajax_user_delete_gallery', $plugin_edit_gallery, 'user_delete_gallery');
        $this->loader->add_action( 'wp_ajax_hide_gallery_help', $plugin_edit_gallery, 'hide_gallery_help');

        $planet = new Pg_Show_Planet_Map_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_ajax_ban_image', $planet, 'ban_image');
        $this->loader->add_action( 'wp_ajax_get_bb_images', $planet, 'get_bb_images');
        $this->loader->add_action( 'wp_ajax_nopriv_get_bb_images', $planet, 'get_bb_images');

		new Pg_Show_User_Map_Public( $this->get_plugin_name(), $this->get_version() );

        new Pg_Geoposts_Table( $this->get_plugin_name(), $this->get_version() );
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Geolocated_Photo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
