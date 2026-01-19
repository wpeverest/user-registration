<?php
/**
 * Form View: Country.
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
			<option><?php echo __( 'Select a country...', 'user-registration' ); ?></option>
		</select>
	</div>
	<?php
		$inline = '';
		if ( empty( $instance->admin_data->advance_setting->enable_state ) ) {
			$inline = 'style="display: none"';
		}
		?>
	<div class="ur-state-container-wrapper" <?php echo $inline; ?> >
		<div class="ur-label">
			<label><?php echo __( 'State', 'user-registration' ); ?></label>
		</div>
		<div class="ur-field" data-field-key="country">
			<select class="ur-input-type-country" disabled>
				<option><?php echo __( 'Select state...', 'user-registration' ); ?></option>
			</select>
		</div>
	</div>
</div>
