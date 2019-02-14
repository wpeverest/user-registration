<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-edit-password.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'user_registration_before_edit_profile_form' ); ?>

<div class="ur-frontend-form login" id="ur-frontend-form">
	<form class="user-registration-EditProfileForm edit-profile" action="" method="post">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<div class="user-registration-profile-fields">
					<?php
					$gravatar_image     = get_avatar_url( get_current_user_id(), $args = null );
					$profile_picture_id = get_user_meta( get_current_user_id(), 'profile_pic_id', true );

					if ( $profile_picture_id ) {
						$profile_image = wp_get_attachment_thumb_url( $profile_picture_id );
						$image         = $profile_image;
					} else {
						$image = $gravatar_image;
					}
					?>
					<img class="profile-preview" alt="profile-picture" src="<?php echo $image; ?>"><br/>

					<input type="hidden" name="profile-pic-id" value="<?php echo $profile_picture_id; ?>" />
					<input type="hidden" name="gravatar-image" value="<?php echo $gravatar_image; ?>" />

					<button class="button profile-pic-remove" style="<?php echo ( $profile_image !== $image ) ? 'display:none;' : ''; ?>"><?php echo __( 'Remove', 'user-registration' ); ?></php></button>
					<button class="button profile-pic-upload"><?php echo __( 'Upload Image', 'user-registration' ); ?></php></button>
					<br/>
					<span><i>You can change your profile picture on <a href="https://en.gravatar.com/"><?php _e( 'Gravatar', 'user-registration' ); ?></a></i></span>
					<?php do_action( 'user_registration_edit_profile_form_start' ); ?>
					<div class="user-registration-profile-fields__field-wrapper">

						<?php foreach ( $profile as $key => $field ) : ?>
							<?php user_registration_form_field( $key, $field, ! empty( $_POST[ $key ] ) ? ur_clean( $_POST[ $key ] ) : $field['value'] ); ?>
						<?php endforeach; ?>
					</div>
					<?php do_action( 'user_registration_edit_profile_form' ); ?>
					<p>
						<?php wp_nonce_field( 'save_profile_details' ); ?>
						<input type="submit" class="user-registration-Button button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'user-registration' ); ?>" />
						<input type="hidden" name="action" value="save_profile_details" />
					</p>
				</div>
			</div>

		</div>
	</form>
</div>

<?php do_action( 'user_registration_after_edit_profile_form' ); ?>
