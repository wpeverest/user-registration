<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/navigation.php.
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
	exit; // Exit if accessed directly.
}

/**
 * Action to fire before the rendering of user registration account navigation.
 */
do_action( 'user_registration_before_account_navigation' );
$logout_confirmation = ur_option_checked( 'user_registration_disable_logout_confirmation', false );
?>

<nav class="user-registration-MyAccount-navigation">
	<ul>
		<?php foreach ( ur_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php
			$actual_endpoint = $endpoint;

			?>
			<li class="<?php echo esc_attr( ur_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( ur_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo 'user-logout' === $actual_endpoint && ! $logout_confirmation ? esc_attr( 'class=ur-logout' ) : ''; ?> ><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>

	</ul>
</nav>

<?php
/**
 * Action to fire after the rendering of user registration account navigation.
 */
do_action( 'user_registration_after_account_navigation' ); ?>
