<?php
/**
 * Form View: Input Display Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-display-name ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="user_display_name">

		<input type="text" id="ur-input-type-user-display-name"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_User_Display_Name::get_instance()->get_setting();

	?>
</div>

