import React, {useRef, useState} from 'react';
import {Box, Flex, Tooltip, Heading, Text, Switch } from '@chakra-ui/react';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, TextControl, CheckboxControl, Disabled, PanelRow, ToggleControl} from '@wordpress/components';
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
		attributes: {header, footer, notice_message, transaction_info, show_notice_1, show_notice_2, show_bank_details, show_heading_icon, show_headline, headline_text, show_redirect_btn },
		setAttributes,
	} = props;
	const useProps = useBlockProps();
	// Verify a page
	const blockName = metadata.name;
	const defaultNoticeOne = metadata.attributes.notice_message.default;
	const defaultNoticeTwo = metadata.attributes.transaction_info.default;

	const SMART_TAGS = [
		{ text: 'Membership Plan Details', value: '{membership_plan_details}' }
	];

	// Render the component
	return (
		<>
			<Box {...useProps}>
				<InspectorControls key="ur-gutenberg-thank-you-inspector-controls">
					<PanelBody initialOpen={true} title={__('Content Settings', 'user-registration')}>

						<PanelRow>
							<ToggleControl
									label={ __( 'Show icon', 'user-registration' ) }
									checked={ show_heading_icon }
									onChange={ ( value ) =>
										setAttributes({ show_heading_icon: value })
									}
								/>
						</PanelRow>

						<PanelRow>
							<Box>
								<ToggleControl
									label={ __( 'Headline', 'user-registration' ) }
									checked={ show_headline }
									onChange={ ( value ) =>
										setAttributes({ show_headline: value })
									}
								/>
								<TextControl
									key="ur-gutenberg-notice-text"
									placeholder={__('Thank you for registering.', "user-registration")}
									value={ headline_text }
									onChange={(value) => setAttributes({headline_text: value})}
									width={'100%'}
								/>
							</Box>
						</PanelRow>

						 <Heading as='h4' size='sm' marginBottom={ '4px'}>
							{__('Main Content', 'user-registration')}
						</Heading>
						<Editor
							value={header}
							onEditorChange={(value) => setAttributes({header: value})}

							init={{
								height: 200,
								menubar: false,
								plugins: "link lists textcolor colorpicker hr",
								toolbar: `
										  undo redo | smarttags |
										  styleselect | fontselect fontsizeselect |
										  bold italic underline strikethrough |
										  forecolor backcolor |
										  hr |
										  alignleft aligncenter alignright alignjustify |
										  bullist numlist outdent indent |
										  link image emoticons charmap |
										  removeformat
										`,
										setup: function (editor) {
											editor.addButton('smarttags', {
												type: 'menubutton',
												text: 'Smart Tags',
												icon: false,
												menu: SMART_TAGS.map((tag) => ({
													text: tag.text,
													onclick: function () {
														editor.insertContent(tag.value);
													},
												})),
											});
										},

								content_style:
									"body { font-family:Arial,sans-serif; font-size:14px }"
							}}
						/>

						<PanelRow>
							<ToggleControl
										label={ __( 'Show bank details', 'user-registration' ) }
										checked={ show_bank_details }
										onChange={ ( value ) =>
											setAttributes({ show_bank_details: value })
										}
									/>
						</PanelRow>

						<PanelRow>
							<ToggleControl
								label={ __( 'Redirect Button', 'user-registration' ) }
								checked={ show_redirect_btn }
								onChange={ ( value ) =>
									setAttributes({ show_redirect_btn: value } )
								}
							/>
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
