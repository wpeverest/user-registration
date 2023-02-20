<?php
/**
 * Functionality related to the admin TinyMCE editor.
 *
 * @class    UR_Admin_Form_Modal
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Admin_Form_Modal', false ) ) :

	/**
	 * UR_Admin_Form_Modal Class.
	 */
	class UR_Admin_Form_Modal {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {

			add_action( 'media_buttons', array( $this, 'media_button' ), 15 );
		}

		/**
		 * Allow easy shortcode insertion via a custom media button.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 */
		public function media_button( $editor_id ) {

			if ( ! apply_filters( 'ur_display_media_button', is_admin(), $editor_id ) ) {
				return;
			}

			// Remove Add User Registration Form button from wp-editor in customize my account settings page.
			if ( isset( $_GET['tab'] ) && 'user-registration-customize-my-account' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			// Setup the icon - currently using a dashicon.
			$icon       = '<span class="dashicons dashicons-list-view" style="line-height:25px; font-size:16px"></span>';
			$login_icon = '<span class="dashicons dashicons-migrate" style="line-height:25px; font-size:16px"></span>';

			printf(
				'<a href="#" class="button ur-insert-form-button" data-editor="%s" title="%s">%s %s</a>',
				esc_attr( $editor_id ),
				esc_attr__( 'Add User Registration Form', 'user-registration' ),
				wp_kses_post( $icon ),
				esc_html__( 'Add Registration Form', 'user-registration' )
			);
			$smart_tags_list = UR_Emailer::smart_tags_list();
			printf( '<select id="select-smart-tags" class="button" style="color:#2271B1; border-color:#2271B1">', esc_attr( $editor_id ) );
			printf( '<option value="">%s</option>', esc_html__( 'Add Smart Tags', 'user-registration' ) );
			foreach ( $smart_tags_list as $key => $value ) {
				printf( "<option class='ur-select-smart-tag' value = '%s'> %s</option>", esc_attr( $key ), esc_html( $value ) );
			}
			echo '</select>';

			add_action( 'admin_footer', array( $this, 'shortcode_modal' ) );
		}

		/**
		 * Shortcode Modal
		 */
		public function shortcode_modal() {

			?>
				<div id="ur-modal-backdrop" style="display: none"></div>
					<div id="ur-modal-wrap" style="display: none">
						<form id="ur-modal" tabindex="-1">
							<div id="ur-modal-title">
								<?php esc_html_e( 'Insert Form', 'user-registration' ); ?>
								<button type="button" id="ur-modal-close"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'user-registration' ); ?></span></button>
							</div>
							<div id="ur-modal-inner">
								<div id="ur-modal-options">
										<?php
										$forms = ur_get_all_user_registration_form();

										if ( ! empty( $forms ) ) {
											printf( '<p><label for="ur-modal-select-form">%s</label></p>', esc_html__( 'Select a form below to insert', 'user-registration' ) );
											echo '<select id="ur-modal-select-form">';
											foreach ( $forms as $form => $form_value ) {
												printf( '<option value="%d">%s</option>', esc_attr( $form ), esc_html( $form_value ) );
											}
											echo '</select>';

										} else {
											echo '<p>';
											echo esc_html__( 'Whoops, you haven\'t created a form yet.', 'user-registration' );
											echo '</p>';
										}
										?>
								</div>
							</div>
							<div class="submitbox">
								<div id="ur-modal-cancel">
									<a class="submitdelete deletion" href="#"><?php esc_html_e( 'Cancel', 'user-registration' ); ?></a>
								</div>
								<?php if ( ! empty( $forms ) ) : ?>
								<div id="ur-modal-update">
									<button class="button button-primary" id="ur-modal-submit"><?php esc_html_e( 'Add Form', 'user-registration' ); ?></button>
								</div>
								<?php endif; ?>
							</div>
						</form>
					</div>
			<?php
		}
	}

endif;

return new UR_Admin_Form_Modal();
