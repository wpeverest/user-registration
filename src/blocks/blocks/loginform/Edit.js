import React from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";

import { TextControl, PanelBody } from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const Edit = (props) => {
	const useProps = useBlockProps();
	const blockName = metadata.name;

	const {
		attributes: { redirectUrl, logoutUrl },
		setAttributes,
	} = props;

	const setRedirectUrl = (url) => {
		setAttributes({ redirectUrl: url });
	};
	const setLogoutUrl = (url) => {
		setAttributes({ logoutUrl: url });
	};

	return (
		<>
			<InspectorControls key="ur-gutenberg-login-form-inspector-controls">
				<PanelBody
					title={__("Login Form Settings", "user-registration")}
				>
					<TextControl
						key="ur-gutenberg-login-form-redirect-url"
						label={__("Redirect URL", "user-registration")}
						value={redirectUrl}
						onChange={setRedirectUrl}
					/>
					<TextControl
						key="ur-gutenberg-login-form-logout-url"
						label={__("Logout URL", "user-registration")}
						value={logoutUrl}
						onChange={setLogoutUrl}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<ServerSideRender
					key="ur-gutenberg-login-form-server-side-renderer"
					block={blockName}
					attributes={props.attributes}
				/>
			</Box>
		</>
	);
};

export default Edit;
