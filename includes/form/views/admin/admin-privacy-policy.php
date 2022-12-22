<?php
/**
 * Form View: Input Type Date
 *
 * @package UserRegistration/Form/Views/Admin/Date
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-input-type-privacy-policy ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?><span style="color:red">*</span></label>
	</div>
	<div class="ur-field" data-field-key="privacy_policy">
		<input type="checkbox" id="ur-input-type-<?php echo esc_attr( $this->get_general_setting_data( 'field_name' ) ); ?>" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled/>
	</div>
</div>
