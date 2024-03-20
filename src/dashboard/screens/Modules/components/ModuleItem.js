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
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";

/**
 *  Internal Dependencies
 */
import { activateModule, deactivateModule } from "./modules-api";
import { useStateValue } from "../../../../context/StateProvider";
import { actionTypes } from "../../../../context/dashboardContext";

const ModuleItem = (props) => {
	/* global _UR_DASHBOARD_ */
	const { assetsURL, liveDemoURL, isPro, licensePlan } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ upgradeModal }, dispatch] = useStateValue();
	const [requirementFulfilled, setRequirementFulfilled] = useState(false);
	const [licenseActivated, setLicenseActivated] = useState(false);
	const [moduleEnabled, setModuleEnabled] = useState(false);

	const {
		data,
		isChecked,
		onCheckedChange,
		isPerformingBulkAction,
		selectedModuleData,
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
	} = data;
	const [moduleStatus, setModuleStatus] = useState(status);
	const [isPerformingAction, setIsPerformingAction] = useState(false);

	// console.log(data);
	const handleModuleAction = () => {
		setIsPerformingAction(true);

		if (moduleEnabled) {
			if (
				moduleStatus === "inactive" ||
				moduleStatus === "not-installed"
			) {
				activateModule(slug, name, type)
					.then((data) => {
						console.log(data);
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
							// window.location.reload();
							setModuleStatus("active");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
							setModuleStatus("not-installed");
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
						setModuleStatus("not-installed");
					})
					.finally(() => {
						setIsPerformingAction(false);
					});
			} else {
				deactivateModule(slug, type)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
							// window.location.reload();
							setModuleStatus("inactive");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
							setModuleStatus("active");
						}
					})
					.finally(() => {
						setIsPerformingAction(false);
					});
			}
		} else {
			const upgradeModalRef = { ...upgradeModal };
			upgradeModalRef.enable = true;
			// Handle Pro Upgrade notice
			dispatch({
				type: actionTypes.GET_UPGRADE_MODAL,
				upgradeModal: upgradeModalRef,
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

	const handleBoxClick = () => {
		const upgradeModalRef = { ...upgradeModal };
		upgradeModalRef.moduleType = "module";
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
			upgradeModal: upgradeModalRef,
		});
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
				onClick={() => {
					!moduleEnabled && handleBoxClick();
				}}
			>
				<Image
					src={assetsURL + image}
					borderTopRightRadius="sm"
					borderTopLeftRadius="sm"
					w="full"
				/>
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
				<Button
					colorScheme={
						moduleEnabled
							? "active" === moduleStatus
								? "red"
								: "green"
							: "primary"
					}
					size="sm"
					fontSize="xs"
					borderRadius="base"
					fontWeight="semibold"
					_hover={{
						color: "white",
						textDecoration: "none",
					}}
					_focus={{
						color: "white",
						textDecoration: "none",
					}}
					onClick={handleModuleAction}
					isLoading={
						isPerformingAction ||
						(selectedModuleData.hasOwnProperty(slug) &&
							isPerformingBulkAction)
					}
				>
					{moduleEnabled
						? "active" === moduleStatus
							? __("Deactivate", "user-registration")
							: __("Activate", "user-registration")
						: __("Upgrade Plan", "user-registration")}
				</Button>
			</Box>
		</Box>
	);
};

export default ModuleItem;
