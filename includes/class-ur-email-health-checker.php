<?php
/**
 * Email Health Checker.
 *
 * Runs a set of read-only checks against the site's current email settings
 * and DNS records to surface common causes of registration emails failing
 * to deliver.
 *
 * @class    UR_Email_Health_Checker
 * @version  1.0.0
 * @package  UserRegistration/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Email_Health_Checker' ) ) :

	/**
	 * UR_Email_Health_Checker Class.
	 */
	class UR_Email_Health_Checker {

		/**
		 * Common free-mail provider domains that block "From" spoofing by other senders.
		 *
		 * @var array
		 */
		private static $personal_domains = array(
			'gmail.com',
			'yahoo.com',
			'outlook.com',
			'hotmail.com',
			'live.com',
			'icloud.com',
			'aol.com',
			'protonmail.com',
			'zoho.com',
		);

		/**
		 * Run every check and return the results plus an issue count.
		 *
		 * @return array
		 */
		public static function run_checks() {
			$checks = array(
				self::check_sending_enabled(),
				self::check_from_domain_personal(),
				self::check_admin_email_set(),
				self::check_user_registration_email_enabled(),
				self::check_admin_notification_enabled(),
				self::check_smtp_configured(),
				self::check_from_domain_mx(),
				self::check_admin_email_pending_change(),
			);

			$issue_count = count(
				array_filter(
					$checks,
					function ( $check ) {
						return 'issue' === $check['status'];
					}
				)
			);

			return array(
				'checks'           => $checks,
				'issue_count'      => $issue_count,
				'smartsmtp_status' => self::smartsmtp_status(),
			);
		}

		/**
		 * Whether SmartSMTP (this vendor's own bundled SMTP plugin) is
		 * installed and/or active, so the UI can offer to install/activate
		 * it directly as the preferred fix when no SMTP is configured.
		 *
		 * @return string One of 'active', 'inactive', 'not_installed'.
		 */
		public static function smartsmtp_status() {
			$plugin_file = 'smart-smtp/smart-smtp.php';

			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $plugin_file ) ) {
				return 'active';
			}

			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				return 'inactive';
			}

			return 'not_installed';
		}

		/**
		 * The "From" address currently in effect.
		 *
		 * @return string
		 */
		private static function from_address() {
			return get_option( 'user_registration_email_from_address', get_option( 'admin_email' ) );
		}

		/**
		 * The domain portion of the "From" address.
		 *
		 * @return string
		 */
		private static function from_domain() {
			$address = self::from_address();
			$at_pos  = strrpos( (string) $address, '@' );

			return false === $at_pos ? '' : strtolower( substr( $address, $at_pos + 1 ) );
		}

		private static function check_sending_enabled() {
			$disabled = ur_option_checked( 'user_registration_email_setting_disable_email' );

			return array(
				'key'     => 'sending_enabled',
				'title'   => $disabled
					? __( 'Email sending is disabled', 'user-registration' )
					: __( 'Email sending is enabled', 'user-registration' ),
				'status'  => $disabled ? 'issue' : 'pass',
				'message' => $disabled
					? __( '"Disable Emails" is on. No registration emails will be sent.', 'user-registration' )
					: __( '"Disable Emails" is off. Emails will fire after registration.', 'user-registration' ),
				'fix'     => $disabled ? __( 'Turn it off under **Emails → General**.', 'user-registration' ) : '',
			);
		}

		private static function check_from_domain_personal() {
			$domain      = self::from_domain();
			$is_personal = in_array( $domain, self::$personal_domains, true );
			$site_domain = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );

			return array(
				'key'     => 'from_domain_personal',
				'title'   => $is_personal
					? __( '"From" address uses a personal provider', 'user-registration' )
					: __( '"From" address uses your own domain', 'user-registration' ),
				'status'  => $is_personal ? 'issue' : 'pass',
				'message' => $is_personal
					? sprintf(
						/* translators: %s: from address */
						__( 'Currently set to `%s`. Providers like Gmail block emails sent "from" their domains by other servers.', 'user-registration' ),
						self::from_address()
					)
					: sprintf(
						/* translators: %s: from address */
						__( 'Notifications send from `%s`.', 'user-registration' ),
						self::from_address()
					),
				'fix'     => $is_personal
					? sprintf(
						/* translators: 1: site domain, 2: suggested address */
						__( 'Change it to an address on **%1$s**, e.g. `noreply@%2$s`.', 'user-registration' ),
						$site_domain,
						$site_domain
					)
					: '',
			);
		}

		private static function check_admin_email_set() {
			$admin_email = get_option( 'admin_email' );
			$is_valid    = ! empty( $admin_email ) && is_email( $admin_email );

			return array(
				'key'     => 'admin_email_set',
				'title'   => $is_valid
					? __( 'Admin email is set', 'user-registration' )
					: __( 'Admin email is not set', 'user-registration' ),
				'status'  => $is_valid ? 'pass' : 'issue',
				'message' => $is_valid
					? sprintf(
						/* translators: %s: admin email */
						__( 'Notifications go to `%s`.', 'user-registration' ),
						$admin_email
					)
					: __( 'No valid admin email address is configured.', 'user-registration' ),
				'fix'     => $is_valid ? '' : __( 'Set a valid address under **Settings → General**.', 'user-registration' ),
			);
		}

		private static function check_user_registration_email_enabled() {
			$enabled = ur_option_checked( 'user_registration_enable_successfully_registered_email', true );

			return array(
				'key'     => 'user_registration_email_enabled',
				'title'   => $enabled
					? __( 'User registration email is enabled', 'user-registration' )
					: __( 'User registration email is disabled', 'user-registration' ),
				'status'  => $enabled ? 'pass' : 'issue',
				'message' => $enabled
					? __( 'Users will receive an email when they register.', 'user-registration' )
					: __( 'Users will not receive a confirmation email when they register.', 'user-registration' ),
				'fix'     => $enabled ? '' : __( 'Enable it under **Emails → To User**.', 'user-registration' ),
			);
		}

		private static function check_admin_notification_enabled() {
			$enabled = ur_option_checked( 'user_registration_enable_admin_email', true );

			return array(
				'key'     => 'admin_notification_enabled',
				'title'   => $enabled
					? __( 'Admin notification email is on', 'user-registration' )
					: __( 'Admin notification email is off', 'user-registration' ),
				'status'  => $enabled ? 'pass' : 'issue',
				'message' => $enabled
					? __( 'You will be notified of new registrations.', 'user-registration' )
					: __( 'You won\'t be notified of new registrations.', 'user-registration' ),
				'fix'     => $enabled ? '' : __( 'Enable it in the **Emails → To Admin** tab.', 'user-registration' ),
			);
		}

		private static function check_smtp_configured() {
			// Most SMTP plugins (WP Mail SMTP, FluentSMTP, Post SMTP, ...)
			// configure the mailer by hooking `phpmailer_init`. Merely
			// checking whether *anything* is hooked is unreliable — e.g.
			// WooCommerce hooks it on every site to set multipart bodies,
			// with no relation to SMTP at all — so build a real (but never
			// sent) PHPMailer instance, fire the hook, and check whether
			// something actually switched the transport away from PHP's
			// mail().
			$is_smtp = false;

			if ( ! class_exists( 'PHPMailer\PHPMailer\PHPMailer' ) ) {
				require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
				require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
				require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			}

			$phpmailer = new PHPMailer\PHPMailer\PHPMailer( true );

			/** This filter is documented in wp-includes/pluggable.php */
			do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

			if ( isset( $phpmailer->Mailer ) && 'mail' !== $phpmailer->Mailer ) {
				$is_smtp = true;
			}

			// Some SMTP plugins instead replace the pluggable `wp_mail()`
			// function outright, never touching `phpmailer_init` at all —
			// detect that by checking whether `wp_mail()` is still
			// WordPress core's own definition. Note this only catches an
			// override that actually took effect: a regular (non-mu,
			// non-network) plugin using the classic
			// `if ( ! function_exists( 'wp_mail' ) )` guard loads *after*
			// core's own pluggable.php has already claimed the function
			// name, so such an override silently never activates — which
			// is itself a real "mail isn't actually going through SMTP"
			// condition worth surfacing as an issue.
			if ( ! $is_smtp && function_exists( 'wp_mail' ) && class_exists( 'ReflectionFunction' ) ) {
				try {
					$reflection = new ReflectionFunction( 'wp_mail' );
					$core_file  = wp_normalize_path( ABSPATH . WPINC . '/pluggable.php' );
					$is_smtp    = wp_normalize_path( (string) $reflection->getFileName() ) !== $core_file;
				} catch ( ReflectionException $e ) {
					$is_smtp = false;
				}
			}

			return array(
				'key'     => 'smtp_configured',
				'title'   => $is_smtp
					? __( 'SMTP is configured', 'user-registration' )
					: __( 'No SMTP plugin found', 'user-registration' ),
				'status'  => $is_smtp ? 'pass' : 'issue',
				'message' => $is_smtp
					? __( 'An SMTP connection is configured for outgoing mail.', 'user-registration' )
					: __( "Your site is using PHP mail, which many hosts don't deliver reliably.", 'user-registration' ),
				'fix'     => $is_smtp ? '' : __( 'Install an SMTP plugin and connect a sending service for reliable delivery.', 'user-registration' ),
			);
		}

		private static function check_from_domain_mx() {
			$domain = self::from_domain();
			$has_mx = ! empty( $domain ) && checkdnsrr( $domain, 'MX' );

			return array(
				'key'     => 'from_domain_mx',
				'title'   => $has_mx
					? __( '"From" domain can receive mail', 'user-registration' )
					: __( '"From" domain can\'t receive mail', 'user-registration' ),
				'status'  => $has_mx ? 'pass' : 'issue',
				'message' => $has_mx
					? sprintf(
						/* translators: %s: domain */
						__( '`%s` has valid mail servers configured.', 'user-registration' ),
						$domain
					)
					: sprintf(
						/* translators: %s: domain */
						__( 'No mail servers (MX records) were found for `%s` — check for a typo in the "From" address.', 'user-registration' ),
						$domain
					),
				'fix'     => $has_mx ? '' : __( 'Double-check the domain in your "From" address is spelled correctly and can receive mail.', 'user-registration' ),
			);
		}

		private static function check_admin_email_pending_change() {
			$pending     = get_option( 'new_admin_email' );
			$has_pending = ! empty( $pending ) && is_array( $pending ) && ! empty( $pending['newemail'] );

			return array(
				'key'     => 'admin_email_pending_change',
				'title'   => $has_pending
					? __( 'Admin email change pending confirmation', 'user-registration' )
					: __( 'No admin email change pending', 'user-registration' ),
				'status'  => $has_pending ? 'issue' : 'pass',
				'message' => $has_pending
					? sprintf(
						/* translators: %s: pending new admin email */
						__( 'An email change to `%s` is waiting on confirmation. Notifications still go to the old address until it\'s confirmed.', 'user-registration' ),
						$pending['newemail']
					)
					: __( 'No admin email change is pending.', 'user-registration' ),
				'fix'     => $has_pending ? __( 'Check the old inbox for the confirmation link, or cancel the change under **Settings → General**.', 'user-registration' ) : '',
			);
		}
	}

endif;
