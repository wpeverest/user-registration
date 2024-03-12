<?php
/**
 * Form View: Select.
 *
 * @package UserRegistration/Form/Views/Admin/Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Compatibility for older version. Get string value from options in advanced settings Modified since @1.5.7.
$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
$old_options     = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $default_options;
$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
$default_value   = isset( $this->admin_data->general_setting->default_value ) ? $this->admin_data->general_setting->default_value : '';
$options         = array_map( 'trim', $options );

?>
<div class="ur-input-type-select ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="select">
		<select id="ur-input-type-select" disabled>
			<?php
			foreach ( $options as $option ) {
				echo "<option value='" . esc_attr( trim( $option ) ) . "' '" . esc_attr( selected( $option, $default_value, false ) ) . "'>" . esc_html( trim( $option ) ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
