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
	InputLeftElement,
	Input,
	FormControl,
	useToast,
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { Search } from "../../components/Icon/Icon";
import Features from "./Features/Features";
import Addons from "./Addons/Addons";
import {
	bulkActivateAddons,
	bulkDeactivateAddons,
	bulkInstallAddon,
} from "./Addons/addons-api";
import { isEmpty } from "../../../utils/utils";
import { getAllAddons } from "./Addons/addons-api";
import { useOnType } from "use-ontype";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/gettingStartedContext";

const Modules = () => {
	const [tabIndex, setTabIndex] = useState(0);
	const [selectedSlugs, setSelectedSlugs] = useState([]);
	const [selectedAddonsNames, setSelectedAddonsNames] = useState([]);
	const [bulkAction, setBulkAction] = useState("");
	const [isPerformingBulkAction, setIsPerformingBulkAction] = useState(false);
	const toast = useToast();
	const [addonsLoaded, setAddonsLoaded] = useState(false);
	const [isSearching, setIsSearching] = useState(false);
	const [{ allAddons }, dispatch] = useStateValue();
	const [filteredAddons, setFilteredAddons] = useState([]);

	useEffect(() => {}, [selectedSlugs]);
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
	}, [addonsLoaded, filteredAddons]);

	const handleBulkActions = () => {
		setIsPerformingBulkAction(true);

		if (bulkAction === "activate") {
			bulkActivateAddons(selectedSlugs)
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
		} else if (bulkAction === "deactivate") {
			bulkDeactivateAddons(selectedSlugs)
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
		} else if (bulkAction === "install") {
			const addonData = selectedSlugs.map((slug, index) => ({
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
	};

	const onSearchInput = useOnType(
		{
			onTypeStart: (val) => {
				setIsSearching(true);
			},
			onTypeFinish: (val) => {
				if (isEmpty(val)) {
					setAddonsLoaded(false);
				} else {
					const searchedData = allAddons?.filter((addon) =>
						addon.title.toLowerCase().includes(val.toLowerCase())
					);
					if (!isEmpty(searchedData)) {
						setFilteredAddons(searchedData);
						setAddonsLoaded(true);
					} else {
						setAddonsLoaded(false);
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

	const handleSorterChange = (e) => {
		const sortType = e.target.value;

		switch (sortType) {
			case "newest":
				setFilteredAddons(
					[...filteredAddons].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(secondAddonInContext.released_date) -
							parseDate(firstAddonInContext.released_date)
					)
				);
				break;
			case "oldest":
				setFilteredAddons(
					[...filteredAddons].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(firstAddonInContext.released_date) -
							parseDate(secondAddonInContext.released_date)
					)
				);
				break;
			case "asc":
				setFilteredAddons(
					[...filteredAddons].sort(
						(firstAddonInContext, secondAddonInContext) =>
							firstAddonInContext.title.localeCompare(
								secondAddonInContext.title
							)
					)
				);
				break;
			case "desc":
				setFilteredAddons(
					[...filteredAddons].sort(
						(firstAddonInContext, secondAddonInContext) =>
							secondAddonInContext.title.localeCompare(
								firstAddonInContext.title
							)
					)
				);
				break;
			default:
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
							onChange={handleSorterChange}
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
								{/* <Tab
									fontSize="14px"
									borderRadius="4px 0 0 4px"
									_selected={{
										color: "white",
										bg: "#2563EB",
									}}
								>
									{__("Features", "user-registration")}
								</Tab> */}
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
						>
							<option value="activate">
								{__("Activate", "user-registration")}
							</option>
							<option value="deactivate">
								{__("Deactivate", "user-registration")}
							</option>
							<option value="install">
								{__("Install", "user-registration")}
							</option>
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
								<Input
									type="text"
									placeholder={__(
										"Search...",
										"user-registration"
									)}
									{...onSearchInput}
								/>
								<InputLeftElement pointerEvents="none">
									<Search h="5" w="5" color="gray.300" />
								</InputLeftElement>
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
								{/* <TabPanel>
								<Features />
							</TabPanel> */}
								<TabPanel>
									<Addons
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={filteredAddons}
										selectedSlugs={selectedSlugs}
										setSelectedSlugs={setSelectedSlugs}
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
