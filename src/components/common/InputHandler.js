import React, { useEffect } from "react";
import {
	Flex,
	Switch,
	FormLabel,
	Box,
	Text,
	Tooltip,
	useRadio,
	useRadioGroup,
	HStack,
	Image,
	Icon,
} from "@chakra-ui/react";
import { Select } from "chakra-react-select";

import { useStateValue } from "../../context/StateProvider";
import { actionTypes } from "../../context/gettingStartedContext";

function InputHandler({ setting, onBoardIconsURL }) {
	const [{ settings }, dispatch] = useStateValue();

	const renderOptions = () => {
		let newOptionsRef = [];

		if (setting.options) {
			let newSelectedOptionRef = [setting.default];

			Object.keys(setting.options).map((key, value) => {
				if (settings[setting.id]) {
					if (
						typeof settings[setting.id] !== "string" &&
						Object.values(settings[setting.id]).includes(key)
					) {
						newSelectedOptionRef.push(value);
					} else {
						newSelectedOptionRef =
							key === settings[setting.id]
								? [value]
								: newSelectedOptionRef;
					}
				}

				newOptionsRef.default =
					typeof newSelectedOptionRef === "object" &&
					newSelectedOptionRef.filter((value, index) => {
						return newSelectedOptionRef.indexOf(value) === index;
					});

				return newOptionsRef.push({
					label: setting.options[key],
					value: key,
				});
			});
		}

		return newOptionsRef;
	};

	const RadioCard = (props) => {
		const { radioProps, label, identifier, color, backgroundColor } = props;
		const { state, getInputProps, getCheckboxProps } = useRadio(radioProps);

		const input = getInputProps();
		const checkbox = getCheckboxProps();
		return (
			<Box as="label" marginleft="0px !important" marginBottom="0px">
				<input {...input} />
				<Box
					{...checkbox}
					cursor="pointer"
					borderWidth="1px"
					borderRadius="7px"
					boxShadow="md"
					borderColor={
						setting.id ===
						"user_registration_form_setting_minimum_password_strength"
							? "#6B6B6B"
							: "#E9E9E9"
					}
					_checked={{
						bg: backgroundColor,
						borderColor: color,
					}}
					_focus={{
						boxShadow: "outline",
					}}
					px={5}
					py={3}
					style={{
						flex: "1 0 30%",
					}}
				>
					{setting.id ===
						"user_registration_login_options_form_template" ||
					setting.id === "user_registration_form_template" ||
					setting.id === "user_registration_my_account_layout" ? (
						<Flex direction="column" align="center">
							<Image
								src={`${onBoardIconsURL}/${identifier}.png`}
							/>
							<Text
								fontSize="12px"
								fontWeight="600"
								color={state.isChecked && color}
								mt={2}
							>
								{label}
							</Text>
						</Flex>
					) : (
						<Text
							fontSize="14px"
							fontWeight="600"
							color={
								state.isChecked
									? color
									: setting.id ===
									  "user_registration_form_setting_minimum_password_strength"
									? "#6B6B6B"
									: "#212121"
							}
						>
							{label}
						</Text>
					)}
				</Box>
			</Box>
		);
	};

	const handleInputChange = (fieldType, fieldIdentifier, event) => {
		const newChangedValueRef = { ...settings };

		if (fieldType === "checkbox") {
			newChangedValueRef[fieldIdentifier] = event.target.checked
				? "yes"
				: "no";
		} else if (fieldType === "select") {
			newChangedValueRef[fieldIdentifier] = event.value;
		} else if (fieldType === "radio") {
			newChangedValueRef[fieldIdentifier] = Object.keys(setting.options)[
				event
			];
		} else {
			const multiselectValue = [];
			event.map((eve) => {
				multiselectValue.push(eve.value);
			});

			newChangedValueRef[fieldIdentifier] = multiselectValue;
		}

		dispatch({
			type: actionTypes.GET_SETTINGS,
			settings: newChangedValueRef,
		});
	};

	const CarretDownIcon = (props) => (
		<Icon viewBox="0 0 24 24" {...props}>
			<path
				fill="#6B6B6B"
				stroke="#6B6B6B"
				stroke-linecap="round"
				stroke-linejoin="round"
				stroke-width="2"
				d="m6 9 6 6 6-6H6Z"
			/>
		</Icon>
	);

	const renderElement = () => {
		switch (setting.type) {
			case "checkbox":
				return (
					<Switch
						flex={"0 0 60%"}
						className="user-registration-setup-wizard__body--checkbox"
						name={setting.id}
						id={setting.id}
						onChange={(e) =>
							handleInputChange(setting.type, setting.id, e)
						}
						isChecked={settings[setting.id] === "yes"}
						defaultChecked={setting.default}
					/>
				);
			case "select":
				return (
					<Select
						icon={<CarretDownIcon />}
						flex={"0 0 60%"}
						focusBorderColor="blue.500"
						className="user-registration-setup-wizard__body--select"
						name={setting.id}
						id={setting.id}
						options={renderOptions()}
						onChange={(e) =>
							handleInputChange(setting.type, setting.id, e)
						}
						defaultValue={renderOptions()[renderOptions().default]}
						variant="outline"
					/>
				);
			case "multiselect":
				let defaultSelectedOption = [];
				renderOptions().default.map((key) => {
					return defaultSelectedOption.push(renderOptions()[key]);
				});
				return (
					<Select
						icon={<CarretDownIcon />}
						flex={"0 0 60%"}
						isMulti
						focusBorderColor="blue.500"
						className="user-registration-setup-wizard__body--select"
						name={setting.id}
						id={setting.id}
						options={renderOptions()}
						onChange={(e) =>
							handleInputChange(setting.type, setting.id, e)
						}
						defaultValue={defaultSelectedOption}
					/>
				);

			case "radio":
				const reversedOptions = (obj) =>
					Object.fromEntries(
						Object.entries(obj).map((a) => a.reverse())
					);

				var color = {
					activeBackgroundColor: "#F9FAFC",
					activeFontColor: "#475BB2",
				};
				if (
					setting.id ===
						"user_registration_form_setting_minimum_password_strength" &&
					setting.default == 3 &&
					!setting.color
				) {
					setting["color"] = {
						activeBackgroundColor: "#F5FFF4",
						activeFontColor: "#4CC741",
					};
				}
				const { getRootProps, getRadioProps } = useRadioGroup({
					name: setting.id,
					defaultValue: settings[setting.id]
						? reversedOptions(Object.keys(setting.options))[
								settings[setting.id]
						  ]
						: setting.default.toString(),
					onChange: (data) => {
						if (
							setting.id ===
							"user_registration_form_setting_minimum_password_strength"
						) {
							if (data == 0) {
								color.activeBackgroundColor = "#FFF4F4";
								color.activeFontColor = "#F25656";
							} else if (data == 1) {
								color.activeBackgroundColor = "#FFFAF5";
								color.activeFontColor = "#EE9936";
							} else if (data == 2) {
								color.activeBackgroundColor = "#FFFCF1";
								color.activeFontColor = "#FFC700";
							} else {
								color.activeBackgroundColor = "#F5FFF4";
								color.activeFontColor = "#4CC741";
							}
						}
						setting.color = color;

						handleInputChange(setting.type, setting.id, data);
					},
				});

				const group = getRootProps();

				return (
					<HStack
						{...group}
						sx={{ flexWrap: "wrap", gap: "10px" }}
						flex={"1 0 60%"}
					>
						{Object.keys(setting.options).map((value, key) => {
							return (
								<RadioCard
									key={value}
									radioProps={getRadioProps({
										value: key.toString(),
									})}
									label={setting.options[value]}
									identifier={value}
									color={
										setting.color &&
										setting.color.activeFontColor
											? setting.color.activeFontColor
											: "#475BB2"
									}
									backgroundColor={
										setting.color &&
										setting.color.activeBackgroundColor
											? setting.color
													.activeBackgroundColor
											: "#F9FAFC"
									}
								/>
							);
						})}
					</HStack>
				);
		}
	};
	return (
		<Flex justify={"space-between"} align="center">
			<Flex align="center" flex="0 0 40%">
				<FormLabel sx={{ fontWeight: "600", fontSize: "15px" }}>
					{setting.title}
				</FormLabel>
				<Tooltip
					label={setting.desc}
					hasArrow
					fontSize="14px"
					fontWeight="400px"
					backgroundColor="#383838"
				>
					<span
						className="dashicons dashicons-editor-help"
						style={{
							color: "#BABABA",
							marginBottom: "5px",
						}}
					/>
				</Tooltip>
			</Flex>
			{renderElement()}
		</Flex>
	);
}

export default InputHandler;
