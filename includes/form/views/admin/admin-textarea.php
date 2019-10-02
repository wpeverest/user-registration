<?php
/**
 * Form View: Textarea
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="ur-input-type-textarea ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="textarea">
		<textarea id="ur-input-type-textarea" disabled></textarea>
	</div>
</div>

