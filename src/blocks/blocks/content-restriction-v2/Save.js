import { __ } from "@wordpress/i18n";
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";
const Save = (props) => {
	const blockProps = useBlockProps.save();

	return <InnerBlocks.Content {...blockProps} />;
};

export default Save;
