import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { Box, Textarea } from "@chakra-ui/react";

import { SelectControl, PanelBody } from "@wordpress/components";

import {
	InspectorControls,
	useBlockProps,
	InnerBlocks
} from "@wordpress/block-editor";
import { ChakraProvider } from "@chakra-ui/react";

/* global _UR_BLOCKS_ */
const { urRestApiNonce, restURL, logoUrl } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = (props) => {
	const useProps = useBlockProps();
	const {
		attributes: { accessRole, accessControl, content },
		setAttributes
	} = props;
	const [roleOptions, setRoleOptions] = useState([]);
	const [defaultMessage, setDefaultMessage] = useState("");

	useEffect(() => {
		const fetchData = async () => {
			if (roleOptions.length === 0) {
				try {
					const res = await apiFetch({
						path:
							restURL +
							"user-registration/v1/gutenberg-blocks/role-list",
						method: "GET",
						headers: {
							"X-WP-Nonce": urRestApiNonce
						}
					});
					if (res.success) {
						setRoleOptions(res.role_lists);
					}
				} catch (error) {
					console.error("Error fetching data:", error);
				}
			}
		};
		const fetchContentRestrictionMessage = async () => {
			if (roleOptions.length === 0) {
				try {
					const res = await apiFetch({
						path:
							restURL +
							"user-registration/v1/gutenberg-blocks/cr-data",
						method: "GET",
						headers: {
							"X-WP-Nonce": urRestApiNonce
						}
					});
					if (res.success) {
						setDefaultMessage(res.cr_data.default_message);
					}
				} catch (error) {
					console.error("Error fetching data:", error);
				}
			}
		};
		fetchData();
		fetchContentRestrictionMessage();
	}, []);
	const options = Object.keys(roleOptions).map((index) => ({
		value: index,
		label: roleOptions[index]
	}));

	const setAccessRole = (role) => {
		setAttributes({ accessRole: role });
	};
	const onChangeContent = (newContent) => {
		setAttributes({ content: newContent });
	};
	const setAccessControl = async (state) => {
		setAttributes({ accessControl: state });
	};

	return (
		<ChakraProvider>
			<Box {...useProps} borderWidth="1px" borderRadius="lg" p={5}>
				<InspectorControls key="ur-gutenberg-popup-inspector-controls">
					<PanelBody title={__("Settings", "user-registration")}>
						<SelectControl
							label={__(
								"Select access role",
								"user-registration"
							)}
							key="ur-gutenberg-popup-popup"
							value={accessRole}
							options={[
								{
									label: __(
										"Select Role",
										"user-registration"
									),
									value: ""
								},
								...options
							]}
							onChange={setAccessRole}
						/>
						<SelectControl
							label={__(
								"Select Access Control",
								"user-registration"
							)}
							key="ur-gutenberg-cr-access-control"
							value={accessControl}
							options={[
								{
									label: __("Access", "user-registration"),
									value: "access"
								},
								{
									label: __("Restrict", "user-registration"),
									value: "restrict"
								}
							]}
							onChange={setAccessControl}
						/>
					</PanelBody>
				</InspectorControls>

				{accessControl === "access" ? (
					<div>
						<InnerBlocks
							value={content}
							onChange={onChangeContent}
							templateLock={false}
						/>
						<div className="user-registration-content-restriction-block-note">
							<span className="dashicon dashicons dashicons-lock"></span>
							<p className="user-registration-content-restriction-block-note-text">
								{__(
									"This block has global content restriction settings.",
									"user-registration"
								)}
							</p>
						</div>
					</div>
				) : (
					<span
						dangerouslySetInnerHTML={{ __html: defaultMessage }} //since this is set by admin , no escaping is done here.
					/>
				)}
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
