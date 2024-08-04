<?php

namespace Wpe_Content_Engine\Helper;

use Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Interface;

abstract class Multisite_Network_Sync implements Batch_Sync_Interface {

	/**
	 * @var bool
	 */
	protected $is_network_activated = false;

	/**
	 * @var ?string
	 */
	protected $current_site_id = null;


	/**
	 * @param bool    $is_network_activated .
	 * @param ?string $current_site_id .
	 */
	public function __construct( $is_network_activated = false, $current_site_id = null ) {
		$this->is_network_activated = $is_network_activated;
		$this->current_site_id      = $current_site_id;
	}

	/**
	 * Sync data
	 *
	 * @param int $offset Offset.
	 * @param int $number Number.
	 */
	abstract protected function _get_items( $offset, $number );
	abstract protected function _sync( $items );

	/**
	 * @param int $offset Offset.
	 * @param int $number Number.
	 *
	 * @return array $items .
	 */
	public function get_items( $offset, $number ) {
		$items = array();
		if ( $this->is_network_activated ) {
			switch_to_blog( $this->current_site_id );
		}

		$items = $this->_get_items( $offset, $number );

		if ( $this->is_network_activated ) {
			restore_current_blog();
		}

		return $items;
	}


	public function sync( $items ) {
		if ( $this->is_network_activated ) {
			switch_to_blog( $this->current_site_id );
		}

		$this->_sync( $items );

		if ( $this->is_network_activated ) {
			restore_current_blog();
		}
	}
}
