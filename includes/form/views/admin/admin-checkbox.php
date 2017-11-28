<?php
/**
 * Form View: Input Type Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = isset( $this->admin_data->advance_setting->options ) ? explode( ',', trim( $this->admin_data->advance_setting->options, ',' ) ) : array();
?>

<div class="ur-input-type-checkbox ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="checkbox">

		<?php
			foreach ( $options as $option ) {
				echo "<input type = 'checkbox'  value='" . esc_attr($option) . "'>" . esc_html($option) . '</option><br>';

			}
		?>

	</div>
	<?php

	UR_Checkbox::get_instance()->get_setting();

	?>
</div>

