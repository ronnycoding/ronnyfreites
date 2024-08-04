<?php

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

use Wpe_Content_Engine\Core_Wp_Wrapper\Wp_Progress_Bar;
use Wpe_Content_Engine\Helper\Progress_Bar_Info_Trait;
use Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Factory;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager;
use Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Interface;
use const AtlasSearch\Index\MANUAL_INDEX;
use const Wpe_Content_Engine\Helper\Notifications\WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED;

// @codingStandardsIgnoreStart

/**
 * Implements example command.
 */
class Wpe_Content_Engine_Sync_Data {

	/**
	 * Syncs all data to Smart Search.
	 *
	 * ## EXAMPLES
	 *    wp wpe-smart-search sync-data --size=10 --no-resume
	 *    or
	 *    wp wpe-smart-search sync-data
	 *    or
	 *    wp wpe-smart-search sync-data --reset
	 *
	 * [--size=<batch-size>]
	 * : Used to sync ALL data in batches of <batch-size>. Batch size should be a positive integer.
	 * If is set to a very big integer the system might run out of memory or fail to sync all data.
	 * If no value is specified then defaults to 20
	 *
	 * [--no-resume]
	 * : Start sync from the start and not from last error
	 * default: true
	 *
	 * [--reset]
	 * : Clear all data before sync
	 * default: false
	 *
	 * @subcommand sync-data
	 * @when after_wp_load
	 *
	 */

	public function _sync_data( $args, $assoc_args ) {
		$batch_size = (int) ( $assoc_args['size'] ?? Batch_Options::DEFAULT_BATCH_SIZE );

		if ( $batch_size <= 0 ) {
			WP_CLI::error( 'Batch size should be an integer greater than zero' );
			return;
		}

		$with_reset = $assoc_args['reset'] ?? false;
		$with_resume = $assoc_args['resume'] ?? true;

		$batch_options = new Batch_Options(
			$batch_size,
			1,
			Batch_Sync_Factory::DATA_TO_SYNC,
			array( get_current_blog_id() )
		);

		/** @var Resume_Options|null $resume_options */
		$resume_options = get_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME, null );

		if ( $with_resume && !$with_reset && !empty( $resume_options ) && ( $resume_options instanceof Resume_Options ) ) {
			$batch_options->calculate_with_resume( $resume_options );

			WP_CLI::log(
				"Resume started! Starting with these parameters --> Table:{$resume_options->get_entity()}, "
				. " Page: {$batch_options->get_page()}, Batch Size: {$batch_options->get_batch_size()}"
			);
		}

		$sync_lock_manager = new Sync_Lock_Manager();
		$uuid = null;

		try {
			$time_start = new DateTime();
			$start = microtime(true);

			$moment = new DateTime();
			$can_start = $sync_lock_manager->can_start( $moment );
			$cannot_start_text = !$can_start ? 'yes' : 'no';
			WP_CLI::log( "Init check, lock present: {$cannot_start_text}" );

			if ( $can_start ) {
				// activate
				WP_CLI::log( "No lock present, starting sync..." );
				$uuid = $sync_lock_manager->start( $moment, null );
				WP_CLI::log( "Sync lock acquired. Sync lock ID: {$uuid}" );
			}
			else {
				WP_CLI::log( "Lock active! Cannot start a new sync! Exiting..." );
				return;
			}

			delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );

			if ( $with_reset ) {
				WP_CLI::log( "'--reset' flag was provided. Deleting all.");
				\AtlasSearch\Index\delete_all( MANUAL_INDEX );
			}

			foreach ( $batch_options->get_data_to_be_synced() as $short_name => $class ) {

				/** @var Batch_Sync_Interface| Progress_Bar_Info_Trait $obj ,
				 * @var string $class
				 */
				$obj = Batch_Sync_Factory::build( $class );
				$page = $batch_options->get_page();
				WP_CLI::log( "Sync started : {$short_name}" );

				do {
					try {
						$items = $obj->get_items( $page, $batch_size );

						if ( empty( $items ) ) {
							continue;
						}

						$obj->format_items( $items, $page );
						$obj->set_progress_bar( new Wp_Progress_Bar( count( $items ) ) );
						$obj->sync( $items );
						$page ++;
					} catch ( ErrorException $e ) {
						WP_CLI::error( "Something went wrong. Error message: {$e->getMessage()}", false );
					} finally {
						update_option(
							Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
							new Resume_Options( $short_name, $batch_size, $page )
						);

						if ( isset( $e ) && ( $e instanceof ErrorException ) ) {
							/** @var ErrorException|null $e */
							throw $e;
						}
					}
				} while ( count( $items ) >= $batch_size );

				$batch_options->set_page( 1 );

				WP_CLI::log(
					empty( $obj->get_items( 1, $batch_size ) ) ? "No {$short_name} data to sync" : "Sync Success: {$short_name}"
				);

				update_option( WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED, true );
			}

			delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );
			WP_CLI::log( 'Success syncing all data' );

			$fd = ( new DateTime() )->diff( $time_start );
			WP_CLI::log( WP_CLI::colorize( "%GTotal time: {$fd->format('%H:%i:%s.%f')}%n " ) );
		} catch ( ErrorException $e ) {
			WP_CLI::error( "There was an error during sync. Error message: {$e->getMessage()}", false );
		}
		finally {
			// make sure that we always release the lock if we acquired one this run.
			if ( !empty( $uuid ) ) {
				$moment = new DateTime();
				$sync_lock_manager->finish( $moment, $uuid );
				WP_CLI::log( "Sync lock ID {$uuid} released!" );
			}
			else {
				WP_CLI::log( 'No sync lock acquired this run.!' );
			}
		}
	}
}
WP_CLI::add_command( 'wpe-smart-search', 'Wpe_Content_Engine_Sync_Data' );

// @codingStandardsIgnoreEnd
