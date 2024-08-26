<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/my-account.php.
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

if ( function_exists( 'ur_print_notices' ) ) {
	ur_print_notices();
}
/**
 * My Account navigation.
 */
do_action( 'user_registration_account_navigation' ); ?>

<div class="user-registration-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 */
		do_action( 'user_registration_account_content' );
	?>
</div>
