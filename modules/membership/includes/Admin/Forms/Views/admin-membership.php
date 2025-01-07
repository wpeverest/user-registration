<?php
/**
 * Form View: Membership Field.
 */

use WPEverest\URMembership\Admin\Services\MembershipGroupService;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$membership_group_service = new MembershipGroupService();
$default_group            = isset( $this->field_defaults['default_group'] ) && ! empty( $this->field_defaults['default_group'] ) ? $this->field_defaults['default_group'] : 0;
$selected_group_id        = isset( $this->admin_data->general_setting->membership_group ) && ! empty( $this->admin_data->general_setting->membership_group ) ? $this->admin_data->general_setting->membership_group : $default_group;
$selected_group_id        = trim( $selected_group_id );
$memberships              = array();
$group_status = false;
if ( ! empty( $selected_group_id ) ) {
	$group = $membership_group_service->get_membership_group_by_id($selected_group_id);
	$content = json_decode( wp_unslash( $group['post_content'] ), true );
	$group_status = ur_string_to_bool($content['status']);
	if($group_status) {
		$memberships = $membership_group_service->get_group_memberships( $selected_group_id );
	}
}

?>
<div class="ur-input-type-select ur-admin-template">
	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>
	<div class="ur-field" data-field-key="membership">
		<?php
		$style = ! empty( $selected_group_id ) &&  $group_status && !empty ( $memberships ) ? " style='display:none;'" : "";

		echo "<span class='empty-urmg-label' " . $style . " > " . __( "Please select a membership group." . "</span>", "user-registration" );
		echo "<span class='urmg-loader'></span>";
		echo "<div class='urmg-container'>";
		foreach ( $memberships as $k => $option ) {
			echo "<label>
					<input type = 'radio'  value='" . esc_attr( trim( $option['ID'] ) ) . "' disabled/>
					<span class='user-registration-image-label'>" . esc_html( trim( $option['title'] ) ) . "</span>
				 	<span class='ur-membership-duration'> - ". esc_html__( $option["period"], "user-registration" ) ."</span>
				 </label>";
		}
		echo "</div>"
		?>
	</div>
</div>
