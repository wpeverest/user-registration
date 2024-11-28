/**
 *  External Dependencies
 */
import React, { useState, useEffect, Fragment } from "react";
import {
	Flex,
	Text,
	Box,
	HStack,
	Image,
	useRadio,
	useRadioGroup
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/gettingStartedContext";

const RegistrationType = () => {
	/* global _UR_WIZARD_ */
	const { onBoardIconsURL } =
		typeof _UR_WIZARD_ !== "undefined" && _UR_WIZARD_;
	const [{ registrationType }, dispatch] = useStateValue();

	const [selectedType, setSelectedType] = useState(
		"user_registration_normal_registration"
	);

	const registrationTypeData = {
		id: "user_registration_registration_type",
		options: {
			user_registration_normal_registration: {
				label: __("Normal registration", "user-registration"),
				desc: __(
					"Create normal registration system without membership.",
					"user-registration"
				),
				image: `${onBoardIconsURL}/without-membership.png`
			},
			user_registration_membership_registration: {
				label: __("Membership registration", "user-registration"),
				desc: __(
					"Create normal registration system with membership.",
					"user-registration"
				),
				image: `${onBoardIconsURL}/with-membership.png`
			}
		}
	};

	const RadioCard = (props) => {
		const { radioProps, label, desc, image, identifier, borderColor } =
			props;
		const { getInputProps } = useRadio(radioProps);

		const input = getInputProps();

		return (
			<Box
				as="label"
				border={`2px solid ${borderColor}`}
				borderRadius="12px"
				padding="20px"
			>
				<input {...input} />
				<Flex direction="column" align="center" gap="16px">
					<Image src={image} width="322px" height="215px" />
					<Flex direction="column" align="center" gap="6px">
						<Text
							fontSize="18px"
							fontWeight="600"
							lineHeight="28px"
							color="#222222"
						>
							{label}
						</Text>
						<Text
							fontSize="16px"
							fontWeight="400"
							lineHeight="26px"
							color="#383838"
							textAlign="center"
						>
							{desc}
						</Text>
					</Flex>
				</Flex>
			</Box>
		);
	};

	useEffect(() => {
		dispatch({
			type: actionTypes.GET_REGISTRATION_TYPE,
			registrationType: selectedType
		});
	}, [selectedType]);

	const CustomRadioGroup = ({ registrationTypeData }) => {
		const { getRootProps, getRadioProps } = useRadioGroup({
			name: registrationTypeData.id,
			onChange: (data) => {
				setSelectedType(
					Object.keys(registrationTypeData.options)[data]
				);
			}
		});

		const group = getRootProps();

		return (
			<HStack {...group} gap="32px" flex={"1 0 60%"}>
				{Object.keys(registrationTypeData.options).map((value, key) => {
					const radioOptions = registrationTypeData.options[value];

					return (
						<RadioCard
							key={value}
							radioProps={getRadioProps({
								value: key.toString()
							})}
							label={radioOptions["label"]}
							desc={radioOptions["desc"]}
							image={radioOptions["image"]}
							identifier={value}
							borderColor={
								selectedType === value ? "#475BB2" : "#E1E1E1"
							}
						/>
					);
				})}
			</HStack>
		);
	};

	return (
		<Fragment>
			<CustomRadioGroup registrationTypeData={registrationTypeData} />
		</Fragment>
	);
};

export default RegistrationType;
