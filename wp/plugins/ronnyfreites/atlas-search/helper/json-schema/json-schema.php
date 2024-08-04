<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Invalid_Json_Schema_Exception extends \Exception {}

class Json_Schema extends Property {
	const ALLOWED_KEY_NAME_REGEX = '/^[_a-zA-Z][_a-zA-Z0-9.]*$/';

	/**
	 * @var array
	 */
	private $required = array();

	/**
	 * @var Property[]
	 */
	private $object_properties = array();

	protected function load_type(): void {
		$this->type = Json_Schema_Type::OBJECT;
	}

	/**
	 * @return array
	 */
	public function get_required(): array {
		return $this->required;
	}

	/**
	 * @return array
	 */
	public function get_object_properties(): array {
		return $this->object_properties;
	}

	/**
	 * @param Property $property Property.
	 * @param bool     $is_required Is required.
	 * @return Json_Schema
	 */
	public function add_property( Property $property, bool $is_required ): Json_Schema {
		$this->object_properties[] = $property;

		if ( $is_required && ! in_array( $property->get_name(), $this->required, true ) ) {
			$this->required[] = $property->get_name();
		}

		return $this;
	}

	protected function build(): void {
		$this->json_properties = array(
			'$id'                  => 'https://aql.wpengine.com/wordpress/custom_type/' . $this->get_name(),
			'title'                => $this->get_name(),
			'type'                 => $this->get_type(),
			'required'             => $this->required,
			'properties'           => array(),
			'additionalProperties' => false,
		);

		if ( ! empty( $this->object_properties ) ) {
			foreach ( $this->object_properties as $property ) {
				$this->json_properties['properties'][ $property->get_name() ] = $property->generate();
			}
		} else {
			$this->json_properties['properties'] = new \stdClass();
		}
	}
}
