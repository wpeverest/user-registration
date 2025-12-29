import metadata from "./../../block.json";
import Save from "./Save";

const v1 = {
	attributes: {
		...metadata.attributes,
		orientation: {
			type: "object"
		},
		enableCustomRestrictionMessage: {
			type: "boolean",
			default: true
		}
	},
	supports: metadata.supports,
	save: Save
};

export default v1;
