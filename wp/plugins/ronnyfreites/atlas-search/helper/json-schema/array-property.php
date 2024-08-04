<?php

namespace Wpe_Content_Engine\Helper\Json_Schema;

use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;

class Array_Property extends Property {
	private string $items_type;

	public function __construct( string $name, string $items_type, bool $nullable = false ) {
		parent::__construct( $name, $nullable );
		$this->items_type = $items_type;
	}

	protected function load_type(): void {
		$this->type = Json_Schema_Type::ARRAY;
	}

	protected function build(): void {
		$this->add_json_property( 'type', $this->get_type() );
		$this->add_json_property(
			'items',
			array(
				'type' => $this->items_type,
			)
		);
	}
}
