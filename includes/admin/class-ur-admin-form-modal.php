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
		 * Store editor settings for button visibility control.
		 *
		 * @var array
		 */
		private $editor_settings = array();

		/**
		 * Primary class constructor.
		 */
		public function __construct() {

			add_action( 'media_buttons', array( $this, 'media_button' ), 15 );
			add_action( 'ur_smart_tags_list', array( $this, 'select_smart_tags' ), 15, 1 );
			add_filter( 'wp_editor_settings', array( $this, 'capture_editor_settings' ), 10, 2 );
		}

		/**
		 * Allow easy shortcode insertion via a custom media button.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 */
		public function media_button( $editor_id ) {

			/**
			 * Filter the Display Media button
			 *
			 * @param boolean
			 * @param int $editor_id Editor ID
			 */
			if ( ! apply_filters( 'ur_display_media_button', is_admin(), $editor_id ) ) {
				return;
			}

			// Show Reset Content button if enabled for this editor (independent of other buttons)
			if ( $this->should_show_reset_content_button( $editor_id ) ) {
				printf(
					'<a href="#" class="button ur-reset-content-button" data-editor="%s" title="%s">%s</a>',
					esc_attr( $editor_id ),
					esc_attr__( 'Reset email content', 'user-registration' ),
					esc_html__( 'Reset Content', 'user-registration' )
				);
			}

			// Check if the Add Registration button should be shown
			if ( ! $this->should_show_registration_button( $editor_id ) ) {
				// Still show smart tags if enabled
				if ( $this->should_show_smart_tags_button( $editor_id ) ) {
					do_action( 'ur_smart_tags_list', $editor_id );
				}
				return;
			}

			// Remove Add User Registration Form button from wp-editor in customize my account settings page.
			if ( isset( $_GET['tab'] ) && 'user-registration-customize-my-account' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Action to display Smart Tags List
				 *
				 * @param int $editor_id Editor ID
				 */
				do_action( 'ur_smart_tags_list', $editor_id );
				return;
			}

			// Setup the icon - currently using a dashicon.
			$icon       = '<span class="dashicons dashicons-list-view" style="line-height:25px; font-size:16px"></span>';
			$login_icon = '<span class="dashicons dashicons-migrate" style="line-height:25px; font-size:16px"></span>';

			printf(
				'<a href="#" class="button ur-insert-form-button" data-editor="%s" title="%s">%s %s</a>',
				esc_attr( $editor_id ),
				esc_attr__( 'Add User Registration & Membership Form', 'user-registration' ),
				wp_kses_post( $icon ),
				esc_html__( 'Add Registration Form', 'user-registration' )
			);

			/**
			 * Action Hook for Smart Tags List
			 *
			 * @param int $editor_id Editor ID
			 */
			do_action( 'ur_smart_tags_list', $editor_id );
			add_action( 'admin_footer', array( $this, 'shortcode_modal' ) );
		}

		/**
		 * Check if the Add Registration button should be shown for this editor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 * @return bool True if button should be shown, false otherwise.
		 */
		private function should_show_registration_button( $editor_id ) {
			$show_button = $this->get_editor_setting( $editor_id, 'show-ur-registration-form-button' );
			return $show_button !== false; // Show by default, hide only if explicitly set to false
		}

		/**
		 * Check if the Smart Tags button should be shown for this editor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 * @return bool True if button should be shown, false otherwise.
		 */
		private function should_show_smart_tags_button( $editor_id ) {
			$show_button = $this->get_editor_setting( $editor_id, 'show-smart-tags-button' );
			return $show_button !== false; // Show by default, hide only if explicitly set to false
		}

		/**
		 * Check if the Reset Content button should be shown for this editor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 * @return bool True if button should be shown, false otherwise.
		 */
		private function should_show_reset_content_button( $editor_id ) {
			$show_button = $this->get_editor_setting( $editor_id, 'show-reset-content-button' );
			return $show_button !== false; // Show by default, hide only if explicitly set to false
		}

		/**
		 * Capture editor settings when wp_editor is called.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $settings Editor settings.
		 * @param string $editor_id Editor ID.
		 * @return array Editor settings.
		 */
		public function capture_editor_settings( $settings, $editor_id ) {
			// Store the settings for this editor
			$this->editor_settings[ $editor_id ] = $settings;
			return $settings;
		}

		/**
		 * Get editor setting from captured settings.
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Editor ID.
		 * @param string $setting_name Setting name to retrieve.
		 * @return mixed Setting value or null if not found.
		 */
		private function get_editor_setting( $editor_id, $setting_name ) {

			if ( isset( $this->editor_settings[ $editor_id ] ) && isset( $this->editor_settings[ $editor_id ][ $setting_name ] ) ) {
				return $this->editor_settings[ $editor_id ][ $setting_name ];
			}

			return null;
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
		/**
		 * Smart tag list button
		 *
		 * @param int $editor_id  editor id.
		 */
		public function select_smart_tags( $editor_id ) {
			// Check if the Smart Tags button should be shown
			if ( ! $this->should_show_smart_tags_button( $editor_id ) ) {
				return;
			}

			// For membership access tab editor and global restriction message, only show sign_up and log_in smart tags
			if ( 'urcr-membership-action-message' === $editor_id || 'user_registration_content_restriction_message' === $editor_id ) {
				$smart_tags_list = array(
					'{{sign_up}}' => esc_html__( 'Sign Up', 'user-registration' ),
					'{{log_in}}'  => esc_html__( 'Log In', 'user-registration' ),
				);
			} else {
				$smart_tags_list = UR_Smart_Tags::smart_tags_list();
			}

			$selector  = '<a id="ur-smart-tags-selector">';
			$selector .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
			<path d="M10 3.33203L14.2 7.53203C14.3492 7.68068 14.4675 7.85731 14.5483 8.05179C14.629 8.24627 14.6706 8.45478 14.6706 8.66536C14.6706 8.87595 14.629 9.08446 14.5483 9.27894C14.4675 9.47342 14.3492 9.65005 14.2 9.7987L11.3333 12.6654" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M6.39132 3.7227C6.14133 3.47263 5.80224 3.33211 5.44865 3.33203H2.00065C1.82384 3.33203 1.65427 3.40227 1.52925 3.52729C1.40422 3.65232 1.33398 3.82189 1.33398 3.9987V7.4467C1.33406 7.80029 1.47459 8.13938 1.72465 8.38937L5.52732 12.192C5.83033 12.4931 6.24015 12.6621 6.66732 12.6621C7.09449 12.6621 7.50431 12.4931 7.80732 12.192L10.194 9.80537C10.4951 9.50236 10.6641 9.09253 10.6641 8.66537C10.6641 8.2382 10.4951 7.82837 10.194 7.52537L6.39132 3.7227Z" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M4.33333 6.66667C4.51743 6.66667 4.66667 6.51743 4.66667 6.33333C4.66667 6.14924 4.51743 6 4.33333 6C4.14924 6 4 6.14924 4 6.33333C4 6.51743 4.14924 6.66667 4.33333 6.66667Z" fill="#6B6B6B" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>';
			$selector .= esc_html__( 'Add Smart Tags', 'user-registration' );
			$selector .= '</a>';
			$selector .= '<select id="select-smart-tags" style="display: none;">';
			$selector .= '<option></option>';

			foreach ( $smart_tags_list as $key => $value ) {
				$selector .= '<option class="ur-select-smart-tag" value = "' . esc_attr( $key ) . '"> ' . esc_html( $value ) . '</option>';
			}
			$selector .= '</select>';

			echo $selector; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

endif;

return new UR_Admin_Form_Modal();
