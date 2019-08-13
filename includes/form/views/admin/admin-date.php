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
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="date">
		<input type="text" id="ur-input-type-date" placeholder="<?php echo esc_attr( $this->get_advance_setting_data( 'date_format' ) ); ?>" disabled/>
	</div>

	<?php
		UR_Form_Field_Date::get_instance()->get_setting();
	?>
</div>
