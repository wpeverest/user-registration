import React, { Component } from "react";
import metadata from "./block.json";
import { MembershipListing } from "./../../components/Icon";
import Edit from "./Edit";
import Save from "./Save";
import "./editor.scss";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: MembershipListing,
	edit: Edit,
	save: Save
};
