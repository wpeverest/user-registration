<?php
/**
 * Admin View: Plugins - License form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$license_row = sanitize_title( $this->plugin_slug . '_license_row' );
$license_key = sanitize_title( $this->plugin_slug . '_license_key' );

?>
<tr id="<?php echo esc_attr( $license_row ); ?>" class="plugin-update-tr ur-license-key-row-tr active">
	<td colspan="3" class="plugin-update colspanchange">
		<?php $this->error_notices(); ?>
		<input type="checkbox" name="checked[]" value="1" checked="checked" style="display: none;">
		<div class="ur-license-key-row inline">
			<label for="<?php echo $license_key ?>"><?php _e( 'License:', 'user-registration' ); ?></label>
			<input type="text" id="<?php echo $license_key; ?>" name="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php echo esc_attr( 'XXXX-XXXX-XXXX-XXXX', 'user-registration' ); ?>" />
			<span class="description"><?php _e( 'Enter your license key and hit return. A valid key is required for updates.', 'user-registration' ); ?> <?php printf( 'Lost your key? <a href="%s">Retrieve it here</a>.', esc_url( 'https://wpeverest.com/lost-licence-key/' ) ); ?></span>
		</div>
	</td>
	<script>
		jQuery( function() {
			jQuery( 'tr#<?php echo esc_attr( $license_row ); ?>' ).prev().attr( 'id', '<?php echo sanitize_title( $this->plugin_slug ); ?>' ).addClass( 'user-registration-license-updater' );
		});
	</script>
</tr>
