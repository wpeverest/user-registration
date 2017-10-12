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


		<select id="ur-input-type-country">
			<?php

			foreach ( $instance->get_country() as $country_key => $country_name ) {
				?>
				<option value="<?php echo esc_attr($country_key) ?>"><?php echo esc_html($country_name); ?></option>
				<?php

			}

			?>

		</select>

	</div>
	<?php


	$instance->get_setting();

	?>
</div>

