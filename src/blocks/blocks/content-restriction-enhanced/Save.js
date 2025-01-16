import { __ } from "@wordpress/i18n";
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";

const Save = ({ attributes }) => {
    let param = "";
    if (attributes.accessRole !== "") {
        param = ` access_role="${attributes.accessRole}"`;
    }
    const blockProps = useBlockProps.save();

    return (
        <>
            [urcr_restrict{param}]
            <div {...blockProps}>
                <InnerBlocks.Content />
            </div>
            [/urcr_restrict]
        </>
    );
};

export default Save;
