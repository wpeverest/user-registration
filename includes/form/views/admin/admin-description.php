<?php
/**
 * Form View: User Description
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ur-input-type-description ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>
	<div class="ur-field" data-field-key="description">
		<textarea id="ur-input-type-description"><?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?></textarea>
	</div>

	<?php
		UR_Description::get_instance()->get_setting();
	?>
</div>

