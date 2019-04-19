<?php
/**
 * Form View: Input Type User Confirm_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-input-type-user-email ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="user_confirm_email">
		<input type="email" id="ur-input-type-user-confirm-email" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled/>
	</div>

	<?php
		UR_Form_Field_User_Confirm_Email::get_instance()->get_setting();
	?>
</div>

