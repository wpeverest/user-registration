<?php
/**
 * Form View: Input Type Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-email ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>
	<div class="ur-field" data-field-key="email">

		<input type="email" id="ur-input-type-email"/>

	</div>
	<?php

	  UR_Field_Email::get_instance()->get_setting();

		?>
</div>

