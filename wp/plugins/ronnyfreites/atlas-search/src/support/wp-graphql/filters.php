<?php
namespace AtlasSearch\Support\WpGraphql;

if ( ! is_plugin_active( 'wp-graphql/wp-graphql.php' )
	&& defined( 'WPE_SMARTSEARCH_WPGQL_TEST_FUNCTIONALITY' )
	&& ! WPE_SMARTSEARCH_WPGQL_TEST_FUNCTIONALITY ) {
	return;
}

function graphql_connection_page_info( $page_info, $page_class ) {
	global $global_hits;

	// We need to grab the query args from the connection to get the hits
	// and then we can use the hits to generate the cursor.
	$query_key = generate_query_key( $page_class->get_query_args() );

	// These are the actual hits for the respective query
	// We then use the hits to generate the cursor.
	$hits = &$global_hits[ $query_key ] ?? array();
	if ( empty( $hits ) ) {
		return $page_info;
	}
	$page_info['startCursor'] = encode_cursor( extract_order_by_fields( reset( $hits ) ) );
	$page_info['endCursor']   = encode_cursor( extract_order_by_fields( end( $hits ) ) );

	return $page_info;
}

add_filter(
	'graphql_connection_page_info',
	'AtlasSearch\Support\WpGraphql\graphql_connection_page_info',
	10,
	2
);

function graphql_connection_edges( $edges ) {
	global $global_hits;
	foreach ( $edges as &$edge ) {
		// We need to grab the query args from the connection to get the hits
		// and then we can use the hits to generate the cursor.
		$query_key = generate_query_key( $edge['connection']->get_query_args() );
		$hits      = &$global_hits[ $query_key ] ?? array();
		if ( empty( $hits ) ) {
			return $edges;
		}

		$current_doc    = array_shift( $hits );
		$edge['cursor'] = encode_cursor( extract_order_by_fields( $current_doc ) );
	}

	return $edges;
}

add_filter(
	'graphql_connection_edges',
	'AtlasSearch\Support\WpGraphql\graphql_connection_edges',
	10
);

/**
 * This function is used to generate a unique md5 key with any variable that is passed to it.
 *
 * @param mixed $var The query vars to be used to generate the key.
 */
function generate_query_key( $var ) {
	return md5( wp_json_encode( $var ) );
}

add_action(
	'wpe_smartsearch/search_operation_completed',
	function ( $hits, $query_vars ) {
		// This hook is fired before any wp-graphql hooks are fired
		// we must store all of the possible hits in a global variable
		// so that we can use them later in the graphql_connection_edges and graphql_connection_page_info hooks.
		global $global_hits;

		// We serialize the query_vars to create a unique key for each query
		// This is necessary because the same query can be executed multiple times
		// such as posts/pages/rabbits/myAmazingCustomPostType you name it.
		$key = md5( wp_json_encode( $query_vars ) );

		// We assign each query's hits to a global variable which is a hash of the query_vars
		// that belongs to the query.
		$global_hits[ $key ] = $hits;
	},
	10,
	2
);

add_filter( 'wpe_smartsearch/get_search_after', '\AtlasSearch\Support\WpGraphql\get_search_after_value', 10, 2 );

function get_search_after_value( $default_value, $query_vars ) {
	$search_after  = $query_vars['graphql_args']['after'] ?? null;
	$search_before = $query_vars['graphql_args']['before'] ?? null;
	$last          = $query_vars['graphql_args']['last'] ?? null;

	/**
	 * If both `after` and `before` are set, we should return the default value.
	 * This is because we actually need the last elements from the list of ordered documents.
	 */
	if ( isset( $search_after ) && isset( $last ) ) {
		return $default_value;
	}

	$decoded_value = decode_cursor( $search_after ?? $search_before );
	$decoded_value = is_array( $decoded_value ) ? $decoded_value : $default_value;

	return $decoded_value ?? $default_value;
}

add_filter(
	'wpe_smartsearch/get_order_by',
	function ( $value, $query_vars ) {
		if ( is_reversed( $query_vars ) ) {
			return reverse_orderby_order( $value );
		}

		return $value;
	},
	10,
	2
);

add_filter(
	'wpe_smartsearch/search_hits',
	function ( $hits, $query_vars ) {
		$search_after = $query_vars['graphql_args']['after'] ?? null;
		$first        = $query_vars['graphql_args']['first'] ?? null;
		$last         = $query_vars['graphql_args']['last'] ?? null;

		if ( isset( $search_after ) && isset( $last ) ) {
			$hits = filter_results( $hits, $search_after );
		}

		if ( is_reversed( $query_vars ) ) {
			$hits = array_reverse( $hits );
		}
		if ( $first ) {
			return array_slice( $hits, 0, $first );
		}
		if ( $last ) {
			// WP_GraphQL needs results reversed when "last" parameter is set.
			return array_reverse( array_slice( $hits, -$last, $last ) );
		}

		return $hits;
	},
	10,
	2
);
