<?php

namespace AtlasSearch\Migrations;

use Wpe_Content_Engine\Helper\Search\Search_Config;

/**
 * Determine if the migration should be ran.
 *
 * @param mixed $search_config The search config.
 * @return bool
 */
function should_migrate( $search_config ) {
	if ( ! isset( $search_config['version'] ) ) {
		return true;
	}

	if ( Search_Config::EXPECTED_VERSION !== $search_config['version'] ) {
		return true;
	}

	return false;
}


const NAMING_MAPPING = array(
	'title'              => 'post_title',
	'content'            => 'post_content',
	'excerpt'            => 'post_excerpt',
	'author.displayName' => 'author.user_nicename',
);

/**
 * Rename in all post types me config keys listed below
 * (title -> post_title, excerpt -> post_excerpt, content -> post_content, author.displayName -> author.user_nicename)
 *
 * @return bool
 */
function rename_post_keys_config() {
	$config = get_option( Search_Config::WPE_CONTENT_ENGINE_SEARCH_FIELDS, array() );

	if ( empty( $config ) ) {
		return false;
	}

	$models       = &$config['models'];
	$mapping_keys = array_keys( NAMING_MAPPING );

	if ( empty( $models ) ) {
		return false;
	}

	if ( ! array_intersect_key( $models['post'], array_flip( $mapping_keys ) ) ) {
		return false;
	}

	foreach ( $models as $post_type => $fields ) {
		foreach ( $fields as $field_name => $field_config ) {
			if ( in_array( $field_name, $mapping_keys, true ) ) {
				$models[ $post_type ][ NAMING_MAPPING[ $field_name ] ] = $field_config;
				unset( $models[ $post_type ][ $field_name ] );
			}
		}
	}

	update_option( Search_Config::WPE_CONTENT_ENGINE_SEARCH_FIELDS, $config );
	delete_transient( Search_Config::WPE_CONTENT_ENGINE_SEARCH_FIELDS );

	return true;
}
