<?php
/**
 * Form View: Input Type User Last Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-last-name ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="last_name">

		<input type="text" id="ur-input-type-last-name" value="<?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?>" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_Last_Name::get_instance()->get_setting();

	?>
</div>

