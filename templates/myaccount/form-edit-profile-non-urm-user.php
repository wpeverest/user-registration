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

if (! defined('ABSPATH')) {
	exit;
}

$user           = wp_get_current_user();
$user_id        = get_current_user_id();
$endpoint_label = isset( $args['endpoint_label'] ) ? $args['endpoint_label'] : '';

$layout = get_option('user_registration_my_account_layout', 'horizontal');

if ('vertical' === $layout) {
	?>
	<div class="user-registration-MyAccount-content__header">
		<h1><?php echo wp_kses_post($endpoint_label); ?></h1>
	</div>
	<?php
}
?>
<div class="user-registration-MyAccount-content__body">
	<div class="ur-frontend-form login ur-edit-profile" id="ur-frontend-form">
	<?php if ( current_user_can( 'manage_options' ) ): ?>
				<div class="user-registration-myaccount-notice-box">
				<div class="user-registration-myaccount-notice-box--title">
					<div class="user-registration-myaccount-notice-box--title-icon">
						<span class="dashicons dashicons-info-outline notice-icon"></span>
					</div>
					<div class="user-registration-myaccount-notice-box--title-text">
						<h2><?php echo esc_html__( "Hey! Your users see a different account page", "user-registration"); ?></h2>
					</div>
				</div>
				<p><?php echo esc_html__( "What you're seeing isn't the full user experience. Users who register through your form get more profile features and a better interface.", "user-registration"); ?></p>
				<p class="pro-tip"><?php echo wp_kses_post(__( "<strong>Pro tip:</strong> Create a test user with your registration form to see how the account page really looks!", "user-registration") ); ?></p>
				<?php
					$user_args = array(
						'meta_query'  => array(
							array(
								'key'     => 'ur_form_id',
								'compare' => 'NOT EXISTS',
							),
						),
						'count_total' => true,
						'fields'      => 'ID',
					);
					$user_query = new WP_User_Query( $user_args );
					$existing_non_urm_user = $user_query->get_total();
					?>
				<?php if ( $existing_non_urm_user >= 5 ): ?>
					<p class="existing-users">
					<strong>Existing users:</strong> Your site has <span class="highlight"><?php echo $existing_non_urm_user; ?> users</span> registered before this plugin.
					Want them to enjoy the new profile features too? Use the
					<a class="addon-link" href="https://wpuserregistration.com/features/profile-connect/?utm_source=my-account&utm_medium=profile-connect-addon-link&utm_campaign=<?php echo UR()->utm_campaign ?>" rel="noreferrer noopener" target="_blank">Profile Connect addon</a> to link these existing users to your new registration form.
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<form class="user-registration-EditProfileForm edit-profile" action="" method="post" enctype="multipart/form-data">
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
						$is_profile_pic_on_form    = ! ur_option_checked( 'user_registration_disable_profile_picture', false );
						if ( $is_profile_pic_on_form ) {
							?>
						<div class="user-registration-profile-header">
							<div class="user-registration-img-container" style="width:100%">
								<?php
								$gravatar_image      = get_avatar_url( get_current_user_id(), null );
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

								?>
									<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>" style='max-width:96px; max-height:96px;' >

									<p class="user-registration-tips"><?php echo esc_html__( 'Max size: ', 'user-registration' ) . esc_attr( size_format( $max_upload_size ) ); ?></p>
									</div>
									<header>
										<p><strong>
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
										<div class="button-group">
											<input type="hidden" name="profile-pic-url" id="profile_pic_url" value="<?php echo esc_attr( $profile_picture_url ); ?>" />
											<input type="hidden" name="profile-default-image" value="<?php echo esc_url( $gravatar_image ); ?>" />
											<button class="button profile-pic-remove" data-attachment-id="<?php echo esc_attr( get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true ) ); ?>" style="<?php echo esc_attr( ( $gravatar_image === $image ) ? 'display:none;' : '' ); ?>"><?php echo esc_html__( 'Remove', 'user-registration' ); ?></php></button>

											<button type="button" class="button user_registration_profile_picture_upload hide-if-no-js" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" ><?php echo esc_html__( 'Upload Picture', 'user-registration' ); ?></button>
											<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" accept="image/jpeg,image/gif,image/png" style="display:none" />
										</div>
										<?php
										if ( ! $profile_picture_url ) {
											?>
											<span><i><?php echo esc_html__( 'You can change your profile picture on', 'user-registration' ); ?> <a href="https://en.gravatar.com/"><?php esc_html_e( 'Gravatar', 'user-registration' ); ?></a></i></span>
										<?php } ?>
									</header>
								</div>
							<?php } ?>
							<div class="user-registration-profile-fields__field-wrapper">
									<div class="ur-form-row" data-row-id="0">
										<div class="ur-form-grid ur-grid-1" style="width:48%;">
											<div class="ur-field-item field-user_login" data-field-id="user_login" data-ref-id="user_registration_user_login">
												<div class="form-row validate-required" id="user_registration_user_login_field" data-priority=""><label for="user_registration_user_login" class="ur-label"><?php _e( 'Username', 'user-registration' ); ?> <abbr class="required" title="required">*</abbr></label> <span class="input-wrapper"> <input data-rules="" data-id="user_registration_user_login" type="text" class="input-text  without_icon input-text ur-edit-profile-field  user-registration-help-tip" name="user_registration_user_login" id="user_registration_user_login" placeholder="" value="<?php echo $user->user_login; ?>" readonly="readonly" title="Username can not be changed." required="required" data-default="copaturer"> </span> </div>
											</div>
											<div class="ur-field-item field-first_name" data-field-id="first_name" data-ref-id="user_registration_first_name">
												<div class="form-row" id="user_registration_first_name_field" data-priority=""><label for="user_registration_first_name" class="ur-label"><?php _e( 'First Name', 'user-registration' ); ?></label> <span class="input-wrapper"> <input data-rules="" data-id="user_registration_first_name" type="text" class="input-text  without_icon input-text ur-edit-profile-field" name="user_registration_first_name" id="user_registration_first_name" placeholder="" value="<?php echo esc_attr($user->first_name); ?>" data-default=""> </span> </div>
											</div>
										</div>
										<div class="ur-form-grid ur-grid-2" style="width:48%;">
											<div class="ur-field-item field-user_email" data-field-id="user_email" data-ref-id="user_registration_user_email">
												<div class="form-row validate-required" id="user_registration_user_email_field" data-priority=""><label for="user_registration_user_email" class="ur-label"><?php _e( 'User Email', 'user-registration' ); ?> <abbr class="required" title="required">*</abbr></label> <span class="input-wrapper"> <input data-rules="" data-id="user_registration_user_email" type="email" class="input-text  without_icon input-email ur-edit-profile-field " name="user_registration_user_email" id="user_registration_user_email" placeholder="" value="<?php echo $user->user_email; ?>" required="required" data-default="zapoda@mailinator.com"> </span> </div>
											</div>
											<div class="ur-field-item field-last_name" data-field-id="last_name" data-ref-id="user_registration_last_name">
												<div class="form-row" id="user_registration_last_name_field" data-priority=""><label for="user_registration_last_name" class="ur-label"><?php _e( 'Last Name', 'user-registration' ); ?></label> <span class="input-wrapper"> <input data-rules="" data-id="user_registration_last_name" type="text" class="input-text  without_icon input-text ur-edit-profile-field " name="user_registration_last_name" id="user_registration_last_name" placeholder="" value="<?php echo esc_attr($user->last_name); ?>" data-default=""> </span> </div>
											</div>
										</div>
									</div>
							</div>
						<?php
						/**
						 * Fires at the start of rendering user registration edit profile form.
						 */
						do_action( 'user_registration_edit_profile_form_start' );
						?>
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
							if ( ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
								/**
								 * Filter to modify the profile update button text.
								 *
								 * @param string Text content to be modified.
								 * @return string button text.
								 */
								$submit_button_text = apply_filters( 'user_registration_profile_update_button', __( 'Save changes', 'user-registration' ) );
								?>
								<button type="submit" class="user-registration-submit-Button btn button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" ><span></span>
									<?php echo esc_html($submit_button_text);?>
								</button>
								<?php
							} else {
								wp_nonce_field( 'save_profile_details' );
								/**
								 * Filter to modify the profile update button text.
								 *
								 * @param string text content for button.
								 * @return string button text.
								 */
								$submit = apply_filters( 'user_registration_profile_update_button', __( 'Save changes', 'user-registration' ) );
								?>
								<input type="submit" class="user-registration-Button button button-primary<?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" value="<?php esc_attr_e( $submit) //PHPCS:ignore ?>"
								/>
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
	do_action('user_registration_after_edit_profile_form');
	?>
</div>
