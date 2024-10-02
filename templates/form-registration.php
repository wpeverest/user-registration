<?php
/**
 * User Registration Form
 *
 * Shows user registration form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/form-registration.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

/**
 * Template for Registration Form.
 *
 * @var $form_data_array array
 * @var $form_id         int
 * @var $is_field_exists boolean
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$frontend       = UR_Frontend::instance();
$form_template  = ur_get_form_setting_by_key( $form_id, 'user_registration_form_template', 'Default' );
$custom_class   = ur_get_form_setting_by_key( $form_id, 'user_registration_form_custom_class', '' );
$redirect_url   = ur_get_form_redirect_url( $form_id );
$template_class = '';

if ( 'Bordered' === $form_template ) {
	$template_class = 'ur-frontend-form--bordered';

} elseif ( 'Flat' === $form_template ) {
	$template_class = 'ur-frontend-form--flat';

} elseif ( 'Rounded' === $form_template ) {
	$template_class = 'ur-frontend-form--rounded';

} elseif ( 'Rounded Edge' === $form_template ) {
	$template_class = 'ur-frontend-form--rounded ur-frontend-form--rounded-edge';
}

$custom_class =
/**
 * Filter to modify the user registration form custom class.
 *
 * @param string $custom class Custom class for user registration form.
 * @param int $form_id Id of the class to add the custom class.
 *
 * @return string custom class for user registration form.
 */
apply_filters( 'user_registration_form_custom_class', $custom_class, $form_id );

require_once UR()->plugin_path() . '/includes/functions-ur-notice.php';

$notices =
/**
 * Filter to modify the user registration form notice before the rendering of form.
 *
 * @param function Function to modify the notices.
 *
 * @return function.
 */
apply_filters( 'user_registration_before_registration_form_notice', ur_print_notices() );

echo esc_html( $notices );
/**
 * Hook for Before registration form
 *
 * @since 1.5.1
 */
do_action( 'user_registration_before_registration_form', $form_id );

?>
	<div class='user-registration ur-frontend-form <?php echo esc_attr( $template_class ) . ' ' . esc_attr( $custom_class ); ?>' id='user-registration-form-<?php echo absint( $form_id ); ?>'>
		<?php
		$form_status = get_post_status( $form_id );

		$form_data = UR()->form->get_form( $form_id );

		if ( empty( $form_data ) ) {
			?>
			<div class="user-registration-info">
				<?php
				printf(
					/* translators: %s: Form Status. */
					esc_html__( 'Form not Found. Please contact your site administrator.', 'user-registration' ),
					esc_html( ucfirst( $form_status ) )
				)
				?>
			</div>
			<?php
		} elseif ( 'publish' !== get_post_status( $form_id ) ) {
			?>
			<div class="user-registration-info">
				<?php
				printf(
					/* translators: %s: Form Status. */
					esc_html__( 'The form is in %s. Please contact your site administrator.', 'user-registration' ),
					esc_html( ucfirst( $form_status ) )
				)
				?>
			</div>
			<?php
		} else {
			?>
			<form method='post' class='register' data-form-id="<?php echo absint( $form_id ); ?>"
				data-enable-strength-password="<?php echo esc_attr( $enable_strong_password ); ?>" data-minimum-password-strength="<?php echo esc_attr( $minimum_password_strength ); ?>"
															<?php
															echo /**
				 * Filter to modify the user registration form paramaters.
				 *
				 * @param string paramater for user registration form.
				 * @return string.
				 */
				apply_filters( 'user_registration_form_params', '' );  //phpcs:ignore ?> data-captcha-enabled="<?php echo esc_attr( $recaptcha_enabled ); ?>">

				<?php
				/**
				 * Action to fire before rendering form field.
				 *
				 * @param array $form_data_array Form data.
				 * @param int $form_id Form ID.
				 */
				do_action( 'user_registration_before_form_fields', $form_data_array, $form_id );

				foreach ( $form_data_array as $index => $data ) {
					$row_id = ( ! empty( $row_ids ) ) ? absint( $row_ids[ $index ] ) : $index;

					/**
					 * Action to fire before the the rendering of form field row.
					 *
					 * @param int $row_id Row ID.
					 * @param array $form_data_array Array of Form Data.
					 * @param int $form_id Form ID.
					 */
					do_action( 'user_registration_before_field_row', $row_id, $form_data_array, $form_id );

					$row_cl_props = '';

					// If the conditional logic addon is installed.
					if ( class_exists( 'UserRegistrationConditionalLogic' ) ) {
						$form_row_data = get_post_meta( $form_id, 'user_registration_form_row_data', true );
						$row_datas     = ! empty( $form_row_data ) ? json_decode( $form_row_data ) : array();
						foreach ( $row_datas as $individual_row_data ) {
							$conditional_logic_enabled = false;
							$conditional_settings      = array();

							if ( isset( $individual_row_data->row_id ) && $row_id == $individual_row_data->row_id && isset( $individual_row_data->conditional_logic_enabled ) ) {

								$row_cl_enabled = ur_string_to_bool( $individual_row_data->conditional_logic_enabled ) ? ur_string_to_bool( $individual_row_data->conditional_logic_enabled ) : '';
								$row_cl_map     = isset( $individual_row_data->cl_map ) ? $individual_row_data->cl_map : array();
								$row_cl_props   = sprintf( 'data-conditional-logic-enabled="%s" data-conditional-logic-map="%s"', esc_attr( $row_cl_enabled ), esc_attr( $row_cl_map ) );

							}
						}
					}

					$form_row_div = apply_filters( 'user_registration_frontend_form_row_start', '<div class="ur-form-row" data-row-id="' . esc_attr( $row_id ) . '" ' . $row_cl_props . ' >', $form_id, $row_id );
					echo wp_kses_post( $form_row_div );

					$width = floor( 100 / count( $data ) ) - count( $data );

					foreach ( $data as $grid_key => $grid_data ) {
						?>
						<div class="ur-form-grid ur-grid-<?php echo esc_attr( $grid_key + 1 ); ?>"
							style="width:<?php echo esc_attr( $width ); ?>%">
						<?php
						$grid_data = apply_filters( 'user_registration_handle_form_fields', $grid_data, $form_id );

						foreach ( $grid_data as $grid_data_key => $single_item ) {

							if ( isset( $single_item->field_key ) ) {
								$field_id = $single_item->general_setting->field_name;
								$cl_props = '';

								// If the conditional logic addon is installed.
								if ( class_exists( 'UserRegistrationConditionalLogic' ) ) {
									// Migrate the conditional logic to logic_map schema.
									$single_item = class_exists( 'URCL_Field_Settings' ) && method_exists( URCL_Field_Settings::class, 'migrate_to_logic_map_schema' ) ? URCL_Field_Settings::migrate_to_logic_map_schema( $single_item ) : $single_item; //phpcs:ignore

									$enabled_status = isset( $single_item->advance_setting->enable_conditional_logic ) ? $single_item->advance_setting->enable_conditional_logic : '';
									$cl_enabled     = ur_string_to_bool( $enabled_status );
									$cl_map         = '';
									$cl_props       = sprintf( 'data-conditional-logic-enabled="%s"', esc_attr( $cl_enabled ) );

									if ( $cl_enabled && isset( $single_item->advance_setting->cl_map ) ) {
										$cl_map   = esc_attr( $single_item->advance_setting->cl_map );
										$cl_props = sprintf( 'data-conditional-logic-enabled="%s" data-conditional-logic-map="%s"', esc_attr( $cl_enabled ), esc_attr( $cl_map ) );
									}
								}
								?>
								<div <?php echo $cl_props; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-field-id="<?php echo esc_attr( $field_id ); ?>" class="ur-field-item field-<?php echo esc_attr( $single_item->field_key ); ?> <?php echo esc_attr( ! empty( $single_item->advance_setting->custom_class ) ? $single_item->advance_setting->custom_class : '' ); ?>" data-ref-id="<?php echo esc_attr( $field_id ); ?>">
									<?php
									$grid_data =
									/**
									 * Filter to modify the form fields handle.
									 *
									 * @param array $grid_data Grid Data.
									 * @param int $form_id Form ID.
									 *
									 * @return array Modified form field handles.
									 */
									apply_filters( 'user_registration_handle_form_fields', $grid_data, $form_id );

									if ( isset( $single_item->field_key ) ) {
										$field_id = $single_item->general_setting->field_name;
										$cl_props = '';

										// If the conditional logic addon is installed.
										if ( class_exists( 'UserRegistrationConditionalLogic' ) ) {
											// Migrate the conditional logic to logic_map schema.
											$single_item = class_exists( 'URCL_Field_Settings' ) && method_exists( URCL_Field_Settings::class, 'migrate_to_logic_map_schema' ) ? URCL_Field_Settings::migrate_to_logic_map_schema( $single_item ) : $single_item; //phpcs:ignore

											$enabled_status = isset( $single_item->advance_setting->enable_conditional_logic ) ? $single_item->advance_setting->enable_conditional_logic : '';
											$cl_enabled     = ur_string_to_bool( $enabled_status );
											$cl_map         = '';
											$cl_props       = sprintf( 'data-conditional-logic-enabled="%s"', esc_attr( $cl_enabled ) );

											if ( $cl_enabled && isset( $single_item->advance_setting->cl_map ) ) {
												$cl_map   = esc_attr( $single_item->advance_setting->cl_map );
												$cl_props = sprintf( 'data-conditional-logic-enabled="%s" data-conditional-logic-map="%s"', esc_attr( $cl_enabled ), esc_attr( $cl_map ) );
											}
										}

												/**
												 * Action to fire before rendering of the frontend form field.
												 *
												 * @param string $single_item Single Item of frontend form field.
												 * @param int $form_id Form ID.
												 */
												do_action( 'user_registration_before_frontend_form_field', $single_item, $form_id );
												$frontend->user_registration_frontend_form( $single_item, $form_id );

												/**
												 * Action to fire after the rendering of frontend form field.
												 *
												 * @param string $single_item Single item of frontend form field.
												 * @param int $form_id Form ID.
												 */
												do_action( 'user_registration_after_frontend_form_field', $single_item, $form_id );
												$is_field_exists = true;

									}
									?>
								</div>
								<?php
							}
						}
						?>
						</div>
						<?php
					}
					$form_row_end_div = apply_filters( 'user_registration_frontend_form_row_end', '', $form_id, $row_id );
					echo $form_row_end_div;
					?>
					</div>
					<?php
					/**
					 * Action to fire after the rendering of the form field row.
					 *
					 * @param int $row_id Row ID of the form field.
					 * @param array $form_data_array Array of form data.
					 * @param int $form_id Form ID.
					 */
					do_action( 'user_registration_after_field_row', $row_id, $form_data_array, $form_id );
				}
				/**
				 * Action to fire after rendering of the form fields.
				 *
				 * @param array $form_data_aaray Array of form data.
				 * @param int $form_id Form ID.
				 */
				do_action( 'user_registration_after_form_fields', $form_data_array, $form_id );

				if ( $is_field_exists ) {
					?>
						<?php
						if ( ! empty( $recaptcha_node ) ) {
							echo '<div id="ur-recaptcha-node"> ' . $recaptcha_node . '</div>'; //phpcs:ignore
						}

						$btn_container_class = apply_filters( 'user_registration_form_btn_container_class', array(), $form_id );
						?>
						<div class="ur-button-container <?php echo esc_attr( implode( ' ', $btn_container_class ) ); ?>" >
							<?php
							do_action( 'user_registration_before_form_buttons', $form_id );

							$submit_btn_class =
							/**
							 * Filter to modify the class of form submit button.
							 *
							 * @param array Array of classes for submit button.
							 * @param int $form_id Form ID.
							 *
							 * @return array Form submit button class.
							 */
							apply_filters( 'user_registration_form_submit_btn_class', array(), $form_id );
							$condition_submit_settings = ur_maybe_unserialize( get_post_meta( $form_id, 'user_registration_submit_condition', true ) );

							$submit_btn_class = array_merge( $submit_btn_class, (array) ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_form_submit_class' ) );
							?>
							<button type="submit" class="btn button ur-submit-button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" conditional_rules="<?php echo ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_submit_conditional_logic', true ) ) ? esc_attr( wp_json_encode( $condition_submit_settings ) ) : ''; ?>">
								<span></span>
								<?php
								$submit = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_form_submit_label' );
									echo esc_html( ur_string_translation( $form_id, 'user_registration_form_setting_form_submit_label', $submit ) );
								?>
							</button>
							<?php
							/**
							 * Action to fire after rendering of form buttons.
							 *
							 * @param int $form_id Form ID.
							 */
							do_action( 'user_registration_after_form_buttons', $form_id );
							?>
							<?php
							/**
							 * Action to fire after the submit buttons.
							 *
							 * @param int $form_id Form ID.
							 */
							do_action( 'user_registration_after_submit_buttons', $form_id );
							?>
						</div>
						<?php
				}

				if ( count( $form_data_array ) == 0 ) {
					?>
							<h2><?php echo esc_html__( 'Form not found, form id :' . $form_id, 'user-registration' ); //phpcs:ignore ?></h2>
						<?php
				}
				$enable_field_icon = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_enable_field_icon' ) );
				?>

				<div style="clear:both"></div>
				<?php if ( $enable_field_icon ) { ?>
				<input type="hidden" id="ur-form-field-icon" name="ur-field-icon" value="<?php echo esc_attr( $enable_field_icon ); ?>"/>
					<?php
				}
				$current_language = ur_get_current_language();
				?>
				<input type="hidden" name="ur-registration-language" value="<?php echo esc_attr( $current_language ); ?>"/>
				<input type="hidden" name="ur-user-form-id" value="<?php echo absint( $form_id ); ?>"/>
				<input type="hidden" name="ur-redirect-url" value="<?php echo esc_url( ur_string_translation( $form_id, 'user_registration_form_setting_redirect_options', $redirect_url ) ); ?>"/>
				<?php wp_nonce_field( 'ur_frontend_form_id-' . $form_id, 'ur_frontend_form_nonce', false ); ?>

				<?php
				/**
				 * Action to fire at the end of rendering the regsitration form.
				 *
				 * @param int $form_id Form ID.
				 */
				do_action( 'user_registration_form_registration_end', $form_id );
				?>
			</form>
			<?php
		}
		?>

		<div style="clear:both"></div>
	</div>
<?php

/**
 * User registration form template.
 *
 * @since 1.0.0
 */
do_action( 'user_registration_form_registration', $form_id );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
