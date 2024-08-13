import React from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";

import {TextControl, SelectControl, PanelBody, Disabled} from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const Edit = (props) => {
	const useProps = useBlockProps();
	const blockName = metadata.name;

	const {
		attributes: { redirectUrl, logoutUrl, userState },
		setAttributes,
	} = props;

	const setRedirectUrl = (url) => {
		setAttributes({ redirectUrl: url });
	};
	const setLogoutUrl = (url) => {
		setAttributes({ logoutUrl: url });
	};
	const setUserState = (state) => {
		setAttributes({ userState: state });
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
					<SelectControl
						key="ur-gutenberg-login-user-login-state"
						label={__("User State", "user-registration")}
						value={userState}
						options={[
							{ label: "Logged Out", value: "logged_out" },
							{ label: "Logged In", value: "logged_in" },
						]}
						onChange={setUserState}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<Disabled>
					<ServerSideRender
						key="ur-gutenberg-login-form-server-side-renderer"
						block={blockName}
						attributes={props.attributes}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
