import React, { Component } from "react";
import metadata from "./block.json";
import { Icon } from "./../../components/Icon";
import Edit from "./Edit";
import Save from "./Save";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: Icon,
	edit: Edit,
	save: Save,
};
