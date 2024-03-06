import React from "react";
import metadata from "./block.json";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: "smiley",
	edit: function (props) {
		return "User registraion form block";
	},
	save: function (props) {
		return "User registraion form block";
	},
};
