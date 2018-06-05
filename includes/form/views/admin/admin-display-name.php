<?php
/**
 * Form View: Input Display Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-display-name ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="display_name">
		<input type="text" value="<?php echo esc_attr( $this->get_advance_setting_data( 'default_value' )); ?>" id="ur-input-type-display-name"  placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>
	</div>

	<?php
		UR_Display_Name::get_instance()->get_setting();
	?>
</div>

