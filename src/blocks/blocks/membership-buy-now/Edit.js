import { __ } from "@wordpress/i18n";
import { useEffect, useMemo, useRef, useState } from "react";
import metadata from "./block.json";

import {
	Button,
	ColorPalette,
	Dropdown,
	Flex,
	FlexItem,
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	__experimentalUnitControl as UnitControl
} from "@wordpress/components";

import {
	InspectorControls,
	useBlockProps,
	useSettings
} from "@wordpress/block-editor";

import apiFetch from "@wordpress/api-fetch";
import JustifyControl from "./components/JustifyContentControl";
import "./editor.scss";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const { urRestApiNonce } = typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const buildHoverCss = ({ blockId, hoverTextColor, hoverBgColor }) => {
	if (!blockId) return "";

	const selector = `.urm-${blockId}:hover`;
	let css = "";

	if (hoverTextColor) {
		css += `${selector}{color:${hoverTextColor} !important;}\n`;
	}
	if (hoverBgColor) {
		css += `${selector}{background-color:${hoverBgColor} !important;}\n`;
	}
	return css;
};

const ColorControl = ({ label, colorValue, onChange, themeColors }) => (
	<div
		data-wp-component="ToolsPanelItem"
		className="components-tools-panel-item block-editor-tools-panel-color-gradient-settings__item urm-custom-hover-tool-panel"
	>
		<Dropdown
			className="block-editor-tools-panel-color-gradient-settings__dropdown"
			contentClassName="block-editor-panel-color-gradient-settings__dropdown-content"
			popoverProps={{
				placement: "left-start",
				offset: 36,
				shift: true,
				focusOnMount: "container",
				__unstableSlotName: "Popover"
			}}
			focusOnMount={false}
			renderToggle={({ isOpen, onToggle }) => (
				<Button
					className="block-editor-panel-color-gradient-settings__dropdown"
					onClick={onToggle}
					aria-expanded={isOpen}
					style={{ width: "100%" }}
				>
					<Flex justify="flex-start" align="center" gap={2}>
						<FlexItem>
							<span
								className={
									colorValue
										? "custom-component-color-indicator"
										: "component-color-indicator"
								}
								style={{
									backgroundColor: colorValue || "transparent"
								}}
							/>
						</FlexItem>
						<FlexItem className="block-editor-panel-color-gradient-settings__color-name">
							{label}
						</FlexItem>
					</Flex>
				</Button>
			)}
			renderContent={({ onClose }) => (
				<div style={{ padding: "16px", width: "240px" }}>
					<ColorPalette
						value={colorValue}
						onChange={onChange}
						colors={themeColors || []}
						clearable={true}
						enableAlpha={true}
						__experimentalIsRenderedInSidebar={true}
					/>
				</div>
			)}
		/>
	</div>
);

const Edit = (props) => {
	const blockName = metadata.name;

	const { attributes, setAttributes, clientId } = props;

	const {
		membershipType,
		text,
		width,
		hoverTextColor,
		hoverBgColor,
		backgroundColor,
		textColor,
		borderColor,
		borderRadius,
		borderWidth,
		borderStyle,
		openInNewTab,
		savedBackgroundColor,
		savedHoverBgColor
	} = attributes;

	const [membershipList, setMembershipList] = useState(null);
	const blockProps = useBlockProps();

	const [themeColors] = useSettings("color.palette.theme");

	const isOutlineStyle =
		blockProps.className?.includes("is-style-outline") || false;

	const prevIsOutlineRef = useRef(isOutlineStyle);

	const fetchData = async () => {
		try {
			const res = await apiFetch({
				path: `user-registration/v1/gutenberg-blocks/membership-list`,
				method: "GET",
				headers: { "X-WP-Nonce": urRestApiNonce }
			});

			if (res?.success) {
				setMembershipList(res.membership_list || {});
			} else {
				setMembershipList({});
			}
		} catch (error) {
			console.error("Error fetching data:", error);
			setMembershipList({});
		}
	};

	useEffect(() => {
		fetchData();
	}, []);

	useEffect(() => {
		if (!attributes.clientId && clientId) {
			setAttributes({ clientId });
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [clientId]);

	useEffect(() => {
		if (prevIsOutlineRef.current === isOutlineStyle) return;

		if (isOutlineStyle) {
			const updates = {};

			if (backgroundColor) {
				updates.savedBackgroundColor = backgroundColor;
				updates.backgroundColor = "";
			}

			if (hoverBgColor) {
				updates.savedHoverBgColor = hoverBgColor;
				updates.hoverBgColor = "";
			}

			if (!borderColor) {
				updates.borderColor = "#000000";
			}

			if (Object.keys(updates).length > 0) {
				setAttributes(updates);
			}
		}

		if (!isOutlineStyle) {
			const updates = {};

			if (!backgroundColor) {
				updates.backgroundColor = savedBackgroundColor || "#475bb2";
			}

			if (!hoverBgColor) {
				updates.hoverBgColor = savedHoverBgColor || "#3a4a8f";
			}

			if (borderColor) {
				updates.borderColor = "";
			}

			if (Object.keys(updates).length > 0) {
				setAttributes(updates);
			}
		}

		prevIsOutlineRef.current = isOutlineStyle;
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [isOutlineStyle]);

	useEffect(() => {
		const editorWrapper = document.querySelector(
			".interface-interface-skeleton"
		);

		if (editorWrapper) {
			if (isOutlineStyle) {
				editorWrapper.classList.remove("urm-hide-border-panel");
			} else {
				editorWrapper.classList.add("urm-hide-border-panel");
			}
		}

		return () => {
			if (editorWrapper) {
				editorWrapper.classList.remove("urm-hide-border-panel");
			}
		};
	}, [isOutlineStyle]);

	const membershipOptions = useMemo(() => {
		if (!membershipList) return [];
		return Object.values(membershipList).map((member, index) => ({
			value: index,
			label: member.title
		}));
	}, [membershipList]);

	const blockId = attributes.clientId || clientId;

	const hoverCss = useMemo(() => {
		return buildHoverCss({
			blockId,
			hoverTextColor,
			hoverBgColor
		});
	}, [blockId, hoverTextColor, hoverBgColor]);

	const ssrAttributes = useMemo(() => {
		return {
			...attributes,
			clientId: blockId
		};
	}, [attributes, blockId]);

	if (!membershipList) {
		return (
			<div {...blockProps}>
				{__("Loading memberships…", "user-registration")}
			</div>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__("Settings", "user-registration")}
					initialOpen={true}
				></PanelBody>
				<SelectControl
					key="urm-select-membership-type"
					label={__("Membership Plan", "user-registration")}
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
					onChange={(type) => setAttributes({ membershipType: type })}
				/>

				<ToggleControl
					className="urm-buynow-open-new-tab"
					__nextHasNoMarginBottom
					label={__("Open in a new tab", "user-registration")}
					checked={openInNewTab}
					onChange={(value) => setAttributes({ openInNewTab: value })}
				/>

				<TextControl
					label={__("Button Text", "user-registration")}
					value={text}
					onChange={(value) => setAttributes({ text: value })}
				/>

				<ToggleGroupControl
					label={__("Width", "user-registration")}
					value={width}
					onChange={(value) => setAttributes({ width: value })}
					isBlock
				>
					<ToggleGroupControlOption value="25%" label="25%" />
					<ToggleGroupControlOption value="50%" label="50%" />
					<ToggleGroupControlOption value="75%" label="75%" />
					<ToggleGroupControlOption value="100%" label="100%" />
				</ToggleGroupControl>

				<JustifyControl
					value={attributes.justifyContent}
					onChange={(val) => setAttributes({ justifyContent: val })}
				/>
			</InspectorControls>

			<InspectorControls group="color">
				<ColorControl
					label={__("Text Color", "user-registration")}
					colorValue={textColor}
					onChange={(c) => setAttributes({ textColor: c })}
					themeColors={themeColors}
				/>

				{!isOutlineStyle && (
					<ColorControl
						label={__("Background Color", "user-registration")}
						colorValue={backgroundColor}
						onChange={(c) => setAttributes({ backgroundColor: c })}
						themeColors={themeColors}
					/>
				)}

				<ColorControl
					label={__("Text Hover", "user-registration")}
					colorValue={hoverTextColor}
					onChange={(c) => setAttributes({ hoverTextColor: c })}
					themeColors={themeColors}
				/>

				{!isOutlineStyle && (
					<ColorControl
						label={__("Background Hover", "user-registration")}
						colorValue={hoverBgColor}
						onChange={(c) => setAttributes({ hoverBgColor: c })}
						themeColors={themeColors}
					/>
				)}
			</InspectorControls>

			{!!hoverCss && <style>{hoverCss}</style>}

			<div {...blockProps}>
				<ServerSideRender
					key="ur-gutenberg-membership-buy-now-form-server-side-renderer"
					block={blockName}
					attributes={ssrAttributes}
				/>
			</div>
		</>
	);
};

export default Edit;
