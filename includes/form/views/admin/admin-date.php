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
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>
	
	<div class="ur-field" data-field-key="date">
		<input type="date" id="ur-input-type-date" value="<?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?>" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>
	</div>

	<?php
		UR_Date::get_instance()->get_setting();
	?>
</div>

