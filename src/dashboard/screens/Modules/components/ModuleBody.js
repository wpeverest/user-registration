/**
 *  External Dependencies
 */
import React, { useState, useEffect } from "react";
import {
	Tabs,
	Container,
	Modal,
	ModalOverlay,
	ModalContent,
	ModalCloseButton,
	ModalFooter,
	Button,
	Text,
	SimpleGrid,
	Input,
	Link,
	VStack,
	useToast
} from "@chakra-ui/react";
import { sprintf, __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import { isArray, isEmpty } from "../../../../utils/utils";
import { actionTypes } from "../../../../context/dashboardContext";
import { useStateValue } from "../../../../context/StateProvider";
import { Lock } from "../../../components/Icon/Icon";
import ModuleItem from "./ModuleItem";
import AddonSkeleton from "../../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { activateLicense } from "./modules-api";

const ModuleBody = ({
	isPerformingBulkAction,
	filteredAddons,
	setSelectedModuleData,
	selectedModuleData
}) => {
	/* global _UR_DASHBOARD_ */
	const { upgradeURL, licenseActivationURL, isPro } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ upgradeModal }, dispatch] = useStateValue();
	const [upgradeContent, setUpgradeContent] = useState({
		title: "",
		body: "",
		buttonText: __("Upgrade to Pro", "user-registration"),
		upgradeURL:
			upgradeURL +
			"&utm_source=dashboard-all-features&utm_medium=upgrade-popup",
		licenseActivationPlaceholder: __("License key", "user-registration")
	});

	const toast = useToast();

	const [licenseActivationKey, setLicenseKey] = useState("");
	const [licenseActivationValidationMessage, setLicenseValidationMessage] =
		useState("");
	const [reloadPage, setReloadPage] = useState(false);

	const handleActivationKeyChange = (event) => {
		setLicenseKey(event.target.value);
	};

	const [licenseValidationStatus, setLicenseValidationStatus] = useState("");

	const [isLicenseActivation, setLicenseActivation] = useState(false);

	const handleCheckedChange = (slug, checked, name, type) => {
		var selectedModules = { ...selectedModuleData };

		if (checked) {
			selectedModules[slug] = {
				slug: slug + "/" + slug + ".php",
				name,
				type
			};
		} else {
			if (selectedModules.hasOwnProperty(slug)) {
				delete selectedModules[slug];
			}
		}
		setSelectedModuleData(selectedModules);
	};

	useEffect(() => {
		const upgradeContentRef = { ...upgradeContent };

		if (upgradeModal.enable) {
			if (upgradeModal.type === "pro") {
				upgradeContentRef.title = __(
					"User Registration Pro Required",
					"user-registration"
				);
				upgradeContentRef.body = sprintf(
					__(
						"%s requires User Registration Pro to be activated. Please upgrade to a premium plan and unlock this addon",
						"user-registration"
					),
					upgradeModal.moduleName
				);
			} else if (upgradeModal.type === "license") {
				upgradeContentRef.title = __(
					"License Activation Required",
					"user-registration"
				);
				upgradeContentRef.body = sprintf(
					__(
						"Please activate license of User Registration Pro plugin in order to use %s",
						"user-registration"
					),
					upgradeModal.moduleName
				);
				upgradeContentRef.buttonText = sprintf(
					__("Activate License", "user-registration"),
					upgradeModal.moduleName
				);
				upgradeContentRef.licenseActivationPlaceholder = sprintf(
					__("Enter your license key", "user-registration"),
					upgradeModal.moduleName
				);
				upgradeContentRef.upgradeURL = licenseActivationURL;
			} else if (upgradeModal.type === "requirement") {
				upgradeContentRef.title = __(
					"Activation Requirement not Fulfilled",
					"user-registration"
				);
				upgradeContentRef.body = sprintf(
					__(
						"%s requires Membership module to be activated. Please activate Membership module in order to activate %s",
						"user-registration"
					),
					upgradeModal.moduleName,
					upgradeModal.moduleName
				);
				upgradeContentRef.buttonText = sprintf(
					__("Ok", "user-registration"),
					upgradeModal.moduleName
				);
				upgradeContentRef.upgradeURL = "";
			} else {
				upgradeContentRef.title = __(
					"License Upgrade Required",
					"user-registration"
				);
				upgradeContentRef.body = sprintf(
					__(
						"%s is only available in the plus plan and above. Please upgrade to a plus plan and above to unlock this addon",
						"user-registration"
					),
					upgradeModal.moduleName
				);
				upgradeContentRef.buttonText = sprintf(
					__("Upgrade Plan", "user-registration"),
					upgradeModal.moduleName
				);
			}

			setUpgradeContent(upgradeContentRef);
		}
	}, [upgradeModal]);

	useEffect(() => {
		if (reloadPage) {
			window.location.reload();
			setReloadPage(false);
		}
	}, [reloadPage]);

	const updateUpgradeModal = () => {
		const upgradeModalRef = { ...upgradeModal };
		upgradeModalRef.enable = false;
		dispatch({
			type: actionTypes.GET_UPGRADE_MODAL,
			upgradeModal: upgradeModalRef
		});
	};

	const licenseActivation = (upgradeURL) => {
		if ("" === upgradeURL) {
			const upgradeModalRef = { ...upgradeModal };
			upgradeModalRef.enable = false;
			dispatch({
				type: actionTypes.GET_UPGRADE_MODAL,
				upgradeModal: upgradeModalRef
			});
		} else {
			if ("" === licenseActivationKey) {
				setLicenseValidationMessage(
					sprintf(
						__(
							"Please enter your plugin activation license key",
							"user-registration"
						)
					)
				);
				setLicenseValidationStatus(true);
			} else if (licenseActivationKey.length < 32) {
				setLicenseValidationMessage(
					sprintf(
						__(
							"Please enter the valid license key",
							"user-registration"
						)
					)
				);
				setLicenseValidationStatus(true);
			} else {
				setLicenseValidationMessage("");
				setLicenseValidationStatus(false);
				setLicenseActivation(true);

				activateLicense(licenseActivationKey)
					.then((data) => {
						setLicenseActivation(true);
						if (data.code === 200) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000
							});
							setLicenseActivation(false);
							setReloadPage(true);
						} else if (data.code === 400) {
							toast({
								title: data.message,
								status: "error",
								duration: 3000
							});
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000
						});
					})
					.finally(() => {
						setLicenseActivation(false);
					});
			}
		}
	};

	return (
		<>
			<Tabs>
				{upgradeModal.enable && (
					<Modal
						isOpen={true}
						onClose={updateUpgradeModal}
						size="lg"
						isCentered
					>
						<ModalOverlay />
						<ModalContent
							alignItems={"center"}
							p="50px 11px 55px 11px"
						>
							<Lock h={"131px"} w={"150px"} />
							<Text
								fontSize="24px"
								lineHeight="44px"
								fontWeight="600"
							>
								{upgradeContent.title}
							</Text>
							<ModalCloseButton boxShadow="none !important" />
							<Text
								fontSize="16px"
								lineHeight="26px"
								fontWeight="400"
								padding="10px 50px"
							>
								{upgradeContent.body}
							</Text>
							<ModalFooter paddingBottom="0px" w="400px">
								<VStack width="100%">
									{isPro && (
										<Input
											placeholder={
												upgradeContent.licenseActivationPlaceholder
											}
											onChange={handleActivationKeyChange}
										/>
									)}
									<Button
										colorScheme="primary"
										color="white !important"
										textDecor="none !important"
										onClick={() =>
											licenseActivation(
												upgradeContent.upgradeURL
											)
										}
										w="100%"
										as={
											!isPro &&
											"" !== upgradeContent.upgradeURL
												? Link
												: undefined
										}
										href={
											!isPro &&
											"" !== upgradeContent.upgradeURL
												? upgradeContent.upgradeURL
												: ""
										}
										isLoading={isLicenseActivation}
										{...(!isPro &&
											"" !==
												upgradeContent.upgradeURL && {
												isExternal: true
											})}
									>
										{upgradeContent.buttonText}
									</Button>
									{isPro &&
										licenseActivationValidationMessage && (
											<Text fontSize="md" color="red">
												{
													licenseActivationValidationMessage
												}
											</Text>
										)}
								</VStack>
							</ModalFooter>
						</ModalContent>
					</Modal>
				)}
				<Container maxW="container.xl">
					{isEmpty(filteredAddons) ? (
						<AddonSkeleton />
					) : (
						<SimpleGrid columns={3} spacing="5">
							{isArray(filteredAddons) &&
								filteredAddons?.map((data) => (
									<ModuleItem
										key={data.slug}
										data={data}
										isChecked={selectedModuleData.hasOwnProperty(
											data.slug
										)}
										onCheckedChange={(slug, checked) => {
											handleCheckedChange(
												slug,
												checked,
												data.name,
												data.type
											);
										}}
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										selectedModuleData={selectedModuleData}
									/>
								))}
						</SimpleGrid>
					)}
				</Container>
			</Tabs>
		</>
	);
};

export default ModuleBody;
