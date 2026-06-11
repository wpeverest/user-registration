<?php
/**
 * DiviBuilder D5: Builder
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5;

use WPEverest\URM\DiviBuilder\D5\Modules\RegistrationFormModule\RegistrationFormModule;
use WPEverest\URM\DiviBuilder\D5\Modules\LoginFormModule\LoginFormModule;
use WPEverest\URM\DiviBuilder\D5\Modules\MyAccountModule\MyAccountModule;
use WPEverest\URM\DiviBuilder\D5\Modules\EditProfileModule\EditProfileModule;
use WPEverest\URM\DiviBuilder\D5\Modules\EditPasswordModule\EditPasswordModule;
use WPEverest\URM\DiviBuilder\D5\Modules\MembershipGroupsModule\MembershipGroupsModule;
use WPEverest\URM\DiviBuilder\D5\Modules\MembershipThankYouModule\MembershipThankYouModule;
use WPEverest\URM\DiviBuilder\D5\Modules\ContentRestrictionModule\ContentRestrictionModule;
use ET\Builder\Framework\DependencyManagement\DependencyTree;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all core URM modules with the Divi 5 Visual Builder.
 *
 * Hooks into `divi_module_library_modules_dependency_tree` so modules appear
 * as native D5 modules (no "Legacy" badge) rather than shortcode-wrapped D4 modules.
 *
 * @since xx.xx.xx
 */
class Builder {

	/**
	 * Register the D5 hook.
	 *
	 * @since xx.xx.xx
	 */
	public static function init(): void {
		add_action( 'divi_module_library_modules_dependency_tree', array( self::class, 'register_modules' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) );
		// Enqueue VB JS registration after Divi's divi-module-library and
		// divi-edit-post have both loaded (store is initialized at that point).
		add_action( 'divi_visual_builder_assets_after_enqueue_app_window_scripts', array( self::class, 'enqueue_vb_scripts' ) );
		// AJAX endpoint used by the VB canvas renderer to preview module output.
		add_action( 'wp_ajax_urm_d5_preview', array( self::class, 'ajax_preview' ) );
	}

	/**
	 * Add all core URM modules to the Divi 5 dependency tree.
	 *
	 * @since xx.xx.xx
	 *
	 * @param DependencyTree $dependency_tree Divi 5 dependency tree instance.
	 */
	public static function register_modules( DependencyTree $dependency_tree ): void {
		$modules = apply_filters(
			'urm_divi5_modules',
			array(
				new RegistrationFormModule(),
				new LoginFormModule(),
				new MyAccountModule(),
				new EditProfileModule(),
				new EditPasswordModule(),
				new MembershipGroupsModule(),
				new MembershipThankYouModule(),
				// Registered for rendering only — excluded from the VB insert panel
				// (no JS registerModule call) so new users cannot add it, but existing
				// D4 pages that used urm-content-restriction continue to render correctly.
				new ContentRestrictionModule(),
			)
		);

		foreach ( $modules as $module ) {
			$dependency_tree->add_dependency( $module );
		}
	}

	/**
	 * Enqueue the JS module-registration script inside the Divi 5 VB app window.
	 *
	 * Called on `divi_visual_builder_assets_after_enqueue_app_window_scripts`, which
	 * fires after divi-module-library and divi-edit-post are already in the queue —
	 * both are guaranteed to have run before this script by the time the page loads.
	 *
	 * @since xx.xx.xx
	 */
	public static function enqueue_vb_scripts(): void {
		wp_enqueue_script(
			'urm-divi-vb-modules',
			UR()->plugin_url() . '/assets/js/divi-vb-modules.js',
			array( 'divi-module-library' ),
			UR_VERSION,
			true
		);

		// Build registration form options for divi/select.
		$forms        = function_exists( 'ur_get_all_user_registration_form' ) ? ur_get_all_user_registration_form() : array();
		$form_options = array( '0' => array( 'label' => __( '-- Select Form --', 'user-registration' ) ) );
		foreach ( $forms as $id => $label ) {
			$form_options[ (string) $id ] = array( 'label' => $label );
		}

		$vb_data = array(
			'forms'        => $form_options,
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'previewNonce' => wp_create_nonce( 'urm_d5_preview' ),
		);

		// Allow pro plugin to append its own select data (e.g. membership groups).
		$vb_data = apply_filters( 'urm_divi_vb_modules_data', $vb_data );

		wp_localize_script( 'urm-divi-vb-modules', 'urmDiviVbData', $vb_data );

		$pro_vb_script = apply_filters( 'urm_divi_vb_modules_script', '' );
		if ( $pro_vb_script ) {
			wp_enqueue_script(
				'urm-divi-vb-modules-pro',
				$pro_vb_script,
				array( 'urm-divi-vb-modules' ),
				UR_VERSION,
				true
			);
		}
	}

	/**
	 * Returns the block-name → render_callback mapping used by the preview endpoint.
	 *
	 * Pro plugin extends this list via the `urm_d5_preview_callbacks` filter.
	 *
	 * @since xx.xx.xx
	 * @return array<string, callable>
	 */
	private static function get_preview_callbacks(): array {
		return array(
			'urm/registration-form'    => array( RegistrationFormModule::class, 'render_callback' ),
			'urm/login-form'           => array( LoginFormModule::class, 'render_callback' ),
			'urm/myaccount'            => array( MyAccountModule::class, 'render_callback' ),
			'urm/edit-profile'         => array( EditProfileModule::class, 'render_callback' ),
			'urm/edit-password'        => array( EditPasswordModule::class, 'render_callback' ),
			'urm/membership-groups'    => array( MembershipGroupsModule::class, 'render_callback' ),
			'urm/membership-thank-you' => array( MembershipThankYouModule::class, 'render_callback' ),
			'urm/content-restriction'  => array( ContentRestrictionModule::class, 'render_callback' ),
		);
	}

	/**
	 * AJAX handler: render a URM D5 module for the VB canvas preview.
	 *
	 * Receives `block` (module name) and `attrs` (JSON-encoded D5 attribute tree)
	 * via POST, calls the matching render_callback, and returns the HTML.
	 *
	 * @since xx.xx.xx
	 */
	public static function ajax_preview(): void {
		if ( ! check_ajax_referer( 'urm_d5_preview', 'nonce', false ) || ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$block_name = sanitize_text_field( wp_unslash( $_POST['block'] ?? '' ) );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON payload; sanitize_text_field would corrupt it. json_decode + is_array check below validates the structure.
		$raw_attrs  = wp_unslash( $_POST['attrs'] ?? '{}' );
		$attrs      = json_decode( $raw_attrs, true );

		if ( ! is_array( $attrs ) ) {
			wp_send_json_error( 'Invalid attributes', 400 );
		}

		$callbacks = apply_filters( 'urm_d5_preview_callbacks', self::get_preview_callbacks() );

		if ( empty( $block_name ) || ! isset( $callbacks[ $block_name ] ) || ! is_callable( $callbacks[ $block_name ] ) ) {
			wp_send_json_error( 'Unknown block', 404 );
		}

		// For 'logged_out' preview, temporarily simulate a guest user so:
		//   (a) the userState visibility filter passes, and
		//   (b) the form renders its guest-facing content (the actual form fields).
		// For 'logged_in', the logged-in admin is the correct context: the form
		// naturally renders the "You are already logged in. Log out?" message.
		$user_state        = $attrs['content']['innerContent']['desktop']['value']['userState'] ?? '';
		$simulated_user_id = null;
		if ( 'logged_out' === $user_state ) {
			$simulated_user_id = get_current_user_id();
			wp_set_current_user( 0 );
		}

		$dummy_block = new \WP_Block(
			array(
				'blockName'    => $block_name,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		$html = call_user_func( $callbacks[ $block_name ], $attrs, '', $dummy_block );

		// Restore the original user after rendering.
		if ( null !== $simulated_user_id ) {
			wp_set_current_user( $simulated_user_id );
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Enqueue frontend assets required by URM D5 modules.
	 *
	 * @since xx.xx.xx
	 */
	public static function enqueue_scripts(): void {
		wp_register_style(
			'urm-form-style',
			UR()->plugin_url() . '/assets/css/user-registration.css',
			array(),
			UR()->version
		);
		wp_enqueue_style( 'urm-form-style' );

		if ( defined( 'UR_VERSION' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script(
				'user-registration-membership-frontend-script',
				UR()->plugin_url() . '/assets/js/modules/membership/frontend/user-registration-membership-frontend' . $suffix . '.js',
				array( 'jquery' ),
				UR_VERSION,
				true
			);
			wp_register_style(
				'user-registration-membership-frontend-style',
				UR()->plugin_url() . '/assets/css/modules/membership/user-registration-membership-frontend.css',
				array(),
				UR_VERSION
			);
			wp_enqueue_script( 'user-registration-membership-frontend-script' );
			wp_enqueue_style( 'user-registration-membership-frontend-style' );
		}
	}
}
