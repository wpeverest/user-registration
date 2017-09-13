<?php
/**
 * Installation related functions and actions.
 *
 * @class    UR_Install
 * @version  1.0.0
 * @package  UserRegistration/Classes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Install Class.
 */
class UR_Install {
	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'1.0.0' => array(
			'ur_update_100_db_version',
		),
	);

	/** @var object Background update class */
	private static $background_updater;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_action( 'in_plugin_update_message-user-registration/user-registration.php', array(
			__CLASS__,
			'in_plugin_update_message'
		) );
		add_filter( 'plugin_action_links_' . UR_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		include_once( 'class-ur-background-updater.php' );
		self::$background_updater = new UR_Background_Updater();
	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'user_registration_version' ) !== UR()->version ) {
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
		if ( ! empty( $_GET['do_update_user_registration'] ) ) {
			self::update();
			UR_Admin_Notices::add_notice( 'update' );
		}
		if ( ! empty( $_GET['force_update_user_registration'] ) ) {
			do_action( 'wp_ur_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=user-registration-settings' ) );
		}
		if ( ! empty( $_GET['install_user_registration_pages'] ) ) {
			self::create_pages();
			UR_Admin_Notices::remove_notice( 'install' );
		}
	}

	/**
	 * Install UR.
	 */
	public static function install() {

		global $wpdb;
		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'UR_INSTALLING' ) ) {
			define( 'UR_INSTALLING', true );
		}

		// Ensure needed classes are loaded
		include_once( dirname( __FILE__ ) . '/admin/class-ur-admin-notices.php' );

		self::create_options();
		self::create_tables();
		self::create_roles();

		// Register post types
		UR_Post_Types::register_post_types();

		// Create default form
		self::create_form();

		// Also register endpoints - this needs to be done prior to rewrite rule flush
		UR()->query->init_query_vars();
		UR()->query->add_endpoints();

		// Queue upgrades wizard
		$current_ur_version = get_option( 'user_registration_version', null );
		$current_db_version = get_option( 'user_registration_db_version', null );

		self::create_files();

		UR_Admin_Notices::remove_all_notices();

		// No versions? This is a new install :)
		if ( is_null( $current_ur_version ) && is_null( $current_db_version ) && apply_filters( 'user_registration_enable_setup_wizard', true ) ) {
			UR_Admin_Notices::add_notice( 'install' );
			set_transient( '_ur_activation_redirect', 1, 30 );

			// No page? Let user run wizard again..
		} elseif ( ! get_option( 'user_registration_myaccount_page_id' ) ) {
			UR_Admin_Notices::add_notice( 'install' );
		}

		if ( ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
			UR_Admin_Notices::add_notice( 'update' );
		} else {
			self::update_db_version();
		}

		self::update_ur_version();

		// Flush rules after install
		do_action( 'user_registration_flush_rewrite_rules' );

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

		// Trigger action
		do_action( 'user_registration_installed' );
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Install files and folders for uploading files and prevent hotlinking
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
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
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
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'user_registration_db_version' );
		$update_queued      = false;

		foreach ( self::$db_updates as $version => $update_callbacks ) {
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
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'user_registration_db_version' );
		add_option( 'user_registration_db_version', is_null( $version ) ? UR()->version : $version );
	}

	/**
	 * Create form on first install.
	 */
	public static function create_form() {
		$hasposts = get_posts( 'post_type=user_registration' );

		if ( 0 === count( $hasposts ) ) {
			$post_content = '[[[{"field_key":"user_username","general_setting":{"label":"Username","field_name":"user_username","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_password","general_setting":{"label":"User Password","field_name":"user_password","placeholder":"","required":"yes"},"advance_setting":{}}],[{"field_key":"user_email","general_setting":{"label":"User Email","field_name":"user_email","placeholder":"","required":"yes"},"advance_setting":{}},{"field_key":"user_confirm_password","general_setting":{"label":"Confirm Password","field_name":"user_confirm_password","placeholder":"","required":"yes"},"advance_setting":{}}]]]';

			// Insert default form :)
			$default_post_id = wp_insert_post( array(
				'post_type'      => 'user_registration',
				'post_title'     => __( 'Default form', 'user-registration' ),
				'post_content'   => $post_content,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			) );

			update_option( 'user_registration_default_form_page_id', $default_post_id );
		}
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {
		include_once( dirname( __FILE__ ) . '/admin/functions-ur-admin.php' );

		$pages = apply_filters( 'user_registration_create_pages', array(
			'myaccount' => array(
				'name'    => _x( 'my-account', 'Page slug', 'user-registration' ),
				'title'   => _x( 'My Account', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_my_account_shortcode_tag', 'user_registration_my_account' ) . ']',
			),
		) );

		if ( $default_form_page_id = get_option( 'user_registration_default_form_page_id' ) ) {
			$pages['registration'] = array(
				'name'    => _x( 'registration', 'Page slug', 'user-registration' ),
				'title'   => _x( 'Registration', 'Page title', 'user-registration' ),
				'content' => '[' . apply_filters( 'user_registration_form_shortcode_tag', 'user_registration_form' ) . ' id="' . $default_form_page_id . '"]',
			);
		}

		foreach ( $pages as $key => $page ) {
			ur_create_page( esc_sql( $page['name'] ), 'user_registration_' . $key . '_page_id', $page['title'], $page['content'] );
		}
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {
		// Include settings so that we can run through defaults
		include_once( dirname( __FILE__ ) . '/admin/class-ur-admin-settings.php' );

		$settings = UR_Admin_Settings::get_settings_pages();

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

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 */
	public static function in_plugin_update_message( $args ) {
		$transient_name = 'ur_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/user-registration/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
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

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

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
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

	/**
	 * Get capabilities for User Registration.
	 *
	 * @return array
	 */
	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_user_registration'
		);

		$capability_types = array( 'user_registration' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
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

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);
		}

		return $capabilities;
	}

	/**
	 * Parse update notice from readme file
	 *
	 * @param  string $content
	 * @param  string $new_version
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

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions
	 *
	 * @return array
	 */
	public static function plugin_action_links( $actions ) {
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-settings' ) . '" title="' . esc_attr( __( 'View User Registration Settings', 'user-registration' ) ) . '">' . __( 'Settings', 'user-registration' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Display row meta in the Plugins list table.
	 *
	 * @param  array  $plugin_meta
	 * @param  string $plugin_file
	 *
	 * @return array
	 */
	public static function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( $plugin_file == UR_PLUGIN_BASENAME ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'user_registration_docs_url', 'https://docs.wpeverest.com/user-registration/' ) ) . '" title="' . esc_attr( __( 'View User Registration Documentation', 'user-registration' ) ) . '">' . __( 'Docs', 'user-registration' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'user_registration_support_url', 'https://wpeverest.com/support-forum/' ) ) . '" title="' . esc_attr( __( 'Visit Free Customer Support Forum', 'user-registration' ) ) . '">' . __( 'Free Support', 'user-registration' ) . '</a>',
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}
}

UR_Install::init();
