<?php
/**
 * Form View: Input Type Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-checkbox ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="checkbox">

		<input type="checkbox" id="ur-input-type-checkbox"
		       placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_Checkbox::get_instance()->get_setting();

	?>
</div>

