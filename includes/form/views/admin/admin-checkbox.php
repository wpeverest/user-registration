<?php
/**
 * Form View: Input Type Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
$old_options     = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : $default_options;
$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
$default_values  = isset( $this->admin_data->general_setting->default_value ) ? $this->admin_data->general_setting->default_value : array();
$options         = array_map( 'trim', $options );
?>

<div class="ur-input-type-checkbox ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="checkbox">
		<?php
		if ( count( $options ) < 1 ) {
			echo "<label><input type = 'checkbox'  value='1' disabled/></label>";
		}

		foreach ( $options as $option ) {

			$checked = in_array( $option, $default_values ) ? 'checked' : '';

			echo "<label><input type = 'checkbox'  value='" . esc_attr( trim( $option ) ) . "' " . esc_attr( $checked ) . ' disabled/>' . wp_kses_post( trim( $option ) ) . '</label>';
		}
		?>
	</div>
</div>
