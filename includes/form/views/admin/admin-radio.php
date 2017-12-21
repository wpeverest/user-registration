<?php
/**
 * Form View: Radio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : array();

?>
<div class="ur-input-type-select ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="radio">

		<select id="ur-input-type-radio">

			<?php
			foreach ( $options as $option ) {

				echo "<option value='" . esc_attr($option) . "'>" . esc_html($option) . '</option>';
			}
			?>
		</select>

	</div>
	<?php

	  UR_Radio::get_instance()->get_setting();

	?>
</div>

