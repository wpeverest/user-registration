<?php
/**
 * Form View: Select
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

	<div class="ur-field" data-field-key="select">
		<?php $selected = $this->get_advance_setting_data( 'default_value' ); ?>
		<select id="ur-input-type-select">
			<?php
				foreach ( $options as $option ) {
					echo "<option value=" . esc_attr($option) . " " . selected( $selected, $option ) . ">" . esc_html( trim( $option ) ) . '</option>';
				}
			?>
		</select>
	</div>

	<?php
	  UR_Select::get_instance()->get_setting();
	?>
</div>

