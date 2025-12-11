import React, { useState, useEffect, useMemo } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
	Box,
	ChakraProvider,
	extendTheme,
	Flex,
	FormControl,
	FormLabel
} from "@chakra-ui/react";
import { SelectControl, PanelBody, ToggleControl } from "@wordpress/components";
import {
	InspectorControls,
	useBlockProps,
	InnerBlocks
} from "@wordpress/block-editor";
import Select from "react-select";
import { Editor } from "@tinymce/tinymce-react";

/* global _UR_BLOCKS_ */
const { urRestApiNonce, restURL, urcrConfigurl, urcrGlobalRestrictionMsgUrl } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

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
		CustomRestrictionMessage,
		enableCustomRestrictionMessage,
		contentRule
	} = attributes;

	// ✅ Block wrapper for InnerBlocks must be a plain div
	const blockProps = useBlockProps({
		className: "ur-content-restriction-wrapper"
	});

	const [contentRules, setContentRules] = useState({});

	useEffect(() => {
		const fetchData = async () => {
			try {
				const [contentRules] = await Promise.all([
					apiFetch({
						path: `${restURL}user-registration/v1/gutenberg-blocks/get-content-rules`,
						headers: { "X-WP-Nonce": urRestApiNonce }
					})
				]);
				if (contentRules.success)
					setContentRules(contentRules.rule_lists);
			} catch (error) {
				console.error(
					"Data fetch failed in Content Restriction block:",
					error
				);
			}
		};
		fetchData();
	}, []);

	const contentRuleOptions = useMemo(
		() =>
			Object.entries(contentRules).map(([key, rule]) => ({
				value: rule.ID,
				label: rule.post_title
			})),
		[contentRules]
	);

	return (
		<ChakraProvider theme={extendTheme()}>
			<InspectorControls>
				<PanelBody>
					<Box>
						<SelectControl
							className="urcr-block-restriction-rule-select"
							label={__(
								"Block Restriction Rules",
								"user-registration"
							)}
							value={Number(contentRule)}
							options={[
								{ label: "Select a content rule", value: "" },
								...contentRuleOptions
							]}
							onChange={(val) =>
								setAttributes({ contentRule: Number(val) })
							}
						/>

						<div className="urcr-config-link">
							<a
								className="link"
								href={urcrConfigurl}
								target="__blank"
							>
								{__(
									"Setup your restriction rules from here",
									"user-registration"
								)}
							</a>
						</div>

						<ToggleControl
							className="urcr-custom-restriction-msg-toggle"
							label={__(
								"Custom restriction message",
								"user-registration"
							)}
							checked={enableCustomRestrictionMessage}
							onChange={(val) =>
								setAttributes({
									enableCustomRestrictionMessage: val
								})
							}
						/>

						{!enableCustomRestrictionMessage && (
							<div className="urcr-config-link">
								<a
									className="link"
									href={urcrGlobalRestrictionMsgUrl}
									target="__blank"
								>
									{__(
										"Setup global restriction message from here",
										"user-registration"
									)}
								</a>
							</div>
						)}

						{enableCustomRestrictionMessage && (
							<FormControl mt={6}>
								<FormLabel sx={labelStyle}>
									{__(
										"Restricted Content Message",
										"user-registration"
									)}
								</FormLabel>
								<Editor
									value={CustomRestrictionMessage}
									onEditorChange={(val) =>
										setAttributes({
											CustomRestrictionMessage: val
										})
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
						)}
					</Box>
				</PanelBody>
			</InspectorControls>

			<Box {...blockProps} p={"1px"}>
				<Flex
					className="ur-note"
					bg={"gray.200"}
					justify="center"
					align="center"
					h="40px"
				>
					<span className="dashicons dashicons-lock" />
					<span>
						{__(
							"The contents of this block are protected",
							"user-registration"
						)}
					</span>
				</Flex>

				<InnerBlocks templateLock={false} />
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
