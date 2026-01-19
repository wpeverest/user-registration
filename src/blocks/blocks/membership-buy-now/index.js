import React, { Component } from "react";
import metadata from "./block.json";
import { MembershipBuyNow } from "./../../components/Icon";
import Edit from "./Edit";
import "./editor.scss";
import "./style.scss";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: MembershipBuyNow,
	edit: Edit,
	save: () => null
};
