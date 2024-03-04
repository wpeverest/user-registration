import React, { useState, useEffect } from "react";
import AddonSkeleton from "../../../skeleton/AddonsSkeleton/AddonsSkeleton";
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
	Link,
	SimpleGrid,
} from "@chakra-ui/react";
import AddonItem from "./components/AddonItem";
import { isArray, isEmpty } from "../../../../utils/utils";
import { actionTypes } from "../../../../context/dashboardContext";
import { useStateValue } from "../../../../context/StateProvider";
import { Megaphone } from "../../../components/Icon/Icon";
import { sprintf, __ } from "@wordpress/i18n";

const Addons = ({
	isPerformingBulkAction,
	filteredAddons,
	selectedAddonsSlugs,
	setSelectedAddonsSlugs,
	setSelectedAddonsNames,
}) => {
	/* global _UR_ */
	const { upgradeURL, licenseActivationURL } =
		typeof _UR_ !== "undefined" && _UR_;
	const [{ upgradeModal }, dispatch] = useStateValue();
	const [upgradeContent, setUpgradeContent] = useState({
		title: "",
		body: "",
		buttonText: __("Upgrade to Pro", "user-registration"),
		upgradeURL: upgradeURL,
	});
	const handleCheckedChange = (slug, checked, name) => {
		if (checked) {
			setSelectedAddonsSlugs((prev) => [
				...prev,
				slug + "/" + slug + ".php",
			]);
			setSelectedAddonsNames((prev) => [...prev, name]);
		} else {
			setSelectedAddonsSlugs((prev) =>
				prev.filter((s) => s !== slug + "/" + slug + ".php")
			);
			setSelectedAddonsNames((prev) => prev.filter((s) => s !== name));
		}
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
				upgradeContentRef.buttonText = licenseActivationURL;
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

	const updateUpgradeModal = () => {
		const upgradeModalRef = { ...upgradeModal };
		upgradeModalRef.enable = false;
		dispatch({
			type: actionTypes.GET_UPGRADE_MODAL,
			upgradeModal: upgradeModalRef,
		});
	};

	return (
		<>
			<Tabs>
				{upgradeModal.enable && (
					<Modal isOpen={true} onClose={updateUpgradeModal} size="lg">
						<ModalOverlay />
						<ModalContent
							alignItems={"center"}
							p="50px 11px 55px 11px"
						>
							<Megaphone h={"131px"} w={"150px"} />
							<Text
								fontSize="24px"
								lineHeight="44px"
								fontWeight="600"
							>
								{upgradeContent.title}
							</Text>
							<ModalCloseButton />
							<Text
								fontSize="16px"
								lineHeight="26px"
								fontWeight="400"
								padding="10px 50px"
							>
								{upgradeContent.body}
							</Text>
							<ModalFooter>
								<Button
									as={Link}
									colorScheme="primary"
									href={upgradeURL}
									color="white !important"
									textDecor="none !important"
									isExternal
									onClick={upgradeContent.upgradeURL}
								>
									{upgradeContent.buttonText}
								</Button>
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
									<AddonItem
										key={data.slug}
										data={data}
										isChecked={Object.values(
											selectedAddonsSlugs
										)?.includes(
											data.slug + "/" + data.slug + ".php"
										)}
										onCheckedChange={(slug, checked) =>
											handleCheckedChange(
												slug,
												checked,
												data.name
											)
										}
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										selectedAddonsSlugs={
											selectedAddonsSlugs
										}
									/>
								))}
						</SimpleGrid>
					)}
				</Container>
			</Tabs>
		</>
	);
};

export default Addons;
