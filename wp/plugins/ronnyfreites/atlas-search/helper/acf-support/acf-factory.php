<?php

namespace Wpe_Content_Engine\Helper\Acf_Support;

use Wpe_Content_Engine\Helper\Acf_Support\Types\Abstract_Type;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Email;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Number;
use Wpe_Content_Engine\Helper\Acf_Support\Types\Text;
use Wpe_Content_Engine\Helper\Acf_Support\Acf as Acf_Helper;

class Acf_Factory {
	public const EMAIL            = 'email';
	public const NUMBER           = 'number';
	public const TEXT             = 'text';
	public const TEXTAREA         = 'textarea';
	public const FLEXIBLE_CONTENT = 'flexible_content';
	public const GROUP            = 'group';
	public const POST_OBJECT      = 'post_object';
	public const RELATIONSHIP     = 'relationship';
	public const LINK             = 'link';
	public const TAXONOMY         = 'taxonomy';
	public const REPEATER         = 'repeater';
	public const USER             = 'user';
	public const IMAGE            = 'image';
	public const FILE             = 'file';
	public const GOOGLE_MAP       = 'google_map';
	public const PASSWORD         = 'password';
	public const GALLERY          = 'gallery';
	public const TRUE_FALSE       = 'true_false';
	public const URL              = 'url';

	/**
	 * @param string $type Type.
	 * @param string $name Name.
	 * @return Abstract_Type
	 */
	public static function build( string $type, string $name ): ?Abstract_Type {
		if ( self::EMAIL === $type ) {
			return new Email( $name );
		} elseif ( self::NUMBER === $type ) {
			return new Number( $name );
		} elseif ( self::TEXT === $type ) {
			return new Text( $name );
		} elseif ( self::TEXTAREA === $type ) {
			return new Text( $name );
		}

		return null;
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post Type.
	 * @return Acf|null
	 */
	public static function build_acf_helper_for_type( int $post_id, string $post_type ): ?Acf {
		if ( ! Acf_Helper::acf_exists_for_post_type( $post_type ) ) {
			return null;
		}

		$acf_field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );

		foreach ( $acf_field_groups as $key => $acf_field_group ) {
			if ( empty( $acf_field_group ) || ! $acf_field_group['active'] ) {
				continue;
			}
			$acf_field_groups[ $key ]['fields'] = \acf_get_fields( $acf_field_group );
		}

		return new Acf_Helper( $acf_field_groups, get_fields( $post_id ) ?: array() );
	}
}
