<?php

namespace AtlasSearch\Support\WordPress;

/**
 * Use the correct set transient function
 *
 * @param string $transient The transient name.
 * @param mixed  $value The transient value.
 * @param int    $expiration Time until expiration in seconds.
 *
 * @return bool True if the transient was set, false otherwise.
 */
function set_transient( $transient, $value, $expiration = 0 ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \set_site_transient( $transient, $value, $expiration );
	} else {
		return \set_transient( $transient, $value, $expiration );
	}
}

/**
 * Use the correct delete transient function
 *
 * @param string $transient The transient name.
 *
 * @return bool
 */
function delete_transient( $transient ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \delete_site_transient( $transient );
	} else {
		return \delete_transient( $transient );
	}
}

/**
 * Use the correct get transient function
 *
 * @param string $transient The transient name.
 *
 * @return mixed Value of transient.
 */
function get_transient( $transient ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \get_site_transient( $transient );
	} else {

		return \get_transient( $transient );
	}
}
