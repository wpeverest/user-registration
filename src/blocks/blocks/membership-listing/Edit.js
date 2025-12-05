import React, { useEffect, useState, useRef } from "react";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";
import {
	InspectorControls,
	useBlockProps,
	useSettings
} from "@wordpress/block-editor";
import {
	Disabled,
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
	FontSizePicker,
	__experimentalUnitControl as UnitControl,
	__experimentalBoxControl as BoxControl,
	SelectControl as FontFamilySelect
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

const { urRestApiNonce, restURL } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

// Helper function to map options for SelectControl
const mapOptions = (list) =>
	Object.keys(list).map((index) => ({
		value: Number(index),
		label: list[index]
	}));

/**
 * Edit component for the membership listing block.
 *
 * @param {Object} props The props received from the parent component.
 * @return {JSX.Element} The Edit component.
 */
const Edit = (props) => {
	const {
		attributes: {
			id,
			redirection_page_id,
			group_id,
			thank_you_page_id,
			type,
			button_text,
			columnNumber,
			openInNewTab,
			showDescription,
			buttonTextColor,
			buttonBgColor,
			buttonTextHoverColor,
			buttonBgHoverColor,
			radioColor
		},
		setAttributes
	} = props;

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
	const [isBlockList, setIsBlockList] = useState(type === "block");

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
		} catch (error) {
			console.error("Error fetching data:", error);
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

	const onGroupTypeChange = (id) => {
		setAttributes({ type: id });
		setIsBlockList("block" === id);
	};
	const onButtonTextChange = (val) => {
		setAttributes({ button_text: val });
	};
	// generate a strong uuid
	const generateUUID = () => {
		return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
			/[xy]/g,
			function (c) {
				const r = (Math.random() * 16) | 0,
					v = c === "x" ? r : (r & 0x3) | 0x8;
				return v.toString(16);
			}
		);
	};
	const [themeColors] = useSettings("color.palette.theme");
	const [defaultColors] = useSettings("color.palette.default");
	const [customColors] = useSettings("color.palette.custom");
	// Handle block removal (cleanup)
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

	// Fetch data on component mount
	useEffect(() => {
		fetchData();
	}, []);

	// Verify pages on component mount
	useEffect(() => {
		verifyPagesOnLoad();
	}, []);

	useEffect(() => {
		if (!id) {
			const uid = generateUUID();
			setAttributes({ id: uid });
		}
	}, []);

	const HoverColorControl = ({ label, colorValue, onChange }) => (
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

	// Render the component
	return (
		<>
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
						onChange={(id) =>
							setAttributes({ redirection_page_id: id }) ||
							verifyPage({
								id,
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
						onChange={(id) =>
							setAttributes({ thank_you_page_id: id }) ||
							verifyPage({
								id,
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
						onChange={(id) => setAttributes({ group_id: id })}
						__nextHasNoMarginBottom={true}
						__next40pxDefaultSize
					/>
					<a href="#">
						{__(
							"Configure membership groups from here",
							"user-registration"
						)}
					</a>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__("Open in a new tab", "user-registration")}
						checked={openInNewTab}
						onChange={(value) => {
							setAttributes({
								openInNewTab: value
							});
						}}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__("Show description", "user-registration")}
						checked={showDescription}
						onChange={(value) => {
							setAttributes({
								showDescription: value
							});
						}}
					/>
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
						colorValue={buttonTextColor}
						onChange={(color) =>
							setAttributes({ buttonTextColor: color })
						}
					/>
					<HoverColorControl
						label={__("Button Background", "user-registration")}
						colorValue={buttonBgColor}
						onChange={(color) =>
							setAttributes({ buttonBgColor: color })
						}
					/>
					<HoverColorControl
						label={__("Button Text Hover", "user-registration")}
						colorValue={buttonTextHoverColor}
						onChange={(color) =>
							setAttributes({ buttonTextHoverColor: color })
						}
					/>
					<HoverColorControl
						label={__(
							"Button Background Hover",
							"user-registration"
						)}
						colorValue={buttonBgHoverColor}
						onChange={(color) =>
							setAttributes({ buttonBgHoverColor: color })
						}
					/>
					<HoverColorControl
						label={__("Radio Color", "user-registration")}
						colorValue={radioColor}
						onChange={(color) =>
							setAttributes({ radioColor: color })
						}
					/>
				</>
			</InspectorControls>

			<Box {...useProps}>
				<Disabled>
					<wp.serverSideRender
						key="ur-gutenberg-membership-listing-server-side-renderer"
						block={blockName}
						attributes={props.attributes}
					/>
				</Disabled>
			</Box>
		</>
	);
};

export default Edit;
