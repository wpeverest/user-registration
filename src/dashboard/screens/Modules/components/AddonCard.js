/**
 * External Dependencies
 */
import React, { useState, useEffect } from "react";
import {
	Box,
	Badge,
	Heading,
	HStack,
	VStack,
	Switch,
	IconButton,
	Link,
	Text,
	Icon,
	Button,
	Spinner,
	Flex,
	Tooltip
} from "@chakra-ui/react";
import { FaCog, FaPlay, FaLock } from "react-icons/fa";
import { activateModule, deactivateModule } from "./modules-api";

const AddonCard = ({ addon, showToast }) => {
	const [isActive, setIsActive] = useState(addon.status === "active");
	const [isLoading, setIsLoading] = useState(false);
	const [moduleEnabled, setModuleEnabled] = useState(false);

	// Get assets URL from global variable
	const getImageUrl = (imagePath) => {
		/* global _UR_DASHBOARD_ */
		const { assetsURL } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
		if (imagePath && assetsURL) {
			return assetsURL + imagePath;
		}
		return imagePath;
	};

	// Check if module is enabled based on plan requirements
	useEffect(() => {
		/* global _UR_DASHBOARD_ */
		const { isPro, licensePlan } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

		if (addon.plan && addon.plan.includes("free")) {
			setModuleEnabled(true);
		} else if (isPro && licensePlan) {
			const requiredPlan = licensePlan.item_plan.replace(" lifetime", "");
			if (addon.plan && addon.plan.includes(requiredPlan.trim())) {
				setModuleEnabled(true);
			} else {
				setModuleEnabled(false);
			}
		} else {
			setModuleEnabled(false);
		}
	}, [addon.plan]);

	const handleUpgradePlan = () => {
		const { upgradeURL } = typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
		if (upgradeURL) {
			const plan_upgrade_url = upgradeURL + "&utm_source=dashboard-all-feature&utm_medium=dashboard-upgrade-plan";
			window.open(plan_upgrade_url, "_blank");
		}
	};

	const handleToggle = async () => {
		setIsLoading(true);
		try {
			let response;
			if (isActive) {
				response = await deactivateModule(addon.slug, addon.type);
				if (response.success) {
					setIsActive(false);
					showToast(response.message || "Module deactivated successfully", "success");
				} else {
					showToast(response.message || "Failed to deactivate module", "error");
				}
			} else {
				response = await activateModule(addon.slug, addon.name, addon.type);
				if (response.success) {
					setIsActive(true);
					showToast(response.message || "Module activated successfully", "success");
				} else {
					showToast(response.message || "Failed to activate module", "error");
				}
			}
		} catch (error) {
			showToast(error.message || "An error occurred", "error");
		}
		setIsLoading(false);
	};

	const getPlanBadge = (plan) => {
		if (plan.includes("free")) return "Free";
		if (plan.includes("personal")) return "Personal";
		if (plan.includes("plus")) return "Plus";
		if (plan.includes("professional")) return "Professional";
		return "Pro";
	};

	const getPlanBadgeColor = (plan) => {
		if (plan.includes("free")) return "gray";
		if (plan.includes("personal")) return "green";
		if (plan.includes("plus")) return "purple";
		if (plan.includes("professional")) return "blue";
		return "gray";
	};


	return (
		<Box
			bg="#fafbfd"
			borderRadius="xl"
			border="1px solid"
			borderColor="gray.200"
			p="6"
			boxShadow="sm"
			_hover={{ boxShadow: "lg" }}
			transition="all 0.2s"
			position="relative"
			height="100%"
			display="flex"
			flexDirection="column"
		>
			{/* Loading Overlay */}
			{isLoading && (
				<Flex
					position="absolute"
					top="0"
					left="0"
					right="0"
					bottom="0"
					bg="rgba(255, 255, 255, 0.8)"
					borderRadius="xl"
					alignItems="center"
					justifyContent="center"
					zIndex="10"
				>
					<Spinner
						size="lg"
						color="gray.500"
						thickness="3px"
					/>
				</Flex>
			)}
			{/* Main Content Layout */}
			<HStack align="start" spacing="4" flex="1" mb="6">
				{/* Left Side - Icon */}
				<Box
					w="10"
					h="10"
					bg="white"
					borderRadius="full"
					display="flex"
					alignItems="center"
					justifyContent="center"
					boxShadow="sm"
					flexShrink={0}
					overflow="hidden"
				>
					{addon.image ? (
						<img
							src={getImageUrl(addon.image)}
							alt={addon.title}
							style={{
								width: "100%",
								height: "100%",
								objectFit: "contain",
								borderRadius: "50%"
							}}
							onError={(e) => {
								// Fallback to placeholder if image fails to load
								e.target.style.display = "none";
								e.target.nextSibling.style.display = "flex";
							}}
						/>
					) : null}
					<Box
						display={addon.image ? "none" : "flex"}
						alignItems="center"
						justifyContent="center"
						fontSize="2xl"
						width="100%"
						height="100%"
					>
						🔧
					</Box>
				</Box>

				{/* Right Side - Title, Description, and Plan Badge */}
				<VStack align="start" spacing="3" flex="1">
					{/* Title and Plan Badge */}
					<HStack justify="space-between" w="full" align="start">
						<Heading size="md" color="gray.800" fontWeight="600">
							{addon.title}
						</Heading>
						<Badge
							colorScheme={getPlanBadgeColor(addon.plan)}
							fontSize="xs"
							px="2"
							py="1"
							borderRadius="sm"
						>
							{getPlanBadge(addon.plan)}
						</Badge>
					</HStack>

					{/* Description */}
					<Tooltip 
						label={addon.excerpt} 
						placement="top" 
						hasArrow
						isDisabled={addon.excerpt.length <= 120}
						backgroundColor="white"
						color="gray.600"
						borderRadius="sm"
						p="5"
						cursor="default"
					>
						<Text 
							fontSize="sm" 
							color="gray.600" 
							lineHeight="1.5" 
							flex="1"
							noOfLines={2}
							cursor={"text"}
						>
							{addon.excerpt}
						</Text>
					</Tooltip>
				</VStack>
			</HStack>

			{/* Footer Section */}
			<HStack justify="space-between" align="center">
				<HStack spacing="3">
					<Link
						href={addon.link}
						fontSize="sm"
						color="gray.500"
						textDecoration="underline"
						isExternal
					>
						Docs
					</Link>
					{addon.setting_url && isActive && (
						<>
							<Text color="gray.300">|</Text>
							<IconButton
								size="sm"
								icon={<FaCog />}
								aria-label="Settings"
								variant="ghost"
								onClick={() => window.open(addon.setting_url, "_blank")}
							/>
						</>
					)}
					{addon.demo_video_url && (
						<>
							<Text color="gray.300">|</Text>
							<IconButton
								size="sm"
								icon={<Icon as={FaPlay} />}
								aria-label="Video Tutorial"
								variant="ghost"
								onClick={() => window.open(`https://www.youtube.com/watch?v=${addon.demo_video_url}`, "_blank")}
							/>
						</>
					)}
				</HStack>
				<HStack spacing="2">
					{moduleEnabled ? (
						<Switch
							isChecked={isActive}
							onChange={handleToggle}
							colorScheme="green"
							isDisabled={isLoading}
						/>
					) : (
						<Button
							size="sm"
							variant="outline"
							colorScheme="gray"
							fontSize="xs"
							fontWeight="normal"
							borderColor="gray.300"
							color="gray.600"
							_hover={{
								bg: "gray.50",
								borderColor: "gray.400"
							}}
							onClick={handleUpgradePlan}
						>
							Upgrade Plan
						</Button>
					)}
				</HStack>
			</HStack>
		</Box>
	);
};

export default AddonCard;
