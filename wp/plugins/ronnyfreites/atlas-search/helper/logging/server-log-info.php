<?php

namespace Wpe_Content_Engine\Helper\Logging;

class Server_Log_Info {
	/**
	 * WordPress' data that we want Smart Search server to log
	 *
	 * @return array
	 */
	public function get_data(): array {
		return array(
			'domainName' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) ),
		);
	}
}
