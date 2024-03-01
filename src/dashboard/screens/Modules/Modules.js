import React, { useState, useEffect } from "react";
import {
	Box,
	Container,
	Stack,
	Select,
	Tabs,
	Tab,
	TabList,
	TabPanels,
	TabPanel,
	Button,
	InputGroup,
	InputRightElement,
	Input,
	FormControl,
	useToast,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { Search } from "../../components/Icon/Icon";
import Features from "./Features/Features";
import Addons from "./Addons/Addons";
import { isEmpty } from "../../../utils/utils";
import {
	getAllAddons,
	bulkActivateAddons,
	bulkDeactivateAddons,
	bulkInstallAddon,
} from "./Addons/addons-api";
import {
	bulkEnableFeatures,
	bulkDisableFeatures,
	getAllFeatures,
} from "./Features/features-api";
import { useOnType } from "use-ontype";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/gettingStartedContext";

const Modules = () => {
	const [tabIndex, setTabIndex] = useState(0);
	const [selectedAddonsSlugs, setSelectedAddonsSlugs] = useState([]);
	const [selectedAddonsNames, setSelectedAddonsNames] = useState([]);
	const [selectedFeaturesSlugs, setSelectedFeaturesSlugs] = useState([]);
	const [selectedFeaturesNames, setSelectedFeaturesNames] = useState([]);
	const [bulkAction, setBulkAction] = useState("");
	const [isPerformingBulkAction, setIsPerformingBulkAction] = useState(false);
	const toast = useToast();
	const [addonsLoaded, setAddonsLoaded] = useState(false);
	const [featuresLoaded, setFeaturesLoaded] = useState(false);
	const [isSearching, setIsSearching] = useState(false);
	const [{ allAddons, allFeatures }, dispatch] = useStateValue();
	const [filteredAddons, setFilteredAddons] = useState([]);
	const [filteredFeatures, setFilteredFeatures] = useState([]);

	const bulkOptions = [
		{
			enable: `${__("Enable", "user-registration")}`,
			disable: `${__("Disable", "user-registration")}`,
		},
		{
			activate: `${__("Activate", "user-registration")}`,
			deactivate: `${__("Deactivate", "user-registration")}`,
			install: `${__("Install", "user-registration")}`,
		},
	];

	useEffect(() => {}, [selectedAddonsSlugs]);
	useEffect(() => {
		if (!addonsLoaded) {
			getAllAddons().then((data) => {
				if (data.success) {
					dispatch({
						type: actionTypes.GET_ALL_ADDONS,
						allAddons: data.addons_lists,
					});

					setFilteredAddons(data.addons_lists);
					setAddonsLoaded(true);
				}
			});
		}
		if (!featuresLoaded) {
			getAllFeatures().then((data) => {
				if (data.success) {
					dispatch({
						type: actionTypes.GET_ALL_Features,
						allFeatures: data.features_lists,
					});

					setFilteredFeatures(data.features_lists);
					setFeaturesLoaded(true);
				}
			});
		}
	}, [addonsLoaded, filteredAddons, featuresLoaded, filteredFeatures]);

	const handleBulkActions = () => {
		setIsPerformingBulkAction(true);

		if (tabIndex === 0) {
			if (bulkAction === "enable") {
				bulkEnableFeatures(selectedFeaturesSlugs)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
					})
					.finally(() => {
						setIsPerformingBulkAction(false);
					});
			} else {
				bulkDisableFeatures(selectedFeaturesSlugs)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
					})
					.finally(() => {
						setIsPerformingBulkAction(false);
					});
			}
			setFeaturesLoaded(false);
		} else {
			if (bulkAction === "activate") {
				bulkActivateAddons(selectedAddonsSlugs)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
					})
					.finally(() => {
						setIsPerformingBulkAction(false);
					});
			} else if (bulkAction === "deactivate") {
				bulkDeactivateAddons(selectedAddonsSlugs)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
					})
					.finally(() => {
						setIsPerformingBulkAction(false);
					});
			} else if (bulkAction === "install") {
				const addonData = selectedAddonsSlugs.map((slug, index) => ({
					slug: slug,
					name: selectedAddonsNames[index],
				}));
				bulkInstallAddon(addonData)
					.then((data) => {
						if (data.success) {
							toast({
								title: data.message,
								status: "success",
								duration: 3000,
							});
							// window.location.reload();
							// setAddonStatus("active");
						} else {
							toast({
								title: data.message,
								status: "error",
								duration: 3000,
							});
							// setAddonStatus("inactive");
						}
					})
					.catch((e) => {
						toast({
							title: e.message,
							status: "error",
							duration: 3000,
						});
						// setAddonStatus("inactive");
					})
					.finally(() => {
						setIsPerformingBulkAction(false);
					});
			}
			setAddonsLoaded(false);
		}
	};

	const onSearchInput = useOnType(
		{
			onTypeStart: (val) => {
				setIsSearching(true);
			},
			onTypeFinish: (val) => {
				if (0 === tabIndex) {
					if (isEmpty(val)) {
						setFeaturesLoaded(false);
					} else {
						const searchedData = allFeatures?.filter((feature) =>
							feature.title
								.toLowerCase()
								.includes(val.toLowerCase())
						);
						if (!isEmpty(searchedData)) {
							setFilteredFeatures(searchedData);
							setFeaturesLoaded(true);
						} else {
							setFeaturesLoaded(false);
						}
					}
				} else {
					if (isEmpty(val)) {
						setAddonsLoaded(false);
					} else {
						const searchedData = allAddons?.filter((addon) =>
							addon.title
								.toLowerCase()
								.includes(val.toLowerCase())
						);
						if (!isEmpty(searchedData)) {
							setFilteredAddons(searchedData);
							setAddonsLoaded(true);
						} else {
							setAddonsLoaded(false);
						}
					}
				}

				setIsSearching(false);
			},
		},
		800
	);

	const parseDate = (dateString) => {
		const [day, month, year] = dateString.split("/").map(Number);
		return new Date(year, month - 1, day);
	};

	const handleSorterChange = (sortType, data, setData) => {
		console.log(data);
		switch (sortType) {
			case "newest":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(secondAddonInContext.released_date) -
							parseDate(firstAddonInContext.released_date)
					)
				);
				break;
			case "oldest":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(firstAddonInContext.released_date) -
							parseDate(secondAddonInContext.released_date)
					)
				);
				break;
			case "asc":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							firstAddonInContext.title.localeCompare(
								secondAddonInContext.title
							)
					)
				);
				break;
			case "desc":
				setData(
					[...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							secondAddonInContext.title.localeCompare(
								firstAddonInContext.title
							)
					)
				);
				break;
			default:
				console.log(tabIndex);
				if (0 === tabIndex) {
					getAllFeatures().then((data) => {
						if (data.success) {
							dispatch({
								type: actionTypes.GET_ALL_Features,
								allFeatures: data.features_lists,
							});

							setFilteredFeatures(data.features_lists);
							setFeaturesLoaded(true);
						}
					});
				} else {
					getAllAddons().then((data) => {
						if (data.success) {
							dispatch({
								type: actionTypes.GET_ALL_ADDONS,
								allAddons: data.addons_lists,
							});

							setFilteredAddons(data.addons_lists);
							setAddonsLoaded(true);
						}
					});
				}
		}
	};
	return (
		<Box top="var(--wp-admin--admin-bar--height, 0)" zIndex={1}>
			<Container maxW="container.xl">
				<Stack
					direction="row"
					minH="70px"
					justify="space-between"
					px="6"
				>
					<Stack direction="row" align="center" gap="7">
						<Select
							display="inline-flex"
							alignItems="center"
							size="md"
							bg="#DFDFE0"
							onChange={(e) => {
								if (tabIndex === 0) {
									handleSorterChange(
										e.target.value,
										filteredFeatures,
										setFilteredFeatures
									);
								} else {
									handleSorterChange(
										e.target.value,
										filteredAddons,
										setFilteredAddons
									);
								}
							}}
							icon=""
							width="fit-content"
						>
							<option value="default">
								{__("Most Downloaded", "user-registration")}
							</option>
							<option value="newest">
								{__("Newest", "user-registration")}
							</option>
							<option value="oldest">
								{__("Oldest", "user-registration")}
							</option>
							<option value="asc">
								{__("Ascending", "user-registration")}
							</option>
							<option value="desc">
								{__("Descending", "user-registration")}
							</option>
						</Select>

						<Tabs
							index={tabIndex}
							border="1px solid #DFDFE0"
							onChange={(index) => {
								setTabIndex(index);
							}}
						>
							<TabList>
								<Tab
									fontSize="14px"
									borderRadius="4px 0 0 4px"
									_selected={{
										color: "white",
										bg: "#2563EB",
									}}
								>
									{__("Features", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									borderRadius="0 4px 4px 0"
									_selected={{
										color: "white",
										bg: "#2563EB",
									}}
								>
									{__("Addons", "user-registration")}
								</Tab>
							</TabList>
						</Tabs>

						<Select
							display="inline-flex"
							alignItems="center"
							size="md"
							bg="#DFDFE0"
							placeholder={__(
								"Bulk Actions",
								"user-registration"
							)}
							onChange={(e) => setBulkAction(e.target.value)}
							icon=""
							width="fit-content"
						>
							{Object.entries(bulkOptions[tabIndex]).map(
								([option_key, option_value], k) => (
									<option key={k} value={option_key}>
										{option_value}
									</option>
								)
							)}
						</Select>

						<Button
							fontSize="14px"
							variant="outline"
							fontWeight="normal"
							color="gray.600"
							borderRadius="base"
							borderColor="gray.300"
							textDecor="none !important"
							py="3"
							px="6"
							onClick={handleBulkActions}
							isLoading={isPerformingBulkAction}
						>
							{__("Apply", "user-registration")}
						</Button>
					</Stack>
					<Stack direction="row" align="center" gap="7">
						<FormControl>
							<InputGroup>
								<InputRightElement pointerEvents="none">
									<Search h="5" w="5" color="gray.300" />
								</InputRightElement>
								<Input
									type="text"
									placeholder={__(
										"Search...",
										"user-registration"
									)}
									{...onSearchInput}
								/>
							</InputGroup>
						</FormControl>
					</Stack>
				</Stack>
			</Container>
			<Container maxW="container.xl">
				{isSearching ? (
					<AddonSkeleton />
				) : (
					<Box>
						<Tabs index={tabIndex}>
							<TabPanels>
								<TabPanel>
									<Features
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredFeatures={filteredFeatures}
										selectedFeaturesSlugs={
											selectedFeaturesSlugs
										}
										setSelectedFeaturesSlugs={
											setSelectedFeaturesSlugs
										}
									/>
								</TabPanel>
								<TabPanel>
									<Addons
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={filteredAddons}
										selectedAddonsSlugs={
											selectedAddonsSlugs
										}
										setSelectedAddonsSlugs={
											setSelectedAddonsSlugs
										}
										setSelectedAddonsNames={
											setSelectedAddonsNames
										}
									/>
								</TabPanel>
							</TabPanels>
						</Tabs>
					</Box>
				)}
			</Container>
		</Box>
	);
};

export default Modules;
