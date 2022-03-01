<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-login.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
 * @package UserRegistration/Templates
 * @version 1.4.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$form_template  = get_option( 'user_registration_login_options_form_template', 'default' );
$template_class = '';

if ( 'bordered' === $form_template ) {
	$template_class = 'ur-frontend-form--bordered';

} elseif ( 'flat' === $form_template ) {
	$template_class = 'ur-frontend-form--flat';

} elseif ( 'rounded' === $form_template ) {
	$template_class = 'ur-frontend-form--rounded';

} elseif ( 'rounded_edge' === $form_template ) {
	$template_class = 'ur-frontend-form--rounded ur-frontend-form--rounded-edge';
}

$labels       = array(
	'username'           => get_option( 'user_registration_label_username_or_email', __( 'Username or email address', 'user-registration' ) ),
	'password'           => get_option( 'user_registration_label_password', __( 'Password', 'user-registration' ) ),
	'remember_me'        => get_option( 'user_registration_label_remember_me', __( 'Remember me', 'user-registration' ) ),
	'login'              => get_option( 'user_registration_label_login', __( 'Login', 'user-registration' ) ),
	'lost_your_password' => get_option( 'user_registration_label_lost_your_password', __( 'Lost your password?', 'user-registration' ) ),
);
$placeholders = array(
	'username' => get_option( 'user_registration_placeholder_username_or_email', '' ),
	'password' => get_option( 'user_registration_placeholder_password', '' ),
);
$hide_labels  = 'yes' === get_option( 'user_registration_login_options_hide_labels', 'no' );

$enable_ajax = 'yes' === get_option( 'ur_login_ajax_submission', 'no' );

$enable_field_icon = 'yes' === get_option( 'user_registration_pro_general_setting_login_form', 'no' );

$login_title = 'yes' === get_option( 'user_registration_login_title', 'no' );

?>

<?php apply_filters( 'user_registration_login_form_before_notice', ur_print_notices() ); ?>

<?php do_action( 'user_registration_before_customer_login_form' ); ?>

<div class="ur-frontend-form login <?php echo esc_attr( $template_class ); ?>" id="ur-frontend-form">

	<form class="user-registration-form user-registration-form-login login" method="post">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<?php
				if ( $login_title ) {
					$login_title_label = apply_filters( 'ur_login_title', $labels['login'] );
					/* translators: %s - Login Title. */
					echo wp_kses_post( sprintf( __( '<span> %s </span>', 'user-registration' ), $login_title_label ) );
				}
				?>
					<?php do_action( 'user_registration_login_form_start' ); ?>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide">
						<?php
						if ( ! $hide_labels ) {
							printf( '<label for="username">%s <span class="required">*</span></label>', esc_html( $labels['username'] ) );
						}
						?>
						<span class="input-wrapper">
						<input placeholder="<?php echo esc_attr( $placeholders['username'] ); ?>" type="text" class="user-registration-Input user-registration-Input--text input-text" name="username" id="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['username'] ) ) ) : ''; // phpcs:ignore ?>" style="<?php echo $enable_field_icon ? "padding-left: 32px !important" : '' ?>"/>
						<?php if ( $enable_field_icon ) { ?>
						<span class="ur-icon ur-icon-user"></span>
						<?php } ?>
						</span>
					</p>
					<p class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide<?php echo ( 'yes' === get_option( 'user_registration_login_option_hide_show_password', 'no' ) ) ? ' hide_show_password' : ''; ?>">
						<?php
						if ( ! $hide_labels ) {
							printf( '<label for="password">%s <span class="required">*</span></label>', esc_html( $labels['password'] ) );
						}
						?>
						<span class="input-wrapper">
						<span class="password-input-group">
						<input placeholder="<?php echo esc_attr( $placeholders['password'] ); ?>" class="user-registration-Input user-registration-Input--text input-text" type="password" name="password" id="password" style="<?php echo $enable_field_icon ? 'padding-left: 32px !important' : ''; ?>" />

						<?php
						if ( 'yes' === get_option( 'user_registration_login_option_hide_show_password', 'no' ) ) {
							?>
						<a href="javaScript:void(0)" class="password_preview dashicons dashicons-hidden" title="<?php echo esc_attr__( 'Show password', 'user-registration' ); ?>"></a>
						</span>
							<?php
						}
						?>
						<?php if ( $enable_field_icon ) { ?>
						<span class="ur-icon ur-icon-password"></span>
						<?php } ?>
						</span>
					</p>

					<?php
					if ( ! empty( $recaptcha_node ) ) {
						echo '<div id="ur-recaptcha-node"> ' . $recaptcha_node . '</div>';  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>

					<?php do_action( 'user_registration_login_form' ); ?>

					<p class="form-row">
						<?php wp_nonce_field( 'user-registration-login', 'user-registration-login-nonce' ); ?>
						<div>
							<?php if ( $enable_ajax ) { ?>
							<input type="submit" class="user-registration-Button button ur-submit-button" id="user_registration_ajax_login_submit" name="login" value="<?php echo esc_html( $labels['login'] ); ?>" />
							<span></span>
							<?php } else { ?>
							<input type="submit" class="user-registration-Button button " name="login" value="<?php echo esc_html( $labels['login'] ); ?>" />
							<?php } ?>
						</div>
						<input type="hidden" name="redirect" value="<?php echo isset( $redirect ) ? esc_attr( $redirect ) : esc_attr( the_permalink() ); ?>" />
						<?php
							$remember_me_enabled = get_option( 'user_registration_login_options_remember_me', 'yes' );

						if ( 'yes' === $remember_me_enabled ) {
							?>
								<label class="user-registration-form__label user-registration-form__label-for-checkbox inline">
									<input class="user-registration-form__input user-registration-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php echo esc_html( $labels['remember_me'] ); ?></span>
								</label>
								<?php
						}
						?>
					</p>

					<?php
						$lost_password_enabled = get_option( 'user_registration_login_options_lost_password', 'yes' );

					if ( 'yes' === $lost_password_enabled ) {
						?>
								<p class="user-registration-LostPassword lost_password">
									<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php echo esc_html( $labels['lost_your_password'] ); ?></a>
								</p>
							<?php
					}
					?>

					<?php
					$users_can_register = get_option( 'users_can_register', 'yes' );

					if ( $users_can_register ) {
						$url_options = get_option( 'user_registration_general_setting_registration_url_options' );

						if ( ! empty( $url_options ) ) {
							echo '<p class="user-registration-register register">';
							$label = get_option( 'user_registration_general_setting_registration_label' );

							if ( ! empty( $label ) ) {
								?>
								<a href="<?php echo esc_url( get_option( 'user_registration_general_setting_registration_url_options' ) ); ?>"> <?php echo esc_html( get_option( 'user_registration_general_setting_registration_label' ) ); ?>
									</a>
								<?php
							} else {
								update_option( 'user_registration_general_setting_registration_label', __( 'Not a member yet? Register now.', 'user-registration' ) );
								?>
									<a href="<?php echo esc_url( get_option( 'user_registration_general_setting_registration_url_options' ) ); ?>"> <?php echo esc_html( get_option( 'user_registration_general_setting_registration_label' ) ); ?>
									</a>
								<?php
							}
							echo '</p>';
						}
					}
					?>
					<?php do_action( 'user_registration_login_form_end' ); ?>
			</div>
		</div>
	</form>

</div>

<?php do_action( 'user_registration_after_login_form' ); ?>
