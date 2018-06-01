<?php
/**
 * Form View: Input Type Section Divider
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-section-divider ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="section_divider">

		<input type="text" id="ur-input-type-section-divider"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>

	</div>
	<?php

	UR_Section_Divider::get_instance()->get_setting();

	?>
</div>

