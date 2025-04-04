import React, { Component } from "react";

class EditPassword extends Component {
	static slug = "urm-edit-password";
	render() {
		if (this.props.__render_edit_password) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_edit_password
					}}
				></div>
			);
		}
		return null;
	}
}

export default EditPassword;
