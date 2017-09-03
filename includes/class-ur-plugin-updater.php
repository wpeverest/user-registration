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

/**
 * UR_Plugin_Updater Class.
 */
class UR_Plugin_Updater {

	/**
	 * Plugin ID.
	 * @var int
	 */
	private $plugin_id = 24; // Don't know how to fetch personal or developer plan over here. This ID is of personal plan :)

	/**
	 * Plugin Name.
	 * @var string
	 */
	private $plugin_name = '';

	/**
	 * Plugin File.
	 * @var string
	 */
	private $plugin_file = '';

	/**
	 * Plugin Slug.
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * Plugins data.
	 * @var array of strings
	 */
	private $plugin_data = array();

	/**
	 * Validation errors.
	 * @var array of strings
	 */
	private $errors = array();

	/**
	 * Constructor, used if called directly.
	 */
	public function __construct( $file ) {
		$this->init_updates( $file );
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

		include_once( dirname( __FILE__ ) . '/admin/updater/class-ur-plugin-updater-api.php' );
	}

	/**
	 * Run on admin init.
	 */
	public function admin_init() {
		$this->load_errors();

		add_action( 'shutdown', array( $this, 'store_errors' ) );

		$this->api_key     = get_option( $this->plugin_slug . '_license_key' );
		$this->plugin_data = get_plugin_data( $this->plugin_file );

		// Check for plugins update capability.
		if ( current_user_can( 'update_plugins' ) ) {
			$this->plugin_requests();
			$this->plugin_license_view();
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
			wp_redirect( remove_query_arg( array( 'deactivated_license', $this->plugin_slug . '_deactivate_license' ), add_query_arg( 'activated_license', $this->plugin_slug ) ) );
			exit;
		} else {
			wp_redirect( remove_query_arg( array( 'activated_license', 'deactivated_license', $this->plugin_slug . '_deactivate_license' ) ) );
			exit;
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

		add_action( 'admin_notices', array( $this, 'error_notices' ) );
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
	 * Output errors
	 */
	public function error_notices() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $key => $error ) {
				include( dirname( __FILE__ ) . '/admin/views/html-notice-error.php' );
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
		$this->deactivate_license();
	}

	/**
	 * Show the input form for the license key.
	 */
	public function plugin_license_form( $plugin_file ) {
		if ( strtolower( basename( dirname( $plugin_file ) ) ) === strtolower( $this->plugin_slug ) ) {
			include_once( dirname( __FILE__ ) . '/admin/views/html-license-form.php' );
		}
	}

	/**
	 * Display action links in the Plugins list table.
	 * @param  array $actions
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'deactivate_license' => '<a href="' . remove_query_arg( array( 'deactivated_license', 'activated_license' ), add_query_arg( $this->plugin_slug . '_deactivate_license', 1 ) ) . '" class="deactivate-license" title="' . esc_attr( __( 'Deactivate License Key', 'restaurantpress' ) ) . '">' . __( 'Deactivate License', 'restaurantpress' ) . '</a>',
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

			$activate_results = json_decode( UR_Plugin_Updater_Key_API::activate( array(
				'license' => $license_key,
				'item_id' => $this->plugin_id,
			) ), true );

			if ( false === $activate_results ) {
				throw new Exception( 'Connection failed to the License Key API server - possible server issue.' );

			} elseif ( isset( $activate_results['error_code'] ) ) {
				throw new Exception( $activate_results['error'] );

			} elseif ( isset( $activate_results['license'] ) && 'invalid' === $activate_results['license'] ) {
				throw new Exception( 'Activation error: The provided license is invalid.' );

			} elseif ( isset( $activate_results['license'] ) && 'valid' === $activate_results['license'] ) {
				$this->api_key = $license_key;
				$this->errors  = array();

				update_option( $this->plugin_slug . '_license_key', $this->api_key );
				delete_option( $this->plugin_slug . '_errors' );

				return true;
			}

			throw new Exception( 'License could not activate. Please contact support.' );

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return false;
		}
	}

	/**
	 * Deactivate a license.
	 */
	public function deactivate_license() {
		$deactivate_results = json_decode( UR_Plugin_Updater_Key_API::deactivate( array(
			'license' => $this->api_key,
			'item_id' => $this->plugin_id,
		) ), true );

		if ( isset( $deactivate_results['license'] ) && 'deactivated' === $deactivate_results['license'] ) {
			delete_option( $this->plugin_slug . '_license_key' );
			delete_option( $this->plugin_slug . '_errors' );
			delete_site_transient( 'update_plugins' );

			// Reset huh?
			$this->errors  = array();
			$this->api_key = '';
		}
	}

	/**
	 * Show a notice prompting the user to update.
	 */
	public function key_notice() {
		if ( sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_key_notice' ) ) {
			include( dirname( __FILE__ ) . '/admin/views/html-notice-key-unvalidated.php' );
		}
	}

	/**
	 * Activation success notice.
	 */
	public function activated_key_notice() {
		if ( $this->api_key ) {
			include( dirname( __FILE__ ) . '/admin/views/html-notice-key-activated.php' );
		} else {
			$this->add_error( 'Connection failed to the License Key API server - possible server issue.', 'restaurantpress' );
		}
	}

	/**
	 * Dectivation success notice.
	 */
	public function deactivated_key_notice() {
		if ( ! $this->api_key ) {
			include( dirname( __FILE__ ) . '/admin/views/html-notice-key-deactivated.php' );
		} else {
			$this->add_error( 'Connection failed to the License Key API server - possible server issue.', 'restaurantpress' );
		}
	}
}

new UR_Plugin_Updater( UR_PLUGIN_FILE );
