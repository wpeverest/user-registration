<?php
/**
 * Form View: Radio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
$stored_options  = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;

// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7
$options = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : $stored_options;

?>
<div class="ur-input-type-select ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="radio">
			<?php
			if ( count( $options ) < 1 ) {
				echo "<input type = 'radio'  value='1' disabled/>";
			}

			foreach ( $options as $option ) {
				echo "<input type = 'radio'  value='" . esc_attr( trim( $option ) ) . "' disabled/>" . esc_html( trim( $option ) ) . '<br>';
			}
			?>
	</div>

	<?php
	  UR_Form_Field_Radio::get_instance()->get_setting();
	?>
</div>

