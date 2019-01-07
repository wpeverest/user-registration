<?php
/**
 * Form View: Input Type User URL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-user-url ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="user_url">
		<input type="text" id="ur-input-type-user-url" placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>" disabled/>
	</div>

	<?php
		UR_Form_Field_User_Url::get_instance()->get_setting();
	?>
</div>

