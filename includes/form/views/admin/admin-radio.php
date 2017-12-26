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

			<?php
			if(count($options)<1){
				echo "<input type = 'radio'  value='1'/>";
			}
			foreach ( $options as $option ) {
				echo "<input type = 'radio'  value='" . esc_attr( $option ) . "'/>" . esc_html( $option ) . '<br>';
			}
			?>
	</div>
	<?php

	  UR_Radio::get_instance()->get_setting();

	?>
</div>

