import React, { Component } from "react";

class MembershipThankYou extends Component {
	static slug = "urm-membership-thank-you";
	render() {
		if (this.props.__render_membership_thank_you) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_membership_thank_you
					}}
				></div>
			);
		}
		return null;
	}
}

export default MembershipThankYou;
