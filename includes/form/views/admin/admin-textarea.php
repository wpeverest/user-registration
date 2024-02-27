<?php
/**
 * Form View: Textarea.
 *
 * @package UserRegistration/Form/Views/Admin/TextArea
 */

error_log( print_r( $this->admin_data->advance_setting->limit_length, true ) );
error_log( print_r( $this->admin_data->advance_setting->limit_length_limit_count, true ) );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="ur-input-type-textarea ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="textarea">
		<textarea id="ur-input-type-<?php echo esc_attr( $this->get_general_setting_data( 'field_name' ) ); ?>"
			disabled></textarea>
		<div class="ur_limit_count_mode" style="text-align:right; color:#737373; margin-top:0px;">
			0/<p class="ur_limit_count" style="display:inline-block;">
				<?php
				if ( empty( $this->admin_data->advance_setting->limit_length ) ) {
					echo '500';
				} else {
					echo esc_html( $this->admin_data->advance_setting->limit_length_limit_count );
				}
				?>
			</p>
			<p class="ur_limit_mode" style="display:inline-block;">
				<?php
				if ( empty( $this->admin_data->advance_setting->limit_length ) ) {
					echo 'characters';
				} else {
					echo esc_html( $this->admin_data->advance_setting->limit_length_limit_mode );
				}
				?>
			</p>
		</div>
	</div>
</div>