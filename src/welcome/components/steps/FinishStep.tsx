import React, { useEffect, useState } from "react";
import {
	Heading,
	Button,
	Flex,
	useColorModeValue,
	FormControl,
	FormLabel,
	Select,
	Tooltip,
	Icon,
	Box,
	Skeleton,
	HStack,
	Text
} from "@chakra-ui/react";
import { ArrowForwardIcon } from "@chakra-ui/icons";
import { __ } from "@wordpress/i18n";
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
	const { state } = useStateValue();
	const { membershipSetupType } = state;

	const [roles, setRoles] = useState<RoleOption[]>([]);
	const [selectedRole, setSelectedRole] = useState<string>("subscriber");
	const [isLoadingRoles, setIsLoadingRoles] = useState(false);
	const [isSaving, setIsSaving] = useState(false);

	const textColor = useColorModeValue("gray.800", "white");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	const isOtherType = membershipSetupType === "other";
	const isMembershipType =
		membershipSetupType === "paid" || membershipSetupType === "free";

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

	const handleGoToDashboard = () => {
		const dashboardUrl =
			(window as any).urmSetupWizard?.dashboardUrl ||
			"/wp-admin/admin.php?page=user-registration-dashboard";
		window.location.href = dashboardUrl;
	};

	const handleCreateMembership = () => {
		const membershipUrl =
			(window as any).urmSetupWizard?.createMembershipUrl ||
			"/wp-admin/admin.php?page=user-registration&tab=membership&action=add";
		window.location.href = membershipUrl;
	};

	const handleVisitRegistration = async () => {
		try {
			setIsSaving(true);

			if (isOtherType && selectedRole) {
				await apiPost("/finish", { default_user_role: selectedRole });
			}

			const registrationUrl =
				(window as any).urmSetupWizard?.registrationPageUrl ||
				"/wp-admin/admin.php?page=user-registration";
			window.location.href = registrationUrl;
		} catch (e) {
			console.error("Failed to save settings:", e);
		} finally {
			setIsSaving(false);
		}
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
				mb={isOtherType ? 6 : 10}
			>
				{__("Congratulations 🎉", "user-registration")}
			</Heading>

			{/* Role selection for "other" type - side by side layout */}
			{isOtherType && (
				<Box mb={10}>
					<FormControl>
						<Flex align="center" gap={4}>
							<FormLabel
								fontSize="sm"
								fontWeight="600"
								color={textColor}
								display="flex"
								alignItems="center"
								gap={2}
								mb={0}
								whiteSpace="nowrap"
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
							{isLoadingRoles ? (
								<Skeleton
									height="40px"
									width="200px"
									borderRadius="md"
								/>
							) : (
								<Select
									value={selectedRole}
									onChange={handleRoleChange}
									bg={inputBg}
									borderColor={inputBorder}
									fontSize="sm"
									maxW="200px"
									_focus={{
										borderColor: "#475BD8",
										boxShadow: "0 0 0 1px #475BD8"
									}}
								>
									{roles.map((role) => (
										<option
											key={role.value}
											value={role.value}
										>
											{role.label}
										</option>
									))}
								</Select>
							)}
						</Flex>
					</FormControl>
				</Box>
			)}

			{/* Buttons Section */}
			<Flex
				justify={isMembershipType ? "space-between" : "flex-end"}
				align="center"
			>
				{/* For membership flow: Go to dashboard on left */}
				{isMembershipType && (
					<Button
						variant="outline"
						borderColor="#475BD8"
						color="#475BD8"
						_hover={{ bg: "blue.50" }}
						px={6}
						onClick={handleGoToDashboard}
					>
						{__("Go to dashboard", "user-registration")}
					</Button>
				)}

				{/* Right side buttons */}
				{isMembershipType ? (
					<Button
						bg="#475BD8"
						color="white"
						rightIcon={<ArrowForwardIcon />}
						_hover={{ bg: "#3a4bc2" }}
						_active={{ bg: "#2f3da6" }}
						px={6}
						onClick={handleCreateMembership}
					>
						{__("Create Membership", "user-registration")}
					</Button>
				) : (
					<Button
						bg="#475BD8"
						color="white"
						rightIcon={<ArrowForwardIcon />}
						_hover={{ bg: "#3a4bc2" }}
						_active={{ bg: "#2f3da6" }}
						px={6}
						onClick={handleVisitRegistration}
						isLoading={isSaving}
					>
						{__("Visit Registration", "user-registration")}
					</Button>
				)}
			</Flex>
		</>
	);
};

export default FinishStep;
