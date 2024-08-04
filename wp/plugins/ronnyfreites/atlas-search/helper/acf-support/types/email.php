<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Property;
use Wpe_Content_Engine\Helper\Json_Schema\String_Property;

class Email extends Abstract_Type {

	/**
	 * @return Property
	 */
	public function to_json_schema_property(): Property {
		return ( new String_Property( $this->name ) );
	}
}
