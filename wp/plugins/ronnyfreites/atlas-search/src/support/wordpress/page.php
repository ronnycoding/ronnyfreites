<?php

namespace AtlasSearch\Support\WordPress;

function make_pages_publicly_queryable( $args, $post_type ) {
	if ( 'page' === $post_type && true === $args['public'] ) {
		$args['publicly_queryable'] = true;
	}

	return $args;
}

add_filter( 'register_post_type_args', 'AtlasSearch\Support\WordPress\make_pages_publicly_queryable', 10, 2 );
