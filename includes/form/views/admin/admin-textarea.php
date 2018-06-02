<?php
/**
 * Form View: Textarea
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-textarea ur-admin-template">

	<div class="ur-label">

		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="textarea">

		<textarea id="ur-input-type-textarea"></textarea>

	</div>
	<?php

	UR_Form_Field_Textarea::get_instance()->get_setting();

	?>
</div>

