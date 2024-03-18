import React from "react";
import { __ } from "@wordpress/i18n";
import { Box } from "@chakra-ui/react";
import metadata from "./block.json";

import { useBlockProps } from "@wordpress/block-editor";
const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

const Edit = (props) => {
	const useProps = useBlockProps();
	const blockName = metadata.name;

	return (
		<>
			<Box {...useProps}>
				<ServerSideRender
					key="ur-gutenberg-login-form-server-side-renderer"
					block={blockName}
					attributes={props.attributes}
				/>
			</Box>
		</>
	);
};

export default Edit;
