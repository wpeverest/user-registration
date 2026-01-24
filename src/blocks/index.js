import registerBlocks from "./blocks";
import "./editor.scss";
import "./style.scss";

const isProEnabled =
	process.env.UR_PRO === "true" || process.env.UR_PRO === true;

//Register the blocks.
registerBlocks();

if (isProEnabled) {
	let registerProBlocks;

	try {
		registerProBlocks = require("./blocks/pro/blocks").default;
	} catch (error) {
		console.error("Failed to import registerProBlocks:", error);
	}

	if (registerProBlocks) {
		registerProBlocks();
	}
}
