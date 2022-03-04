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
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
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
$redirect_url   = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_redirect_options', '' );
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

$custom_class = apply_filters( 'user_registration_form_custom_class', $custom_class, $form_id );

/**
 * Hook for Before registration form
 *
 * @since 1.5.1
 */
do_action( 'user_registration_before_registration_form', $form_id );

?>
	<div class='user-registration ur-frontend-form <?php echo esc_attr( $template_class ) . ' ' . esc_attr( $custom_class ); ?>' id='user-registration-form-<?php echo absint( $form_id ); ?>'>
		<form method='post' class='register' data-form-id="<?php echo absint( $form_id ); ?>"
			  data-enable-strength-password="<?php echo esc_attr( $enable_strong_password ); ?>" data-minimum-password-strength="<?php echo esc_attr( $minimum_password_strength ); ?>" <?php echo apply_filters( 'user_registration_form_params', '' );  //phpcs:ignore ?> data-captcha-enabled="<?php echo esc_attr( $recaptcha_enabled ); ?>">

			<?php
			do_action( 'user_registration_before_form_fields', $form_data_array, $form_id );

			foreach ( $form_data_array as $index => $data ) {
				$row_id = ( ! empty( $row_ids ) ) ? absint( $row_ids[ $index ] ) : $index;
				do_action( 'user_registration_before_field_row', $row_id, $form_data_array, $form_id );
				?>
						<div class='ur-form-row'>
						<?php
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
												$cl_enabled     = '1' === $enabled_status || 'on' === $enabled_status ? 'yes' : 'no';
												$cl_map         = '';
												$cl_props       = sprintf( 'data-conditional-logic-enabled="%s"', esc_attr( $cl_enabled ) );

												if ( 'yes' === $cl_enabled && isset( $single_item->advance_setting->cl_map ) ) {
													$cl_map   = esc_attr( $single_item->advance_setting->cl_map );
													$cl_props = sprintf( 'data-conditional-logic-enabled="%s" data-conditional-logic-map="%s"', esc_attr( $cl_enabled ), esc_attr( $cl_map ) );
												}
											}
											?>
															<div <?php echo $cl_props; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-field-id="<?php echo esc_attr( $field_id ); ?>" class="ur-field-item field-<?php echo esc_attr( $single_item->field_key ); ?> <?php echo esc_attr( ! empty( $single_item->advance_setting->custom_class ) ? $single_item->advance_setting->custom_class : '' ); ?>">
													<?php
														$frontend->user_registration_frontend_form( $single_item, $form_id );
														$is_field_exists = true;
													?>
															</div>
													<?php
										}
									}
									?>
										</div>
									<?php
						}
						?>
						</div>
				<?php
				do_action( 'user_registration_after_field_row', $row_id, $form_data_array, $form_id );
			}
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

						$submit_btn_class = apply_filters( 'user_registration_form_submit_btn_class', array(), $form_id );
						$submit_btn_class = array_merge( $submit_btn_class, (array) ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_form_submit_class' ) );
						?>
						<button type="submit" class="btn button ur-submit-button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>">
							<span></span>
							<?php
							$submit = ur_get_form_setting_by_key( $form_id, 'user_registration_form_setting_form_submit_label' );
								echo esc_html( ur_string_translation( $form_id, 'user_registration_form_setting_form_submit_label', $submit ) );
							?>
						</button>
						<?php do_action( 'user_registration_after_form_buttons', $form_id ); ?>
						<?php do_action( 'user_registration_after_submit_buttons', $form_id ); ?>
					</div>
					<?php
			}

			if ( count( $form_data_array ) == 0 ) {
				?>
						<h2><?php echo esc_html__( 'Form not found, form id :' . $form_id, 'user-registration' ); //phpcs:ignore ?></h2>
					<?php
			}
			$enable_field_icon   = ur_get_single_post_meta( $form_id, 'user_registration_enable_field_icon' );
			?>

			<div style="clear:both"></div>
			<?php if ( '1' === $enable_field_icon ) { ?>
			<input type="hidden" id="ur-form-field-icon" name="ur-field-icon" value="<?php echo esc_attr( $enable_field_icon ); ?>"/>
			<?php } ?>
			<input type="hidden" name="ur-user-form-id" value="<?php echo absint( $form_id ); ?>"/>
			<input type="hidden" name="ur-redirect-url" value="<?php echo esc_attr( ur_string_translation( $form_id, 'user_registration_form_setting_redirect_options', $redirect_url ) ); ?>"/>
			<?php wp_nonce_field( 'ur_frontend_form_id-' . $form_id, 'ur_frontend_form_nonce', false ); ?>

			<?php do_action( 'user_registration_form_registration_end', $form_id ); ?>
		</form>

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
