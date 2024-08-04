<?php

namespace Wpe_Content_Engine\Helper\API;

use PHP_CodeSniffer\Tokenizers\JS;
use WP_REST_Request;
use WP_REST_Response;
use Wpe_Content_Engine\Helper\Json_Schema\Array_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Boolean_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Integer_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Json_Schema;
use Wpe_Content_Engine\Helper\Json_Schema\Number_Property;
use Wpe_Content_Engine\Helper\Json_Schema\String_Property;
use Wpe_Content_Engine\Helper\Search\Search_Config;


class Search_Config_Controller extends \WP_REST_Controller {
	private string $resource_name;
	private Search_Config $search_config;

	public function __construct() {
		$this->namespace     = 'wpengine-smart-search/v1';
		$this->resource_name = '/search-config';
		$this->search_config = new Search_Config();
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_config' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
				'schema' => array( $this, 'get_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'set_config' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
				'schema' => array( $this, 'get_schema' ),
			)
		);
	}

	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param WP_REST_Request $request WordPress REST Request.
	 * @return array
	 */
	public function get_config( WP_REST_Request $request ) {
		try {
			return new WP_REST_Response( $this->search_config->get_config() );

		} catch ( \ErrorException $e ) {
			return new \WP_Error(
				'bad-request',
				$e->getMessage(),
				array( 'status' => 400 )
			);
		}
	}

	/**
	 * @param WP_REST_Request $request WordPress Rest Request.
	 * @return WP_REST_Response
	 */
	public function set_config( WP_REST_Request $request ) {
		$json   = $request->get_json_params();
		$schema = $this->get_schema();
		$result = rest_validate_value_from_schema( $request->get_json_params(), $this->get_schema(), 'Body' );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array( 'error' => $result->get_error_message() ),
				'400',
			);
		}
		$sanitized = rest_sanitize_value_from_schema( $json, $schema );

		return new WP_REST_Response( $this->search_config->set_config( $sanitized ) );
	}

	public function get_schema(): array {
		$fuzzy_schema = new Json_Schema( 'fuzzy' );
		$fuzzy_schema
			->add_property( new Boolean_Property( 'enabled' ), false )
			->add_property( new Integer_Property( 'distance' ), false );

		$search_config = $this->search_config->get_config();

		$schema = new Json_Schema( 'search_config' );
		$schema
			->add_property( $this->get_models_schema( $search_config['models'] ), false )
			->add_property( $fuzzy_schema, false )
			->add_property( new Array_Property( 'disabledModelNames', 'string', false ), false )
			->add_property( new Integer_Property( 'version' ), false )
			->add_property( new String_Property( 'searchType', true ), false );

		return $schema->generate();
	}

	/**
	 * @param array $search_config Search config with the available fields.
	 *
	 * @return Json_Schema
	 */
	private function get_models_schema( array $search_config ): Json_Schema {
		$models_schema = new Json_Schema( 'models' );

		foreach ( $search_config as $content_type => $config ) {
			$json_schema = new Json_Schema( $content_type );
			foreach ( $config as $field_name => $value ) {
				$property_obj = new Json_Schema( $field_name );
				$property_obj
					->add_property( new Boolean_Property( 'searchable' ), true )
					->add_property( new Number_Property( 'weight' ), true )
					->add_property( new Boolean_Property( 'has_sub_fields' ), true );
				$json_schema->add_property( $property_obj, false );
			}
			$models_schema->add_property( $json_schema, false );
		}

		return $models_schema;
	}


}
