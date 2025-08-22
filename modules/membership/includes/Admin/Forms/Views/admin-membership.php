<?php
/**
 * Form View: Membership Field.
 */

use WPEverest\URMembership\Admin\Services\ {
	MembershipGroupService,
	MembershipService
};

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$memberships             = array();
$membership_list_options = isset( $this->admin_data->general_setting->membership_listing_option ) && ! empty( $this->admin_data->general_setting->membership_listing_option ) ? $this->admin_data->general_setting->membership_listing_option : 'all';

if ( $membership_list_options === 'group' ) {
	$membership_group_service = new MembershipGroupService();
	$default_group            = isset( $this->field_defaults['default_group'] ) && ! empty( $this->field_defaults['default_group'] ) ? $this->field_defaults['default_group'] : 0;
	$selected_group_id        = isset( $this->admin_data->general_setting->membership_group ) && ! empty( $this->admin_data->general_setting->membership_group ) ? $this->admin_data->general_setting->membership_group : $default_group;
	$selected_group_id        = trim( $selected_group_id );
	$group_status             = false;
	if ( ! empty( $selected_group_id ) ) {
		$group        = $membership_group_service->get_membership_group_by_id( $selected_group_id );
		$content      = isset( $group['post_content'] ) ? json_decode( wp_unslash( $group['post_content'] ), true ) : array();
		$group_status = isset( $content['status'] ) ? ur_string_to_bool( $content['status'] ) : false;
		if ( $group_status ) {
			$memberships = $membership_group_service->get_group_memberships( $selected_group_id );
		}
	}
	$style = ! empty( $selected_group_id ) && $group_status && ! empty ( $memberships ) && "group" === $membership_list_options ? " style='display:none;'" : "";
} else {
	$membership_service = new MembershipService();
	$memberships        = $membership_service->list_active_memberships();
	$style              = "style='display:none;'";
}

?>
<div class="ur-input-type-select ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="membership">
		<?php

		echo "<span class='empty-urmg-label' " . $style . " > " . __( "Please select a membership group." . "</span>", "user-registration" );
		echo "<span class='urmg-loader'></span>";
		echo "<div class='urmg-container'>";
		foreach ( $memberships as $k => $option ) {
			echo "<label>
					<input type = 'radio'  value='" . esc_attr( trim( $option['ID'] ) ) . "' disabled/>
					<span class='urm-membership-title'>" . esc_html( trim( $option['title'] ) ) . "</span>
				 	<span class='ur-membership-duration'> - " . esc_html__( $option["period"], "user-registration" ) . "</span>
				 </label>";
		}
		echo "</div>"
		?>
	</div>
</div>
