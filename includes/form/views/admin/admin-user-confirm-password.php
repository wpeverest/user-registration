<?php
/**
 * Form View: Input Type User Confirm_Password
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-password ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="user_confirm_password">

		<input type="password" id="ur-input-type-user-confirm-password" value="<?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?>" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_User_Confirm_Password::get_instance()->get_setting();

	?>
</div>

