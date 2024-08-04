<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Date_Time_Property extends Primitive_Type_Property {

	protected function load_type(): void {
		$this->type   = Json_Schema_Type::STRING;
		$this->format = 'date-time';
	}

	protected function build(): void {
		parent::build();
		$this->add_json_property( 'format', $this->format );
	}

}
