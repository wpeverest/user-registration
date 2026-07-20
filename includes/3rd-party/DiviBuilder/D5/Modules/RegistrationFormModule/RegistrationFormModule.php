<?php
/**
 * DiviBuilder D5: Registration Form Module
 *
 * @package UserRegistration
 * @since   xx.xx.xx
 */

namespace WPEverest\URM\DiviBuilder\D5\Modules\RegistrationFormModule;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Native Divi 5 Registration Form module.
 *
 * @since xx.xx.xx
 */
class RegistrationFormModule implements DependencyInterface {

	/**
	 * Register this module with Divi 5.
	 *
	 * @since xx.xx.xx
	 */
	public function load(): void {
		ModuleRegistration::register_module(
			__DIR__,
			array(
				'render_callback' => array( self::class, 'render_callback' ),
			)
		);
	}

	/**
	 * Server-side render callback.
	 *
	 * @since xx.xx.xx
	 *
	 * @param array    $attrs   Block attributes.
	 * @param string   $content Inner block content (unused).
	 * @param WP_Block $block   Block instance (unused).
	 * @return string Rendered HTML.
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block ): string {
		$d5_values  = $attrs['content']['innerContent']['desktop']['value'] ?? array();
		$form_id    = absint( $d5_values['formId'] ?? 0 );
		$user_state = sanitize_text_field( $d5_values['userState'] ?? '' );

		if ( 0 === $form_id ) {
			return sprintf(
				'<div class="user-registration ur-frontend-form"><div class="user-registration-info">%s</div></div>',
				esc_html__( 'Please select the registration form.', 'user-registration' )
			);
		}

		if ( 'logged_in' === $user_state && ! is_user_logged_in() ) {
			return '';
		}

		if ( 'logged_out' === $user_state && is_user_logged_in() ) {
			return '';
		}

		if ( 'logged_in' === $user_state && is_user_logged_in() ) {
			global $wp;
			$user_id      = get_current_user_id();
			$user         = get_user_by( 'ID', $user_id );
			$current_url  = home_url( add_query_arg( array(), $wp->request ) );
			$display_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_email;
			/* translators: 1: display name link 2: logout link */
			return apply_filters(
				'ur_register_pre_form_message',
				'<p class="alert" id="ur_register_pre_form_message">' . sprintf(
					__( 'You are currently logged in as %1$1s. %2$2s', 'user-registration' ),
					'<a href="#" title="' . esc_attr( $display_name ) . '">' . esc_html( $display_name ) . '</a>',
					'<a href="' . wp_logout_url( $current_url ) . '" title="' . __( 'Log out of this account.', 'user-registration' ) . '">' . __( 'Logout', 'user-registration' ) . '  &raquo;</a>'
				) . '</p>',
				$user_id
			);
		}

		return \UR_Shortcodes::form( array( 'id' => $form_id ) );
	}
}
