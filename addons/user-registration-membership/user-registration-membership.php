<?php
/**
 * Plugin Name: User Registration Membership
 * Plugin URI: https://wpuserregistration.com/features/membership/
 * Description: Membership addon for user registration plugin.
 * Version: 1.0.2
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: user-registration-membership
 * Domain Path: /languages/
 * UR Pro requires at least: 4.2.0
 * UR tested up to: 4.3.4
 *
 * Copyright: © 2020 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package User_Registration_MEMBERSHIP
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf(
			/* translators: 1: composer command. 2: plugin directory */
				esc_html__( 'Your installation of the User Registration Membership plugin is incomplete. Please run %1$s within the %2$s directory.', 'user-registration-membership' ),
				'`composer install`',
				'`' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '`'
			)
		);
	}

	/**
	 * Outputs an admin notice if composer install has not been ran.
	 */
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: 1: composer command. 2: plugin directory */
						esc_html__( 'Your installation of the  User Registration Membership plugin is incomplete. Please run %1$s within the %2$s directory.', 'user-registration-membership' ),
						'<code>composer install</code>',
						'<code>' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

use WPEverest\URMembership\Admin;

if ( ! defined( 'UR_MEMBERSHIP_VERSION' ) ) {
	define( 'UR_MEMBERSHIP_VERSION', '1.0.2' );
}

// Define UR_MEMBERSHIP_PLUGIN_FILE.
if ( ! defined( 'UR_MEMBERSHIP_PLUGIN_FILE' ) ) {
	define( 'UR_MEMBERSHIP_PLUGIN_FILE', __FILE__ );
}

// Define UR_MEMBERSHIP_DIR.
if ( ! defined( 'UR_MEMBERSHIP_DIR' ) ) {
	define( 'UR_MEMBERSHIP_DIR', plugin_dir_path( __FILE__ ) );
}

// Define UR_MEMBERSHIP_DS.
if ( ! defined( 'UR_MEMBERSHIP_DS' ) ) {
	define( 'UR_MEMBERSHIP_DS', DIRECTORY_SEPARATOR );
}

// Define UR_MEMBERSHIP_URL.
if ( ! defined( 'UR_MEMBERSHIP_URL' ) ) {
	define( 'UR_MEMBERSHIP_URL', plugin_dir_url( __FILE__ ) );
}

// Define UR_MEMBERSHIP_ASSETS_URL.
if ( ! defined( 'UR_MEMBERSHIP_ASSETS_URL' ) ) {
	define( 'UR_MEMBERSHIP_ASSETS_URL', UR_MEMBERSHIP_URL . 'assets' );
}

// Define UR_MEMBERSHIP_TEMPLATE_PATH.
if ( ! defined( ' UR_MEMBERSHIP_TEMPLATE_PATH' ) ) {
	define( 'UR_MEMBERSHIP_TEMPLATE_PATH', UR_MEMBERSHIP_DIR . 'templates' );
}

/**
 * Initialization of Membership instance.
 **/
function user_registration_membership() {
	return Admin::get_instance();
}

user_registration_membership();
