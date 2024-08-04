<?php

namespace Wpe_Content_Engine\Helper\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryException;
use Wpe_Content_Engine\WPSettings;


/**
 * Semantic Search controller allowing getting and setting of semantic search settings
 */
class Semantic_Search_Controller extends WP_REST_Controller {
	private string $resource_name;

	public function __construct() {
		$this->namespace     = 'wpengine-smart-search/v1';
		$this->resource_name = '/semantic-search';

	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array(
						$this,
						'get_semantic_search_config',
					),
					'permission_callback' => array(
						$this,
						'permission_callback',
					),
				),
				'schema' => array(
					$this,
					'get_schema',
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array(
						$this,
						'set_semantic_search_config',
					),
					'permission_callback' => array(
						$this,
						'network_admin_permission_callback',
					),
				),
				'schema' => array(
					$this,
					'get_schema',
				),
			)
		);

	}//end register_routes()


	/**
	 * Returns the Semantic Search settings.
	 *
	 * @param  WP_REST_Request $request WP Rest request.
	 * @return WP_REST_Response
	 */
	public function get_semantic_search_config( WP_REST_Request $request ) {
		try {
			$config = \AtlasSearch\Index\get_semantic_search_config();

			return new WP_REST_Response(
				$config
			);

		} catch ( ClientQueryException $e ) {
			return new WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				'400',
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				'500',
			);
		}
	}

	/**
	 * Set the settings.
	 *
	 * @param WP_REST_Request $request WP Rest request.
	 * @return WP_REST_Response
	 */
	public function set_semantic_search_config( WP_REST_Request $request ): WP_REST_Response {
		$body   = $request->get_json_params();
		$result = rest_validate_value_from_schema( $body, $this->get_schema(), 'semanticSearch' );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array( 'error' => $result->get_error_message() ),
				'400',
			);
		}

		try {
			$config = \AtlasSearch\Index\set_semantic_search_config( $body['fields'], $body['searchBias'], $body['enabled'] );
			return new WP_REST_Response(
				$config
			);

		} catch ( ClientQueryException $e ) {
			return new WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				'400',
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				'500',
			);
		}

	}


	/**
	 * Check permissions.
	 *
	 * @param WP_REST_Request $request The WP Rest request.
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check if the current user has the correct permissions to POST.
	 *
	 * @param WP_REST_Request $request The WP Rest request.
	 * @return bool
	 */
	public function network_admin_permission_callback( WP_REST_Request $request ): bool {
		if ( is_multisite() ) {
			return current_user_can( 'manage_network' );
		} else {
			return current_user_can( 'manage_options' );
		}
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = array(
			'fields'     => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'searchBias' => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 10,
			),
			'enabled'    => array(
				'type' => 'boolean',
			),
		);

		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'semanticSearch',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
			'required'             => array( 'fields', 'searchBias' ),
		);
	}

}
