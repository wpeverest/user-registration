<?php
/**
 * Form View: Country
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$instance = UR_Country::get_instance();
?>

<div class="ur-input-type-country ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>
	</div>

	<div class="ur-field" data-field-key="country">
		<?php $selected = $this->get_advance_setting_data( 'default_value' ); ?>
		<select id="ur-input-type-country">
			<?php
				foreach ( $instance->get_country() as $country_key => $country_name ) {
					echo "<option value='" . esc_attr( $country_key ) . "' '"  . selected( $selected, $country_key ) . "' >" . esc_html( trim( $country_name ) ) . '</option>';
				}
			?>
		</select>
	</div>

	<?php
		$instance->get_setting();
	?>
</div>

