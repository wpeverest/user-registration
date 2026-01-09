import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
	Image,
	Box,
	Heading,
	Center,
	Card,
	CardBody,
	Text,
	Stack
} from "@chakra-ui/react";

import { SelectControl, PanelBody, Disabled } from "@wordpress/components";

import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { ChakraProvider } from "@chakra-ui/react";
import metadata from "./block.json";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

/* global _UR_BLOCKS_ */
const { urRestApiNonce, logoUrl } =
	typeof _UR_BLOCKS_ !== "undefined" && _UR_BLOCKS_;

const Edit = (props) => {
	const useProps = useBlockProps();
	const {
		attributes: { formId },
		setAttributes
	} = props;

	const blockName = metadata.name;
	const [formList, setFormList] = useState("");
	const [formState, setFormState] = useState(!!formId);
	const [userState, setUserState] = useState("logged_out");

	useEffect(() => {
		const fetchData = async () => {
			if (!formList) {
				try {
					const res = await apiFetch({
						path: "user-registration/v1/gutenberg-blocks/form-list",
						method: "GET",
						headers: {
							"X-WP-Nonce": urRestApiNonce
						}
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
		label: formList[index]
	}));

	useEffect(() => {
		if (formId === "initial" && formOptions.length > 0) {
			setAttributes({ formId: String(formOptions[0].value) });
			setFormState(String(formOptions[0].value));
		}
	}, [formId, formOptions]);

	const selectRegistrationForm = (id) => {
		setAttributes({ formId: id });
		setFormState(id);
	};

	return (
		<ChakraProvider>
			<Box {...useProps} borderWidth="1px" borderRadius="lg">
				<InspectorControls key="ur-gutenberg-registration-form-inspector-controls">
					<PanelBody
						title={__(
							"User Registration & Membership Forms",
							"user-registration"
						)}
					>
						<SelectControl
							key="ur-gutenberg-registration-form-registration-form"
							value={formId}
							options={[
								{
									label: __(
										"Select a Form",
										"user-registration"
									),
									value: ""
								},
								...formOptions
							]}
							onChange={selectRegistrationForm}
						/>
						{formState && (
							<SelectControl
								key="ur-gutenberg-registration-form-user-login-state"
								label={__("User State", "user-registration")}
								value={userState}
								options={[
									{ label: "Logged In", value: "logged_in" },
									{
										label: "Logged Out",
										value: "logged_out"
									}
								]}
								onChange={setUserState}
							/>
						)}
					</PanelBody>
				</InspectorControls>
				{!formState ? (
					<Card>
						<CardBody>
							<Center>
								<Image src={logoUrl} />
							</Center>
							<Center>
								<Heading as="h3" ml={5}>
									{__(
										"User Registration & Membership Forms",
										"user-registration"
									)}
								</Heading>
							</Center>
							<Center>
								<Stack spacing="3">
									<Text fontSize="sm" as="i">
										{__(
											"Select a registration form name to display one of your form.",
											"user-registration"
										)}
									</Text>
								</Stack>
							</Center>
							<Center>
								<Box w="sm" m="4">
									<SelectControl
										key="ur-gutenberg-registration-form-select-control"
										value={formId}
										options={[
											{
												label: __(
													"Select a Form",
													"user-registration"
												),
												value: ""
											},
											...formOptions
										]}
										onChange={selectRegistrationForm}
									/>
								</Box>
							</Center>
						</CardBody>
					</Card>
				) : (
					<>
						{"initial" !== props.attributes.formId && (
							<Disabled>
								<ServerSideRender
									key="ur-gutenberg-registration-form-server-side-renderer"
									block={blockName}
									attributes={{
										...props.attributes,
										userState
									}}
								/>
							</Disabled>
						)}
					</>
				)}
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
