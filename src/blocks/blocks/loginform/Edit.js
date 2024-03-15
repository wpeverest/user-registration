import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";

import { TextControl, PanelBody } from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const Edit = (props) => {
	const useProps = useBlockProps();
	const {
		attributes: { redirectUrl, logOutUrl },
		setAttributes,
	} = props;

	const setRedirectUrl = (url) => {
		setAttributes({ redirectUrl: url });
	};
	const setLogOutUrl = (url) => {
		setAttributes({ logOutUrl: url });
	};

	return (
		<>
			<InspectorControls key="ur-gutenberg-form-selector-inspector-controls">
				<PanelBody
					title={__("User Registration Forms", "user-registration")}
				>
					<TextControl
						key="ur-gutenberg-form-selector-redirect-url"
						label={__("Redirect URL", "user-registration")}
						value={redirectUrl}
						onChange={setRedirectUrl}
					/>
					<TextControl
						key="ur-gutenberg-form-selector-redirect-url"
						label={__("Logout URL", "user-registration")}
						value={logOutUrl}
						onChange={setLogOutUrl}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<ServerSideRender
					key="ur-gutenberg-form-selector-server-side-renderer"
					block="user-registration/loginform"
					attributes={props.attributes}
				/>
			</Box>
		</>
	);
};

export default Edit;
