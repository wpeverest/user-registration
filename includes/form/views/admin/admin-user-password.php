<?php
/**
 * Form View: Input Type User Password
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-password ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?><span style="color:red">*</span></label>

	</div>
	<div class="ur-field" data-field-key="user_password">

		<input type="password" id="ur-input-type-user-password"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_User_Password::get_instance()->get_setting();

	?>
</div>

