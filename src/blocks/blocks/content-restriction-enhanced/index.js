import React, { Component } from "react";
import metadata from "./block.json";
import { ContentRestriction } from "./../../components/Icon";
import Edit from "./Edit";
import Save from "./Save";

const { isProActive } = typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: ContentRestriction,
	edit: Edit,
	save: Save
};
