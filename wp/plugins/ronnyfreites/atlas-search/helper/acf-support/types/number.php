<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Number_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Property;

class Number extends Abstract_Type {

	/**
	 * @return Property
	 */
	public function to_json_schema_property(): Property {
		return ( new Number_Property( $this->name ) );
	}

}
