import React, { Component } from "react";
import metadata from "./block.json";
import Edit from "./Edit";
import Save from "./Save";
import { RegistrationForm } from "./../../components/Icon";

export const name = metadata.name;
export const settings = {
	...metadata,
	icon: RegistrationForm,
	edit: Edit,
	save: Save,
};
