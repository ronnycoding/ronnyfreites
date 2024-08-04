<?php

namespace Wpe_Content_Engine\Helper\Acf_Support\Types;

use Wpe_Content_Engine\Helper\Json_Schema\Integer_Property;
use Wpe_Content_Engine\Helper\Json_Schema\Property;

class Integer extends Abstract_Type {

	/**
	 * @return Property
	 */
	public function to_json_schema_property(): Property {
		return ( new Integer_Property( $this->name ) );
	}

}
