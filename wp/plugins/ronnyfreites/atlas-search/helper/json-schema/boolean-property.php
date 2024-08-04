<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Boolean_Property extends Primitive_Type_Property {

	protected function load_type(): void {
		$this->type = Json_Schema_Type::BOOLEAN;
	}

}
