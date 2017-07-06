<?php
/**
 * Form View: Input Type Password
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-text ur-admin-template">

	<div class="ur-label">
		<label><?php echo $this->get_general_setting_data( 'label' ); ?></label>

	</div>
	<div class="ur-field" data-field-key="text">

		<input type="text" id="ur-input-type-text"
			   placeholder="<?php echo $this->get_general_setting_data( 'placeholder' ); ?>"/>

	</div>
	<?php

	  UR_Text::get_instance()->get_setting();

	?>
</div>

