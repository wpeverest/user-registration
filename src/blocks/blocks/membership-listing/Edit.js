import React, {
	useEffect,
	useMemo,
	useRef,
	useState,
	useCallback
} from "react";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";
import {
	InspectorControls,
	useBlockProps,
	useSettings
} from "@wordpress/block-editor";
import {
	Notice,
	PanelBody,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	ColorPalette,
	Dropdown,
	Button,
	Flex,
	FlexItem,
	__experimentalUnitControl as UnitControl,
	__experimentalBoxControl as BoxControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	RangeControl
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

const {
	urRestApiNonce,
	restURL,
	membership_all_plan_url,
	membership_group_url
} = typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

// Helper function to map options for SelectControl
const mapOptions = (list) =>
	Object.keys(list).map((index) => ({
		value: Number(index),
		label: list[index]
	}));

const HoverColorControl = ({ label, colorValue, themeColors, onChange }) => (
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
				<div
					style={{ padding: "16px", width: "240px" }}
					onClick={(e) => e.stopPropagation()}
					onMouseDown={(e) => e.stopPropagation()}
				>
					<ColorPalette
						enableCustomColor
						value={colorValue}
						onChange={onChange}
						colors={[]}
						clearable={true}
						enableAlpha={true}
						__experimentalIsRenderedInSidebar={true}
					/>

					{themeColors && themeColors.length > 0 && (
						<>
							<span className="ur-hover-color-picker__title">
								{__("Theme", "user-registration")}
							</span>
							<ColorPalette
								colors={themeColors}
								value={colorValue}
								onChange={onChange}
								clearable={false}
								disableCustomColors={true}
							/>
						</>
					)}
				</div>
			)}
		/>
	</div>
);

const buildMembershipCss = ({ buttonClass, radioClass, style }) => {
	if (!style) return "";
	console.log(buttonClass, radioClass, style);

	let buttonStyle = "";
	let buttonHoverStyle = "";
	let radioCss = "";

	const map = {
		buttonTextColor: "color",
		buttonBgColor: "background",
		buttonFontSize: "font-size"
	};

	Object.entries(map).forEach(([key, cssProp]) => {
		if (style?.[key]) {
			buttonStyle += `${cssProp}:${style[key]} !important;`;
		}
	});

	// Typography
	if (style?.buttonTypography?.fontWeight) {
		buttonStyle += `font-weight:${style.buttonTypography.fontWeight} !important;`;
	}
	if (style?.buttonTypography?.fontStyle) {
		buttonStyle += `font-style:${style.buttonTypography.fontStyle};`;
	}

	// Padding
	["top", "right", "bottom", "left"].forEach((pos) => {
		const v = style?.buttonPadding?.[pos];
		if (v) buttonStyle += `padding-${pos}:${v} !important;`;
	});

	// Margin
	["top", "right", "bottom", "left"].forEach((pos) => {
		const v = style?.buttonMargin?.[pos];
		if (v) buttonStyle += `margin-${pos}:${v} !important;`;
	});

	// Hover colors
	if (style?.buttonTextHoverColor) {
		buttonHoverStyle += `color:${style.buttonTextHoverColor} !important;`;
	}
	if (style?.buttonBgHoverColor) {
		buttonHoverStyle += `background:${style.buttonBgHoverColor} !important;`;
	}

	// Radio color
	const radioColor = style?.radioColor;
	if (radioColor) {
		radioCss = `
.${radioClass}{
	appearance:none;
	-webkit-appearance:none;
	width:16px !important;
	height:16px !important;
	border:2px solid ${radioColor} !important;
	border-radius:50%;
	cursor:pointer;
	position:relative;
}
.${radioClass}:checked::before{
	content:"";
	width:10px;
	height:10px;
	background:${radioColor} !important;
	border-radius:50%;
	position:absolute !important;
	top:50% !important;
	left:50% !important;
	transform:translate(-50%, -50%) !important;
	margin:0px !important;
}
`;
	}

	let css = "";

	if (buttonHoverStyle) {
		css += `.${buttonClass}:hover{${buttonHoverStyle}}\n`;
	}

	if (buttonStyle) {
		css += `.${buttonClass}{${buttonStyle}}\n`;
	}

	if (radioCss) css += radioCss;

	return css;
};

const Edit = (props) => {
	const { attributes, setAttributes } = props;

	const {
		id,
		redirection_page_id,
		group_id,
		thank_you_page_id,
		type,
		button_text,
		columnNumber,
		openInNewTab,
		showDescription,

		// style attributes
		buttonTextColor,
		buttonBgColor,
		buttonTextHoverColor,
		buttonBgHoverColor,
		radioColor,
		buttonFontSize,
		buttonTypography,
		buttonPadding,
		buttonMargin
	} = attributes;

	// State variables
	const [redirectionPageList, setRedirectionPageList] = useState("");
	const [thankYouPageList, setThankYouPageList] = useState("");
	const [groupList, setGroupList] = useState("");
	const [error, setError] = useState("");
	const [isLoading, setIsLoading] = useState(false);

	// Refs for locking post saving
	const redirectionLock = useRef(false);
	const thankYouLock = useRef(false);

	// Block props
	const useProps = useBlockProps();
	const blockName = metadata.name;
	const [success, setSuccess] = useState(false);

	const updateTypography = (key, value) => {
		setAttributes({
			buttonTypography: {
				...(buttonTypography || {}),
				[key]: value
			}
		});
	};

	// Fetch data for pages and groups
	const fetchData = async () => {
		try {
			if (!thankYouPageList || !redirectionPageList) {
				const res = await apiFetch({
					path: `${restURL}user-registration/v1/gutenberg-blocks/pages`,
					method: "GET",
					headers: { "X-WP-Nonce": urRestApiNonce }
				});
				if (res.success) {
					setThankYouPageList(res.page_lists);
					setRedirectionPageList(res.page_lists);
				}
			}

			if (!groupList) {
				const res = await apiFetch({
					path: `${restURL}user-registration/v1/gutenberg-blocks/groups`,
					method: "GET",
					headers: { "X-WP-Nonce": urRestApiNonce }
				});
				if (res.success) {
					setGroupList(res.group_lists);
				}
			}
		} catch (e) {
			console.error("Error fetching data:", e);
		}
	};

	// Verify a page
	const verifyPage = async ({ id, type, lockKey, isLockedRef }) => {
		setIsLoading(true);
		setError("");
		setSuccess(false);
		try {
			await apiFetch({
				path: `${restURL}user-registration/v1/gutenberg-blocks/verify-pages`,
				method: "POST",
				headers: {
					"X-WP-Nonce": urRestApiNonce,
					"Content-Type": "application/json"
				},
				data: JSON.stringify({ page_id: id, type })
			});

			if (isLockedRef.current) {
				wp.data.dispatch("core/editor").unlockPostSaving(lockKey);
				isLockedRef.current = false;
			}
			setSuccess(true);
		} catch (err) {
			setError(err?.message || "Something went wrong.");
			if (!isLockedRef.current) {
				wp.data.dispatch("core/editor").lockPostSaving(lockKey);
				isLockedRef.current = true;
			}
		} finally {
			setIsLoading(false);
		}
	};

	// Verify pages on load
	const verifyPagesOnLoad = async () => {
		if (redirection_page_id) {
			await verifyPage({
				id: redirection_page_id,
				type: "user_registration_member_registration_page_id",
				lockKey: "ur-redirection-page-lock",
				isLockedRef: redirectionLock
			});
		}

		if (thank_you_page_id) {
			await verifyPage({
				id: thank_you_page_id,
				type: "user_registration_thank_you_page_id",
				lockKey: "ur-thank-you-page-lock",
				isLockedRef: thankYouLock
			});
		}
	};

	const onGroupTypeChange = (val) => setAttributes({ type: val });
	const onButtonTextChange = (val) => setAttributes({ button_text: val });

	const generateUUID = () =>
		"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
			const r = (Math.random() * 16) | 0;
			const v = c === "x" ? r : (r & 0x3) | 0x8;
			return v.toString(16);
		});

	const [themeColors] = useSettings("color.palette.theme");

	useEffect(() => {
		return () => {
			if (redirectionLock.current) {
				wp.data
					.dispatch("core/editor")
					.unlockPostSaving("ur-redirection-page-lock");
				redirectionLock.current = false;
			}
			if (thankYouLock.current) {
				wp.data
					.dispatch("core/editor")
					.unlockPostSaving("ur-thank-you-page-lock");
				thankYouLock.current = false;
			}
		};
	}, []);

	useEffect(() => {
		fetchData();
	}, []);

	useEffect(() => {
		verifyPagesOnLoad();
	}, []);

	useEffect(() => {
		if (!id) {
			setAttributes({ id: generateUUID() });
		}
	}, []);

	const [localColors, setLocalColors] = useState({
		buttonTextColor: buttonTextColor || "",
		buttonBgColor: buttonBgColor || "",
		buttonTextHoverColor: buttonTextHoverColor || "",
		buttonBgHoverColor: buttonBgHoverColor || "",
		radioColor: radioColor || ""
	});

	useEffect(() => {
		setLocalColors({
			buttonTextColor: buttonTextColor || "",
			buttonBgColor: buttonBgColor || "",
			buttonTextHoverColor: buttonTextHoverColor || "",
			buttonBgHoverColor: buttonBgHoverColor || "",
			radioColor: radioColor || ""
		});
	}, [
		buttonTextColor,
		buttonBgColor,
		buttonTextHoverColor,
		buttonBgHoverColor,
		radioColor
	]);

	const commitColor = useCallback(
		(key) => {
			setAttributes({ [key]: localColors[key] });
		},
		[localColors, setAttributes]
	);

	const uuid = id || "temp";
	const buttonClass = `ur-membership-signup-btn-${uuid}`;
	const radioClass = `ur-membership-radio-${uuid}`;

	const styleObj = useMemo(
		() => ({
			buttonTextColor,
			buttonBgColor,
			buttonFontSize,
			buttonTypography,
			buttonPadding,
			buttonMargin,
			buttonTextHoverColor,
			buttonBgHoverColor,
			radioColor
		}),
		[
			buttonTextColor,
			buttonBgColor,
			buttonFontSize,
			buttonTypography,
			buttonPadding,
			buttonMargin,
			buttonTextHoverColor,
			buttonBgHoverColor,
			radioColor
		]
	);

	const inlineCss = useMemo(() => {
		return buildMembershipCss({
			buttonClass,
			radioClass,
			style: styleObj
		});
	}, [buttonClass, radioClass, styleObj]);

	const ssrAttributes = useMemo(() => {
		const {
			buttonTextColor,
			buttonBgColor,
			buttonTextHoverColor,
			buttonBgHoverColor,
			radioColor,
			buttonFontSize,
			buttonTypography,
			buttonPadding,
			buttonMargin,
			...rest
		} = attributes;

		rest.uuid = uuid;

		return rest;
	}, [attributes, uuid]);

	return (
		<>
			{!!inlineCss && <style>{inlineCss}</style>}

			<InspectorControls key="ur-gutenberg-membership-listing-inspector-controls">
				<PanelBody
					initialOpen={false}
					title={__("Redirection Page settings", "user-registration")}
				>
					<SelectControl
						key="ur-gutenberg-registration-form-id"
						value={redirection_page_id}
						label={__("Set Redirection Page", "user-registration")}
						options={[
							{
								label: __("Select a page", "user-registration"),
								value: ""
							},
							...mapOptions(redirectionPageList)
						]}
						onChange={(pid) =>
							setAttributes({ redirection_page_id: pid }) ||
							verifyPage({
								id: pid,
								type: "user_registration_member_registration_page_id",
								lockKey: "ur-redirection-page-lock",
								isLockedRef: redirectionLock
							})
						}
						__nextHasNoMarginBottom={true}
						__next40pxDefaultSize
					/>

					<SelectControl
						key="ur-gutenberg-thank-you-page-id"
						value={thank_you_page_id}
						label={__("Set Thank You Page", "user-registration")}
						options={[
							{
								label: __(
									"Select thank you page",
									"user-registration"
								),
								value: ""
							},
							...mapOptions(thankYouPageList)
						]}
						onChange={(pid) =>
							setAttributes({ thank_you_page_id: pid }) ||
							verifyPage({
								id: pid,
								type: "user_registration_thank_you_page_id",
								lockKey: "ur-thank-you-page-lock",
								isLockedRef: thankYouLock
							})
						}
						__nextHasNoMarginBottom={true}
						__next40pxDefaultSize
					/>

					{isLoading && (
						<div style={{ textAlign: "center", margin: "10px 0" }}>
							<Spinner />
						</div>
					)}
					{error && (
						<Notice status="error" isDismissible={false}>
							{error}
						</Notice>
					)}
					{!error && success && (
						<Notice status="success" isDismissible={false}>
							{__(
								"Page's verified successfully!",
								"user-registration"
							)}
						</Notice>
					)}
				</PanelBody>

				<PanelBody title={__("Group Settings", "user-registration")}>
					<SelectControl
						key="ur-gutenberg-group-id"
						value={group_id}
						label={__(
							"Select Membership Group",
							"user-registration"
						)}
						options={[
							{
								label: __(
									"Select a Group",
									"user-registration"
								),
								value: ""
							},
							...mapOptions(groupList)
						]}
						onChange={(gid) => setAttributes({ group_id: gid })}
						__nextHasNoMarginBottom={true}
						__next40pxDefaultSize
					/>

					<div className="ur-membership-listing-config-link">
						<a
							className="ur-membership-listing-config-link"
							href={membership_group_url}
							target="__blank"
							rel="noreferrer"
						>
							{__(
								"Configure groups from here",
								"user-registration"
							)}
						</a>
					</div>

					<ToggleControl
						__nextHasNoMarginBottom
						label={__("Open in a new tab", "user-registration")}
						checked={openInNewTab}
						onChange={(v) => setAttributes({ openInNewTab: v })}
					/>

					<ToggleControl
						__nextHasNoMarginBottom
						label={__("Show description", "user-registration")}
						checked={showDescription}
						onChange={(v) => setAttributes({ showDescription: v })}
					/>

					<div className="ur-membership-listing-config-link">
						<a
							className="ur-membership-listing-config-link"
							href={membership_all_plan_url}
							target="__blank"
							rel="noreferrer"
						>
							{__(
								"Configure description from here",
								"user-registration"
							)}
						</a>
					</div>

					<ToggleGroupControl
						label="Layout"
						value={type}
						onChange={onGroupTypeChange}
						isBlock
					>
						<ToggleGroupControlOption
							value="list"
							label={__("List", "user-registration")}
						/>
						<ToggleGroupControlOption
							value="row"
							label={__("Row", "user-registration")}
						/>
						<ToggleGroupControlOption
							value="block"
							label={__("Column", "user-registration")}
						/>
					</ToggleGroupControl>

					{"block" === type && (
						<ToggleGroupControl
							label={__("No. of Columns", "user-registration")}
							value={columnNumber}
							onChange={(value) =>
								setAttributes({ columnNumber: value })
							}
							isBlock
						>
							<ToggleGroupControlOption
								value={2}
								label={__("2", "user-registration")}
							/>
							<ToggleGroupControlOption
								value={3}
								label={__("3", "user-registration")}
							/>
							<ToggleGroupControlOption
								value={4}
								label={__("4", "user-registration")}
							/>
						</ToggleGroupControl>
					)}
				</PanelBody>

				<PanelBody title={__("Content Settings", "user-registration")}>
					<TextControl
						key="ur-gutenberg-button-text"
						label={__("Button Text", "user-registration")}
						value={button_text}
						onChange={onButtonTextChange}
					/>
				</PanelBody>
			</InspectorControls>

			<InspectorControls group="color">
				<>
					<HoverColorControl
						label={__("Button Text", "user-registration")}
						colorValue={attributes.buttonTextColor}
						onChange={(c) => setAttributes({ buttonTextColor: c })}
						themeColors={themeColors}
					/>
					<HoverColorControl
						label={__("Button Background", "user-registration")}
						colorValue={attributes.buttonBgColor}
						onChange={(c) => setAttributes({ buttonBgColor: c })}
						themeColors={themeColors}
					/>
					<HoverColorControl
						label={__("Button Text Hover", "user-registration")}
						colorValue={attributes.buttonTextHoverColor}
						onChange={(c) =>
							setAttributes({ buttonTextHoverColor: c })
						}
						themeColors={themeColors}
					/>
					<HoverColorControl
						label={__(
							"Button Background Hover",
							"user-registration"
						)}
						colorValue={attributes.buttonBgHoverColor}
						onChange={(c) =>
							setAttributes({ buttonBgHoverColor: c })
						}
						themeColors={themeColors}
					/>
					<HoverColorControl
						label={__("Radio Color", "user-registration")}
						colorValue={attributes.radioColor}
						onChange={(c) => setAttributes({ radioColor: c })}
						themeColors={themeColors}
					/>
				</>
			</InspectorControls>

			<InspectorControls group="styles">
				<ToolsPanel
					label={__("Button Typography", "user-registration")}
					resetAll={() =>
						setAttributes({
							buttonFontSize: undefined,
							buttonTypography: {}
						})
					}
					className="ur-button-typography-panel"
				>
					<ToolsPanelItem
						label={__("Font Size", "user-registration")}
						hasValue={() => !!buttonFontSize}
						onDeselect={() =>
							setAttributes({ buttonFontSize: undefined })
						}
						isShownByDefault
					>
						{(() => {
							const fontSizeValue = buttonFontSize || "";
							const numericValue = parseFloat(fontSizeValue) || 0;
							const unitValue =
								fontSizeValue.replace(/[0-9.]/g, "") || "px";

							const getConfig = (unit) => {
								switch (unit) {
									case "em":
									case "rem":
										return { max: 10, step: 0.1 };
									case "%":
										return { max: 200, step: 1 };
									default:
										return { max: 100, step: 1 };
								}
							};

							const config = getConfig(unitValue);

							return (
								<div className="ur-fontsize-control">
									<div className="ur-fontsize-control__header">
										<span className="ur-fontsize-control__label">
											{__(
												"Font Size",
												"user-registration"
											)}
										</span>
										<UnitControl
											value={buttonFontSize}
											onChange={(value) =>
												setAttributes({
													buttonFontSize: value
												})
											}
											units={[
												{
													value: "px",
													label: "px",
													default: 16
												},
												{
													value: "em",
													label: "em",
													default: 1
												},
												{
													value: "rem",
													label: "rem",
													default: 1
												},
												{
													value: "%",
													label: "%",
													default: 100
												}
											]}
											min={0}
											max={config.max}
											__nextHasNoMarginBottom
										/>
									</div>
									<RangeControl
										value={numericValue}
										onChange={(value) =>
											setAttributes({
												buttonFontSize:
													value + unitValue
											})
										}
										min={0}
										max={config.max}
										step={config.step}
										withInputField={false}
										__nextHasNoMarginBottom
									/>
								</div>
							);
						})()}
					</ToolsPanelItem>

					<ToolsPanelItem
						label={__("Appearance", "user-registration")}
						hasValue={() => !!buttonTypography?.fontWeight}
						onDeselect={() => updateTypography("fontWeight", "")}
						isShownByDefault
					>
						<SelectControl
							className="ur-button-font-weight"
							label={__("Font Weight", "user-registration")}
							value={buttonTypography?.fontWeight || ""}
							options={[
								{
									label: __("Default", "user-registration"),
									value: ""
								},
								{ label: "400 (Normal)", value: "400" },
								{ label: "500 (Medium)", value: "500" },
								{ label: "600 (Semi Bold)", value: "600" },
								{ label: "700 (Bold)", value: "700" }
							]}
							onChange={(value) =>
								updateTypography("fontWeight", value)
							}
							__nextHasNoMarginBottom
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						label={__("Font Style", "user-registration")}
						hasValue={() => !!buttonTypography?.fontStyle}
						onDeselect={() => updateTypography("fontStyle", "")}
						isShownByDefault
					>
						<SelectControl
							label={__("Style", "user-registration")}
							value={buttonTypography?.fontStyle || ""}
							options={[
								{
									label: __("Default", "user-registration"),
									value: ""
								},
								{
									label: __("Normal", "user-registration"),
									value: "normal"
								},
								{
									label: __("Italic", "user-registration"),
									value: "italic"
								}
							]}
							onChange={(value) =>
								updateTypography("fontStyle", value)
							}
							__nextHasNoMarginBottom
						/>
					</ToolsPanelItem>
				</ToolsPanel>

				<ToolsPanel
					label={__("Button Dimensions", "user-registration")}
					resetAll={() =>
						setAttributes({
							buttonPadding: {
								top: 0,
								right: 0,
								bottom: 0,
								left: 0
							},
							buttonMargin: {
								top: 0,
								right: 0,
								bottom: 0,
								left: 0
							}
						})
					}
					className="ur-button-dimensions-panel"
				>
					<ToolsPanelItem
						label={__("Padding", "user-registration")}
						hasValue={() =>
							Object.values(buttonPadding || {}).some(
								(v) => v !== 0
							)
						}
						onDeselect={() =>
							setAttributes({
								buttonPadding: {
									top: 0,
									right: 0,
									bottom: 0,
									left: 0
								}
							})
						}
						isShownByDefault
					>
						<BoxControl
							label={__("Padding", "user-registration")}
							values={buttonPadding}
							onChange={(value) =>
								setAttributes({ buttonPadding: value })
							}
							units={[
								{ value: "px", label: "px", default: 0 },
								{ value: "em", label: "em", default: 0 },
								{ value: "rem", label: "rem", default: 0 },
								{ value: "%", label: "%", default: 0 }
							]}
							allowReset={true}
							splitOnAxis={false}
						/>
					</ToolsPanelItem>

					<ToolsPanelItem
						label={__("Margin", "user-registration")}
						hasValue={() =>
							Object.values(buttonMargin || {}).some(
								(v) => v !== 0
							)
						}
						onDeselect={() =>
							setAttributes({
								buttonMargin: {
									top: 0,
									right: 0,
									bottom: 0,
									left: 0
								}
							})
						}
						isShownByDefault
					>
						<BoxControl
							label={__("Margin", "user-registration")}
							values={buttonMargin}
							onChange={(value) =>
								setAttributes({ buttonMargin: value })
							}
							units={[
								{ value: "px", label: "px", default: 0 },
								{ value: "em", label: "em", default: 0 },
								{ value: "rem", label: "rem", default: 0 },
								{ value: "%", label: "%", default: 0 }
							]}
							allowReset={true}
							splitOnAxis={false}
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<Box {...useProps}>
				<wp.serverSideRender
					key="ur-gutenberg-membership-listing-server-side-renderer"
					block={blockName}
					attributes={ssrAttributes}
				/>
			</Box>
		</>
	);
};

export default Edit;
