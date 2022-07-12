<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/dashboard.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<h2>
	<?php
	/* translators: %s - Users display name. */
	echo sprintf( esc_html__( 'Welcome, %1$s', 'user-registration' ), esc_html( $current_user->display_name ) );
	?>
</h2>

<div class="user-registration-profile-header">
	<div class="user-registration-img-container">
		<?php
			$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
			$profile_picture_url = get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true );

		if ( is_numeric( $profile_picture_url ) ) {
			$profile_picture_url  = wp_get_attachment_url( $profile_picture_url );
		}
			$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;

		if ( 'no' === get_option( 'user_registration_disable_profile_picture', 'no' ) ) {

			?>
					<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>">
				<?php } ?>

	</div>
	<header>
		<?php
		$first_name = ucfirst( get_user_meta( get_current_user_id(), 'first_name', true ) );
		$last_name  = ucfirst( get_user_meta( get_current_user_id(), 'last_name', true ) );
		$full_name  = $first_name . ' ' . $last_name;
		if ( empty( $first_name ) && empty( $last_name ) ) {
			$full_name = $current_user->display_name;
		}
		?>
		<h3>
		<?php
			echo esc_html( $full_name );
		?>
			</h3>
		<span class="user-registration-nick-name">
			<?php
				echo esc_html( $current_user->display_name );
			?>
		</span>
	</header>
</div>

<p>
<?php
	/* translators: 1 profile details url, 2: change password url */
	echo wp_kses_post( sprintf( __( 'From your account dashboard you can edit your <a href="%1$s"> profile details</a> and <a href="%2$s">edit your password</a>.', 'user-registration' ), esc_url( ur_get_endpoint_url( 'edit-profile' ) ), esc_url( ur_get_endpoint_url( 'edit-password' ) ) ) );
?>
</p>

<p>
	<?php
		/* translators: 1: user display name 2: logout url */
		echo wp_kses_post( sprintf( __( 'Not %1$s? <a href="%2$s">Sign out</a>', 'user-registration' ), '<strong>' . esc_html( $current_user->display_name ) . '</strong>', esc_url( ur_logout_url( ur_get_page_permalink( 'myaccount' ) ) ) ) );
	?>
</p>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'user_registration_account_dashboard' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
