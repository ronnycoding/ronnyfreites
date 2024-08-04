<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

abstract class Primitive_Type_Property extends Property {

	abstract protected function load_type(): void;

	protected function build(): void {
		$this->add_json_property( 'type', $this->get_type() );
	}

}
