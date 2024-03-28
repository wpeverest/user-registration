/**
 *  External Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import * as Icon from "../components/Icon/Icon";
import colormag from "../images/colormag.webp";
import evf from "../images/evf.webp";
import magazineBlocks from "../images/magazine-blocks.webp";
import masteriyo from "../images/masteriyo.webp";
import blockart from "../images/blockart-blocks.webp";
import zakra from "../images/zakra.webp";

export const PLUGINS = [
	{
		label: "Masteriyo",
		slug: "learning-management-system/lms.php",
		description: __(
			"Revolutionize e-learning effortlessly with Masteriyo, a WordPress LMS plugin. Sell courses with quizzes, assignments, etc., for a dynamic learning experience.",
			"user-registration"
		),
		type: "plugin",
		image: masteriyo,
		website: "https://masteriyo.com/",
		shortDescription: __(
			"WordPress LMS plugin with Quiz Builder",
			"user-registration"
		),
		logo: Icon.Masteriyo,
		liveDemoURL: "https://masteriyo.demoswp.net/",
	},
	{
		label: "BlockArt Blocks",
		slug: "blockart-blocks/blockart.php",
		description: __(
			"Fuel your digital creativity with BlockArt Blocks, a dynamic collection of custom Gutenberg blocks for designing captivating WordPress sites.",
			"user-registration"
		),
		type: "plugin",
		image: blockart,
		website: "https://wpblockart.com/blockart-blocks/",
		shortDescription: __(
			"Custom Gutenberg Blocks Plugin",
			"user-registration"
		),
		logo: Icon.Blockart,
		liveDemoURL: "https://tastewp.com/template/blockartblocks",
	},
	{
		label: "Everest Forms",
		slug: "everest-forms/everest-forms.php",
		description: __(
			"Manage online communication with Everest Forms, a lightning-fast and secure contact form plugin offering beautiful templates for professional forms.",
			"user-registration"
		),
		type: "plugin",
		image: evf,
		website: "https://everestforms.net/",
		shortDescription: __(
			"User-friendly Contact Form Plugin for WordPress",
			"user-registration"
		),
		logo: Icon.EVF,
		liveDemoURL: "https://everestforms.demoswp.net/",
	},
	{
		label: "Magazine Blocks",
		slug: "magazine-blocks/magazine-blocks.php",
		description: __(
			"Experience advanced Gutenberg blocks with Magazine Blocks, designed for crafting stunning magazine and news websites.",
			"user-registration"
		),
		type: "plugin",
		image: magazineBlocks,
		website: "https://wpblockart.com/magazine-blocks/",
		shortDescription: __(
			"Gutenberg Blocks for Magazine-style Websites",
			"user-registration"
		),
		logo: Icon.MagazineBlocks,
		liveDemoURL: "https://tastewp.com/template/magazineblocks",
	},
];

export const THEMES = [
	{
		label: "Zakra",
		slug: "zakra",
		description: __(
			"Unlock boundless website possibilities with Zakra, a versatile multipurpose theme offering over 40 free starter sites for a tailored web experience.",
			"user-registration"
		),
		type: "theme",
		image: zakra,
		website: "https://zakratheme.com/",
		liveDemoURL: "https://zakratheme.com/demos/#/",
	},
	{
		label: "ColorMag",
		slug: "colormag",
		description: __(
			"Elevate your website's style with Colormag, the go-to choice for news, blogs, and magazines. Embark on a digital spectacle of website-building excellence! ",
			"user-registration"
		),
		type: "theme",
		image: colormag,
		website: "https://themegrill.com/themes/colormag/",
		liveDemoURL: "https://themegrilldemos.com/colormag-demos/#/",
	},
];
