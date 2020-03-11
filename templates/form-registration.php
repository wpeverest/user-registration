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
 * @author  WPEverest
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

/**
 * @var $form_data_array array
 * @var $form_id         int
 * @var $is_field_exists boolean
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
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
 * @since 1.5.1
 */
do_action( 'user_registration_before_registration_form', $form_id );

?>
	<div class='user-registration ur-frontend-form <?php echo $template_class . ' ' . $custom_class; ?>' id='user-registration-form-<?php echo absint( $form_id ); ?>'>
		<form method='post' class='register'
			  data-enable-strength-password="<?php echo $enable_strong_password; ?>" data-minimum-password-strength="<?php echo $minimum_password_strength; ?>" <?php echo apply_filters( 'user_registration_form_params', '' ); ?>>

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
											 style="width:<?php echo $width; ?>%">
									<?php
									foreach ( $grid_data as $grid_data_key => $single_item ) {

										if ( isset( $single_item->field_key ) ) {
											?>
															<div class="ur-field-item field-<?php echo esc_attr( $single_item->field_key ); ?>">
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
						echo '<div id="ur-recaptcha-node" style="width:100px;max-width: 100px;"> ' . $recaptcha_node . '</div>';
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
								echo ur_string_translation( $form_id, 'user_registration_form_setting_form_submit_label', $submit );
							?>
						</button>

						<?php do_action( 'user_registration_after_form_buttons', $form_id ); ?>
					</div>
					<?php
			}

			if ( count( $form_data_array ) == 0 ) {
				?>
						<h2><?php echo esc_html__( 'Form not found, form id :' . $form_id, 'user-registration' ); ?></h2>
					<?php
			}
			?>

			<div style="clear:both"></div>
			<input type="hidden" name="ur-user-form-id" value="<?php echo absint( $form_id ); ?>"/>
			<input type="hidden" name="ur-redirect-url" value="<?php echo $redirect_url; ?>"/>
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
