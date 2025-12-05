import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";

import {
	TextControl,
	SelectControl,
	Disabled,
	PanelBody,
	TabPanel,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	ColorPalette,
	BaseControl,
	Dropdown,
	Button,
	Flex,
	FlexItem
} from "@wordpress/components";

import {
	InspectorControls,
	useBlockProps,
	useSettings
} from "@wordpress/block-editor";
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

	const {
		membershipType,
		text,
		pageID,
		width,
		hoverTextColor,
		hoverBgColor
	} = attributes;

	const [membsershipList, setMembershipList] = useState("");
	const useProps = useBlockProps();

	// Get colors from settings
	const colors = useSettings("color.palette");

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

	const HoverColorControl = ({ label, colorValue, onChange }) => (
		<div className="components-tools-panel-item block-editor-tools-panel-color-gradient-settings__item">
			<Dropdown
				className="block-editor-tools-panel-color-gradient-settings__dropdown"
				contentClassName="block-editor-panel-color-gradient-settings__dropdown-content"
				renderToggle={({ isOpen, onToggle }) => (
					<Button
						className="block-editor-panel-color-gradient-settings__dropdown"
						onClick={onToggle}
						aria-expanded={isOpen}
					>
						<Flex justify="flex-start" align="center" gap={2}>
							<FlexItem>
								<span
									className="custom-component-color-indicator"
									style={{
										backgroundColor:
											colorValue || "transparent"
									}}
								/>
							</FlexItem>
							<FlexItem className="block-editor-panel-color-gradient-settings__color-name">
								{label}
							</FlexItem>
						</Flex>
					</Button>
				)}
				renderContent={() => (
					<div style={{ padding: "16px", width: "240px" }}>
						<ColorPalette
							value={colorValue}
							onChange={onChange}
							colors={colors}
							clearable={true}
						/>
					</div>
				)}
			/>
		</div>
	);

	return (
		<>
			<InspectorControls>
				<TabPanel
					className="urm-buy-now-tabs"
					activeClass="active-tab"
					tabs={[{ name: "general", title: "General" }]}
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
						</>
					)}
				</TabPanel>
			</InspectorControls>

			<InspectorControls group="styles">
				<div className="color-block-support-panel">
					<div className="color-block-support-panel__inner-wrapper">
						<div className="block-editor-panel-color-gradient-settings__panel-title">
							{__("Hover", "user-registration")}
						</div>
						<HoverColorControl
							label={__("Text", "user-registration")}
							colorValue={hoverTextColor}
							onChange={(color) =>
								setAttributes({ hoverTextColor: color })
							}
						/>
						<HoverColorControl
							label={__("Background", "user-registration")}
							colorValue={hoverBgColor}
							onChange={(color) =>
								setAttributes({ hoverBgColor: color })
							}
						/>
					</div>
				</div>
			</InspectorControls>

			<div {...useProps}>
				<ServerSideRender
					key="ur-gutenberg-membership-buy-now-form-server-side-renderer"
					block={blockName}
					attributes={attributes}
				/>
			</div>
		</>
	);
};

export default Edit;
