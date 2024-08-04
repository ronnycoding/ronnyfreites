<?php

namespace Wpe_Content_Engine\Helper\Constants;

class Post_Status {

	// WP STATUSES.
	public const WP_PUBLISH    = 'publish';
	public const WP_FUTURE     = 'future';
	public const WP_DRAFT      = 'draft';
	public const WP_PENDING    = 'pending';
	public const WP_PRIVATE    = 'private';
	public const WP_TRASH      = 'trash';
	public const WP_AUTO_DRAFT = 'auto-draft';
	public const WP_INHERIT    = 'inherit';

	public const WP_STATUSES = array(
		self::WP_PUBLISH,
		self::WP_FUTURE,
		self::WP_DRAFT,
		self::WP_PENDING,
		self::WP_PRIVATE,
		self::WP_TRASH,
		self::WP_AUTO_DRAFT,
		self::WP_INHERIT,
	);
}
