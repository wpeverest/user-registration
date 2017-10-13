<?php

/**
 * Functionality related to the admin TinyMCE editor.
 *
 * @class    UR_Admin_Editor
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Admin_Editor', false ) ) :

	class UR_Admin_Editor {

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
		 * @param string $editor_id
		 */
		function media_button( $editor_id ) {

			if ( ! apply_filters( 'ur_display_media_button', is_admin(), $editor_id ) ) {
				return;
			}

			// Setup the icon - currently using a dashicon
			
			$icon = '<span class="dashicons dashicons-list-view" style="line-height:25px; font-size:16px"></span>';
			$login_icon = '<span class="dashicons dashicons-migrate" style="line-height:25px; font-size:16px"></span>';

			printf( '<a href="#" class="button ur-insert-form-button" data-editor="%s" title="%s">%s %s</a>',
				esc_attr( $editor_id ),
				esc_attr__( 'Add User Registration Form', 'user-registration' ),
				$icon,
				__( 'Add Registration Form', 'user-registration' )
			);

			add_action( 'admin_footer', array( $this, 'shortcode_modal' ) );
		}

		function shortcode_modal() {

           	?>
           		<div id="ur-modal-backdrop" style="display: none"></div>
					<div id="ur-modal-wrap" style="display: none">
						<form id="ur-modal" tabindex="-1">
							<div id="ur-modal-title">
								<?php _e( 'Insert Form', 'user-registration' ); ?>
								<button type="button" id="ur-modal-close"><span class="screen-reader-text"><?php _e( 'Close', 'user-registration' ); ?></span></button>
							</div>
							<div id="ur-modal-inner">
								<div id="ur-modal-options">
										<?php							
										$forms = ur_get_all_user_registration_form();
										
										if ( !empty( $forms ) ) {
											printf( '<p><label for="ur-modal-select-form">%s</label></p>', __( 'Select a form below to insert', 'user-registration' ) );
											echo '<select id="ur-modal-select-form">';
											foreach ( $forms as $form => $form_value) {
												printf( '<option value="%d">%s</option>', $form, esc_html( $form_value ) );
											}
											echo '</select>';
											
										} else {
											echo '<p>';
												__(printf( 'Whoops, you haven\'t created a form yet.'),'user-registration');
											echo '</p>';
										}
										?>
								</div>
							</div>
							<div class="submitbox">
								<div id="ur-modal-cancel">
									<a class="submitdelete deletion" href="#"><?php _e( 'Cancel', 'user-registration' ); ?></a>
								</div>
								<?php if ( ! empty( $forms ) ) : ?>
								<div id="ur-modal-update">
									<button class="button button-primary" id="ur-modal-submit"><?php _e( 'Add Form', 'user-registration' ); ?></button>
								</div>
								<?php endif; ?>
							</div>
						</form>
					</div>
		
           	<?php
		}
	}

endif;

return new UR_Admin_Editor();
