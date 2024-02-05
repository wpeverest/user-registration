<?php
/**
 * Form View: Input Type User Username.
 *
 * @package UserRegistration/Form/Views/Admin/Username
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-input-type-user-login ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="user_login">
		<input type="text" id="ur-input-type-<?php echo esc_attr( $this->get_general_setting_data( 'field_name' ) ); ?>" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled/>
	</div>
</div>
