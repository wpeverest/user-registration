import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";

import {
	TextControl,
	SelectControl,
	Disabled,
	PanelBody,
	RangeControl,
	__experimentalUnitControl as UnitControl,
	__experimentalHStack as HStack,
	TabPanel,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption
} from "@wordpress/components";

import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import JustifyControl from "./components/JustifyContentControl";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const { urRestApiNonce, restURL, pages } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = (props) => {
	const blockName = metadata.name;

	const { attributes, setAttributes } = props;

	const { membershipType, text, pageID, width } = attributes;

	const [membsershipList, setMembershipList] = useState("");
	const useProps = useBlockProps({
		// className: "wp-block-buttons"
	});

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
			<InspectorControls>
				<TabPanel
					className="urm-buy-now-tabs"
					activeClass="active-tab"
					tabs={[
						{ name: "general", title: "General" },
						{ name: "styles", title: "Styles" }
					]}
				>
					{(tab) => (
						<>
							{tab.name === "general" && (
								<PanelBody title="" initialOpen={true}>
									<SelectControl
										key="urm-select-page"
										label={__(
											"Success Page (after purchase)",
											"user-registration"
										)}
										value={pageID}
										options={[
											{
												label: __(
													"Select a page...",
													"user-registration"
												),
												value: ""
											},
											...pages
										]}
										onChange={(page) =>
											setAttributes({
												pageID: page
											})
										}
									/>
									<SelectControl
										key="urm-select-membership-type"
										label={__(
											"Membership Plan",
											"user-registration"
										)}
										value={membershipType}
										options={[
											{
												label: __(
													"Select a membership...",
													"user-registration"
												),
												value: ""
											},
											...membershipOptions
										]}
										onChange={setMembershipType}
									/>
									<TextControl
										label={__(
											"Button Text",
											"user-registration"
										)}
										value={text}
										onChange={(value) =>
											setAttributes({ text: value })
										}
									/>

									<ToggleGroupControl
										label="Width"
										value={width}
										onChange={(value) =>
											setAttributes({ width: value })
										}
										isBlock
									>
										<ToggleGroupControlOption
											value="25%"
											label="25%"
										/>
										<ToggleGroupControlOption
											value="50%"
											label="50%"
										/>
										<ToggleGroupControlOption
											value="75%"
											label="75%"
										/>
										<ToggleGroupControlOption
											value="100%"
											label="100%"
										/>
									</ToggleGroupControl>

									<JustifyControl
										value={attributes.justifyContent}
										onChange={(val) =>
											setAttributes({
												justifyContent: val
											})
										}
									/>
								</PanelBody>
							)}

							{tab.name === "styles" && (
								<PanelBody title="" initialOpen={true}>
									<BorderControl
										value={attributes.border}
										onChange={(value) =>
											setAttributes({ border: value })
										}
									/>
									<RangeControl
										label="Radius"
										min={0}
										max={50}
										value={attributes.borderRadius}
										onChange={(value) =>
											setAttributes({
												borderRadius: value
											})
										}
									/>
								</PanelBody>
							)}
						</>
					)}
				</TabPanel>
			</InspectorControls>
			<div {...useProps}>
				<Disabled>
					<ServerSideRender
						key="ur-gutenberg-membership-buy-now-form-server-side-renderer"
						block={blockName}
						attributes={attributes}
					/>
				</Disabled>
			</div>
		</>
	);
};

export default Edit;
