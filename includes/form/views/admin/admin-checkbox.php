<?php
/**
 * Form View: Input Type Checkbox.
 *
 * @package  UserRegistration/Form/Views/Admin/Checkbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7 .
$default_values = isset( $this->admin_data->general_setting->default_value ) ? $this->admin_data->general_setting->default_value : array();
$placeholder    = UR()->plugin_url() . '/assets/images/UR-placeholder.png';
$image_class    = '';
if ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) ) {
	$image_class     = 'user-registration-image-options';
	$default_options = isset( $this->field_defaults['default_image_options'] ) ? $this->field_defaults['default_image_options'] : array();
	$options         = isset( $this->admin_data->general_setting->image_options ) ? $this->admin_data->general_setting->image_options : $default_options;
	$options         = array_map(
		function ( $option ) {
			if ( is_array( $option ) ) {
				$option['label'] = trim( $option['label'] );
			} elseif ( is_object( $option ) ) {
				$option->label = isset( $option->label ) ? trim( $option->label ) : $option->label;
			}
			return $option;
		},
		$options
	);
} else {
	$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
	$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;
	$options         = array_map( 'trim', $options );
}
?>

<div class="ur-input-type-checkbox ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field <?php esc_attr( $image_class ); ?>" data-field-key="checkbox">
		<?php
		if ( count( $options ) < 1 ) {
			echo "<label><input type = 'checkbox'  value='1' disabled/></label>";
		}

		if ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) ) {
			foreach ( $options as $option ) {
				$checked       = '';
				$checked_class = '';

				$label = is_array( $option ) ? $option['label'] : $option->label;
				$image = is_array( $option ) ? $option['image'] : $option->image;

				if ( ! empty( $option ) ) {
					$checked      = in_array( $label, $default_values ) ? 'checked' : '';
					$checkedclass = in_array( $label, $default_values ) ? 'ur-image-choice-checked' : '';

				}

				echo "<label class='" . esc_attr( $checkedclass ) . "'><span class='user-registration-image-choice'>";
				if ( ! empty( $image ) ) {
					echo "<img src='" . esc_url( $image ) . "' alt='" . esc_attr( trim( $label ) ) . "' width='200px'>";
				} else {
					echo "<img src='" . esc_url( $placeholder ) . "' alt='" . esc_attr( trim( $label ) ) . "' width='200px'>";
				}

				echo "</span><input type = 'checkbox'  value='" . esc_attr( trim( $label ) ) . "' '" . esc_attr( $checked ) . "' disabled/><span class='user-registration-image-label'>" . esc_html( trim( $label ) ) . '</span></label>';
			}
		} else {
			foreach ( $options as $option ) {
				$checked = '';

				if ( ! empty( $option ) ) {
					$checked = in_array( $option, $default_values ) ? 'checked' : '';
				}

				echo "<label><span class='user-registration-image-choice'><img src='" . esc_url( $placeholder ) . "' alt='" . esc_attr( trim( $option ) ) . "' width='200px' style='display:none'></span><input type = 'checkbox'  value='" . esc_attr( trim( $option ) ) . "' '" . esc_attr( $checked ) . "' disabled/><span class='user-registration-image-label'>" . esc_html( trim( $option ) ) . '</span></label>';
			}
		}
		?>
	</div>
</div>
