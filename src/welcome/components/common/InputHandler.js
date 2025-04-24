import React from "react";
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
	Checkbox,
	Input,
	InputGroup,
	InputLeftElement
} from "@chakra-ui/react";
import { Select } from "chakra-react-select";

import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/gettingStartedContext";

function InputHandler({
	setting,
	onBoardIconsURL,
	customStyle,
	onModify,
	hideElement
}) {
	const [{ settings, allowUsageData }, dispatch] = useStateValue();

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
					value: key
				});
			});
		}

		return newOptionsRef;
	};

	const RadioCard = (props) => {
		const { radioProps, label, identifier, color, backgroundColor } = props;
		const { state, getInputProps, getRadioProps } = useRadio(radioProps);

		const input = getInputProps();
		const checkbox = getRadioProps();

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
						borderColor: color
					}}
					_focus={{
						boxShadow: "outline"
					}}
					px={5}
					py={3}
					style={{
						flex: "1 0 30%"
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
		const newAllowUsageDataChangedValueRef = { ...allowUsageData };

		if (fieldType === "text") {
			newChangedValueRef[fieldIdentifier] = event.target.value;
		} else if (fieldType === "switch" || fieldType === "checkbox") {
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

		Object.keys(newChangedValueRef).map((key, value) => {
			if (newAllowUsageDataChangedValueRef[key]) {
				newAllowUsageDataChangedValueRef[key] = newChangedValueRef[key];
				delete newChangedValueRef[key];
			}
		});

		if (
			newChangedValueRef.user_registration_form_setting_enable_strong_password ===
			"no"
		) {
			onModify({ value: true });
		} else if (
			newChangedValueRef.user_registration_form_setting_enable_strong_password ===
			"yes"
		) {
			onModify({ value: false });
		}

		dispatch({
			type: actionTypes.GET_SETTINGS,
			settings: newChangedValueRef
		});

		dispatch({
			type: actionTypes.GET_ALLOW_USAGE,
			allowUsageData: newAllowUsageDataChangedValueRef
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

	const MailIcon = (props) => (
		<Icon viewBox="0 0 16 13" {...props}>
			<path
				fillRule="evenodd"
				clipRule="evenodd"
				d="M2.54552 1.36362C2.16896 1.36362 1.86371 1.66888 1.86371 2.04543V10.2272C1.86371 10.6037 2.16896 10.909 2.54552 10.909H13.4545C13.831 10.909 14.1363 10.6037 14.1363 10.2272V2.04543C14.1363 1.66888 13.831 1.36362 13.4545 1.36362H2.54552ZM0.500086 2.04543C0.500086 0.915771 1.41586 0 2.54552 0H13.4545C14.5841 0 15.4999 0.915771 15.4999 2.04543V10.2272C15.4999 11.3568 14.5841 12.2726 13.4545 12.2726H2.54552C1.41586 12.2726 0.500086 11.3568 0.500086 10.2272V2.04543Z"
				fill="#6B6B6B"
			/>
			<path
				fillRule="evenodd"
				clipRule="evenodd"
				d="M0.606442 2.36157C0.808398 2.04375 1.22976 1.94983 1.54757 2.15179L7.66125 6.03673C7.76289 6.10002 7.88024 6.13357 8 6.13357C8.11976 6.13357 8.23711 6.10002 8.33875 6.03673L8.34027 6.03578L14.4524 2.15179C14.7702 1.94983 15.1916 2.04375 15.3936 2.36157C15.5955 2.67938 15.5016 3.10074 15.1838 3.3027L9.06427 7.19135C8.74527 7.39121 8.37643 7.49719 8 7.49719C7.62357 7.49719 7.25474 7.3912 6.93574 7.19134L6.93205 7.18903L0.816223 3.3027C0.498408 3.10074 0.404486 2.67938 0.606442 2.36157Z"
				fill="#6B6B6B"
			/>
		</Icon>
	);

	const renderElement = () => {
		switch (setting.type) {
			case "text":
				return (
					<InputGroup flex="1">
						<InputLeftElement>
							<MailIcon />
						</InputLeftElement>
						<Input
							className="user-registration-setup-wizard__body--input-box"
							name={setting.id}
							id={setting.id}
							onChange={(e) =>
								handleInputChange(setting.type, setting.id, e)
							}
							defaultValue={setting.default}
						/>
					</InputGroup>
				);
			case "checkbox":
				return (
					<Checkbox
						flex={"0 0 60%"}
						className="user-registration-setup-wizard__body--normal-checkbox"
						name={setting.id}
						id={setting.id}
						onChange={(e) =>
							handleInputChange(setting.type, setting.id, e)
						}
						defaultChecked={setting.default === "yes"}
						{...(settings[setting.id] === "yes" && {
							isChecked: true
						})}
					/>
				);
			case "switch":
				return (
					<Switch
						flex={"0 0 60%"}
						className="user-registration-setup-wizard__body--checkbox"
						name={setting.id}
						id={setting.id}
						onChange={(e) =>
							handleInputChange(setting.type, setting.id, e)
						}
						defaultChecked={setting.default === "yes"}
						{...(settings[settings.id] === "yes" && {
							isChecked: true
						})}
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
					activeFontColor: "#475BB2"
				};

				if (
					setting.id ===
						"user_registration_form_setting_minimum_password_strength" &&
					setting.default == 3 &&
					!setting.color
				) {
					setting["color"] = {
						activeBackgroundColor: "#F5FFF4",
						activeFontColor: "#4CC741"
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
					}
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
										value: key.toString()
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

	if (
		"undefined" !== typeof hideElement[setting.id] &&
		hideElement[setting.id]
	) {
		return "";
	}

	return (
		<Flex justify={"space-between"} align="center" sx={customStyle}>
			<Flex align="center" flex="0 0 40%">
				<FormLabel
					sx={{
						fontWeight: "600",
						fontSize: "15px",
						marginInlineEnd: "0.5rem"
					}}
				>
					{setting.title}
				</FormLabel>
				{setting.desc && (
					<Tooltip
						label={setting.desc}
						hasArrow
						fontSize="14px"
						fontWeight="400px"
						backgroundColor="#383838"
					>
						<span
							className="ur-setup-wizard-tool-tip"
							style={{
								color: "#BABABA",
								marginBottom: "5px"
							}}
						/>
					</Tooltip>
				)}
			</Flex>
			{renderElement()}
		</Flex>
	);
}

export default InputHandler;
