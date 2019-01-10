<?php
/**
 * Form View: Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$options = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : array();

?>
<div class="ur-input-type-select ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="select">
		<select id="ur-input-type-select">
			<?php
			foreach ( $options as $option ) {
				echo "<option value='" . esc_attr( trim( $option ) ) . "'>" . esc_html( trim( $option ) ) . '</option>';
			}
			?>
		</select>
	</div>

	<?php
		UR_Form_Field_Select::get_instance()->get_setting();
	?>
</div>

