import {
	Box,
	Flex,
	FormControl,
	FormLabel,
	Heading,
	HStack,
	Icon,
	Popover,
	PopoverArrow,
	PopoverBody,
	PopoverContent,
	PopoverTrigger,
	Select,
	Skeleton,
	Text,
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
	<Icon viewBox="0 0 16 16" boxSize="14px" color="#383838">
		<circle
			cx="8"
			cy="8"
			r="7"
			stroke="currentColor"
			strokeWidth="1.5"
			fill="none"
		/>
		<path
			fill="currentColor"
			d="M8 7a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 7zM8 4.5a.75.75 0 100 1.5.75.75 0 000-1.5z"
		/>
	</Icon>
);
interface CustomTooltipProps {
	label: string;
}

const CustomTooltip: React.FC<CustomTooltipProps> = ({ label }) => {
	return (
		<Popover trigger="hover" placement="bottom-start" gutter={8}>
			<PopoverTrigger>
				<Box
					as="span"
					cursor="pointer"
					display="inline-flex"
					alignItems="center"
				>
					<InfoIcon />
				</Box>
			</PopoverTrigger>
			<PopoverContent
				bg="white"
				border="none"
				boxShadow="0px 4px 16px rgba(0, 0, 0, 0.12)"
				borderRadius="4px"
				width="auto"
				maxW="250px"
				_focus={{ boxShadow: "0px 4px 16px rgba(0, 0, 0, 0.12)" }}
			>
				<PopoverArrow bg="white" boxShadow="none" />
				<PopoverBody
					px={3}
					py={2}
					fontSize="13px"
					color="#222222"
					fontWeight="400"
					lineHeight="1.5"
				>
					{label}
				</PopoverBody>
			</PopoverContent>
		</Popover>
	);
};

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

			<VStack gap={6} align="stretch" spacing={0}>
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
							<HStack spacing={2}>
								<Text>
									{__(
										"User Approval And Login Option",
										"user-registration"
									)}
								</Text>
								<CustomTooltip
									label={__(
										"Select how users are approved after registration",
										"user-registration"
									)}
								/>
							</HStack>
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
							<HStack spacing={2}>
								<Text>
									{__(
										"Assign Default User Role",
										"user-registration"
									)}
								</Text>
								<CustomTooltip
									label={__(
										"Select the default role assigned to new users upon registration",
										"user-registration"
									)}
								/>
							</HStack>
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
