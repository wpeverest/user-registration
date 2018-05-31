<?php
/**
 * Form View: Input Type User Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-email ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?><span style="color:red">*</span></label>

	</div>
	<div class="ur-field" data-field-key="user_email">

		<input type="email" id="ur-input-type-user-email" value="<?php echo esc_attr($this->get_general_setting_data( 'default' )); ?>"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_User_Email::get_instance()->get_setting();

	?>
</div>

