<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches\Options;

use \DateTime;

class Sync_Lock_Status {
	public const OPTIONS_WPE_ATLAS_SEARCH_SYNC_STATUS = 'wpe_atlas_search_sync_status';

	private DateTime $last_updated;
	private string $state = Sync_Lock_State::STOPPED;
	private ?string $uuid;

	public function __construct() {
		$this->last_updated = $this->get_default_date();
	}

	public function get_default_date(): DateTime {
		return new DateTime( '1900-01-01' );
	}

	public function get_state(): string {
		return $this->state;
	}

	public function set_state( string $state ) {
		$this->state = $state;
	}

	public function get_last_updated(): DateTime {
		return $this->last_updated;
	}

	public function set_last_updated( DateTime $last_updated ) {
		$this->last_updated = $last_updated;
	}

	public function get_uuid(): ?string {
		return $this->uuid;
	}

	public function set_uuid( ?string $uuid ): void {
		$this->uuid = $uuid;
	}
}
