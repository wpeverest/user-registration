<?php
/**
 * Form View: Input Type Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$choices = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : array();
?>

<div class="ur-input-type-checkbox ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="checkbox">
		<?php $selected = $this->get_advance_setting_data( 'default_value' ); ?>
		<?php
			if( count( $choices ) <= 1 ) {
				echo "<input type = 'checkbox'  value='1' " . checked( $selected, 1, false ) . ">";
			}
			else {
				foreach ( $choices as $choice ) {
					$checked = ( is_array( $selected ) && in_array( $choice, $selected ) ) ? 'checked="checked"' : '';
					echo "<input type = 'checkbox'  value=" . esc_attr( $choice ) . " ". $checked . ">" . esc_html( trim( $choice ) ) . "<br>";
				}
			}
	
		?>
	</div>
	
	<?php
		UR_Checkbox::get_instance()->get_setting();
	?>
</div>

