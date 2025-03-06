import React, { Component } from "react";

class LoginForm extends Component {
	static slug = "urm-login-form";
	render() {
		if (this.props.__render_login_form) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_login_form
					}}
				></div>
			);
		}
		return null;
	}
}

export default LoginForm;
