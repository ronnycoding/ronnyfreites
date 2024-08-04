<?php

namespace Wpe_Content_Engine\Helper\Search;

use Wpe_Content_Engine\Helper\Search\Config\PostConfig;

class Search_Config {
	public const WPE_CONTENT_ENGINE_SEARCH_FIELDS = 'wpe_content_engine_search_fields';
	public const EXPECTED_VERSION                 = 1;

	/**
	 * @return array
	 */
	public function get_fields(): array {
		return get_option( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS, array() );
	}

	/**
	 * @param array $search_fields The search fields.
	 *
	 * @return bool
	 */
	public function set_fields( array $search_fields ): bool {
		return update_option( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS, $search_fields );
	}

	/**
	 * @param bool $use_cache Use the cache or not.
	 * @return array
	 */
	public function get_config( bool $use_cache = false ): array {

		if ( $use_cache ) {
			$search_config = get_transient( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS );

			// Check if a migration is required based on the transient.
			if ( \AtlasSearch\Migrations\should_migrate( $search_config ) ) {
				\AtlasSearch\Migrations\rename_post_keys_config();
			} elseif ( false !== $search_config ) {
				return $search_config;
			}
		}

		$existing_config = $this->get_fields();

		// Check if we should migrate the options.
		if ( \AtlasSearch\Migrations\should_migrate( $existing_config ) ) {
			\AtlasSearch\Migrations\rename_post_keys_config();
			$existing_config = $this->get_fields();
		}

		$search_config = $this->generate_search_config( $existing_config );
		$this->set_fields( $search_config );

		set_transient( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS, $search_config );

		return $search_config;
	}

	public function clear_config() {
		delete_transient( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS );
		delete_option( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS );
	}

	/**
	 * @param array $config_updates The search configuration.
	 * @return array
	 */
	public function set_config( array $config_updates ): array {
		$search_config      = $this->get_config();
		$search_config_copy = $search_config;

		foreach ( $config_updates as $cat_key => $cat_value ) {
			if ( ! is_array( $cat_value ) ) {
				$search_config[ $cat_key ] = $cat_value;
				continue;
			}

			$search_config[ $cat_key ] = array();

			foreach ( $cat_value as $key => $value ) {
				$search_config[ $cat_key ][ $key ] = is_array( $value ) ?
					array_merge( $search_config_copy[ $cat_key ][ $key ], $value ) : $value;
			}
		}

		$this->set_fields( $search_config );

		set_transient( self::WPE_CONTENT_ENGINE_SEARCH_FIELDS, $search_config );

		return $search_config;
	}

	/**
	 * @param array $existing_config The existing search config in the DB.
	 * @return array
	 */
	public function generate_search_config( array $existing_config ): array {
		$posts = new PostConfig();

		return array(
			'models'             =>
				$posts->get_config( $existing_config['models'] ?? array() ),
			'fuzzy'              => array(
				'enabled'  => $existing_config['fuzzy']['enabled'] ?? false,
				'distance' => $existing_config['fuzzy']['distance'] ?? 1,
			),
			'disabledModelNames' => $existing_config['disabledModelNames'] ?? array(),
			'version'            => self::EXPECTED_VERSION,
			'searchType'         => $existing_config['searchType'] ?? null,
		);
	}
}
