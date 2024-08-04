<?php

namespace Wpe_Content_Engine\Core_Wp_Wrapper;

use function WP_CLI\Utils\make_progress_bar;

class Wp_Progress_Bar {

	/**
	 * @var \cli\progress\Bar|\WP_CLI\NoOp
	 */
	protected $progress_bar;

	/**
	 * @param int    $count  Total count of items to be shown.
	 * @param string $message Message to be shown.
	 */
	public function __construct( int $count, string $message = '' ) {
		$this->progress_bar = make_progress_bar( $message, $count );
	}

	public function tick(): void {
		$this->progress_bar->tick();
	}

	public function finish(): void {
		$this->progress_bar->finish();
	}
}
