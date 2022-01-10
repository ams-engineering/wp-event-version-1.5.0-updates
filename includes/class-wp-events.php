<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
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
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 * @author     All Marketing Solutions <btltimes39@gmail.com>
 */
class Wp_Events {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Events_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'WP_EVENTS_VERSION' ) ) {
			$this->version = WP_EVENTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-events';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->handle_form_requests();
		$this->handle_locations_functions();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Events_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Events_i18n. Defines internationalization functionality.
	 * - Wp_Events_Admin. Defines all hooks for the admin area.
	 * - Wp_Events_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-events-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-events-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-events-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-events-shortcodes.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-events-public.php';

		/**
		 * The class responsible for handling all the POST requests from Forms.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/includes/class-wp-events-form-request.php';

		/**
		 * The file responsible to run functions on plugin update.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-events-updater.php';


		$this->loader = new Wp_Events_Loader();
		$this->shortcodes = new Wpe_Shortcodes();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Events_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Events_i18n();

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

		$plugin_admin = new Wp_Events_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'load_google_maps' );
        $this->loader->add_action( 'init',  $plugin_admin, 'register_event_post_type' );
        $this->loader->add_action( 'init', $plugin_admin, 'wpevents_category');
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'wpevents_submenu_page');
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_custom_metaboxes_for_details');
        $this->loader->add_action( 'save_post', $plugin_admin, 'wpevents_save_meta_box');
        $this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'restrict_events_by_type');
        $this->loader->add_filter( 'parse_query', $plugin_admin, 'wpe_filter_by_type');
        $this->loader->add_filter( 'post_row_actions', $plugin_admin, 'wpe_duplicate_post_link', 10, 2);
        $this->loader->add_action( 'admin_action_wpe_duplicate_post_as_draft', $plugin_admin, 'wpe_duplicate_post_as_draft', 10, 2);
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'wpe_duplication_admin_notice' );
		
        $this->loader->add_action( 'wp_events_settings_tab', $plugin_admin, 'wpevents_admin_settings_tabs',1 );
        $this->loader->add_action( 'wp_events_settings_content', $plugin_admin, 'wpevents_admin_settings_content' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'wpevents_register_settings');

        // Custom Columns for wp_events
        $this->loader->add_filter( 'manage_wp_events_posts_columns', $plugin_admin, 'wpevents_post_type_columns' );
        $this->loader->add_filter( 'manage_wp_events_posts_custom_column', $plugin_admin, 'wpevents_fill_post_type_columns', 10, 2  );
        $this->loader->add_filter( 'manage_edit-wp_events_sortable_columns', $plugin_admin, 'wpevent_custom_sortable_columns' );
		$this->loader->add_filter( 'views_edit-wp_events', $plugin_admin, 'change_publish_status_text', 10, 1);
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'wpevents_post_status_param');

		//screen options
		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'wpe_set_screen_option', 10, 3 );

		//quickeditmetaboxes
		$this->loader->add_action( 'quick_edit_custom_box', $plugin_admin, 'wpe_quick_edit_fields', 10, 2 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'wpe_quick_edit_save');
		$this->loader->add_action( 'admin_print_footer_scripts-edit.php', $plugin_admin, 'wpe_quick_edit_js');

		//Edit row actions
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'view_registrations_link', 10, 2 );

		//Handle view/edit entry ajax request
		$dbOPerations = new Wp_Events_Db_Actions();
		$this->loader->add_action( 'wp_ajax_wpe_update_entry', $dbOPerations, 'wpe_update_entry' );	
		//handles removal of entries for deleted events.
		// $this->loader->add_action( 'before_delete_post', $dbOPerations, 'wpe_remove_trash_event_entries' );	

		//Handle ajax requests for view/edit sidebar buttons
		$adminRequests = new Wp_Admin_Request();
		$this->loader->add_action( 'wp_ajax_wpe_resend_notification', $adminRequests, 'wpe_resend_notification' );	
		$this->loader->add_action( 'wp_ajax_wpe_trash_restore', $adminRequests, 'wpe_trash_restore' );	
		$this->loader->add_action( 'wp_ajax_wpe_update_entry_status', $adminRequests, 'wpe_update_entry_status' );	
		$this->loader->add_action( 'wp_ajax_wpe_update_location', $adminRequests, 'wpe_update_location' );	
		$this->loader->add_action( 'wp_ajax_wpe_create_location', $adminRequests, 'wpe_create_location' );	
		$this->loader->add_action( 'wp_ajax_wpe_upload_file', $adminRequests, 'wpe_upload_file' );	
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Events_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'template_include', $plugin_public, 'wpevents_archive_template' );
		$this->loader->add_action( 'template_include', $plugin_public, 'wpevents_taxonomy_template');
		$this->loader->add_action( 'single_template', $plugin_public, 'wpevents_single_template' );
		$this->loader->add_action( 'body_class', $plugin_public, 'wpe_body_classes' );
		$this->loader->add_action( 'wp_events_archive_image', $plugin_public, 'wpe_image_on_archive' );
		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'wpe_custom_query_post_setup');
		$this->loader->add_filter( 'theme_page_templates', $plugin_public, 'wpevents_themes_page_template' );
		$this->loader->add_filter( 'page_template', $plugin_public, 'wpevents_archive_to_page_template' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'wpe_meta_description' );
	}

	/**
	 * hook all the function related to form handling
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function handle_form_requests() {
		$request_handler = new Wp_Form_Request();

		$this->loader->add_action( 'wp_ajax_nopriv_wpe_subscribe_form', $request_handler, 'wpe_subscribe_form' );
		$this->loader->add_action( 'wp_ajax_wpe_subscribe_form', $request_handler, 'wpe_subscribe_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpe_registration_form', $request_handler, 'wpe_registration_form' );
		$this->loader->add_action( 'wp_ajax_wpe_registration_form', $request_handler, 'wpe_registration_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpe_verify_captcha', $request_handler, 'wpe_verify_captcha' );
		$this->loader->add_action( 'wp_ajax_wpe_verify_captcha', $request_handler, 'wpe_verify_captcha' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpe_update_default_view', $request_handler, 'wpe_update_default_view' );
		$this->loader->add_action( 'wp_ajax_wpe_update_default_view', $request_handler, 'wpe_update_default_view' );
	}

	/**
	 * hook all the functions related to locations post type
	 *
	 * @since 1.3.0
	 * @access private
	 */
	private function handle_locations_functions() {
		$locations_handler = new Wp_Events_Locations();

		$this->loader->add_action( 'init', $locations_handler, 'register_locations_post_type' );
		$this->loader->add_action( 'add_meta_boxes', $locations_handler, 'register_custom_metaboxes_for_locations' );
		$this->loader->add_action( 'save_post', $locations_handler, 'wpevents_save_locations_meta');
		$this->loader->add_action( 'save_post', $locations_handler, 'wpevents_save_maps_meta');
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
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Events_Loader    Orchestrates the hooks of the plugin.
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
