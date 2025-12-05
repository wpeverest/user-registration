<?php
/**
 * Base Page class for pages.
 *
 */

namespace WPEverest\URMembership\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BaseLayout {
    /**
     * Render a standard list-table page layout for a given WP_List_Table instance.
     *
     * @param \WP_List_Table $table Instance of a list table (usually extends UR_List_Table).
     * @param array          $args  Arguments to control title, add-new action, search id, and page slug.
     *                              Supported keys: 'page', 'title', 'add_new_label', 'add_new_action', 'search_id', 'skip_query_key', 'form_id'.
     *
     * @return void
     */
    public static function render_layout( $table, $args = array() ) {
        $defaults = array(
            'page'           => '',
            'title'          => '',
            'add_new_label' => esc_html__( 'Add New', 'user-registration' ),
            'add_new_action' => '',
            'search_id'      => '',
            'skip_query_key' => '',
            'form_id' => '',
        );

        $data = wp_parse_args( $args, $defaults );

        if ( ! empty( $data['skip_query_key'] ) && isset( $_GET[ $data['skip_query_key'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        if ( is_object( $table ) && method_exists( $table, 'prepare_items' ) ) {
            $table->prepare_items();
        }

        ?>
        <div id="user-registration-base-list-table-page">
			<div class="user-registration-base-list-top-wrapper">
				<div class="user-registration-base-list-table-heading">
					<h1>
						<?php echo esc_html( $data['title'] ); ?>
					</h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $data['page'] . '&action=' . $data['add_new_action'] ) ); ?>" class="page-title-action">
						<?php echo esc_html($data['add_new_label']) ?>
					</a>
				</div>
				<div id="user-registration-base-list-filters-row">

					<?php
					if ( is_object( $table ) && method_exists( $table, 'display_search_box' ) ) {
						$table->display_search_box( $data['search_id'] );
					}
					?>
				</div>
			</div>
            <form id="<?php echo esc_attr( $data['form_id'] );?>" method="get" class="user-registration-base-list-table-form">
                <input type="hidden" name="page" value="<?php echo esc_attr( $data['page'] ); ?>"/>
                <?php
                if ( is_object( $table ) && method_exists( $table, 'display' ) ) {
                    $table->display();
                }
                ?>
            </form>
        </div>
        <?php
    }

	/**
	 * Display Search Input with button
	 *
	 * @param $search_id
	 *
	 * @return void
	 */
	public static function display_search_field( $search_id, $placeholder ) {
		?>
			<input type="search" id="<?php echo $search_id; ?>" name="s"
					value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>"
					placeholder="<?php echo esc_attr($placeholder); ?> ..."
					autocomplete="off">
			<button type="submit" id="search-submit">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<path fill="#000" fill-rule="evenodd"
							d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z"
							clip-rule="evenodd"></path>
				</svg>
			</button>
		<?php

	}
}
