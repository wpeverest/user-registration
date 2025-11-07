<?php

namespace WPEverest\URMembership\Admin\Services;

/**
 * Payment Gateway Logging Service
 *
 * Comprehensive logging service for payment gateway activities using the existing ur_get_logger()
 *
 * Available logging methods:
 * - log_general() - General information logging
 * - log_transaction_start() - When a transaction begins
 * - log_transaction_success() - When a transaction completes successfully
 * - log_transaction_failure() - When a transaction fails
 * - log_webhook_received() - When a webhook is received
 * - log_webhook_processed() - When a webhook is processed
 * - log_api_request() - When making API requests
 * - log_api_response() - When receiving API responses
 * - log_subscription_cancellation() - When subscriptions are cancelled
 * - log_subscription_reactivation() - When subscriptions are reactivated
 * - log_payment_validation() - When validating payments
 * - log_refund_initiated() - When refunds are initiated
 * - log_refund_completed() - When refunds are completed
 * - log_error() - For error conditions
 * - log_warning() - For warning conditions
 * - log_debug() - For debug information
 */
class PaymentGatewayLogging {

	/**
	 * Constructor - Add filter to format log entries with context
	 */
	public function __construct() {
		add_filter( 'user_registration_format_log_entry', array( $this, 'format_log_entry_with_context' ), 10, 2 );
	}

	/**
	 * Format log entry to include context data
	 *
	 * @param string $entry Formatted log entry
	 * @param array $data Log data containing timestamp, level, message, context
	 * @return string Modified log entry
	 */
	public function format_log_entry_with_context( $entry, $data ) {
		// Only add context for payment gateway logs
		if ( ! empty( $data['context'] ) && isset( $data['context']['source'] ) && strpos( $data['context']['source'], 'urm-pg-' ) === 0 ) {
			$context = $data['context'];

			// Remove internal keys
			unset( $context['source'], $context['tag'], $context['_legacy'] );

			if ( ! empty( $context ) ) {
				$context_str = array();
				foreach ( $context as $key => $value ) {
					if ( is_array( $value ) || is_object( $value ) ) {
						$value = json_encode( $value );
					}
					$context_str[] = "$key: $value";
				}
				$entry .= ' | ' . implode( ' | ', $context_str );
			}
		}

		return $entry;
	}

	/**
	 * Log payment gateway activity
	 *
	 * @param string $gateway Payment gateway name (e.g., 'paypal', 'stripe')
	 * @param string $message Log message
	 * @param string $level Log level (info, notice, warning, error)
	 * @param array $context Additional context data
	 */
	public static function log( $gateway, $message, $level = 'info', $context = array() ) {
		// Initialize the class to ensure filter is added
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
		}

		// Use gateway-specific source name for separate log files
		$source = 'urm-pg-' . sanitize_title( $gateway );
		$context['source'] = $source;
		$context['gateway'] = $gateway; // Add gateway info to context

		ur_get_logger()->log( $level, $message, $context );
	}

	/**
	 * Log general information
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param string $level Log level (default: info)
	 * @param array $context Additional context data
	 */
	public static function log_general( $gateway, $message, $level = 'info', $context = array() ) {
		self::log( $gateway, $message, $level, $context );
	}

	/**
	 * Log transaction start
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: member_id, membership_id, amount, currency)
	 */
	public static function log_transaction_start( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'transaction_start';
		self::log( $gateway, $message, 'info', $context );
	}

	/**
	 * Log transaction success
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: transaction_id, member_id, amount, payment_method)
	 */
	public static function log_transaction_success( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'transaction_success';
		self::log( $gateway, $message, 'success', $context );
	}

	/**
	 * Log transaction failure
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: error_code, error_message, member_id)
	 */
	public static function log_transaction_failure( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'transaction_failure';
		self::log( $gateway, $message, 'error', $context );
	}

	/**
	 * Log webhook received
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: webhook_type, webhook_id, raw_data)
	 */
	public static function log_webhook_received( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'webhook_received';
		self::log( $gateway, $message, 'info', $context );
	}

	/**
	 * Log webhook processed
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: webhook_type, processing_result, member_id)
	 */
	public static function log_webhook_processed( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'webhook_processed';
		self::log( $gateway, $message, 'notice', $context );
	}

	/**
	 * Log API request
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: endpoint, method, request_data)
	 */
	public static function log_api_request( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'api_request';
		self::log( $gateway, $message, 'debug', $context );
	}

	/**
	 * Log API response
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: status_code, response_data, response_time)
	 */
	public static function log_api_response( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'api_response';
		self::log( $gateway, $message, 'debug', $context );
	}

	/**
	 * Log subscription cancellation
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: subscription_id, member_id, cancellation_reason)
	 */
	public static function log_subscription_cancellation( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'subscription_cancellation';
		self::log( $gateway, $message, 'notice', $context );
	}

	/**
	 * Log subscription reactivation
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: subscription_id, member_id, reactivation_reason)
	 */
	public static function log_subscription_reactivation( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'subscription_reactivation';
		self::log( $gateway, $message, 'notice', $context );
	}

	/**
	 * Log payment validation
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: validation_result, transaction_id, validation_method)
	 */
	public static function log_payment_validation( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'payment_validation';
		self::log( $gateway, $message, 'info', $context );
	}

	/**
	 * Log error conditions
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: error_code, error_details, stack_trace)
	 */
	public static function log_error( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'error';
		self::log( $gateway, $message, 'error', $context );
	}

	/**
	 * Log debug information
	 *
	 * @param string $gateway Payment gateway name
	 * @param string $message Log message
	 * @param array $context Additional context data (should include: debug_data, function_name, line_number)
	 */
	public static function log_debug( $gateway, $message, $context = array() ) {
		$context['event_type'] = 'debug';
		self::log( $gateway, $message, 'debug', $context );
	}
}
