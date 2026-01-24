import { __ } from "@wordpress/i18n";
import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";

const Save = ({ attributes }) => {
	const {
		accessAllRoles,
		accessSpecificRoles,
		accessMembershipRoles,
		accessControl,
		message,
		enableContentRestriction
	} = attributes;

	const escapeAttribute = (str) =>
		String(str)
			.replace(/&/g, "&amp;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;");

	let params = "";

	if (accessSpecificRoles && accessSpecificRoles.length > 0) {
		const roles = Array.isArray(accessSpecificRoles)
			? accessSpecificRoles.join(",")
			: accessSpecificRoles;
		params += ` access_specific_role="${escapeAttribute(roles)}"`;
	}

	if (accessMembershipRoles && accessMembershipRoles.length > 0) {
		const membershipRoles = Array.isArray(accessMembershipRoles)
			? accessMembershipRoles.map((r) => r.trim()).join(",")
			: String(accessMembershipRoles).trim();
		params += ` access_membership_role="${escapeAttribute(
			membershipRoles
		)}"`;
	}

	if (accessControl) {
		params += ` access_control="${escapeAttribute(accessControl)}"`;
	}

	if (accessAllRoles) {
		params += ` access_all_roles="${escapeAttribute(accessAllRoles)}"`;
	}

	if (enableContentRestriction) {
		params += ` enable_content_restriction="${escapeAttribute(
			enableContentRestriction
		)}"`;
	}

	if (message) {
		params += ` message="${escapeAttribute(message)}"`;
	}

	const blockProps = useBlockProps.save();

	return (
		<>
			{`[urcr_restrict${params}]`}
			<div {...blockProps}>
				<InnerBlocks.Content />
			</div>
			{`[/urcr_restrict]`}
		</>
	);
};

export default Save;
