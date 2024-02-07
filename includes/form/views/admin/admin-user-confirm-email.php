<?php
/**
 * Form View: Input Type User Confirm_Email.
 *
 * @package UserRegistration/Form/Views/Admin/ConfirmEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-input-type-user-email ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?><span style="color:red">*</span></label>
	</div>
	<div class="ur-field" data-field-key="user_confirm_email">
		<input type="email" id="ur-input-type-user-<?php echo esc_attr( $this->get_general_setting_data( 'field_name' ) ); ?>" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled/>
	</div>
</div>
