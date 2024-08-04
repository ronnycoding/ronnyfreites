<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use ErrorException;

interface Batch_Sync_Interface {

	public function get_items( $offset, $number);

	/**
	 * @param mixed $items Items.
	 *
	 * @throws ErrorException Exception.
	 */
	public function sync( $items);

	public function format_items( $items, $page);

	public function get_total_items();

}
