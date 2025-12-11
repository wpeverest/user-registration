import React, { Component } from "react";
import metadata from "./block.json";
import { ContentRestriction } from "../../components/Icon";
import Edit from "./Edit";
import Save from "./Save";
import "./editor.scss";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: ContentRestriction,
	edit: Edit,
	save: Save
};
