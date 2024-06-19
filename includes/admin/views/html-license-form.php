<?php
/**
 * Admin View: Plugins - License form
 *
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$license_key = sanitize_title( $this->plugin_slug . '_license_key' );

?>
<tr class="plugin-update-tr active update" id="<?php echo esc_attr( sanitize_title( $this->plugin_slug . '-license-row' ) ); ?>">
	<td colspan="4" class="plugin-update colspanchange">
		<?php $this->user_registration_error_notices(); ?>
		<input type="checkbox" name="checked[]" value="1" checked="checked" style="display: none;">
		<div class="update-message inline user-registration-updater-license-key">
		<?php
			wp_nonce_field( '_ur_license_nonce', 'ur_license_nonce' );
		?>
			<label for="<?php echo esc_attr( $license_key ); ?>"><?php esc_html_e( 'License:', 'user-registration' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $license_key ); ?>" name="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php echo esc_attr__( 'XXXX-XXXX-XXXX-XXXX', 'user-registration' ); ?>" />
			<span class="description"><?php esc_html_e( 'Enter your license key and hit return. A valid key is required for updates.', 'user-registration' ); ?> <?php printf( 'Lost your key? <a href="%s">Retrieve it here</a>.', esc_url( 'https://wpeverest.com/my-account' ) ); ?></span>
		</div>
	</td>
	<script>
		jQuery( function() {
			jQuery( 'tr#<?php echo esc_attr( $this->plugin_slug ); ?>-license-row' ).prev().attr( 'id', '<?php echo esc_attr( sanitize_title( $this->plugin_slug ) ); ?>' ).addClass( 'update user-registration-updater-licensed' );
		});
	</script>
</tr>
