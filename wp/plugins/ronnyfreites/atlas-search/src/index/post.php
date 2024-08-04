<?php

namespace AtlasSearch\Index;

use Exception;
use WP_Post;
use Wpe_Content_Engine\Helper\Acf_Support\Acf_Factory;
use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Exceptions\MissingSettingsException;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;
use Wpe_Content_Engine\WPSettings;

const INDEX_GRAPHQL = <<<'GRAPHQL'
mutation IndexRecord($input: DocumentInput!) {
	index(input: $input) {
		code
		document {
			data
			id
		}
		message
		success
	}
}
GRAPHQL;

const DELETE_GRAPHQL = <<<'GRAPHQL'
mutation DeleteRecord($id: ID!, $meta: MetaInput) {
  delete(id: $id, meta: $meta) {
    code
    message
    success
  }
}
GRAPHQL;

const DELETE_ALL_GRAPHQL = <<<'GRAPHQL'
mutation DeleteAllRecords($meta: MetaInput) {
  deleteAll(meta: $meta){
    code
    message
    success
  }
}
GRAPHQL;


function format_date( $date ) {
	return wp_date( 'Y-m-d\TH:i:s', strtotime( $date ) );
}


const MANUAL_INDEX         = 'MANUAL';
const AUTOMATIC_INDEX      = 'AUTOMATIC';
const ID_PREFIX_FIELD_NAME = '__id_prefix';
/**
 * Indexes a WP_Post
 *
 * @param WP_Post $post The post.
 * @param int     $post_id The post id.
 * @param string  $index_type The index operation type.
 */
function index_post( WP_Post $post, int $post_id, string $index_type = AUTOMATIC_INDEX ) {
	// bring the blocking states.
	if ( false === is_allowed( $post ) ) {
		return false;
	}

	$post_array                      = filter_wp_object_to_array( $post );
	$post_array['post_date_gmt']     = format_date( $post->post_date_gmt );
	$post_array['post_modified_gmt'] = format_date( $post->post_modified_gmt );
	$post_array['post_content']      = ! empty( $post_array['post_content'] ) ? wp_strip_all_tags( $post_array['post_content'] ) : $post_array['post_content'];
	// Get taxonomies.
	$terms = terms_for_post( $post );

	// Get author.
	$author_array = author_for_post( $post );

	$post_array = array_merge( $post_array, $terms, $author_array );

	// Get ACF content.
	$acf_info = Acf_Factory::build_acf_helper_for_type( $post_id, $post->post_type );
	if ( $acf_info ) {
		$post_array = array_merge( $post_array, $acf_info->get_data() );
	}

	/**
	 * Apply filters
	 */

	$id_prefix = \AtlasSearch\Hooks\filter_id_prefix();
	if ( $id_prefix ) {
		$post_array = array_merge( $post_array, array( \AtlasSearch\Index\ID_PREFIX_FIELD_NAME => $id_prefix ) );
	}

	$post_array = \AtlasSearch\Hooks\filter_extra_fields( $post_array, $post );

	$variables = array(
		'input' => array(
			'id'   => \AtlasSearch\Hooks\filter_index_id( $post->post_type, $post_id ),
			'data' => $post_array,
			'meta' => array(
				'system' => \AtlasSearch\Meta\get_system_version(),
				'action' => $index_type . ':index:' . $post->post_type,
				'source' => \AtlasSearch\Meta\get_domain_name(),
			),
		),
	);

	return graphql( INDEX_GRAPHQL, $variables );
}

function should_be_indexed( string $wp_post_status, string $wp_post_password ): bool {
	return Post_Status::WP_PUBLISH === $wp_post_status && empty( $wp_post_password );
}

function author_for_post( \WP_Post $post ) {
	if ( ! post_type_supports( $post->post_type, 'author' ) ) {
		return array();
	}

	$author = get_userdata( $post->post_author );
	if ( ! $author ) {
		return array( 'author' => null );
	}

	return array(
		'author' => array(
			'user_nicename' => $author->user_nicename,
		),
	);
}

function delete_post( WP_Post $post, int $post_id, $index_type = AUTOMATIC_INDEX ) {

	if ( false === is_allowed( $post ) ) {
		return false;
	}

	$variables = array(
		'id'   => $post->post_type . ':' . $post_id,
		'meta' => array(
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => $index_type . ':delete:' . $post->post_type,
			'source' => \AtlasSearch\Meta\get_domain_name(),
		),
	);

	return graphql( DELETE_GRAPHQL, $variables );
}

function delete_all( $index_type = AUTOMATIC_INDEX ) {
	$variables = array(
		'meta' => array(
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => $index_type . ':deleteAll',
			'source' => \AtlasSearch\Meta\get_domain_name(),
		),
	);

	return graphql( DELETE_ALL_GRAPHQL, $variables );
}

function graphql( $query, $variables ) {
	$client       = new Client( 'wpe-content-engine', WPE_SMART_SEARCH_VERSION );
	$opts         = \AtlasSearch\Support\WordPress\get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );
	$url          = $opts['url'] ?? '';
	$access_token = $opts['access_token'] ?? '';

	return $client->query(
		$url,
		$query,
		$variables,
		$access_token,
		array(
			'domainName' => \AtlasSearch\Meta\get_domain_name(),
		)
	);
}

function terms_for_post( WP_Post $post ) {
	$terms_obj  = array();
	$taxonomies = get_object_taxonomies( $post, 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( false === $taxonomy->publicly_queryable ) {
			continue;
		}

		$terms = get_the_terms( $post, $taxonomy->name );

		if ( $terms ) {
			$terms_obj[ map_taxonomy_name( $taxonomy->name ) ] = array();
			foreach ( $terms as $term ) {
				$terms_obj[ map_taxonomy_name( $taxonomy->name ) ][] = array(
					'name'             => $term->name,
					'slug'             => $term->slug,
					'term_taxonomy_id' => $term->term_taxonomy_id,
				);
			}
		}
	}

	return $terms_obj;
}

function map_taxonomy_name( $name ) {
	$mapping = array(
		'category' => 'categories',
		'post_tag' => 'tags',
	);

	if ( isset( $mapping[ $name ] ) ) {
		return $mapping[ $name ];
	}

	return $name;
}

function get_supported_custom_post_types(): array {
	return get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		)
	);
}

function get_supported_post_types(): array {
	$allowed_core_post_types = array( Post_Type::POST, Post_Type::PAGE );
	$allow_list              = array_merge( $allowed_core_post_types, get_supported_custom_post_types() );
	$excluded_post_types     = \AtlasSearch\Hooks\filter_excluded_post_types();

	return array_diff( $allow_list, $excluded_post_types );
}

/**
 * @param WP_Post $post Post.
 * @return bool
 */
function is_allowed( WP_Post $post ): bool {
	$allowed_post_types = get_supported_post_types();

	if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
		return false;
	}

	if ( Post_Status::WP_AUTO_DRAFT === $post->post_status ) {
		return false;
	}

	if ( '' === $post->post_name ) {
		return false;
	}

	return true;
}


/**
 * Gets the keys of a WP_Post.
 *
 * @param \WP_Post|\WP_Term|\WP_User $wp_object The WP Object.
 *
 * @return array Array of the post keys.
 */
function filter_wp_object_to_array( $wp_object ) {
	$wp_object_array = $wp_object->to_array();

	$keys_to_remove = array(
		// WP Post fields.
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
		'page_template',
		'post_category',
		'tags_input',
		'category',
		'ancestors',

		// WP Term fields.
		'count',
		'description',
		'filter',
		'parent',
		'term_group',
		'term_id',
		'term_taxonomy_id',

		// WP User fields.
		'user_avatar',
		'user_description',
		'user_nicename',
		'user_registered',
		'user_url',
		'user_login',
		'user_pass',
		'user_activation_key',
		'user_status',
	);

	foreach ( $keys_to_remove as $key ) {
		unset( $wp_object_array[ $key ] );
	}

	return $wp_object_array;
}

/**
 * Handle admin notice for any errors during realtime indexing.
 *
 * @param function $func Wrapped function.
 */
function admin_notice_handler( $func ) {
	try {
		$func();
	} catch ( MissingSettingsException $e ) {
		( new Admin_Notice() )->add_message( 'WP Engine Smart Search Settings Error: ' . $e->getMessage() );
	} catch ( \Exception $e ) {
		( new Admin_Notice() )->add_message( 'There was an error during WP Engine Smart Search indexing: ' . $e->getMessage() );
	}
}
