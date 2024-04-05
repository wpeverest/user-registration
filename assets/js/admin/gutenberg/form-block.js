"use strict";

import React from "react";

/* global ur_form_block_data, wp */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor ? wp.blockEditor : wp.editor;
const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;
const { TextControl, SelectControl, PanelBody, Placeholder, RadioControl, Notice } =
	wp.components;
const UserRegistrationIcon = createElement(
	"svg",
	{ width: 24, height: 24, viewBox: "0 0 32 32" },
	createElement("path", {
		fill: "currentColor",
		d: "M27.58 4a27.9 27.9 0 0 0-5.17 4 27 27 0 0 0-4.09 5.08 33.06 33.06 0 0 1 2 4.65A23.78 23.78 0 0 1 24 12.15V18a8 8 0 0 1-5.89 7.72l-.21.05a27 27 0 0 0-1.9-8.16A27.9 27.9 0 0 0 9.59 8a27.9 27.9 0 0 0-5.17-4L4 3.77V18a12 12 0 0 0 9.93 11.82h.14a11.72 11.72 0 0 0 3.86 0h.14A12 12 0 0 0 28 18V3.77zM8 18v-5.85a23.86 23.86 0 0 1 5.89 13.57A8 8 0 0 1 8 18zm8-16a3 3 0 1 0 3 3 3 3 0 0 0-3-3z",
	})
);
const { i18n, forms, logo_url } =
	typeof ur_form_block_data !== "undefined" && ur_form_block_data;
const { title, form_select, form_settings, deprecated_notice } =
	typeof i18n !== "undefined" && i18n;

registerBlockType("user-registration/form-selector", {
	title: title,
	icon: UserRegistrationIcon,
	category: "widgets",
	attributes: {
		formId: {
			type: "string",
		},
		formType: {
			type: "string",
		},
		shortcode: {
			type: "string",
		},
		redirectUrl: {
			type: "string",
		},
		logoutUrl: {
			type: "string",
		},
	},
	supports:{
		"inserter": false
	},
	edit(props) {
		const {
			attributes: {
				formId = "",
				formType = "registration_form",
				shortcode = "",
				redirectUrl = "",
				logoutUrl = "",
			},
			setAttributes,
		} = props;
		const formOptions = Object.keys(forms).map((index) => ({
			value: Number(index),
			label: forms[index],
		}));
		let jsx;
		formOptions.unshift({
			value: "",
			label: form_select,
		});
		function selectForm(value) {
			setAttributes({ formType: value });
		}
		function selectRegistrationForm(value) {
			setAttributes({ formId: value });
		}
		function selectLoginForm(value) {
			setAttributes({ shortcode: value });
		}
		function enterRedirectURL(value) {
			setAttributes({ redirectUrl: value });
		}
		function enterLogoutURL(value) {
			setAttributes({ logoutUrl: value });
		}
		jsx = [
			<InspectorControls key="ur-gutenberg-form-selector-inspector-controls">
				<PanelBody title={form_settings}>
					<RadioControl
						key="ur-gutenberg-form-selector-radio-control"
						selected={formType}
						options={[
							{
								label: "Registration Form",
								value: "registration_form",
							},
							{ label: "Login Form", value: "login_form" },
						]}
						onChange={selectForm}
					/>
					{formType === "registration_form" ? (
						<SelectControl
							key="ur-gutenberg-form-selector-registration-form"
							value={formId}
							options={formOptions}
							onChange={selectRegistrationForm}
						/>
					) : (
						[
							<SelectControl
								key="ur-gutenberg-form-selector-login-form"
								value={shortcode}
								options={[
									{ label: "Select Shortcode", value: "" },
									{
										label: "Login Shortcode",
										value: "user_registration_login",
									},
									{
										label: "My Account Shortcode",
										value: "user_registration_my_account",
									},
								]}
								onChange={selectLoginForm}
							/>,
							<TextControl
								key="ur-gutenberg-form-selector-redirect-url"
								label="Redirect URL"
								value={redirectUrl}
								onChange={enterRedirectURL}
							/>,
							<TextControl
								key="ur-gutenberg-form-selector-logout-url"
								label="Logout URL"
								value={logoutUrl}
								onChange={enterLogoutURL}
							/>,
						]
					)}
				</PanelBody>
			</InspectorControls>,
		];
		jsx.push(
		<Notice status="warning" isDismissible={false}>
			<p>{i18n.deprecated_notice}</p>
    	</Notice>
		)
		if (formId || shortcode !== "") {
			jsx.push(
				<ServerSideRender
					key="ur-gutenberg-form-selector-server-side-renderer"
					block="user-registration/form-selector"
					attributes={props.attributes}
				/>
			);
		} else {
			jsx.push(
				<Placeholder
					key="ur-gutenberg-form-selector-wrap"
					className="ur-gutenberg-form-selector-wrap"
				>
					<img src={logo_url} />
					<h2>{title}</h2>
					<RadioControl
						key="ur-gutenberg-form-selector-radio-control"
						selected={formType}
						options={[
							{
								label: "Registration Form",
								value: "registration_form",
							},
							{ label: "Login Form", value: "login_form" },
						]}
						onChange={selectForm}
					/>
					{formType === "registration_form" ? (
						<SelectControl
							key="ur-gutenberg-form-selector-select-control"
							value={formId}
							options={formOptions}
							onChange={selectRegistrationForm}
						/>
					) : (
						<SelectControl
							key="ur-gutenberg-form-selector-select-control"
							selected={shortcode}
							options={[
								{ label: "Select Shortcode", value: "" },
								{
									label: "Login Shortcode",
									value: "user_registration_login",
								},
								{
									label: "My Account Shortcode",
									value: "user_registration_my_account",
								},
							]}
							onChange={selectLoginForm}
						/>
					)}
				</Placeholder>
			);
		}
		return jsx;
	},

	save() {
		return null;
	},
});
