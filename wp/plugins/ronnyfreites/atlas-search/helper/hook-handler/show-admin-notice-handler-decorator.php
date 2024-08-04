<?php

namespace Wpe_Content_Engine\Helper\Hook_Handler;

use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Exceptions\AtlasUrlNotSetException;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Sync\Entity\Wordpress\WP_Entity;

class Show_Admin_Notice_Handler_Decorator {

	private const ACTION_UPSERT = 'upsert';
	private const ACTION_DELETE = 'delete';

	/**
	 * @var WP_Entity $wrapee
	 */
	private $wrapee;

	public function __construct( WP_Entity $wrapee ) {
		$this->wrapee = $wrapee;
	}

	/**
	 * @param mixed ...$args Rest of the args.
	 */
	public function upsert( ...$args ) {
		$this->handle( self::ACTION_UPSERT, ...$args );
	}

	/**
	 * @param mixed ...$args Rest of the args.
	 */
	public function delete( ...$args ) {
		$this->handle( self::ACTION_DELETE, ...$args );
	}

	/**
	 * @param string $action Action.
	 * @param mixed  ...$args Rest of the args.
	 */
	private function handle( string $action, ...$args ): void {
		try {
			if ( self::ACTION_UPSERT === $action ) {
				$this->wrapee->upsert( ...$args );
			} elseif ( self::ACTION_DELETE === $action ) {
				$this->wrapee->delete( ...$args );
			}
		} catch ( AtlasUrlNotSetException $e ) {
			$message = $e->getMessage();
			( new Debug_Logger() )->log( "There was an error during Smart Search sync: $message" );
			( new Admin_Notice() )->add_message( $e->getMessage() . ' Please check your settings to ensure you have configured & enabled content sync.' );

		} catch ( \Exception $e ) {
			( new Admin_Notice() )->add_message( 'There was an error during Smart Search sync. ' . $e->getMessage() );
			// TODO: ORN-264        Log error to WordPress.
		}
	}
}
