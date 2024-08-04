<?php
/**
 * The graphql-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wpe_Content_Engine
 * @subpackage Wpe_Content_Engine/admin
 */

namespace Wpe_Content_Engine\Helper\Sync\GraphQL;

use Wpe_Content_Engine\Helper\Client_Interface;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryException;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryGraphqlErrorsException;
use Wpe_Content_Engine\Helper\Exceptions\MissingSettingsException;

class Client implements Client_Interface {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * @param string      $endpoint Endpoint URI.
	 * @param string      $query Query string.
	 * @param array       $variables Query string variables.
	 * @param string|null $token Access Token.
	 * @param array|null  $log_info Log info that we want the server to log.
	 *
	 * @return array
	 * @throws ClientQueryException Throws error Exception.
	 * @throws MissingSettingsException Throws error Exception.
	 * @throws ClientQueryGraphqlErrorsException Throws error Exception.
	 */
	public function query(
		string $endpoint,
		string $query,
		array $variables = array(),
		?string $token = null,
		$log_info = array()
	): array {
		if ( empty( $endpoint ) ) {
			throw new MissingSettingsException( 'Missing WP Engine Smart Search URL.' );
		}

		if ( empty( $token ) ) {
			throw new MissingSettingsException( 'Missing WP Engine Smart Search access token.' );
		}

		$headers = array(
			'Content-Type'           => 'application/json',
			'X-CONTENT-ENGINE-AGENT' => "{$this->plugin_name}/{$this->version}",
			'Authorization'          => "Bearer $token",
		);

		if ( ! empty( $log_info ) ) {
			$headers['X-CONTENT-ENGINE-LOG-INFO'] = wp_json_encode( $log_info );
		}

		$body = array(
			'query'     => $query,
			'variables' => $variables,
		);

		if ( empty( $body['variables'] ) ) {
			unset( $body['variables'] );
		}

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers'       => $headers,
				'timeout'       => 20,
				'ignore_errors' => true,
				'body'          => wp_json_encode(
					$body
				),
			),
		);

		$response_http_code = (int) wp_remote_retrieve_response_code( $response );
		$data               = wp_remote_retrieve_body( $response );
		$logger             = new Debug_Logger();

		// WP ERRORS.
		if ( is_wp_error( $response ) ) {
			$logger->log( 'WP Engine Smart Search client query error: ' . wp_json_encode( $response->errors ) );

			throw new ClientQueryException(
				sprintf( 'WordPress request error: %s. Please contact our support team.', $response->get_error_message() )
			);
		}

		// BAD REQUEST.
		if ( 400 === $response_http_code ) {
			$logger->log( "BAD_REQUEST: $response_http_code. Response: $data" );
			throw new ClientQueryException( 'Please upgrade WP Engine Smart Search plugin to the latest version.' );
		}

		// NOT FOUND.
		if ( 404 === $response_http_code ) {
			$logger->log( "NOT FOUND: $response_http_code. URL:  $endpoint" );
			throw new ClientQueryException( 'Please verify your WP Engine Smart Search URL.' );
		}

		// SERVER ERROR.
		if ( $response_http_code >= 500 ) {
			$logger->log( "Server Error. Response code: $response_http_code. Error: $data" );
			throw new ClientQueryException( 'An unexpected server error occurred. Please contact support.' );
		}

		$response_data = json_decode( $data, true );

		// UNAUTHORIZED.
		if (
			401 === $response_http_code ||
			'UNAUTHENTICATED' === ( $response_data['errors'][0]['extensions']['code'] ?? '' )
		) {
			$data = trim( $data );
			if ( 'Authentication failed' === $data || 'Authentication header not present or malformed' === $data ) {
				$logger->log( "UNAUTHORIZED: Please check your access token. Response code: $response_http_code" );
				throw new ClientQueryException( 'Please verify your WP Engine Smart Search access token.' );
			}

			$logger->log( "UNAUTHORIZED: CloudRun seems not to be ready. Response code: $response_http_code" );
			throw new ClientQueryException( 'Please try again in a minute. Some of our systems are still initializing.' );
		}

		// BAD RESPONSE FORMAT.
		if ( empty( $data ) || ( empty( $response_data ) && ! is_array( $response_data ) ) ) {
			$logger->log(
				sprintf( 'BAD_JSON: Response was not in JSON format, DATA: %s RESPONSE CODE: %s', $data, $response_http_code )
			);

			throw new ClientQueryException(
				sprintf( 'Empty server response. Please contact our support team.' )
			);
		}

		// GRAPHQL ERRORS.
		if ( ! empty( $response_data['errors'] ) ) {
			$graphql_errors = $response_data['errors'];

			$logger->log(
				sprintf(
					'GRAPHQL_ERROR: Graphql Error occurred when sending request, ERRORS: %s',
					wp_json_encode( $graphql_errors )
				)
			);

			throw new ClientQueryGraphqlErrorsException( $graphql_errors[0]['message'] );
		}

		return $response_data;
	}
}
