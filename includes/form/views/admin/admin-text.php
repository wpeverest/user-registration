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
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="text">
		<input type="text" id="ur-input-type-text" value="<?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?>" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>
	</div>

	<?php
		UR_Text::get_instance()->get_setting();
	?>
</div>

