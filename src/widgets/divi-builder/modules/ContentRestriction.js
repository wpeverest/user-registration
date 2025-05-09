import React, { Component } from "react";

class ContentRestriction extends Component {
	static slug = "urm-content-restriction";

	render() {

		if (this.props.__render_content_restriction) {
			return (
				<div
					dangerouslySetInnerHTML={{
						__html: this.props.__render_content_restriction
					}}
				></div>
			);
		}

		return null;
	}
}

export default ContentRestriction;
