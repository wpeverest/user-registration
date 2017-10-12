<?php
/**
 * Form View: User Description
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-user-description ur-admin-template">

	<div class="ur-label">

		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="user_description">

		<textarea id="ur-input-type-user-description"></textarea>

	</div>
	<?php

	UR_User_Description::get_instance()->get_setting();

	?>
</div>

