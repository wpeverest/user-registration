import React, { useEffect, useState } from "react";
import apiFetch from "@wordpress/api-fetch";
import {
	Button,
	useToast,
	Spinner,
	Box,
	Text,
	Table,
	Tbody,
	Tr,
	Td,
	Icon,
	Divider,
	VStack
} from "@chakra-ui/react";
import { CheckCircleIcon, WarningIcon } from "@chakra-ui/icons";
import { __, sprintf } from "@wordpress/i18n";

const { security } = ur_templates_script;

const PluginStatus = ({ requiredPlugins, onActivateAndContinue }) => {
	const [pluginStatuses, setPluginStatuses] = useState({});
	const [loading, setLoading] = useState(false);
	const [installInProgress, setInstallInProgress] = useState(false);
	const [installComplete, setInstallComplete] = useState(false);
	const [buttonLabel, setButtonLabel] = useState("");
	const toast = useToast();
	useEffect(() => {
		const fetchPluginStatus = async () => {
			try {
				const data = await apiFetch({
					path: `user-registration/v1/plugin/status`,
					method: "GET",
					headers: {
						"X-WP-Nonce": security
					}
				});

				if (data?.success && data?.plugin_status) {
					setPluginStatuses(data.plugin_status);
					updateButtonLabel(data.plugin_status);
					return;
				}

				toast({
					title: __("Error", "user-registration"),
					description: __(
						"Invalid response format.",
						"user-registration"
					),
					status: "error",
					position: "bottom-right",
					duration: 5000,
					isClosable: true,
					variant: "subtle"
				});
			} catch (error) {
				toast({
					title: __("Error", "user-registration"),
					description:
						error?.message ||
						__(
							"Unable to check plugin status.",
							"user-registration"
						),
					status: "error",
					position: "bottom-right",
					duration: 5000,
					isClosable: true,
					variant: "subtle"
				});
			}
		};

		fetchPluginStatus();
	}, [toast, requiredPlugins]);

	const updateButtonLabel = (statuses) => {
		const allActive = requiredPlugins.every(
			(plugin) => statuses[plugin.key] === "active"
		);
		const anyNotInstalled = requiredPlugins.some(
			(plugin) => statuses[plugin.key] === "not-installed"
		);
		const anyInactive = requiredPlugins.some(
			(plugin) => statuses[plugin.key] === "inactive"
		);

		if (allActive) {
			setButtonLabel(__("Continue", "user-registration"));
			setInstallComplete(true);
		} else if (anyNotInstalled) {
			setButtonLabel(__("Install & Activate", "user-registration"));
			setInstallComplete(false);
		} else if (anyInactive) {
			setButtonLabel(__("Activate and Continue", "user-registration"));
			setInstallComplete(false);
		} else {
			setButtonLabel(__("Continue", "user-registration"));
			setInstallComplete(false);
		}
	};

	const handleButtonClick = async () => {
		if (installComplete) {
			onActivateAndContinue();
		} else {
			const anyNotInstalled = requiredPlugins.some(
				(plugin) => pluginStatuses[plugin.key] === "not-installed"
			);
			const anyInactive = requiredPlugins.some(
				(plugin) => pluginStatuses[plugin.key] === "inactive"
			);

			if (anyInactive || anyNotInstalled) {
				setLoading(true);
				setInstallInProgress(true);

				let finalMessage = "";
				let finalTitle = "";
				let finalResponseType = "";
				for (const plugin of requiredPlugins) {
					try {
						const response = await apiFetch({
							path: `user-registration/v1/plugin/activate`,
							method: "POST",
							body: JSON.stringify({
								addonData: {
									name: plugin.value,
									slug: plugin.key,
									type:
										pluginStatuses[plugin.key] ===
										"not-installed"
											? "addon"
											: "addon"
								}
							}),
							headers: {
								"Content-Type": "application/json",
								"X-WP-Nonce": security
							}
						});

						if (response.success) {
							setPluginStatuses((prevStatuses) => ({
								...prevStatuses,
								[plugin.key]: "active"
							}));

							finalMessage =
								response.message ||
								__(
									"Plugin activated successfully.",
									"user-registration"
								);
							finalTitle = __("Success", "user-registration");
							finalResponseType = "success";
						} else {
							setPluginStatuses((prevStatuses) => ({
								...prevStatuses,
								[plugin.key]: "error"
							}));

							finalMessage =
								response.message ||
								sprintf(
									__(
										"Failed to activate plugin: %s.",
										"user-registration"
									),
									plugin.value
								);
							finalTitle = __("Error", "user-registration");
							finalResponseType = "error";
						}
					} catch (error) {
						setPluginStatuses((prevStatuses) => ({
							...prevStatuses,
							[plugin.key]: "error"
						}));

						finalMessage =
							error.message ||
							sprintf(
								__(
									"Unable to activate %s.",
									"user-registration"
								),
								plugin.value
							);
						finalTitle = __("Error", "user-registration");
						finalResponseType = "error";
					}
				}

				setLoading(false);
				setInstallInProgress(false);
				setInstallComplete(true);
				setButtonLabel("Continue");

				toast({
					title: finalTitle,
					description: finalMessage,
					status: finalResponseType,
					position: "bottom-right",
					duration: 5000,
					isClosable: true,
					variant: "subtle"
				});
			} else {
				onActivateAndContinue();
			}
		}
	};
	return (
		<VStack spacing={4} align="stretch">
			{requiredPlugins?.length > 0 && (
				<>
					<Divider color={"gray.200"} mb={0} />
					<Text my={0} fontSize={16} color={"gray.700"}>
						This form template requires the following addons:
					</Text>
					<Box
						borderWidth="1px"
						borderRadius="md"
						overflow="hidden"
						w="100%"
					>
						<Table variant="simple">
							<Tbody>
								{requiredPlugins.map((plugin) => (
									<Tr key={plugin.key}>
										<Td>{plugin.value}</Td>
										<Td textAlign="right">
											{pluginStatuses[plugin.key] ===
											"active" ? (
												<Icon
													as={CheckCircleIcon}
													color="green"
												/>
											) : pluginStatuses[plugin.key] ===
													"inactive" ||
											  pluginStatuses[plugin.key] ===
													"not-installed" ? (
												<Icon
													as={WarningIcon}
													color="yellow"
												/>
											) : (
												<Spinner size="sm" />
											)}
										</Td>
									</Tr>
								))}
							</Tbody>
						</Table>
					</Box>
				</>
			)}
			{buttonLabel && (
				<Button
					marginLeft={"auto"}
					onClick={handleButtonClick}
					size="md"
					isLoading={loading}
					isDisabled={installInProgress}
					style={{
						backgroundColor: "#475BB2",
						color: "#FFFFFF"
					}}
				>
					{buttonLabel}
				</Button>
			)}
		</VStack>
	);
};

export default PluginStatus;
