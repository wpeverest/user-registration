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
	Divider,
	Stack,
} from "@chakra-ui/react";
import { Icon } from "./components/Icon";

import { SelectControl, Placeholder, PanelBody } from "@wordpress/components";

import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { ChakraProvider } from "@chakra-ui/react";

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
		<ChakraProvider>
			<Box {...useProps} maxW="sm" borderWidth="1px" borderRadius="lg">
				<InspectorControls key="ur-gutenberg-form-selector-inspector-controls">
					<PanelBody
						title={__(
							"User Registration Forms",
							"user-registration",
						)}
					>
						<SelectControl
							key="ur-gutenberg-form-selector-registration-form"
							value={formId}
							options={[
								{
									label: __(
										"Select a Form",
										"user-registration",
									),
									value: "",
								},
								...formOptions,
							]}
							onChange={selectRegistrationForm}
						/>
					</PanelBody>
				</InspectorControls>
				<Card>
					<CardBody>
						<Center>
							<Image src={logoUrl} />
						</Center>
						<Center>
							<Heading as="h3" ml={5}>
								{__(
									"User Registration Forms",
									"user-registration",
								)}
							</Heading>
						</Center>
						<Center>
							<Stack spacing="3">
								<Text fontSize="sm" as="i">
									{__(
										"Select a registration form name to display one of your form.",
										"user-registration",
									)}
								</Text>
							</Stack>
						</Center>
						<Center>
							<Box w="sm" m="4">
								<SelectControl
									key="ur-gutenberg-form-selector-select-control"
									value={formId}
									options={[
										{
											label: __(
												"Select a Form",
												"user-registration",
											),
											value: "",
										},
										...formOptions,
									]}
									onChange={selectRegistrationForm}
								/>
							</Box>
						</Center>
					</CardBody>
				</Card>
			</Box>
		</ChakraProvider>
	);
};

export default Edit;
