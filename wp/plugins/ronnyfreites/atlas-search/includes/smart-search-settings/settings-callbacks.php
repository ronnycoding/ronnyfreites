<?php

use function AtlasSearch\Support\WordPress\is_network_activated;

/**
 * This file enqueues scripts and styles.
 *
 * @package    Wpe_Content_Engine
 */
class WPE_Atlas_Search_Settings_Page {


	private $loader;

	public const PAGE                     = 'wpengine-smart-search';
	public const SETTINGS_PAGE            = 'wpengine-smart-search&amp;view=settings';
	public const CONFIG_PAGE              = 'wpengine-smart-search&amp;view=search-config';
	public const AI_POWERED_PAGE          = 'wpengine-smart-search&amp;view=ai-powered-search';
	public const HYBRID_SETTINGS_PAGE = 'wpengine-smart-search&amp;view=hybrid-settings';
	public const INDEX_DATA_PAGE          = 'wpengine-smart-search&amp;view=sync-data';

	public function __construct( \Wpe_Content_Engine_Loader $loader ) {
		$this->loader = $loader;
	}

	public function init_page() {
		$this->loader->add_action( 'admin_init', $this, 'enqueue_settings_page' );
		$this->loader->add_action( 'admin_menu', $this, 'add_settings_menu' );
		$this->loader->add_filter( 'parent_file', $this, 'maybe_override_submenu_file' );

		// Only add the network admin menu if the plugin is network activated.
		if ( is_wpe_smart_search_network_activated() ) {
			$this->loader->add_action( 'network_admin_menu', $this, 'add_network_admin_settings_menu' );
		}
	}

	public function enqueue_settings_page() {
		add_action(
			'admin_enqueue_scripts',
			function () {

				wp_localize_script(
					'wp-api',
					'wpApiSettings',
					array(
						'root'   => esc_url_raw( rest_url() ),
						'nonce'  => wp_create_nonce( 'wp_rest' ),
						'siteId' => is_network_admin() ? AtlasSearch\Support\WordPress\NETWORK_ADMIN : get_current_blog_id(),
					)
				);
				wp_localize_script(
					'wp-api',
					'smartSearchSettings',
					array(
						'siteId'             => is_network_admin() ? AtlasSearch\Support\WordPress\NETWORK_ADMIN : get_current_blog_id(),
						'isNetworkActivated' => is_wpe_smart_search_network_activated() ? 'true' : 'false',
						'haveDoneIndex'      => get_option( 'wpe_content_engine_re_sync_has_occurred', false ) ? 'true' : 'false',
					)
				);
				wp_enqueue_script( 'wp-api' );
				// @todo Material UI requires Roboto Fonts. Uncomment if necessary
				// @todo wp_enqueue_style( 'roboto-font', 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap', false );

				if ( getenv( 'WPE_SMART_SEARCH_ENV' ) === 'dev' ) {
					wp_enqueue_script(
						'wpengine-smart-search-js',
						'http://localhost:3002/wp-content/plugins/wpengine-smart-search/includes/smart-search-settings/build/static/js/bundle.js',
						'',
						wp_rand( 10, 1000 ),
						true
					);
				} else {
					$asset_manifest = json_decode( file_get_contents( WPE_SMART_SEARCH_ASSET_MANIFEST ), true )['files'];

					if ( isset( $asset_manifest['main.css'] ) ) {
						wp_enqueue_style(
							'wpengine-smart-search-css',
							get_site_url() . $asset_manifest['main.css'],
							array(),
							WPE_SMART_SEARCH_VERSION
						);
					}

					if ( isset( $asset_manifest['runtime-main.js'] ) ) {
						wp_enqueue_script(
							'wpengine-smart-search-runtime',
							get_site_url() . $asset_manifest['runtime-main.js'],
							array(),
							WPE_SMART_SEARCH_VERSION,
							true
						);

						wp_enqueue_script(
							'wpengine-smart-search-js',
							get_site_url() . $asset_manifest['main.js'],
							array( 'wpengine-smart-search-runtime' ),
							WPE_SMART_SEARCH_VERSION,
							true
						);
					} else {
						wp_enqueue_script(
							'wpengine-smart-search-js',
							get_site_url() . $asset_manifest['main.js'],
							array(),
							WPE_SMART_SEARCH_VERSION,
							true
						);
					}

					foreach ( $asset_manifest as $key => $value ) {
						if ( preg_match( '@static/js/(.*)\.chunk\.js@', $key, $matches ) ) {
							if ( $matches && is_array( $matches ) && count( $matches ) === 2 ) {
								$name = 'wpengine-smart-search-' . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
								wp_enqueue_script( $name, get_site_url() . $value, array( 'wpengine-smart-search-js' ), WPE_SMART_SEARCH_VERSION, true );
							}
						}

						if ( preg_match( '@static/css/(.*)\.chunk\.css@', $key, $matches ) ) {
							if ( $matches && is_array( $matches ) && count( $matches ) === 2 ) {
								$name = 'wpengine-smart-search-' . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
								wp_enqueue_style( $name, get_site_url() . $value, array( 'wpengine-smart-search-css' ), WPE_SMART_SEARCH_VERSION );
							}
						}
					}
				}
			}
		);
	}

	private function menu_slug( string $origin, bool $main ) {
		if ( $main ) {
			return self::PAGE;
		}

		return $origin;
	}

	public function add_settings_menu() {
		$icon = include __DIR__ . '/views/search-menu-icon.php';

		add_menu_page(
			esc_html__( 'WP Engine Smart Search', 'wpengine-smart-search' ),
			esc_html__( 'WP Engine Smart Search', 'wpengine-smart-search' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_settings_page' ),
			$icon
		);

		if ( ! is_wpe_smart_search_network_activated() ) {
			add_submenu_page(
				self::PAGE,
				'Index Data',
				'Index Data',
				'manage_options',
				$this->menu_slug( self::INDEX_DATA_PAGE, ! is_wpe_smart_search_network_activated() ),
				'__return_null',
				1
			);
		}

		add_submenu_page(
			self::PAGE,
			'Configuration',
			'Configuration',
			'manage_options',
			$this->menu_slug( self::CONFIG_PAGE, is_wpe_smart_search_network_activated() ),
			'__return_null',
			2
		);

		if ( ! is_wpe_smart_search_network_activated() ) {
			add_submenu_page(
				self::PAGE,
				'Settings',
				'Settings',
				'manage_options',
				self::SETTINGS_PAGE,
				'__return_null',
				3
			);
		}
	}

	public function add_network_admin_settings_menu() {
		 $icon = include __DIR__ . '/views/search-menu-icon.php';

		add_menu_page(
			esc_html__( 'WP Engine Smart Search', 'wpengine-smart-search' ),
			esc_html__( 'WP Engine Smart Search', 'wpengine-smart-search' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_settings_page' ),
			$icon
		);

		add_submenu_page(
			self::PAGE,
			'Index Data',
			'Index Data',
			'manage_options',
			$this->menu_slug( self::INDEX_DATA_PAGE, is_network_admin() ),
			'__return_null',
			1
		);

		add_submenu_page(
			self::PAGE,
			'Settings',
			'Settings',
			'manage_options',
			self::SETTINGS_PAGE,
			'__return_null',
			2
		);

		add_submenu_page(
			self::PAGE,
			'Hybrid Settings',
			'Hybrid Settings',
			'manage_options',
			self::HYBRID_SETTINGS_PAGE,
			'__return_null',
			3
		);
	}

	public function render_settings_page() {            ?>
		<div class="wpengine-smart-search-page">
			<div id='wpengine-smart-search-root'></div>
		</div>
		<?php
	}

	/**
	 * Overrides the “submenu file” that determines which admin submenu item gains
	 * the `current` CSS class. Without this, WordPress incorrectly gives the
	 * “Model” subpage the `current` class when the “Taxonomies” subpage is active.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/9937fea517ac165ad01f67c54216469e48c48ca7/wp-admin/menu-header.php#L223-L227
	 * @link https://wordpress.stackexchange.com/a/131873
	 * @link https://developer.wordpress.org/reference/hooks/parent_file/
	 * @param string $parent_file The original parent file.
	 * @return string The $parent_file unaltered. Only the $submenu_file global is altered.
	 */
	public function maybe_override_submenu_file( $parent_file ) {
		global $submenu_file;

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( self::PAGE === $page && 'search-config' === $view ) {
			$submenu_file = self::CONFIG_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'settings' === $view ) {
			$submenu_file = self::SETTINGS_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'ai-powered-search' === $view ) {
			$submenu_file = self::AI_POWERED_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'hybrid-settings' === $view ) {
			$submenu_file = self::HYBRID_SETTINGS_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		return $parent_file;
	}
}
