<?php
namespace AtlasSearch\Support\WpGraphql;

function extract_order_by_fields( $document ) {
	return $document['sort'];
}

function encode_cursor( array $cursor_data ) {
	return base64_encode( 'cursor-connenction:' . json_encode( $cursor_data ) );
}

function decode_cursor( string $cursor = null ) {
	if ( ! empty( $cursor ) ) {
		return json_decode( substr( base64_decode( $cursor ), strlen( 'cursor-connenction:' ) ) );
	}
}

function is_reversed( $query_vars ) {
	$search_before = $query_vars['graphql_args']['before'] ?? null;
	$last          = $query_vars['graphql_args']['last'] ?? null;

	if ( $search_before ?? $last ) {
		return true;
	}
	return false;
}

function reverse_orderby_order( array $order_by ) {
	if ( empty( $order_by ) ) {
		return $order_by;
	}

	foreach ( $order_by as $key => $value ) {
		$field                         = $value['field'];
		$direction                     = $value['direction'] ?? get_default_orderby_direction( $field );
		$order_by[ $key ]['direction'] = reverse_orderby_direction( $direction );
	}

	return $order_by;
}

function get_default_orderby_direction( string $field ) {
	return '_score' == $field ? 'desc' : 'asc';
}

function reverse_orderby_direction( string $direction ) {
	return 'asc' == $direction ? 'desc' : 'asc';
}

function filter_results( $hits, $search_after ) {
	foreach ( $hits as $key => $hit ) {
		$cursor = encode_cursor( extract_order_by_fields( $hit ) );
		if ( $cursor == $search_after ) {
			return array_slice( $hits, 0, $key );
		}
	}
	return $hits;
}
