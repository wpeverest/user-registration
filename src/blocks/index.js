import registerBlocks from "./blocks";
import "./editor.scss";
import "./style.scss";

const { isPro } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

//Register the blocks.
registerBlocks();
if (isPro) {
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
