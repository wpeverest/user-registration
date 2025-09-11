<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-edit-profile.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();
$form_id = ur_get_form_id_by_userid( $user_id );

/**
 * Deprecated in version 3.1.3. Use 'user_registration_before_edit_profile_form_data' instead.
 *
 * @deprecated 3.1.3 Use 'user_registration_before_edit_profile_form_data' instead.
 *
 * @param array array value.
 * @param string deprecated_version.
 * @param string hook_name to be used instead.
 */
do_action_deprecated( 'user_registration_before_edit_profile_form', array(), '3.1.3', 'user_registration_before_edit_profile_form_data' );

/**
 * Fires before rendering the edit profile form with additional data.
 *
 * @param int $user_id User id of the current profile being edited.
 * @param int $form_id Form id through which user registered.
 */
do_action( 'user_registration_before_edit_profile_form_data', $user_id, $form_id );

$layout = get_option( 'user_registration_my_account_layout', 'horizontal' );

if ( 'vertical' === $layout ) {
	?>
	<div class="user-registration-MyAccount-content__header">
		<h1><?php echo wp_kses_post( $endpoint_label ); ?></h1>
	</div>
	<?php
}
?>
<div class="user-registration-MyAccount-content__body">
	<div class="ur-frontend-form login ur-edit-profile" id="ur-frontend-form">
		<form class="user-registration-EditProfileForm edit-profile" action="" method="post" enctype="multipart/form-data" data-form-id="<?php echo esc_attr( $form_id ); ?>">
			<div class="ur-form-row">
				<div class="ur-form-grid">
					<div class="user-registration-profile-fields">
						<?php
						/**
						 * Fires before rendering of profile detail title.
						 */
						do_action( 'user_registration_before_profile_detail_title' );
						?>
						<h2>
						<?php
						esc_html_e(
							/**
							 * Filter to modify the profile detail title.
							 *
							 * @param string Profile detail title content.
							 * @return string modified profile detail title.
							 */
							apply_filters( 'user_registation_profile_detail_title', __( 'Profile Detail', 'user-registration' ) ) ); //PHPCS:ignore ?></h2>
						<?php

						$is_sync_profile           = ur_option_checked( 'user_registration_sync_profile_picture', false );
						$is_profile_field_disabled = ur_option_checked( 'user_registration_disable_profile_picture', false );
						$is_profile_pic_on_form    = false;
						if ( $is_sync_profile ) {
							foreach ( $form_data_array as $data ) {
								foreach ( $data as $grid_key => $grid_data ) {
									foreach ( $grid_data as $grid_data_key => $single_item ) {
										if ( isset( $single_item->field_key ) && 'profile_picture' === $single_item->field_key ) {
											$is_profile_pic_on_form = true;
										}
									}
								}
							}
						} else {
							$is_profile_pic_on_form = ! $is_profile_field_disabled;
						}
						if ( $is_profile_pic_on_form ) {
							?>
						<div class="user-registration-profile-header">
							<div class="user-registration-img-container" style="width:100%">
								<?php
								$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
								$profile_picture_url = get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true );

								if ( is_numeric( $profile_picture_url ) ) {
									$profile_picture_url = wp_get_attachment_url( $profile_picture_url );
								}

								$profile_picture_url          = apply_filters( 'user_registration_profile_picture_url', $profile_picture_url, $user_id );
								$image                        = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
								$max_size                     = wp_max_upload_size();
								$max_upload_size              = $max_size;
								$crop_picture                 = false;
								$profile_pic_args             = array();
								$edit_profile_valid_file_type = 'image/jpeg,image/gif,image/png';

								foreach ( $form_data_array as $data ) {
									foreach ( $data as $grid_key => $grid_data ) {
										foreach ( $grid_data as $grid_data_key => $single_item ) {

											if ( isset( $single_item->field_key ) && 'profile_picture' === $single_item->field_key ) {
												$profile_pic_args             = (array) $single_item->advance_setting;
												$edit_profile_valid_file_type = isset( $single_item->advance_setting->valid_file_type ) && '' !== $single_item->advance_setting->valid_file_type ? implode( ', ', $single_item->advance_setting->valid_file_type ) : $edit_profile_valid_file_type;
												$max_upload_size              = isset( $single_item->advance_setting->max_upload_size ) && '' !== $single_item->advance_setting->max_upload_size ? $single_item->advance_setting->max_upload_size : $max_size;
												$crop_picture                 = isset( $single_item->advance_setting->enable_crop_picture ) ? ur_string_to_bool( $single_item->advance_setting->enable_crop_picture ) : false;
											}
										}
									}
								}

								?>
									<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>" style='max-width:96px; max-height:96px;' >

									<p class="user-registration-tips"><?php echo esc_html__( 'Max size: ', 'user-registration' ) . esc_attr( size_format( $max_upload_size ) ); ?></p>
									</div>
									<header>
										<p class="ur-new-profile-image-message"><strong>
										<?php
										echo esc_html(
											/**
											 * Filter to modify the upload new profile image message.
											 *
											 * @param string Message content to be modified.
											 * @return string modified message.
											 */
											apply_filters( 'user_registration_upload_new_profile_image_message', esc_html__( 'Upload your new profile image.', 'user-registration' ) )
										);
										?>
											</strong></p>
										<p class="ur-profile-image-updated-message" style="display:none;"><strong> <?php echo esc_html( 'You\'ve uploaded a profile image. You can change it below.', 'user-registration' ); ?></strong></p>
										<div class="button-group">
											<?php

											if ( has_action( 'uraf_profile_picture_buttons' ) ) {
												?>
												<div class="uraf-profile-picture-upload">
													<p class="form-row " id="profile_pic_url_field" data-priority="">
														<span class="uraf-profile-picture-upload-node" style="height: 0;width: 0;margin: 0;padding: 0;float: left;border: 0;overflow: hidden;">
														<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" size="<?php echo esc_attr( $max_upload_size ); ?>" accept="<?php echo esc_attr( $edit_profile_valid_file_type ); ?>" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" data-crop-picture="<?php echo esc_attr( $crop_picture ); ?>"/>
														<?php echo '<input type="text" class="uraf-profile-picture-input input-text ur-frontend-field" name="profile_pic_url" id="profile_pic_url" value="' . get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true ) . '" />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														</span>
														<?php
														/**
														 * Fires to display buttons for user profile picture.
														 *
														 * @param array $profile_pic_args Array of buttons to be added.
														 */
														do_action( 'uraf_profile_picture_buttons', $profile_pic_args );
														?>
													</p>
													<div style="clear:both; margin-bottom: 20px"></div>
												</div>

												<?php
											} else {
												?>
												<input type="hidden" name="profile-pic-url" id="profile_pic_url" value="<?php echo esc_attr( $profile_picture_url ); ?>" />
												<input type="hidden" name="profile-default-image" value="<?php echo esc_url( $gravatar_image ); ?>" />
												<button class="button profile-pic-remove" data-attachment-id="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true ) ); ?>" style="<?php echo esc_attr( ( $gravatar_image === $image ) ? 'display:none;' : '' ); ?>"><?php echo esc_html__( 'Change', 'user-registration' ); ?></php></button>

												<button type="button" class="button user_registration_profile_picture_upload hide-if-no-js" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" ><?php echo esc_html__( 'Upload Picture', 'user-registration' ); ?></button>
												<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" accept="image/jpeg,image/gif,image/png" style="display:none" />
												<?php
											}
											?>

										</div>
										<?php
										if ( ! $profile_picture_url ) {
											?>
											<span><i><?php echo esc_html__( 'You can change your profile picture on', 'user-registration' ); ?> <a href="https://en.gravatar.com/"><?php esc_html_e( 'Gravatar', 'user-registration' ); ?></a></i></span>
										<?php } ?>
									</header>
								</div>
							<?php } ?>
						<?php
						/**
						 * Fires at the start of rendering user registration edit profile form.
						 */
						do_action( 'user_registration_edit_profile_form_start' );
						?>
						<div class="user-registration-profile-fields__field-wrapper">

							<?php
							foreach ( $form_data_array as $index => $data ) {
								$row_id = ( ! empty( $row_ids ) ) ? absint( $row_ids[ $index ] ) : $index;

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

								ob_start();
								echo '<div class="ur-form-row" data-row-id=' . $row_id . ' ' . $row_cl_props . '>';
								user_registration_edit_profile_row_template( $data, $profile );
								echo '</div>';
								$row_template = ob_get_clean();

								$row_template = apply_filters( 'user_registration_frontend_edit_profile_form_row_template', $row_template, $form_id, $profile, $row_id, $data );

								echo $row_template; // phpcs:ignore
							}
							?>

						</div>
						<?php
						do_action( 'user_registration_edit_profile_form' );
						$submit_btn_class =
						/**
						 * Filter to modify the form update button class.
						 *
						 * @param array array value.
						 * @return array form update button classes.
						 */
						apply_filters( 'user_registration_form_update_btn_class', array() );
						?>
						<p>
							<?php
							/**
							 * Filter to modify the profile update button text.
							 *
							 * @param string Text content to be modified.
							 * @return string button text.
							 */
							$submit_btn_text = apply_filters( 'user_registration_profile_update_button', __( 'Save changes', 'user-registration' ) );
							if ( ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
								?>
								<button type="submit" class="user-registration-submit-Button btn button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" ><span></span>
									<?php echo esc_html( $submit_btn_text); ?>
								</button>
								<?php
							} else {
								wp_nonce_field( 'save_profile_details' );
								?>
								<input type="submit" class="user-registration-Button button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" value="<?php echo esc_attr( $submit_btn_text); ?>"/>
								<?php
								echo apply_filters( 'user_registration_edit_profile_extra_data_div', '', $form_id ); // phpcs:ignore.
								?>
								<input type="hidden" name="action" value="save_profile_details" />
								<?php
							}
							?>
						</p>
					</div>
				</div>

			</div>
		</form>
	</div>

	<?php
	/**
	 * Fires after rendering the user registration edit profile form.
	 */
	do_action( 'user_registration_after_edit_profile_form' );
	?>
</div>
