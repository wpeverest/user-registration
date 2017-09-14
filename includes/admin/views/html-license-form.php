<?php
/**
 * Admin View: Plugins - License form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$license_key = sanitize_title( $this->plugin_slug . '_license_key' );

?>
<tr class="plugin-update-tr active" id="<?php echo esc_attr( sanitize_title( $this->plugin_slug . '-license-row' ) ); ?>">
	<td colspan="3" class="plugin-update colspanchange">
		<?php $this->error_notices(); ?>
		<input type="checkbox" name="checked[]" value="1" checked="checked" style="display: none;">
		<div class="update-message inline user-registration-updater-license-key">
			<label for="<?php echo $license_key ?>"><?php _e( 'License:', 'user-registration' ); ?></label>
			<input type="text" id="<?php echo $license_key; ?>" name="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php echo esc_attr( 'XXXX-XXXX-XXXX-XXXX', 'user-registration' ); ?>" />
			<span class="description"><?php _e( 'Enter your license key and hit return. A valid key is required for updates.', 'user-registration' ); ?> <?php printf( 'Lost your key? <a href="%s">Retrieve it here</a>.', esc_url( 'https://wpeverest.com/lost-licence-key/' ) ); ?></span>
		</div>
	</td>
	<script>
		jQuery( function() {
			// jQuery( 'tr[data-slug="<?php echo sanitize_title( $this->plugin_slug ); ?>"]' ).prev().addClass( 'update' );
			jQuery( 'tr#<?php echo esc_attr( $this->plugin_slug ); ?>-license-row' ).prev().attr( 'id', '<?php echo sanitize_title( $this->plugin_slug ); ?>' ).addClass( 'update restaurantpress-updater-licensed' );
		});
	</script>
</tr>
