<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use ErrorException;
use WP_CLI;
use WP_Query;
use WP_Post;
use Wpe_Content_Engine\Helper\Constants\Order;
use Wpe_Content_Engine\Helper\Constants\Order_By;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Multisite_Network_Sync;
use Wpe_Content_Engine\Helper\Progress_Bar_Info_Trait;

use const AtlasSearch\Index\MANUAL_INDEX;

class Page extends Multisite_Network_Sync {

	use Progress_Bar_Info_Trait;

	/**
	 * @param int   $offset Offset.
	 * @param mixed $number Offset.
	 * @return WP_Post[]
	 */
	protected function _get_items( $offset, $number ): array {
		$q   = array(
			'post_type'           => array( Post_Type::PAGE ),
			'post_status'         => Post_Status::WP_PUBLISH,
			'posts_per_page'      => $number,
			'paged'               => $offset,
			'ignore_sticky_posts' => true,
			'orderby'             => Order_By::MODIFIED,
			'order'               => Order::ASCENDING,
		);
		$qry = new WP_Query( $q );

		return $qry->posts;
	}

	/**
	 * @param WP_Post[] $pages Pages.
	 *
	 * @throws ErrorException Exception.
	 */
	protected function _sync( $pages ) {
		if ( count( $pages ) <= 0 ) {
			return;
		}

		foreach ( $pages as $page ) {
			\AtlasSearch\Index\index_post( $page, $page->ID, MANUAL_INDEX );
			$this->tick();
		}
		$this->finish();
	}

	/**
	 * @param mixed $items Items.
	 * @param mixed $page Page.
	 */
	public function format_items( $items, $page ) {
		$o = array_column( $items, 'ID' );
		WP_CLI::log( WP_CLI::colorize( "%RSyncing WordPress Pages - Page:{$page} Ids:" . implode( ',', $o ) . '%n ' ) );
	}

	/**
	 * @return int
	 */
	public function get_total_items(): int {
		return wp_count_posts( Post_Type::PAGE )->publish;
	}
}
