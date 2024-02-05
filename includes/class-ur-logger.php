<?php
/**
 * Provides logging capabilities for debugging purposes.
 *
 * @class           UR_Logger
 * @since           1.0.5
 * @package         UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Logger Class.
 */
class UR_Logger implements UR_Logger_Interface {

	/**
	 * Stores registered log handlers.
	 *
	 * @var array
	 */
	protected $handlers;

	/**
	 * Minimum log level this handler will process.
	 *
	 * @var int Integer representation of minimum log level to handle.
	 */
	protected $threshold;

	/**
	 * Constructor for the logger.
	 *
	 * @param array  $handlers  Optional. Array of log handlers. If $handlers is not provided,
	 *                          the filter 'user_registration_register_log_handlers' will be used to define the handlers.
	 *                          If $handlers is provided, the filter will not be applied and the handlers will be
	 *                          used directly.
	 * @param string $threshold Optional. Define an explicit threshold. May be configured
	 *                          via  user_registration_register_log_handlers UR_LOG_THRESHOLD. By default, all logs will be processed.
	 */
	public function __construct( $handlers = null, $threshold = null ) {
		if ( null === $handlers ) {
			/**
			 * Filter to modify the log handlers.
			 * Default value is empty array.
			 */
			$handlers = apply_filters( 'user_registration_register_log_handlers', array() );
		}

		$register_handlers = array();

		if ( ! empty( $handlers ) && is_array( $handlers ) ) {
			foreach ( $handlers as $handler ) {
				$implements = class_implements( $handler );
				if ( is_object( $handler ) && is_array( $implements ) && in_array( 'UR_Log_Handler_Interface', $implements ) ) {
					$register_handlers[] = $handler;
				} else {
					/* translators: %1$s is replaced with the handler name. */
					$message = sprintf(
						__( 'The provided handler <code>%1$s</code> does not implement UR_Log_Handler_Interface.', 'user-registration' ),
						esc_html( is_object( $handler ) ? get_class( $handler ) : $handler )
					);
					ur_doing_it_wrong(
						__METHOD__,
						$message,
						'1.0.5'
					);
				}
			}
		}

		if ( null !== $threshold ) {
			$threshold = UR_Log_Levels::get_level_severity( $threshold );
		} elseif ( defined( 'UR_LOG_THRESHOLD' ) && UR_Log_Levels::is_valid_level( UR_LOG_THRESHOLD ) ) {
			$threshold = UR_Log_Levels::get_level_severity( UR_LOG_THRESHOLD );
		} else {
			$threshold = null;
		}

		$this->handlers  = $register_handlers;
		$this->threshold = $threshold;
	}

	/**
	 * Determine whether to handle or ignore log.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 *
	 * @return bool True if the log should be handled.
	 */
	protected function should_handle( $level ) {
		if ( null === $this->threshold ) {
			return true;
		}

		return $this->threshold <= UR_Log_Levels::get_level_severity( $level );
	}

	/**
	 * Add a log entry.
	 *
	 * This is not the preferred method for adding log messages. Please use log() or any one of
	 * the level methods (debug(), info(), etc.). This method may be deprecated in the future.
	 *
	 * @param string $handle Handler.
	 * @param string $message Message.
	 * @param string $level Log Level.
	 *
	 * @return bool
	 */
	public function add( $handle, $message, $level = UR_Log_Levels::NOTICE ) {
		/**
		 * Filter to modify the logger message.
		 *
		 * @param string $message Logger message.
		 * @param string $handle Logger handler.
		 */
		$message = apply_filters( 'user_registration_logger_add_message', $message, $handle );
		$this->log(
			$level,
			$message,
			array(
				'source'  => $handle,
				'_legacy' => true,
			)
		);

		return true;
	}

	/**
	 * Add a log entry.
	 *
	 * @param string $level   One of the following:
	 *                        'emergency': System is unusable.
	 *                        'alert': Action must be taken immediately.
	 *                        'critical': Critical conditions.
	 *                        'error': Error conditions.
	 *                        'warning': Warning conditions.
	 *                        'notice': Normal but significant condition.
	 *                        'info': Informational messages.
	 *                        'debug': Debug-level messages.
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function log( $level, $message, $context = array() ) {
		if ( ! UR_Log_Levels::is_valid_level( $level ) ) {
			/* translators: %s - Log Level */
			ur_doing_it_wrong( __METHOD__, sprintf( __( 'UR_Logger::log was called with an invalid level "%s".', 'user-registration' ), $level ), '3.0' );
		}

		if ( $this->should_handle( $level ) ) {
			$timestamp = current_time( 'timestamp' );
			/**
			 * Filter to modify the logger log messages.
			 *
			 * @param string $level   One of the following:
			 *                        'emergency': System is unusable.
			 *                        'alert': Action must be taken immediately.
			 *                        'critical': Critical conditions.
			 *                        'error': Error conditions.
			 *                        'warning': Warning conditions.
			 *                        'notice': Normal but significant condition.
			 *                        'info': Informational messages.
			 *                        'debug': Debug-level messages.
			 * @param string $message Log message.
			 * @param array  $context Optional. Additional information for log handlers.
			 */
			$message = apply_filters( 'user_registration_logger_log_message', $message, $level, $context );

			foreach ( $this->handlers as $handler ) {
				$handler->handle( $timestamp, $level, $message, $context );
			}
		}
	}

	/**
	 * Adds an emergency level message.
	 *
	 * System is unusable.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( UR_Log_Levels::EMERGENCY, $message, $context );
	}

	/**
	 * Adds an alert level message.
	 *
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function alert( $message, $context = array() ) {
		$this->log( UR_Log_Levels::ALERT, $message, $context );
	}

	/**
	 * Adds a critical level message.
	 *
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function critical( $message, $context = array() ) {
		$this->log( UR_Log_Levels::CRITICAL, $message, $context );
	}

	/**
	 * Adds an error level message.
	 *
	 * Runtime errors that do not require immediate action but should typically be logged
	 * and monitored.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function error( $message, $context = array() ) {
		$this->log( UR_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Adds a warning level message.
	 *
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
	 * necessarily wrong.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function warning( $message, $context = array() ) {
		$this->log( UR_Log_Levels::WARNING, $message, $context );
	}

	/**
	 * Adds a notice level message.
	 *
	 * Normal but significant events.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function notice( $message, $context = array() ) {
		$this->log( UR_Log_Levels::NOTICE, $message, $context );
	}

	/**
	 * Adds a info level message.
	 *
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function info( $message, $context = array() ) {
		$this->log( UR_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Adds a debug level message.
	 *
	 * Detailed debug information.
	 *
	 * @see UR_Logger::log
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 */
	public function debug( $message, $context = array() ) {
		$this->log( UR_Log_Levels::DEBUG, $message, $context );
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @param string $handle Handle.
	 *
	 * @return bool
	 */
	public function clear( $handle ) {
		$handler = new UR_Log_Handler_File();

		return $handler->clear( $handle );
	}
}
