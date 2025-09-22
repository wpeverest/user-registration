import React, { useEffect } from "react";
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
	VStack
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";

import { useUpgradeModal } from "../hooks/useUpgradeModal";
import { useLicenseActivation } from "../hooks/useLicenseActivation";
import { useStateValue } from "../../../../context/StateProvider";
import { actionTypes } from "../../../../context/dashboardContext";
import { Lock } from "../../../components/Icon/Icon";

import ModuleItem from "./ModuleItem";
import AddonSkeleton from "../../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { isArray, isEmpty } from "../../../../utils/utils";

const ModuleBody = ({
	isPerformingBulkAction,
	filteredAddons,
	setSelectedModuleData,
	selectedModuleData,
	IsStateUpdated,
	resetIsStateUpdated
}) => {
	/* global _UR_DASHBOARD_ */
	const { upgradeURL, licenseActivationURL, isPro } = _UR_DASHBOARD_ || {};
	const [{ upgradeModal }, dispatch] = useStateValue();
	const upgradeContent = useUpgradeModal(
		upgradeModal,
		upgradeURL,
		licenseActivationURL
	);

	const {
		licenseKey,
		setLicenseKey,
		isLicenseActivation,
		validationMessage,
		handleActivation
	} = useLicenseActivation(() => window.location.reload());

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

	const closeModal = () => {
		dispatch({
			type: actionTypes.GET_UPGRADE_MODAL,
			upgradeModal: { enable: false }
		});
	};

	return (
		<Tabs>
			{upgradeModal.enable && (
				<Modal isOpen onClose={closeModal} size="lg" isCentered>
					<ModalOverlay />
					<ModalContent alignItems="center" p="50px 11px 55px 11px">
						<Lock h={"131px"} w={"150px"} />
						<Text
							fontSize="24px"
							fontWeight="600"
							lineHeight="44px"
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
										value={licenseKey}
										onChange={(e) =>
											setLicenseKey(e.target.value)
										}
									/>
								)}
								<Button
									colorScheme="primary"
									color="white !important"
									textDecor="none !important"
									w="100%"
									onClick={handleActivation}
									isLoading={isLicenseActivation}
									as={!isPro ? Link : undefined}
									href={
										!isPro
											? upgradeContent.upgradeURL
											: undefined
									}
									isExternal={!isPro}
									width="100%"
								>
									{upgradeContent.buttonText}
								</Button>
								{isPro && validationMessage && (
									<Text fontSize="md" color="red">
										{validationMessage}
									</Text>
								)}
							</VStack>
						</ModalFooter>
					</ModalContent>
				</Modal>
			)}
			<Container maxWidth="2000px !important" padding={0}>
				{isEmpty(filteredAddons) ? (
					<AddonSkeleton />
				) : (
					<SimpleGrid columns={4} spacing="30px">
						{isArray(filteredAddons) &&
							filteredAddons.map((data) => (
								<ModuleItem
									key={data.slug}
									data={data}
									isChecked={
										"undefined" !==
											typeof selectedModuleData &&
										selectedModuleData.hasOwnProperty(
											data.slug
										)
									}
									onCheckedChange={(slug, checked) =>
										handleCheckedChange(
											slug,
											checked,
											data.name,
											data.type
										)
									}
									isPerformingBulkAction={
										isPerformingBulkAction
									}
									selectedModuleData={selectedModuleData}
									IsStateUpdated={IsStateUpdated}
									resetIsStateUpdated={resetIsStateUpdated}
								/>
							))}
					</SimpleGrid>
				)}
			</Container>
		</Tabs>
	);
};

export default ModuleBody;
