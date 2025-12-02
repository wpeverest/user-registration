import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";

import {
	TextControl,
	SelectControl,
	PanelBody,
	Disabled
} from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const { urRestApiNonce, restURL, pages } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = (props) => {
	const useProps = useBlockProps();
	const blockName = metadata.name;

	const { attributes, setAttributes } = props;

	const { membershipType, buttonText, pageID } = attributes;

	const [membsershipList, setMembershipList] = useState("");

	// Fetch data for pages and groups
	const fetchData = async () => {
		try {
			const res = await apiFetch({
				path: `${restURL}user-registration/v1/gutenberg-blocks/membership-list`,
				method: "GET",
				headers: { "X-WP-Nonce": urRestApiNonce }
			});
			if (res.success) {
				setMembershipList(res.membership_list);
			}
		} catch (error) {
			console.error("Error fetching data:", error);
		}
	};

	useEffect(() => {
		if (!membsershipList) {
			fetchData();
		}
	}, [membsershipList]);

	const setMembershipType = (type) => {
		setAttributes({ membershipType: type });
	};

	if (!membsershipList) {
		return;
	}

	const membershipOptions = Object.values(membsershipList).map(
		(member, index) => ({
			value: index,
			label: member.title
		})
	);

	return (
		<>
			<InspectorControls key="ur-gutenberg-membership-buy-now-form-inspector-controls">
				<PanelBody title={__("General Settings", "user-registration")}>
					<SelectControl
						key="urm-select-page"
						label={__("Membership Form Pages", "user-registration")}
						value={pageID}
						options={[
							{
								label: __("Select Page", "user-registration"),
								value: ""
							},
							...pages
						]}
						onChange={(page) =>
							setAttributes({ pageID: Number(page) })
						}
					/>
					<SelectControl
						key="urm-select-membership-type"
						label={__("Membership Type", "user-registration")}
						value={membershipType}
						options={[
							{
								label: __(
									"Select a Membership Plan",
									"user-registration"
								),
								value: ""
							},
							...membershipOptions
						]}
						onChange={setMembershipType}
					/>
					<TextControl
						label={__("Button Text", "user-registration")}
						value={buttonText}
						onChange={(value) =>
							setAttributes({ buttonText: value })
						}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<Disabled>
					<ServerSideRender
						key="ur-gutenberg-membership-buy-now-form-server-side-renderer"
						block={blockName}
						attributes={attributes}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
