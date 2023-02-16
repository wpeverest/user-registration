<?php
/**
 * Installation related functions and actions.
 *
 * @package UserRegistration\Classes
 * @since   1.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_Install Class.
 */
class UR_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.0.0'   => array(
			'ur_update_100_db_version',
		),
		'1.2.0'   => array(
			'ur_update_120_usermeta',
			'ur_update_120_db_version',
		),
		'1.3.0'   => array(
			'ur_update_130_db_version',
			'ur_update_130_post',
		),
		'1.4.0'   => array(
			'ur_update_140_db_version',
			'ur_update_140_option',
		),
		'1.4.2'   => array(
			'ur_update_142_db_version',
			'ur_update_142_option',
		),
		'1.5.8.1' => array(
			'ur_update_1581_db_version',
			'ur_update_1581_meta_key',
		),
		'1.6.0'   => array(
			'ur_update_160_db_version',
			'ur_update_160_option_migrate',
		),
		'1.6.2'   => array(
			'ur_update_162_db_version',
			'ur_update_162_meta_key',
		),
	);

	/**
	 * Background update class.
	 *
	 * @var object
	 */
	private static $background_updater;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_action( 'in_plugin_update_message-user-registration/user-registration.php', array( __CLASS__, 'in_plugin_update_message' ) );
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		include_once dirname( __FILE__ ) . '/class-ur-background-updater.php';
		self::$background_updater = new UR_Background_Updater();
	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'user_registration_version' ), UR()->version, '<' ) ) {
			self::install();
			do_action( 'user_registration_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_user_registration'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			self::update();
			UR_Admin_Notices::add_notice( 'update' );
		}
		if ( ! empty( $_GET['force_update_user_registration'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			do_action( 'wp_' . get_current_blog_id() . '_ur_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=user-registration-settings' ) );
			exit;
		}
		if ( ! empty( $_GET['install_user_registration_pages'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			self::create_pages();
			UR_Admin_Notices::remove_notice( 'install' );
			delete_option( 'user_registration_onboarding_skipped' );
		}
	}

	/**
	 * Install UR.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'ur_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'ur_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		ur_maybe_define_constant( 'UR_INSTALLING', true );

		self::remove_admin_notices();
		self::create_options();
		self::create_tables();
		self::create_roles();
		self::setup_environment();
		self::create_form();
		self::create_files();
		self::update_ur_version();
		self::maybe_update_db_version();
		self::maybe_add_installation_date();

		$path = WP_CONTENT_DIR . '/uploads/user_registration_uploads/profile-pictures';

		if ( ! is_dir( $path ) ) {
			mkdir( $path, 0777, true );
		}

		delete_transient( 'ur_installing' );

		do_action( 'user_registration_flush_rewrite_rules' );
		do_action( 'user_registration_installed' );
		set_transient( '_ur_activation_redirect', 1, 30 );
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 1.2.0
	 */
	private static function remove_admin_notices() {
		include_once dirname( __FILE__ ) . '/admin/class-ur-admin-notices.php';
		UR_Admin_Notices::remove_all_notices();
	}

	/**
	 * Setup UR environment - post types, taxonomies, endpoints.
	 *
	 * @since 1.2.0
	 */
	private static function setup_environment() {
		UR_Post_Types::register_post_types();
		UR()->query->init_query_vars();
		UR()->query->add_endpoints();
	}

	/**
	 * Is this a brand new UR install?
	 *
	 * @since  1.2.0
	 * @return boolean
	 */
	private static function is_new_install() {
		return is_null( get_option( 'user_registration_version', null ) ) && is_null( get_option( 'user_registration_db_version', null ) );
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since  1.2.0
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'user_registration_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * See if we need the wizard or not.
	 */
	private static function maybe_enable_setup_wizard() {
		if ( apply_filters( 'user_registration_enable_setup_wizard', self::is_new_install() ) ) {
			UR_Admin_Notices::add_notice( 'install' );
		}
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 1.2.0
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			if ( apply_filters( 'user_registration_enable_auto_update_db', false ) ) {
				self::init_background_updater();
				self::update();
			} else {
				UR_Admin_Notices::add_notice( 'update' );
			}
		} else {
			self::update_db_version();
		}
	}

	/**
	 * May be add installation date. Donot insert on every update.
	 *
	 * @since 1.5.8
	 */
	private static function maybe_add_installation_date() {

		$installed_date = get_option( 'user_registration_activated' );

		if ( empty( $installed_date ) ) {
			update_option( 'user_registration_activated', current_time( 'Y-m-d' ) );
			update_option( 'user_registration_updated_at', current_time( 'Y-m-d' ) );
		}else{
			update_option( 'user_registration_updated_at', current_time( 'Y-m-d' ) );
		}
	}

	/**
	 * Update UR version to current.
	 */
	private static function update_ur_version() {
		delete_option( 'user_registration_version' );
		add_option( 'user_registration_version', UR()->version );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  1.2.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		$updates            = self::$db_updates;
		$current_db_version = get_option( 'user_registration_db_version' );

		$db_needs_update = array( '1.2.2', '1.2.3', '1.2.4' );

		if ( in_array( $current_db_version, $db_needs_update ) ) {
			$updates['1.2.5'] = array(
				'ur_update_125_usermeta',
				'ur_update_125_db_version',
			);
		}
		return $updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'user_registration_db_version' );
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New UserRegistration DB version or null.
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'user_registration_db_version' );
		add_option( 'user_registration_db_version', is_null( $version ) ? UR()->version : $version );
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {
		include_once dirname( __FILE__ ) . '/admin/functions-ur-admin.php';

		$pages = apply_filters(
			'user_registration_create_pages',
			array(
				'myaccount' => array(
					'name'    => _x( 'my-account', 'Page slug', 'user-registration' ),
					'title'   => _x( 'My Account', 'Page title', 'user-registration' ),
					'content' => '[' . apply_filters( 'user_registration_my_account_shortcode_tag', 'user_registration_my_account' ) . ']',
				),
			)
		);

		$default_form_page_id = get_option( 'user_registration_default_form_page_id' );

		if ( $default_form_page_id ) {
			$pages['registration'] = array(
				'name'    => _x( 'registration', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Registration', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . esc_attr( $default_form_page_id ) . '"]',
			);
		}

		foreach ( $pages as $key => $page ) {
			ur_create_page( esc_sql( $page['name'] ), 'user_registration_' . $key . '_page_id', wp_kses_post( ( $page['title'] ) ), wp_kses_post( $page['content'] ) );
		}
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {
		// Include settings so that we can run through defaults.
		include_once dirname( __FILE__ ) . '/admin/class-ur-admin-settings.php';

		$settings = UR_Admin_Settings::get_settings_pages();

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $section ) {
				if ( ! method_exists( $section, 'get_settings' ) ) {
					continue;
				}
				$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

				foreach ( $subsections as $subsection ) {
					foreach ( $section->get_settings( $subsection ) as $value ) {
						if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
							$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
							add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Create form on first install.
	 */
	public static function create_form() {
		$hasposts = get_posts( 'post_type=user_registration' );

		if ( 0 === count( $hasposts ) ) {
			update_option( 'user_registration_first_time_activation_flag', true );
			$post_content = '[[[{"field_key":"user_login","general_setting":{"label":"Username","field_name":"user_login","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_pass","general_setting":{"label":"User Password","field_name":"user_pass","placeholder":"","required":"yes"},"advance_setting":{}}],[{"field_key":"user_email","general_setting":{"label":"User Email","field_name":"user_email","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","field_name":"user_confirm_password","placeholder":"","required":"yes"},"advance_setting":{}}]]]';

			// Insert default form.
			$default_post_id = wp_insert_post(
				array(
					'post_type'      => 'user_registration',
					'post_title'     => esc_html__( 'Default form', 'user-registration' ),
					'post_content'   => $post_content,
					'post_status'    => 'publish',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);

			update_option( 'user_registration_default_form_page_id', $default_post_id );
		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * When adding or removing a table, make sure to update the list of tables in UR_Install::get_tables().
	 *
	 * Tables:
	 *        user_registration_sessions - Table for storing sessions data.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "
CREATE TABLE {$wpdb->prefix}user_registration_sessions (
  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY  (session_key),
  UNIQUE KEY session_id (session_id)
) $collate;
		";

		dbDelta( $sql );
	}

	/**
	 * Return a list of UserRegistration tables. Used to make sure all UR tables are dropped when uninstalling the plugin
	 * in a single site or multi site environment.
	 *
	 * @return array UR tables.
	 */
	public static function get_tables() {
		global $wpdb;

		$tables = array(
			"{$wpdb->prefix}user_registration_sessions",
		);

		return $tables;
	}

	/**
	 * Drop UsageMonitor tables.
	 */
	public static function drop_tables() {
		global $wpdb;

		$tables = self::get_tables();

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
		}
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables List of tables that will be deleted by WP.
	 * @return string[]
	 */
	public static function wpmu_drop_tables( $tables ) {
		return array_merge( $tables, self::get_tables() );
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

	/**
	 * Get capabilities for UserRegistration.
	 *
	 * @return array
	 */
	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_user_registration',
		);

		$capability_types = array( 'user_registration' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type.
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms.
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);
		}

		return $capabilities;
	}

	/**
	 * Remove UserRegistration roles.
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // phpcs:ignore
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Bypass if filesystem is read-only and/or non-standard upload system is used.
		if ( apply_filters( 'user_registration_install_skip_create_files', false ) ) {
			return;
		}

		// Install files and folders for uploading files and prevent hotlinking.
		$upload_dir = wp_upload_dir();

		$files = array(
			array(
				'base'    => UR_LOG_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => UR_LOG_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' );

				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 *
	 * @param array $args Arguments.
	 */
	public static function in_plugin_update_message( $args ) {
		$transient_name = 'ur_upgrade_notice_' . $args['Version'];
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/user-registration/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file
	 *
	 * @param  string $content Content of notice.
	 * @param  string $new_version Version of plugin.
	 *
	 * @return string
	 */
	private static function parse_update_notice( $content, $new_version ) {
		// Output Upgrade Notice.
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( UR_VERSION ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$version = trim( $matches[1] );
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			// Check the latest stable version and ignore trunk.
			if ( $version === $new_version && version_compare( UR_VERSION, $version, '<' ) ) {

				$upgrade_notice .= '<div class="ur_plugin_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
				}

				$upgrade_notice .= '</div> ';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
}

UR_Install::init();
