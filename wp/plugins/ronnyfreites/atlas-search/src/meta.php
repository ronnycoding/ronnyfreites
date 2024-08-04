<?php

namespace AtlasSearch\Meta;

function get_system_version() {
	$multisite = is_multisite() ? ' multisite' : '';
	return 'WP Engine Smart Search v' . WPE_SMART_SEARCH_VERSION . $multisite;
}

function get_domain_name() {
	return sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) );
}
