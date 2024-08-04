<?php

namespace Wpe_Content_Engine\Helper;

/**
 * Class Admin_Notice
 *
 * @package Wpe_Content_Engine\Helper
 */
class Admin_Notice {

	/**
	 * @var string Transient name
	 */
	public const CONTENT_ENGINE_ADMIN_NOTICE = 'content_engine_admin_notice';

	public const NOTICE_TYPE_ERROR   = 'error';
	public const NOTICE_TYPE_WARNING = 'warning';
	public const NOTICE_TYPE_SUCCESS = 'success';
	public const NOTICE_TYPE_INFO    = 'info';

	private $current_user_key;

	/**
	 * @var int
	 */
	private const DEFAULT_EXPIRATION_TIME_IN_SECONDS = 5;
	/**
	 * @var string
	 */
	public string $notice_type;

	public function __construct() {
		$this->current_user_key = self::CONTENT_ENGINE_ADMIN_NOTICE . '_' . $this->get_current_user_id();
	}

	/**
	 * @param array $messages Messages for admin page.
	 */
	protected function set_messages( array $messages ): void {
		set_transient( $this->current_user_key, $messages, self::DEFAULT_EXPIRATION_TIME_IN_SECONDS );
	}

	/**
	 * Adds a message to notification array. For list of notice types please look here:
	 * https://developer.wordpress.org/reference/hooks/admin_notices/
	 *
	 * @param string $message Message to be added.
	 * @param string $notice_type Notice type. WordPress' values could be error, warning, success, info.
	 * @return void
	 */
	public function add_message( string $message, string $notice_type = self::NOTICE_TYPE_ERROR ): void {
		$notice_type_order = array(
			self::NOTICE_TYPE_ERROR,
			self::NOTICE_TYPE_WARNING,
			self::NOTICE_TYPE_INFO,
			self::NOTICE_TYPE_SUCCESS,
		);
		if ( ! in_array( $notice_type, $notice_type_order, true ) ) {
			return;
		}

		$all_messages                 = $this->get_messages();
		$all_messages[ $notice_type ] = array_unique( array( ...( $all_messages[ $notice_type ] ?? array() ), $message ) );
		$ordered_messages             = array();

		foreach ( $notice_type_order as $notice_type ) {
			if ( array_key_exists( $notice_type, $all_messages ) ) {
				$ordered_messages[ $notice_type ] = $all_messages[ $notice_type ];
			}
		}

		$this->set_messages( $ordered_messages );
	}

	/**
	 * @return array
	 */
	public function get_messages(): array {
		$messages = get_transient( $this->current_user_key );
		if ( empty( $messages ) ) {
			return array();
		}

		return $messages;
	}

	public function delete_messages(): void {
		delete_transient( $this->current_user_key );
	}

	/**
	 * @param array $messages Messages for admin page.
	 * @return string
	 */
	public function get_html( array $messages ): string {
		if ( empty( $messages ) ) {
			return '';
		}

		$html = '';

		foreach ( $messages as $notice_type => $messages_per_notice_type ) {
			$html .= $this->get_notice_type_html( $messages_per_notice_type, $notice_type );
		}

		return $html;
	}

	/**
	 * @param array  $notice_type_messages Messages per notice type.
	 * @param string $notice_type Notice type.
	 * @return string Html to be displayed with errors, warnings ...
	 */
	private function get_notice_type_html( array $notice_type_messages, string $notice_type ) {
		$error_html = '';

		foreach ( $notice_type_messages as $message ) {
			$error_html .= "<div class=\"notice notice-$notice_type is-dismissible\"><p>{$message}</p></div>";
		}

		return $error_html;
	}

	public function show_messages(): void {
		$messages = $this->get_messages();

		if ( empty( $messages ) ) {
			return;
		}
		// @codingStandardsIgnoreLine
		echo $this->get_html( $messages );

		$this->delete_messages();
	}

	/**
	 * Tried to use get_current_user_id() but there were some cases that this function hasn't been loaded yet
	 *
	 * @return int
	 */
	private function get_current_user_id(): int {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}
		wp_cookie_constants();

		return ( wp_get_current_user() )->ID;
	}
}
