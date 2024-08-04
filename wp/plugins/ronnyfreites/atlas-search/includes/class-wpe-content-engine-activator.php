<?php

namespace Wpe_Content_Engine;

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wpe_Content_Engine
 * @subpackage Wpe_Content_Engine/includes
 */

use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wpe_Content_Engine
 * @subpackage Wpe_Content_Engine/includes
 * @author     wpe <user@example.com>
 */
class Wpe_Content_Engine_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		 delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );

		$settings = new WPSettings( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );

		if ( $settings ) {
			$admin_notice = new Admin_Notice();

			$admin_notice->add_message(
				'<b>WP Engine Smart Search</b> requires a one-time data sync if you have made any modifications to your content'
					. ' or models. Please run sync in the <a href="admin.php?page=wpengine-smart-search&view=sync-data">'
					. 'Index Data</a> page.',
				Admin_Notice::NOTICE_TYPE_WARNING
			);
		}

		// Run config migration.
		// TODO: During activation config field option is empty.
		// Find a way to get the real value.
		\AtlasSearch\Migrations\rename_post_keys_config();
	}
}
