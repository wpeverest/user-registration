<?php
/**
 * User Registration Captcha Conflict Manager
 *
 * @package UserRegistration
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Captcha_Conflict_Manager Class
 *
 * Handles conflicts between different captcha implementations from various plugins.
 */
class UR_Captcha_Conflict_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		if (
			! get_option('urm_enable_no_conflict', true) ||
			! apply_filters( 'urm_apply_no_conflict', true )
		) {
			return;
		}


		// Add multiple hook points to catch scripts at different stages
		add_action( 'wp_loaded', function () {
			add_action( 'wp_footer', [ $this, 'enforce_no_conflict' ], 20 );
			add_action( 'wp_enqueue_scripts', [ $this, 'enforce_no_conflict' ], 10000 );
			add_action( 'wp_print_scripts', [ $this, 'enforce_no_conflict' ], PHP_INT_MAX );
			add_action( 'wp_head', [ $this, 'enforce_no_conflict' ], PHP_INT_MAX );
			add_action( 'wpforms_wp_footer', [ $this, 'enforce_no_conflict' ], 999 );
			add_action( 'shutdown', [ $this, 'enforce_no_conflict' ], 1 );
		}, PHP_INT_MAX );

		// Check if current page contains User Registration content
		add_action( 'wp', function() {
			if ( $this->page_contains_user_registration_content() ) {
				// Initialize plugin-specific restrictions only if UR content is present
				$this->init_wpforms_restrictions();
				$this->init_contact_form_7_restrictions();
				$this->init_contact_form_7_simple_recaptcha_restrictions();
				$this->init_gravity_forms_restrictions();
				$this->init_woocommerce_restrictions();
				$this->init_elementor_restrictions();
			}
		});
	}

	/**
	 * Initialize WPForms restrictions.
	 */
	private function init_wpforms_restrictions() {
		add_filter( 'wpforms_frontend_recaptcha_noconflict', '__return_false' );
		add_filter( 'wpforms_frontend_captcha_api', '__return_empty_string' );
	}

	/**
	 * Initialize Contact Form 7 restrictions.
	 */
	private function init_contact_form_7_restrictions() {
		add_filter( 'wpcf7_recaptcha_url', '__return_empty_string' );
	}

	/**
	 * Initialize Contact Form 7 Simple reCAPTCHA restrictions.
	 */
	private function init_contact_form_7_simple_recaptcha_restrictions() {
		// Prevent Contact Form 7 Simple reCAPTCHA from enqueuing scripts
		add_action( 'wp_footer', function() {
			// Remove the hCaptcha script enqueue
			remove_action( 'wp_footer', 'enqueue_cf7sr_hcaptcha_script' );
			// Remove the reCAPTCHA script enqueue
			remove_action( 'wp_footer', 'enqueue_cf7sr_recaptcha_script' );
			// Remove the Turnstile script enqueue
			remove_action( 'wp_footer', 'enqueue_cf7sr_turnstile_script' );
		}, 1 );
	}

	/**
	 * Initialize Gravity Forms restrictions.
	 */
	private function init_gravity_forms_restrictions() {
		add_filter( 'gform_recaptcha_url', '__return_empty_string' );
	}

	/**
	 * Initialize WooCommerce restrictions.
	 */
	private function init_woocommerce_restrictions() {
		add_filter( 'woocommerce_recaptcha_url', '__return_empty_string' );
	}

	/**
	 * Initialize Elementor restrictions.
	 */
	private function init_elementor_restrictions() {
		add_filter( 'elementor_recaptcha_url', '__return_empty_string' );
	}

	/**
	 * Enforce no conflict by removing captcha scripts from other plugins.
	 */
	public function enforce_no_conflict() {

		if (
			! get_option('urm_enable_no_conflict', true) ||
			! apply_filters( 'urm_apply_no_conflict', true )
		) {
			return;
		}

		// Remove scripts by plugin
		$this->remove_wpforms_scripts();
		$this->remove_contact_form_7_scripts();
		$this->remove_contact_form_7_simple_recaptcha_scripts();
		$this->remove_gravity_forms_scripts();
		$this->remove_woocommerce_scripts();
		$this->remove_elementor_scripts();
		$this->remove_generic_captcha_scripts();
	}

	/**
	 * Remove WPForms captcha scripts.
	 */
	private function remove_wpforms_scripts() {
		$wpforms_handles = [
			'wpforms-recaptcha',
			'wpforms-captcha'
		];

		foreach ( $wpforms_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing WPForms captcha script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove Contact Form 7 captcha scripts.
	 */
	private function remove_contact_form_7_scripts() {
		$cf7_handles = [
			'contact-form-7-recaptcha',
			'cf7-recaptcha'
		];

		foreach ( $cf7_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing Contact Form 7 captcha script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove Contact Form 7 Simple reCAPTCHA scripts.
	 */
	private function remove_contact_form_7_simple_recaptcha_scripts() {
		$cf7sr_handles = [
			'cf7sr-hcaptcha',    // [cf7sr-hcaptcha] shortcode
			'cf7sr-recaptcha',   // [cf7sr-recaptcha] shortcode
			'cf7sr-turnstile'    // [cf7sr-turnstile] shortcode
		];

		foreach ( $cf7sr_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing Contact Form 7 Simple reCAPTCHA script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove Gravity Forms captcha scripts.
	 */
	private function remove_gravity_forms_scripts() {
		$gf_handles = [
			'gravity-forms-recaptcha',
			'gf-recaptcha'
		];

		foreach ( $gf_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing Gravity Forms captcha script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove WooCommerce captcha scripts.
	 */
	private function remove_woocommerce_scripts() {
		$wc_handles = [
			'woocommerce-recaptcha',
			'wc-recaptcha'
		];

		foreach ( $wc_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing WooCommerce captcha script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove Elementor captcha scripts.
	 */
	private function remove_elementor_scripts() {
		$elementor_handles = [
			'elementor-recaptcha',
			'elementor-captcha'
		];

		foreach ( $elementor_handles as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				ur_get_logger()->notice(
					sprintf( __( 'Removing Elementor captcha script: %s', 'user-registration' ), $handle ),
					array( 'source' => 'ur-captcha-logs' )
				);
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove generic captcha scripts by URL pattern.
	 */
	private function remove_generic_captcha_scripts() {
		$scripts = wp_scripts();
		$captcha_urls = [
			'hcaptcha.com',
			'google.com/recaptcha',
			'gstatic.com/recaptcha',
			'challenges.cloudflare.com/turnstile',
			'api.recaptcha.net',
			'js.hcaptcha.com'
		];

		foreach ( $scripts->queue as $handle ) {
			// Skip User Registration scripts
			if (
				! isset( $scripts->registered[ $handle ] ) ||
				false !== strpos( $scripts->registered[ $handle ]->handle, 'ur-' ) ||
				false !== strpos( $scripts->registered[ $handle ]->handle, 'user-registration' )
			) {
				continue;
			}

			foreach ( $captcha_urls as $url ) {
				if ( false !== strpos( $scripts->registered[ $handle ]->src, $url ) ) {
					ur_get_logger()->notice(
						sprintf( __( 'Removing generic captcha script: %s (src: %s)', 'user-registration' ), $handle, $scripts->registered[ $handle ]->src ),
						array( 'source' => 'ur-captcha-logs' )
					);
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
					break;
				}
			}
		}
	}

	/**
	 * Check if the current page contains User Registration shortcodes or blocks.
	 *
	 * @return bool True if page contains UR content, false otherwise.
	 */
	private function page_contains_user_registration_content() {
		global $post;

		// If no post object, return false
		if ( ! $post ) {
			return false;
		}

		$content = $post->post_content;

		// Check for User Registration shortcodes
		$ur_shortcodes = [
			'user_registration_form',
			'user_registration_login',
			'user_registration_my_account',
			'user_registration_profile',
			'user_registration_reset_password'
		];

		foreach ( $ur_shortcodes as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) ) {
				return true;
			}
		}

		// Check for User Registration blocks
		$ur_blocks = [
			'user-registration/form',
			'user-registration/login',
			'user-registration/my-account',
			'user-registration/profile',
			'user-registration/reset-password'
		];

		foreach ( $ur_blocks as $block ) {
			if ( has_block( $block, $content ) ) {
				return true;
			}
		}

		if ( strpos( $content, '<!-- wp:user-registration/' ) !== false ) {
			return true;
		}

		if ( preg_match( '/user_registration_form.*id=["\'](\d+)["\']/', $content ) ) {
			return true;
		}

		return false;
	}
}
