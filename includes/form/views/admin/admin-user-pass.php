<?php
/**
 * Form View: Input Type User Pass.
 *
 * @package UserRegistration/Form/Views/Admin/UserPass
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-input-type-user-pass ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?><span style="color:red">*</span></label>
	</div>
	<div class="ur-field" data-field-key="user_pass">
		<input type="password" id="ur-input-type-<?php echo esc_attr( $this->get_general_setting_data( 'field_name' ) ); ?>" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled/>
	</div>
</div>
