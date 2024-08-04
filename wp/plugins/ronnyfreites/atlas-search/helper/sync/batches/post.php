<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use ErrorException;
use WP_CLI;
use WP_Query;
use WP_Post;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Multisite_Network_Sync;
use Wpe_Content_Engine\Helper\Progress_Bar_Info_Trait;

use const AtlasSearch\Index\MANUAL_INDEX;

class Post extends Multisite_Network_Sync {

	use Progress_Bar_Info_Trait;

	/**
	 * @param int $offset Offset.
	 * @param int $number Number.
	 * @return WP_Post[]
	 */
	protected function _get_items( $offset, $number ): array {
		$q   = array(
			'post_type'           => array( Post_Type::POST ),
			'post_status'         => Post_Status::WP_PUBLISH,
			'posts_per_page'      => $number,
			'paged'               => $offset,
			'ignore_sticky_posts' => true,
		);
		$qry = new WP_Query( $q );

		return $qry->posts;
	}

	/**
	 * @param WP_Post[] $posts Posts.
	 *
	 * @throws ErrorException Exception.
	 */
	protected function _sync( $posts ) {
		if ( count( $posts ) <= 0 ) {
			return;
		}

		foreach ( $posts as $post ) {
			\AtlasSearch\Index\index_post( $post, $post->ID, MANUAL_INDEX );
			$this->tick();

		}
		$this->finish();
	}

	/**
	 * @param mixed $items Items.
	 * @param int   $page Page.
	 */
	public function format_items( $items, $page ) {
		$o = array_column( $items, 'ID' );
		WP_CLI::log( WP_CLI::colorize( "%RSyncing WordPress Posts - Page:{$page} Ids:" . implode( ',', $o ) . '%n ' ) );
	}

	/**
	 * @return int
	 */
	public function get_total_items(): int {
		return wp_count_posts( Post_Type::POST )->publish;
	}
}
