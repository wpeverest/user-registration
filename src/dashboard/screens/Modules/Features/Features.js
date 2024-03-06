/**
 *  External Dependencies
 */
import React, { useEffect } from "react";
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
import { sprintf, __ } from "@wordpress/i18n";

/**
 *  Internal Dependencies
 */
import FeatureItem from "./components/FeatureItem";
import { isArray, isEmpty } from "../../../../utils/utils";
import { actionTypes } from "../../../../context/dashboardContext";
import { useStateValue } from "../../../../context/StateProvider";
import { Lock } from "../../../components/Icon/Icon";

const Features = ({
	isPerformingBulkAction,
	filteredFeatures,
	selectedFeaturesSlugs,
	setSelectedFeaturesSlugs,
}) => {
	/* global _UR_DASHBOARD_ */
	const { upgradeURL } =
		typeof _UR_DASHBOARD_ !== "undefined" && _UR_DASHBOARD_;
	const [{ upgradeModal }, dispatch] = useStateValue();
	const handleCheckedChange = (slug, checked) => {
		if (checked) {
			setSelectedFeaturesSlugs((prev) => [...prev, slug]);
		} else {
			setSelectedFeaturesSlugs((prev) => prev.filter((s) => s !== slug));
		}
	};

	useEffect(() => {}, [upgradeModal]);

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
								{__(
									"Unlock all Addons of User Registration",
									"user-registration"
								)}
							</Text>
							<ModalCloseButton />
							<Text
								fontSize="16px"
								lineHeight="26px"
								fontWeight="400"
								padding="10px 50px"
							>
								{__(
									"This Addon is only available in the pro version. Please upgrade to a pro plan and unlock all addons",
									"user-registration"
								)}
							</Text>
							<ModalFooter>
								<Button
									as={Link}
									colorScheme="primary"
									href={upgradeURL}
									color="white !important"
									textDecor="none !important"
									isExternal
									onClick={updateUpgradeModal}
								>
									{__("Upgrade to Pro", "user-registration")}
								</Button>
							</ModalFooter>
						</ModalContent>
					</Modal>
				)}
				<Container maxW="container.xl">
					{isEmpty(filteredFeatures) ? (
						<AddonSkeleton />
					) : (
						<SimpleGrid columns={3} spacing="5">
							{isArray(filteredFeatures) &&
								filteredFeatures?.map((data) => (
									<FeatureItem
										key={data.slug}
										data={data}
										isChecked={Object.values(
											selectedFeaturesSlugs
										)?.includes(data.slug)}
										onCheckedChange={(slug, checked) =>
											handleCheckedChange(slug, checked)
										}
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										selectedFeaturesSlugs={
											selectedFeaturesSlugs
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

export default Features;
