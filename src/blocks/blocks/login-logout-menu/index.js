import React, { Component } from "react";
import metadata from "./block.json";
import { LoginLogoutIcon } from "./../../components/Icon";
import Edit from "./Edit";
export const name = metadata.name;
export const settings = {
	...metadata,
	icon: LoginLogoutIcon,	
	edit: Edit
};
