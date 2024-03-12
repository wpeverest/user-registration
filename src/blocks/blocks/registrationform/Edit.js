import React from "react";
import {Button} from "@wordpress/components";

/* global ur_form_block_data, wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor ? wp.blockEditor : wp.editor;

const { TextControl, SelectControl, PanelBody, Placeholder, RadioControl } =
	wp.components;


const Edit = () => {
	return (
		<div className="user-registration-block">
			<TextControl
				key="ur-gutenberg-form-selector-redirect-url"
				label="Redirect URL"
				value=""
			/>
			<Button>Click me</Button>
		</div>
	);
};

export default Edit;
