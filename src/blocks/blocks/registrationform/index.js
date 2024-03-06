import React, { Component } from "react";
import metadata from "./block.json";
import { Icon } from "./components/Icon";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: Icon,
	edit: function (props) {
		return "User registraion form block";
	},
	save: function (props) {
		return "User registraion form block";
	},
};
