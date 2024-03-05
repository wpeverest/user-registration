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
	Divider,
	HStack,
	Switch,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";
import { enableFeature, disableFeature } from "../features-api";
import { useStateValue } from "../../../../../context/StateProvider";
import { actionTypes } from "../../../../../context/dashboardContext";

const FeatureItem = (props) => {
	/* global _UR_ */
	const { assetsURL, liveDemoURL, isPro } =
		typeof _UR_ !== "undefined" && _UR_;
	const [{ upgradeModal }, dispatch] = useStateValue();

	const {
		data,
		isChecked,
		onCheckedChange,
		isPerformingBulkAction,
		selectedFeaturesSlugs,
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
	} = data;
	const [featureStatus, setFeatureStatus] = useState(status);
	const [isPerformingAction, setIsPerformingAction] = useState(false);

	const onToggle = () => {
		if (isPro) {
			//Toggle Handler
			if (featureStatus === "disabled") {
				enableFeature(slug)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});

							setFeatureStatus("enabled");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
							setFeatureStatus("disabled");
						}
					})
					.finally(() => {
						setIsPerformingAction(false);
					});
			} else {
				disableFeature(slug)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
							// window.location.reload();
							setFeatureStatus("disabled");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
							setFeatureStatus("enabled");
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
		setFeatureStatus(data.status);

		if (!upgradeModal.enable) {
			setIsPerformingAction(false);
		}
	}, [data, upgradeModal]);

	const handleBoxClick = () => {
		const upgradeModalRef = { ...upgradeModal };

		if (!isPro) {
			upgradeModalRef.type = "pro";
			upgradeModalRef.moduleType = "feature";
			upgradeModalRef.enable = true;
			upgradeModalRef.moduleName = data.name;
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
				opacity={isPro ? 1 : 0.7}
				onClick={() => {
					!isPro && handleBoxClick();
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
					{__("Pro", "user-registration")}
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
										isPro
											? onCheckedChange(
													slug,
													e.target.checked
											  )
											: handleBoxClick();
									}}
									isDisabled={
										isPerformingAction ||
										(selectedFeaturesSlugs.includes(slug) &&
											isPerformingBulkAction)
									}
								>
									{title}
								</Checkbox>
							</Heading>
							<Switch
								colorScheme="green"
								onChange={onToggle}
								isChecked={featureStatus === "enabled"}
								isDisabled={
									isPerformingAction ||
									(selectedFeaturesSlugs.includes(slug) &&
										isPerformingBulkAction) ||
									isPro
								}
							/>
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
			</Box>
		</Box>
	);
};

export default FeatureItem;
