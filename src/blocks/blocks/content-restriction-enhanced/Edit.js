import React, { useState, useEffect, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { Box, ChakraProvider } from "@chakra-ui/react";
import { SelectControl, PanelBody } from "@wordpress/components";
import { InspectorControls, useBlockProps, InnerBlocks } from "@wordpress/block-editor";
import Select from "react-select";

/* global _UR_BLOCKS_ */
const { urRestApiNonce, restURL } = typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = ({ attributes, setAttributes }) => {
	const { accessAllRoles,accessSpecificRoles, accessControl, content } = attributes;
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
						headers: { "X-WP-Nonce": urRestApiNonce },
					}),
					apiFetch({
						path: `${restURL}user-registration/v1/gutenberg-blocks/cr-data`,
						method: "GET",
						headers: { "X-WP-Nonce": urRestApiNonce },
					}),
				]);

				if (roleRes.success) setRoleOptions(roleRes.role_lists);
				if (messageRes.success) setDefaultMessage(messageRes.cr_data.default_message);
			} catch (error) {
				console.error("Error fetching data:", error);
			}
		};

		fetchData();
	}, []);

	const roleDropdownOptions = useMemo(() => {
		return Object.keys(roleOptions).map((key) => ({
			value: key,
			label: roleOptions[key],
		}));
	}, [roleOptions]);

	const handleAccessControlChange = (value) => {
		setAttributes({
			accessControl: value,
		});
	};

	const handleAccessAllRoleChange = (value) => {
		setAttributes({ accessAllRoles: value })
	};


	const getAccessLabel = () =>
		accessControl === "access" ? __("Allow Access to", "user-registration") : __("Restrict Access to", "user-registration");

	return (
		<ChakraProvider>
			<Box {...blockProps} borderWidth="1px" borderRadius="lg" p={5}>
				<InspectorControls>
					<PanelBody title={__("Settings", "user-registration")}>
						<SelectControl
							label={__("Select Access Control", "user-registration")}
							value={accessControl}
							options={[
								{ label: __("Access", "user-registration"), value: "access" },
								{ label: __("Restrict", "user-registration"), value: "restrict" },
							]}
							onChange={handleAccessControlChange}
						/>

						<SelectControl
							label={getAccessLabel()}
							value={accessAllRoles}
							options={[
								{ label: __(`Select ${getAccessLabel()}`, "user-registration"), value: "" },
								{ label: __("All Logged In Users", "user-registration"), value: "0" },
								{ label: __("Choose Specific Roles", "user-registration"), value: "1" },
								{ label: __("Guest Users", "user-registration"), value: "2" },
								{ label: __("Memberships", "user-registration"), value: "3" },
							]}
							onChange={handleAccessAllRoleChange}
						/>


						{accessAllRoles === "1" && (
							<Select
								isMulti
								options={roleDropdownOptions}
								className="react-select-container"
								classNamePrefix="react-select"
								placeholder={__("Select specific roles...", "user-registration")}
								value={roleDropdownOptions.filter((option) =>
									Array.isArray(accessSpecificRoles) ? accessSpecificRoles.includes(option.value) : false
								)}
								onChange={(selected) => { console.log(selected,'selected'); setAttributes({ accessSpecificRoles: selected.map((option) => option.value) })}}
							/>
						)}
					</PanelBody>
				</InspectorControls>

				{accessControl === "access" ? (
					<div>
						<InnerBlocks value={content} templateLock={false} />
						<div className="user-registration-content-restriction-block-note">
							<span className="dashicon dashicons dashicons-lock"></span>
							<p className="user-registration-content-restriction-block-note-text">
								{__("This block has global content restriction settings.", "user-registration")}
							</p>
						</div>
					</div>
				) : (
					<span dangerouslySetInnerHTML={{ __html: defaultMessage }} />
				)}
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
