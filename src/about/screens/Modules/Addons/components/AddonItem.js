import {
	Alert,
	AlertIcon,
	Badge,
	Box,
	Checkbox,
	Heading,
	Image,
	Stack,
	Switch,
	Text,
	useBoolean,
	useToast,
	Link,
	Button,
	Divider,
	HStack,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import React, { useState, useEffect } from "react";
import { isEmpty } from "../../../../../utils/utils";
import { activateAddon, deactivateAddon, installAddon } from "../addons-api";

const AddonItem = (props) => {
	/* global _UR_ */
	const { assetsURL } = typeof _UR_ !== "undefined" && _UR_;
	const {
		data,
		isChecked,
		onCheckedChange,
		isPerformingBulkAction,
		selectedSlugs,
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
	const [addonStatus, setAddonStatus] = useState(status);
	const [isPerformingAction, setIsPerformingAction] = useState(false);

	const handleAddonAction = () => {
		setIsPerformingAction(true);
		if (addonStatus === "inactive") {
			activateAddon(slug)
				.then((data) => {
					if (data.status === "active") {
						toast({
							title: __(
								"Addon activated successfully.",
								"user-registration"
							),
							status: "success",
							duration: 3000,
						});

						// window.location.reload();
						setAddonStatus("active");
					} else {
						toast({
							title: __(
								"Addon cannot be activated. Please try again later.",
								"user-registration"
							),
							status: "error",
							duration: 3000,
						});
						setAddonStatus("inactive");
					}
				})
				.finally(() => {
					setIsPerformingAction(false);
				});
		} else if (addonStatus === "active") {
			deactivateAddon(slug)
				.then((data) => {
					if (data.success) {
						toast({
							title: data.message,
							status: "success",
							duration: 3000,
						});
						// window.location.reload();
						setAddonStatus("inactive");
					} else {
						toast({
							title: data.message,
							status: "error",
							duration: 3000,
						});
						setAddonStatus("active");
					}
				})
				.finally(() => {
					setIsPerformingAction(false);
				});
		} else {
			installAddon(slug, name)
				.then((data) => {
					if (data.success) {
						toast({
							title: data.message,
							status: "success",
							duration: 3000,
						});
						// window.location.reload();
						setAddonStatus("inactive");
					} else {
						toast({
							title: data.message,
							status: "error",
							duration: 3000,
						});
						setAddonStatus("not-installed");
					}
				})
				.catch((e) => {
					toast({
						title: e.message,
						status: "error",
						duration: 3000,
					});
					setAddonStatus("not-installed");
				})
				.finally(() => {
					setIsPerformingAction(false);
				});
		}
	};

	useEffect(() => {
		setAddonStatus(data.status);
	}, [data]);

	return (
		<Box
			borderColor="gray.100"
			rounded="sm"
			bg="white"
			shadow="sm"
			h="full"
			minH="sm"
			pos="relative"
		>
			<Image
				src={assetsURL + image}
				objectFit="cover"
				borderTopRightRadius="sm"
				borderTopLeftRadius="sm"
				w="100%"
				h="40%"
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
				{required_plan}
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
								onChange={(e) =>
									onCheckedChange?.(slug, e.target.checked)
								}
							>
								{title}
							</Checkbox>
						</Heading>
					</Stack>

					<Text color="gray.500">{excerpt}</Text>
				</Stack>
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
						href={link}
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
						"active" === addonStatus
							? "red"
							: "inactive" === addonStatus
							? "green"
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
					onClick={handleAddonAction}
					isLoading={
						isPerformingAction ||
						(selectedSlugs.includes(slug + "/" + slug + ".php") &&
							isPerformingBulkAction)
					}
				>
					{"active" === addonStatus
						? __("Deactivate", "user-registration")
						: "inactive" === addonStatus
						? __("Activate", "user-registration")
						: __("Install", "user-registration")}
				</Button>
			</Box>
		</Box>
	);
};

export default AddonItem;
