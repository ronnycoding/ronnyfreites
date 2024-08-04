<?php

namespace Wpe_Content_Engine\Helper;

use ErrorException;

/**
 * Interface Client_Interface
 * Could be used in the future to provide implementation for other API ( e.g. REST)
 *
 * @package Wpe_Content_Engine\Helper\Sync\GraphQL
 */
interface Client_Interface {

	/**
	 * @param string      $endpoint Endpoint.
	 * @param string      $query GraphQL Query.
	 * @param array       $variables Any variables to include.
	 * @param string|null $token API Token.
	 * @return array
	 * @throws ErrorException Throws and exception.
	 */
	public function query( string $endpoint, string $query, array $variables = array(), ?string $token = null): array;
}
