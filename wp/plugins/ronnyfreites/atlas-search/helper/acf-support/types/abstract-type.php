<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Property;

abstract class Abstract_Type {

	/**
	 * @var string
	 */
	protected $name;

	public function __construct( string $name ) {
		$this->name = $name;
	}

	/**
	 * @return Property
	 */
	abstract public function to_json_schema_property(): Property;

}
