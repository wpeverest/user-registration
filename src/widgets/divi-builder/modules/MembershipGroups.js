import React, { Component } from "react";

class MembershipGroups extends Component {
	static slug = "urm-membership-groups";
	render() {
		if (this.props.__render_memebership_groups) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_memebership_groups
					}}
				></div>
			);
		}
		return null;
	}
}

export default MembershipGroups;
