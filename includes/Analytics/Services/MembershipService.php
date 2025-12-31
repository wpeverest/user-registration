<?php

namespace WPEverest\URM\Analytics\Services;

defined( 'ABSPATH' ) || exit;

class MembershipService {

	public function get_memberships_list() {
		$memberships = get_posts(
			[
				'post_type'      => 'ur_membership',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			]
		);

		return array_map(
			function ( $membership ) {
				return [
					'id'   => $membership->ID,
					'name' => $membership->post_title,
				];
			},
			$memberships
		);
	}
}
