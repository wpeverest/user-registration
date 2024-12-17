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
	Text
} from "@chakra-ui/react";
import { __ } from "@wordpress/i18n";
import { debounce } from "lodash";

import { Search, PageNotFound } from "../../components/Icon/Icon";
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
	const [{ allModules }, dispatch] = useStateValue();
	const [state, setState] = useState({
		modules: [],
		originalModules: [],
		modulesLoaded: false,
		selectedModuleData: [],
		bulkAction: "",
		isPerformingBulkAction: false,
		searchItem: "",
		noItemFound: false,
		isSearching: false,
		error: null
	});
	const [tabIndex, setTabIndex] = useState(0);

	const fetchModules = useCallback(() => {
		getAllModules()
			.then((data) => {
				if (data.success) {
					dispatch({
						type: actionTypes.GET_ALL_MODULES,
						allModules: data.modules_lists
					});
					setState((prev) => ({
						...prev,
						originalModules: data.modules_lists,
						modulesLoaded: true
					}));
					filterModules(data.modules_lists, tabIndex);
				}
			})
			.catch((error) =>
				setState((prev) => ({ ...prev, error: error.message }))
			);
	}, [dispatch, tabIndex]);

	useEffect(() => {
		fetchModules();
	}, [fetchModules]);

	useEffect(() => {
		filterModules(state.originalModules, tabIndex);
	}, [tabIndex]);

	// Filter Modules by Tabs
	const filterModules = (modules, index) => {
		let filtered = modules;
		if (index === 1)
			filtered = modules.filter((mod) => mod.type === "feature");
		else if (index === 2)
			filtered = modules.filter((mod) => mod.type === "addon");

		setState((prev) => ({
			...prev,
			modules: filtered,
			noItemFound: filtered.length === 0
		}));
	};

	// Bulk Actions
	const handleBulkActions = () => {
		const { selectedModuleData, bulkAction } = state;
		if (!selectedModuleData.length) {
			showToast("Please select at least a feature", "error");
			return;
		}

		setState((prev) => ({ ...prev, isPerformingBulkAction: true }));
		const actionFunc =
			bulkAction === "activate"
				? bulkActivateModules
				: bulkDeactivateModules;

		actionFunc(selectedModuleData)
			.then((data) =>
				showToast(data.message, data.success ? "success" : "error")
			)
			.catch((e) => showToast(e.message, "error"))
			.finally(() => {
				setState((prev) => ({
					...prev,
					isPerformingBulkAction: false,
					selectedModuleData: {}
				}));
				fetchModules();
			});
	};

	const showToast = (title, status) => {
		toast({
			title: __(title, "user-registration"),
			status,
			duration: 3000
		});
	};

	// Search Modules
	const debounceSearch = useCallback(
		debounce((val) => {
			const lowerVal = val.toLowerCase();
			const filtered = state.originalModules.filter(
				(mod) =>
					(tabIndex === 1 ? mod.type === "feature" : true) &&
					(tabIndex === 2 ? mod.type === "addon" : true) &&
					mod.title.toLowerCase().includes(lowerVal)
			);

			setState((prev) => ({
				...prev,
				modules: filtered,
				noItemFound: filtered.length === 0,
				isSearching: false
			}));
		}, 800),
		[state.originalModules, tabIndex]
	);

	const handleSearchInputChange = (e) => {
		const val = e.target.value;
		setState((prev) => ({ ...prev, searchItem: val, isSearching: true }));
		debounceSearch(val);
	};

	const parseDate = (dateString) => {
		const [day, month, year] = dateString.split("/").map(Number);
		return new Date(year, month - 1, day);
	};

	const handleSorterChange = (sortType, data) => {
		switch (sortType) {
			case "newest":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(secondAddonInContext.released_date) -
							parseDate(firstAddonInContext.released_date)
					)
				}));

				break;
			case "oldest":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							parseDate(firstAddonInContext.released_date) -
							parseDate(secondAddonInContext.released_date)
					)
				}));
				break;
			case "asc":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							firstAddonInContext.title.localeCompare(
								secondAddonInContext.title
							)
					)
				}));
				break;
			case "desc":
				setState((prev) => ({
					...prev,
					modules: [...data].sort(
						(firstAddonInContext, secondAddonInContext) =>
							secondAddonInContext.title.localeCompare(
								firstAddonInContext.title
							)
					)
				}));
				break;
			default:
				setState((prev) => ({
					...prev,
					modulesLoaded: false
				}));
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
									state.modules
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
							onChange={(index) => setTabIndex(index)}
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
											target: {
												value: state.searchItem
											}
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
											target: { value: state.searchItem }
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
											target: {
												value: state.searchItem
											}
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
								isLoading={state.isPerformingBulkAction}
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
									value={state.searchItem}
									onChange={handleSearchInputChange}
								/>
							</InputGroup>
						</FormControl>
					</Stack>
				</Stack>
			</Container>
			<Container maxW="container.xl">
				{state.isSearching ? (
					<AddonSkeleton />
				) : state.noItemFound && state.searchItem ? (
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
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
										}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
										}
									/>
								</TabPanel>
								<TabPanel>
									<ModuleBody
										isPerformingBulkAction={
											state.isPerformingBulkAction
										}
										filteredAddons={state.modules}
										setSelectedModuleData={(data) =>
											setState((prev) => ({
												...prev,
												selectedModuleData: data
											}))
										}
										selectedModuleData={
											state.selectedModuleData
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
