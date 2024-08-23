/**
 *  External Dependencies
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
import React, { useState, useEffect } from "react";
import YouTubePlayer from "react-player/youtube";
import { FaInfoCircle, FaPlayCircle } from "react-icons/fa";
import { SettingsIcon } from "@chakra-ui/icons";

/**
 *  Internal Dependencies
 */
import { activateModule, deactivateModule } from "./modules-api";
import { useStateValue } from "../../../../context/StateProvider";
import { actionTypes } from "../../../../context/dashboardContext";

const ModuleItem = (props) => {
	/* global _UR_DASHBOARD_ */
	const { assetsURL, liveDemoURL, isPro, licensePlan, adminURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ upgradeModal }, dispatch] = useStateValue();
	const [requirementFulfilled, setRequirementFulfilled] = useState(false);
	const [licenseActivated, setLicenseActivated] = useState(false);
	const [moduleEnabled, setModuleEnabled] = useState(false);
	const [showPlayVideoButton, setShowPlayVideoButton] = useState(false);
	const [thumbnailVideoPlaying, setThumbnailVideoPlaying] = useState(false);

	const [thumbnailVideoLoading, setThumbnailVideoLoading] = useState(true);
	const { isOpen, onOpen, onClose } = useDisclosure();
	const [isAddonActivating, setAddonActivated] = useState(false);

	const {
		data,
		isChecked,
		onCheckedChange,
		isPerformingBulkAction,
		selectedModuleData
	} = props;
	const toast = useToast();
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
	const [moduleStatus, setModuleStatus] = useState(status);
	const [isPerformingAction, setIsPerformingAction] = useState(false);
	const [moduleSettingsURL, setModuleSettingsURL] = useState("");

	const handleModuleAction = () => {
		setAddonActivated(true);
		setIsPerformingAction(true);

		if (moduleEnabled) {
			if (
				moduleStatus === "inactive" ||
				moduleStatus === "not-installed"
			) {
				activateModule(slug, name, type)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000
							});
							setModuleStatus("active");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000
							});
							setModuleStatus("not-installed");
						}
						setAddonActivated(false);
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000
						});
						setModuleStatus("not-installed");
					})
					.finally(() => {
						setIsPerformingAction(false);
						setAddonActivated(false);
					});
			} else {
				deactivateModule(slug, type)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000
							});
							setModuleStatus("inactive");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000
							});
							setModuleStatus("active");
						}
					})
					.finally(() => {
						setAddonActivated(false);
						setIsPerformingAction(false);
					});
			}
		} else {
			const upgradeModalRef = { ...upgradeModal };
			upgradeModalRef.enable = true;
			// Handle Pro Upgrade notice
			dispatch({
				type: actionTypes.GET_UPGRADE_MODAL,
				upgradeModal: upgradeModalRef
			});
		}
	};

	useEffect(() => {
		setModuleStatus(data.status);

		if (!upgradeModal.enable) {
			setIsPerformingAction(false);
		}

		if (isPro) {
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
			setModuleEnabled(false);
		}
	}, [data, upgradeModal]);

	useEffect(() => {
		if (thumbnailVideoPlaying) {
			setShowPlayVideoButton(false);
		}
	}, [thumbnailVideoPlaying]);

	const handleBoxClick = () => {
		const upgradeModalRef = { ...upgradeModal };
		upgradeModalRef.moduleType = data.type;
		upgradeModalRef.moduleName = data.name;

		if (!isPro) {
			upgradeModalRef.type = "pro";
			upgradeModalRef.enable = true;
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

	const handleModuleSettingsURL = () => {
		var settingsURL = adminURL + setting_url;
		window.open(settingsURL, "_blank");
	};

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
						demo_video_url && setShowPlayVideoButton(false)
					}
				>
					{((demo_video_url && !thumbnailVideoPlaying) ||
						!demo_video_url) && (
						<Image
							src={assetsURL + image}
							borderTopRightRadius="sm"
							borderTopLeftRadius="sm"
							w="full"
							onMouseOver={() =>
								demo_video_url && setShowPlayVideoButton(true)
							}
						/>
					)}

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

					{showPlayVideoButton && (
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
								label={__(
									"Play Video",
									"learning-management-system"
								)}
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
					<Text as="span" lineHeight="1" color="gray.500">
						|
					</Text>
					<Link
						href={liveDemoURL}
						fontSize="xs"
						color="gray.500"
						textDecoration="underline"
						isExternal
					>
						{__("Live Demo", "user-registration")}
					</Link>
				</HStack>
				{moduleEnabled &&
					setting_url !== "" &&
					moduleStatus === "active" && (
						<IconButton
							size="sm"
							icon={<SettingsIcon />}
							onClick={handleModuleSettingsURL}
						/>
					)}
				{moduleEnabled && (
					<>
						{isAddonActivating ? (
							<Spinner
								speed="0.50s"
								emptyColor="gray.200"
								color="blue.500"
								size="md"
							/>
						) : (
							<Switch
								isChecked={moduleStatus === "active"}
								onChange={
									moduleEnabled
										? handleModuleAction
										: handleBoxClick
								}
								colorScheme="green"
							/>
						)}
					</>
				)}

				{!moduleEnabled && (
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
