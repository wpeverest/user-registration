<?php
/**
 * Form View: Country
 *
 * @package UserRegistration/Form/Views/Admin/Country
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$instance = UR_Form_Field_Country::get_instance();
?>
<div class="ur-input-type-country ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="country">
		<select id="ur-input-type-country" disabled>
			<option>Select a country...</option>
		</select>
	</div>
</div>
