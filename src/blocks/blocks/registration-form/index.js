import React, { Component } from "react";
import metadata from "./block.json";
import Edit from "./Edit";
import Save from "./Save";
import { RegistrationForm } from "./../../components/Icon";

const { oldestForm = 0 } =
	typeof _UR_BLOCKS_ !== "undefined" ? _UR_BLOCKS_ : {};

export const name = metadata.name;

export const settings = {
	...metadata,
	example: {
		...metadata.example,
		attributes: {
			...(metadata.example?.attributes ?? {}),
			...(oldestForm !== 0 ? { formId: oldestForm } : {})
		}
	},
	icon: RegistrationForm,
	edit: Edit,
	save: Save
};
