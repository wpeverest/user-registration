<?php
/**
 * Abstract UR_Nav_Menu_Item Class
 *
 * @since 4.2.3
 * @package  UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Nav_Menu_Item Class
 */
abstract class UR_Nav_Menu_Item {
	protected $menu_item_prefix = '';
	protected $menu_item_group = '';
	abstract protected function get_fields();
	abstract protected function get_title();
	abstract protected function get_key_identifier();
	public function __construct() {
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_fields' ), 10, 3 );
		add_filter( 'walker_nav_menu_start_el', array( $this, 'menu_item_frontend_display' ), 10, 4 );

		add_filter( 'user_registration_custom_nav_menu_items', array( $this, 'add_to_ur_nav_menu_item_list' ), 10, 1);
	}

	/**
	 * Adds a 'Login | Logout' menu item to the user registration navigation menu item list.
	 *
	 * @param array $endpoints An array of existing navigation menu items.
	 * @return array Modified array with the 'Login | Logout' menu item added.
	 */
	public function add_to_ur_nav_menu_item_list( $endpoints ) {
		$endpoints[ $this->get_key_identifier() ] = __( $this->get_title(), 'user-registration' );
		return $endpoints;
	}
	/**
	 * Renders fields for a specific menu item within a menu structure.
	 *
	 * @param int $item_id The ID of the menu item being rendered.
	 * @param object $item The menu item data.
	 * @param int $depth Depth of the menu item in the hierarchy.
	 * @param array $args Additional arguments for rendering the menu item.
	 * @return void
	 */
	public function render_fields( $item_id, $item, $depth, $args ) {
		if ( $item->post_title != $this->get_title() ) {
			return;
		}
		foreach ( $this->get_fields() as $field_id => $field_data ) {
			$meta_key = $this->menu_item_prefix . $field_id;
			if ( ur_get_single_post_meta( $item_id, $meta_key ) ) {
				$meta_value = ur_get_single_post_meta( $item_id, $meta_key );
			} elseif ( ! empty( $field_data['default'] ) ) {
				$meta_value = $field_data['default'];
			} else {
				$meta_value = '';
			}
			if( 'text' === $field_data[ 'type' ] ) {
			?>
				<p class="field-<?php echo esc_attr( $field_id ); ?> description description-wide">
					<label for="edit-menu-item-<?php echo esc_attr( $field_id ); ?>">
						<?php echo esc_html( $field_data[ 'label' ] ); ?><br />
						<input type="<?php echo esc_attr( $field_data[ 'type' ] ?? 'text' ); ?>"
							name="menu-item-<?php echo esc_attr( $field_id ); ?>[<?php echo esc_attr( $item_id ); ?>]"
							id="edit-menu-item-<?php echo esc_attr( $field_id . '-' . $item_id ); ?>"
							value="<?php echo esc_attr( $meta_value ); ?>" />
					</label>
				</p>
			<?php
			}
			elseif( 'page' === $field_data[ 'type' ] ) {
			?>
				<p class="field-<?php echo esc_attr( $field_id ); ?> description description-wide">
				<label for="edit-menu-item-<?php echo esc_attr( $field_id ); ?>">
				<?php echo esc_html( $field_data[ 'label' ] ); ?><br />
			<?php
				$pages = get_pages();
				echo '<select class="edit-menu-item-' . esc_attr( $field_id ) . 'name="menu-item-' . esc_attr( $field_id ) . '[' . esc_attr( $item_id ) . ']" ' . '>';
				$login_page_id = get_option('user_registration_login_page_id');

				foreach( $pages as $page ) {
					$selected = ($page->ID == $login_page_id) ? 'selected' : '';
					echo '<option value="' . esc_attr(ur_get_page_permalink($page)) . '" ' . esc_attr($selected)  . '>' . esc_html($page->post_title) . '</option>';
				}
				echo '</select>';
				echo '</label>';
			}
		}
		/**
		 * Fires after rendering custom fields for a specific menu item.
		 *
		 * This action hook allows developers to add, modify, or interact with
		 * the custom fields that are rendered for each menu item within a menu group.
		 *
		 * @param int $item_id The ID of the current menu item being processed.
		 * @param array $item An array of menu item data.
		 * @param int $depth Depth level of the menu item in the menu hierarchy.
		 * @param array $args Additional arguments provided to customize menu rendering.
		 * @param string $menu_group Menu group identifier for the menu item.
		 * @since 4.2.3
		 *
		 */
		do_action( 'ur_render_custom_menu_fields', $item_id, $item, $depth, $args, $this->menu_item_group );
	}

	/**
	 * Updates fields associated with a menu item.
	 *
	 * @param int $menu_id The ID of the menu.
	 * @param int $item_id The ID of the menu item.
	 * @param array $args Additional arguments for the update process.
	 * @return void
	 */
	public function update_fields( $menu_id, $item_id, $args )
	{
		foreach ($this->get_fields() as $field_id => $field_data) {
			$posted = $_POST["menu-item-$field_id"][$item_id] ?? null;
			if ($posted) {
				update_post_meta($item_id, $this->menu_item_prefix . $field_id, sanitize_text_field($posted));
			}
			do_action('ur_update_custom_menu_fields', $menu_id, $item_id, $args, $this->menu_item_group);
		}
	}

	public function menu_item_frontend_display( $item_output, $item, $depth, $args ) {
		if ( $item->post_title != $this->get_title() ) {
			return $item_output; //if not this menu item; return
		}
		if ( is_user_logged_in() ) {
			$item_url = ur_logout_url();
			$item_label = ur_get_single_post_meta( $item->ID, $this->menu_item_prefix . 'logout_label', 'Logout' );
		} else {
			$item_url = ur_get_login_url();
			$item_label = ur_get_single_post_meta( $item->ID, $this->menu_item_prefix . 'login_label', 'Login' );
		}
		$item_output = sprintf(
			"<a href='%s'>%s</a>",
			esc_url( $item_url ),
			esc_html( $item_label )
		);
		/**
		 * Filter to modify nav menu item frontend display
		 */
		return apply_filters( 'ur_menu_item_frontend_display', $item_output, $item, $depth, $args );
	}
}
