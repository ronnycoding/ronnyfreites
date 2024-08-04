<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wpe_Content_Engine
 * @subpackage Wpe_Content_Engine/includes
 */

use Wpe_Content_Engine\Helper\API\Settings_Controller;
use Wpe_Content_Engine\Helper\API\Search_Config_Controller;
use Wpe_Content_Engine\Helper\API\Sync_Data\Sync_Data_Controller;
use Wpe_Content_Engine\Helper\Search\Search;
use Wpe_Content_Engine\Helper\Search\Search_Config;
use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\API\Semantic_Search_Controller;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;
use Wpe_Content_Engine\Helper\Constants\Security;
use Wpe_Content_Engine\Settings_Interface;
use function Wpe_Content_Engine\Helper\Notifications\handle_re_sync_notification;

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
 * @package    Wpe_Content_Engine
 * @subpackage Wpe_Content_Engine/includes
 * @author     wpe <user@example.com>
 */
class Wpe_Content_Engine {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpe_Content_Engine_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @var Settings_Interface $settings Settings.
	 */
	protected Settings_Interface $settings;

	/**
	 * @var Client $client Sync API Client.
	 */
	protected Client $client;


	/**
	 * @var Search_Config
	 */
	private Search_Config $search_config;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @param Settings_Interface $settings Settings.
	 * @param Client             $client API Client.
	 */
	public function __construct( Settings_Interface $settings, Client $client ) {
		$this->client   = $client;
		$this->settings = $settings;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->search_config = new Search_Config();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpe_Content_Engine_Loader. Orchestrates the hooks of the plugin.
	 * - Wpe_Content_Engine_I18n. Defines internationalization functionality.
	 * - Wpe_Content_Engine_Admin. Defines all hooks for the admin area.
	 * - Wpe_Content_Engine_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		* Load Core WP plugin functions
		*/
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpe-content-engine-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpe-content-engine-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpe-content-engine-public.php';

		$this->loader = new Wpe_Content_Engine_Loader();

		/**
		 * Helper classes used to batch sync data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings-interface.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core-wp-wrapper/wp-progress-bar.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'wp-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/string-transformation.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/progress-bar-info-trait.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/notifications/re-sync-notifier.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/batch-sync-type-names.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/security.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/json-schema-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/http-verb.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/graphql-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/order-by.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/post-mime-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/post-status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/sync-response-status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/constants/post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/logging/logger-interface.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/logging/debug-logger.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/logging/server-log-info.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/json-schema.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/primitive-type-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/number-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/integer-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/boolean-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/string-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/date-time-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/json-schema/array-property.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/acf.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/acf-factory.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/types/abstract-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/types/number.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/types/text.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/acf-support/types/email.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/client-interface.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/graphql/client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/asset-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/hook-handler/show-admin-notice-handler-decorator.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/sync-interface.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/multisite-network-sync.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/custom-post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/post.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/options/progress.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/options/batch-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/options/resume-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/options/sync-lock-status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/options/sync-lock-state.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/batch-sync-factory.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/sync/batches/sync-lock-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/admin-notice.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/search/search.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/search/search-config.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/api/search-config-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/search/config/configurable.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/search/config/post-config.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/exceptions/client-query-exception.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/exceptions/missing-settings-exception.php';

		/**
		 * API Imports
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/api/settings-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/api/sync-data/response.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/api/sync-data/sync-data-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helper/api/semantic-search-controller.php';

		/**
		 * Settings page class
		 */
		require_once WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . 'settings-callbacks.php';

		/**
		 * WP CLI Commands
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'commands/class-wpe-content-engine-sync-data.php';

		/**
		 * Src imports
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/meta.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/index/post.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/index/semantic-search.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/hooks/filters.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/migrations/rename-post-keys-config.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wp-graphql/pagination.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wp-graphql/filters.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wordpress/page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wordpress/multisite.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wordpress/options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/support/wordpress/transients.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'src/query/taxonomy.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpe_Content_Engine_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Wpe_Content_Engine_I18n();

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
		$settings_page = new WPE_Atlas_Search_Settings_Page( $this->loader );
		$settings_page->init_page();

		$this->loader->add_action( 'enqueue_block_editor_assets', $this, 'post_notices', 9 );
		$this->loader->add_action( 'wp_ajax_block_editor_notices', $this, 'block_editor_notices_callback', 9 );

		// Register admin message handling.
		$this->loader->add_action( 'admin_notices', new Admin_Notice(), 'show_messages', 10, 0 );

		// Post hooks.
		$this->loader->add_action( 'wp_after_insert_post', $this, 'post_upsert_handler', 10, 4 );
		$this->loader->add_action( 'delete_post', $this, 'post_delete_handler', 10, 2 );

		// REST API.
		$this->loader->add_action( 'rest_api_init', new Settings_Controller(), 'register_routes', 10, 0 );

		// Search API.
		$this->loader->add_action( 'rest_api_init', new Search_Config_Controller(), 'register_routes', 10, 0 );

		$this->loader->add_action(
			'rest_api_init',
			new Sync_Data_Controller( $this->client, $this->settings ),
			'register_routes',
			10,
			0
		);

		$this->loader->add_action(
			'rest_api_init',
			new Semantic_Search_Controller( $this->client, $this->settings ),
			'register_routes',
			10,
			0
		);
		$this->loader->add_filter( 'rest_pre_serve_request', $this, 'add_custom_rest_headers', 10, 2 );
	}

	/**
	 * ORN-205 We were asked to disable delete hooks for posts. We can talk about it post Q2
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post      $post WP Post.
	 * @param bool         $update Not used.
	 * @param WP_Post|null $post_before Previous post.
	 */
	public function post_upsert_handler( int $post_id, WP_Post $post, bool $update, ?WP_Post $post_before ) {
		$should_be_indexed_old_status = isset( $post_before ) && \AtlasSearch\Index\should_be_indexed( $post_before->post_status, $post_before->post_password );
		$should_be_indexed_new_status = \AtlasSearch\Index\should_be_indexed( $post->post_status, $post->post_password );

		\AtlasSearch\Index\admin_notice_handler(
			function () use ( $should_be_indexed_old_status, $should_be_indexed_new_status, $post ) {
				if ( $should_be_indexed_new_status ) {
					\AtlasSearch\Index\index_post( $post, $post->ID );
				}

				if ( $should_be_indexed_old_status && ! $should_be_indexed_new_status ) {
					\AtlasSearch\Index\delete_post( $post, $post->ID );
				}
			}
		);

		$this->search_config->get_config();
	}

	public function post_delete_handler( int $post_id, WP_Post $post ) {
		\AtlasSearch\Index\admin_notice_handler(
			function() use ( $post_id, $post ) {
				if ( \AtlasSearch\Index\should_be_indexed( $post->post_status, $post->post_password ) ) {
					\AtlasSearch\Index\delete_post( $post, $post_id );
				}
			}
		);

		$this->search_config->get_config();
	}

	public function block_editor_notices_callback() {
		check_ajax_referer( 'ajax-nonce', 'security' );
		$admin_notices = new Admin_Notice();
		$messages      = $admin_notices->get_messages();

		header( 'Content-Type: application/json' );
		echo json_encode(
			array(
				'hasError' => ! empty( $messages ),
				'message'  => $messages,
			)
		);
		die();
	}

	public function post_notices() {
		// @codingStandardsIgnoreLine
		wp_enqueue_script(
			'display-post-notice',
			plugins_url( '../public/js/display-post-notice.js', __FILE__ ),
			array( 'jquery', 'wp-editor' )
		);

		wp_localize_script( 'display-post-notice', 'ajax_var', array( 'nonce' => wp_create_nonce( 'ajax-nonce' ) ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Wpe_Content_Engine_Public( $this->get_plugin_name(), $this->get_version() );

		$search = new Search(
			$this->client,
			$this->settings,
			new Search_Config()
		);

		$this->loader->add_filter( 'posts_pre_query', $search, 'get_ce_posts', 100, 2 );
		$this->loader->add_filter( 'found_posts', $search, 'found_posts', 100, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
		handle_re_sync_notification( new Admin_Notice() );
	}
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public static function get_plugin_name() {
		return 'wpe-content-engine';
	}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
	public static function get_version() {
		return defined( 'WPE_SMART_SEARCH_VERSION' ) ? WPE_SMART_SEARCH_VERSION : '1.0.0';
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpe_Content_Engine_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	public function add_custom_rest_headers( $served ) {
		foreach ( Security::HEADERS as $header ) {
			header( $header );
		}

		return $served;
	}

}
