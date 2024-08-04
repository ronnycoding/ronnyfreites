<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use Wpe_Content_Engine\Helper\Constants\Batch_Sync_Type_Names;
use Wpe_Content_Engine\Helper\Sync\Batches\Custom_Post_Type as Custom_Post_Type_Batch;
use Wpe_Content_Engine\Helper\Sync\Batches\Post as Post_Batch;
use Wpe_Content_Engine\Helper\Sync\Batches\Page as Page_Batch;

class Batch_Sync_Factory {
	/**
	 * @var Batch_Sync_Interface[] DATA_TO_SYNC
	 */
	public const DATA_TO_SYNC = array(
		Batch_Sync_Type_Names::CUSTOM_POST_TYPES => Custom_Post_Type_Batch::class,
		Batch_Sync_Type_Names::POSTS             => Post_Batch::class,
		Batch_Sync_Type_Names::PAGES             => Page_Batch::class,
	);



	/**
	 * @param string  $batch_sync_class_name .
	 * @param bool    $is_network_activated .
	 * @param ?string $current_site_id .
	 *
	 * @return Batch_Sync_Interface
	 */
	public static function build( string $batch_sync_class_name, $is_network_activated = false, $current_site_id = null ): Batch_Sync_Interface {
		return new $batch_sync_class_name( $is_network_activated, $current_site_id );
	}
}
