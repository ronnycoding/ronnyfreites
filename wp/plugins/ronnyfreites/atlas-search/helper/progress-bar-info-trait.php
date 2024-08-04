<?php

namespace Wpe_Content_Engine\Helper;

use Wpe_Content_Engine\Core_Wp_Wrapper\Wp_Progress_Bar;

trait Progress_Bar_Info_Trait {

	/**
	 * @var Wp_Progress_Bar $progress_bar Progress Bar.
	 */
	protected $progress_bar = null;

	/**
	 * @param Wp_Progress_Bar|null $progress_bar Progress Bar.
	 */
	public function set_progress_bar( Wp_Progress_Bar $progress_bar = null ) {
		$this->progress_bar = $progress_bar;
	}

	public function tick() {
		if ( isset( $this->progress_bar ) ) {
			$this->progress_bar->tick();
		}
	}

	public function finish() {
		if ( isset( $this->progress_bar ) ) {
			$this->progress_bar->finish();
		}
	}

}
