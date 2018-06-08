<?php
/**
 * Form View: Input Type Password
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ur-input-type-password ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="password">
		<input type="password" id="ur-input-type-password"/>
	</div>

	<?php
		UR_Password::get_instance()->get_setting();
	?>
</div>

