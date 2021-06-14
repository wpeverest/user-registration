<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract UR_Meta_Boxes Class
 *
 * Implemented by classes using the same CRUD(s) pattern.
 *
 * @version  2.6.0
 * @package  UserRegistration/Abstracts
 */
abstract class UR_Meta_Boxes {

	/**
	 * Get General Setting fields
	 *
	 * @param array $field Atrribute of fields.
	 */
	public function ur_metabox_checkbox_field( $field, $post_meta ) {

		global $thepostid, $post;

        $get_meta_data = get_post_meta($post->ID, $post_meta, $single = true);

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'ur-checkbox';
        $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

        echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

        $non_checked = '<input type="checkbox" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" >';

        $checked = '<input type="checkbox" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" checked>';

        if($get_meta_data == "on")
        {
            echo $checked;
        }
        else
        {
            echo $non_checked;
        }
	}

	public function ur_metabox_select( $field ,$post_meta) {

        wp_enqueue_script( 'select2-js' );
        wp_enqueue_script( 'custom-js' );

        wp_enqueue_style( 'select2-css' );

        global $thepostid, $post;

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select';
        $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

        $get_meta_data = get_post_meta( $post->ID, $post_meta, $single = true );

        echo '<div class="ur-metabox-select">';
        echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" >';

        foreach ( $field['options'] as $key => $value ) {
            ?>
                <option value="<?php echo esc_attr( $key ); ?>"
            <?php

                if ( is_array( $get_meta_data ) ) {
					selected( in_array( $key, $get_meta_data ), true );
                } else {
					selected( $get_meta_data, $key );
                }

            ?>	>
			<?php echo $value; ?>
				</option>
            <?php
            }

        echo '</select> ';
        echo '</p>';
        echo '</div>';
    }
}
