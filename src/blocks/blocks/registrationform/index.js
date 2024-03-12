import React, { Component } from "react";
import metadata from "./block.json";
import { Icon } from "./components/Icon";
import Edit from "./Edit";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: Icon,
	edit: Edit,
	save: function (props) {
		return "User registraion form block";
	},
};
