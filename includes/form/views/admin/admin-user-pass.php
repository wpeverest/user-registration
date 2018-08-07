<?php
/**
 * Form View: Input Type User Pass
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-pass ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?><span style="color:red">*</span></label>
	</div>

	<div class="ur-field" data-field-key="user_pass">
		<input type="password" id="ur-input-type-user-pass" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>"/>
	</div>

	<?php
		UR_Form_Field_User_Pass::get_instance()->get_setting();
	?>
</div>

