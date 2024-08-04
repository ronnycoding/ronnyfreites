<?php

namespace Wpe_Content_Engine\Helper\Acf_Support;

use WP_Post;
use WP_Term;
use WP_User;
use Wpe_Content_Engine\Helper\Acf_Support\Acf_Factory;
use Wpe_Content_Engine\Helper\Constants\Json_Schema_Type;
use Wpe_Content_Engine\Helper\String_Transformation;

class Acf {
	const SMART_SEARCH_FILTER_ACF_EXCLUDED_FIELDS_NAMES = 'wpe_smartsearch/acf/excluded_field_names';

	/**
	 * @var array
	 */
	public const ACF_UNSUPPORTED_TYPES = array(
		Acf_Factory::IMAGE,
		Acf_Factory::FILE,
		Acf_Factory::GOOGLE_MAP,
		Acf_Factory::PASSWORD,
		Acf_Factory::GALLERY,
	);

	/**
	 * @var array
	 */
	public const ACF_NESTED_TYPES = array(
		Acf_Factory::FLEXIBLE_CONTENT,
		Acf_Factory::GROUP,
		Acf_Factory::POST_OBJECT,
		Acf_Factory::RELATIONSHIP,
		Acf_Factory::LINK,
		Acf_Factory::TAXONOMY,
		Acf_Factory::REPEATER,
		Acf_Factory::USER,
	);

	public const ACF_CLEANED_RECURSIVELY = array(
		Acf_Factory::FLEXIBLE_CONTENT,
		Acf_Factory::GROUP,
		Acf_Factory::REPEATER,
	);


	/**
	 * @var array
	 */
	private $field_structure = array();

	/**
	 * @var array
	 */
	private $data = array();


	public function __construct( array $field_structure, array $data ) {
		$this->field_structure = $field_structure;
		$this->data            = $this->format_data_according_structure( $data );
	}

	/**
	 * @return array
	 */
	public function get_field_structure(): array {
		return $this->field_structure;
	}

	/**
	 * @return array
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * @return bool
	 */
	public static function is_acf_loaded(): bool {
		return class_exists( 'ACF' );
	}

	/**
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function acf_exists_for_post_type( string $post_type ): bool {
		return self::is_acf_loaded() && ! empty( acf_get_field_groups( array( 'post_type' => $post_type ) ) );
	}

	/**
	 * @param mixed $data Data.
	 * @return mixed
	 */
	protected function convert_empty_data_to_null( $data ) {
		if ( '' === $data || false === $data ) {
			return null;
		}

		if ( is_array( $data ) || is_object( $data ) ) {
			foreach ( $data as &$value ) {
				$value = $this->convert_empty_data_to_null( $value );
			}
		}

		return $data;
	}

	/**
	 * @param mixed $data Data.
	 * @return mixed
	 */
	protected function remove_empty_keys( &$data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => &$value ) {
				if ( '' === $key ) {
					unset( $data[ $key ] );
				} else {
					$this->remove_empty_keys( $value );
				}
			}
		}
	}

	/**
	 * @param array $data Data.
	 * @return array
	 */
	protected function format_data_according_structure( array $data ): array {
		if ( empty( $this->field_structure ) || empty( $data ) ) {
			return array();
		}

		/**
		 * Filter ACF fields from being indexed to WPE Engine Smart Search.
		 *
		 * This filter prevents ACF fields to be indexed using the field name. This is very useful for a number of reasons:
		 *  - Preventing unnecessary data from being indexed, increases performance.
		 *  - Prevents errors from being thrown when indexing data ( Errors like: Limit of total fields [1000] has been exceeded )
		 *
		 *
		 * example:
		 *
		 * You would want to prevent ACF fields with names 'acf_field_name1', 'acf_field_name2', 'acf_field_name3'
		 * are not indexed.  *
		 *
		 * add_filter( 'wpe_smartsearch/acf/excluded_field_names', function ( $excluded_field_names ) {
		 *      $custom_excluded_field_names= array(
		 *          'acf_field_name1',
		 *          'acf_field_name2',
		 *          'acf_field_name3',
		 *      );
		 *
		 *      return array_merge($excluded_field_names,$custom_excluded_field_names );
		 *  },
		 *  10,
		 *  1
		 * );
		 */
		$excluded_field_names = apply_filters( self::SMART_SEARCH_FILTER_ACF_EXCLUDED_FIELDS_NAMES, array() );

		$field_data = array();
		foreach ( $this->field_structure as $field_group ) {
			if ( empty( $field_group['fields'] ) ) {
				continue;
			}

			$field_group_title                = String_Transformation::camel_case( $field_group['title'] );
			$field_data[ $field_group_title ] = array();

			foreach ( $field_group['fields'] as $field ) {
				if ( is_array( $data ) && ! array_key_exists( $field['name'], $data ) ) {
					continue;
				}

				if ( in_array( $field['type'], $this::ACF_UNSUPPORTED_TYPES, true ) ) {
					continue;
				}

				$value = $data[ $field['name'] ];
				if ( Json_Schema_Type::NUMBER === $field['type'] || Json_Schema_Type::INTEGER === $field['type'] ) {
					// check with regex if value is an integer.
					$value = preg_match( '/^-?\d+$/', $value ) ? (int) $value : (float) $value;
				}

				$this->remove_empty_keys( $value );
				$this->remove_unsupported_data( $value, $excluded_field_names );
				$value = $this->convert_empty_data_to_null( $value );
				$field_data[ $field_group_title ][ String_Transformation::camel_case( $field['name'], array( '_' ) ) ] = $value;
			}
		}
		return $field_data;
	}

	protected function remove_unsupported_data( &$data, $excluded_field_names ) {
		if ( $this->is_wp_instance( $data ) ) {
			$data = \AtlasSearch\Index\filter_wp_object_to_array( $data );
		} elseif ( is_array( $data ) ) {
			foreach ( $data as $key => &$value ) {
				if ( $value ) {
					if ( $this->is_wp_instance( $value ) ) {
						$data[ $key ] = \AtlasSearch\Index\filter_wp_object_to_array( $value );
					} elseif ( '' === $key || ! $this->should_be_indexed( $value, $key, $excluded_field_names ) ) {
						unset( $data[ $key ] );
					} else {
						$this->remove_unsupported_data( $value, $excluded_field_names );
						if ( ! $this->should_be_indexed( $value, $key, $excluded_field_names ) ) {
							unset( $data[ $key ] );
						}
					}
				}
			}
		}
	}

	private function is_wp_instance( $data ): bool {
		if ( $data instanceof WP_Post || $data instanceof WP_Term || $data instanceof WP_User ) {
			return true;
		}

		return false;
	}

	private function should_be_indexed( $value, $key, $excluded_field_names ): bool {
		if ( is_array( $value ) && isset( $value['type'] ) && in_array( $value['type'], self::ACF_UNSUPPORTED_TYPES ) ) {
			return false;
		}

		return ! $this->filter_excluded_field_from_search_index( $excluded_field_names, $key );
	}

	 /**
	  * Apply filter to exclude specific field names from indexing.
	  *
	  * @param array  $excluded_field_names An array of field names excluded from indexing by default.
	  * @param string $field_name Field name to check if it should be excluded from indexing.
	  *
	  * @return bool True if the field name should be excluded from indexing, false otherwise.
	  */
	public function filter_excluded_field_from_search_index( $excluded_field_names, $field_name ): bool {
		if ( ! empty( $field_name )
			 && ! empty( $excluded_field_names )
			 && is_array( $excluded_field_names )
			 && in_array( $field_name, $excluded_field_names )
		) {
			return true;
		}

		return false;
	}
}
