import { __ } from "@wordpress/i18n";
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";

const Save = ({ attributes }) => {
console.log(attributes,'attributes');

    let param = "";
    if (attributes.accessSpecificRoles !== "") {
		const roles = Array.isArray(attributes.accessSpecificRoles)
		? attributes.accessSpecificRoles.join(',')
		: attributes.accessSpecificRoles;
        param = ` access_role="${roles}"`;
    }
console.log(param,'param');

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
