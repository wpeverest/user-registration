<?php
/**
 * Form View: Input Type User Nickname
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-nickname ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="user_nickname">

		<input type="text" id="ur-input-type-user-nickname"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_User_Nickname::get_instance()->get_setting();

	?>
</div>

