import { registerBlockType } from "@wordpress/blocks";
import { applyFilters } from "@wordpress/hooks";

import * as registrationform from "./registrationform";
import * as loginform from "./loginform";
import * as myaccount from "./myaccount";
import * as editprofile from "./editprofile";
import * as editpassword from "./editpassword";

let blocks = [
	registrationform,
	loginform,
	myaccount,
	editprofile,
	editpassword,
];
blocks = applyFilters("user-registration.blocks", blocks);
/**
 * The function "registerBlocks" iterates over an array of blocks and calls the
 * "register" method on each block.
 */
export const registerBlocks = () => {
	for (const block of blocks) {
		const settings = applyFilters(
			"user-registration.blocks.metadata",
			block.settings,
		);
		settings.edit = applyFilters(
			"user-registration.blocks.edit",
			settings.edit,
			settings,
		);
		//Register the blocks.
		registerBlockType(block.name, settings);
	}
};

export default registerBlocks;
