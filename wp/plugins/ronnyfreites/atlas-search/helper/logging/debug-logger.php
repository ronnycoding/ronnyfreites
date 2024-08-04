<?php

namespace Wpe_Content_Engine\Helper\Logging;

class Debug_Logger implements Logger {
	/**
	 * @param mixed $log Item to log.
	 */
	public function log( $log ) {
		if ( ! WP_DEBUG ) {
				return;
		}

		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}
