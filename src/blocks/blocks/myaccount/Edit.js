import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";

import {
	TextControl,
	SelectControl,
	PanelBody,
	Disabled
} from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import metadata from "./block.json";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const Edit = (props) => {
	const useProps = useBlockProps();
	const blockName = metadata.name;

	const {
		attributes: { redirectUrl, logoutUrl, userState },
		setAttributes
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
			<InspectorControls key="ur-gutenberg-myaccount-inspector-controls">
				<PanelBody
					title={__("Myaccount Settings", "user-registration")}
				>
					<TextControl
						key="ur-gutenberg-myaccount-redirect-url"
						label={__("Redirect URL", "user-registration")}
						value={redirectUrl}
						onChange={setRedirectUrl}
					/>
					<TextControl
						key="ur-gutenberg-myaccount-logout-url"
						label={__("Logout URL", "user-registration")}
						value={logoutUrl}
						onChange={setLogoutUrl}
					/>
					<SelectControl
						key="ur-gutenberg-myaccount-user-login-state"
						label={__("User State", "user-registration")}
						value={userState}
						options={[
							{ label: "Logged In", value: "logged_in" },
							{ label: "Logged Out", value: "logged_out" }
						]}
						onChange={setUserState}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<Disabled>
					<ServerSideRender
						key="ur-gutenberg-myaccount-server-side-renderer"
						block={blockName}
						attributes={{ ...props.attributes, userState }}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
