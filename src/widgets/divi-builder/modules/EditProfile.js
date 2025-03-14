import React, { Component } from "react";

class EditProfile extends Component {
	static slug = "urm-edit-profile";
	render() {
		if (this.props.__render_edit_profile) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_edit_profile
					}}
				></div>
			);
		}
		return null;
	}
}

export default EditProfile;
