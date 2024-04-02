import React, { Component } from "react";
import metadata from "./block.json";
import { LoginForm } from "./../../components/Icon";
import Edit from "./Edit";
import Save from "./Save";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: LoginForm,
	edit: Edit,
	save: Save,
};
