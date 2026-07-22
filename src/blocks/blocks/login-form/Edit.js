import React, {useState} from "react";
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
		attributes: { redirectUrl, logoutUrl },
		setAttributes,
	} = props;

	const setRedirectUrl = (url) => {
		setAttributes({ redirectUrl: url });
	};
	const setLogoutUrl = (url) => {
		setAttributes({ logoutUrl: url });
	};

	const [userState, setUserState] = useState("logged_out");


	return (
		<>
			<InspectorControls key="ur-gutenberg-login-form-inspector-controls">
				<PanelBody
					title={__("Login Form Settings", "user-registration")}
				>
					<p>
						Want to control where users land after they log in or log out? Configure it <a target="_blank" href="/wp-admin/admin.php?page=user-registration-login-forms&tab=ur-tab-login-form-settings&tab-item=advanced-settings&highlight=user_registration_login_options_enable_custom_redirect">here</a>.
					</p>
					<SelectControl
						key="ur-gutenberg-login-user-login-state"
						label={__("User State", "user-registration")}
						value={userState}
						options={[
							{ label: "Logged In", value: "logged_in" },
							{ label: "Logged Out", value: "logged_out" },
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
						attributes={{...props.attributes, userState  }}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
