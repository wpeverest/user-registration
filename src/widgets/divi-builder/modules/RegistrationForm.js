import React, { Component } from "react";

class RegistrationForm extends Component {
	static slug = "urm-registration-form";
	render() {
		if (this.props.__render_registration_form) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_registration_form
					}}
				></div>
			);
		}
		return null;
	}
}

export default RegistrationForm;
