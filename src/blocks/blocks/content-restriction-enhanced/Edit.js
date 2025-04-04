import React, { useState, useEffect, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { Box, ChakraProvider, FormControl, FormLabel } from "@chakra-ui/react";
import { SelectControl, PanelBody, ToggleControl } from "@wordpress/components";
import {
	InspectorControls,
	useBlockProps,
	InnerBlocks
} from "@wordpress/block-editor";
import Select from "react-select";
import { Editor } from "@tinymce/tinymce-react";

/* global _UR_BLOCKS_ */
const { urRestApiNonce, restURL } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = ({ attributes, setAttributes }) => {
	const {
		accessAllRoles,
		accessSpecificRoles,
		accessControl,
		content,
		message,
		enableContentRestriction
	} = attributes;
	const blockProps = useBlockProps();

	const [roleOptions, setRoleOptions] = useState([]);
	const [defaultMessage, setDefaultMessage] = useState("");

	useEffect(() => {
		const fetchData = async () => {
			try {
				const [roleRes, messageRes] = await Promise.all([
					apiFetch({
						path: `${restURL}user-registration/v1/gutenberg-blocks/role-list`,
						method: "GET",
						headers: { "X-WP-Nonce": urRestApiNonce }
					}),
					apiFetch({
						path: `${restURL}user-registration/v1/gutenberg-blocks/cr-data`,
						method: "GET",
						headers: { "X-WP-Nonce": urRestApiNonce }
					})
				]);

				if (roleRes.success) setRoleOptions(roleRes.role_lists);
				if (messageRes.success)
					setDefaultMessage(messageRes.cr_data.default_message);
			} catch (error) {
				console.error("Error fetching data:", error);
			}
		};

		fetchData();
	}, []);

	const roleDropdownOptions = useMemo(() => {
		return Object.keys(roleOptions).map((key) => ({
			value: key,
			label: roleOptions[key]
		}));
	}, [roleOptions]);

	const handleAccessControlChange = (value) => {
		setAttributes({ accessControl: value });
	};

	const handleAccessAllRoleChange = (value) => {
		setAttributes({ accessAllRoles: value });
	};

	const handleContentChange = (value) => {
		setAttributes({ message: value });
	};

	const handleEnableContentRestriction = (value) => {
		setAttributes({ enableContentRestriction: value });
	};

	const getAccessLabel = () =>
		accessControl === "access"
			? __("Allow Access to", "user-registration")
			: __("Restrict Access to", "user-registration");

	return (
		<ChakraProvider>
			<Box {...blockProps} borderWidth="1px" borderRadius="lg" p={5}>
				<InspectorControls>
					<PanelBody title={__("Settings", "user-registration")}>
						<ToggleControl
							label={__(
								"Enable Content Restriction",
								"user-registration"
							)}
							checked={enableContentRestriction}
							onChange={handleEnableContentRestriction}
						/>
						{enableContentRestriction && (
							<Box>
								<SelectControl
									label={__(
										"Select Access Control",
										"user-registration"
									)}
									value={accessControl}
									options={[
										{
											label: __(
												"Access",
												"user-registration"
											),
											value: "access"
										},
										{
											label: __(
												"Restrict",
												"user-registration"
											),
											value: "restrict"
										}
									]}
									onChange={handleAccessControlChange}
								/>

								<SelectControl
									label={getAccessLabel()}
									value={accessAllRoles}
									options={[
										{
											label: __(
												`Select ${getAccessLabel()}`,
												"user-registration"
											),
											value: ""
										},
										{
											label: __(
												"All Logged In Users",
												"user-registration"
											),
											value: "all_logged_in_users"
										},
										{
											label: __(
												"Choose Specific Roles",
												"user-registration"
											),
											value: "choose_specific_roles"
										},
										{
											label: __(
												"Guest Users",
												"user-registration"
											),
											value: "guest_users"
										},
										{
											label: __(
												"Memberships",
												"user-registration"
											),
											value: "memberships"
										}
									]}
									onChange={handleAccessAllRoleChange}
								/>

								{accessAllRoles === "choose_specific_roles" && (
									<FormControl>
										<FormLabel
											sx={{
												fontSize: "11px",
												fontWeight: 500,
												lineHeight: "1.4",
												textTransform: "uppercase",
												boxSizing: "border-box",
												display: "block",
												paddingTop: "0px",
												paddingBottom: "0px",
												maxWidth: "100%",
												zIndex: 1,
												overflow: "hidden",
												textOverflow: "ellipsis",
												whiteSpace: "nowrap"
											}}
										>
											{__("Specific Roles")}
										</FormLabel>
										<Select
											isMulti
											options={roleDropdownOptions}
											className="react-select-container"
											classNamePrefix="react-select"
											placeholder={__(
												"Select specific roles...",
												"user-registration"
											)}
											value={roleDropdownOptions.filter(
												(option) =>
													Array.isArray(
														accessSpecificRoles
													)
														? accessSpecificRoles.includes(
																option.value
														  )
														: false
											)}
											onChange={(selected) =>
												setAttributes({
													accessSpecificRoles:
														selected.map(
															(option) =>
																option.value
														)
												})
											}
										/>
									</FormControl>
								)}
								
								<FormControl>
								<FormLabel
									sx={{
										fontSize: "11px",
										fontWeight: 500,
										lineHeight: "1.4",
										textTransform: "uppercase",
										boxSizing: "border-box",
										display: "block",
										paddingTop: "0px",
										paddingBottom: "0px",
										maxWidth: "100%",
										zIndex: 1,
										overflow: "hidden",
										textOverflow: "ellipsis",
										whiteSpace: "nowrap"
									}}
								>
									{__("Restricted Content Message")}
								</FormLabel>
								<Editor
									value={message}
									onEditorChange={handleContentChange}
									initialValue={defaultMessage}
									init={{
										height: 200,
										menubar: false,
										plugins: "link lists",
										toolbar:
											"undo redo | bold italic | alignleft aligncenter alignright | bullist numlist",
										content_style:
											"body { font-family:Arial,sans-serif; font-size:14px }"
									}}
								/>
								</FormControl>
							</Box>
						)}
					</PanelBody>
				</InspectorControls>

				{accessControl === "access" ? (
					<div>
						<InnerBlocks templateLock={false} />
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
						dangerouslySetInnerHTML={{ __html: attributes.message }}
					/>
				)}
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
