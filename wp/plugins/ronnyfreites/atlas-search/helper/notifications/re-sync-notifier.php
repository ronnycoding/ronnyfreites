<?php

namespace Wpe_Content_Engine\Helper\Notifications;

use Wpe_Content_Engine\Helper\Admin_Notice;

const  WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED = 'wpe_content_engine_re_sync_has_occurred';

if ( ! function_exists( 'handle_re_sync_notification' ) ) {
	/**
	 * Show notification when option WPE_CONTENT_ENGINE_ASK_TO_RUN_SYNC false.
	 *
	 * @param Admin_Notice $notification Notification.
	 * @return void
	 */
	function handle_re_sync_notification( Admin_Notice $notification ): void {
		$current_page = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

		if (
			str_contains( $current_page, 'favicon.ico' )
			|| str_contains( $current_page, 'admin-ajax.php' )
			|| str_contains( $current_page, 'wp-json' )
		) {
			return;
		}

		if ( ! get_option( WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED ) && ! str_ends_with( $current_page, 'page=wpengine-smart-search' ) ) {
			$notification->add_message(
				'<b>WP Engine Smart Search</b> requires a one-time data sync in the '
					. '<a href="admin.php?page=wpengine-smart-search&view=sync-data">Index Data</a> page',
				Admin_Notice::NOTICE_TYPE_WARNING
			);
		}
	}
}
