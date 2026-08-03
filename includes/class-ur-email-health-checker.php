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
				'smtp_plugin'      => self::detected_smtp_plugin(),
			);
		}

		/**
		 * Whether SmartSMTP is installed and/or active.
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
		 * Common third-party SMTP plugins, used to name the active one in messaging.
		 *
		 * @var array
		 */
		private static $known_smtp_plugins = array(
			'smart-smtp/smart-smtp.php'     => 'SmartSMTP',
			'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
			'fluent-smtp/fluent-smtp.php'   => 'FluentSMTP',
			'post-smtp/postman-smtp.php'    => 'Post SMTP',
			'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
			'wp-smtp/wp-smtp.php'           => 'WP SMTP',
		);

		/**
		 * Which known SMTP plugin (if any) is currently active.
		 *
		 * @return array|null { slug, name, is_smartsmtp } or null if none of
		 *                     the known plugins are active.
		 */
		public static function detected_smtp_plugin() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( self::$known_smtp_plugins as $plugin_file => $name ) {
				if ( is_plugin_active( $plugin_file ) ) {
					return array(
						'slug'         => $plugin_file,
						'name'         => $name,
						'is_smartsmtp' => 'smart-smtp/smart-smtp.php' === $plugin_file,
					);
				}
			}

			return null;
		}

		/**
		 * Whether a plugin file is one of the SMTP plugins this feature knows
		 * about. The activate endpoint gates on this so a request can never
		 * make it activate an arbitrary plugin.
		 *
		 * @param string $plugin_file Plugin file, e.g. 'wp-mail-smtp/wp_mail_smtp.php'.
		 * @return bool
		 */
		public static function is_known_smtp_plugin( $plugin_file ) {
			return isset( self::$known_smtp_plugins[ $plugin_file ] );
		}

		/**
		 * Display name for a known SMTP plugin file.
		 *
		 * @param string $plugin_file Plugin file.
		 * @return string Empty string if the plugin isn't a known one.
		 */
		public static function known_smtp_plugin_name( $plugin_file ) {
			return self::is_known_smtp_plugin( $plugin_file ) ? self::$known_smtp_plugins[ $plugin_file ] : '';
		}

		/**
		 * A known SMTP plugin that's installed but not currently active.
		 *
		 * @return array|null { slug, name, is_smartsmtp } or null.
		 */
		private static function installed_inactive_smtp_plugin() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( self::$known_smtp_plugins as $plugin_file => $name ) {
				if ( ! is_plugin_active( $plugin_file ) && file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
					return array(
						'slug'         => $plugin_file,
						'name'         => $name,
						'is_smartsmtp' => 'smart-smtp/smart-smtp.php' === $plugin_file,
					);
				}
			}

			return null;
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
			// Fire a real (unsent) PHPMailer instance through `phpmailer_init` and
			// check if the transport switched away from PHP's mail() — checking
			// whether anything merely hooked the filter is unreliable (e.g.
			// WooCommerce hooks it too, unrelated to SMTP).
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

			// Some plugins replace wp_mail() directly instead of hooking
			// phpmailer_init — check if it's still core's own definition. A
			// plugin's `if ( ! function_exists( 'wp_mail' ) )` guard loading
			// after core's pluggable.php can silently lose that race, which
			// itself means SMTP isn't really active.
			if ( ! $is_smtp && function_exists( 'wp_mail' ) && class_exists( 'ReflectionFunction' ) ) {
				try {
					$reflection = new ReflectionFunction( 'wp_mail' );
					$core_file  = wp_normalize_path( ABSPATH . WPINC . '/pluggable.php' );
					$is_smtp    = wp_normalize_path( (string) $reflection->getFileName() ) !== $core_file;
				} catch ( ReflectionException $e ) {
					$is_smtp = false;
				}
			}

			if ( $is_smtp ) {
				return array(
					'key'     => 'smtp_configured',
					'title'   => __( 'SMTP is configured', 'user-registration' ),
					'status'  => 'pass',
					'message' => __( 'An SMTP connection is configured for outgoing mail.', 'user-registration' ),
					'fix'     => '',
				);
			}

			// An SMTP plugin that's running but hasn't switched the transport is
			// only missing its connection — don't report it as "not found".
			$active_plugin = self::detected_smtp_plugin();

			if ( $active_plugin ) {
				return array(
					'key'     => 'smtp_configured',
					'title'   => sprintf(
						/* translators: %s: SMTP plugin name */
						__( '%s is active but not connected', 'user-registration' ),
						$active_plugin['name']
					),
					'status'  => 'issue',
					// The title already names the plugin and its state, and the
					// action link states the fix — this only adds the consequence.
					'message' => __( 'Your site is still sending through PHP mail until its connection is set up.', 'user-registration' ),
					'fix'     => '',
					'action'  => $active_plugin['is_smartsmtp']
						? array(
							'type'  => 'link',
							'label' => __( 'Configure SmartSMTP', 'user-registration' ),
							'url'   => admin_url( 'admin.php?page=smart-smtp#/primary-connection' ),
						)
						: array(
							'type'  => 'link',
							'label' => __( 'Go to Installed Plugins', 'user-registration' ),
							'url'   => admin_url( 'plugins.php' ),
						),
				);
			}

			// "No SMTP plugin found" should only fire if that's actually true —
			// an installed-but-inactive plugin is a much smaller fix.
			$inactive_plugin = self::installed_inactive_smtp_plugin();

			if ( $inactive_plugin ) {
				return array(
					'key'     => 'smtp_configured',
					'title'   => sprintf(
						/* translators: %s: SMTP plugin name */
						__( '%s is installed but not active', 'user-registration' ),
						$inactive_plugin['name']
					),
					'status'  => 'issue',
					'message' => __( 'Your site is still sending through PHP mail.', 'user-registration' ),
					'fix'     => '',
					'action'  => array(
						'type'   => 'activate',
						'plugin' => $inactive_plugin['slug'],
						'label'  => sprintf(
							/* translators: %s: SMTP plugin name */
							__( 'Activate %s', 'user-registration' ),
							$inactive_plugin['name']
						),
					),
				);
			}

			return array(
				'key'     => 'smtp_configured',
				'title'   => __( 'No SMTP plugin found', 'user-registration' ),
				'status'  => 'issue',
				'message' => __( "Your site is using PHP mail, which many hosts don't deliver reliably.", 'user-registration' ),
				'fix'     => '',
				'action'  => array(
					'type'  => 'install_smartsmtp',
					'label' => __( 'Install & activate SmartSMTP', 'user-registration' ),
				),
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
