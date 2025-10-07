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
	Tooltip,
	Modal,
	ModalOverlay,
	ModalContent,
	ModalHeader,
	ModalCloseButton,
	useDisclosure
} from "@chakra-ui/react";
import { FaCog, FaPlay, FaLock } from "react-icons/fa";
import YouTubePlayer from "react-player/youtube";
import { activateModule, deactivateModule } from "./modules-api";

const AddonCard = ({ addon, showToast }) => {
	const [isActive, setIsActive] = useState(addon.status === "active");
	const [isLoading, setIsLoading] = useState(false);
	const [moduleEnabled, setModuleEnabled] = useState(false);
	const [videoLoading, setVideoLoading] = useState(false);
	const { isOpen: isVideoOpen, onOpen: onVideoOpen, onClose: onVideoClose } = useDisclosure();

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

	const handleVideoPlay = () => {
		setVideoLoading(true);
		onVideoOpen();
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
			bg="white"
			borderRadius="lg"
			border="1px solid"
			borderColor="gray.200"
			p="6"
			boxShadow="sm"
			_hover={{ boxShadow: "md" }}
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
						<Heading size="sm" color="gray.800" fontWeight="600" fontSize="16px">
							{addon.title}
						</Heading>
						<Badge
							fontSize="10px"
							px="2"
							py="1"
							borderRadius="base"
							bg={
								getPlanBadge(addon.plan) === "Free" ? "transparent" :
								getPlanBadge(addon.plan) === "Personal" ? "#F0FDF4" :
								getPlanBadge(addon.plan) === "Plus" ? "#f0f3fa" :
								getPlanBadge(addon.plan) === "Professional" ? "#EFF6FF" :
								"#EFF6FF"
							}
							border={
								getPlanBadge(addon.plan) === "Free" ? "1px solid #D1D5DB" :
								getPlanBadge(addon.plan) === "Personal" ? "1px solid #16A34A" :
								getPlanBadge(addon.plan) === "Plus" ? "1px solid #92a2e4" :
								getPlanBadge(addon.plan) === "Professional" ? "1px solid #BFDBFE" :
								"1px solid #BFDBFE"
							}
							color={
								getPlanBadge(addon.plan) === "Free" ? "#4B5563" :
								getPlanBadge(addon.plan) === "Personal" ? "#16A34A" :
								getPlanBadge(addon.plan) === "Plus" ? "#92a2e4" :
								getPlanBadge(addon.plan) === "Professional" ? "#3B82F6" :
								"#3B82F6"
							}
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
						color={"gray.500"}
						borderRadius="sm"
						p="5"
						cursor="default"
					>
					<Text 
						fontSize="13px !important" 
						color="gray.500 !important" 
						lineHeight="1.5" 
						flex="1"
						noOfLines={2}
						cursor={"text"}
						sx={{ 
							color: "gray.500 !important",
							fontSize: "13px !important"
						}}
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
						fontSize="13px"
						color="#6B7280"
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
								onClick={handleVideoPlay}
							/>
						</>
					)}
				</HStack>
				<HStack spacing="2">
					{moduleEnabled ? (
						<Switch
							isChecked={isActive}
							onChange={handleToggle}
							isDisabled={isLoading}
							sx={{
								"& .chakra-switch__track[data-checked]": {
									bg: "#475bb2"
								}
							}}
						/>
					) : (
						<Button
							size="sm"
							variant="solid"
							fontSize="xs"
							fontWeight="normal"
							bg="#475bb2"
							color="white"
							borderColor="#475bb2"
							_hover={{
								bg: "#3a4a8f",
								borderColor: "#3a4a8f"
							}}
							onClick={handleUpgradePlan}
						>
							Upgrade Plan
						</Button>
					)}
				</HStack>
			</HStack>

			{/* YouTube Video Modal */}
			{isVideoOpen && addon.demo_video_url && (
				<Modal
					isOpen={isVideoOpen}
					onClose={onVideoClose}
					size="3xl"
					isCentered
				>
					<ModalOverlay />
					<ModalContent px={4} pb={4}>
						<ModalHeader textAlign="center">
							{addon.title}
						</ModalHeader>
						<ModalCloseButton />
						<Box position="relative">
							<YouTubePlayer
								url={`https://www.youtube.com/embed/${addon.demo_video_url}`}
								playing={true}
								width="100%"
								controls
								onReady={() => setVideoLoading(false)}
								onBufferEnd={() => setVideoLoading(false)}
							/>
							{videoLoading && (
								<Box
									position="absolute"
									top="50%"
									left="50%"
									transform="translate(-50%, -50%)"
								>
									<Spinner size="lg" />
								</Box>
							)}
						</Box>
					</ModalContent>
				</Modal>
			)}
		</Box>
	);
};

export default AddonCard;
