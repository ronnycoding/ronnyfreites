<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://developers.wpengine.com/
 * @since             1.0.0
 * @package           Wpe_Content_Engine
 *
 * @wordpress-plugin
 * Plugin Name:       WP Engine Smart Search
 * Plugin URI:        https://developers.wpengine.com/
 * Description:       Searching WordPress data with WP Engine Smart Search.
 * Version:           0.2.53
 * Author:            WP Engine
 * Author URI:        https://wpengine.com/
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpengine-smart-search
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
use Wpe_Content_Engine\WPSettings;
use Wpe_Content_Engine\Wpe_Content_Engine_Activator;
use Wpe_Content_Engine\Helper\Sync\GraphQL;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPE_SMART_SEARCH_VERSION', '0.2.53' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpe-content-engine-activator.php
 */
function activate_wpengine_smart_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine-activator.php';

	Wpe_Content_Engine_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpe-content-engine-deactivator.php
 */
function deactivate_wpengine_smart_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine-deactivator.php';
	Wpe_Content_Engine_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpengine_smart_search' );
register_deactivation_hook( __FILE__, 'deactivate_wpengine_smart_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine.php';

// Path definitions for the atlas search settings page.
define( 'WPE_SMART_SEARCH_SETTINGS_PAGE_PATH', plugin_dir_path( __FILE__ ) . '/includes/smart-search-settings/' );
define( 'WPE_SMART_SEARCH_ASSET_MANIFEST', WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . '/build/asset-manifest.json' );

require_once WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . 'settings-callbacks.php';


require_once plugin_dir_path( __FILE__ ) . 'src/support/wordpress/utils.php';
define( 'SMART_SEARCH_FILE', plugin_basename( __FILE__ ) );

if ( \AtlasSearch\Support\WordPress\is_network_activated( SMART_SEARCH_FILE ) ) {
	$GLOBALS['is_smart_search_network_activated'] = true;
}

/**
 * @return bool .
 */
function is_wpe_smart_search_network_activated(): bool {
	return ! empty( $GLOBALS['is_smart_search_network_activated'] ) && $GLOBALS['is_smart_search_network_activated'];
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpe_content_engine() {
	require_once plugin_dir_path( __FILE__ ) . 'settings-interface.php';
	require_once plugin_dir_path( __FILE__ ) . 'wp-settings.php';
	require_once plugin_dir_path( __FILE__ ) . '/helper/client-interface.php';
	require_once plugin_dir_path( __FILE__ ) . '/helper/sync/graphql/client.php';

	$client = new GraphQL\Client( Wpe_Content_Engine::get_plugin_name(), Wpe_Content_Engine::get_version() );
	$plugin = new Wpe_Content_Engine( new WPSettings(), $client );
	$plugin->run();
}

run_wpe_content_engine();
