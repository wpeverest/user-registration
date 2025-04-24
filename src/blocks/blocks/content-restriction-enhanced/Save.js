import { __ } from "@wordpress/i18n";
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";

const Save = ({ attributes }) => {

    let params = "";

    if (attributes.accessSpecificRoles !== "") {
        const roles = Array.isArray(attributes.accessSpecificRoles)
            ? attributes.accessSpecificRoles.join(',')
            : attributes.accessSpecificRoles;
        params += ` access_specific_role="${roles}"`;
    }

	if (attributes.accessMembershipRoles){
		const membershipRoles = Array.isArray(attributes.accessMembershipRoles)
   	 ? attributes.accessMembershipRoles.map(r => r.trim()).join(',')
    	: String(attributes.accessMembershipRoles).trim();
		params += ` access_membership_role="${membershipRoles}"`;
	}

	

    if (attributes.accessControl !== "") {
        params += ` access_control="${attributes.accessControl}"`;
    }

    if (attributes.accessAllRoles !== "") {
        params += ` access_all_roles="${attributes.accessAllRoles}"`;
    }


    if (attributes.accessAllRoles !== "") {
		const escapedMessage = attributes.message.replace(/"/g, "&quot;").replace(/\n/g, " ");
		params += ` message="${escapedMessage}"`;
    }

	if (attributes.enableContentRestriction) {
        params += ` enable_content_restriction="${attributes.enableContentRestriction}"`;
    }


    const blockProps = useBlockProps.save();

    return (
        <>
            [urcr_restrict{params}]
            <div {...blockProps}>
                <InnerBlocks.Content />
            </div>
            [/urcr_restrict]
        </>
    );
};

export default Save;
