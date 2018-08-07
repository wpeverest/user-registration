<?php
/**
 * Form View: Input Type First Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-first-name ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="first_name">
		<input type="text" id="ur-input-type-first-name" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>
	</div>

	<?php
		UR_Form_Field_First_Name::get_instance()->get_setting();
	?>
</div>

