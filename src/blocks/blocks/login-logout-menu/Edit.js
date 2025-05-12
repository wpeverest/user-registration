import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";

const { pages, login_page_id } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;
const Edit = (props) => {
	const blockProps = useBlockProps();

	const { attributes, setAttributes } = props;
	const { loginPage, loginLabel, logoutLabel } = attributes;

	return (
		<>
			<InspectorControls key="ur-gutenberg-login-logout-menu-inspector-controls">
				<PanelBody title="Login | Logout Menu Settings">
					<TextControl
						label={__("Login Label", "user-registration")}
						value={loginLabel}
						onChange={(value) =>
							setAttributes({ loginLabel: value })
						}
					/>
					<SelectControl
						label={__("Login Page", "user-registration")}
						options={pages}
						value={loginPage ? loginPage : login_page_id}
						onChange={(value) =>
							setAttributes({ loginPage: value })
						}
					/>
					<TextControl
						label={__("Logout Label", "user-registration")}
						value={logoutLabel}
						onChange={(value) =>
							setAttributes({ logoutLabel: value })
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<a href="#">{logoutLabel}</a>
			</div>
		</>
	);
};
export default Edit;
