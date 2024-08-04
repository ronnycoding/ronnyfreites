<?php

namespace Wpe_Content_Engine\Helper;

class String_Transformation {

	/**
	 * @param string $str String to be formatted.
	 * @param array  $no_strip Dont strip those characters.
	 *
	 * @return string
	 */
	public static function camel_case( string $str, array $no_strip = array() ): string {
		$str = preg_replace( '/[^a-z0-9' . implode( '', $no_strip ) . ']+/i', ' ', $str );
		$str = trim( $str );
		$str = ucwords( $str );
		$str = str_replace( ' ', '', $str );
		$str = lcfirst( $str );

		return $str;
	}
}
