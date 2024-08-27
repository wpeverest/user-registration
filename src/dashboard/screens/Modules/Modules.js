/**
 *  External Dependencies
 */
import React, { useState, useEffect, useCallback } from "react";
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
	Text,
	Image
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { debounce } from "lodash";

/**
 *  Internal Dependencies
 */
import { Search } from "../../components/Icon/Icon";
import { PageNotFound } from "../../components/Icon/Icon";

import {
	getAllModules,
	bulkActivateModules,
	bulkDeactivateModules
} from "./components/modules-api";
import AddonSkeleton from "../../skeleton/AddonsSkeleton/AddonsSkeleton";
import { useStateValue } from "../../../context/StateProvider";
import { actionTypes } from "../../../context/dashboardContext";
import ModuleBody from "./components/ModuleBody";

const Modules = () => {
	const toast = useToast();
	const [isSearching, setIsSearching] = useState(false);
	const [{ allModules }, dispatch] = useStateValue();
	const [modulesLoaded, setModulesLoaded] = useState(false);
	const [originalModules, setOriginalModules] = useState([]);
	const [selectedModuleData, setSelectedModuleData] = useState("");
	const [bulkAction, setBulkAction] = useState("");
	const [isPerformingBulkAction, setIsPerformingBulkAction] = useState(false);
	const [tabIndex, setTabIndex] = useState(0);
	const [searchItem, setSearchItem] = useState("");
	const [noItemFound, setNoItemFound] = useState(false);
	const [modules, setModules] = useState([]);

	const fetchModules = useCallback(() => {
		getAllModules()
			.then((data) => {
				if (data.success) {
					dispatch({
						type: actionTypes.GET_ALL_MODULES,
						allModules: data.modules_lists
					});
					setOriginalModules(data.modules_lists);
					filterModules(data.modules_lists);
					setModulesLoaded(true);
				}
			})
			.catch((error) => {
				setError(error.message);
			});
	}, [dispatch, tabIndex]);

	const filterModules = (modules) => {
		let filteredModules = modules;

		if (tabIndex === 1) {
			filteredModules = modules.filter(
				(module) => module.type === "feature"
			);
		} else if (tabIndex === 2) {
			filteredModules = modules.filter(
				(module) => module.type === "addon"
			);
		}
		setModules(filteredModules);
		setModulesLoaded(true);
	};

	useEffect(() => {
		fetchModules();
	}, [fetchModules]);

	useEffect(() => {
		filterModules(originalModules);
	}, [tabIndex, originalModules]);

	const handleBulkActions = () => {
		if (selectedModuleData.length < 1) {
			toast({
				title: __(
					"Please select at least a feature",
					"user-registration"
				),
				status: "error",
				duration: 3000
			});
		} else {
			setIsPerformingBulkAction(true);

			const actionFunction =
				bulkAction === "activate"
					? bulkActivateModules
					: bulkDeactivateModules;

			actionFunction(selectedModuleData)
				.then((data) => {
					toast({
						title: data.message,
						status: data.success ? "success" : "error",
						duration: 3000
					});
				})
				.catch((e) => {
					toast({
						title: e.message,
						status: "error",
						duration: 3000
					});
				})
				.finally(() => {
					setIsPerformingBulkAction(false);
					setSelectedModuleData({});
					fetchModules();
				});
		}
	};

	const debounceSearch = debounce((val) => {
		setIsSearching(true);

		if (!val) {
			filterModules(originalModules);
			setIsSearching(false);
			return;
		}

		let searchedData = [];

		if (tabIndex === 1) {
			searchedData = originalModules.filter(
				(module) =>
					module.type === "feature" &&
					module.title.toLowerCase().includes(val.toLowerCase())
			);
		} else if (tabIndex === 2) {
			searchedData = originalModules.filter(
				(module) =>
					module.type === "addon" &&
					module.title.toLowerCase().includes(val.toLowerCase())
			);
		} else {
			searchedData = originalModules.filter((module) =>
				module.title.toLowerCase().includes(val.toLowerCase())
			);
		}

		if (searchedData.length > 0) {
			setModules(searchedData);
			setModulesLoaded(true);
			setNoItemFound(false);
		} else {
			setModules([]);
			setModulesLoaded(false);
			setNoItemFound(true);
		}

		setIsSearching(false);
	}, 800);

	const handleSearchInputChange = (e) => {
		const val = e.target.value;
		setSearchItem(val);
		debounceSearch(val);
	};

	const parseDate = (dateString) => {
		const [day, month, year] = dateString.split("/").map(Number);
		return new Date(year, month - 1, day);
	};

	const handleSorterChange = (sortType, data, setData) => {
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
				setModulesLoaded(false);
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
					<Stack direction="row" align="center" gap="5">
						<Select
							display="inline-flex"
							alignItems="center"
							size="md"
							bg="#DFDFE0"
							onChange={(e) => {
								handleSorterChange(
									e.target.value,
									modules,
									setModules
								);
							}}
							border="1px solid #DFDFE0 !important"
							borderRadius="4px !important"
							icon=""
							width="fit-content"
						>
							<option value="default">
								{__("Popular", "user-registration")}
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
							onChange={(index) => {
								setTabIndex(index);
							}}
						>
							<TabList
								borderBottom="0px"
								border="1px solid #DFDFE0"
								borderRadius="4px"
							>
								<Tab
									fontSize="14px"
									borderRadius="4px 0 0 4px"
									style={{
										boxSizing: "border-box"
									}}
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									boxShadow="none !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: { value: searchItem }
										})
									}
								>
									{__("All Modules", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box"
									}}
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									boxShadow="none !important"
									borderRight="1px solid #E9E9E9"
									borderLeft="1px solid #E9E9E9"
									marginLeft="0px !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: { value: searchItem }
										})
									}
								>
									{__("Features", "user-registration")}
								</Tab>
								<Tab
									fontSize="14px"
									style={{
										boxSizing: "border-box"
									}}
									borderRadius="0 4px 4px 0"
									_focus={{
										boxShadow: "none"
									}}
									_selected={{
										color: "white",
										bg: "#2563EB",
										marginBottom: "0px",
										boxShadow: "none"
									}}
									marginLeft="0px !important"
									boxShadow="none !important"
									transition="none !important"
									onClick={() =>
										handleSearchInputChange({
											target: { value: searchItem }
										})
									}
								>
									{__("Addons", "user-registration")}
								</Tab>
							</TabList>
						</Tabs>

						<Box display="flex" gap="8px">
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
								border="1px solid #DFDFE0 !important"
								borderRadius="4px !important"
							>
								<option value="activate">
									{__("Activate", "user-registration")}
								</option>
								<option value="deactivate">
									{__("Deactivate", "user-registration")}
								</option>
							</Select>

							<Button
								fontSize="14px"
								variant="outline"
								fontWeight="normal"
								color="gray.600"
								borderRadius="base"
								border="1px solid #DFDFE0 !important"
								textDecor="none !important"
								padding="6px 12px"
								onClick={handleBulkActions}
								isLoading={isPerformingBulkAction}
							>
								{__("Apply", "user-registration")}
							</Button>
						</Box>
					</Stack>
					<Stack direction="row" align="center" gap="7">
						<FormControl>
							<InputGroup>
								<InputLeftElement
									pointerEvents="none"
									top="2px"
								>
									<Search h="5" w="5" color="gray.300" />
								</InputLeftElement>
								<Input
									type="text"
									placeholder={__(
										"Search...",
										"user-registration"
									)}
									paddingLeft="32px !important"
									value={searchItem}
									onChange={handleSearchInputChange}
								/>
							</InputGroup>
						</FormControl>
					</Stack>
				</Stack>
			</Container>
			<Container maxW="container.xl">
				{isSearching ? (
					<AddonSkeleton />
				) : noItemFound ? (
					<Box
						display="flex"
						justifyContent="center"
						flexDirection="column"
						padding="100px"
						gap="10px"
						alignItems="center"
					>
						<PageNotFound color="gray.300" />
						<Text fontSize="20px" fontWeight="600">
							{__("Sorry, no result found.", "user-registration")}
						</Text>
						<Text fontSize="14px" color="gray.500">
							{__(
								"Please try another search",
								"user-registration"
							)}
						</Text>
					</Box>
				) : (
					<Box>
						<Tabs index={tabIndex}>
							<TabPanels>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											isPerformingBulkAction
										}
										filteredAddons={modules}
										setSelectedModuleData={
											setSelectedModuleData
										}
										selectedModuleData={selectedModuleData}
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
