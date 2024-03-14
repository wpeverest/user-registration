import React, { useState, useEffect } from "react";
import { Icon } from "./components/Icon";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

import { SelectControl, Placeholder, PanelBody } from "@wordpress/components";

import {
	RichText,
	InspectorControls,
	useBlockProps,
} from "@wordpress/block-editor";

/* global _UR_BLOCKS_ */
const { urRestApiNonce, restURL, logoUrl } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = (props) => {
	const useProps = useBlockProps();
	const {
		attributes: { formId = "" },
		setAttributes,
	} = props;

	const [formList, setFormList] = useState("");

	useEffect(() => {
		const fetchData = async () => {
			if (!formList) {
				try {
					const res = await apiFetch({
						path:
							restURL +
							"user-registration/v1/gutenberg-blocks/form-list",
						method: "GET",
						headers: {
							"X-WP-Nonce": urRestApiNonce,
						},
					});
					if (res.success) {
						setFormList(res.form_lists);
					}
				} catch (error) {
					console.error("Error fetching data:", error);
				}
			}
		};

		fetchData();
	}, []);

	const formOptions = Object.keys(formList).map((index) => ({
		value: Number(index),
		label: formList[index],
	}));
	const selectRegistrationForm = (id) => {
		setAttributes({ formId: id });
	};

	return (
		<div {...useProps}>
			<InspectorControls key="ur-gutenberg-form-selector-inspector-controls">
				<PanelBody
					title={__("User Registration Forms", "user-registration")}
				>
					<SelectControl
						key="ur-gutenberg-form-selector-registration-form"
						value={formId}
						options={[
							{
								label: __("Select a Form", "user-registration"),
								value: "",
							},
							...formOptions,
						]}
						onChange={selectRegistrationForm}
					/>
				</PanelBody>
			</InspectorControls>
			<Placeholder
				key="ur-gutenberg-form-selector-wrap"
				className="ur-gutenberg-form-selector-wrap"
			>
				<img src={logoUrl} />
				<h2>{__("User Registration Forms", "user-registration")}</h2>

				<SelectControl
					key="ur-gutenberg-form-selector-select-control"
					value={formId}
					options={[
						{
							label: __("Select a Form", "user-registration"),
							value: "",
						},
						...formOptions,
					]}
					onChange={selectRegistrationForm}
				/>
			</Placeholder>
		</div>
	);
};

export default Edit;
