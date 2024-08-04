<?php

namespace Wpe_Content_Engine\Helper\API\Sync_Data;

use ErrorException;
use WP_REST_Controller;
use WP_REST_Request;
use Wpe_Content_Engine\Helper\Client_Interface;
use Wpe_Content_Engine\Helper\Sync\Batches\Batch_Sync_Factory;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Progress;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Resume_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager;
use Wpe_Content_Engine\Settings_Interface;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Constants\Sync_Response_Status as Status;
use DateTime;

use const AtlasSearch\Index\MANUAL_INDEX;
use const Wpe_Content_Engine\Helper\Notifications\WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED;

/**
 * Sync data controller allowing syncing data from sync button
 */
class Sync_Data_Controller extends WP_REST_Controller {

	private const DEFAULT_LOCK_ROLLING_TIMEOUT = 10;

	private string $resource_name;

	/**
	 * @var Client_Interface $client
	 */
	protected $client;

	/**
	 * @var Settings_Interface $settings
	 */
	protected $settings;


	public function __construct( Client_Interface $client, Settings_Interface $settings ) {
		$this->client        = $client;
		$this->settings      = $settings;
		$this->namespace     = 'wpengine-smart-search/v1';
		$this->resource_name = '/sync-data';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array(
						$this,
						'sync_data',
					),
					'permission_callback' => array(
						$this,
						'permission_callback',
					),
				),
				'schema' => array(
					$this,
					'get_schema',
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->resource_name,
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array(
						$this,
						'delete_sync_data',
					),
					'permission_callback' => array(
						$this,
						'permission_callback',
					),
				),
			)
		);
	}

	/**
	 * Returns the Smart Search sync data info.
	 *
	 * @param  WP_REST_Request $request WP Rest request.
	 *
	 * @return Response
	 *
	 * @throws \Exception|ErrorException Thrown if there is an issue processing the sync.
	 */
	public function sync_data( WP_REST_Request $request ) {
		// validate the REST parameters.
		$json   = $request->get_json_params();
		$schema = $this->get_schema();
		$result = rest_validate_value_from_schema( $request->get_json_params(), $schema, 'Body' );

		$site_ids      = $this->get_site_ids( \AtlasSearch\Support\WordPress\NETWORK_ADMIN === $json['siteId'] );
		$batch_options = new Batch_Options(
			Batch_Options::DEFAULT_BATCH_SIZE,
			1,
			Batch_Sync_Factory::DATA_TO_SYNC,
			$site_ids
		);
		return $this->manage_sync_data( $json, $result, $batch_options );
	}

	private function get_site_ids( $is_network_activated ) {
		return $is_network_activated ? get_sites(
			array(
				'fields' => 'ids',
				'number' => 0,
			)
		) : array( get_current_blog_id() );
	}

	private function get_synced_types() {
		return array_keys( Batch_Sync_Factory::DATA_TO_SYNC );
	}

	/**
	 * Reset sync data progress.
	 *
	 * @param WP_REST_Request        $request WP Rest request.
	 * @param null|Sync_Lock_Manager $sync_lock_manager WP Sync Lock manager.
	 *
	 * @return \WP_REST_Response
	 */
	public function delete_sync_data(
		WP_REST_Request $request,
		Sync_Lock_Manager $sync_lock_manager = null
	) {
		$sync_lock_manager = $sync_lock_manager ?? new Sync_Lock_Manager( self::DEFAULT_LOCK_ROLLING_TIMEOUT );
		$can_start         = $sync_lock_manager->can_start( new DateTime() );

		if ( ! $can_start ) {
			return new \WP_REST_Response(
				array(
					'status'  => Status::ERROR,
					'message' => 'A data sync seems to already be active! Please wait for it to finish.',
				),
			);
		}

		try {
			\AtlasSearch\Index\delete_all( MANUAL_INDEX );
			\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );
			update_option( WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED, false );
		} catch ( \Exception $e ) {
			$logger = new Debug_Logger();

			$logger->log(
				"An error occurred while trying to delete all data. Error message: {$e->getMessage()} \n"
				. "Trace: {$e->getTraceAsString()} "
			);

			return new \WP_REST_Response(
				array(
					'status'  => Status::ERROR,
					'message' => 'Delete Sync Data Error: ' . $e->getMessage(),
				),
			);
		}

		return new \WP_REST_Response(
			array(
				'status'  => Status::COMPLETED,
				'message' => 'Indexed data were deleted successfully!',
			),
		);
	}

	/**
	 * Check permissions.
	 *
	 * @param WP_REST_Request $request The WP Rest request.
	 *
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Schema of the REST Endpoints
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$properties = array(
			'uuid'   => array( 'type' => 'string' ),
			'siteId' => array( 'type' => 'string' ),
		);

		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'sync-data',
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		);
	}

	private function manage_sync_data( $json, $result, Batch_Options $batch_options ) {
		$site_id      = $batch_options->get_current_site_id();
		$site_name    = $this->get_site_name( $site_id );
		$synced_types = $this->get_synced_types();
		if ( is_wp_error( $result ) ) {
			return new Response( Status::ERROR, 100, $result->get_error_message(), null, $site_name, $synced_types[0], $synced_types );
		}

		/** @var Resume_Options|null $resume_options */
		$resume_options = \AtlasSearch\Support\WordPress\get_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME, null );
		$all_site_ids   = $batch_options->get_site_ids();

		if ( ! empty( $resume_options ) && ( $resume_options instanceof Resume_Options ) ) {
			$batch_options->calculate_with_resume( $resume_options );
		}

		if ( ! isset( $resume_options ) || ! $resume_options->get_progress() ) {
			$progress = new Progress( $this->get_count_data_to_be_synced( $all_site_ids ), 0 );
		} else {
			$progress = $resume_options->get_progress();
		}

		$logger            = new Debug_Logger();
		$sync_lock_manager = new Sync_Lock_Manager( self::DEFAULT_LOCK_ROLLING_TIMEOUT );
		$uuid              = $json['uuid'];
		$logger->log( "Sync lock ID given: {$uuid}" );

		$moment         = new DateTime();
		$complete       = false;
		$can_start      = $sync_lock_manager->can_start( $moment, $uuid );
		$can_start_text = $can_start ? 'yes' : 'no';
		$logger->log( "Init check, can start: {$can_start_text}, Uuid supplied: {$uuid}" );

		if ( $can_start ) {
			// activate sync lock.
			$logger->log( 'No lock present, starting sync...' );

			try {
				$uuid = $sync_lock_manager->start( $moment, $uuid );
			} catch ( \Exception $e ) {
				$logger->log( "Something went wrong. Error message: {$e->getMessage()}" );
				$message = 'Sync Error: ' . $e->getMessage();

				return new Response( Status::ERROR, 0, $message, $uuid, $site_name, $synced_types[0], $synced_types );
			}

			$logger->log( "Sync lock acquired. Sync lock ID: {$uuid}" );
		} else {
			// log details of the lock in place for diagnosis.
			$last_status = $sync_lock_manager->get_status();
			$active_uuid = $last_status->get_uuid();
			$ids_equal   = $active_uuid === $uuid ? 'yes' : 'no';
			$logger->log( "UUIDs equal? {$ids_equal}" );
			$last_updated      = $last_status->get_last_updated();
			$last_updated_text = $last_updated->format( 'Y-m-d H:i:s' );
			$logger->log(
				"Lock active [{$active_uuid}], last updated {$last_updated_text}! Cannot start a new sync! Exiting..."
			);
			$uuid = null;

			return new Response(
				Status::ERROR,
				100,
				'A data sync is already in progress. You or another user may have begun this process. '
				. 'Please wait a few seconds and try again.',
				$uuid,
				$site_name,
				$synced_types[0],
				$synced_types
			);
		}

		\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );
		$current_data = $batch_options->get_current_class_to_be_synced();
		$site_id      = $batch_options->get_current_site_id();
		$site_name    = $this->get_site_name( $site_id );
		if ( ! empty( $current_data ) ) {
			$short_name = $current_data['short_name'];
			$obj        = Batch_Sync_Factory::build( $current_data['class'], \AtlasSearch\Support\WordPress\NETWORK_ADMIN === $json['siteId'], $site_id );
			$page       = $batch_options->get_page();
			$items      = $obj->get_items( $page, Batch_Options::DEFAULT_BATCH_SIZE );
		}

		if ( ! empty( $resume_options ) && ( $resume_options instanceof Resume_Options ) && $resume_options->get_entity() === 'COMPLETED' ) {
			\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );

			if ( ! empty( $uuid ) ) {
				$moment = new DateTime();
				$sync_lock_manager->finish( $moment, $uuid );
				$logger->log( "Sync lock ID {$uuid} released!" );
			} else {
				$logger->log( 'No sync lock acquired this run.!' );
			}

			$logger->log( 'Returning a status of COMPLETED' );
			$complete = true;

			update_option( WPE_CONTENT_ENGINE_RE_SYNC_HAS_OCCURRED, true );

			return new Response( Status::COMPLETED, 100, '', $uuid, $site_name, null, $synced_types );
		}

		if ( empty( $items ) ) {
			if ( $batch_options->is_last() ) {

				\AtlasSearch\Support\WordPress\update_option(
					Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
					new Resume_Options( 'COMPLETED' )
				);

				$logger->log( 'COMPLETING ....' );

				return new Response( Status::PENDING, 100, "{$site_name}: Syncing {$short_name}", $uuid, $site_name, $short_name, $synced_types );
			}

			$debug_message = "Current Site id: {$site_id} - Post Type{$short_name}: page -> {$page}";
			if ( $batch_options->is_last_class_to_be_synced() ) {
				$site_id = $batch_options->get_next_site_id();
			}
			$next_short_name = $batch_options->get_next_class_name();

			\AtlasSearch\Support\WordPress\update_option(
				Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
				new Resume_Options( $next_short_name, Batch_Options::DEFAULT_BATCH_SIZE, 1, $site_id, $progress )
			);

			$logger->log( "Returning a status of PENDING with lockID [{$uuid}] for object {$short_name}, {$debug_message}." );

			return new Response( Status::PENDING, $progress->get_rounded_percentage(), "{$site_name}: Syncing {$short_name}", $uuid, $site_name, $short_name, $synced_types );
		}

		$logger->log( "Performing sync batch and incrementing page with lockID [{$uuid}] for object {$short_name}." );
		try {
			$obj->sync( $items );
			$page++;
		} catch ( \Throwable $e ) {
			$message = $e->getMessage();
			$logger->log( "Something went wrong. Error message: {$message}" );

			if ( ! empty( $uuid ) ) {
				$moment = new DateTime();
				$sync_lock_manager->finish( $moment, $uuid );
				$logger->log( "Sync lock ID {$uuid} released!" );
			} else {
				$logger->log( 'No sync lock acquired this run.!' );
			}
		} finally {
			if ( ! $complete && $can_start ) {
				$progress->increase_synced_items( count( $items ) );

				\AtlasSearch\Support\WordPress\update_option(
					Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME,
					new Resume_Options( $short_name, Batch_Options::DEFAULT_BATCH_SIZE, $page, $batch_options->get_current_site_id(), $progress )
				);

				if ( isset( $e ) && ( $e instanceof ErrorException ) ) {
					/** @var ErrorException|null $e */
					$logger->log( "Returning a status of ERROR with lockID [{$uuid}] for object {$short_name}." );
					$message = 'Sync Error: ' . $e->getMessage();

					return new Response( Status::ERROR, 0, $message, $uuid, $site_name, $short_name, $synced_types );
				}

				$logger->log( "Finally{} Returning a status of PENDING with lockID [{$uuid}] for object {$short_name}." );

				return new Response( Status::PENDING, $progress->get_rounded_percentage(), "{$site_name}: Syncing {$short_name}", $uuid, $site_name, $short_name, $synced_types );
			}
		}
	}

	/**
	 * In case of multisite we provide total site ids to be synced.
	 *
	 * @param array $site_ids .
	 * @return int
	 */
	private function get_count_data_to_be_synced( $site_ids ) {
		$counter              = 0;
		$is_network_activated = count( $site_ids ) > 1;
		foreach ( $site_ids as $site_id ) {
			if ( $is_network_activated ) {
				switch_to_blog( $site_id );
			}
			foreach ( Batch_Sync_Factory::DATA_TO_SYNC as $item ) {
				$obj      = Batch_Sync_Factory::build( $item, $is_network_activated, $site_id );
				$counter += $obj->get_total_items();
			}
			if ( $is_network_activated ) {
				restore_current_blog();
			}
		}

		return $counter;
	}

	public function get_site_name( $site_id ) {
		if ( is_multisite() ) {
			return get_blog_details( $site_id )->blogname;
		}

		// get site name if not multisite enabled.
		return get_bloginfo( 'name' );
	}
}
