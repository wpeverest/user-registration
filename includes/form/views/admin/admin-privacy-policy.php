<?php
/**
 * Form View: Input Type Date
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-privacy-policy ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="privacy_policy">
		<?php $selected = $this->get_advance_setting_data( 'default_value' ); ?>
		<input type="checkbox" id="ur-input-type-privacy-policy" value="1" <?php echo checked( $selected, 1, false );?> >
	</div>
	<?php
		UR_Privacy_Policy::get_instance()->get_setting();
	?>
</div>

