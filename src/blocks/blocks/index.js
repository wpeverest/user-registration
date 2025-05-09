import { registerBlockType } from "@wordpress/blocks";
import { applyFilters } from "@wordpress/hooks";

import * as registrationForm from "./registration-form";
import * as loginForm from "./login-form";
import * as myaccount from "./myaccount";
import * as editProfile from "./edit-profile";
import * as editPassword from "./edit-password";
import * as contentRestrictionEnhanced from "./content-restriction-enhanced";
import * as membershipListing from "./membership-listing";
import * as thankYou from "./thank-you";

/* global _UR_BLOCKS_ */
const { iscRestrictionActive } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

let blocks = [
	registrationForm,
	loginForm,
	myaccount,
	editProfile,
	editPassword,
	membershipListing,
	thankYou
];

if (iscRestrictionActive) {
	blocks.push(contentRestrictionEnhanced);
}

blocks = applyFilters("user-registration.blocks", blocks);
/**
 * The function "registerBlocks" iterates over an array of blocks and calls the
 * "register" method on each block.
 */
export const registerBlocks = () => {
	for (const block of blocks) {
		const settings = applyFilters(
			"user-registration.blocks.metadata",
			block.settings
		);
		settings.edit = applyFilters(
			"user-registration.blocks.edit",
			settings.edit,
			settings
		);
		//Register the blocks.
		registerBlockType(block.name, settings);
	}
};

export default registerBlocks;
