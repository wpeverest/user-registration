import { __ } from "@wordpress/i18n";
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
			"WordPress LMS & e-Learning plugin to create and sell online courses. Easy quiz creation with inbuilt quiz builder.",
			"user-registration"
		),
		type: "plugin",
		image: masteriyo,
		website: "https://masteriyo.com/",
		shortDescription: __(
			"WordPress e-Learning Plugin with Quiz Builder.",
			"user-registration"
		),
		logo: Icon.Masteriyo,
		liveDemoURL: "https://masteriyo.demoswp.net/",
	},
	{
		label: "BlockArt Blocks",
		slug: "blockart-blocks/blockart.php",
		description: __(
			"Explore your creativity! Design any type of WordPress page and post with Gutenberg Blocks. Whether you’re a beginner or a skilled designer, we’ve got you covered. ",
			"user-registration"
		),
		type: "plugin",
		image: blockart,
		website: "https://wpblockart.com/blockart-blocks/",
		shortDescription: __("Gutenberg blocks", "user-registration"),
		logo: Icon.Blockart,
		liveDemoURL: "https://tastewp.com/template/blockartblocks",
	},
	{
		label: "Everest Forms",
		slug: "everest-forms/everest-forms.php",
		description: __(
			"Fast, Lightweight & Secure Contact Form plugin. Beautiful & Responsive Pre-Built Templates.",
			"user-registration"
		),
		type: "plugin",
		image: evf,
		website: "https://everestforms.net/",
		shortDescription: __(
			"Quick, Secure Contact Form with Templates.",
			"user-registration"
		),
		logo: Icon.EVF,
		liveDemoURL: "https://everestforms.demoswp.net/",
	},
	{
		label: "Magazine Blocks",
		slug: "magazine-blocks/magazine-blocks.php",
		description: __(
			"Collection of Posts Blocks to build magazine and blog websites. Comes with various dynamic, beautiful, and advanced Gutenberg blocks.",
			"user-registration"
		),
		type: "plugin",
		image: magazineBlocks,
		website: "https://wpblockart.com/magazine-blocks/",
		shortDescription: __(
			"Dynamic Gutenberg Blocks for Magazine/Blog.",
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
			"A powerful and versatile multipurpose theme that makes it easy to create beautiful and professional websites. With over free 40 pre-designed starter demo sites to choose from, you can quickly build a unique and functional site that fits your specific needs.",
			"user-registration"
		),
		type: "theme",
		image: zakra,
		website: "https://zakratheme.com/",
		liveDemoURL: "https://zakratheme.com/",
	},
	{
		label: "ColorMag",
		slug: "colormag",
		description: __(
			"ColorMag is always the best choice when it comes to magazine, news, and blog WordPress themes. You can create elegant and modern websites for news portals, online magazines, and publishing sites.",
			"user-registration"
		),
		type: "theme",
		image: colormag,
		website: "https://themegrill.com/themes/colormag/",
		liveDemoURL: "https://themegrilldemos.com/colormag-demos/#/",
	},
];
