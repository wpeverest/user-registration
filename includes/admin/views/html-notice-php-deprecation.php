<?php
/**
 * Admin View: Notice - PHP Deprecation
 *
 * @package  UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="notice notice-warning is-dismissible" id="user-registration-php-deprecation-notice">
	<p>
		<strong><?php esc_html_e( 'Warning!', 'user-registration' ); ?></strong>
		<?php _e( "Your website is running on an outdated version of PHP ( v$php_version ) that might not be supported by <strong>User Registration</strong> plugin in future updates.", 'user-registration' ); //phpcs:ignore ?>
		</br>
		<?php
		echo esc_html__( //phpcs:ignore
			sprintf( //phpcs:ignore
				'Please update to atleast PHP v%s to ensure compatibility and security.',
				$base_version
			),
			'user-registration'
		);
		?>
		<a href="https://docs.wpeverest.com/user-registration/docs/php-version-lesser-than-7-2-is-not-supported/" target="_blank"><?php esc_html_e( 'Learn More', 'user-registration' ); ?> </a>
	</p>
</div>

<script>

	jQuery( function( $ ) {
		$(document).ready( function() {
			var notice_container = $('#user-registration-php-deprecation-notice');
			notice_container.find( '.notice-dismiss' ).on( 'click', function(e) {
				e.preventDefault();
				var data = {
					action: "user_registration_php_notice_dismiss",
				};

				$.post(ur_notice_params.ajax_url, data, function (response) {
					// Success. Do nothing. Silence is golden.
				});
			});

		});
	});
</script>
