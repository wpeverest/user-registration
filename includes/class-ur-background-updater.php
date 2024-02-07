<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    UR_Background_Updater
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once dirname( __FILE__ ) . '/libraries/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once dirname( __FILE__ ) . '/libraries/wp-background-process.php';
}

/**
 * UR_Background_Updater Class.
 */
class UR_Background_Updater extends WP_Background_Process {

	/**
	 * Action name.
	 * @var string ur_updater in default.
	 */
	protected $action = 'ur_updater';

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		parent::dispatch();
	}

	/**
	 * Handle cron healthcheck.
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}
		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();

			return;
		}
		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param  string $callback Update callback function.
	 *
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'UR_UPDATING' ) ) {
			define( 'UR_UPDATING', true );
		}

		include_once 'functions-ur-update.php';

		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		UR_Install::update_db_version();
		parent::complete();
	}
}
