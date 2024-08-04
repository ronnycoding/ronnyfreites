<?php

namespace Wpe_Content_Engine\Helper\Search\Config;

use Wpe_Content_Engine\Helper\Acf_Support\Acf;

use function AtlasSearch\Index\get_supported_post_types;

class PostConfig extends Configurable {
	/**
	 * Gets the keys of a WP_Post.
	 *
	 * @return array Array of the post keys.
	 */
	public function get_post_keys() {
		$keys = array_keys( get_object_vars( new \WP_Post( new \stdClass() ) ) );

		$elements_to_remove = array(
			'ID',
			'post_author',
			'comment_status',
			'ping_status',
			'post_password',
			'to_ping',
			'pinged',
			'post_parent',
			'menu_order',
			'post_mime_type',
			'comment_count',
			'filter',
			'post_content_filtered',
			'guid',
			'post_modified',
			'post_date',
			'post_type',
			'post_status',
		);

		return array_diff( $keys, $elements_to_remove );
	}

	public function get_author_keys( string $post_type ) {
		if ( ! post_type_supports( $post_type, 'author' ) ) {
			return array();
		}

		return array(
			'author.user_nicename',
		);
	}

	public function get_config( array $existing_config ): array {
		$post_keys     = $this->get_post_keys();
		$type_configs  = array();
		$types         = get_supported_post_types();
		$search_config = array();

		foreach ( $types as $type ) {
			$all_post_keys         = \AtlasSearch\Hooks\filter_extra_search_config_fields( $post_keys, $type );
			$type_configs[ $type ] = array_merge( $all_post_keys, $this->get_author_keys( $type ) );
		}

		foreach ( $type_configs as $name => $fields ) {
			$search_config = $this->generate_taxonomies_config( $name, $existing_config, $search_config );

			foreach ( $fields as $field ) {
				$search_config[ $name ][ $field ] = $this->provide_config( $name, $field, $existing_config );
			}

			if ( ! Acf::is_acf_loaded() ) {
				continue;
			}

			$search_config = $this->get_acf_search_config( $name, $existing_config, $search_config );
		}

		return $search_config;
	}
}
