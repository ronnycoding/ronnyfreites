<?php

namespace Wpe_Content_Engine\Helper\Constants;

class Security {
	public const HEADERS = array(
		'X-XSS-Protection:1; mode=block',
		'Permissions-Policy:camera=(),microphone=()',
		'Referrer-Policy:strict-origin-when-cross-origin always',
		'X-Content-Type-Options:nosniff always',
		'X-Frame-Options:deny',
		"Content-Security-Policy:script-src 'self'",
	);
}
