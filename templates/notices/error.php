<?php
/**
 * Show error messages
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-login.php.
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

if ( ! $messages ) {
	return;
}
?>
<ul class="user-registration-error">
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="35"
		height="35"
		viewBox="0 0 35 35"
		fill="none"
	>
		<g clip-path="url(#clip0_8269_857)">
			<path
				d="M10.4979 24.6391C14.4408 28.5063 20.7721 28.445 24.6394 24.5022C28.5066 20.5593 28.4453 14.2279 24.5025 10.3607C20.5596 6.49343 14.2283 6.55472 10.361 10.4976C6.49374 14.4404 6.55503 20.7718 10.4979 24.6391Z"
				stroke="#F25656"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M20.3008 14.6445L14.699 20.3559"
				stroke="#F25656"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M14.6445 14.6992L20.3559 20.301"
				stroke="#F25656"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
		</g>
		<defs>
			<clipPath id="clip0_8269_857">
				<rect
					width="24"
					height="24"
					fill="white"
					transform="translate(17.3359 0.530273) rotate(44.4454)"
				/>
			</clipPath>
		</defs>
	</svg>
	<?php foreach ( $messages as $message ) : ?>
		<li><?php echo wp_kses_post( $message ); ?></li>
	<?php endforeach; ?>
</ul>
