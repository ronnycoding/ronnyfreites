<?php

namespace Wpe_Content_Engine;

class WPSettings implements Settings_Interface {

	public const WPE_CONTENT_ENGINE_OPTION_NAME = 'wpe_content_engine_option_name';

	/**
	 * @param string $option_name The wp get option name.
	 *
	 * @return mixed
	 */
	public function get( string $option_name ) {
		return \AtlasSearch\Support\WordPress\get_option(
			$option_name,
			array(
				'url'          => '',
				'access_token' => null,
			)
		);
	}
}
