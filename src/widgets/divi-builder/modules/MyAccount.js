import React, { Component } from "react";

class MyAccount extends Component {
	static slug = "urm-myaccount";
	render() {
		if (this.props.__render_myaccount) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_myaccount
					}}
				></div>
			);
		}
		return null;
	}
}

export default MyAccount;
