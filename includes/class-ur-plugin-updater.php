<?php
/**
 * Plugin Updater
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistration/Admin
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_AddOn_Updater', false ) ) {
	include_once dirname( __FILE__ ) . '/admin/updater/class-ur-addon-updater.php';
}

if ( ! class_exists( 'UR_Plugin_Updates', false ) ) {
	include_once dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updates.php';
}

/**
 * UR_Plugin_Updater Class.
 */
class UR_Plugin_Updater extends UR_Plugin_Updates {

	/**
	 * Plugin File.
	 *
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	private $plugin_name = '';

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * Plugins data.
	 *
	 * @var array of strings
	 */
	private $plugin_data = array();

	/**
	 * Validation errors.
	 *
	 * @var array of strings
	 */
	private $errors = array();

	/**
	 * Plugin Api Key.
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_updates( UR_PLUGIN_FILE );
	}

	/**
	 * Init the updater.
	 */
	public function init_updates( $_plugin_file ) {
		$this->plugin_file = $_plugin_file;
		$this->plugin_slug = str_replace( '.php', '', basename( $this->plugin_file ) );
		$this->plugin_name = basename( dirname( $this->plugin_file ) ) . '/' . $this->plugin_slug . '.php';

		register_activation_hook( $this->plugin_name, array( $this, 'plugin_activation' ), 10 );
		register_deactivation_hook( $this->plugin_name, array( $this, 'plugin_deactivation' ), 10 );

		add_filter( 'block_local_requests', '__return_false' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Include required files.
		include_once dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updater-api.php';
	}

	/**
	 * Run on admin init.
	 */
	public function admin_init() {
		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );

		$this->api_key     = get_option( $this->plugin_slug . '_license_key' );
		$this->plugin_data = get_plugin_data( $this->plugin_file );

		// Check to make sure we've RP extensions and plugin update capability.
		$extensions = $this->get_plugins_with_header( self::VERSION_TESTED_HEADER );
		if ( ! empty( $extensions ) && current_user_can( 'update_plugins' ) ) {
			$this->plugin_requests();
			add_action( "admin_notices", array( $this, "user_registration_upgrade_to_pro_notice" ) );
			$this->plugin_license_view();
		}

		$message = get_option( 'user_registration_failed_installing_extensions_message', '' );

		if ( $message ) {
			add_action( 'admin_notices', array( $this, 'user_registration_failed_extension_install' ) );
		}
	}

	/**
	 * Process plugin requests.
	 */
	private function plugin_requests() {
		if ( ! empty( $_POST[ $this->plugin_slug . '_license_key' ] ) ) {
			$this->activate_license_request();
		} elseif ( ! empty( $_GET[ $this->plugin_slug . '_deactivate_license' ] ) ) {
			$this->deactivate_license_request();
		} elseif ( ! empty( $_GET[ 'dismiss-' . sanitize_title( $this->plugin_slug ) ] ) ) {
			update_option( $this->plugin_slug . '_hide_key_notice', 1 );
		} elseif ( ! empty( $_GET['activated_license'] ) && $_GET['activated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'activated_key_notice' ) );
		} elseif ( ! empty( $_GET['deactivated_license'] ) && $_GET['deactivated_license'] === $this->plugin_slug ) {
			$this->add_notice( array( $this, 'deactivated_key_notice' ) );
		}
	}

	/**
	 * Activate a license request.
	 */
	private function activate_license_request() {
		$license_key = sanitize_text_field( $_POST[ $this->plugin_slug . '_license_key' ] );

		if ( $this->activate_license( $license_key ) ) {
			$this->install_extension();
			wp_redirect( remove_query_arg( array( 'deactivated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'activated_license', $this->plugin_slug ) ) );
			exit;
		} else {
			wp_redirect( remove_query_arg( array( 'activated_license', 'deactivated_license', $this->plugin_slug . '_deactivate_license' ) ) );
			exit;
		}
	}

	public function install_extension() {

		try {

			$slug   = 'user-registration-pro';
			$plugin = plugin_basename( sanitize_text_field( wp_unslash( 'user-registration-pro/user-registration.php' ) ) );
			$status = array(
				'install' => 'plugin',
				'slug'    => sanitize_key( wp_unslash( $slug ) ),
			);

			if ( ! current_user_can( 'install_plugins' ) ) {
				$status['errorMessage'] = esc_html__( 'Sorry, you are not allowed to install plugins on this site.', 'user-registration' );
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
				$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$status['plugin']     = $plugin;
				$status['pluginName'] = $plugin_data['Name'];

				if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
					$result = activate_plugin( $plugin );

					if ( is_wp_error( $result ) ) {
						$status['errorCode']    = $result->get_error_code();
						$status['errorMessage'] = $result->get_error_message();
						throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
					}

					add_action( "admin_notices", array( $this, "user_registration_extension_download_success_notice" ) );
				}
			}

			$api = json_decode(
				UR_Updater_Key_API::version(
					array(
						'license'   => get_option( 'user-registration_license_key' ),
						'item_name' => 'User Registration PRO',
					)
				)
			);

			if ( is_wp_error( $api ) ) {
				$status['errorMessage'] = $api->get_error_message();
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			$status['pluginName'] = $api->name;

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$status['debug'] = $skin->get_upgrade_messages();
			}

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( is_wp_error( $skin->result ) ) {
				$status['errorCode']    = $skin->result->get_error_code();
				$status['errorMessage'] = $skin->result->get_error_message();
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( $skin->get_errors()->get_error_code() ) {
				$status['errorMessage'] = $skin->get_error_messages();
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;

				$status['errorCode']    = 'unable_to_connect_to_filesystem';
				$status['errorMessage'] = esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'user-registration' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}
				throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $status['errorMessage'] ) );
			}

			$install_status = install_plugin_install_status( $api );

			if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
				activate_plugin( $install_status['file'] );
			}

			add_action( "admin_notices", array( $this, "user_registration_extension_download_success_notice" ) );
		} catch ( Exception $e ) {

			$message = $e->getMessage();
			add_option(
				'user_registration_failed_installing_extensions_message',
				 $message
			);
		}
	}

	/**
	 * Deactivate a license request
	 */
	private function deactivate_license_request() {
		$this->deactivate_license();
		wp_redirect( remove_query_arg( array( 'activated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'deactivated_license', $this->plugin_slug ) ) );
		exit;
	}

	/**
	 * Display plugin license view.
	 */
	private function plugin_license_view() {
		if ( ! $this->api_key ) {
			add_action( 'after_plugin_row', array( $this, 'plugin_license_form' ) );
			$this->add_notice( array( $this, 'key_notice' ) );
		} else {
			add_filter( 'plugin_action_links_' . $this->plugin_name, array( $this, 'plugin_action_links' ) );
		}

		add_action( 'admin_notices', array( $this, 'user_registration_error_notices' ) );
	}

	/**
	 * Add notices
	 */
	private function add_notice( $callback ) {
		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Add an error message
	 *
	 * @param string $message Your error message
	 * @param string $type    Type of error message
	 */
	public function add_error( $message, $type = '' ) {
		if ( $type ) {
			$this->errors[ $type ] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * Load errors from option
	 */
	public function load_errors() {
		$this->errors = get_option( $this->plugin_slug . '_errors', array() );
	}

	/**
	 * Store errors in option
	 */
	public function store_errors() {
		if ( sizeof( $this->errors ) > 0 ) {
			update_option( $this->plugin_slug . '_errors', $this->errors );
		} else {
			delete_option( $this->plugin_slug . '_errors' );
		}
	}

	/**
	 * @deprecated 2.0.6
	 */
	public function error_notices() {
		ur_deprecated_function( 'UR_Admin_Profile::error_notices', '1.4.1', 'UR_Plugin_Updater::user_registration_error_notices' );
	}

	/**
	 * Output errors
	 */
	public function user_registration_error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				include dirname( __FILE__ ) . '/admin/views/html-notice-error.php';
				if ( $key !== 'invalid_key' && did_action( 'all_admin_notices' ) ) {
					unset( $this->errors[ $key ] );
				}
			}
		}
	}

	/**
	 * Ran on plugin-activation.
	 */
	public function plugin_activation() {
		delete_option( $this->plugin_slug . '_hide_key_notice' );
	}

	/**
	 * Ran on plugin-deactivation.
	 */
	public function plugin_deactivation() {
		delete_option( 'user_registration_activated' );
		$this->deactivate_license();
	}

	/**
	 * Show the input form for the license key.
	 */
	public function plugin_license_form( $plugin_file ) {

		$slug = $this->plugin_slug;

		if ( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			$slug .= '-pro';
		}

		if ( strtolower( basename( dirname( $plugin_file ) ) ) === strtolower( $slug ) ) {
			include_once dirname( __FILE__ ) . '/admin/views/html-license-form.php';
		}
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'deactivate_license' => '<a href="' . remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( $this->plugin_slug . '_deactivate_license', 1 ) ) . '" class="deactivate-license" style="color: #a00;" title="' . esc_attr( __( 'Deactivate License Key', 'user-registration' ) ) . '">' . __( 'Deactivate License', 'user-registration' ) . '</a>',
		);

		return array_merge( $actions, $new_actions );
	}

	/**
	 * Try to activate a license.
	 */
	public function activate_license( $license_key ) {

		try {

			if ( empty( $license_key ) ) {
				throw new Exception( 'Please enter your license key' );
			}

			$activate_results = json_decode(
				UR_Updater_Key_API::activate(
					array(
						'license' => $license_key,
					)
				)
			);

			// Update activate results.
			update_option( $this->plugin_slug . '_license_active', $activate_results );

			if ( ! empty( $activate_results ) && is_object( $activate_results ) ) {

				if ( isset( $activate_results->error_code ) ) {
					throw new Exception( $activate_results->error );

				} elseif ( false === $activate_results->success ) {
					switch ( $activate_results->error ) {
						case 'expired':
							$error_msg = sprintf( __( 'The provided license key expired on %1$s. Please <a href="%2$s" target="_blank">renew your license key</a>.', 'user-registration' ), date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ), 'https://wpeverest.com/checkout/?edd_license_key=' . $license_key . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired' );
							break;

						case 'revoked':
							$error_msg = sprintf( __( 'The provided license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'user-registration' ), 'https://wpeverest.com/contact?utm_campaign=admin&utm_source=licenses&utm_medium=revoked' );
							break;

						case 'missing':
							$error_msg = sprintf( __( 'The provided license is invalid. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'user-registration' ), 'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing' );
							break;

						case 'invalid':
						case 'site_inactive':
							$error_msg = sprintf( __( 'The provided license is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'user-registration' ), 'https://wpeverest.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing' );
							break;

						case 'invalid_item_id':
						case 'item_name_mismatch':
							$error_msg = sprintf( __( 'This appears to be an invalid license key for <strong>%1$s</strong>.', 'user-registration' ), $this->plugin_data['Name'] );
							break;

						case 'no_activations_left':
							$error_msg = sprintf( __( 'The provided license key has reached its activation limit. Please <a href="%1$s" target="_blank">View possible upgrades</a> now.', 'user-registration' ), 'https://wpeverest.com/my-account/' );
							break;

						case 'license_not_activable':
							$error_msg = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'user-registration' );
							break;

						default:
							$error_msg = sprintf( __( 'The provided license key could not be found. Please <a href="%s" target="_blank">contact support</a> for more information.', 'user-registration' ), 'https://wpeverest.com/contact/' );
							break;
					}

					throw new Exception( sprintf( __( '<strong>Activation error:</strong> %1$s', 'user-registration' ), $error_msg ) );

				} elseif ( 'valid' === $activate_results->license ) {
					$this->api_key = $license_key;
					$this->errors  = array();

					update_option( $this->plugin_slug . '_license_key', $this->api_key );
					delete_option( $this->plugin_slug . '_errors' );

					return true;
				}

				throw new Exception( 'License could not activate. Please contact support.' );
			} else {
				throw new Exception( 'Connection failed to the License Key API server - possible server issue.' );
			}
		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Deactivate a license.
	 */
	public function deactivate_license() {
		$reset = UR_Updater_Key_API::deactivate(
			array(
				'license' => $this->api_key,
			)
		);

		delete_option( $this->plugin_slug . '_errors' );
		delete_option( $this->plugin_slug . '_license_key' );
		delete_option( $this->plugin_slug . '_license_active' );

		// Reset huh?
		$this->errors  = array();
		$this->api_key = '';
	}

	/**
	 * Show a notice prompting the user to update.
	 */
	public function key_notice() {
		if ( sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			include dirname( __FILE__ ) . '/admin/views/html-notice-key-unvalidated.php';
		}
	}

	/**
	 * Activation success notice.
	 */
	public function activated_key_notice() {
		include dirname( __FILE__ ) . '/admin/views/html-notice-key-activated.php';
	}

	/**
	 * Dectivation success notice.
	 */
	public function deactivated_key_notice() {
		include dirname( __FILE__ ) . '/admin/views/html-notice-key-deactivated.php';
	}

	/**
	 * Display error message when extension installation fails.
	 *
	 * @since 2.0.6
	 */
	public function user_registration_failed_extension_install() {
		$ur_pro_plugins_path = WP_PLUGIN_DIR . '\user-registration-pro\user-registration.php';
		$message = get_option( 'user_registration_failed_installing_extensions_message', '' );

		if ( ! file_exists ( $ur_pro_plugins_path ) ) {
			$message = $message . ' Please manually download <strong>User Registration PRO</strong>.';
			echo '<div class="error updated notice is-dismissible">
					<p>' . sprintf( __( '%1$s', 'user-registration' ), wp_kses_post( $message ) ) . '</p>
				</div>';

		} else if ( ! is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			$message = ' Please manually activate <strong>User Registration PRO</strong>.';
			echo '<div class="error updated notice is-dismissible">
					<p>' . sprintf( __( '%1$s', 'user-registration' ), wp_kses_post( $message ) ) . '</p>
				</div>';

		} else {
			delete_option( 'user_registration_failed_installing_extensions_message' );
		}

	}

	/**
	 * Display upgrade to PRO notice.
	 *
	 * @since 3.0.0
	 */
	public function user_registration_upgrade_to_pro_notice() {
		$license_key = get_option( $this->plugin_slug . '_license_key' );
		$ur_pro_plugins_path = WP_PLUGIN_DIR . '\user-registration-pro\user-registration.php';

		$link = '';

		if ( $license_key ) {
			$link = '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings' ) . '&tab=license') . '" target="_blank"><span class="dashicons dashicons-external"></span>' . __( 'Download and Install PRO', 'user-registration' ) . '</a>';
		} else {
			$link = '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings' ) . '&tab=license') . '" target="_blank"><span class="dashicons dashicons-external"></span>' . __( 'Activate License', 'user-registration' ) . '</a>';
		}

		if ( ! file_exists( $ur_pro_plugins_path ) ) {
			?>
				<div id="user-registration-review-notice" class="notice notice-info user-registration-notice" data-purpose="review">
					<div class="user-registration-notice-thumbnail">
						<img src="<?php echo UR()->plugin_url() . '/assets/images/UR-Logo.png'; ?>" alt="">
					</div>
					<div class="user-registration-notice-text">
						<h3><?php _e( '<strong> Upgrade To PRO!!</strong>', 'user-registration' ); ?></h3>
						<p><?php _e( '<strong>User Registration PRO</strong> will be effective 3 months from today. So If you are a premium user and have a license key then in order to smoothly using our addons please upgrade to PRO', 'user-registration' ); ?></p>
						<ul class="user-registration-notice-ul">
							<li><?php echo wp_kses_post( $link ); ?></li>
							<li><a href="https://wpeverest.com/support-forum/" class="button button-secondary notice-have-query"><span class="dashicons dashicons-testimonial"></span><?php _e( 'I have a query', 'user-registration' ); ?></a></li>
						</ul>
					</div>
				</div>
			<?php
		}
	}

	/**
	 * Success notice on PRO installation.
	 *
	 * @since 3.0.0
	 */
	public function user_registration_extension_download_success_notice() {
		 $notice_html = __("User Registration PRO has been installed successfully.", 'user-registration' );
		include dirname( __FILE__ ) . '/admin/views/html-notice-key-activated.php';
	}
}

new UR_Plugin_Updater();
