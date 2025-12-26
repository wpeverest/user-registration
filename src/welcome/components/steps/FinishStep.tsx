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
	Skeleton
} from "@chakra-ui/react";
import { ArrowForwardIcon } from "@chakra-ui/icons";
import { __ } from "@wordpress/i18n";
import { useStateValue } from "../../context/StateProvider";
import { apiGet, apiPost } from "../../api/gettingStartedApi";

interface RoleOption {
	value: string;
	label: string;
}

interface FinishLinks {
	primary_action?: string;
	primary_action_label?: string;
	registration_page?: string;
	dashboard?: string;
	settings?: string;
	memberships?: string;
	forms?: string;
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
	const [isLoadingData, setIsLoadingData] = useState(true);
	const [isSaving, setIsSaving] = useState(false);
	const [links, setLinks] = useState<FinishLinks>({});

	const textColor = useColorModeValue("gray.800", "white");
	const inputBg = useColorModeValue("white", "gray.700");
	const inputBorder = useColorModeValue("gray.300", "gray.600");

	const isOtherType = membershipSetupType === "other";
	const isMembershipType =
		membershipSetupType === "paid" || membershipSetupType === "free";

	useEffect(() => {
		const loadFinishData = async () => {
			try {
				setIsLoadingData(true);
				const response = await apiGet("/finish");

				if (response.roles) {
					setRoles(response.roles);
				}

				if (response.default_role) {
					setSelectedRole(response.default_role);
				}

				if (response.links) {
					setLinks(response.links);
				}
			} catch (e) {
				console.error("Failed to load finish data:", e);
			} finally {
				setIsLoadingData(false);
			}
		};

		loadFinishData();
	}, []);

	const handleRoleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
		setSelectedRole(e.target.value);
	};

	const handleGoToDashboard = () => {
		if (links.dashboard) {
			window.location.href = links.dashboard;
		}
	};

	const handlePrimaryAction = async () => {
		try {
			setIsSaving(true);

			
			if (isOtherType && selectedRole) {
				await apiPost("/finish", { default_user_role: selectedRole });
			}

			if (links.primary_action) {
				window.location.href = links.primary_action;
			}
		} catch (e) {
			console.error("Failed to complete action:", e);
		} finally {
			setIsSaving(false);
		}
	};


	const primaryActionLabel = links.primary_action_label ||
		(isMembershipType ? __("Create Membership", "user-registration") : __("Visit Registration", "user-registration"));

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
							{isLoadingData ? (
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

			<Flex
				justify={isMembershipType ? "space-between" : "flex-end"}
				align="center"
			>

				{isMembershipType && (
					<Button
						variant="outline"
						borderColor="#475BD8"
						color="#475BD8"
						_hover={{ bg: "blue.50" }}
						px={6}
						onClick={handleGoToDashboard}
						isDisabled={isLoadingData || !links.dashboard}
					>
						{__("Go to dashboard", "user-registration")}
					</Button>
				)}


				<Button
					bg="#475BD8"
					color="white"
					rightIcon={<ArrowForwardIcon />}
					_hover={{ bg: "#3a4bc2" }}
					_active={{ bg: "#2f3da6" }}
					px={6}
					onClick={handlePrimaryAction}
					isLoading={isSaving}
					isDisabled={isLoadingData || !links.primary_action}
				>
					{primaryActionLabel}
				</Button>
			</Flex>
		</>
	);
};

export default FinishStep;
