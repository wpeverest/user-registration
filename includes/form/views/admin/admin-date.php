<?php
/**
 * Form View: Input Type Date
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-date ur-admin-template">

	<div class="ur-label">
		<label><?php echo $this->get_general_setting_data( 'label' ); ?></label>

	</div>
	<div class="ur-field" data-field-key="date">

		<input type="date" id="ur-input-type-date"
		       placeholder="<?php echo $this->get_general_setting_data( 'placeholder' ); ?>"/>

	</div>
	<?php

	UR_Date::get_instance()->get_setting();

	?>
</div>

