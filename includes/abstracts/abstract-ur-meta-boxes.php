<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract UR_Meta_Boxes Class
 *
 * @since v2.0.0
 * @package  UserRegistration/Abstracts
 */
abstract class UR_Meta_Boxes {

	/**
	 * Renders the Checkbox field in metabox.
	 *
	 * @param array $field Metabox Field.
	 */
	public function ur_metabox_checkbox( $field ) {

		global $thepostid, $post;

		$get_meta_data = get_post_meta( $post->ID, $field['id'], true );

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'urfl-checkbox';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['desc']          = isset( $field['desc'] ) ? $field['desc'] : '';

		echo '<div class="ur-metabox-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
		echo '<div class="ur-metabox-field-row">';
		echo '<div class="ur-metabox-field-label">';
		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo ur_help_tip( $field['desc'] );
		echo '</div>';

		echo '<div class="ur-metabox-field-detail">';
		$non_checked = '<input type="checkbox" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" >';

		$checked = '<input type="checkbox" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" checked>';

		if ( 'on' === $get_meta_data ) {
			echo $checked;
		} else {
			echo $non_checked;
		}

		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders the Select field in metabox.
	 *
	 * @param array $field Metabox Field.
	 */
	public function ur_metabox_select( $field ) {

        global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

		$field['desc']          = isset( $field['desc'] ) ? $field['desc'] : '';

		$get_meta_data = get_post_meta( $post->ID, $field['id'], true );

		echo '<div class="ur-metabox-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
		echo '<div class="ur-metabox-field-row">';
		echo '<div class="ur-metabox-field-label">';
		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo ur_help_tip( $field['desc'] );
		echo '</div>';
		echo '<div class="ur-metabox-field-detail">';
		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" >';
		foreach ( $field['options'] as $key => $value ) {
			?>
				<option value="<?php echo esc_attr( $key ); ?>"
					<?php

					if ( is_array( $get_meta_data ) ) {
						selected( in_array( $key, $get_meta_data, true ), true );
					} else {
						selected( $get_meta_data, $key );
					}

					?>
					><?php echo esc_html( $value ); ?></option>
			<?php
		}

		echo '</select> ';
		echo '</div>';
		echo '</div>';
		echo '</div>';
    }

	/**
	 * Renders the Multiple Select field in metabox.
	 *
	 * @param array $field Metabox Field.
	 */
	public function ur_metabox_multiple_select( $field ) {

		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'multiple-select';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['desc']          = isset( $field['desc'] ) ? $field['desc'] : '';

		$get_meta_data = get_post_meta( $post->ID, chop( $field['id'], '[]' ), true );

		echo '<div class="ur-metabox-field ' . esc_attr( chop( $field['id'], '[]' ) ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
		echo '<div class="ur-metabox-field-row">';
		echo '<div class="ur-metabox-field-label">';
		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo ur_help_tip( $field['desc'] );
		echo '</div>';
		echo '<div class="ur-metabox-field-detail">';
		echo '<select multiple id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" >';

		foreach ( $field['options'] as $key => $value ) {
			?>
				<option value="<?php echo esc_attr( $key ); ?>"
					<?php

					if ( is_array( $get_meta_data ) ) {
						selected( in_array( $key, $get_meta_data, true ), true );
					} else {
						selected( $get_meta_data, $key );
					}

					?>
					><?php echo esc_html( $value ); ?></option>
			<?php
		}

		echo '</select> ';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders the Input field in metabox.
	 *
	 * @param array $field Metabox Field.
	 */
	public function ur_metabox_input( $field ) {

		global $thepostid, $post;

		$get_meta_data = get_post_meta( $post->ID, $field['id'], true );

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'urfl-input';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = ( isset( $get_meta_data ) && '' !== $get_meta_data ) ? $get_meta_data : $field['value'];
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['desc']          = isset( $field['desc'] ) ? $field['desc'] : '';

		echo '<div class="ur-metabox-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
		echo '<div class="ur-metabox-field-row">';
		echo '<div class="ur-metabox-field-label">';
		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo ur_help_tip( $field['desc'] );
		echo '</div>';
		echo '<div class="ur-metabox-field-detail">';
		echo '<input type="text" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" value="' . esc_attr( $field['value'] ) . '" >';
		echo '</div>';
		echo '</div>';
		echo '</div>';

	}
}
