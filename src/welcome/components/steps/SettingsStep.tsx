import {
	Flex,
	FormControl,
	FormLabel,
	Heading,
	Icon,
	Select,
	Skeleton,
	Text,
	Tooltip,
	useColorModeValue,
	VStack
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useEffect, useState } from "react";
import { apiGet } from "../../api/gettingStartedApi";
import { useStateValue } from "../../context/StateProvider";

interface OptionItem {
	value: string;
	label: string;
}

const InfoIcon: React.FC = () => (
	<Icon viewBox="0 0 20 20" boxSize={4} color="gray.400">
		<path
			fill="currentColor"
			d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zm1 15H9v-2h2v2zm0-4H9V5h2v6z"
		/>
	</Icon>
);

const SettingsStep: React.FC = () => {
	const { dispatch } = useStateValue();
	const [isLoading, setIsLoading] = useState(true);
	const [loginOptions, setLoginOptions] = useState<OptionItem[]>([]);
	const [roles, setRoles] = useState<OptionItem[]>([]);
	const [selectedLoginOption, setSelectedLoginOption] =
		useState<string>("default");
	const [selectedRole, setSelectedRole] = useState<string>("subscriber");

	const textColor = useColorModeValue("gray.800", "white");
	const subtextColor = useColorModeValue("gray.600", "gray.300");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	useEffect(() => {
		const loadSettingsData = async () => {
			try {
				setIsLoading(true);
				const response = await apiGet("/settings");
				const data = response.data || response;

				if (data.login_options && Array.isArray(data.login_options)) {
					setLoginOptions(data.login_options);
				}

				if (data.roles && Array.isArray(data.roles)) {
					setRoles(data.roles);
				}

				if (data.selected_login_option) {
					setSelectedLoginOption(data.selected_login_option);
				}

				if (data.selected_role) {
					setSelectedRole(data.selected_role);
				}
			} catch (e) {
				console.error("Failed to load settings data:", e);
			} finally {
				setIsLoading(false);
			}
		};

		loadSettingsData();
	}, []);

	useEffect(() => {
		dispatch({
			type: "SET_REGISTRATION_SETTINGS",
			payload: {
				loginOption: selectedLoginOption,
				defaultRole: selectedRole
			}
		});
	}, [selectedLoginOption, selectedRole, dispatch]);

	const handleLoginOptionChange = (
		e: React.ChangeEvent<HTMLSelectElement>
	) => {
		setSelectedLoginOption(e.target.value);
	};

	const handleRoleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
		setSelectedRole(e.target.value);
	};

	return (
		<>
			<Heading
				size="lg"
				fontFamily="Inter"
				fontWeight={600}
				fontSize="21px"
				lineHeight="34px"
				letterSpacing="-0.01em"
				color={textColor}
				mb={2}
			>
				{__("Registration Settings", "user-registration")}
			</Heading>

			<Text fontSize="sm" color={subtextColor} mb={8}>
				{__(
					"Set up how new users register and what role they'll be assigned. You can edit this anytime.",
					"user-registration"
				)}
			</Text>

			<VStack spacing={6} align="stretch">
				<FormControl>
					<Flex align="center" justify="space-between">
						<FormLabel
							fontSize="sm"
							fontWeight="600"
							color={textColor}
							display="flex"
							alignItems="center"
							gap={2}
							mb={0}
							minW="250px"
						>
							{__(
								"User Approval And Login Option",
								"user-registration"
							)}
							<Tooltip
								label={__(
									"Select how users are approved after registration",
									"user-registration"
								)}
								hasArrow
							>
								<span>
									<InfoIcon />
								</span>
							</Tooltip>
						</FormLabel>
						{isLoading ? (
							<Skeleton
								height="40px"
								flex="1"
								maxW="400px"
								borderRadius="md"
							/>
						) : (
							<Select
								value={selectedLoginOption}
								onChange={handleLoginOptionChange}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								flex="1"
								maxW="400px"
								_focus={{
									borderColor: "#475BB2",
									boxShadow: "0 0 0 1px #475BB2"
								}}
							>
								{loginOptions.map((option) => (
									<option
										key={option.value}
										value={option.value}
									>
										{option.label}
									</option>
								))}
							</Select>
						)}
					</Flex>
				</FormControl>

				<FormControl>
					<Flex align="center" justify="space-between">
						<FormLabel
							fontSize="sm"
							fontWeight="600"
							color={textColor}
							display="flex"
							alignItems="center"
							gap={2}
							mb={0}
							minW="250px"
						>
							{__(
								"Assign Default User Role",
								"user-registration"
							)}
							<Tooltip
								label={__(
									"Select the default role assigned to new users upon registration",
									"user-registration"
								)}
								hasArrow
							>
								<span>
									<InfoIcon />
								</span>
							</Tooltip>
						</FormLabel>
						{isLoading ? (
							<Skeleton
								height="40px"
								flex="1"
								maxW="400px"
								borderRadius="md"
							/>
						) : (
							<Select
								value={selectedRole}
								onChange={handleRoleChange}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								flex="1"
								maxW="400px"
								_focus={{
									borderColor: "#475BB2",
									boxShadow: "0 0 0 1px #475BB2"
								}}
							>
								{roles.map((role) => (
									<option key={role.value} value={role.value}>
										{role.label}
									</option>
								))}
							</Select>
						)}
					</Flex>
				</FormControl>
			</VStack>
		</>
	);
};

export default SettingsStep;
