<?php
// phpcs:ignoreFile WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

namespace Wpe_Content_Engine\Helper\API\Sync_Data;

/**
 * Settings controller allowing getting and setting of
 * Smart Search settings
 */
class Response {
	public string $status;
	public int $progress;
	public ?string $message;
	public ?string $siteName;
	public ?string $currentSyncedType;
	public ?array $allSyncedTypes;
	public ?string $uuid;
	public ?string $searchType;

	public function __construct(
		string $status,
		int $progress,
		?string $message = null,
		?string $uuid = null,
		?string $site_name = null,
		?string $current_synced_type = null,
		?array $all_synced_types = null,
		?string $search_type = null
	) {
		$this->status   = $status;
		$this->progress = $progress;
		$this->uuid     = empty( $uuid ) ? '' : $uuid;
		$this->setField( 'message', $message );
		$this->setField( 'siteName', $site_name );
		$this->setField( 'currentSyncedType', $current_synced_type );
		$this->setField( 'allSyncedTypes', $all_synced_types);
		$this->setField( 'searchType', $search_type);
	}

	/**
	 * @param null $field
	 *
	 * @return void
	 */
	private function setField( string $field, $value = null ) {
		if ( empty( $value ) ) {
			unset( $this->$field );
		} else {
			$this->$field = $value;
		}
	}
}


