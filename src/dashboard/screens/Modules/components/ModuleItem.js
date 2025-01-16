/**
 * External Dependencies
 */
import {
	Badge,
	Box,
	Checkbox,
	Heading,
	Image,
	Stack,
	Text,
	useToast,
	Link,
	Button,
	Divider,
	HStack,
	IconButton,
	Modal,
	Tooltip,
	ModalCloseButton,
	ModalContent,
	ModalOverlay,
	ModalHeader,
	Spinner,
	useDisclosure,
	Switch
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect, useCallback } from "react";
import YouTubePlayer from "react-player/youtube";
import { FaPlayCircle } from "react-icons/fa";
import { SettingsIcon } from "@chakra-ui/icons";

/**
 * Internal Dependencies
 */
import { activateModule, deactivateModule } from "./modules-api";
import { useStateValue } from "../../../../context/StateProvider";
import { actionTypes } from "../../../../context/dashboardContext";

const ModuleItem = (props) => {
	/* global _UR_DASHBOARD_ */
	const { assetsURL, isPro, licensePlan, adminURL, upgradeURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;

	const [{ upgradeModal, isMembershipActivated }, dispatch] = useStateValue();
	const toast = useToast();
	const { isOpen, onOpen, onClose } = useDisclosure();

	const {
		data,
		isChecked,
		onCheckedChange,
		isPerformingBulkAction,
		selectedModuleData
	} = props;

	const {
		title,
		name,
		excerpt,
		slug,
		image,
		plan,
		link,
		status,
		required_plan,
		type,
		demo_video_url,
		setting_url
	} = data;

	// States
	const [moduleStatus, setModuleStatus] = useState(status);
	const [isPerformingAction, setIsPerformingAction] = useState(false);
	const [thumbnailVideoPlaying, setThumbnailVideoPlaying] = useState(false);
	const [showPlayButton, setShowPlayButton] = useState(false);
	const [moduleEnabled, setModuleEnabled] = useState(false);
	const [thumbnailVideoLoading, setThumbnailVideoLoading] = useState(true);
	const [isFreeModuleEnabled, setIsFreeModuleEnabled] = useState(true);
	const [requirementFulfilled, setRequirementFulfilled] = useState(false);
	const [licenseActivated, setLicenseActivated] = useState(false);

	// Helper for showing toast
	const showToast = useCallback(
		(message, status) => {
			toast({
				title: message,
				status,
				duration: 3000
			});
		},
		[toast]
	);

	// Handle module activation/deactivation
	const handleModuleAction = () => {
		setIsPerformingAction(true);

		if (moduleEnabled && isFreeModuleEnabled) {
			if (
				moduleStatus === "inactive" ||
				moduleStatus === "not-installed"
			) {
				activateModule(slug, name, type)
					.then((data) => {
						if (data.success) {
							showToast(data.message, "success");
							setModuleStatus("active");
						} else {
							showToast(data.message, "error");
							setModuleStatus("not-installed");
						}
					})
					.catch((e) => {
						showToast(e.message, "error");
						setModuleStatus("not-installed");
					})
					.finally(() => {
						setIsPerformingAction(false);
					});
			} else {
				deactivateModule(slug, type)
					.then((data) => {
						if (data.success) {
							showToast(data.message, "success");
							setModuleStatus("inactive");
						} else {
							showToast(data.message, "error");
							setModuleStatus("active");
						}
					})
					.finally(() => {
						setIsPerformingAction(false);
					});
			}
		} else {
			handleBoxClick();
		}
	};

	useEffect(() => {
		if (data.plan.includes("free")) {
			if (
				data.activation_requirements &&
				data.activation_requirements.includes("membership")
			) {
				setIsFreeModuleEnabled(isMembershipActivated);
			}
		}
	}, [isMembershipActivated]);

	useEffect(() => {
		setModuleStatus(data.status);

		if (!upgradeModal.enable) {
			setIsPerformingAction(false);
		}

		if (data.plan.includes("free")) {
			if (
				data.activation_requirements &&
				data.activation_requirements.includes("membership")
			) {
				setIsFreeModuleEnabled(isMembershipActivated);
			}

			setModuleEnabled(true);
		} else if (isPro) {
			setModuleEnabled(true);
			if (licensePlan) {
				const requiredPlan = licensePlan.item_plan.replace(
					" lifetime",
					""
				);

				if (data.plan && data.plan.includes(requiredPlan.trim())) {
					setRequirementFulfilled(true);
				} else {
					setModuleEnabled(false);
				}
				setLicenseActivated(true);
			} else {
				setLicenseActivated(false);
				setModuleEnabled(false);
			}
		} else {
			setModuleEnabled(isPro && licensePlan?.item_plan.includes(plan));
		}
	}, [data, upgradeModal]);

	// Update membership activation status
	useEffect(() => {
		if (thumbnailVideoPlaying) {
			setShowPlayButton(false);
		}
	}, [thumbnailVideoPlaying]);

	useEffect(() => {
		if ("user-registration-membership" === slug) {
			dispatch({
				type: actionTypes.GET_IS_MEMBERSHIP_ACTIVATED,
				isMembershipActivated: moduleStatus === "active"
			});
		}
	}, [moduleStatus, dispatch, slug]);

	const handleBoxClick = () => {
		const upgradeModalRef = { ...upgradeModal };
		upgradeModalRef.moduleType = data.type;
		upgradeModalRef.moduleName = data.name;

		if (!isPro) {
			if (data.plan.includes("free")) {
				upgradeModalRef.enable = false;

				if (!isFreeModuleEnabled) {
					upgradeModalRef.enable = true;
					upgradeModalRef.type = "requirement";
				}
			} else {
				const plan_upgrade_url =
					upgradeURL +
					"&utm_source=dashboard-all-feature&utm_medium=dashboard-upgrade-plan";
				window.open(plan_upgrade_url, "_blank");
			}
		} else if (isPro && !licenseActivated) {
			upgradeModalRef.type = "license";
			upgradeModalRef.enable = true;
		} else if (isPro && licenseActivated && !requirementFulfilled) {
			upgradeModalRef.type = "requirement";
			upgradeModalRef.enable = true;
		} else {
			upgradeModalRef.enable = false;
		}

		dispatch({
			type: actionTypes.GET_UPGRADE_MODAL,
			upgradeModal: upgradeModalRef
		});
	};

	const renderThumbnail = () => (
		<Image
			src={assetsURL + image}
			loading="lazy"
			borderTopRightRadius="sm"
			borderTopLeftRadius="sm"
			w="full"
			onMouseOver={() => demo_video_url && setShowPlayButton(true)}
		/>
	);

	return (
		<Box
			overflow="hidden"
			boxShadow="none"
			border="1px"
			borderRadius="base"
			borderColor="gray.100"
			display="flex"
			flexDir="column"
			bg="white"
		>
			<Box
				p="0"
				flex="1 1 0%"
				position="relative"
				overflow="visible"
				opacity={moduleEnabled ? 1 : 0.7}
			>
				<Box
					position="relative"
					borderTopRightRadius="sm"
					borderTopLeftRadius="sm"
					overflow="hidden"
					onMouseLeave={() =>
						demo_video_url && setShowPlayButton(false)
					}
				>
					{((demo_video_url && !thumbnailVideoPlaying) ||
						!demo_video_url) &&
						renderThumbnail()}

					{thumbnailVideoPlaying && (
						<Modal
							isOpen={true}
							onClose={() => setThumbnailVideoPlaying(false)}
							size="3xl"
						>
							<ModalOverlay />
							<ModalContent px={4} pb={4}>
								<ModalHeader textAlign="center">
									{title}
								</ModalHeader>
								<ModalCloseButton />
								<YouTubePlayer
									url={
										"https://www.youtube.com/embed/" +
										demo_video_url
									}
									playing={true}
									width={"100%"}
									controls
									onReady={() =>
										setThumbnailVideoLoading(false)
									}
									onBufferEnd={() =>
										setThumbnailVideoLoading(false)
									}
								/>
								{thumbnailVideoLoading && (
									<Box
										position={"absolute"}
										top={"50%"}
										left={"50%"}
										transform={"translate(-50%, -50%)"}
									>
										<Spinner size={"lg"} />
									</Box>
								)}
							</ModalContent>
						</Modal>
					)}

					{showPlayButton && (
						<Box
							pos="absolute"
							top={0}
							left={0}
							right={0}
							bottom={0}
							bg="black"
							opacity={0.7}
							display="flex"
							alignItems="center"
							justifyContent="center"
							borderTopStartRadius={10}
							borderTopEndRadius={10}
						>
							<Tooltip
								label={__("Play Video", "user-registration")}
							>
								<span>
									<FaPlayCircle
										color="white"
										size={50}
										cursor={"pointer"}
										onClick={() => {
											setThumbnailVideoPlaying(true);
											setThumbnailVideoLoading(true);
										}}
									/>
								</span>
							</Tooltip>
						</Box>
					)}
				</Box>
				<Badge
					backgroundColor="black"
					color="white"
					position="absolute"
					top="0"
					right="0"
					textTransform="none"
					fontSize="12px"
					fontWeight="500"
					p="5px"
					m="5px"
				>
					{data.required_plan ? data.required_plan : "Pro"}
				</Badge>
				<Box p="6">
					<Stack direction="column" spacing="4">
						<Stack
							direction="row"
							align="center"
							justify="space-between"
						>
							<Heading
								fontSize="sm"
								fontWeight="semibold"
								color="gray.700"
							>
								<Checkbox
									isChecked={isChecked}
									onChange={(e) => {
										moduleEnabled
											? onCheckedChange(
													slug,
													e.target.checked
											  )
											: handleBoxClick();
									}}
								>
									{title}
								</Checkbox>
							</Heading>
						</Stack>

						<Text
							fontWeight="400"
							fontSize="14px"
							color="gray.500"
							textAlign="left"
						>
							{excerpt}
						</Text>
					</Stack>
				</Box>
			</Box>

			<Divider color="gray.300" />
			<Box
				px="4"
				py="5"
				justifyContent="space-between"
				alignItems="center"
				display="flex"
			>
				<HStack gap="1" align="center">
					<Link
						href={link}
						fontSize="xs"
						color="gray.500"
						textDecoration="underline"
						isExternal
					>
						{__("Documentation", "user-registration")}
					</Link>
					{moduleEnabled &&
						setting_url !== "" &&
						moduleStatus === "active" && (
							<>
								<Text as="span" lineHeight="1" color="gray.500">
									|
								</Text>
								<IconButton
									size="sm"
									icon={<SettingsIcon />}
									onClick={() =>
										window.open(
											adminURL + setting_url,
											"_blank"
										)
									}
								/>
							</>
						)}
				</HStack>
				{isPerformingAction ||
				(selectedModuleData.hasOwnProperty(slug) &&
					isPerformingBulkAction) ? (
					<Spinner
						speed="0.50s"
						emptyColor="gray.200"
						color="blue.500"
						size="md"
					/>
				) : (
					(moduleEnabled || plan.includes("free")) && (
						<Switch
							isChecked={
								"active" === moduleStatus
									? plan.includes("free") &&
									  !isFreeModuleEnabled
										? false
										: true
									: false
							}
							onChange={
								moduleEnabled
									? handleModuleAction
									: handleBoxClick
							}
							colorScheme="green"
						/>
					)
				)}

				{(!moduleEnabled ||
					(!moduleEnabled && !plan.includes("free"))) && (
					<Button
						colorScheme={"primary"}
						size="sm"
						fontSize="xs"
						borderRadius="base"
						fontWeight="semibold"
						_hover={{
							color: "white",
							textDecoration: "none"
						}}
						_focus={{
							color: "white",
							textDecoration: "none"
						}}
						onClick={
							moduleEnabled ? handleModuleAction : handleBoxClick
						}
						isLoading={
							isPerformingAction ||
							(selectedModuleData.hasOwnProperty(slug) &&
								isPerformingBulkAction)
						}
					>
						{__("Upgrade Plan", "user-registration")}
					</Button>
				)}
			</Box>
		</Box>
	);
};

export default ModuleItem;
