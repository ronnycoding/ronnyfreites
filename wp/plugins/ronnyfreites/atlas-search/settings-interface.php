<?php

namespace Wpe_Content_Engine;

/**
 * Interface Settings_Interface
 * Could be used in the future to provide implementation get_option in WordPress
 *
 * @package Wpe_Content_Engine\Helper
 */
interface Settings_Interface {

	/**
	 * @param string $option_name The wp get option name.
	 *
	 * @return mixed
	 */
	public function get( string $option_name );
}
