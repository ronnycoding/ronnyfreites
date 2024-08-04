<?php

namespace AtlasSearch\Support\WordPress;

const NETWORK_ADMIN = 'NETWORK_ADMIN';


if ( ! is_multisite() ) {
	return;
}

use const AtlasSearch\Hooks\SMART_SEARCH_HOOK_ID_PREFIX;

/**
 * Sets the index id prefix to the current blog id.
 *
 * @return int
 */
function get_index_id_prefix() {
	return get_current_blog_id();
}


add_filter( SMART_SEARCH_HOOK_ID_PREFIX, __NAMESPACE__ . '\get_index_id_prefix', 10, 0 );
