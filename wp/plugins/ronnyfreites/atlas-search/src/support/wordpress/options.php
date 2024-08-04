<?php

namespace AtlasSearch\Support\WordPress;

/**
 * Use the correct update option function
 *
 * @param string $option The option name.
 * @param mixed  $value The option value.
 * @param string $autoload Optional. Whether to load the option when WordPress starts up. For existing options, $autoload can only be updated using update_option() if $value is also changed. Accepts 'yes'|true to enable or 'no'|false to disable. For non-existent options, the default value is 'yes'. Default null.
 *
 * @return bool True if the value was updated, false otherwise.
 */
function update_option( $option, $value, $autoload = null ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \update_site_option( $option, $value );
	} else {
		return \update_option( $option, $value, $autoload );
	}
}

/**
 * Use the correct delete option function
 *
 * @param string $option The option name.
 *
 * @return bool
 */
function delete_option( $option ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \delete_site_option( $option );
	} else {
		return \delete_option( $option );
	}
}

/**
 * Use the correct get option function
 *
 * @param string $option The option name.
 * @param mixed  $default Optional. Default value to return if the option does not exist. Default false.
 *
 * @return mixed Value set for the option.
 */
function get_option( $option, $default = false ) {
	if ( is_wpe_smart_search_network_activated() ) {
		return \get_site_option( $option, $default );
	} else {
		return \get_option( $option, $default );
	}
}
