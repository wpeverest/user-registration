import React, { useState, useEffect, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { Box, ChakraProvider, FormControl, FormLabel } from "@chakra-ui/react";
import {
	SelectControl,
	PanelBody,
	ToggleControl,
	RadioControl
} from "@wordpress/components";
import {
	InspectorControls,
	useBlockProps,
	InnerBlocks
} from "@wordpress/block-editor";
import Select from "react-select";
import { Editor } from "@tinymce/tinymce-react";

/* global _UR_BLOCKS_ */
const { urRestApiNonce } = typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const labelStyle = {
	fontSize: "11px",
	fontWeight: 500,
	lineHeight: "1.4",
	textTransform: "uppercase",
	display: "block",
	paddingTop: 0,
	paddingBottom: 0,
	maxWidth: "100%",
	overflow: "hidden",
	textOverflow: "ellipsis",
	whiteSpace: "nowrap"
};

const Edit = ({ attributes, setAttributes }) => {
	const {
		accessAllRoles,
		accessSpecificRoles = [],
		accessMembershipRoles = [],
		accessControl,
		message,
		enableContentRestriction
	} = attributes;
	const blockProps = useBlockProps();

	const [roleOptions, setRoleOptions] = useState({});
	const [defaultMessage, setDefaultMessage] = useState("");
	const [accessRolesOptions, setAccessRolesOptions] = useState({});
	const [membershipRolesOptions, setMembershipRolesOptions] = useState({});

	useEffect(() => {
		const fetchData = async () => {
			try {
				const [membershipRoles, accessRoles, roles, crData] =
					await Promise.all([
						apiFetch({
							path: `user-registration/v1/gutenberg-blocks/membership-role-list`,
							headers: { "X-WP-Nonce": urRestApiNonce }
						}),
						apiFetch({
							path: `user-registration/v1/gutenberg-blocks/access-role-list`,
							headers: { "X-WP-Nonce": urRestApiNonce }
						}),
						apiFetch({
							path: `user-registration/v1/gutenberg-blocks/role-list`,
							headers: { "X-WP-Nonce": urRestApiNonce }
						}),
						apiFetch({
							path: `user-registration/v1/gutenberg-blocks/cr-data`,
							headers: { "X-WP-Nonce": urRestApiNonce }
						})
					]);

				if (membershipRoles.success)
					setMembershipRolesOptions(
						membershipRoles.membership_roles_list
					);
				if (accessRoles.success)
					setAccessRolesOptions(
						accessRoles.access_data.access_role_list
					);
				if (roles.success) setRoleOptions(roles.role_lists);
				if (crData.success)
					setDefaultMessage(crData.cr_data.default_message);
			} catch (error) {
				console.error(
					"Data fetch failed in Content Restriction block:",
					error
				);
			}
		};
		fetchData();
	}, []);

	const roleDropdownOptions = useMemo(
		() =>
			Object.entries(roleOptions).map(([value, label]) => ({
				value,
				label
			})),
		[roleOptions]
	);

	const accessRoleDropdownOptions = useMemo(
		() =>
			Object.entries(accessRolesOptions).map(([value, label]) => ({
				value,
				label
			})),
		[accessRolesOptions]
	);

	const membershipRoleDropdownOptions = useMemo(
		() =>
			Object.entries(membershipRolesOptions).map(([value, label]) => ({
				value,
				label
			})),
		[membershipRolesOptions]
	);

	const getAccessLabel = () =>
		accessControl === "access"
			? __("Allow Access to", "user-registration")
			: __("Restrict Access to", "user-registration");

	return (
		<ChakraProvider>
			<Box {...blockProps} borderWidth="1px" borderRadius="lg" p={5}>
				<InspectorControls>
					<PanelBody title={__("Settings", "user-registration")}>
						<RadioControl
							label={__(
								"Restrict Content as per",
								"user-registration"
							)}
							selected={
								enableContentRestriction ? "true" : "false"
							}
							options={[
								{
									label: __(
										"Migrated Global Restriction Rule",
										"user-registration"
									),
									value: "false"
								},
								{
									label: __(
										"Custom Restriction",
										"user-registration"
									),
									value: "true"
								}
							]}
							onChange={(value) => {
								setAttributes({
									enableContentRestriction: value === "true"
								});
							}}
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
									onChange={(val) =>
										setAttributes({ accessControl: val })
									}
								/>

								<SelectControl
									label={getAccessLabel()}
									value={accessAllRoles}
									options={[
										{
											label: `Select ${getAccessLabel()}`,
											value: ""
										},
										...accessRoleDropdownOptions
									]}
									onChange={(val) =>
										setAttributes({ accessAllRoles: val })
									}
								/>

								{accessAllRoles === "choose_specific_roles" && (
									<FormControl>
										<FormLabel sx={labelStyle}>
											{__(
												"Specific Roles",
												"user-registration"
											)}
										</FormLabel>
										<Select
											isMulti
											options={roleDropdownOptions}
											classNamePrefix="react-select"
											placeholder={__(
												"Select specific roles...",
												"user-registration"
											)}
											value={roleDropdownOptions.filter(
												(opt) =>
													accessSpecificRoles.includes(
														opt.value
													)
											)}
											onChange={(selected) =>
												setAttributes({
													accessSpecificRoles:
														selected.map(
															(opt) => opt.value
														)
												})
											}
										/>
									</FormControl>
								)}

								{accessAllRoles === "memberships" && (
									<FormControl>
										<FormLabel sx={labelStyle}>
											{__(
												"Select Memberships",
												"user-registration"
											)}
										</FormLabel>
										<Select
											isMulti
											options={
												membershipRoleDropdownOptions
											}
											classNamePrefix="react-select"
											placeholder={__(
												"Select membership roles...",
												"user-registration"
											)}
											value={membershipRoleDropdownOptions.filter(
												(opt) =>
													accessMembershipRoles.includes(
														opt.value
													)
											)}
											onChange={(selected) =>
												setAttributes({
													accessMembershipRoles:
														selected.map(
															(opt) => opt.value
														)
												})
											}
										/>
									</FormControl>
								)}

								<FormControl mt={6}>
									<FormLabel sx={labelStyle}>
										{__(
											"Restricted Content Message",
											"user-registration"
										)}
									</FormLabel>
									<Editor
										value={message}
										onEditorChange={(val) =>
											setAttributes({ message: val })
										}
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

				<Box mb={6}>
					<InnerBlocks templateLock={false} />
					<div className="user-registration-content-restriction-block-note">
						<span className="dashicon dashicons dashicons-lock" />
						<p className="user-registration-content-restriction-block-note-text">
							{__(
								"This block has global content restriction settings.",
								"user-registration"
							)}
						</p>
					</div>
				</Box>
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
