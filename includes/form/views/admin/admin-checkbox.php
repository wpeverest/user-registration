<?php
/**
 * Form View: Input Type Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
$stored_options  = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;

// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
$options      	 = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $stored_options;
$default_values  = isset( $this->admin_data->general_setting->default_value ) ? $this->admin_data->general_setting->default_value : array();
?>

<div class="ur-input-type-checkbox ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="checkbox">
		<?php
		if ( count( $options ) < 1 ) {
			echo "<input type = 'checkbox'  value='1' disabled/>";
		}

		foreach ( $options as $option ) {

			$checked = in_array( $option, $default_values ) ? 'checked' : '';

			echo "<input type = 'checkbox'  value='" . esc_attr( trim( $option ) ) . "' ". $checked ." disabled/>" . esc_html( trim( $option ) ) . '<br>';
		}
		?>

	</div>

	<?php
		UR_Form_Field_Checkbox::get_instance()->get_setting( $this->id );
	?>
</div>

