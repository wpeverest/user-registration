<?php
/**
 * Form View: Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
$stored_options  = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;

// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
$options       = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $stored_options;
$default_value = isset( $this->admin_data->general_setting->default_value ) ? $this->admin_data->general_setting->default_value : '';
echo '<pre>'; print_r( $default_value ); echo '</pre>';
?>
<div class="ur-input-type-select ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="select">
		<select id="ur-input-type-select" disabled>
			<?php
			foreach ( $options as $option ) {
				echo "<option value='" . esc_attr( trim( $option ) ) . "' '" . selected( $option, $default_value, false ) . "'>" . esc_html( trim( $option ) ) . '</option>';
			}
			?>
		</select>
	</div>


	<?php
		UR_Form_Field_Select::get_instance()->get_setting();
	?>
</div>

