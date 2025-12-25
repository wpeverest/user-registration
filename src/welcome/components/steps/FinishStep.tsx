import React, { useEffect, useState } from "react";
import {
	Heading,
	Button,
	Link,
	Flex,
	useColorModeValue,
	FormControl,
	FormLabel,
	Select,
	Tooltip,
	Icon,
	Box,
	Skeleton,
	Text,
	VStack
} from "@chakra-ui/react";
import { ArrowBackIcon, ArrowForwardIcon } from "@chakra-ui/icons";
import { useStateValue } from "../../context/StateProvider";
import { apiGet, apiPost } from "../../api/gettingStartedApi";

interface RoleOption {
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

const FinishStep: React.FC = () => {
	const { state, dispatch } = useStateValue();
	const { membershipSetupType } = state;

	const [roles, setRoles] = useState<RoleOption[]>([]);
	const [selectedRole, setSelectedRole] = useState<string>("subscriber");
	const [isLoadingRoles, setIsLoadingRoles] = useState(false);
	const [isSaving, setIsSaving] = useState(false);

	const textColor = useColorModeValue("gray.800", "white");
	const mutedColor = useColorModeValue("gray.600", "gray.400");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	const isOtherType = membershipSetupType === "other";

	// Load roles when component mounts and type is "other"
	useEffect(() => {
		const loadRoles = async () => {
			if (!isOtherType) return;

			try {
				setIsLoadingRoles(true);
				const response = await apiGet("/finish");

				if (response.roles) {
					setRoles(response.roles);
				}

				if (response.default_role) {
					setSelectedRole(response.default_role);
				}
			} catch (e) {
				console.error("Failed to load roles:", e);
			} finally {
				setIsLoadingRoles(false);
			}
		};

		loadRoles();
	}, [isOtherType]);

	const handleRoleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
		setSelectedRole(e.target.value);
	};

	const handleViewRegistration = () => {
		const registrationUrl =
			(window as any).urmSetupWizard?.registrationPageUrl ||
			"/registration";
		window.location.href = registrationUrl;
	};

	const handleVisitDashboard = async () => {
		try {
			setIsSaving(true);

			// If "other" type, save the selected role
			if (isOtherType && selectedRole) {
				await apiPost("/finish", { default_user_role: selectedRole });
			}

			const dashboardUrl =
				(window as any).urmSetupWizard?.dashboardUrl ||
				"/wp-admin/admin.php?page=user-registration";
			window.location.href = dashboardUrl;
		} catch (e) {
			console.error("Failed to save settings:", e);
		} finally {
			setIsSaving(false);
		}
	};

	return (
		<>
			<Heading size="lg" color={textColor} mb={isOtherType ? 6 : 10}>
				Success! You're all set!
			</Heading>

			{isOtherType && (
				<Box mb={10}>
					<FormControl>
						<FormLabel
							fontSize="sm"
							fontWeight="600"
							color={textColor}
							display="flex"
							alignItems="center"
							gap={2}
						>
							Assign Default User Role
							<Tooltip
								label="Select the default role assigned to new users upon registration"
								hasArrow
							>
								<span>
									<InfoIcon />
								</span>
							</Tooltip>
						</FormLabel>
						{isLoadingRoles ? (
							<Skeleton height="40px" borderRadius="md" />
						) : (
							<Select
								value={selectedRole}
								onChange={handleRoleChange}
								bg={inputBg}
								borderColor={inputBorder}
								fontSize="sm"
								_focus={{
									borderColor: "#475BD8",
									boxShadow: "0 0 0 1px #475BD8"
								}}
							>
								{roles.map((role) => (
									<option key={role.value} value={role.value}>
										{role.label}
									</option>
								))}
							</Select>
						)}
					</FormControl>
				</Box>
			)}

			<Flex justify="space-between" align="center">
				<Link
					display="flex"
					alignItems="center"
					color={mutedColor}
					fontSize="sm"
					_hover={{ color: textColor, textDecoration: "none" }}
					cursor="pointer"
					onClick={handleViewRegistration}
				>
					<ArrowBackIcon mr={2} />
					View Registration Page
				</Link>

				<Button
					bg="#475BD8"
					color="white"
					rightIcon={<ArrowForwardIcon />}
					_hover={{ bg: "#3a4bc2" }}
					_active={{ bg: "#2f3da6" }}
					px={6}
					onClick={handleVisitDashboard}
					isLoading={isSaving}
				>
					Visit Dashboard
				</Button>
			</Flex>
		</>
	);
};

export default FinishStep;
