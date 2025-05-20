import React, {useRef, useState} from 'react';
import {Box, Flex, Tooltip} from '@chakra-ui/react';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, TextControl, CheckboxControl, Disabled, PanelRow} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import metadata from "../thank-you/block.json";
import {Editor} from '@tinymce/tinymce-react';
import ServerSideRender from '@wordpress/server-side-render';


/**
 * Edit component for the membership thank you block.
 *
 * @param {Object} props The props received from the parent component.
 * @return {JSX.Element} The Edit component.
 */
const Edit = (props) => {
	const {
		attributes: {header, footer, notice_message, transaction_info, show_notice_1, show_notice_2},
		setAttributes,
	} = props;
	const useProps = useBlockProps();
	// Verify a page
	const blockName = metadata.name;
	const defaultNoticeOne = metadata.attributes.notice_message.default;
	const defaultNoticeTwo = metadata.attributes.transaction_info.default;

	// Render the component
	return (
		<>
			<Box {...useProps}>
				<InspectorControls key="ur-gutenberg-thank-you-inspector-controls">
					<PanelBody initialOpen={false} title={__('Header Content', 'user-registration')}>
						<Editor
							value={header}
							onEditorChange={(value) => setAttributes({header: value})}

							init={{
								height: 200,
								menubar: false,
								plugins: "link lists textcolor colorpicker hr",
								toolbar: `
										  undo redo |
										  styleselect | fontselect fontsizeselect |
										  bold italic underline strikethrough |
										  forecolor backcolor |
										  hr |
										  alignleft aligncenter alignright alignjustify |
										  bullist numlist outdent indent |
										  link image emoticons charmap |
										  removeformat
										`,
								content_style:
									"body { font-family:Arial,sans-serif; font-size:14px }"
							}}
						/>


					</PanelBody>
					<PanelBody initialOpen={false} title={__('Payment Information', 'user-registration')}>
						<p style={{marginBottom: '8px', fontSize: '13px', color: '#555'}}>
							{__("This information is shown only when a payment is processed during registration.", "user-registration")}
						</p>
						<PanelRow>
							<Flex align="center" gap="3" width="100%">
								<Box>
									<CheckboxControl
										key="ur-gutenberg-notice-1"
										checked={show_notice_1}
										onChange={(value) => setAttributes({show_notice_1: value})}
										__nextHasNoMarginBottom
									/>
								</Box>

								<Box flex="1">
									<TextControl
										key="ur-gutenberg-notice-text"
										placeholder={__(defaultNoticeOne, "user-registration")}
										value={__(notice_message, 'user-registration')}
										onChange={(value) => setAttributes({notice_message: value})}
									/>
								</Box>
							</Flex>
						</PanelRow>
						<PanelRow>
							<Flex align="center" gap="3" width="100%">
								<Box>
									<CheckboxControl
										key="ur-gutenberg-notice-2"
										checked={show_notice_2}
										onChange={(value) => setAttributes({show_notice_2: value})}
										__nextHasNoMarginBottom
									/>
								</Box>

								<Box flex="1">
									<TextControl
										placeholder={__(defaultNoticeTwo, 'user-registration')}
										key="ur-gutenberg-transaction-info-text"
										value={__(transaction_info, 'user-registration')}
										onChange={(value) => setAttributes({transaction_info: value})}
									/>
								</Box>
							</Flex>
						</PanelRow>


					</PanelBody>
					<PanelBody initialOpen={false} title={__('Footer Content', 'user-registration')}>
						<Editor
							value={footer}
							onEditorChange={(value) => setAttributes({footer: value})}

							init={{
								height: 200,
								menubar: false,
								plugins: "link lists textcolor colorpicker hr",
								toolbar: `
										  undo redo |
										  styleselect | fontselect fontsizeselect |
										  bold italic underline strikethrough |
										  forecolor backcolor |
										  hr |
										  alignleft aligncenter alignright alignjustify |
										  bullist numlist outdent indent |
										  link image emoticons charmap |
										  removeformat
										`,
								content_style:
									"body { font-family:Arial,sans-serif; font-size:14px }"
							}}
						/>


					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						key="ur-gutenberg-thank-you-server-side-renderer"
						block={blockName}
						attributes={{...props.attributes, is_preview: true}}
					/>
				</Disabled>
			</Box>
		</>
);
};

export default Edit;
