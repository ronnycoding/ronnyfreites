<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches\Options;

class Resume_Options {


	/**
	 * @var string;
	 */
	private $entity;

	/**
	 * @var int
	 */
	private $batch_size;

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var string
	 */
	private $site_id;

	/**
	 * @var ?Progress $progress
	 */
	private $progress = null;

	/**
	 * Resume_Options constructor.
	 *
	 * @param string    $entity Entity.
	 * @param int       $batch_size Batch Size.
	 * @param int       $page Page.
	 * @param string    $site_id string.
	 * @param ?Progress $progress Progress object.
	 */
	public function __construct( string $entity = '', int $batch_size = Batch_Options::DEFAULT_BATCH_SIZE, int $page = 1, string $site_id = '', ?Progress $progress = null ) {
		$this->entity     = $entity;
		$this->batch_size = $batch_size;
		$this->page       = $page;
		$this->progress   = $progress;
		$this->site_id    = $site_id;
	}

	/**
	 * @return string
	 */
	public function get_entity(): string {
		return $this->entity;
	}

	/**
	 * @return int
	 */
	public function get_batch_size(): int {
		return $this->batch_size;
	}

	/**
	 * @return int
	 */
	public function get_page(): int {
		return $this->page;
	}

	/**
	 * @return string
	 */
	public function get_site_id(): string {
		return $this->site_id;
	}

	/**
	 * @return ?Progress
	 */
	public function get_progress(): ?Progress {

		return $this->progress;
	}
}
